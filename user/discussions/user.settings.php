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

if (isset($user)) {
    $ank['id'] = $user['id'];
}
if (isset($_GET['id'])) {
    $ank['id'] = intval($_GET['id']);
}
$ank = get_user($ank['id']);
if (!$ank || $ank['id'] == 0) {
    header('Location: /index.php?' . SID);
    exit;
}

$frend = $db->query(
                    'SELECT * FROM `frends` WHERE `user`=?i AND `frend`=?i AND `i`=?i',
                            [$user['id'], $ank['id'], 1])->row();

if (!isset($frend['user'])) {
    header('Location: index.php?' . SID);
    exit;
}

if (isset($_POST['save'])) {
    $disc = [];
    // Обсуждения фото
    if (isset($_POST['disc_foto']) && ($_POST['disc_foto'] == 0 || $_POST['disc_foto'] == 1)) {
        $disc += ['disc_foto' => (int) $_POST['disc_foto']];
    }
    // Обсуждения файлов
    if (isset($_POST['disc_obmen']) && ($_POST['disc_obmen'] == 0 || $_POST['disc_obmen'] == 1)) {
        $disc += ['disc_obmen' => (int) $_POST['disc_obmen']];
    }
    // Обсуждения статусов
    if (isset($_POST['disc_status']) && ($_POST['disc_status'] == 0 || $_POST['disc_status'] == 1)) {
        $disc += ['disc_status' => (int) $_POST['disc_status']];
    }
    // Обсуждения дневников
    if (isset($_POST['disc_notes']) && ($_POST['disc_notes'] == 0 || $_POST['disc_notes'] == 1)) {
        $disc += ['disc_notes' => (int) $_POST['disc_notes']];
    }
    // Обсуждения форум
    if (isset($_POST['disc_forum']) && ($_POST['disc_forum'] == 0 || $_POST['disc_forum'] == 1)) {
        $disc += ['disc_forum' => (int) $_POST['disc_forum']];
    }
    
    $table = $db->getTable('frends');
    $table->update($disc, ['user' => (int) $user['id'], 'frend' => (int) $ank['id']]);
    
    $_SESSION['message'] = __('Изменения успешно приняты');
    header('Location: user.settings.php?id=' . $ank['id']);
    exit;
}
$set['title'] = __('Настройка ленты для ') . $ank['nick'];
include_once '../../sys/inc/thead.php';
title();
err();
aut();
?>
<div id="comments" class="menus">
<div class="webmenu">
<a href="index.php"><?= __('Обсуждения')?></a>
</div> 
<div class="webmenu">
<a href="settings.php"><?= __('Настройки')?></a>
</div> 
</div>
<form action="?id=<?= $ank['id']?>" method="post">
	<div class="mess">
	<?= __('Уведомления о обсуждениях в дневниках')?> <?= $ank['nick']?>.
	</div>
	<div class="nav1">
	<input name="disc_notes" type="radio" <?= ($frend['disc_notes'] == 1 ? ' checked="checked"' : null)?> value="1" /> <?= __('Да')?> 
	<input name="disc_notes" type="radio" <?= ($frend['disc_notes'] == 0 ? ' checked="checked"' : null)?> value="0" /> <?= __('Нет')?> 
	</div>
	<div class="mess">
	<?= __('Уведомления о обсуждениях в темах')?> <?= $ank['nick']?> <?= __('в форуме')?>.
	</div>
	<div class="nav1">
	<input name="disc_forum" type="radio" <?= ($frend['disc_forum'] == 1 ? ' checked="checked"' : null)?> value="1" /> <?= __('Да')?> 
	<input name="disc_forum" type="radio" <?= ($frend['disc_forum'] == 0 ? ' checked="checked"' : null)?> value="0" /> <?= __('Нет')?> 
	</div>
	<div class="mess">
	<?= __('Уведомления о обсуждениях в фото')?> <?= $ank['nick']?>.
	</div>
	<div class="nav1">
	<input name="disc_foto" type="radio" <?= ($frend['disc_foto'] == 1 ? ' checked="checked"' : null)?> value="1" /> <?= __('Да')?> 
	<input name="disc_foto" type="radio" <?= ($frend['disc_foto'] == 0 ? ' checked="checked"' : null)?> value="0" /> <?= __('Нет')?> 
	</div>
	<div class="mess">
	<?= __('Уведомления о обсуждениях в файлах')?> <?= $ank['nick']?>.
	</div>
	<div class="nav1">
	<input name="disc_obmen" type="radio" <?= ($frend['disc_obmen'] == 1 ? ' checked="checked"' : null)?> value="1" /> <?= __('Да')?> 
	<input name="disc_obmen" type="radio" <?= ($frend['disc_obmen'] == 0 ? ' checked="checked"' : null)?> value="0" /> <?= __('Нет')?> 
	</div>
	<div class="mess">
	<?= __('Уведомления о обсуждениях в статусах')?> <?= $ank['nick']?>.
	</div>
	<div class="nav1">
	<input name="disc_status" type="radio" <?= ($frend['disc_status'] == 1 ? ' checked="checked"' : null)?> value="1" /> <?= __('Да')?> 
	<input name="disc_status" type="radio" <?= ($frend['disc_status'] == 0 ? ' checked="checked"' : null)?> value="0" /> <?= __('Нет')?> 
	</div>
	<div class="main">
	<input type="submit" name="save" value="<?= __('Сохранить')?>" />
	</div>
</form>
<?php

include_once '../../sys/inc/tfoot.php';
