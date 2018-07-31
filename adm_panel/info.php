<?php
include_once '../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/adm_check.php';
include_once H . 'sys/inc/user.php';
user_access('adm_info',null,'index.php?'.SID);
adm_check();
$set['title']='Общая информация';
include_once H . 'sys/inc/thead.php';
title();
err();
aut();
include_once H.'sys/inc/testing.php';
echo "<hr />\n";
include_once H.'sys/inc/chmod_test.php';
if (isset($err))
{
if (is_array($err))
{
foreach ($err as $key=>$value) {
echo "<div class='err'>$value</div>\n";
}
}
else
echo "<div class='err'>$err</div>\n";
}
if (user_access('adm_panel_show')){
echo "<div class='foot'>\n";
echo "&laquo;<a href='/adm_panel/'>В админку</a><br />\n";
echo "</div>\n";
}
include_once H . 'sys/inc/tfoot.php';
?>