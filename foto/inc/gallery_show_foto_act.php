<?php
/*
* Установка аватара на главной
*/
if (isset($input_get['act']) && $input_get['act'] == 'avatar') {
    if ($user['id'] == $ank['id']) {
        /* Отправляем в ленту смену аватара */
        $avatar_id = $db->query(
            "SELECT `id` FROM `gallery_foto` WHERE `avatar`=? AND `id_user`=?i",
                             ['1', $user['id']]
        )->el();
        
        if ($avatar_id != $gallery_foto['id']) {
            /*---------друзьям автора--------------*/
            $q = $db->query(
                "SELECT fr.user, fr.lenta_avatar, ts.lenta_avatar as ts_avatar FROM `frends` fr 
JOIN tape_set ts ON ts.id_user=fr.user
WHERE fr.`frend`=?i AND fr.`lenta_avatar`=?i AND `i`=?i",
                            [$gallery_foto['id_user'], 1, 1]
            );
            while ($frend = $q->row()) {
                if ($frend['user'] != $user['id'] && $gallery_foto['id'] != $avatar_id && $frend['lenta_avatar'] == 1 && $frend['ts_avatar'] == 1) {
                    $db->query(
                        "INSERT INTO `tape` (`id_user`, `avtor`, `type`, `time`, `id_file`, `count`, `avatar`) VALUES(?i, ?i, ?, ?i, ?i, ?i, ?i)",
                               [$frend['user'], $gallery_foto['id_user'], 'avatar', $time, $gallery_foto['id'], 1, $avatar_id]
                    );
                }
            }

            $db->query("UPDATE `gallery_foto` SET `avatar`=? WHERE `id_user`=?i", ['0', $user['id']]);
            $db->query("UPDATE `gallery_foto` SET `avatar`=? WHERE `id`=?i", ['1', $gallery_foto['id']]);
            $db->query(
                "INSERT INTO `stena` (`id_user`,`id_stena`,`time`,`info`,`info_1`,`type`) VALUES(?i, ?i, ?i, ?, ?i, ?)",
                       [$user['id'], $user['id'], $time, 'новый аватар', $gallery_foto['id'], 'foto']
            );
            $_SESSION['message'] = 'Фотография успешно установлена на главной!';
        }
        
        header("Location: ?");
        exit;
    }
}
/*
* Удаление фотографии
*/
if (isset($input_get['act']) && $input_get['act'] == 'delete' && isset($input_get['ok'])) {
    if ($user['id'] != $ank['id']) {
        admin_log('Фотогалерея', 'Фотографии', 'Удаление фото пользователя "[url=/id' . $ank['id'] . ']' . $ank['nick'] . '[/url]"');
    }
    
    array_map('unlink', glob(H . 'sys/gallery/*/' . $gallery_foto['id'] . '.jpg'));
    $db->query("DELETE FROM `gallery_foto` WHERE `id`=?i", [$gallery_foto['id']]);
    
    $_SESSION['message'] = 'Фотография успешно удалена';
    header('Location: /foto/' . $ank['id'] . '/' . $gallery_foto['id'] . '/');
    exit;
}
/*
* Редактирование фотографии
*/
if (isset($input_get['act']) && $input_get['act'] == 'rename' && isset($input_get['ok']) && isset($input_post['name']) && isset($input_post['opis'])) {
    $name = trim($input_post['name']);
    if (!preg_match("#^([A-zА-я0-9\-\_\(\)\,\.\ \:])+$#ui", $name)) {
        $err = 'В названии темы присутствуют запрещенные символы';
    }
    if (strlen2($name) < 3) {
        $err = 'Короткое название';
    }
    if (strlen2($name) > 32) {
        $err = 'Название не должно быть длиннее 32-х символов';
    }
    $msg = trim($input_post['opis']);
    
    if (strlen2($msg) > 1024) {
        $err = 'Длина описания превышает предел в 1024 символа';
    }

    $metka = !empty($input_post['metka']) ?: 0;
    
    if (!isset($err)) {
        if ($user['id'] != $ank['id']) {
            admin_log('Фотогалерея', 'Фотографии', 'Переименование фото пользователя "[url=/id' . $ank['id'] . ']' . $ank['nick'] . '[/url]"');
        }
        $db->query(
            "UPDATE `gallery_foto` SET `name`=?, `metka`=?i, `opis`=? WHERE `id`=?i",
                   [$name, $metka, $msg, $gallery_foto['id']]
        );
        $gallery_foto = $db->query("SELECT * FROM `gallery_foto` WHERE `id`=?i", [$gallery_foto['id']])->row();
        
        $_SESSION['message'] = 'Фотография успешно переименована';
        header("Location: ?");
        exit;
    }
}
