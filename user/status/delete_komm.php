<?php
include_once '../../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

if ($id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)) {
    if ($db->query(
    "SELECT COUNT( * ) FROM `status_komm` WHERE `id`=?i",
                                     [$id])->el()) {
        $post = $db->query(
    "SELECT stk.id, stk.id_status, u.`level` FROM `status_komm` stk  JOIN `user` u ON u.id=stk.id_user WHERE stk.`id`=?i",
                                     [$id])->row();
        $status = $db->query(
    "SELECT `id`, `id_user` FROM `status` WHERE `id`=?i",
                   [$post['id_status']])->row();
        if (isset($user) && ($user['level'] > $post['level']) || $status['id_user'] == $user['id']) {
            $db->query(
    "DELETE FROM `status_komm` WHERE `id`=?i",
           [$post['id']]);
            $_SESSION['message'] = 'Комментарий упешно удален';
        }
        header('Location: /user/status/komm.php?id=' . $status['id']);
        exit;
    } else {
        http_response_code(404);
        header('Location: /err.php?err=404');
    }
}
