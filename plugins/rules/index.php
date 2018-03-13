<?php
include_once '../../sys/inc/start.php';
include_once '../../sys/inc/compress.php';
include_once '../../sys/inc/sess.php';
include_once '../../sys/inc/home.php';
include_once '../../sys/inc/settings.php';
include_once '../../sys/inc/db_connect.php';
include_once '../../sys/inc/ipua.php';
include_once '../../sys/inc/fnc.php';
include_once '../../sys/inc/user.php';

$set['title']='Информация';
include_once '../../sys/inc/thead.php';
title();
aut(); // форма авторизации

if (isset($user) && $user['level'] > 2) {
    if (isset($_GET['del']) && is_numeric($_GET['del']) && $db->query("SELECT COUNT(*) FROM `rules` WHERE `id` = '".intval($_GET['del'])."' LIMIT 1")->el()) {
        $db->query("DELETE FROM `rules` WHERE `id` = '".intval($_GET['del'])."' LIMIT 1");
        $db->query("OPTIMIZE TABLE `rules`");
        $_SESSION['message'] = 'Пункт успешно удален';
        header("Location: ?");
        exit;
    }
}
if (isset($_GET['id']) && isset($_GET['act']) && $db->query("SELECT COUNT(*) FROM `rules` WHERE `id` = '".intval($_GET['id'])."'")->el()) {
    $menu=$db->query("SELECT * FROM `rules` WHERE `id` = '".intval($_GET['id'])."' LIMIT 1")->row();
    if ($_GET['act']=='up' && $user['level'] > 2) {
        $db->query("UPDATE `rules` SET `pos` = '".($menu['pos'])."' WHERE `pos` = '".($menu['pos']-1)."' LIMIT 1");
        $db->query("UPDATE `rules` SET `pos` = '".($menu['pos']-1)."' WHERE `id` = '".intval($_GET['id'])."' LIMIT 1");
        $_SESSION['message'] = 'Пункт меню сдвинут на позицию вверх';
        header("Location: ?");
        exit;
    }
    if ($_GET['act']=='down' && $user['level'] > 2) {
        $db->query("UPDATE `rules` SET `pos` = '".($menu['pos'])."' WHERE `pos` = '".($menu['pos']+1)."' LIMIT 1");
        $db->query("UPDATE `rules` SET `pos` = '".($menu['pos']+1)."' WHERE `id` = '".intval($_GET['id'])."' LIMIT 1");
        $_SESSION['message'] = 'Пункт меню сдвинут на позицию вниз';
        header("Location: ?");
        exit;
    }
}

$k_post = $db->query("SELECT COUNT(*) FROM `rules`")->el();

echo '<table class="post">';
if (!$k_post) {
    echo '<div class="mess">';
    echo 'Раздел информации не заполнен';
    echo '</div>';
} else {
    $q = $db->query("SELECT * FROM `rules` ORDER BY `pos` ASC");
    while ($post = $q->row()) {
        /*-----------зебра-----------*/
        if ($num==0) {
            echo '<div class="nav1">';
            $num=1;
        } elseif ($num==1) {
            echo '<div class="nav2">';
            $num=0;
        }
        /*---------------------------*/
        if ($post['title']) {
            echo(($user['level'] > 2) ? $post['pos'] . ") " : "") . ' <a href="post.php?id=' . $post['id'] . '">' . output_text($post['title']) . '</a> ';
        }
        if ($post['url']) {
            echo(($user['level'] > 2) ? $post['pos'] . ") " : "") . ' <a href="' . htmlspecialchars($post['url']) . '">' . output_text($post['name_url']) . '</a> ';
        }
        if ($post['msg']) {
            echo(($user['level'] > 2)? $post['pos'] . ") " : "") . output_text($post['msg']) . ' ';
        }
        if ($user['level'] > 2) {
            echo '<a href="?id=' . $post['id'] . '&amp;act=up&amp;' . $passgen . '"><img src="/style/icons/up.gif" alt="*" /></a> | ';
            echo '<a href="?id=' . $post['id'] . '&amp;act=down&amp;' . $passgen . '"><img src="/style/icons/down.gif" alt="*" /></a> | ';
            echo '<a href="edit.php?id=' . $post['id'] . '&amp;act=edits&amp;' . $passgen . '"><img src="/style/icons/edit.gif" alt="*" /></a> | ';
            echo '<a href="index.php?del=' . $post['id'] . '"><img src="/style/icons/delete.gif" alt="*" /></a>';
        }
        echo '</div>';
    }
}
echo '</table>';
if ($user['level'] > 2) {
    echo '<div class="foot"><img src="/style/icons/ok.gif" alt="*" /> <a href="new.php?msg">Добавить текст</a></div>';
    echo '<div class="foot"><img src="/style/icons/ok.gif" alt="*" /> <a href="new.php?post">Добавить пункт</a></div>';
    echo '<div class="foot"><img src="/style/icons/ok.gif" alt="*" /> <a href="new.php?url">Добавить ссылку</a></div>';
}
include_once '../../sys/inc/tfoot.php';
