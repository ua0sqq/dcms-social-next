<?php
include_once '../../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

$set['title'] = 'Статус - комментарии';
include_once H . 'sys/inc/thead.php';
title();

if (!$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)) {
    header('Location: /');
}
if (!$status = $db->query(
    "SELECT st.*, u.nick, u.pol, u.group_access FROM `status` st JOIN `user` u ON u.id=st.id_user WHERE st.`id`=?i",
                     [$id])->row()) {
    $_SESSION['err'] = 'Статус не найден!';
    header('Location: index.php?' . SID);
    exit;
}

//Приватность станички пользователя
if (isset($user)) {
    $sql = ', (
SELECT COUNT( * ) FROM `discussions` WHERE `count`<>0 AND `id_user`=' . $user['id'] . ' AND `type`="status" AND `id_sim`=' . $status['id'] . ') is_read_discus, (
SELECT COUNT( * ) FROM `notification` WHERE `read`="0" AND `type`="status_komm" AND `id_user`=' . $user['id'] . ' AND `id_object`=' . $status['id'] . ') is_read_notif';
} else {
    $sql = null;
}
    $uSet = $db->query(
        "SELECT uset.privat_str, (
SELECT COUNT( * ) FROM `frends` WHERE (`user`=?i AND `frend`=uset.id_user) OR (`user`=uset.id_user AND `frend`=?i)) frend, (
SELECT COUNT( * ) FROM `frends_new` WHERE (`user`=?i AND `to`=uset.id_user) OR (`user`=uset.id_user AND `to`=?i)) frend_new ?q
FROM `user_set` uset WHERE uset.`id_user`=?i  LIMIT ?i",
                       [$user['id'], $user['id'], $user['id'], $user['id'], $sql, $status['id_user'], 1])->row();

if ($status['id_user'] != $user['id'] && $user['group_access'] == 0) {
    if (($uSet['privat_str'] == 2 && $uSet['frend'] != 2) || $uSet['privat_str'] == 0) { // Начинаем вывод если стр имеет приват настройки
        if ($status['group_access'] > 1) {
            echo "<div class='err'>$status[group_name]</div>\n";
        }
        echo "<div class='nav1'>";
        echo group($status['id_user']) . ' ' . $status['nick'] . ' ';
        echo medal($status['id_user'])." ".online($status['id_user'])." ";
        echo "</div>";
        echo "<div class='nav2'>";
        avatar($status['id_user']);
        echo "</div>";
    }
    // Если только для друзей
    if ($uSet['privat_str'] == 2 && $uSet['frend'] != 2) {
        echo '<div class="mess">';
        echo 'Комментировать статус пользователя могут только его друзья!';
        echo '</div>';
        
        // В друзья
        if (isset($user)) {
            echo '<div class="nav1">';
            if ($uSet['frend_new'] == 0 && $uSet['frend'] == 0) {
                echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?add=".$status['id_user']."'>Добавить в друзья</a><br />\n";
            } elseif ($uSet['frend_new'] == 1) {
                echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?otm={$status['id_user']}'>Отклонить заявку</a><br />\n";
            } elseif ($uSet['frend'] == 2) {
                echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?del={$status['id_user']}'>Удалить из друзей</a><br />\n";
            }
            echo "</div>";
        }
        include_once H . 'sys/inc/tfoot.php';
        exit;
    }
    
    if ($uSet['privat_str'] == 0) { // Если закрыта
        echo '<div class="mess">';
        echo 'Пользователь запретил комментировать его статусы!';
        echo '</div>';
        
        include_once H . 'sys/inc/tfoot.php';
        exit;
    }
}
//Модуль жалобы на пользователя
if (isset($_GET['spam'])  && isset($user)) {
    $mess = $db->query(
        "SELECT * FROM `status_komm` WHERE `id`=?i",
                       [$_GET['spam']])->row();
    $spamer = get_user($mess['id_user']);
    if (!$db->query(
        "SELECT COUNT( * ) FROM `spamus` WHERE `id_user`=?i AND `id_spam`=?i AND `razdel`=? AND `spam`=?",
                    [$user['id'], $spamer['id'], 'status_komm', $mess['msg']])->el()) {
        if (isset($_POST['msg'])) {
            if ($mess['id_user'] != $user['id']) {
                $msg = trim($_POST['msg']);
                if (strlen2($msg) < 3) {
                    $err='Укажите подробнее причину жалобы';
                }
                if (strlen2($msg) > 1512) {
                    $err='Длина текста превышает предел в 512 символов';
                }
                if (isset($_POST['types'])) {
                    $types = intval($_POST['types']);
                } else {
                    $types = 0;
                }
                if (!isset($err)) {
                    $db->query(
                        "INSERT INTO `spamus` (`id_object`, `id_user`, `msg`, `id_spam`, `time`, `types`, `razdel`, `spam`) VALUES(?i, ?i, ?, ?i, ?i, ?i, ?, ?)",
                               [$status['id'], $user['id'], $msg, $spamer['id'], time(), $types, 'status_komm', $mess['msg']]);
                    $_SESSION['message'] = 'Заявка на рассмотрение отправлена';
                    header('Location: ?id=' . $status['id'] . '&amp;spam=' . $mess['id'] . '&amp;page=' . intval($_GET['page']));
                    exit;
                }
            }
        }
    }
    aut();
    err();
    if (!$db->query(
        "SELECT COUNT( * ) FROM `spamus` WHERE `id_user`=?i AND `id_spam`=?i AND `razdel`=?",
                    [$user['id'], $spamer['id'], 'status_komm'])->el()) {
        echo "<div class='mess'>Ложная информация может привести к блокировке ника. 
Если вас постоянно достает один человек - пишет всякие гадости, вы можете добавить его в черный список.</div>";
        echo "<form class='nav1' method='post' action='?id=$status[id]&amp;spam=$mess[id]&amp;page=".intval($_GET['page'])."'>\n";
        echo "<b>Пользователь:</b> ";
        echo " ".status($spamer['id'])."  ".group($spamer['id'])." <a href=\"/info.php?id=$spamer[id]\">$spamer[nick]</a>\n";
        echo "".medal($spamer['id'])." ".online($spamer['id'])." (".vremja($mess['time']).")<br />";
        echo "<b>Нарушение:</b> <font color='green'>".output_text($mess['msg'])."</font><br />";
        echo "Причина:<br />\n<select name='types'>\n";
        echo "<option value='1' selected='selected'>Спам/Реклама</option>\n";
        echo "<option value='2' selected='selected'>Мошенничество</option>\n";
        echo "<option value='3' selected='selected'>Оскорбление</option>\n";
        echo "<option value='0' selected='selected'>Другое</option>\n";
        echo "</select><br />\n";
        echo "Комментарий:$tPanel";
        echo "<textarea name=\"msg\"></textarea><br />";
        echo "<input value=\"Отправить\" type=\"submit\" />\n";
        echo "</form>\n";
    } else {
        echo "<div class='mess'>Жалоба на <span class='on'>$spamer[nick]</span> будет рассмотрена в ближайшее время.</div>";
    }
    echo "<div class='foot'>\n";
    echo "<img src='/style/icons/str2.gif' alt='*'> <a href='?id=$status[id]&page=".intval($_GET['page'])."'>Назад</a><br />\n";
    echo "</div>\n";
    include_once H . 'sys/inc/tfoot.php';
    exit;
}
// The End
// очищаем счетчик этого обсуждения
if (isset($user)) {
    if ($uSet['is_read_discus']) {
        $db->query(
        "UPDATE `discussions` SET `count`=0 WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i LIMIT ?i",
               [$user['id'], 'status', $status['id'], 1]);
    }
    if ($uSet['is_read_notif']) {
        $db->query(
        "UPDATE `notification` SET `read`=? WHERE `type`=? AND `id_user`=?i AND `id_object`=?i",
               ['1', 'status_komm', $user['id'], $status['id']]);
    }
}

if (isset($_POST['msg']) && isset($user)) {
    $msg = trim($_POST['msg']);
    if (isset($_POST['translit']) && $_POST['translit'] == 1) {
        $msg=translit($msg);
    }
    $mat=antimat($msg);
    if ($mat) {
        $err[] = 'В тексте сообщения обнаружен мат: '.$mat;
    }
    if (strlen2($msg) > 1024) {
        $err='Сообщение слишком длинное';
    } elseif (strlen2($msg) < 2) {
        $err='Короткое сообщение';
    } elseif ($db->query(
        "SELECT COUNT( * ) FROM `status_komm` WHERE `id_status`=?i AND `id_user`=?i AND `msg`=?",
                         [$id, $user['id'], $msg])->el()) {
        $err='Ваше сообщение повторяет предыдущее';
    } elseif (!isset($err)) {
        // Уведомления об ответах
        if (isset($user) && $respons == true) {
            $notifiacation = $db->query(
                "SELECT `komm` FROM `notification_set` WHERE `id_user`=?i",
                                        [$ank_reply['id']])->el();
            
            if ($notifiacation == 1 && $ank_reply['id'] != $user['id']) {
                $db->query(
                    "INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES (?i, ?i, ?i, ?, ?i)",
                           [$user['id'], $ank_reply['id'], $status['id'], 'status_komm', time()]);
            }
        }
        // Обсуждения
        $res = $db->query(
                    "SELECT fr.user, fr.disc_status, dsc.disc_status AS set_discus_status, (
SELECT COUNT( * ) FROM `discussions` WHERE `id_user`=fr.`user` AND `type`='status' AND `id_sim`=?i) is_discus
FROM `frends` fr 
JOIN discussions_set dsc ON dsc.id_user=fr.user
WHERE fr.`frend`=?i AND fr.`disc_status`=1 AND fr.`user`<>?i AND `i`=1 AND dsc.disc_status=1",
                        [$status['id'], $status['id_user'], $user['id']])->assoc();
             
        foreach ($res as $frend) {
            if ($frend['disc_status'] == 1 && $frend['set_discus_status'] == 1) {
                if (!$frend['is_discus']) {
                    $insert_discus[] = [(int)$frend['user'], (int)$status['id_user'], 'status', time(), (int)$status['id'], 1];
                } else {
                    $update_list = [$frend['user']];
                }
            }
        }
        if (!empty($insert_discus)) {
            $db->query(
                        'INSERT INTO `discussions` (`id_user`, `avtor`, `type`, `time`, `id_sim`, `count`) VALUES ?v',
                               [$insert_discus]);
        }
        if (!empty($update_list)) {
            $db->query(
                        'UPDATE `discussions` SET `count`=`count`+1, `time`=?i WHERE `id_user` IN(?li) AND `type`=? AND `id_sim`=?i',
                               [time(), $update_list, 'status', $status['id']]);
        }
        // отправляем автору
        if (!$db->query(
            "SELECT COUNT( * ) FROM `discussions` WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i",
                        [$status['id_user'], 'status', $status['id']])->el()) {
            if ($status['id_user'] != $user['id']) {
                $db->query(
                    "INSERT INTO `discussions` (`id_user`, `avtor`, `type`, `time`, `id_sim`, `count`) VALUES(?i, ?i, ?, ?i, ?i, ?i)",
                           [$status['id_user'], $status['id_user'], 'status', time(), $status['id'], 1]);
            }
        } else {
            if ($status['id_user'] != $user['id']) {
                $db->query(
                    "UPDATE `discussions` SET `count`=`count`+1, `time`=?i WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i LIMIT ?i",
                           [time(), $status['id_user'], 'status', $status['id'], 1]);
            }
        }
        $db->query(
            "INSERT INTO `status_komm` (`id_user`, `time`, `msg`, `id_status`) VALUES(?i, ?i, ?, ?i)",
                   [$user['id'], time(), $msg, $id]);
        $db->query(
            "UPDATE `user` SET `balls`=`balls`+1 WHERE `id`=?i",
                   [$user['id']]);
        $_SESSION['message'] = 'Сообщение упешно отправлено';
        header("Location: /user/status/komm.php?id=$status[id]");
        exit;
    }
}

err();
aut(); // форма авторизации

echo "<div class='foot'>";
echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/info.php?id={$status['id_user']}\">{$status['nick']}</a> | <a href='index.php?id=".$status['id_user']."'>Статусы</a> | <b>Комментарии</b>";
echo "</div>";
echo '<div class="main">';
status($status['id_user']);
group($status['id_user']);
echo " <a href='/info.php?id={$status['id_user']}'>{$status['nick']}</a>";
echo " ".medal($status['id_user'])." ".online($status['id_user'])." <br />\n";
if ($status['id']) {
    echo '<div class="st_1"></div>';
    echo '<div class="st_2">';
    echo output_text($status['msg']).' <span style="font-size; small;font-style: oblique;">'.vremja($status['time']).'</span>';
    echo "</div>";
}
echo "</div>";
echo "<div class='foot'>\n";
echo "Комментарии:\n";
echo "</div>";
$k_post = $db->query(
    "SELECT COUNT( * ) FROM `status_komm` WHERE `id_status`=?i",
                     [$id])->el();
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start = $set['p_str']*$page-$set['p_str'];

if (!$k_post) {
    echo "<div class='mess'>\n";
    echo "Нет сообщений\n";
    echo "</div>";
} else {

    $q = $db->query(
    "SELECT stk.*, u.nick, u.group_access, (
SELECT COUNT( * ) FROM `ban` WHERE (`razdel`='all') AND `post`=1 AND `id_user`=stk.id_user AND (`time`>" . time() . " OR `navsegda`=1)) post_ban
FROM `status_komm` stk
JOIN `user` u ON u.id=stk.id_user
WHERE stk.`id_status`=?i ORDER BY stk.`id` DESC LIMIT ?i, ?i",
                [$id, $start, $set['p_str']]);

    while ($post = $q->row()) {
        if ($num==0) {
            echo '<div class="nav1">';
            $num=1;
        } elseif ($num==1) {
            echo '<div class="nav2">';
            $num=0;
        }
        group($post['id_user']);
        echo " <a href='/info.php?id={$post['id_user']}'>{$post['nick']}</a> ";
        if (isset($user) && $post['id_user'] != $user['id']) {
            echo "<a href='?id=$status[id]&amp;response={$post['id_user']}'>[*]</a> ";
        }
        echo "".medal($post['id_user'])." ".online($post['id_user'])." (".vremja($post['time']).")<br />\n";
    
        if (!$post['post_ban']) { // Блок сообщения
            echo output_text($post['msg'])."<br />\n";
        } else {
            echo output_text($banMess).'<br />';
        }
        if (isset($user) && ($user['group_access'] > $post['group_access'] || $user['id'] == $post['id_user'])) {
            echo "<div style='text-align:right;'>";
            if ($post['id_user'] != $user['id']) {
                echo "<a href=\"?id=$status[id]&amp;spam=$post[id]&amp;page=$page\"><img src='/style/icons/blicon.gif' alt='*' title='Это спам'></a> ";
            }
            echo " <a href='/user/status/delete_komm.php?id=$post[id]'><img src='/style/icons/delete.gif' alt='*'></a>";
            echo "</div>";
        }
        echo "</div>";
    }
    if ($k_page>1) {
        str("/user/status/komm.php?id=".$id.'&amp;', $k_page, $page);
    } // Вывод страниц
}
if (isset($user)) {
    echo "<form method=\"post\" name='message' action=\"?id=".$id."&amp;page=$page" . $go_link . "\">\n";
    if ($set['web'] && is_file(H.'style/themes/'.$set['set_them'].'/altername_post_form.php')) {
        include_once H.'style/themes/'.$set['set_them'].'/altername_post_form.php';
    } else {
        echo "$tPanel<textarea name=\"msg\">$insert</textarea><br />\n";
    }
    echo "<input value=\"Отправить\" type=\"submit\" />\n";
    echo "</form>\n";
}
echo "<div class='foot'>";
echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/info.php?id={$status['id_user']}\">{$status['nick']}</a> | <a href='index.php?id=".$status['id_user']."'>Статусы</a> | <b>Комментарии</b>";
echo "</div>";

include_once H . 'sys/inc/tfoot.php';
