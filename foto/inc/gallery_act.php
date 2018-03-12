<?php
if (isset($user) && $user['id'] == $ank['id']) {
    if (isset($_GET['act']) && $_GET['act']=='create' && isset($_GET['ok']) && isset($_POST['name']) && isset($_POST['opis'])) {
        $name = my_esc($_POST['name']);
        if (strlen2($name) < 3) {
            $err = 'Короткое название';
        }
        if (strlen2($name) > 32) {
            $err = 'Название не должно быть длиннее 32-х символов';
        }
        
        $pass = isset($_POST['pass']) ? my_esc($_POST['pass']) : '';
        
        $privat = intval($_POST['privat']);
        $privat_komm = intval($_POST['privat_komm']);
        
        $msg = $_POST['opis'];
        
        if (strlen2($msg) > 256) {
            $err = 'Длина описания превышает предел в 256 символов';
        }
        $msg = my_esc($msg);
        
        if ($db->query("SELECT COUNT(*) FROM `gallery` WHERE `id_user` = '$ank[id]' AND `name` = '$name'")->el()) {
            $err = 'Альбом с таким названием уже существует';
        }
        
        if (!isset($err)) {
            $gallery_id = $db->query("INSERT INTO `gallery` (`opis`, `time_create`, `id_user`, `name`, `time`, `pass`, `privat`, `privat_komm`) values('$msg', '$time', '$ank[id]', '$name', '$time', '$pass', '$privat', '$privat_komm')")->id();
            $_SESSION['message'] = 'Фотоальбом успешно создан';
            header("Location: /foto/$ank[id]/$gallery_id/");
            exit;
        }
    }
}
