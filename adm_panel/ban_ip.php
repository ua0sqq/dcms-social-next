<?php
include_once '../sys/inc/start.php';
include_once '../sys/inc/compress.php';
include_once '../sys/inc/sess.php';
include_once '../sys/inc/home.php';
include_once '../sys/inc/settings.php';
include_once '../sys/inc/db_connect.php';
include_once '../sys/inc/ipua.php';
include_once '../sys/inc/fnc.php';
include_once '../sys/inc/adm_check.php';
include_once '../sys/inc/user.php';
user_access('adm_ban_ip',null,'index.php?'.SID);
$opsos=NULL;
$set['title']='Бан по IP';
include_once '../sys/inc/thead.php';
title();
if (isset($_POST['min']) && isset($_POST['max']))
{
if (!preg_match("#^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$#",$_POST['min']))$err[]='Неверный формат IP-адреса';
if (!preg_match("#^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$#",$_POST['max']))$err[]='Неверный формат IP-адреса';
$min=ip2long($_POST['min']);
$max=ip2long($_POST['max']);
if (ip2long($ip)>=$min && ip2long($ip)<=$max)
{
$err[]='Ваш IP входит в заданный диапазон';
}
elseif (!isset($err))
{
$db->query("INSERT INTO `ban_ip` (`min`, `max`) values('$min', '$max')",$db);
msg ('Диапазон успешно забанен');
}
}
if (isset($_GET['delmin'])  && isset($_GET['delmax']) &&
 $db->query("SELECT COUNT(*) FROM `ban_ip` WHERE `min` = '".$_GET['delmin']."' AND `max` = '".$_GET['delmax']."' LIMIT 1")->el())
{
$db->query("DELETE FROM `ban_ip` WHERE `min` = '".$_GET['delmin']."' AND `max` = '".$_GET['delmax']."' LIMIT 1");
$db->query("OPTIMIZE TABLE `ban_ip`");
msg('Диапазон успешно удален');
}
err();
aut();
$k_post=$db->query("SELECT COUNT(*) FROM `ban_ip`")->el();
$k_page=k_page($k_post,$set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
$q=$db->query("SELECT * FROM `ban_ip` LIMIT $start, $set[p_str]");
echo "<table class='post'>\n";
if ($k_post==0)
{
echo "   <tr>\n";
echo "  <td class='p_t'>\n";
echo "Нет забаненых IP\n";
echo "  </td>\n";
echo "   </tr>\n";
}
while ($post = $q->row())
{
echo "   <tr>\n";
echo "  <td class='p_t'>\n";
echo long2ip($post['min']).' - '.long2ip($post['max']);
echo "  </td>\n";
echo "   </tr>\n";
echo "   <tr>\n";
echo "  <td class='p_m'>\n";
echo "<a href='?page=$page&amp;delmin=$post[min]&amp;delmax=$post[max]'>Удалить</a><br />\n";
echo "  </td>\n";
echo "   </tr>\n";
}
echo "</table>\n";
if ($k_page>1)str('?',$k_page,$page); // Вывод страниц
$min=NULL;$max=NULL;
if (isset($_GET['min']) && preg_match("#^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$#",long2ip($_GET['min'])))
{
echo "HOST: ".gethostbyaddr(long2ip($_GET['min']))."<br />\n";
$min=long2ip($_GET['min']);$max=long2ip($_GET['min']);
if (isset($_GET['max']) && preg_match("#^([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3}).([0-9]{1,3})$#",long2ip($_GET['max'])))$max=long2ip($_GET['max']);
}
echo "<form method='post' action='?ban=$passgen'>\n";
echo "Начало:<br />\n<input name='min' size='16'  value='$min' type='text' /><br />\n";
echo "Конец:<br />\n<input name='max' size='16' value='$max' type='text' /><br />\n";
echo "<input value='Забанить' type='submit' />\n";
echo "</form>\n";
if (user_access('adm_panel_show')){
echo "<div class='foot'>\n";
echo "&laquo;<a href='/adm_panel/'>В админку</a><br />\n";
echo "</div>\n";
}
include_once '../sys/inc/tfoot.php';
?>