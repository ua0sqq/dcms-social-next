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
if (isset($_GET['id']) && is_numeric($_GET['id']) && $db->query("SELECT COUNT(`id`) FROM `stena` WHERE `id` = '".intval($_GET['id'])."' LIMIT 1")!=0)
{
$id=abs(intval($_GET['id']));
$post=$db->query("SELECT * FROM `stena` WHERE `id`='$id' LIMIT 1")->row();
$set['title']=' Комментарии к записи';
include_once '../sys/inc/thead.php';
title();
if (isset($_POST['msg']) && isset($user))
{
$msg = esc(stripslashes(htmlspecialchars($_POST['msg'])));
$mat = antimat($msg);
     if ($mat)$err[] = 'В тексте сообщения обнаружен мат: ' . $mat;
     if (strlen2($msg) > 1024)
     {
     $err[] = 'Сообщение слишком длинное';
     }
     elseif (strlen2($msg) < 2)
     {
     $err[] = 'Короткое сообщение';
     }
     elseif ($db->query("SELECT COUNT(`id`) FROM `stena_komm` WHERE `id_user` = '" . $user['id'] . "' AND `msg` = '" . my_esc($msg) . "' AND `id_stena`='$id' LIMIT 1") != 0)
     {
     $err[] = 'Ваше сообщение повторяет предыдущее';
     }
     elseif(!isset($err))
     {
     $db->query("INSERT INTO `stena_komm` (`id_user`, `time`, `msg`,`id_stena`) values('" . $user['id'] . "', '" . $time . "', '" . my_esc($msg) . "','".$id."')");
     
     /*
     =====
     Отправляем автору комма
     =====
     */
if (isset($user)){
		$notifiacation=$db->query("SELECT * FROM `notification_set` WHERE `id_user` = '".$post['id_user']."' LIMIT 1")->row();
			
			if ($notifiacation['komm'] == 1 && $post['id_user'] != $user['id'])
			$db->query("INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES ('$user[id]', '$post[id_user]', '$post[id]', 'stena_komm2', '$time')");
		
		}
    $_SESSION['message']='Сообщение успешно добавлено';
header('Location: /user/komm.php?id='.$id);
     }
}
elseif (isset($_GET['del']) && $db->query("SELECT COUNT(*) FROM `stena_komm` WHERE `id` = '".intval($_GET['del'])."' AND `id_stena` = '$post[id]'"))
{
if (isset($user) && ($user['level']>=3 || $user['id']=$stena['id_user']))
{
$db->query("DELETE FROM `stena_komm` WHERE `id` = '".intval($_GET['del'])."' LIMIT 1");
$_SESSION['message']='Комментарий успешно удален';
header('Location: /user/komm.php?id='.$id);
}
}
err();
aut(); 
////////////////////////////////////////////////////////
$post=$db->query("SELECT * FROM `stena` WHERE `id` = '".abs(intval($_GET['id']))."' LIMIT 1")->row();
echo "  <div class='nav2'>\n";
echo "<table><td style='width:15%;vertical-align:top;'>"; echo avatar($post['id_user']);
echo "</td><td style='vertical-align:top;'>";
echo  group($post['id_user'])." ";
echo user::nick($post['id_user'],1,1,1);
echo " <span style='color:#666'>".vremja($post['time'])."</span><br/>";
stena($post['id_user'],$post['id']);
echo output_text($post['msg'])."<br />\n";
echo "</td></table></div>";
$k_post=$db->query("SELECT COUNT(*) FROM `stena_komm` WHERE `id_stena` = '$id'");
$k_page=k_page($k_post,$set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
$q=$db->query("SELECT * FROM `stena_komm` WHERE `id_stena` = '".intval($_GET['id'])."' ORDER BY `id` DESC LIMIT $start, $set[p_str]");
echo "<div class='main'><b>Комментарии:</b> (".$k_post.")</div>";
if ($k_post==0)
{
echo'<div class="mess">';
echo'<font color=grey>Запись ещё никто не комментировал.</font>';
echo'</div>';
}
while ($komm = $q->row())
{
echo'<div class="nav1">';
echo group($komm['id_user']).' ' ;
echo user::nick($komm['id_user'],1,1,1);
echo ' ('.vremja($komm['time']).')';
echo "<br />";
echo output_text($komm['msg'])."<br />\n";
if (isset($user) && ($user['level']>=3 || $user['id'] == $post['id_user']))
echo'<a href="?id='.$post['id'].'&del='.$komm['id'].'">Удалить</a><br />';
echo'</div>';
}
if ($k_page>1)str("komm.php?id=$post[id]&",$k_page,$page); // Вывод страниц
if (!isset($_POST['msg']) && isset($user) ) 
{
echo'<div class="main_menu"><form method="post" name="message" action="?id='.$post['id'].'">';
echo $tPanel;
echo'<textarea name="msg"></textarea><br />';
echo'<input value="Отправить" type="submit" />';
echo'</form></div>';
}}else{
header('Location: /index.php');
}
include_once '../sys/inc/tfoot.php';
?>