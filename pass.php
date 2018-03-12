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
$show_all=true; // показ для всех
include_once 'sys/inc/user.php';
only_unreg();
$set['title']='Восстановление пароля';
include_once 'sys/inc/thead.php';
title();
if (isset($_POST['nick']) && isset($_POST['mail']) && $_POST['nick']!=NULL && $_POST['mail']!=NULL)
{
if ($db->query("SELECT COUNT(*) FROM `user` WHERE `nick` = '".my_esc($_POST['nick'])."'")==0)
{
$err = "Пользователь с таким логином не зарегистрирован";
}
elseif ($db->query("SELECT COUNT(*) FROM `user` WHERE `nick` = '".my_esc($_POST['nick'])."' AND `ank_mail` = '".my_esc($_POST['mail'])."'")==0)
{
$err ='Неверный адрес E-mail или информация о E-mail отсутствует';
}
else 
{
$q = $db->query("SELECT * FROM `user` WHERE `nick` = '".my_esc($_POST['nick'])."' LIMIT 1");
$user2 = $q->row();
$new_sess=substr(md5(passgen()), 0, 20);
$subject = "Восстановление пароля";
$regmail = "Здравствуйте $user2[nick]<br />
Вы активировали восстановление пароля<br />
Для установки нового пароля перейдите по ссылке:<br />
<a href='http://$_SERVER[HTTP_HOST]/pass.php?id=$user2[id]&amp;set_new=$new_sess'>http://$_SERVER[HTTP_HOST]/pass.php?id=$user2[id]&amp;set_new=$new_sess</a><br />
Данная ссылка действительна до первой авторизации под своим логином ($user2[nick])<br />
С уважением, администрация сайта<br />
";
$adds="From: \"password@$_SERVER[HTTP_HOST]\" <password@$_SERVER[HTTP_HOST]>\n";
//$adds = "From: <$set[reg_mail]>\n";
//$adds .= "X-sender: <$set[reg_mail]>\n";
$adds .= "Content-Type: text/html; charset=utf-8\n";
mail($user2['ank_mail'],'=?utf-8?B?'.base64_encode($subject).'?=',$regmail,$adds);
$db->query("UPDATE `user` SET `sess` = '$new_sess' WHERE `id` = '$user2[id]' LIMIT 1");
msg("Ссылка для установки нового пароля отправлена на e-mail \"$user2[ank_mail]\"");
}
}
if (isset($_GET['id']) && isset($_GET['set_new']) && strlen($_GET['set_new'])==20 && 
$db->query("SELECT COUNT(*) FROM `user` WHERE `id` = '".intval($_GET['id'])."' AND `sess` = '".my_esc($_GET['set_new'])."'")==1)
{
$q = $db->query("SELECT * FROM `user` WHERE `id` = '".intval($_GET['id'])."' LIMIT 1");
$user2 = $q->row();
if (isset($_POST['pass1']) && isset($_POST['pass2']))
{
if ($_POST['pass1']==$_POST['pass2'])
{
if (strlen2($_POST['pass1'])<6)$err='По соображениям безопасности новый пароль не может быть короче 6-ти символов';
if (strlen2($_POST['pass1'])>32)$err='Длина пароля превышает 32 символа';
}
else $err='Новый пароль не совпадает с подтверждением';
if (!isset($err)){
setcookie('id_user', $user2['id'], time()+60*60*24*365);
$db->query("UPDATE `user` SET `pass` = '".shif($_POST['pass1'])."' WHERE `id` = '$user2[id]' LIMIT 1");
setcookie('pass', cookie_encrypt($_POST['pass1'],$user2['id']), time()+60*60*24*365);
msg('Пароль успешно изменен');
}
}
err();
aut();
echo "<form action='/pass.php?id=$user2[id]&amp;set_new=".esc($_GET['set_new'],1)."&amp;$passgen' method=\"post\">\n";
echo "Логин:<br />\n";
echo "<input type=\"text\" disabled='disabled' value='$user2[nick]' maxlength=\"32\" size=\"16\" /><br />\n";
echo "Новый пароль:<br />\n<input type='password' name='pass1' value='' /><br />\n";
echo "Подтверждение:<br />\n<input type='password' name='pass2' value='' /><br />\n";
echo "<input type='submit' name='save' value='Изменить' />\n";
echo "</form>\n";
}
else
{
err();
aut();
echo "<form action=\"?$passgen\" method=\"post\">\n";
echo "Логин:<br />\n";
echo "<input type=\"text\" name=\"nick\" title=\"Логин\" value=\"\" maxlength=\"32\" size=\"16\" /><br />\n";
echo "E-mail:<br />\n";
echo "<input type=\"text\" name=\"mail\" title=\"E-mail\" value=\"\" maxlength=\"32\" size=\"16\" /><br />\n";
echo "<input type=\"submit\" value=\"Далее\" title=\"Далее\" />";
echo "</form>\n";
echo "На ваш e-mail придет ссылка для установки нового пароля.<br />\n";
echo "Если у вас в анкете отсутствует запись о вашем e-mail, восстановление пароля невозможно.<br />\n";
}
?>
	<div class='foot'>
	Еще не заригистрированы? <br />
	<a href='/reg.php'>Регистрация</a><br />
	</div>
	<div class='foot'>
	Уже заригистрированы? <br />
	<a href='/aut.php'>Авторизация</a><br />
	</div>
<?
include_once 'sys/inc/tfoot.php';
?>