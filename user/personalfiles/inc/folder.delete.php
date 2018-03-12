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
/*-----------------------Удаление папки----------------------*/
if (isset($_GET['delete_folder']) && !isset($_GET['ok']))
{
$folder = $db->query("SELECT * FROM `user_files`  WHERE `id` = '".intval($_GET['delete_folder'])."' LIMIT 1")->row();
echo "<div class='mess'><center>";
echo "Вы действительно желаете удалить <b>".htmlspecialchars($folder['name'])."</b><br />";
echo "[<a href='?delete_folder=$folder[id]&amp;ok'><img src='/style/icons/ok.gif' alt='*'> Да</a>] [<a href='?'><img src='/style/icons/delete.gif' alt='*'> Нет</a>] \n";
echo "</center></div>";
include_once '../../sys/inc/tfoot.php';
}
$a = 0;
$b = 0;
if (isset($_GET['delete_folder']) && isset($_GET['ok']) )
{
$folder = $db->query("SELECT * FROM `user_files`  WHERE `id` = '".intval($_GET['delete_folder'])."' LIMIT 1")->row();
$q=$db->query("SELECT * FROM `user_files` WHERE `id_dires` like '%/$dir[id]/".intval($_GET['delete_folder'])."/%'");
while ($post = $q->row())
{
$a++;
$q2=$db->query("SELECT * FROM `obmennik_files` WHERE `my_dir` = '$post[id]'");
while ($post2 = $q2->row())
{
echo $post2['name'].'<br />';
unlink(H.'sys/obmen/files/'.$post2['id'].'.dat');
unlink(H.'sys/obmen/screens/128/'.$post2['id'].'.gif');
unlink(H.'sys/obmen/screens/128/'.$post2['id'].'.png');
unlink(H.'sys/obmen/screens/128/'.$post2['id'].'.jpg');
unlink(H.'sys/obmen/screens/128/'.$post2['id'].'.jpeg');
unlink(H.'sys/obmen/screens/48/'.$post2['id'].'.gif');
unlink(H.'sys/obmen/screens/48/'.$post2['id'].'.png');
unlink(H.'sys/obmen/screens/48/'.$post2['id'].'.jpg');
unlink(H.'sys/obmen/screens/48/'.$post2['id'].'.jpeg');
$db->query("DELETE FROM `user_music` WHERE `id_file` = '$post2[id]' AND `dir` = 'obmen'");
$db->query("DELETE FROM `obmennik_files` WHERE `id` = '$post2[id]'");
$b++;
}
echo $post['name'].'<br />';
$db->query("DELETE FROM `user_files` WHERE `id` = '$post[id]' LIMIT 1");
}
$q2=$db->query("SELECT * FROM `user_files` WHERE `id` = '".intval($_GET['delete_folder'])."'");
while ($post = $q2->row())
{
$a++;
$q3=$db->query("SELECT * FROM `obmennik_files` WHERE `my_dir` = '$post[id]'");
while ($post2 = $q3->row())
{
echo $post2['name'].'<br />';
unlink(H.'sys/obmen/files/'.$post2['id'].'.dat');	
unlink(H.'sys/obmen/screens/128/'.$post2['id'].'.gif');
unlink(H.'sys/obmen/screens/128/'.$post2['id'].'.png');
unlink(H.'sys/obmen/screens/128/'.$post2['id'].'.jpg');
unlink(H.'sys/obmen/screens/128/'.$post2['id'].'.jpeg');
unlink(H.'sys/obmen/screens/48/'.$post2['id'].'.gif');
unlink(H.'sys/obmen/screens/48/'.$post2['id'].'.png');
unlink(H.'sys/obmen/screens/48/'.$post2['id'].'.jpg');
unlink(H.'sys/obmen/screens/48/'.$post2['id'].'.jpeg');
$db->query("DELETE FROM `user_music` WHERE `id_file` = '$post2[id]' AND `dir` = 'obmen'");
$db->query("DELETE FROM `obmennik_files` WHERE `id` = '$post2[id]'");
$b++;
}
$db->query("DELETE FROM `user_files` WHERE `id` = '$post[id]' LIMIT 1");
echo $post['name'].'<br />';
}
$_SESSION['message']="Удалено \"папок $a \" и \"файлов $b\"";
header("Location: ?".SID);
exit;
}
/*------------------------------------------------------------*/
?>