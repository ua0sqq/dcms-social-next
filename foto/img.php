<?php

include_once '../sys/inc/start.php';
include_once '../sys/inc/sess.php';
include_once '../sys/inc/home.php';
include_once '../sys/inc/settings.php';
include_once '../sys/inc/db_connect.php';
include_once '../sys/inc/ipua.php';
include_once '../sys/inc/fnc.php';
include_once '../sys/inc/downloadfile.php';
//include_once '../sys/inc/user.php';
//header("Last-Modified: ".gmdate("D, d M Y H:i:s", filemtime($time))." GMT");
//header("Expires: ".gmdate("D, d M Y H:i:s", time() + 3600)." GMT");
//if (!isset($_GET['id']) || !isset($_GET['size'])) {
//    exit;
//}

$args = [
         'id' => [
                  'filter'  => FILTER_VALIDATE_INT,
                  'options' => [
                                'default'   => null,
                                'min_range' => 1,
                                ],
                  ],
         'size' => [
                  'filter'  => FILTER_VALIDATE_INT,
                  'options' => [
                                'default'   => 0,
                                'min_range' => 1,
                                ],
                  ]
         ];
$in_get = filter_input_array(INPUT_GET, $args);
unset($args);

if ($in_get['id']) {
    $ank = $db->query('SELECT glf.`id_gallery`, glf.`avatar`, g.`privat`, g.`pass`, u.`id`, u.`group_access` FROM `gallery_foto` glf
JOIN `gallery` g ON g.id=glf.id_gallery
JOIN `user` u ON u.id=g.id_user
WHERE glf.`id`=?i ', [$in_get['id']])->row();

    if (isset($_SESSION['id_user'])) {
        $user = $db->query(
        "SELECT `id`, `group_access` FROM `user` WHERE `id` =?i",
                       [$_SESSION['id_user']]
    )->row();
    } else {
        $user = ['id' => 0, 'group_access' => 0];
    }
    if ($ank['id'] != $user['id'] && ($user['group_access'] == 0 || $user['group_access'] <= $ank['group_access']) && $ank['avatar'] == 0) {
        // Настройки юзера
        $uSet = $db->query("SELECT `privat_str` FROM `user_set` WHERE `id_user`=?i",
						   [$ank['id']])->el();
        // Статус друг ли вы
        $frend = $db->query("SELECT COUNT(*) FROM `frends` WHERE  (`user`=?i AND `frend`=?i) OR  (`user`=?i AND `frend`=?i)",
							[$user['id'], $ank['id'], $ank['id'], $user['id']])->el();
        // Начинаем вывод если стр имеет приват настройки
		// Если только для друзей
        if ($uSet == 2 && $frend != 2) {
            $in_get['id'] = 0;
        } 
        // Если только для меня
        if ($uSet == 0) {
            $in_get['id'] = 0;
        }
        // Если установлена приватность альбома
        if ($ank['privat'] == 1 && ($frend != 2 || !isset($user)) && $user['group_access'] <= $ank['group_access'] && $user['id'] != $ank['id']) {
            $in_get['id'] = 0;
			header('Location: /foto/' . $ank['id'] . '/' . $ank['id_gallery'] . '/');
        } elseif ($ank['privat'] == 2 && $user['id'] != $ank['id'] && $user['group_access'] <= $ank['group_access']) {
            $in_get['id'] = 0;
			header('Location: /foto/' . $ank['id'] . '/' . $ank['id_gallery'] . '/');
        }
    
        /*--------------------Альбом под паролем-------------------*/
        if ($user['id'] != $ank['id'] && $ank['pass'] != null) {
            if (!isset($_SESSION['pass']) || $_SESSION['pass'] != $ank['pass']) {
                $in_get['id'] = 0;
				header('Location: /foto/' . $ank['id'] . '/' . $ank['id_gallery'] . '/');
            }
        }
        /*---------------------------------------------------------*/
    }
    if ($in_get['size'] == 48) {
        if (is_file(H.'sys/gallery/48/'.$in_get['id'].'.png')) {
            downloadfile(H.'sys/gallery/48/'.$in_get['id'].'.png', 'Фото.png', ras_to_mime('png'));
            exit;
        }
    
        if (is_file(H.'sys/gallery/48/'.$in_get['id'].'.gif')) {
            downloadfile(H.'sys/gallery/48/'.$in_get['id'].'.gif', 'Фото.gif', ras_to_mime('gif'));
            exit;
        }
    
        if (is_file(H.'sys/gallery/48/'.$in_get['id'].'.jpg')) {
            downloadfile(H.'sys/gallery/48/'.$in_get['id'].'.jpg', 'Фото.jpg', ras_to_mime('jpg'));
            exit;
        }
    }
    if ($in_get['size'] == 128) {
        if (is_file(H.'sys/gallery/128/'.$in_get['id'].'.jpg')) {
            downloadfile(H.'sys/gallery/128/'.$in_get['id'].'.jpg', 'Фото.jpg', ras_to_mime('jpg'));
            exit;
        }
    }
    if ($in_get['size'] == 50) {
        if (is_file(H.'sys/gallery/50/'.$in_get['id'].'.jpg')) {
            downloadfile(H.'sys/gallery/50/'.$in_get['id'].'.jpg', 'Фото.jpg', ras_to_mime('jpg'));
            exit;
        }
    }
    if ($in_get['size'] == 640) {
        if (is_file(H.'sys/gallery/640/'.$in_get['id'].'.jpg')) {
            downloadfile(H.'sys/gallery/640/'.$in_get['id'].'.jpg', 'Фото.jpg', ras_to_mime('jpg'));
            exit;
        }
    }
    if ($in_get['size'] == 0) {
        if (is_file(H.'sys/gallery/foto/'.$in_get['id'].'.jpg')) {
            downloadfile(H.'sys/gallery/foto/'.$in_get['id'].'.jpg', 'foto_'.$in_get['id'].'.jpg', ras_to_mime('jpg'));
            exit;
        }
    }
}
