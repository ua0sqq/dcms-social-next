<?php
/*
=======================================
Статусы юзеров для Dcms-Social
Автор: Искатель
---------------------------------------
Этот скрипт распостроняется по лицензии
движка Dcms-Social. 
При использовании указывать ссылку на
оф. сайт http://dcms-social.ru
---------------------------------------
Контакты
ICQ: 587863132
http://dcms-social.ru
=======================================
*/
include_once '../../sys/inc/start.php';
include_once '../../sys/inc/compress.php';
include_once '../../sys/inc/sess.php';
include_once '../../sys/inc/home.php';
include_once '../../sys/inc/settings.php';
include_once '../../sys/inc/db_connect.php';
include_once '../../sys/inc/ipua.php';
include_once '../../sys/inc/fnc.php';
include_once '../../sys/inc/user.php';
if (isset($_GET['id']) && $db->query("SELECT COUNT(*) FROM `status` WHERE `id` = '".intval($_GET['id'])."'")==1)
{
$post=$db->query("SELECT * FROM `status` WHERE `id` = '".intval($_GET['id'])."' LIMIT 1")->row();
$ank=$db->query("SELECT * FROM `user` WHERE `id` = $post[id_user] LIMIT 1")->row();
if (isset($user) && ($user['level']>$ank['level']) || $post['id_user']==$user['id'])
$db->query("DELETE FROM `status` WHERE `id` = '$post[id]'");
$db->query("DELETE FROM `status_komm` WHERE `id_status` = '$post[id]'");
$db->query("DELETE FROM `status_like` WHERE `id_status` = '$post[id]'");
$_SESSION['message'] = 'Статус упешно удален';
header("Location: index.php?id=$ank[id]"); 
exit;
}
?>