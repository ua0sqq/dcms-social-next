<?php
include_once '../../sys/inc/start.php';
include_once '../../sys/inc/compress.php';
include_once '../../sys/inc/sess.php';
include_once '../../sys/inc/home.php';
include_once '../../sys/inc/settings.php';
include_once '../../sys/inc/db_connect.php';
include_once '../../sys/inc/ipua.php';
include_once '../../sys/inc/fnc.php';
include_once '../../sys/inc/user.php';

$del = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($del && $db->query(
					   'SELECT COUNT(*) FROM `liders` WHERE `id_user`=?i',
                                [$del])->el()) {
    if (isset($user) && $user['level'] > 2) {
        $db->query(
            'DELETE FROM `liders` WHERE `id_user`=?i',
                    [$del]);

        $_SESSION['message'] = 'Пользователь удален из списка лидеров';
    }
}
if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != null) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    header('Location: index.php?' . SID);
}
exit;
