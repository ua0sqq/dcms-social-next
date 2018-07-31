<?php
require 'sys/inc/start.php';
require 'sys/inc/sess.php';
require 'sys/inc/settings.php';
require 'sys/inc/db_connect.php';
require 'sys/inc/ipua.php';
require 'sys/inc/fnc.php';
require 'sys/inc/user.php';
require 'sys/inc/icons.php'; // Иконки главного меню

require 'sys/inc/thead.php';
title();
err();

if (!$set['web']) {
    $cnt = $db->query(
                    'SELECT * FROM (
		SELECT COUNT( * ) online_user FROM `user` WHERE `date_last` > ?i)q, (
		SELECT COUNT( * ) online_guest FROM `guests` WHERE `date_last` > ?i AND `pereh` >0)q2',
                            [TIME_600,TIME_600])->row(); ?>
	<div class="title">
	<center>
	<a href="/online.php" title="онлайн" style="color:#cdcecf; text-decoration: none">
	<font color="#fee300" size="2">Онлайн </font>
	<font color="#ffffff"><?= $cnt['online_user']; ?></font>
	</a>
	<font color="#fee300" size="2"> (</font>
	<font color="#ffffff">+<?= $cnt['online_guest']; ?></font>
	<font color="#fee300" size="2"> гостей )</font>
	</center>
	</div>
	<div class='main_menu'>
	<?php
    if (isset($user)) {
        ?>
		<div align="right">
		<img src="/style/icons/icon_stranica.gif" alt="DS" />
		<?=user::nick($user['id'])?> | <a href="exit.php"><font color="#ff0000">Выход</font></a>
		</div>
		<?php
    } else {
        ?>
		<div align="right">
		<a href="/aut.php">Вход</a> | <a href="/reg.php">Регистрация</a>
		</div>
		<?php
    } ?></div><?php
    // новости
    require 'sys/inc/news_main.php';
    // главное меню
    require 'sys/inc/main_menu.php';
    require 'sys/inc/main_notes.php';
} else {
    // главная web темы
    require 'style/themes/' . $set['set_them'] . '/index.php';
}
require 'sys/inc/tfoot.php';
?>
