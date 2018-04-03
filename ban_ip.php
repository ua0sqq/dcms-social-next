<?php
include_once 'sys/inc/start.php';
include_once 'sys/inc/compress.php';
include_once 'sys/inc/sess.php';
include_once 'sys/inc/home.php';
include_once 'sys/inc/settings.php';
include_once 'sys/inc/db_connect.php';
include_once 'sys/inc/ipua.php';
$ban_ip_page=true; // чтобы небыло зацикливания
include_once 'sys/inc/fnc.php';
//include_once 'sys/inc/user.php';
$set['title']='Бан по IP';
include_once 'sys/inc/thead.php';
title();
$err="<h1>Доступ с Вашего IP ($_SERVER[REMOTE_ADDR]) заблокирован</h1>";
err();
//aut();
?>
<h2>Возможные причины:</h2>
1) Частые обращения к серверу с одного IP адреса<br />
2) Ваш IP адрес совпадает с адресом нарушителя<br />
<h2>Способы решения:</h2>
1) Перезапустить подключение к интернету<br />
2) В случае статического IP адреса можно воспользоваться прокси-сервером
<br />
<?php
include_once 'sys/inc/tfoot.php';
?>