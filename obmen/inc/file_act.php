<?php
if ((user_access('obmen_file_delete') || $user['id'] == $file_id['id_user'])  && isset($_GET['act']) && $_GET['act'] == 'edit' && isset($_GET['ok']) && $l != '/') {
    $name = trim($_POST['name']);
    $opis = trim($_POST['opis']);
    if (strlen2($name) < 2) {
        $err[]='Короткое Название';
    }
    if (strlen2($name) > 128) {
        $err[]='Длинное Название';
    }
    if ($_POST['metka'] == 0 || $_POST['metka'] == 1) {
        $metka = $_POST['metka'];
    } else {
        $err = 'Ошибка метки +18';
    }
    if (!isset($err)) {
        $db->query(
            "UPDATE `obmennik_files` SET `metka`=?i, `name`=?,`opis`=? WHERE `id`=?i",
                   [$metka, $name, $opis, $file_id['id']]);
        $_SESSION['message']='Файл успешно отредактирован';
        admin_log('Обменник', 'Редактирование файла',
				  'Редактирование файла [url=/obmen' . $dir_id['dir'] . $name . '.' . $file_id['ras'] . '?showinfo]' . $file_id['name'] . '[/url]');
        header('Location: /obmen' . $dir_id['dir'] . $file_id['id'] . '.' . $file_id['ras'] . '?showinfo');
        exit;
    }
}
if ((user_access('obmen_file_delete') || $user['id'] == $file_id['id_user']) && isset($_GET['act']) && $_GET['act'] == 'delete' && isset($_GET['ok']) && $l != '/') {
    $db->query("DELETE FROM `obmennik_files` WHERE `id`=?i", [$file_id['id']]);
    $db->query(
        "DELETE FROM `user_music` WHERE `id_file`=?i AND `dir`=?",
               [$file_id['id'], 'obmen']);
	if (is_file(H . 'sys/obmen/files/' . $file_id['id'] . '.dat')) {
		unlink(H . 'sys/obmen/files/' . $file_id['id'] . '.dat');
	}
	$db->query('DELETE FROM `bookmarks` WHERE `type`="file" AND `id_object`=?i', [$file_id['id']]);
    $_SESSION['message'] = 'Файл успешно удален';
    header('Location: /obmen' . $dir_id['dir'] . '?' . SID);
    exit;
}
