<?php

include_once '../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

$set['title']='Кто на форуме?'; // заголовок страницы
include_once H . 'sys/inc/thead.php';
title();
aut();

$k_post=$db->query(
    'SELECT COUNT(*) FROM `user` WHERE `date_last`>?i AND `url` LIKE "?e%"',
                   [TIME_600, '/forum/'])->el();
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
$q = $db->query(
    'SELECT * FROM `user` WHERE `date_last`>?i AND `url` LIKE "?e%" ORDER BY `date_last` DESC LIMIT ?i OFFSET ?i',
                [TIME_600, '/forum/', $set['p_str'], $start]);

if (!$k_post) {
    echo "<div class='mess'>\n";
    echo "Нет никого\n";
    echo "</div>\n";
}
while ($forum = $q->row()) {
    echo "\n".'<div class="' . ($num % 2 ? "nav1" : "nav2") . '">'."\n";
    $num++;
    echo avatar($forum['id']) . group($forum['id']);
    echo " <a href='/info.php?id=$forum[id]'>$forum[nick]</a>\n";
    echo " ".medal($forum['id'])."  ".online($forum['id'])."\n";
    echo "</div>\n";
}

if ($k_page>1) {
    str("?", $k_page, $page);
} // Вывод страниц
echo "<div class='foot'>\n&laquo;<a href='/forum/'>Назад в форум</a>\n</div>\n";
      
include_once H . 'sys/inc/tfoot.php';
