<?php
include_once 'sys/inc/start.php';
include_once 'sys/inc/compress.php';
include_once 'sys/inc/sess.php';
include_once 'sys/inc/settings.php';
include_once 'sys/inc/db_connect.php';
include_once 'sys/inc/ipua.php';
include_once 'sys/inc/fnc.php';
$show_all=true; // показ для всех
include_once 'sys/inc/user.php';

only_unreg();

if (isset($_GET['pass']) && $_GET['pass']='ok') {
    $_SESSION['message'] = 'Пароль отправлен вам на E-mail';
}
if ($set['guest_select']=='1') {
    $_SESSION['message'] = "Доступ к сайту разрешен только авторизованым пользователям";
}
$set['title']='Авторизация';
include_once 'sys/inc/thead.php';
title();
aut();
if ((!isset($_SESSION['refer']) || $_SESSION['refer']==null)
&& isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']!=null &&
!preg_match('#mail\.php#', $_SERVER['HTTP_REFERER'])) {
    $_SESSION['refer']=str_replace('&', '&amp;', preg_replace('#^http://[^/]*/#', '/', $_SERVER['HTTP_REFERER']));
}
?>
<form class='mess' method='post' action='/login.php'>
Логин:<br /><input type='text' name='nick' maxlength='32' /><br />
Пароль:<br /><input type='password' name='pass' maxlength='32' /><br />
<label><input type='checkbox' name='aut_save' value='1' /> Запомнить меня</label><br />
<input type='submit' value='Войти' />
</form>					
<div class='foot'>Еще не заригистрированы? <br /><a href='/reg.php'>Регистрация</a><br /></div>
<div class='foot'>Забыли пароль? <br /><a href='/pass.php'>Восстановление пароля</a><br /></div>
<?php
include_once 'sys/inc/tfoot.php';
?>