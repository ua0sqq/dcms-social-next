<?php
if (user_access('obmen_dir_delete') && isset($_GET['act']) && $_GET['act']=='delete' && isset($_GET['ok']) && $l!='/')
{
if ($dir_id['my'] == 1)
{
echo "Нельзя удалить папку Личные файлы!";
exit;
}
$q=$db->query("SELECT * FROM `obmennik_dir` WHERE `dir_osn` like '$l%'");
while ($post = $q->row())
{
$q2=$db->query("SELECT * FROM `obmennik_files` WHERE `id_dir` = '$post[id]'");
while ($post2 = $q2->row())
{
if (!@unlink(H.'sys/obmen/files/'.$post2['id'].'.dat'))$err[]='Ошибка удаления файла '.$post2['id'].'.dat';
@unlink(H.'sys/obmen/files/'.$post2['id'].'.dat.GIF');
@unlink(H.'sys/obmen/files/'.$post2['id'].'.dat.JPG');
@unlink(H.'sys/obmen/files/'.$post2['id'].'.dat.PNG');
$db->query("DELETE FROM `user_music` WHERE `id_file` = '$post2[id]' AND `dir` = 'obmen'");
}
$db->query("DELETE FROM `obmennik_files` WHERE `id_dir` = '$post[id]'");
$db->query("DELETE FROM `obmennik_dir` WHERE `id` = '$post[id]' LIMIT 1");
}
$q2=$db->query("SELECT * FROM `obmennik_files` WHERE `id_dir` = '$dir_id[id]'");
while ($post = $q2->row())
{
unlink(H.'sys/obmen/files/'.$post['id'].'.dat');
@unlink(H.'sys/obmen/files/'.$post['id'].'.dat.GIF');
@unlink(H.'sys/obmen/files/'.$post['id'].'.dat.JPG');
@unlink(H.'sys/obmen/files/'.$post['id'].'.dat.PNG');
$db->query("DELETE FROM `user_music` WHERE `id_file` = '$post[id]' AND `dir` = 'obmen'");
}
$db->query("DELETE FROM `obmennik_files` WHERE `id_dir` = '$dir_id[id]'");
$db->query("DELETE FROM `obmennik_dir` WHERE `id` = '$dir_id[id]' LIMIT 1");
$l=$dir_id['dir_osn'];
msg('Папка успешно удалена');
admin_log('Обменник','Удаление папки',"Папка '$dir_id[name]' удалена");
$dir_id=$db->query("SELECT * FROM `obmennik_dir` WHERE `dir` = '/$l' OR `dir` = '$l/' OR `dir` = '$l' LIMIT 1")->row();
$id_dir=$dir_id['id'];
}
if (user_access('obmen_dir_edit') && isset($_GET['act']) && $_GET['act']=='mesto' && isset($_GET['ok']) && isset($_POST['dir_osn']) && $l!='/')
{
if ($_POST['dir_osn']==NULL)
$err= "Не выбран коненый путь";
else
{
$q=$db->query("SELECT * FROM `obmennik_dir` WHERE `dir_osn` like '$l%'");
while ($post = $q->row())
{
$new_dir_osn=preg_replace("#^$l/#",$_POST['dir_osn'],$post['dir_osn']).$dir_id['name'].'/';
$new_dir=$new_dir_osn.$post['name'];
$db->query("UPDATE `obmennik_dir` SET `dir`='$new_dir/', `dir_osn`='$new_dir_osn' WHERE `id` = '$post[id]' LIMIT 1");
}
$l=$_POST['dir_osn'];
$db->query("UPDATE `obmennik_dir` SET `dir`='".$l."$dir_id[name]/', `dir_osn`='".$l."' WHERE `id` = '$dir_id[id]' LIMIT 1");
admin_log('Обменник','Изменение папки',"Папка '$dir_id[name]' перемещена");
msg('Папка успешно перемещена');
$dir_id=$db->query("SELECT * FROM `obmennik_dir` WHERE `id` = '$dir_id[id]' LIMIT 1")->row();
$id_dir=$dir_id['id'];
}
}
if (user_access('obmen_dir_edit') && isset($_GET['act']) && $_GET['act']=='rename' && isset($_GET['ok']) && isset($_POST['name']) && $l!='/')
{
if ($_POST['name']==NULL)
$err= "Введите название папки";
// ShaMan
elseif( !preg_match("#^([A-zА-я0-9\-\_\(\)\ ])+$#ui", $_POST['name']))$err[]='В названии присутствуют запрещенные символы';
// Тут конец моих дум
else
{
$newdir=retranslit($_POST['name'],1);
if (!isset($err)){
if ($l!='/')$l.='/';
$downpath=preg_replace('#[^/]*/$#', NULL, $l);
$db->query("UPDATE `obmennik_dir` SET `name`='".esc($_POST['name'],1)."' WHERE `dir` = '/$l' OR `dir` = '$l/' OR `dir` = '$l' LIMIT 1");
msg('Папка успешно переименована');
admin_log('Обменник','Изменение папки',"Папка '$dir_id[name]' переименована в '".esc($_POST['name'],1)."'");
$l=$downpath.$newdir;
$dir_id=$db->query("SELECT * FROM `obmennik_dir` WHERE `dir` = '/$l' OR `dir` = '$l/' OR `dir` = '$l' LIMIT 1")->row();
$id_dir=$dir_id['id'];
}
}
}
if (user_access('obmen_dir_create') && isset($_GET['act']) && $_GET['act']=='mkdir' && isset($_GET['ok']) && isset($_POST['name']))
{
if ($_POST['name']==NULL)
$err= "Введите название папки";
elseif( !preg_match("#^([A-zА-я0-9\-\_\(\)\ ])+$#ui", $_POST['name']))$err[]='В названии присутствуют запрещенные символы';
else
{
$newdir=retranslit($_POST['name'],1);
if (isset($_POST['upload']) && $_POST['upload']=='1')$upload=1; else $upload=0;
if (!isset($_POST['ras']) || $_POST['ras']==NULL)
{
$upload=0;
}
$size=0;
if ($upload==1 && isset($_POST['size']) && isset($_POST['mn']))
{
$size=intval($_POST['size'])*intval($_POST['mn']);
if ($upload_max_filesize<$size)$size=$upload_max_filesize;
}
else $upload=0;
// ShaMan
$ras=esc(stripcslashes(htmlspecialchars($_POST['ras'],1)));
// Тут конец моих дум
if (!isset($err)){
if ($l!='/')$l.='/';
$db->query("INSERT INTO `obmennik_dir` (`name` , `ras` , `maxfilesize` , `dir` , `dir_osn` , `upload` ) 
VALUES ('".esc($_POST['name'],1)."', '$ras', '$size', '".$l."$newdir/', '".$l."', '$upload')");
msg('Папка "'.esc($_POST['name'],1).'" успешно создана');
admin_log('Обменник','Создание папки',"Создана папка '".esc($_POST['name'],1)."'");
}
}
}
if (user_access('obmen_dir_edit') && isset($_GET['act']) && $_GET['act']=='set' && isset($_GET['ok']))
{
if (isset($_POST['upload']) && $_POST['upload']=='1')$upload=1; else $upload=0;
if (!isset($_POST['ras']) || $_POST['ras']==NULL)
{
$upload=0;
}
$size=0;
if ($upload==1 && isset($_POST['size']) && isset($_POST['mn']))
{
$size=intval($_POST['size'])*intval($_POST['mn']);
if ($upload_max_filesize<$size)$size=$upload_max_filesize;
}
else $upload=0;
// ShaMan
$ras=esc(stripcslashes(htmlspecialchars($_POST['ras'],1)));
// Тут конец моих дум
if (!isset($err)){
if ($l!='/')$l.='/';
$db->query("UPDATE `obmennik_dir` SET `ras`='$ras', `maxfilesize`='$size', `upload`='$upload' WHERE `id` = '$dir_id[id]'");
msg('Параметры папки успешно изменены');
admin_log('Обменник','Изменение папки',"Изменены параметры папки '$dir_id[name]'");
$dir_id=$db->query("SELECT * FROM `obmennik_dir` WHERE `id` = '$dir_id[id]' LIMIT 1")->row();
$id_dir=$dir_id['id'];
}
}
?>