<?PHP
/*
=======================================
Личные файлы юзеров для Dcms-Social
Автор: Искатель
---------------------------------------
Этот скрипт распостроняется по лицензии
движка Dcms-Social. 
При использовании указывать ссылку на
оф. сайт http://dcms-social.ru
---------------------------------------
Контакты
ICQ: 587863132
http://dcms-social.ru
=======================================
*/
$set['title'] = text($dir['name']);
title();
aut();
 // Редактирование и удаление файлов\папок    
if (isset($user) && (user_access('obmen_file_edit') || $ank['id']==$user['id']))
{
	// Удаление папок и файлов в них
	include "inc/folder.delete.php";
	
	// Управление папками
	include "inc/folder.edit.php";
	
	// Прочие формы вывода
	include "inc/all.form.php";
}
 // Вывод обратной навигации
echo "<div class='foot'>";
echo "<img src='/style/icons/up_dir.gif' alt='*'> ".($dir['osn']==1?'Файлы':'')." ".user_files($dir['id_dires'])." ".($dir['osn']==1?'':'&gt; '.text($dir['name']))."\n";
echo "</div>";
 // Перемещение файла в другую папку
if (isset($_GET['go']) && $db->query("SELECT COUNT(*) FROM `obmennik_files` WHERE `id` = '".intval($_GET['go'])."'")->el())
{
	$file_go = $db->query("SELECT * FROM `obmennik_files` WHERE `id` = '".intval($_GET['go'])."'")->row();
	if (isset($_GET['ok']) && isset($_GET['ok']) && $ank['id'] == $user['id'])
	{
		$db->query("UPDATE `obmennik_files` SET `my_dir` = '$dir[id]' WHERE `id` = '$file_go[id]' LIMIT 1");
		$_SESSION['message'] = 'Файл успешно перемещен';
		header("Location: ?");
		exit;
	}
}
/*--------------------Папка под паролем--------------------*/
if ($dir['pass']!=NULL)
{
if (isset($_POST['password']))
{
$_SESSION['pass']=my_esc($_POST['password']);
if ($_SESSION['pass']!=$dir['pass'])
{$_SESSION['message'] = 'Неверный пароль'; $_SESSION['pass']=NULL;}
header("Location: ?");
}
if (!user_access('obmen_dir_edit') && ($user['id']!=$ank['id'] && $_SESSION['pass']!=$dir['pass']))
{
echo '<form action="?" method="POST">Пароль: <br />		<input type="pass" name="password" value="" /><br />		
<input type="submit" value="Войти"/></form>';
echo "<div class='foot'>";
echo "<img src='/style/icons/up_dir.gif' alt='*'> ".($dir['osn']==1?'Файлы':'')." ".user_files($dir['id_dires'])." ".($dir['osn']==1?'':'&gt; '.text($dir['name']))."\n";
echo "</div>";
include_once '../../sys/inc/tfoot.php';
exit;
}
}
/*---------------------------------------------------------*/
if (isset($_GET['go']))
{
	echo '<div class="foot">';
	echo "<img src='/style/icons/ok.gif' alt='*'> <a href='/user/personalfiles/$ank[id]/$dir[id]/?go=$file_go[id]&amp;ok'>Переместить сюда</a>\n";
	echo "</div>";
	echo '<div class="mess">';
	echo "Выбирете папку для файла\n";
	echo "</div>";
}
if (isset($_SESSION['obmen_dir']) || isset($_GET['obmen_dir']))
{
	if (!isset($_SESSION['obmen_dir']) && $db->query("SELECT COUNT(*) FROM `obmennik_dir` WHERE `id` = '" . intval($_GET['obmen_dir']) . "' AND `upload` = '1'")->el())
	$_SESSION['obmen_dir'] = abs(intval($_GET['obmen_dir']));
	
	if (isset($_SESSION['obmen_dir']))
	{
		echo '<div class="mess">';
		echo "Выбирете папку для загрузки файла\n";
		echo "</div>";		
	}
}
$k_files=$db->query("SELECT COUNT(*) FROM `obmennik_files`  WHERE `my_dir` = '$dir[id]' AND `id_user` = '$ank[id]'")->el();
$k_post=$db->query("SELECT COUNT(*) FROM `user_files` WHERE `id_dir` = '$dir[id]' AND `id_user` = '$ank[id]'")->el();
$k_post=$k_post+$k_files;
$k_page=k_page($k_post,$set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
echo "<table class='post'>\n";
if ($k_post==0)
{
echo '<div class="mess">';
echo "Папка пуста\n";
echo "  </div>\n";
}
$q=$db->query("SELECT * FROM `user_files`  WHERE `id_dir` = '$dir[id]'  AND `id_user` = '$ank[id]' ORDER BY time DESC LIMIT $start, $set[p_str]");
while ($post = $q->row())
{
/*-----------зебра-----------*/ 
if ($num==0){
echo '<div class="nav1">';
$num=1;
}
elseif ($num==1){
echo '<div class="nav2">';
$num=0;}
/*---------------------------*/
echo "<img src='/style/themes/$set[set_them]/loads/14/".($post['pass']!=null?'lock.gif':'dir.png')."' alt='*'>";
if (isset($_GET['go'])) // Если перемещаем файл
echo " <a href='/user/personalfiles/$ank[id]/$post[id]/?go=$file_go[id]'>".text($post['name'])."</a>\n";
else
echo " <a href='/user/personalfiles/$ank[id]/$post[id]/'>".text($post['name'])."</a>\n";
/*----------------------Счетчик папок---------------------*/
$k_f=0;
$q3=$db->query("SELECT * FROM `user_files` WHERE `id_dires` like '%$post[id]%'");
while ($post2 = $q3->row())
{
$k_f=$k_f+$db->query("SELECT COUNT(*) FROM `user_files` WHERE `id_dir` = '$post2[id]'")->el();
}
$k_f=$k_f+$db->query("SELECT COUNT(*) FROM `user_files` WHERE `id_dir` = '$post[id]'")->el();
/*---------------------------------------------------------*/
/*----------------------Счетчик файлов--------------------*/
$k_f2=0;
$q4=$db->query("SELECT * FROM `user_files` WHERE `id_dires` like '%$post[id]%'");
while ($post3 = $q4->row())
{
$k_f2=$k_f2+$db->query("SELECT COUNT(*) FROM `obmennik_files` WHERE `my_dir` = '$post3[id]'")->el();
}
$k_f2=$k_f2+$db->query("SELECT COUNT(*) FROM `obmennik_files` WHERE `my_dir` = '$post[id]'")->el();
/*---------------------------------------------------------*/
echo ' ('.$k_f.'/'.$k_f2.') ';
if (isset($user) && $user['group_access']>2 || $ank['id']==$user['id'])
echo "<a href='?edit_folder=$post[id]'><img src='/style/icons/edit.gif' alt='*'></a> <a href='?delete_folder=$post[id]'><img src='/style/icons/delete.gif' alt='*'></a><br />\n";
echo "</div>";
}
if (!isset($_GET['go']))
{
$q2=$db->query("SELECT * FROM `obmennik_files`  WHERE `my_dir` = '$dir[id]' AND `id_user` = '$ank[id]' ORDER BY time DESC LIMIT $start, $set[p_str]");
//echo "<form method='post' action='?move_file'>";
while ($post = $q2->row())
{
$k_p=$db->query("SELECT COUNT(*) FROM `obmennik_komm` WHERE `id_file` = '$post[id]'")->el();
$dir_id=$db->query("SELECT * FROM `obmennik_dir` WHERE `id` = '$post[id_dir]' LIMIT 1")->row();
$ras=$post['ras'];
$file=H."sys/obmen/files/$post[id].dat";
$name=$post['name'];
$size=$post['size'];
/*-----------зебра-----------*/ 
if ($num==0){
echo '<div class="nav1">';
$num=1;
}
elseif ($num==1){
echo '<div class="nav2">';
$num=0;}
/*---------------------------*/
if (is_file(H."obmen/inc/icon48/$ras.php"))
{
include H."obmen/inc/icon48/$ras.php";
}
//echo "<input type='checkbox' name='files_$post[id]' value='1' /> ";
if (is_file(H.'style/themes/'.$set['set_them'].'/loads/14/'.$ras.'.png'))
echo "<img src='/style/themes/$set[set_them]/loads/14/$ras.png' alt='$ras' /> \n";
else 
echo "<img src='/style/themes/$set[set_them]/loads/14/file.png' alt='file' /> \n";
if ($set['echo_rassh']==1)$ras=$post['ras'];else $ras=NULL;
echo '<a href="?id_file='.$post['id'].'&amp;page='.$page.'"><b>'.text($post['name']).'.'.$ras.'</b></a> ('.size_file($post['size']).') ';
if ($post['metka'] == 1)echo ' <font color=red>(18+)</font>';
if ($user['id']==$post['id_user'] && $dir_id['my']==1)echo '<a href="/obmen/?trans='.$post['id'].'"><img src="/style/icons/z.gif" alt="*"> в зону</a> ';
if (user_access('obmen_file_edit') || $user['id']==$post['id_user'])echo '<a href="?id_file='.$post['id'].'&amp;edit"><img src="/style/icons/edit.gif" alt="*"></a> ';
if (user_access('obmen_file_delete') || $user['id']==$post['id_user'])echo '<a href="?id_file='.$post['id'].'&amp;delete&amp;page='.$page.'"><img src="/style/icons/delete.gif" alt="*"></a> ';
echo '<br />';
	if ($post['opis']){
	echo rez_text(text($post['opis'])).'<br />';
	}
echo '<a href="?id_file='.$post['id'].'&amp;page='.$page.'&amp;komm">Комментарии</a> ('.$k_p.')<br />';
echo '</div>';
}
}
//echo "<input value=\"Задание\" type=\"submit\" name=\"job\" />";
//echo "</form>\n";
echo "</table>\n";
if ($k_page>1)str('?',$k_page,$page); // Вывод страниц
echo "<div class='foot'>";
echo "<img src='/style/icons/up_dir.gif' alt='*'> ".($dir['osn']==1?'Файлы':'')." ".user_files($dir['id_dires'])." ".($dir['osn']==1?'':'&gt; '.text($dir['name']))."\n";
echo "</div>";
?>