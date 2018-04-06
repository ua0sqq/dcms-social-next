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

$set['title']='Добавили в закладки';
include_once '../../sys/inc/thead.php';
title();
aut();

if (!$id_notes = $db->query(
    "SELECT `id` FROM `notes` WHERE `id`=?i",
              [$_GET['id']])->el()) {
    echo '<div class="mess">'."\n".'Дневник не найден.'."\n".'</div>'."\n";
    include_once '../../sys/inc/tfoot.php';
    exit;
} else {
    $k_post=$db->query(
    "SELECT COUNT( * ) FROM `bookmarks` WHERE `id_object`=?i AND `type`=?",
                   [$id_notes, 'notes'])->el();
    $k_page=k_page($k_post, $set['p_str']);
    $page=page($k_page);
    $start=$set['p_str']*$page-$set['p_str'];
    if ($k_post==0) {
        echo "<div class='mess'>Никто в закладки не добавлял</div>";
    } else {
        $q=$db->query(
    "SELECT * FROM `bookmarks` WHERE `id_object`=?i AND `type`=? LIMIT ?i OFFSET ?i",
              [$id_notes, 'notes', $set['p_str'], $start]);
        while ($post=$q->row()) {
            echo "<div class='nav2'>";
            echo group($post['id_user'])." ";
            echo user::nick($post['id_user'], 1, 1, 1)." ";
            echo "Добавлено ".vremja($post['time'])."</div>";
        }
        if ($k_page > 1) {
            str('?id='.$id_notes.'&amp;', $k_page, $page);
        } // Вывод страниц
    }
    include_once '../../sys/inc/tfoot.php';
}
