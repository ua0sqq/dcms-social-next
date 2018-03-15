<?php
include_once 'sys/inc/start.php';
include_once 'sys/inc/sess.php';
include_once 'sys/inc/home.php';
include_once 'sys/inc/settings.php';
include_once 'sys/inc/db_connect.php';
include_once 'sys/inc/ipua.php';
include_once 'sys/inc/fnc.php';
include_once 'sys/inc/user.php';
include_once 'sys/inc/icons.php'; // Иконки главного меню

include_once 'sys/inc/thead.php';
title();
err();

if (!$set['web'])
{
	?>
	<div class="title">
	<center>
	<a href="/online.php" title="онлайн" style="color:#cdcecf; text-decoration: none">
	<font color="#fee300" size="2">Онлайн </font>
	<font color="#ffffff"><?= $db->query("SELECT COUNT(*) FROM `user` WHERE `date_last` > ".(time()-600)."")->el()?></font>
	</a>
	<font color="#fee300" size="2"> (</font>
	<font color="#ffffff">+<?= $db->query("SELECT COUNT(*) FROM `guests` WHERE `date_last` > ".(time()-600)." AND `pereh` > '0'")->el()?></font>
	<font color="#fee300" size="2"> гостей )</font>
	</center>
	</div>
	<div class='main_menu'>
	<?php
	if (isset($user))
	{
		?>
		<div align="right">
		<img src="/style/icons/icon_stranica.gif" alt="DS" />
		<?=user::nick($user['id'])?> | <a href="exit.php"><font color="#ff0000">Выход</font></a>
		</div>
		<?php
	}
	else
	{
		?>
		<div align="right">
		<a href="/aut.php">Вход</a> | <a href="/reg.php">Регистрация</a>
		</div>
		<?php
	}
	?></div><?php
	// новости 
	include_once 'sys/inc/news_main.php'; 
	// главное меню
	include_once 'sys/inc/main_menu.php'; 
	include_once H.'sys/inc/main_notes.php';
}
else
{
	// главная web темы
	include_once 'style/themes/' . $set['set_them'] . '/index.php'; 
}
include_once 'sys/inc/tfoot.php';
?>