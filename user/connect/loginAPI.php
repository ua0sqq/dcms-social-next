<?php
include_once '../../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';
include_once H . 'sys/inc/shif.php';

if (isset($_POST['token']) && !isset($user) && $users['network'] && $users['identity'] && $_POST['loginAPI'] == true) {
    if (!$db->query("SELECT COUNT( * ) FROM `user` WHERE `type_reg`=? AND `identity`=?",
                    [$users['network'], $users['identity']])->el()) {
        /*
        ================================
        Имя к id и пол
        ================================
        */
        if ($users['network'] == 'odnoklassniki') {
            $idi = 'ok';
        } else {
            $idi = null;
        }
        if ($users['sex'] == 2) {
            $pol = 1;
        } else {
            $pol = 0;
        }
        /*
        ================================
        Создаем ник
        ================================
        */
        $identity = $users['identity'];
        $identity = str_replace('http://www.facebook.com', '', $identity);
        $identity = str_replace('http://openid.yandex.ru', '', $identity);
        $identity = str_replace('http://vk.com', '', $identity);
        $identity = str_replace('http://odnoklassniki.ru', '', $identity);
        $identity = str_replace('http://my.mail.ru/mail', '', $identity);
        $identity = str_replace('/', '', $identity);
        $identity = str_replace('.', '', $identity);
        $identity = $idi . $identity;
        /*
        ================================
        Проверяем наличие ника в базе
        если есть то добавляем случайное
        число
        ================================
        */
        if ($db->query("SELECT COUNT( * ) FROM `user` WHERE `nick`=?",
                       [$identity])->el()) {
            $identity = $identity . '_' . mt_rand(1, 9999);
        }
        /*
        ================================
        Регаем пользователя
        ================================
        */
        $pass = $passgen;
        $db->query("INSERT INTO `user` (`set_nick`, `nick`, `pass`, `date_reg`, `date_last`, `pol`, `ank_city`, `ank_name`, `identity`, `type_reg`) VALUES(?i, ?, ?, ?i, ?, ?string, ?, ?, ?, ?)",
                   [1, $identity, shif($pass), time(), time(), $pol, $users['city'], $users['first_name'], $users['identity'], $users['network']]);
        $user=$db->query("SELECT `id`, `nick` FROM `user` WHERE `nick`=? AND `pass`=? LIMIT ?i",
                         [$identity, shif($pass), 1])->row();
        // отправка сообщения
        $msgg = 'Уважаем' . ($pol == 1 ? 'ый' : 'ая') . ' ' .
        $users['first_name'] . ', поздравляем с успешной регистрацией на сайте!  ' . ($pol == 1 ? '.дружба.' : '.ромашки.') . ' [br] Ваши регистрационные данные: [br] логин: ' . $identity . ' пароль: ' . $pass . '. [br]Изменить свой ник вы можете [url=/user/info/edit.php?set=nick]ТУТ[/url] [br]И в целях безопасности смените пароль [url=/secure.php]ТУТ[/url]';
        $db->query("INSERT INTO `mail` (`id_user`, `id_kont`, `msg`, `time`) VALUES(?i, ?i, ?, ?i)",
                   [0, $user['id'], $msgg, time()]);
        $_SESSION['id_user'] = $user['id'];
        setcookie('id_user', $user['id'], time()+60*60*24*365);
        setcookie('pass', cookie_encrypt($pass, $user['id']), time()+60*60*24*365);
        /*
        ================================
        Загружаем фото
        ================================
        */
        $photo = $users['photo_big'];
        $db->query("INSERT INTO `gallery` (`id_user`, `name`) VALUES(?i, ?)",
                   [$user['id'], 'Личные фото']);
        $gallery=$db->query("SELECT * FROM `gallery` WHERE `id_user`=?i  LIMIT ?i",
                            [$user['id'], 1])->row();
        // Наличие фото
        if ($photo) { 
            if ($imgc = @imagecreatefromstring(file_get_contents($photo))) {
                // имя файла без расширения)),1);
                $name=$identity; 
                $img_x=imagesx($imgc);
                $img_y=imagesy($imgc);
                if (!isset($err)) {
                    $id_foto=$db->query("INSERT INTO `gallery_foto` (`id_gallery`, `name`, `ras`, `type`, `id_user`,`avatar`) VALUES (?i, ?, ?, ?, ?i, ?string)",
                                        [$gallery['id'], $name, 'jpg', 'image/jpeg', $user['id'], 1])->id();
                    $db->query("UPDATE `gallery` SET `time`=?i WHERE `id`=?i",
                               [time(), $gallery['id']]);
                    $fot_id=$id_foto;
                    if ($img_x==$img_y) {
                        $dstW=48; // ширина
                        $dstH=48; // высота
                    } elseif ($img_x>$img_y) {
                        $prop=$img_x/$img_y;
                        $dstW=48;
                        $dstH=ceil($dstW/$prop);
                    } else {
                        $prop=$img_y/$img_x;
                        $dstH=48;
                        $dstW=ceil($dstH/$prop);
                    }
                    $screen=imagecreatetruecolor($dstW, $dstH);
                    imagecopyresampled($screen, $imgc, 0, 0, 0, 0, $dstW, $dstH, $img_x, $img_y);
                    imagejpeg($screen, H."sys/gallery/48/$id_foto.jpg", 90);
                    imagedestroy($screen);
                    if ($img_x==$img_y) {
                        $dstW=128; // ширина
                        $dstH=128; // высота
                    } elseif ($img_x>$img_y) {
                        $prop=$img_x/$img_y;
                        $dstW=128;
                        $dstH=ceil($dstW/$prop);
                    } else {
                        $prop=$img_y/$img_x;
                        $dstH=128;
                        $dstW=ceil($dstH/$prop);
                    }
                    $screen=imagecreatetruecolor($dstW, $dstH);
                    imagecopyresampled($screen, $imgc, 0, 0, 0, 0, $dstW, $dstH, $img_x, $img_y);
                    // наложение копирайта
					$screen=img_copyright($screen); 
                    imagejpeg($screen, H."sys/gallery/128/$id_foto.jpg", 90);
                    imagedestroy($screen);
                    if ($img_x>640 || $img_y>640) {
                        if ($img_x==$img_y) {
                            $dstW=640; // ширина
                            $dstH=640; // высота
                        } elseif ($img_x>$img_y) {
                            $prop=$img_x/$img_y;
                            $dstW=640;
                            $dstH=ceil($dstW/$prop);
                        } else {
                            $prop=$img_y/$img_x;
                            $dstH=640;
                            $dstW=ceil($dstH/$prop);
                        }
                        $screen=imagecreatetruecolor($dstW, $dstH);
                        imagecopyresampled($screen, $imgc, 0, 0, 0, 0, $dstW, $dstH, $img_x, $img_y);
                        // наложение копирайта
                        $screen=img_copyright($screen); 
                        imagejpeg($screen, H."sys/gallery/640/$id_foto.jpg", 90);
                        imagedestroy($screen);
                        // наложение копирайта
						$screen=img_copyright($screen); 
                        imagejpeg($imgc, H."sys/gallery/foto/$id_foto.jpg", 90);
                    } else {
                        // наложение копирайта
						$screen=img_copyright($screen); 
                        imagejpeg($imgc, H."sys/gallery/640/$id_foto.jpg", 90);
                        imagejpeg($imgc, H."sys/gallery/foto/$id_foto.jpg", 90);
                    }
                    imagedestroy($imgc);
                    crop(H."sys/gallery/640/$id_foto.jpg", H."sys/gallery/50/$id_foto.tmp.jpg");
                    resize(H."sys/gallery/50/$id_foto.tmp.jpg", H."sys/gallery/50/$id_foto.jpg", 50, 50);
                    if (is_file(H."sys/gallery/50/$id_foto.tmp.jpg")) {
                        unlink(H."sys/gallery/50/$id_foto.tmp.jpg");
                    }
                    $db->query("UPDATE `gallery_foto` SET `avatar`=? WHERE `id`=?i",
                               ['1', $id_foto]);
                }
            }
        }
        $db->query("UPDATE `user` SET `wall`=0 WHERE `id`=?i",
                   [$user['id']]);
    
        $_SESSION['message'] = 'Поздравляем с успешной регистрацией!';
        header('Location: /umenu.php?login=' . $user['nick'] . '&pass=' . $pass);
        exit;
    } else {
        $user_id=$db->query("SELECT `id` FROM `user` WHERE `type_reg`=? AND `identity`=? LIMIT ?i",
                         [$users['network'], $users['identity'], 1])->el();
    
        $_SESSION['id_user'] = $user['id'];
        setcookie('id_user', $user['id'], time() + 60 * 60 * 24 * 365);
        $db->query("UPDATE `user` SET `date_aut`=?i, `date_last`=?i WHERE `id`=?i",
                   [time(), time(), $user_id]);
    
        $_SESSION['message'] = 'Вы успешно авторизовались';
        header('Location: /info.php');
        exit;
    }
}
