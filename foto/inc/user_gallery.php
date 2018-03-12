<?php
if (!isset($user) && !isset($_GET['id_user'])){ header("Location: /foto/?".SID);exit; }
if (isset($user))$ank['id'] = $user['id'];
if (isset($_GET['id_user']))$ank['id'] = intval($_GET['id_user']);
// Автор альбома
$ank = get_user($ank['id']);
if (!$ank){header("Location: /foto/?".SID);exit;}
// Если вы в бане 
if ($db->query("SELECT COUNT(*) FROM `ban` WHERE `razdel` = 'foto' AND `id_user` = '$user[id]' AND (`time` > '$time' OR `view` = '0' OR `navsegda` = '1')")->el())
{
	header('Location: /ban.php?'.SID);
	exit;
}
// заголовок страницы
$set['title'] = user::nick($ank['id'], 0) . ' - Фотоальбомы'; 
// Это при создании нового альбома
include 'inc/gallery_act.php';
include_once '../sys/inc/thead.php';
title();
aut();
err();
// Создание альбомов
include 'inc/gallery_form.php'; 
echo '<div class="foot">';
echo '<img src="/style/icons/str2.gif" alt="*"> ' . user::nick($ank['id']) . ' | <b>Альбомы</b></div>';
if ($ank['id'] == $user['id'])
echo '<div class="mess"><a href="/foto/' . $ank['id'] . '/?act=create"><img src="/style/icons/apply14.png"> Новый альбом</a></div>';
// Подключаем приватность стр. 
include H.'sys/add/user.privace.php';
$k_post = $db->query("SELECT COUNT(*) FROM `gallery` WHERE `id_user` = '$ank[id]'")->el();
$k_page = k_page($k_post,$set['p_str']);
$page = page($k_page);
$start = $set['p_str']*$page-$set['p_str'];
echo '<table class="post">';
if ($k_post == 0)
{
	echo '<div class="mess">';
	echo 'Фотоальбомов нет';
	echo '</div>';
}
$q = $db->query("SELECT * FROM `gallery` WHERE `id_user` = '$ank[id]' ORDER BY `time` DESC LIMIT $start, $set[p_str]");
while ($post = $q->row())
{
	// Лесенка
	echo '<div class="' . ($num % 2 ? "nav1" : "nav2") . '">';
	$num++;
	
	// Cчетчик фотографий
	$count = $db->query("SELECT COUNT(*) FROM `gallery_foto` WHERE `id_gallery` = '$post[id]'")->el();
	
	echo '<img src="/style/themes/' . $set['set_them'] . '/loads/14/' . ($post['pass'] != null || $post['privat'] != 0 ? 'lock.gif' : 'dir.png') . '" alt="*" /> ';
	
	echo '<a href="/foto/' . $ank['id'] . '/' . $post['id'] . '/">' . text($post['name']) . '</a> (' . $count . ' фото) ';
	if (isset($user) && (user_access('foto_alb_del') || $user['id'] == $ank['id']))
	{
		echo '[<a href="/foto/' . $ank['id'] . '/' . $post['id'] . '/?edit=rename"><img src="/style/icons/edit.gif" alt="*" /> ред</a>] ';
		echo '[<a href="/foto/' . $ank['id'] . '/' . $post['id'] . '/?act=delete"><img src="/style/icons/delete.gif" alt="*" /> удл</a>]';
	}
	
	echo '<br />';
	
	if ($post['opis'] == null)
		echo 'Без описания<br />';
	else 
		echo '<div class="text">' . output_text($post['opis']) . '</div>';
	echo 'Создан: ' . vremja($post['time_create']);
	
	echo '</div>';
}
echo '</table>';
// Вывод страниц
if ($k_page > 1)str('?', $k_page, $page); 
echo '<div class="foot">';
echo '<img src="/style/icons/str2.gif" alt="*"> ' . user::nick($ank['id']) . ' | <b>Альбомы</b>';
echo '</div>';
include_once '../sys/inc/tfoot.php';
exit;
?>