<?php
/*
* Установка аватара на главной
*/
if (isset($_GET['act']) && $_GET['act']=='avatar')
{
	if ($user['id']==$ank['id'])
	{
		/* Отправляем в ленту смену аватара */
		$avatar = $db->query("SELECT * FROM `gallery_foto` WHERE `avatar` = '1' AND `id_user` = '$user[id]' LIMIT 1")->row();
		
		if ($avatar['id'] != $foto['id'])
		{
			/*---------друзьям автора--------------*/
			$q = $db->query("SELECT * FROM `frends` WHERE `user` = '".$gallery['id_user']."' AND `i` = '1'");
			
			while ($f = $q->row())
			{
				$a = get_user($f['frend']);
				
				if ($a['id'] != $user['id'] && $foto['id'] != $avatar['id'] && $f['lenta_avatar'] == 1)
				$db->query("INSERT INTO `tape` (`id_user`, `avtor`, `type`, `time`, `id_file`, `count`, `avatar`) values('$a[id]', '$gallery[id_user]', 'avatar', '$time', '$foto[id]', '1', '$avatar[id]')"); 
			}
			
			$db->query("UPDATE `gallery_foto` SET `avatar` = '0' WHERE `id_user` = '$user[id]'");
			$db->query("UPDATE `gallery_foto` SET `avatar` = '1' WHERE `id` = '$foto[id]' LIMIT 1");
			$db->query("INSERT INTO `stena` (`id_user`,`id_stena`,`time`,`info`,`info_1`,`type`) values('".$user['id']."','".$user['id']."','".$time."','новый аватар','".$foto['id']."','foto')");
			$_SESSION['message'] = 'Фотография успешно установлена на главной!';
		}
		
		header("Location: ?");
		exit;
	}
}
/*
* Удаление фотографии
*/
if (isset($_GET['act']) && $_GET['act'] == 'delete' && isset($_GET['ok']))
{
	if ($user['id'] != $ank['id'])
	admin_log('Фотогалерея','Фотографии',"Удаление фото пользователя '[url=/id$ank[id]]" . user::nick($ank['id'], 0) . "[/url]'");
	@unlink(H."sys/gallery/48/$foto[id].jpg");
	@unlink(H."sys/gallery/128/$foto[id].jpg");
	@unlink(H."sys/gallery/640/$foto[id].jpg");
	@unlink(H."sys/gallery/foto/$foto[id].jpg");
	$db->query("DELETE FROM `gallery_foto` WHERE `id` = '$foto[id]' LIMIT 1");
	$_SESSION['message'] = 'Фотография успешно удалена';
	header("Location: /foto/$ank[id]/$gallery[id]/");
	exit;
}
/*
* Редактирование фотографии
*/
if (isset($_GET['act']) && $_GET['act']=='rename' && isset($_GET['ok']) && isset($_POST['name']) && isset($_POST['opis']))
{
	$name = esc(stripcslashes(htmlspecialchars($_POST['name'])),1);
	if (!preg_match("#^([A-zА-я0-9\-\_\(\)\,\.\ ])+$#ui",$name))$err = 'В названии темы присутствуют запрещенные символы';
	if (strlen2($name) < 3 )$err = 'Короткое название';
	if (strlen2($name) > 32 )$err = 'Название не должно быть длиннее 32-х символов';
	$name = my_esc($name);
	$msg = $_POST['opis'];
	
	if (strlen2($msg) > 1024)$err = 'Длина описания превышает предел в 1024 символа';
	$msg = my_esc($msg);
	if ($_POST['metka'] == 0 || $_POST['metka'] == 1)
	$metka = $_POST['metka'];
	else $metka = 0;
	if (!isset($err))
	{
		if ($user['id'] != $ank['id'])
		admin_log('Фотогалерея','Фотографии',"Переименование фото пользователя '[url=/id$ank[id]]" . user::nick($ank['id'], 0) . "[/url]'");
		$db->query("UPDATE `gallery_foto` SET `name` = '$name', `metka` = '$metka', `opis` = '$msg' WHERE `id` = '$foto[id]' LIMIT 1");
		$foto=$db->query("SELECT * FROM `gallery_foto` WHERE `id` = '$foto[id]'  LIMIT 1")->row();
		$_SESSION['message'] = 'Фотография успешно переименована';
		header("Location: ?");
		exit;
	}
}
?>