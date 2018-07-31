<?php
include_once '../../../sys/inc/start.php';
include_once '../../../sys/inc/compress.php';
include_once '../../../sys/inc/sess.php';
include_once '../../../sys/inc/settings.php';
include_once '../../../sys/inc/db_connect.php';
include_once '../../../sys/inc/ipua.php';
include_once '../../../sys/inc/fnc.php';
include_once '../../../sys/inc/user.php';

if (!isset($user) || $user['group_access'] < 2) {
    header('location: /err.php?err=403');
    exit;
}
//var_dump($user['group_access']);exit;
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($db->query(
    "SELECT COUNT( * ) FROM `spamus` WHERE `id`=?i",
                                     [$id])->el()) {
    $res = $db->query('select * from user_group where id=15')->vars();
    $post=$db->query(
    "SELECT spm.*, a.nick AS ank_nick, s.nick AS spam_nick
FROM `spamus` spm
JOIN `user` a ON a.id=spm.id_user
JOIN `user` s ON s.id=spm.id_spam WHERE spm.`id`=?i",
    [$id])->row();

    if ($user['group_access'] == 2) {
        $res['type'] = "chat";
    } elseif ($user['group_access'] == 3) {
        $res['type'] = "forum";
    } elseif ($user['group_access'] == 4) {
        $res['type'] = "obmen_komm";
    } elseif ($user['group_access'] == 5) {
        $res['type'] = "notes_komm";
    } elseif ($user['group_access'] == 6) {
        $res['type'] = "foto_komm";
    } elseif ($user['group_access'] > 6) {
        $res['type'] = 'all';
    } else {
        $res['type'] = 0;
    }

    if ($res['type'] == $post['razdel'] || $res['type'] == 'all') {
        admin_log('Жалобы', 'Удаление жалобы', 'Удаление жалобы от ' . $post['ank_nick'] . ' на ' . $post['spam_nick']);
        // отправка сообщения
        if (filter_input(INPUT_GET, 'otkl', FILTER_DEFAULT)) {
            $msg = "Вашу жалобу на пользователя [b]$post[spam_nick][/b] отклонил {$res[$user['group_access']]} [b]$user[nick][/b] [br][red]Будьте внимательней, в следующий раз это может привести к блокировке вашего аккаунта![/red]";
        } else {
            $msg = "Вашу жалобу на пользователя [b]$post[spam_nick][/b] рассмотрел {$res[$user['group_access']]} [b]$user[nick][/b]. [br][b]$post[ank_nick][/b] спасибо вам за вашу бдительность! .дружба.";
        }
        $db->query(
    "INSERT INTO `mail` (`id_user`, `id_kont`, `msg`, `time`) VALUES(?i, ?i, ?, ?i)",
           [0, $post['id_user'], $msg, time()]);
        $db->query(
    "DELETE FROM `spamus` WHERE `id`=?i",
           [$post['id']]);
    }
}

if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != null) {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
} else {
    header('Location: index.php?' . SID);
}
