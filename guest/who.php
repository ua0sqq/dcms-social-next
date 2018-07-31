<?php
include_once '../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

$set['title'] = 'Гостевая - Кто здесь?'; // заголовок страницы
include_once H . 'sys/inc/thead.php';
title();
aut();

$k_post = $db->query(
    'SELECT COUNT(*) FROM `user` WHERE `date_last`>?i AND `url` like "?e%"',
                     [(time()-100), '/guest/']
)->el();
$k_page = k_page($k_post, $set['p_str']);
$page = page($k_page);
$start = $set['p_str']*$page-$set['p_str'];
 
echo '<div class="foot">';
echo '<img src="/style/icons/str2.gif" /> <a href="index.php">Гостевая</a> | <b>Кто в гостевой?</b>';
echo '</div>';
echo '<table class="post">';
if ($k_post == 0) {
    echo '<div class="mess" id="no_object">';
    echo 'Здесь никого нет';
    echo '</div>';
}
$q = $db->query(
    'SELECT id FROM `user` WHERE `date_last`>?i AND `url` LIKE "?e%" ORDER BY `date_last` DESC LIMIT ?i OFFSET ?i',
                [(time()-100), '/guest/', $set['p_str'], $start]
);
while ($ank = $q->row()) {
    // Лесенка
    echo '<div class="' . ($num % 2 ? "nav1" : "nav2") . '">';
    $num++;
    echo user::avatar($ank['id'], 0) . user::nick($ank['id'], 1, 1, 1) . '<br />';
    
    echo '</div>';
}
echo '</table>';
echo '<div class="foot">';
echo '<img src="/style/icons/str2.gif" /> <a href="index.php">Гостевая</a> | <b>Кто в гостевой?</b>';
echo '</div>';

if ($k_page > 1) {
    str('/chat/who.php?', $k_page, $page); // Вывод страниц
}

include_once H . 'sys/inc/tfoot.php';
