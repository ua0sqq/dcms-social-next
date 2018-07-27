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

only_level(3);

if ($del = filter_input(INPUT_GET, 'del', FILTER_VALIDATE_INT)) {
    if ($id = $db->query(
    "SELECT `id` FROM `rules_p` WHERE `id`=?i",
                     [$del])->el()) {
        $post = $db->query(
		"SELECT r.id, u.id AS id_user, u.`level` FROM `rules_p` r
JOIN `user` u ON u.id=r.id_user WHERE r.`id`=?i",
                       [$id])->row();
        if ($user['level'] > $post['level']) {
            $db->query(
            "DELETE FROM `rules_p` WHERE `id`=?i",
                   [$post['id']]);
        }
    }
}

if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']!=null) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    header('Location: post.php?' . SID);
}
