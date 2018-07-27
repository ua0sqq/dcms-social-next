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

if ($id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)) {
    if ($db->query(
        "SELECT COUNT(*) FROM `status` WHERE `id`=?i",
                   [$id])->el()) {
        $post = $db->query(
    "SELECT st.id, u.id AS id_user, u.`level` FROM `status` st
JOIN `user` u ON u.id=st.id_user
WHERE st.`id`=?i",
                   [$id])->row();
        if (isset($user) && ($user['level'] > $post['level']) || $post['id_user'] == $user['id']) {
            $db->query("DELETE FROM `status` WHERE `id`=?i",
                       [$post['id']]);
        }
        $db->query("DELETE FROM `status_komm` WHERE `id_status`=?i",
                       [$post['id']]);
        $db->query("DELETE FROM `status_like` WHERE `id_status`=?i",
                       [$post['id']]);
        $_SESSION['message'] = 'Статус упешно удален';
        header('Location: /user/status/index.php?id=' . $post['id_user']);
        exit;
    } else {
        $_SESSION['err'] = 'Статус не найден';
        header('Location: /user/status/index.php?id=' . $user['id']);
    }
}
