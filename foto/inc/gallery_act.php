<?php
if (isset($user) && $user['id'] == $ank['id']) {
    if (isset($input_get['act']) && $input_get['act']=='create' && isset($input_get['ok']) && isset($input_post['name']) && isset($input_post['opis'])) {
        $name = trim($input_post['name']);
        if (strlen2($name) < 3) {
            $err = 'Короткое название';
        }
        if (strlen2($name) > 32) {
            $err = 'Название не должно быть длиннее 32-х символов';
        }
        
        $pass = isset($input_post['pass']) ? $input_post['pass'] : '';        
        $msg = trim($input_post['opis']);
        $privat = !empty($input_post['privat']) ? abs($input_post['privat']) : 0;
        $privat_komm = !empty($input_post['privat_komm']) ? abs($input_post['privat_komm']) : 0;
        
        if (strlen2($msg) > 256) {
            $err = 'Длина описания превышает предел в 256 символов';
        }
        
        if ($db->query(
            "SELECT COUNT(*) FROM `gallery` WHERE `id_user`=?i AND `name`=?",
                       [$ank['id'], $name]
        )->el()) {
            $err = 'Альбом с таким названием уже существует';
        }
        
        if (!isset($err)) {
            $gallery_id = $db->query(
                "INSERT INTO `gallery` (`opis`, `time_create`, `id_user`, `name`, `time`, `pass`, `privat`, `privat_komm`)
VALUES(?, ?i, ?i, ?, ?i, ?, ?string, ?string)",
                                     [$msg, $time, $ank['id'], $name, $time, $pass, $input_post['privat'], $input_post['privat_komm']]
            )->id();
            $_SESSION['message'] = 'Фотоальбом успешно создан';
            header('Location: /foto/' . $ank['id'] . '/' . $gallery_id . '/');
            exit;
        }
    }
}