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
only_reg();
$discSet = $db->query("SELECT * FROM `discussions_set` WHERE `id_user` = '" . $user['id'] . "' LIMIT 1")->row();
if (isset($_POST['save']))
{
	// Обсуждения фото
	if (isset($_POST['disc_foto']) && ($_POST['disc_foto'] == 0 || $_POST['disc_foto'] == 1))
	{
		$disc = (int) $_POST['disc_foto'];
		$db->query("UPDATE `discussions_set` SET `disc_foto` = '" . $disc . "' WHERE `id_user` = '$user[id]'");
	}
	 
	// Обсуждения файлов
	if (isset($_POST['disc_files']) && ($_POST['disc_files'] == 0 || $_POST['disc_files'] == 1))
	{
		$disc = (int) $_POST['disc_files'];
		$db->query("UPDATE `discussions_set` SET `disc_files` = '" . $disc . "' WHERE `id_user` = '$user[id]'");
	}
	
	 // Обсуждения статусов
	if (isset($_POST['disc_status']) && ($_POST['disc_status'] == 0 || $_POST['disc_status'] == 1))
	{
		$disc = (int) $_POST['disc_status'];
		$db->query("UPDATE `discussions_set` SET `disc_status` = '" . $disc . "' WHERE `id_user` = '$user[id]'");
	}
	
	 // Обсуждения дневников
	if (isset($_POST['disc_notes']) && ($_POST['disc_notes'] == 0 || $_POST['disc_notes'] == 1))
	{
		$disc = (int) $_POST['disc_notes'];
		$db->query("UPDATE `discussions_set` SET `disc_notes` = '" . $disc . "' WHERE `id_user` = '$user[id]'");
	}
	
	 // Обсуждения форум
	if (isset($_POST['disc_forum']) && ($_POST['disc_forum'] == 0 || $_POST['disc_forum'] == 1))
	{
		$disc = (int) $_POST['disc_forum'];
		$db->query("UPDATE `discussions_set` SET `disc_forum` = '" . $disc . "' WHERE `id_user` = '$user[id]'");
	}
	$_SESSION['message'] = __('Изменения успешно приняты');
	header('Location: ?');
	exit;
}
$set['title'] = __('Настройка обсуждений');
include_once '../../sys/inc/thead.php';
title();
err();
aut();
?>
<div id="comments" class="menus">
	<div class="webmenu">
	<a href="/user/info/settings.php"><?= __('Общие')?></a>
	</div> 
	<div class="webmenu">
	<a href="/user/tape/settings.php"><?= __('Лента')?></a>
	</div> 
	<div class="webmenu">
	<a href="/user/discussions/settings.php" class="activ"><?= __('Обсуждения')?></a>
	</div> 
	<div class="webmenu">
	<a href="/user/notification/settings.php"><?= __('Уведомления')?></a>
	</div> 
	<div class="webmenu">
	<a href="/user/info/settings.privacy.php"><?= __('Приватность')?></a>
	</div> 
	<div class="webmenu">
	<a href="/user/info/secure.php"><?= __('Пароль')?></a>
	</div> 
</div>
<form action="?" method="post">
	<div class="mess">
	<?= __('Уведомления о обсуждениях в дневниках')?>.
	</div>
	<div class="nav1">
	<input name="disc_notes" type="radio" <?= ($discSet['disc_notes'] == 1 ? ' checked="checked"' : null)?> value="1" /> <?= __('Да')?> 
	<input name="disc_notes" type="radio" <?= ($discSet['disc_notes'] == 0 ? ' checked="checked"' : null)?> value="0" /> <?= __('Нет')?> 
	</div>
	<div class="mess">
	<?= __('Уведомления о обсуждениях в темах в форуме')?>.
	</div>
	<div class="nav1">
	<input name="disc_forum" type="radio" <?= ($discSet['disc_forum'] == 1 ? ' checked="checked"' : null)?> value="1" /> <?= __('Да')?> 
	<input name="disc_forum" type="radio" <?= ($discSet['disc_forum'] == 0 ? ' checked="checked"' : null)?> value="0" /> <?= __('Нет')?> 
	</div>
	<div class="mess">
	<?= __('Уведомления о обсуждениях в фото')?>.
	</div>
	<div class="nav1">
	<input name="disc_foto" type="radio" <?= ($discSet['disc_foto'] == 1 ? ' checked="checked"' : null)?> value="1" /> <?= __('Да')?> 
	<input name="disc_foto" type="radio" <?= ($discSet['disc_foto'] == 0 ? ' checked="checked"' : null)?> value="0" /> <?= __('Нет')?> 
	</div>
	<div class="mess">
	<?= __('Уведомления о обсуждениях в файлах')?>.
	</div>
	<div class="nav1">
	<input name="disc_files" type="radio" <?= ($discSet['disc_files'] == 1 ? ' checked="checked"' : null)?> value="1" /> <?= __('Да')?> 
	<input name="disc_files" type="radio" <?= ($discSet['disc_files'] == 0 ? ' checked="checked"' : null)?> value="0" /> <?= __('Нет')?> 
	</div>
	<div class="mess">
	<?= __('Уведомления о обсуждениях в статусах')?>.
	</div>
	<div class="nav1">
	<input name="disc_status" type="radio" <?= ($discSet['disc_status'] == 1 ? ' checked="checked"' : null)?> value="1" /> <?= __('Да')?> 
	<input name="disc_status" type="radio" <?= ($discSet['disc_status'] == 0 ? ' checked="checked"' : null)?> value="0" /> <?= __('Нет')?> 
	</div>
	<div class="main">
	<input type="submit" name="save" value="<?= __('Сохранить')?>" />
	</div>
</form>
<div class="foot">
<img src="/style/icons/str2.gif" alt="*"> <a href="/id<?= $user['id']?>"><?= $user['nick']?></a> | <b><?= __('Обсуждения')?></b>
</div>
<?php
include_once '../../sys/inc/tfoot.php';
?>