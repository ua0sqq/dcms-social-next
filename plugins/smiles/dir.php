<?php
/**
 * & CMS Name :: DCMS-Social
 * & Author   :: Alexandr Andrushkin
 * & Contacts :: ICQ 587863132
 * & Site     :: http://dcms-social.ru
 */
include_once '../../sys/inc/home.php';
include_once H.'sys/inc/start.php';
include_once H.'sys/inc/compress.php';
include_once H.'sys/inc/sess.php';
include_once H.'sys/inc/settings.php';
include_once H.'sys/inc/db_connect.php';
include_once H.'sys/inc/ipua.php';
include_once H.'sys/inc/fnc.php';
include_once H.'sys/inc/user.php';

$id = isset($_GET['id']) ? abs(intval($_GET['id'])) : 0;

if ($id < 1) {
    header("Location: /index.php");
	exit;
}
$dir = $db->query("SELECT * FROM `smile_dir` WHERE `id` = '" . $id . "'")->row();
if (!$dir['id']) {
    header("Location: /index.php");
}
$set['title'] = text($dir['name']) . ' | Список смайлов';
include_once H.'sys/inc/thead.php';
title();
aut();
?>
<div class="foot">
<img src="/style/icons/str2.gif" alt="*"> <a href="index.php">Категории</a> | <b><?=text($dir['name'])?></b>
</div>
<?php

$k_post = $db->query("SELECT COUNT(*) FROM `smile` WHERE `dir` = '$id'")->el();
$k_page = k_page($k_post, $set['p_str']);
$page = page($k_page);
$start = $set['p_str']*$page-$set['p_str'];
?><table class="post"><?php
if ($k_post == 0) {
    ?><div class="mess">Список смайлов пуст</div><?php
}
$q = $db->query("SELECT * FROM `smile` WHERE `dir` = '$id' ORDER BY `id` ASC LIMIT $start, $set[p_str]");
while ($post = $q->row()) {
    $post['name'] = isset($post['name']) ? $post['name'] : $post['smile'];
    // Лесенка
    echo '<div class="' . ($num % 2 ? "nav1" : "nav2") . '">';
    $num++; ?>
	<img src="/style/smiles/<?=$post['id']?>.gif" alt="<?=$post['name']?>"/> <?=text($post['smile'])?>
	</div>
	<?php
}
if ($k_page>1) {
    str('/plugins/smiles/dir.php?id='.$id.'&amp;', $k_page, $page);
}
if (isset($user) && $user['level'] > 3) {
    ?>
	<div class="foot">
	<img src="/style/icons/str.gif" alt="*"> <a href="/adm_panel/smiles.php">Админка</a>
	</div>
	<?php
}
?>
<div class="foot">
<img src="/style/icons/str2.gif" alt="*"> <a href="index.php">Категории</a> | <b><?=text($dir['name'])?></b>
</div>
<?php
include_once H.'sys/inc/tfoot.php';
?>