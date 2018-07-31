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
user_access('adm_panel_show',null,'/index.php?'.SID);
		   
if (isset($_SESSION['adm_auth']) && $_SESSION['adm_auth']>$time || isset($_SESSION['captcha']) && isset($_POST['chislo']) && $_SESSION['captcha']==$_POST['chislo'])
{
$_SESSION['adm_auth']=$time+600;
if (isset($_GET['go']) && $_GET['go']!=null)
{
header('Location: '.base64_decode($_GET['go']));exit;
}$set['title']='Админка';
include_once H . 'sys/inc/thead.php';
title();
err();
aut();
echo "<div class='mess'>\n";
echo "<center><b>DCMS-Social v.$set[dcms_version]</b></center>\n";echo "</div>\n";
if (user_access('adm_info'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a target='_blank' href='http://dcms-social.ru'>Форум поддержки</a></div>\n";
if (user_access('adm_info'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='info.php'>Общая информация</a></div>\n";
if (user_access('adm_statistic'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='statistic.php'>Статистика сайта</a></div>\n";
if (user_access('adm_show_adm'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='administration.php'>Администрация</a></div>\n";
if (user_access('adm_log_read'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='adm_log.php'>Действия администрации</a></div>\n";
if (user_access('adm_menu'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='menu.php'>Главное меню</a></div>\n";
if (user_access('adm_rekl'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='rekl.php'>Реклама</a></div>\n";
if (user_access('adm_news'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='/news/add.php'>Новости</a></div>\n";
if (user_access('adm_set_sys'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='settings_sys.php'>Настройки системы</a></div>\n";
if (user_access('adm_set_sys'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='settings_bbcode.php'>Настройки BBcode</a></div>\n";
if ($user['level'] > 3)echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='/user/gift/create.php'>Подарки</a></div>\n";
if ($user['level'] > 3)echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='smiles.php'>Смайлы</a></div>\n";
if (user_access('adm_set_forum'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='settings_forum.php'>Настройки форума</a></div>\n";
if (user_access('adm_set_user'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='settings_user.php'>Пользовательские настройки</a></div>\n";
if (user_access('adm_accesses'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='accesses.php'>Привилегии групп пользователей</a></div>\n";
if (user_access('adm_banlist'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='banlist.php'>Список забаненых</a></div>\n";
if (user_access('adm_set_loads'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='settings_loads.php'>Настройки загрузок</a></div>\n";
if (user_access('adm_set_chat'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='settings_chat.php'>Настройки чата</a></div>\n";
if (user_access('adm_set_foto'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='settings_foto.php'>Настройки фотогалереи</a></div>\n";
if (user_access('adm_forum_sinc'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='forum_sinc.php'>Синхронизация таблиц форума</a></div>\n";
if (user_access('adm_ref'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='referals.php'>Рефералы</a></div>\n";
if (user_access('adm_ip_edit'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='opsos.php'>Редактирование IP операторов</a></div>\n";
if (user_access('adm_ban_ip'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='ban_ip.php'>Бан по IP адресу (диапазону)</a></div>\n";
if (user_access('adm_mysql'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='mysql.php'>MySQL запросы</a></div>\n";
if (user_access('adm_mysql'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='tables.php'>Заливка таблиц</a></div>\n";
if (user_access('adm_themes'))echo "<div class='main'><img src='/style/icons/str.gif' alt=''/> <a href='themes.php'>Темы оформления</a></div>\n";
$opdirbase=@opendir(H.'sys/add/admin');
while ($filebase=@readdir($opdirbase))
if (preg_match('#\.php$#i',$filebase))
include_once(H.'sys/add/admin/'.$filebase);
closedir($opdirbase);
}
else
{
$set['title']='Защита от автоматических изменений';
include_once H . 'sys/inc/thead.php';
title();
err();
aut();
echo "<form method='post' action='?gen=$passgen&amp;".(isset($_GET['go'])?"go=$_GET[go]":null)."'>\n";
echo "<img src='/captcha.php?$passgen&amp;SESS=$sess' width='100' height='30' alt='Проверочное число' /><br />\nВведите число с картинки:<br //>\n<input name='chislo' size='5' maxlength='5' value='' type='text' /><br/>\n";
echo "<input type='submit' value='Далее' />\n";
echo "</form>\n";
}
include_once H . 'sys/inc/tfoot.php';
?>