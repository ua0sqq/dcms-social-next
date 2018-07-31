<?php
include_once '../../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

$set['title']='Раздел администрации'; // заголовок страницы
include_once H . 'sys/inc/thead.php';
title();
aut();

if (user_access('adm_panel_show')) {
    echo "<div class='main'>\n";
    echo "<img src='/style/icons/spam.gif' alt='S' /> <a href='spam'>Жалобы</a>\n";
    include_once "spam/count.php";
    echo "</div>\n";
    echo "<div class='main'>\n";
    echo "<img src='/style/icons/chat.gif' alt='S' /> <a href='chat'>Чат</a>\n";
    include_once "chat/count.php";
    echo "</div>\n";
    echo "<div class='main'>\n";
    echo "<img src='/style/icons/settings.png' alt='S' /> <a href='/adm_panel/'>Админка</a>\n";
    echo "</div>";
}
include_once H . 'sys/inc/tfoot.php';
