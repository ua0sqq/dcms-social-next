<?php
include_once '../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

$set['title']='Чат - Кто здесь?'; // заголовок страницы
include_once H . 'sys/inc/thead.php';
title();
aut();

$k_post=$db->query(
                   'SELECT COUNT(*) FROM `user` WHERE `date_last`>?i AND `url` LIKE "?e%"',
                   [(time()-100), '/chat/'])->el();
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
$q = $db->query(
                'SELECT * FROM `user` WHERE `date_last`>?i AND `url` LIKE "?e%" ORDER BY `date_last` DESC LIMIT ?i OFFSET ?i',
                [(time()-100), '/chat/', $set['p_str'], $start]);
echo "<table class='post'>\n";
if ($k_post==0) {
    echo "   <tr>\n";
    echo "  <td class='p_t'>\n";
    echo "Нет никого\n";
    echo "  </td>\n";
    echo "   </tr>\n";
}
while ($chat = $q->row()) {
    echo "   <tr>\n";
    if ($set['set_show_icon']==2) {
        echo "  <td class='icon48' rowspan='2'>\n";
        avatar($chat['id']);
        echo "  </td>\n";
    } elseif ($set['set_show_icon']==1) {
        echo "  <td class='icon14'>\n";
        echo "".status($chat['id'])."";
        echo "  </td>\n";
    }
    echo "  <td class='p_t'>\n";
    echo "<a href='/info.php?id=$chat[id]'>$chat[nick]</a>\n";
    echo "  ".medal($chat['id'])." ".online($chat['id'])."\n";
    echo "  </td>\n";
    echo "   </tr>\n";
}
echo "</table>\n";
if ($k_page>1) {
    str("?", $k_page, $page);
}

include_once H . 'sys/inc/tfoot.php';
