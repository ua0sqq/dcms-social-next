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
$set['title']='Администрация'; // заголовок страницы
include_once '../sys/inc/thead.php';
title();
aut();
$s = 0;
if (isset($_GET['adm']))
{
$gr = "`group_access` > '7' AND `group_access` < '16'";
} 
else
if (isset($_GET['mod']))
{
$gr = "`group_access` = '7'";
}
else
if (isset($_GET['zone']))
{
$gr = "`group_access` = '4'";
}
else
if (isset($_GET['forum']))
{
$gr = "`group_access` = '3'";
}
else
if (isset($_GET['chat']))
{
$gr = "`group_access` = '2'";
}
else
if (isset($_GET['notes']))
{
$gr = "`group_access` = '11'";
} 
else
if (isset($_GET['guest']))
{
$gr = "`group_access` = '12'";
} 
else 
{
$gr = "`group_access` > '1' AND `date_last` > '".(time()-600)."'";
$s = 1;
}
if (!isset($_GET['adm']) && !isset($_GET['mod']) && !isset($_GET['zone']) && !isset($_GET['forum']) && !isset($_GET['chat']) && !isset($_GET['notes'])  && !isset($_GET['guest']) )
{
echo "<div class = 'nav2'>";
echo "<img src='/style/icons/adm.gif' alt='S' /> <a href='?guest'>Модераторы гостевой</a>";
echo "</div>";
echo "<div class = 'nav1'>";
echo "<img src='/style/icons/adm.gif' alt='S' /> <a href='?notes'>Модераторы дневников</a>";
echo "</div>";
echo "<div class = 'nav2'>";
echo "<img src='/style/icons/adm.gif' alt='S' /> <a href='?chat'>Модераторы чата</a>";
echo "</div>";
echo "<div class = 'nav1'>";
echo "<img src='/style/icons/adm.gif' alt='S' /> <a href='?forum'>Модераторы форума</a>";
echo "</div>";
echo "<div class = 'nav2'>";
echo "<img src='/style/icons/adm.gif' alt='S' /> <a href='?zone'>Модераторы зоны обмена</a>";
echo "</div>";
echo "<div class = 'nav1'>";
echo "<img src='/style/icons/adm.gif' alt='S' /> <a href='?mod'>Модераторы</a>";
echo "</div>";
echo "<div class = 'nav1'>";
echo "<img src='/style/icons/adm.gif' alt='S' /> <a href='?adm'>Администраторы</a>";
echo "</div>";
}
if ($s == 1)
{
echo "<div class = 'foot'>";
echo "Онлайн администрация";
echo "</div>";
}
$k_post=$db->query("SELECT COUNT(*) FROM `user` WHERE $gr");
$k_page=k_page($k_post,$set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
$q = $db->query("SELECT * FROM `user` WHERE $gr ORDER BY `date_last` DESC LIMIT $start, $set[p_str]");
echo "<table class='post'>\n";
if ($k_post == 0)
{
	echo '<div class="mess">';
	echo 'Список пуст';
	echo '</div>';
}
while ($ank = $q->row())
{
$ank=get_user($ank['id']);
/*-----------зебра-----------*/ 
if ($num==0){
	echo '<div class="nav1">';
	$num=1;
}
elseif ($num==1){
	echo '<div class="nav2">';
	$num=0;
}
/*---------------------------*/
if ($set['set_show_icon']==2){
avatar($ank['id']);
}
elseif ($set['set_show_icon']==1)
{
echo "".status($ank['id'])."";
}
echo "<a href='/info.php?id=$ank[id]'>$ank[nick]</a>\n";
echo "".medal($ank['id'])." ".online($ank['id'])." <br />";
echo "$ank[group_name]";
if ($ank['id']!=$user['id']){
echo "<br /> <a href=\"/mail.php?id=$ank[id]\"><img src='/style/icons/pochta.gif' alt='*' /> Сообщение</a> \n";
}
echo "</div>\n";
}
echo "</table>\n";
if ($k_page>1)str("?",$k_page,$page); // Вывод страниц
include_once '../sys/inc/tfoot.php';
?>