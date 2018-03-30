<?php
/*
=======================================
Личные файлы юзеров для Dcms-Social
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

include_once '../../sys/inc/files.php';

include_once '../../sys/inc/thead.php';
if (isset($user)) {
    $ank['id']=$user['id'];
}
if (isset($_GET['id'])) {
    $ank['id']=intval($_GET['id']);
}
if (!isset($ank['id'])) {
    header('Location: /aut.php');
    exit;
}

 // Определяем id автора папки
$ank_id = $db->query('SELECT id FROM `user` WHERE id=?i', [$ank['id']])->el();
if (!$ank_id) {
    header("Location: /index.php?".SID);
    exit;
}
 // Если у юзера нет основной папки создаем
if (!$db->query(
    "SELECT COUNT(*) FROM `user_files` WHERE `id_user`=?i AND `osn`=?i",
                [$ank_id, 1])->el()) {
    $db->query(
        "INSERT INTO `user_files` (`id_user`, `name`,  `osn`) values(?i, ?, ?i)",
               [$ank_id, 'Файлы', 1]);
    $dir = $db->query(
        "SELECT * FROM `user_files`  WHERE `id_user`=?i AND `osn`=?i",
                [$ank_id, 1])->row();
    header('Location: /user/personalfiles/' . $ank_id . '/' . $dir['id'] . '/' . SID);
}
 // Основная папка
$dir_osn = $db->query(
    "SELECT * FROM `user_files` WHERE `id_user`=?i AND `osn`=?i LIMIT ?i",
                      [$ank_id, 1, 1])->row();
 // Текущая папка
$dir = $db->query(
    "SELECT * FROM `user_files` WHERE `id_user`=?i AND `id`=?i",
                  [$ank_id, $_GET['dir']])->row();
 // Блокируем в случае отсутствия папки
if ($dir['id_user'] != $ank_id) {
    title() . aut();
    echo '<div class="err">'."\n".'Ошибка! Возможно папка была удалена, проверьте правильность адреса!'."\n".'</div>'."\n";
    include_once '../../sys/inc/tfoot.php';
}
if (isset($_GET['id']) && isset($_GET['dir'])  && !isset($_GET['add']) && !isset($_GET['upload']) && !isset($_GET['id_file'])) {
    // Вывод папок
    include_once 'inc/folder.php';
} elseif (isset($_GET['id']) && isset($_GET['dir']) && isset($_GET['add']) && !isset($_GET['upload']) && !isset($_GET['id_file'])) {
    // Создание и редактирование папок
    include_once 'inc/folder.create.php';
} elseif (isset($_GET['id']) && isset($_GET['dir']) && isset($_GET['upload']) && !isset($_GET['id_file']) && !isset($_GET['add'])) {
    // Загрузка файла
    
    include_once 'inc/upload.wap.php';
} elseif (isset($_GET['id']) && isset($_GET['dir']) && isset($_GET['id_file']) && !isset($_GET['upload']) && !isset($_GET['add'])) {
    // Вывод файла пользователю
    include_once 'inc/file.php';
}
 // (c) Искатель
include_once '../../sys/inc/tfoot.php';
