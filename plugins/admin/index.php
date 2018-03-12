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
$set['title']='Раздел администрации'; // заголовок страницы
include_once '../../sys/inc/thead.php';
title();
aut(); // форма авторизации
if (user_access('adm_panel_show')){
echo "<div class='main'>\n";
echo "<img src='/style/icons/spam.gif' alt='S' /> <a href='spam'>Жалобы</a> ";
include_once "spam/count.php";
echo "</div>";
echo "<div class='main'>\n";
echo "<img src='/style/icons/chat.gif' alt='S' /> <a href='chat'>Чат</a> ";
include_once "chat/count.php";
echo "</div>";
if (user_access('adm_panel_show')){
echo "<div class='main_seriy'>\n";
echo "<div class='main'>\n";
echo "<img src='/style/icons/settings.png' alt='S' /> <a href='/adm_panel/'>Админка</a> ";
echo "</div>";
echo "</div>";
}
}
include_once '../../sys/inc/tfoot.php';
?>
