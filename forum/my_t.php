<?php
include_once '../sys/inc/start.php';
include_once '../sys/inc/compress.php';
include_once '../sys/inc/sess.php';
include_once '../sys/inc/home.php';
include_once '../sys/inc/settings.php';
include_once '../sys/inc/db_connect.php';
include_once '../sys/inc/ipua.php';
include_once '../sys/inc/fnc.php';
include_once '../sys/inc/user.php';
/* Бан пользователя */ 
if ($db->query("SELECT COUNT(*) FROM `ban` WHERE `razdel` = 'forum' AND `id_user` = '$user[id]' AND (`time` > '$time' OR `view` = '0' OR `navsegda` = '1')")!=0)
{
	header('Location: /ban.php?'.SID);
	exit;
}
// Заголовок страницы
$set['title']='Форум - Мои темы';
include_once '../sys/inc/thead.php';
title();
aut(); 
echo '<div class="foot">';
echo '<img src="/style/icons/str2.gif" /> <a href="/forum/">Форум</a> | <b>Мои темы</b>';
echo '</div>';
$k_post = $db->query("SELECT COUNT(*) FROM `forum_t`  WHERE `id_user` = '$user[id]'");
$k_page = k_page($k_post,$set['p_str']);
$page = page($k_page);
$start = $set['p_str']*$page-$set['p_str'];
echo '<table class="post">';
$q = $db->query("SELECT * FROM `forum_t` WHERE `id_user` = '$user[id]' ORDER BY `time` DESC  LIMIT $start, $set[p_str]");
// Если список пуст
if ($k_post == 0) 
{
	echo '<div class="mess">';
	echo 'Ваших тем нет в форуме';
	echo '</div>';
}
while ($them = $q->row())
{
	// Определение подфорума
	$forum = $db->query("SELECT * FROM `forum_f` WHERE `id` = '$them[id_forum]' LIMIT 1")->row();
	
	// Определение раздела
	$razdel = $db->query("SELECT * FROM `forum_r` WHERE `id` = '$them[id_razdel]' LIMIT 1")->row();
	
	// Лесенка дивов
	if ($num == 0)
	{
		echo '<div class="nav1">';
		$num = 1;
	}
	elseif ($num == 1)
	{
		echo '<div class="nav2">';
		$num = 0;
	}
	
	// Иконка темы
	echo '<img src="/style/themes/' . $set['set_them'] . '/forum/14/them_' . $them['up'] . $them['close'] . '.png" alt="" /> ';
	
	// Ссылка на тему
	echo '<a href="/forum/' . $forum['id'] . '/' . $razdel['id'] . '/' . $them['id'] . '/">' . text($them['name']) . '</a> 
	<a href="/forum/' . $forum['id'] . '/' . $razdel['id'] . '/' . $them['id'] . '/?page=' . $pageEnd . '">
	(' . $db->query("SELECT COUNT(*) FROM `forum_p` WHERE `id_forum` = '$forum[id]' AND `id_razdel` = '$razdel[id]' AND `id_them` = '$them[id]'") . ')</a><br/>';
	
	// Подфорум и раздел
	echo '<a href="/forum/' . $forum['id'] . '/">' . text($forum['name']) . '</a> &gt; <a href="/forum/' . $forum['id'] . '/' . $razdel['id'] . '/">' . text($razdel['name']) . '</a><br />';
	
	// Последний пост 
	$post = $db->query("SELECT * FROM `forum_p` WHERE `id_them` = '$them[id]' AND `id_razdel` = '$razdel[id]' AND `id_forum` = '$forum[id]' ORDER BY `time` DESC LIMIT 1")->row();
	
	// Автор последнего поста
    if ($post['id'])  {
	$ank2 = $db->query("SELECT * FROM `user` WHERE `id` = $post[id_user] LIMIT 1")->row();
	if ($ank2['id'])echo 'Посл.: <a href="/info.php?id=' . $ank2['id'] . '">' . $ank2['nick'] . '</a> (' . vremja($post['time']) . ')<br />'; }
	
	echo '</div>';
}
echo '</table>';
// Вывод cтраниц 
if ($k_page>1)str("?",$k_page,$page); 
// Меню возврата
echo '<div class="foot">';
echo '<img src="/style/icons/str2.gif" /> <a href="/forum/">Форум</a> | <b>Мои темы</b>';
echo '</div>';
include_once '../sys/inc/tfoot.php';
?>