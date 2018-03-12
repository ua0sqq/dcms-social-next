<?php
include_once 'sys/inc/start.php';
include_once 'sys/inc/compress.php';
include_once 'sys/inc/sess.php';
include_once 'sys/inc/home.php';
include_once 'sys/inc/settings.php';
include_once 'sys/inc/db_connect.php';
include_once 'sys/inc/ipua.php';
include_once 'sys/inc/fnc.php';
include_once 'sys/inc/shif.php';
include_once 'sys/inc/user.php';
only_reg();
$set['title']='Безопасность';
include_once 'sys/inc/thead.php';
title();
if (isset($_POST['save'])){
if (isset($_POST['pass']) && $db->query("SELECT COUNT(*) FROM `user` WHERE `id` = $user[id] AND `pass` = '".shif($_POST['pass'])."' LIMIT 1")==1)
{
if (isset($_POST['pass1']) && isset($_POST['pass2']))
{
if ($_POST['pass1']==$_POST['pass2'])
{
if (strlen2($_POST['pass1'])<6)$err='По соображениям безопасности новый пароль не может быть короче 6-ти символов';
if (strlen2($_POST['pass1'])>32)$err='Длина пароля превышает 32 символа';
}
else $err='Новый пароль не совпадает с подтверждением';
}
else $err='Введите новый пароль';
}
else $err='Старый пароль неверен';
if (!isset($err))
{
$db->query("UPDATE `user` SET `pass` = '".shif($_POST['pass1'])."' WHERE `id` = '$user[id]' LIMIT 1");
setcookie('pass', cookie_encrypt($_POST['pass1'],$user['id']), time()+60*60*24*365);
msg('Пароль успешно изменен');
}
}
err();
aut();
echo "<form method='post' action='?$passgen'>\n";
echo "Старый пароль:<br />\n<input type='text' name='pass' value='' /><br />\n";
echo "Новый пароль:<br />\n<input type='password' name='pass1' value='' /><br />\n";
echo "Подтверждение:<br />\n<input type='password' name='pass2' value='' /><br />\n";
echo "<input type='submit' name='save' value='Изменить' />\n";
echo "</form>\n";
include_once 'sys/inc/tfoot.php';
?>
