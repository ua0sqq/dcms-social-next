<?php
include_once '../../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

include_once H . 'sys/inc/thead.php';
$post=$db->query("SELECT * FROM `rules` WHERE `id`=?i", [$_GET['id']])->row();
$set['title'] = htmlspecialchars($post['title']);
title();
aut(); // форма авторизации
$k_post=$db->query("SELECT COUNT(*) FROM `rules`")->el();
if (!isset($_GET['id']) && !is_numeric($_GET['id']));
if ($user['level'] > 2) {
    if (isset($_POST['msg']) && isset($user)) {
        $msg=$_POST['msg'];
        if (strlen2($msg)>99999) {
            $err='Сообщение слишком длинное';
        } elseif (strlen2($msg)<2) {
            $err='Короткое сообщение';
        } elseif ($db->query(
            "SELECT COUNT(*) FROM `rules_p` WHERE `id_news`=?i AND `id_user`=?i AND `msg`=?",
                             [$_GET['id'], $user['id'], $msg]
        )->el()) {
            $err='Ваше сообщение повторяет предыдущее';
        } elseif (!isset($err)) {
            $pos=$db->query("SELECT MAX(`pos`) FROM `rules_p` WHERE `id_news`=?i", [$_GET['id']])->el()+1;
            $db->query(
                "INSERT INTO `rules_p` (`pos`, `id_user`, `time`, `msg`, `id_news`) VALUES( ?i, ?i, ?i, ?, ?i)",
                       [$pos, $user['id'], $time, $msg, $_GET['id']]
            );
            $_SESSION['message'] = 'Ваш пост успешно принят';
            header("Location: ?id=$post[id]");
            exit;
        }
    }
    if (isset($_GET['ids'])) {
        $menu=$db->query("SELECT * FROM `rules_p` WHERE `id`=?i", [$_GET['ids']])->row();
    }
    
    if (isset($_GET['ids']) && isset($_GET['act']) && $_GET['act']=='up') {
        $db->query(
            "UPDATE `rules_p` SET `pos`=?i WHERE `pos`=?i LIMIT ?i",
                   [$menu['pos'], ($menu['pos']-1), 1]
        );
        $db->query(
            "UPDATE `rules_p` SET `pos`=?i WHERE `id`=?i LIMIT ?i",
                   [($menu['pos']-1), $_GET['ids'], 1]
        );
        $_SESSION['message'] = 'Пункт меню сдвинут на позицию вверх';
        header("Location: ?id=$post[id]");
        exit;
    }
    if (isset($_GET['ids']) && isset($_GET['act']) && $_GET['act']=='down') {
        $db->query(
            "UPDATE `rules_p` SET `pos`=?i WHERE `pos`=?i LIMIT ?i",
                   [($menu['pos']), ($menu['pos']+1), 1]
        );
        $db->query(
            "UPDATE `rules_p` SET `pos`=?i WHERE `id`=?i LIMIT ?i",
                   [($menu['pos']+1), $_GET['ids'], 1]
        );
        
        $_SESSION['message'] = 'Пункт меню сдвинут на позицию вниз';
        header("Location: ?id=$post[id]");
        exit;
    }
}
$k_post=$db->query("SELECT COUNT(*) FROM `rules_p` WHERE `id_news`=?i", [$_GET['id']])->el();
$q=$db->query("SELECT * FROM `rules_p` WHERE `id_news`=?i ORDER BY `pos` ASC", [$_GET['id']]);

while ($post2 = $q->row()) {
    $ank=get_user($post2['id_user']);
    
    /*-----------зебра-----------*/
    if ($num==0) {
        echo '<div class="nav1">';
        $num=1;
    } elseif ($num==1) {
        echo '<div class="nav2">';
        $num=0;
    }
    /*---------------------------*/
    echo(($user['level'] > 2)? $post2['pos'] . ") ":"");
    echo output_text($post2['msg']) . '</br>';
    if ($user['level'] > 2) {
        echo '<a href="?ids=' . $post2['id'] . '&amp;id=' . $post['id'] . '&amp;act=up&amp;' . $passgen . '"><img src="/style/icons/up.gif" alt="*" /></a> | ';
        echo '<a href="?ids=' . $post2['id'] . '&amp;id=' . $post['id'] . '&amp;act=down&amp;' . $passgen . '"><img src="/style/icons/down.gif" alt="*" /></a> | ';
        echo '<a href="edit.php?id=' . $post2['id'] . '&amp;act=edit&amp;' . $passgen . '"><img src="/style/icons/edit.gif" alt="*" /></a> | ';
        echo '<a href="./delete.php?del=' . $post2['id'] . '"><img src="/style/icons/delete.gif" alt="*" /></a>';
    }
    echo '</div>';
}

if ($user['level'] > 2) {
    if (isset($_GET['new'])) {
        echo '<form method="post" name="message" action="?id='.intval($_GET['id']) . '">';
        if ($set['web'] && is_file(H.'style/themes/'.$set['set_them'].'/altername_post_form.php')) {
            include_once H.'style/themes/'.$set['set_them'].'/altername_post_form.php';
        } else {
            echo '<textarea name="msg"></textarea><br />';
        }
        echo '<input value="Добавить" type="submit" />';
        echo '</form>';
    }
    echo '<div class="foot"><img src="/style/icons/ok.gif" alt="*"/> <a href="post.php?id=' . intval($_GET['id']) . '&new">Новый пост</a></div>';
}
echo '<div class="foot"><img src="/style/icons/str2.gif" alt="*"/> <a href="index.php">Информация</a></div>';
include_once H . 'sys/inc/tfoot.php';
