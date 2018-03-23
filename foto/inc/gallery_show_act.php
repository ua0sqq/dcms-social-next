<?php
// Удаление альбома
if ((user_access('foto_alb_del') || isset($user) && $user['id'] == $ank['id'])
    && isset($input_get['act']) && $input_get['act'] == 'delete' && isset($input_get['ok'])) {
    $q = $db->query(
        "SELECT * FROM `gallery_foto` WHERE `id_gallery`=?i",
                    [$gallery['id']]
    )->assoc();
    
    foreach ($q as $post) {
        array_map('unlink', glob(H . 'sys/gallery/*/' . $post['id'] . '.jpg'));
    }
    
    $db->query(
        "DELETE FROM `gallery` WHERE `id`=?i",
                    [$gallery['id']]
    );
    $db->query('DELETE FROM `gallery_foto` WHERE `id_gallery` NOT IN(SELECT `id` FROM `gallery`)');
    $db->query('DELETE FROM `gallery_komm` WHERE `id_foto` NOT IN(SELECT `id` FROM `gallery_foto`)');
    $db->query('DELETE FROM `gallery_rating` WHERE `id_foto` NOT IN(SELECT `id` FROM `gallery_foto`)');
    $db->query('OPTIMIZE TABLE `gallery`, `gallery_foto`, `gallery_komm`, `gallery_rating`;');
    
    if ($user['id'] != $ank['id']) {
        admin_log('Фотогалерея', 'Фотоальбомы', 'Удаление альбома ' . $gallery['name'] . ' (фотографий: '.count($q).')');
    }

    $_SESSION['message'] = 'Фотоальбом успешно удален';
    header('Location: /foto/' . $ank['id'] . '/');
    exit;
}
// Загрузка фото
if (isset($user) && $user['id'] == $ank['id'] && isset($_FILES['file'])) {
    if ($imgc = @imagecreatefromstring(file_get_contents($_FILES['file']['tmp_name']))) {
        $name = strip_tags($input_post['name']);
        
        if (empty($name)) {
            $name = uniqid('foto_');
        }

        if (strlen2($name) < 3) {
            $err = 'Короткое название';
        }
        if (strlen2($name) > 32) {
            $err = 'Название не должно быть длиннее 32-х символов';
        }

        $metka = !empty($input_post['metka']) ?: 0;
        $msg = strip_tags($input_post['opis']);
        
        if (strlen2($msg) > 1024) {
            $err = 'Длина описания превышает предел в 1024 символов';
        }

        $img_x = imagesx($imgc);
        $img_y = imagesy($imgc);
        
        if ($img_x > $set['max_upload_foto_x'] || $img_y>$set['max_upload_foto_y']) {
            $err = 'Размер изображения превышает ограничения в '.$set['max_upload_foto_x'].'*'.$set['max_upload_foto_y'];
        }
        if (!isset($err)) {
            if (isset($input_get['avatar'])) {
                $db->query('UPDATE `gallery_foto` SET `avatar`=? WHERE `id_user`=?i', ['0', $user['id']]);
                $id_foto = $db->query(
                    "INSERT INTO `gallery_foto` (`id_gallery`, `name`, `ras`, `opis`, `id_user`,`avatar`, `metka`, `time`) VALUES (?i, ?, ?, ?, ?i, ?, ?i, ?i)",
                                      [$gallery['id'], $name, 'jpg', $msg, $user['id'], '1', $metka, $time]
                )->id();
            } else {
                $id_foto = $db->query(
                    "INSERT INTO `gallery_foto` (`id_gallery`, `name`, `ras`, `opis`, `id_user`, `metka`, `time`) VALUES (?i, ?, ?, ?, ?i, ?i, ?i)",
                                      [$gallery['id'], $name, 'jpg', $msg, $user['id'], $metka, $time]
                )->id();
            }

            $db->query(
                "UPDATE `gallery` SET `time`=?i WHERE `id`=?i",
                       [$time, $gallery['id']]
            );

            $foto['id'] = $id_foto;
            // Лента друзей
            $q = $db->query(
                "SELECT fr.user, fr.lenta_foto, ts.lenta_foto as ts_foto FROM `frends` fr 
JOIN tape_set ts ON ts.id_user=fr.user
WHERE fr.`frend`=?i AND fr.`lenta_foto`=?i AND `i`=?i",
                            [$gallery['id_user'], 1, 1]
            );
            while ($frend = $q->row()) {
                // Фильтр рассылки
                if ($frend['lenta_foto'] == 1 && $frend['ts_foto'] == 1) {
                    // Если грузим со страницы то отправляем как смену аватара
                    if (isset($input_get['avatar'])) {
                        if ($frend['user'] != $user['id'] && $foto['id'] != $avatar['id']) {
                            $db->query(
                                "INSERT INTO `tape` (`id_user`, `avtor`, `type`, `time`, `id_file`, `count`, `avatar`) VALUES(?i, ?i, ?, ?i, ?i, ?i, ?i)",
                                       [$frend['user'], $gallery['id_user'], 'avatar', $time, $foto['id'], 1, $avatar['id']]
                            );
                        }
                    } else {
                        // Если нет то просто шлем в ленту как новое фото
                        if (!$db->query(
                            "SELECT COUNT(*) FROM `tape` WHERE `id_user`=?i AND `type`=? AND `id_file`=?i",
                                        [$frend['user'], 'album', $gallery['id']]
                        )->el()) {
                            $db->query(
                                "INSERT INTO `tape` (`id_user`, `avtor`, `type`, `time`, `id_file`, `count`)
                                       VALUES(?i, ?i, ?, ?i, ?i, ?i)",
                                       [$frend['user'], $gallery['id_user'], 'album', $time, $gallery['id'], 1]
                            );
                        } else {
                            $db->query(
                                "UPDATE `tape` SET `count`=`count`+?i, `read`=?, `time`=?i WHERE `id_user`=?i AND `type`=? AND `id_file`=?i LIMIT ?i",
                                       [1, '0', $time, $frend['user'],  'album', $gallery['id'], 1]
                            );
                        }
                    }
                }
            }
            if ($img_x == $img_y) {
                $dstW = 48; // ширина
                $dstH = 48; // высота
            } elseif ($img_x > $img_y) {
                $prop = $img_x / $img_y;
                $dstW = 48;
                $dstH = ceil($dstW / $prop);
            } else {
                $prop = $img_y / $img_x;
                $dstH = 48;
                $dstW = ceil($dstH / $prop);
            }
            $screen = imagecreatetruecolor($dstW, $dstH);
            imagecopyresampled($screen, $imgc, 0, 0, 0, 0, $dstW, $dstH, $img_x, $img_y);
            imagejpeg($screen, H."sys/gallery/48/$id_foto.jpg", 90);
            imagedestroy($screen);
            if ($img_x == $img_y) {
                $dstW = 128; // ширина
                $dstH = 128; // высота
            } elseif ($img_x > $img_y) {
                $prop = $img_x / $img_y;
                $dstW = 128;
                $dstH = ceil($dstW / $prop);
            } else {
                $prop = $img_y / $img_x;
                $dstH = 128;
                $dstW = ceil($dstH / $prop);
            }
            $screen = imagecreatetruecolor($dstW, $dstH);
            imagecopyresampled($screen, $imgc, 0, 0, 0, 0, $dstW, $dstH, $img_x, $img_y);
            imagejpeg($screen, H."sys/gallery/128/$id_foto.jpg", 90);
            imagedestroy($screen);
            if ($img_x > 640 || $img_y > 640) {
                if ($img_x == $img_y) {
                    $dstW = 640; // ширина
                    $dstH = 640; // высота
                } elseif ($img_x > $img_y) {
                    $prop = $img_x / $img_y;
                    $dstW = 640;
                    $dstH = ceil($dstW / $prop);
                } else {
                    $prop = $img_y / $img_x;
                    $dstH = 640;
                    $dstW = ceil($dstH / $prop);
                }
                $screen = imagecreatetruecolor($dstW, $dstH);
                imagecopyresampled($screen, $imgc, 0, 0, 0, 0, $dstW, $dstH, $img_x, $img_y);
                imagejpeg($screen, H."sys/gallery/640/$id_foto.jpg", 90);
                imagedestroy($screen);
                // наложение копирайта
                $imgc=img_copyright($imgc);
                imagejpeg($imgc, H."sys/gallery/foto/$id_foto.jpg", 90);
            } else {
                imagejpeg($imgc, H."sys/gallery/640/$id_foto.jpg", 90);
                // наложение копирайта
                $imgc = img_copyright($imgc);
                imagejpeg($imgc, H."sys/gallery/foto/$id_foto.jpg", 90);
            }
            imagedestroy($imgc);
            crop(H."sys/gallery/640/$id_foto.jpg", H."sys/gallery/50/$id_foto.tmp.jpg");
            resize(H."sys/gallery/50/$id_foto.tmp.jpg", H."sys/gallery/50/$id_foto.jpg", 50, 50);
            if (is_file(H . 'sys/gallery/50/' . $id_foto . '.tmp.jpg')) {
                unlink(H . 'sys/gallery/50/' . $id_foto . '.tmp.jpg');
            }
            if (isset($input_get['avatar'])) {
                $_SESSION['message'] = 'Фото успешно установлено';
                header('Location: /info.php');
                exit;
            }
            $_SESSION['message'] = 'Фото успешно загружено';
            header('Location: /foto/' . $ank['id'] . '/' . $gallery['id'] . '/' . $id_foto . '/');
            exit;
        }
    } else {
        $err = 'Выбранный Вами формат изображения не поддерживается';
    }
}
// Редактирование альбома
if (isset($input_get['edit']) && $input_get['edit'] == 'rename' && isset($input_get['ok']) && (isset($input_post['name']) || isset($input_post['opis']))) {
    $name = trim($input_post['name']);
    $pass = trim($input_post['pass']);
    $privat = !empty($input_post['privat']) ? abs($input_post['privat']) : 0;
    $privat_komm = !empty($input_post['privat_komm']) ? abs($input_post['privat_komm']) : 0;
    
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

    if (!isset($err)) {
        if ($user['id'] != $ank['id']) {
            admin_log('Фотогалерея', 'Фотографии', 'Переименование альбома пользователя "[url=/id' . $ank['id'] . ']' . $ank['nick'] . '[/url]"');
        }
        $db->query(
            "UPDATE `gallery` SET `name`=?, `privat`=?string, `privat_komm`=?string, `pass`=?, `opis`=? WHERE `id`=?i LIMIT ?i",
                   [$name, $privat, $privat_komm, $pass, $msg, $gallery['id'],  1]
        );
        $_SESSION['message'] = 'Изменения успешно приняты';
        header('Location: /foto/' . $ank['id'] . '/?');
        exit;
    }
}
