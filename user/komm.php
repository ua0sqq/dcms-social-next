<?php
include_once '../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

$get_id = filter_input_array(INPUT_GET, FILTER_VALIDATE_INT);
$post_msg = filter_input(INPUT_POST, 'msg', FILTER_DEFAULT);
if (isset($get_id['id']) && $db->query(
    "SELECT COUNT(*) FROM `stena` WHERE `id`=?i",
                                                                [$get_id['id']]
)->el()) {
    $post=$db->query("SELECT * FROM `stena` WHERE `id`=?i", [$get_id['id']])->row();
    $set['title']=' Комментарии к записи';
    include_once H . 'sys/inc/thead.php';
    title();
    if (isset($post_msg) && isset($user)) {
        $msg = esc($post_msg);
        $mat = antimat($msg);
        if ($mat) {
            $err[] = 'В тексте сообщения обнаружен мат: ' . $mat;
        }
        if (strlen2($msg) > 1024) {
            $err[] = 'Сообщение слишком длинное';
        } elseif (strlen2($msg) < 2) {
            $err[] = 'Короткое сообщение';
        } elseif ($db->query(
            "SELECT COUNT(`id`) FROM `stena_komm` WHERE `id_user`=?i AND `msg`=? AND `id_stena`=?i",
                             [$user['id'], $msg, $get_id['id']]
        )->el()) {
            $err[] = 'Ваше сообщение повторяет предыдущее';
        } elseif (!isset($err)) {
            $db->query(
                "INSERT INTO `stena_komm` (`id_user`, `time`, `msg`,`id_stena`) VALUES(?i, ?i, ?, ?i)",
                       [$user['id'], $time, $msg, $get_id['id']]
            );
            
            // Отправляем автору комма
            if (isset($user)) {
                if ($post['id_user'] != $user['id'] && $db->query(
                    "SELECT `komm` FROM `notification_set` WHERE `id_user` = ?i",
                                                                  [$post['id_user']]
                )->el()) {
                    $db->query(
                        "INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES (?i, ?i, ?i, ?, ?i)",
                               [$user['id'], $post['id_user'], $post['id'],  'stena_komm2', $time]
                    );
                }
            }
            $_SESSION['message']='Сообщение успешно добавлено';
            header('Location: /user/komm.php?id='.$get_id['id']);
        }
    } elseif (isset($get_id['del']) && $db->query(
        "SELECT COUNT(*) FROM `stena_komm` WHERE `id` = ?i AND `id_stena` = ?i",
                                                  [$get_id['del'], $post['id']]
    )->el()) {
        if (isset($user) && ($user['level'] > 2 || $user['id'] = $stena['id_user'])) {
            $db->query("DELETE FROM `stena_komm` WHERE `id` = ?i", [$get_id['del']]);
            $_SESSION['message']='Комментарий успешно удален';
            header('Location: /user/komm.php?id='.$get_id['id']);
        }
    }
    
    err();
    aut();

    $post = $db->query("SELECT * FROM `stena` WHERE `id` = ?i", [$get_id['id']])->row();
    echo "  <div class='nav2'>\n";
    echo "<table><td style='width:15%;vertical-align:top;'>";
    echo avatar($post['id_user']);
    echo "</td><td style='vertical-align:top;'>";
    echo  group($post['id_user'])." ";
    echo user::nick($post['id_user'], 1, 1, 1);
    echo " <span style='color:#666'>".vremja($post['time'])."</span><br/>";
    stena($post['id_user'], $post['id']);
    echo output_text($post['msg'])."<br />\n";
    echo "</td></table></div>";
    $k_post = $db->query(
        "SELECT COUNT(*) FROM `stena_komm` WHERE `id_stena` = ?i",
                       [$get_id['id']]
    )->el();
    $k_page=k_page($k_post, $set['p_str']);
    $page=page($k_page);
    $start=$set['p_str']*$page-$set['p_str'];
    $q=$db->query(
        "SELECT * FROM `stena_komm` WHERE `id_stena` = ?i ORDER BY `id` DESC LIMIT ?i OFFSET ?i",
                  [$get_id['id'], $set['p_str'], $start]
    );
    echo "<div class='main'><b>Комментарии:</b> (".$k_post.")</div>";
    if ($k_post==0) {
        echo'<div class="mess">';
        echo'<font color=grey>Запись ещё никто не комментировал.</font>';
        echo'</div>';
    }
    while ($komm = $q->row()) {
        echo'<div class="nav1">';
        echo group($komm['id_user']).' ' ;
        echo user::nick($komm['id_user'], 1, 1, 1);
        echo ' ('.vremja($komm['time']).')';
        echo "<br />";
        echo output_text($komm['msg'])."<br />\n";
        if (isset($user) && ($user['level']>=3 || $user['id'] == $post['id_user'])) {
            echo'<a href="?id='.$post['id'].'&del='.$komm['id'].'">Удалить</a><br />';
        }
        echo'</div>';
    }
    // Вывод страниц
    if ($k_page>1) {
        str("/user/tape/komm.php?id=$post[id]&", $k_page, $page);
    }
    if (!isset($post_msg) && isset($user)) {

        echo '<form method="post" name="message" action="?id='.$post['id'].'">';
        if ($set['web'] && is_file(H.'style/themes/'.$set['set_them'].'/altername_post_form.php')) {
            include_once H.'style/themes/'.$set['set_them'].'/altername_post_form.php';
        } else {
            echo "$tPanel<textarea name=\"msg\"></textarea><br />\n";
        }
        echo'<input value="Отправить" type="submit" />';
        echo'</form>';
    }
} else {
    header('Location: /index.php');
}
include_once H . 'sys/inc/tfoot.php';
