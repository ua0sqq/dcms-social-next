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

if (isset($_GET['id']) && $db->query("SELECT COUNT(*) FROM `notes` WHERE `id` = '".intval($_GET['id'])."'")->el()) {
    $post=$db->query("SELECT * FROM `notes` WHERE `id` = '".intval($_GET['id'])."' LIMIT 1")->row();
    $ank=$db->query("SELECT * FROM `user` WHERE `id` = $post[id_user] LIMIT 1")->row();
    if (isset($user) && (user_access('notes_delete') || $user['id']==$ank['id'])) {
        $db->query("DELETE FROM `notes` WHERE `id` = '$post[id]'");
        $db->query("DELETE FROM `notes_count` WHERE `id_notes` = '$post[id]'");
        $db->query("DELETE FROM `notes_komm` WHERE `id_notes` = '$post[id]'");
        $db->query("DELETE FROM `mark_notes` WHERE `id_list` = '$post[id]'");
        $_SESSION['message']='Дневник успешно удален';
        header("Location: index.php?".SID);
        exit;
    }
} else {
    echo output_text('А как ты сюда попал? .дум.');
}
if (isset($_GET['komm']) && $db->query("SELECT COUNT(*) FROM `notes_komm` WHERE `id` = '".intval($_GET['komm'])."'")->el()) {
    $post=$db->query("SELECT * FROM `notes_komm` WHERE `id` = '".intval($_GET['komm'])."' LIMIT 1")->row();
    $notes=$db->query("SELECT * FROM `notes` WHERE `id` = '$post[id_notes]' LIMIT 1")->row();
    $ank=$db->query("SELECT * FROM `user` WHERE `id` = $notes[id_user] LIMIT 1")->row();
    if (isset($user) && (user_access('notes_delete') || $user['id']==$ank['id'])) {
        $db->query("DELETE FROM `notes_komm` WHERE `id` = '$post[id]'");
        $_SESSION['message']='Комментарий успешно удален';
        header("Location: " . htmlspecialchars($_SERVER['HTTP_REFERER']));
        exit;
    } else {
        echo output_text('А как ты сюда попал? .дум.');
    }
} else {
    echo output_text('А как ты сюда попал? .дум.');
}
if (isset($_GET['dir']) && $db->query("SELECT COUNT(*) FROM `notes_dir` WHERE `id` = '".intval($_GET['dir'])."'")->el()) {
    if (isset($user) && user_access('notes_delete')) {
        $q = $db->query("SELECT * FROM `notes_dir` WHERE `id` = '".intval($_GET['dir'])."' LIMIT 1");
        while ($post = $q->row()) {
            $notes=$db->query("SELECT * FROM `notes` WHERE `id_dir` = '$post[id]'")->row();
            $db->query("DELETE FROM `notes_count` WHERE `id_notes` = '$notes[id]'");
            $db->query("DELETE FROM `notes_komm` WHERE `id_notes` = '$notes[id]'");
            $db->query("DELETE FROM `mark_notes` WHERE `id_list` = '$notes[id]'");
        }
        $post = $db->query("SELECT * FROM `notes_dir` WHERE `id` = '".intval($_GET['dir'])."' LIMIT 1")->row();
        $db->query("DELETE FROM `notes_count` WHERE `id_notes` = '$notes[id]'");
        $db->query("DELETE FROM `notes_komm` WHERE `id_notes` = '$notes[id]'");
        $db->query("DELETE FROM `mark_notes` WHERE `id_list` = '$notes[id]'");
        $db->query("DELETE FROM `notes` WHERE `id_dir` = '$post[id]'");
        $db->query("DELETE FROM `notes_dir` WHERE `id` = '$post[id]'");
        $_SESSION['message']='Категория успешно удалена';
        header("Location: " . htmlspecialchars($_SERVER['HTTP_REFERER']));
        exit;
    } else {
        echo output_text('А как ты сюда попал? .дум.');
    }
} else {
    echo output_text('А как ты сюда попал? .дум.');
}
