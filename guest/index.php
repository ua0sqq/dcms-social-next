<?php
include_once '../sys/inc/start.php';
include_once '../sys/inc/compress.php';
include_once '../sys/inc/sess.php';
include_once '../sys/inc/home.php';
include_once '../sys/inc/settings.php';
include_once '../sys/inc/db_connect.php';
include_once '../sys/inc/ipua.php';
include_once '../sys/inc/fnc.php';
include_once '../sys/inc/user.php';

// Очищаем уведомления об ответах
if (isset($user)) {
    $db->query(
        "UPDATE `notification` SET `read`=? WHERE `type`=? AND `id_user`=?i",
               ['1', 'guest', $user['id']]
    );
    // Действия с комментариями
    include 'inc/admin_act.php';
}
// Отправка комментариев
if (isset($_POST['msg']) && isset($user)) {
    $msg = $_POST['msg'];
    $mat = antimat($msg);
    if ($mat) {
        $err[] = 'В тексте сообщения обнаружен мат: ' . $mat;
    }
    if (strlen2($msg) > 1024) {
        $err[] = 'Сообщение слишком длинное';
    } elseif (strlen2($msg) < 2) {
        $err[] = 'Короткое сообщение';
    } elseif ($db->query(
        "SELECT COUNT(*) FROM `guest` WHERE `id_user`=?i AND `msg`=?",
                       [$user['id'], $msg]
    )->el()) {
        $err = 'Ваше сообщение повторяет предыдущее';
    } elseif (!isset($err)) {
        // Начисление баллов за активность
        include_once H.'sys/add/user.active.php';
        // Уведомления об ответах

        if (isset($ank_reply['id'])) {
            $notifiacation = $db->query(
                "SELECT * FROM `notification_set` WHERE `id_user`=?i LIMIT ?i",
                                        [$ank_reply['id'], 1]
            )->row();
            
            if ($notifiacation['komm'] == 1 && $ank_reply['id'] != $user['id']) {
                $db->query(
                    "INSERT INTO `notification` (`avtor`, `id_user`, `type`, `time`) VALUES (?i, ?i, ?, ?i)",
                           [$user['id'], $ank_reply['id'], 'guest', $time]
                );
            }
        }
        $db->query(
            "INSERT INTO `guest` (id_user, time, msg) values(?i, ?i, ?)",
                   [$user['id'], $time, $msg]
        );
        
        $_SESSION['message'] = 'Сообщение успешно добавлено';
        header("Location: index.php" . SID);
        exit;
    }
} elseif (!isset($user) && isset($set['write_guest']) && $set['write_guest'] == 1 && isset($_SESSION['captcha']) && isset($_POST['chislo'])) {
    $msg = trim($_POST['msg']);
    $mat = antimat($msg);
    
    if ($mat) {
        $err[] = 'В тексте сообщения обнаружен мат: '.$mat;
    }
    if (strlen2($msg) > 1024) {
        $err = 'Сообщение слишком длинное';
    } elseif ($_SESSION['captcha'] != $_POST['chislo']) {
        $err = 'Неверное проверочное число';
    } elseif (isset($_SESSION['antiflood']) && $_SESSION['antiflood'] > $time - 300) {
        $err = 'Для того чтобы чаще писать нужно авторизоваться';
    } elseif (strlen2($msg) < 2) {
        $err = 'Короткое сообщение';
    } elseif ($db->query(
        "SELECT COUNT(*) FROM `guest` WHERE `id_user`=?i AND `msg`=?",
                         [0, $msg]
    )->el()) {
        $err = 'Ваше сообщение повторяет предыдущее';
    } elseif (!isset($err)) {
        $_SESSION['antiflood'] = $time;
        $db->query(
            "INSERT INTO `guest` (id_user, time, msg) values(?i, ?i, ?)",
                   [0, $time, $msg]
        );
        
        $_SESSION['message'] = 'Сообщение успешно добавлено';
        header("Location: index.php" . SID);
        exit;
    }
}
// заголовок страницы
$set['title'] = 'Гостевая книга';
include_once '../sys/inc/thead.php';
title();
aut();
err();

$k_post = $db->query("SELECT COUNT(*) FROM `guest`")->el();
$k_page = k_page($k_post, $set['p_str']);
$page = page($k_page);
$start = $set['p_str'] * $page - $set['p_str'];

// Форма для комментариев
if (isset($user) || (isset($set['write_guest']) && $set['write_guest'] == 1 && (!isset($_SESSION['antiflood']) || $_SESSION['antiflood'] < $time - 300))) {
    echo '<form method="post" name="message" action="?page=' . $page . $go_link . '">';
    if (is_file(H.'style/themes/' . $set['set_them'] . '/altername_post_form.php')) {
        include_once H.'style/themes/' . $set['set_them'] . '/altername_post_form.php';
    } else {
        echo $tPanel . '<textarea name="msg">' . $insert . '</textarea><br />';
    }
    
    if (!isset($user) && isset($set['write_guest']) && $set['write_guest'] == 1) {
        ?>
        <img src="/captcha.php?SESS=<?= $sess?>" width="100" height="30" alt="Captcha" /> <input name="chislo" size="7" maxlength="5" value="" type="text" placeholder="Цифры.."/><br />
        <?php
    }
    
    echo '<input value="Отправить" type="submit" />';
    echo '</form>';
} elseif (!isset($user) && isset($set['write_guest']) && $set['write_guest'] == 1) {
    ?><div class="mess">Вы сможете писать через <span class="on"><?= abs($time - $_SESSION['antiflood'] - 300)?> сек.</span></div><?php
}
echo '<table class="post">';
if ($k_post == 0) {
    echo '<div class="mess" id="no_object">';
    echo 'Нет сообщений';
    echo '</div>';
}
$q = $db->query(
    "SELECT gst.*, u.id AS id_user, u.`level` FROM `guest` gst
JOIN `user` u ON u.id=gst.id_user ORDER BY gst.id DESC LIMIT ?i OFFSET ?i",
                [$set['p_str'], $start]
);
while ($post = $q->row()) {
    echo '<div class="' . ($num % 2 ? "nav1" : "nav2") . '">';
    $num++;
    
    echo($post['id_user'] != 0 ? user::avatar($post['id_user'], 0) . user::nick($post['id_user'], 1, 1, 1) : user::avatar(0, 0) . ' <b>' . 'Гость' . '</b> ');
    if (isset($user) && $user['id'] != $post['id_user']) {
        echo ' <a href="?page=' . $page . '&amp;response=' . $post['id_user'] . '">[*]</a> (' . vremja($post['time']) . ')<br />';
    }
    echo ' (' . vremja($post['time']) . ') <br />';
    echo output_text($post['msg']) . '<br />';
    if (isset($user) && ($user['level'] > $post['level'] || $user['level'] != 0 && $user['id'] == $post['id_user']) && user_access('guest_delete')) {
        echo '<div class="right">';
        echo '<a href="delete.php?id=' . $post['id'] . '"><img src="/style/icons/delete.gif" alt="*"></a>';
        echo '</div>';
    }
    echo '</div>';
}
echo '</table>';
if ($k_page > 1) {
    str('index.php?', $k_page, $page); // Вывод страниц
}
echo '<div class="foot">';
echo '<img src="/style/icons/str.gif" alt="*"> <a href="who.php">В гостевой (' .
$db->query(
    'SELECT COUNT(*) FROM `user` WHERE `date_last`>?i AND `url` LIKE "?e%"',
           [(time()-100), '/guest/']
)->el() . ' чел.)</a><br />';
echo '</div>';
// Форма очистки комментов
include 'inc/admin_form.php';
include_once '../sys/inc/tfoot.php';
?>