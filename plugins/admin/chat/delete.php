<?php
include_once '../../../sys/inc/start.php';
include_once '../../../sys/inc/compress.php';
include_once '../../../sys/inc/sess.php';
include_once '../../../sys/inc/settings.php';
include_once '../../../sys/inc/db_connect.php';
include_once '../../../sys/inc/ipua.php';
include_once '../../../sys/inc/fnc.php';
include_once '../../../sys/inc/user.php';

if (user_access('guest_delete')) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id && $db->query(
    "SELECT COUNT( * ) FROM `adm_chat` WHERE `id`=?i", [$id])->el()) {
        $post = $db->query("SELECT a.id, a.id_user, u.nick FROM `adm_chat` a
JOIN `user` u ON u.id=a.id_user
WHERE a.`id`=?i", [$id])->row();

        if ($post['id']) {
            admin_log('Гостевая', 'Удаление сообщения', 'Удаление сообщения от ' . $post['nick']);
            $db->query(
            "DELETE FROM `adm_chat` WHERE `id`=?i",
                   [$post['id']]);
            $db->query("OPTIMIZE TABLE `adm_chat`;");
            $_SESSION['message'] = 'Сообщение успешно удалено';
        } else {
            $_SESSION['err'] = 'Empty query!';
        }
  
        header('Location: index.php?');
        exit;
    }
} else {
    http_response_code(403);
    header('Location: /err.php?err=403');
}
