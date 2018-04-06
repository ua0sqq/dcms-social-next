<?php
/*
=======================================
Дневники для Dcms-Social
Автор: Искатель
---------------------------------------
Этот скрипт распостроняется по лицензии
движка Dcms-Social.
При использовании указывать ссылку на
оф. сайт http://dcms-social.ru
---------------------------------------
Контакты
ICQ: 587863132
http://dcms-social.ru
=======================================
*/
include_once '../../sys/inc/start.php';
include_once '../../sys/inc/compress.php';
include_once '../../sys/inc/sess.php';
include_once '../../sys/inc/home.php';
include_once '../../sys/inc/settings.php';
include_once '../../sys/inc/db_connect.php';
include_once '../../sys/inc/ipua.php';
include_once '../../sys/inc/fnc.php';
include_once '../../sys/inc/user.php';

if (isset($_GET['id']) && $db->query("SELECT COUNT( * ) FROM `notes` WHERE `id`=?i",
                                     [$_GET['id']])->el()) {
    $post = $db->query("SELECT id, id_user FROM `notes` WHERE `id`=?i",
                                     [$_GET['id']])->row();
    //$ank=$db->query("SELECT * FROM `user` WHERE `id` = $post[id_user] LIMIT 1")->row();
    if (isset($user) && (user_access('notes_delete') || $user['id'] == $post['id_user'])) {
        $db->query("DELETE FROM `notes` WHERE `id`=?i",
                   [$post['id']]);
        $db->query("DELETE FROM `notes_count` WHERE `id_notes`=?i",
                   [$post['id']]);
        $db->query("DELETE FROM `notes_komm` WHERE `id_notes`=?i",
                   [$post['id']]);
        $db->query("DELETE FROM `bookmarks` WHERE `type`='notes'  AND `id_object`=?i",
                   [$post['id']]);
        $db->query("DELETE FROM `tape` WHERE `type`='notes'  AND `id_file`=?i",
                   [$post['id']]);
        $db->query('OPTIMIZE TABLE `notes`, `notes_count`, `notes_komm`, `bookmarks`, `tape`;');
        
        $_SESSION['message'] = 'Дневник успешно удален';
        header("Location: index.php?".SID);
        exit;
    }
} else {
    echo output_text('А как ты сюда попал? .дум.');
}

if (isset($_GET['komm']) && $db->query("SELECT COUNT(*) FROM `notes_komm` WHERE `id`=?i",
                                       [$_GET['komm']])->el()) {
    $post=$db->query("SELECT nkm.id, n.id_user FROM `notes_komm` nkm
JOIN `notes` n ON n.id=nkm.id_notes WHERE nkm.`id`=?i",
                     [$_GET['komm']])->row();
    
    if (isset($user) && (user_access('notes_delete') || $user['id']==$post['id_user'])) {
        $db->query("DELETE FROM `notes_komm` WHERE `id`=?i",
                   [$post['id']]);
        
        $_SESSION['message']='Комментарий успешно удален';
        header("Location: " . htmlspecialchars($_SERVER['HTTP_REFERER']));
        exit;
    } else {
        echo output_text('А как ты сюда попал? .дум.');
    }
} else {
    echo output_text('А как ты сюда попал? .дум.');
}
$db->setDebug('mydebug');
if (isset($_GET['dir']) && $db->query("SELECT COUNT(*) FROM `notes_dir` WHERE `id`=?i",
                                      [$_GET['dir']])->el()) {
    $ar = 0;
    if (isset($user) && user_access('notes_delete')) {
        $ar = $db->query("DELETE FROM `notes_dir` WHERE `id`=?i",
                                      [$_GET['dir']])->ar();
        $res = $db->query("SELECT `id` FROM `notes` WHERE `id_dir` NOT IN(SELECT `id` FROM `notes_dir`) AND `id_dir` IS NOT NULL")->col();
        foreach ($res as $post_id) {
            $list[] = $post_id;
        }
        $ar += $db->query("DELETE FROM `notes_count` WHERE `id_notes` IN(?li)", [$list])->ar();
        $ar += $db->query("DELETE FROM `notes_komm` WHERE `id_notes` IN(?li)", [$list])->ar();
        $ar += $db->query("DELETE FROM `notes_like` WHERE `id_notes` IN(?li)", [$list])->ar();
        $ar += $db->query("DELETE FROM `bookmarks` WHERE `type`='notes'  AND `id_object` IN(?li)", [$list])->ar();
        $ar += $db->query("DELETE FROM `tape` WHERE `type`='notes'  AND `id_file` IN(?li)", [$list])->ar();
        $ar += $db->query("DELETE FROM `notes` WHERE `id` IN(?li)", [$list])->ar();

        $_SESSION['message'] = 'Категория и '.$ar.' записей в базе, успешно удалены';
        header("Location: " . htmlspecialchars($_SERVER['HTTP_REFERER']));
        exit;
    } else {
        echo output_text('А как ты сюда попал? .дум.');
    }
} else {
    echo output_text('А как ты сюда попал? .дум.');
}
