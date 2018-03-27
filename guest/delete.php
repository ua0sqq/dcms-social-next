<?php
include_once '../sys/inc/start.php';
include_once '../sys/inc/compress.php';
include_once '../sys/inc/sess.php';
include_once '../sys/inc/home.php';
include_once '../sys/inc/settings.php';
include_once '../sys/inc/db_connect.php';
include_once '../sys/inc/ipua.php';
include_once '../sys/inc/fnc.php';
include_once '../sys/inc/user.php';

$get_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (isset($get_id) && $db->query(
    'SELECT COUNT(*) FROM `guest` WHERE `id`=?i',
                                 [$get_id])->el()) {
    $post = $db->query(
        'SELECT * FROM `guest` WHERE `id`=?i',
                       [$get_id])->row();
    if ($post['id_user'] == 0) {
        $ank['id'] = 0;
        $ank['pol'] = 'guest';
        $ank['level'] = 0;
        $ank['nick'] = 'Гость';
    } else {
        $ank = get_user($post['id_user']);
    }
    
    if (user_access('guest_delete')) {
        admin_log('Гостевая', 'Удаление сообщения', 'Удаление сообщения от ' . $ank['nick']);
        $db->query('DELETE FROM `guest` WHERE `id`=?i', [$post['id']]);
    }
}

if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != null) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    header('Location: index.php?' . SID);
}
