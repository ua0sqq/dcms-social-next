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
include_once '../../sys/inc/files.php';

include_once '../../sys/inc/thead.php';
if (isset($user)) {
    $ank['id'] = $user['id'];
}
$get_id = filter_input_array(INPUT_GET, FILTER_VALIDATE_INT);
if ($get_id['id']) {
    $ank['id'] = $get_id['id'];
}
if (!isset($ank['id'])) {
    header('Location: /aut.php');
    exit;
}
// Определяем id автора папки
$ank_id = $db->query('SELECT `id` FROM `user` WHERE id=?i', [$ank['id']])->el();
if (!$ank_id) {
    header("Location: /index.php?".SID);
    exit;
}
 // Если у юзера нет основной папки создаем
if (isset($user) && $ank_id == $user['id'] && !$db->query(
    "SELECT COUNT( * ) FROM `user_files` WHERE `id_user`=?i AND `osn`=?i",
                [$ank_id, 1])->el()) {
    $dir_id = $db->query(
        "INSERT INTO `user_files` (`id_user`, `name`,  `osn`) VALUES(?i, ?, ?i)",
               [$ank_id, 'Файлы', 1])->id();
    header('Location: /user/personalfiles/' . $ank_id . '/' . $dir_id . '/' . SID);
}

 // Текущая папка
$dir = $db->query(
    "SELECT * FROM `user_files` WHERE `id_user`=?i AND `id`=?i",
                  [$ank_id, $get_id['dir']])->row();
 // Блокируем в случае отсутствия папки
if ($dir['id_user'] != $ank_id) {
    $set['title'] = '404 Not Found';
    title() . aut();
    echo '<div class="mess">'."\n".'Ошибка! Возможно папка была удалена, проверьте правильность адреса!'."\n".'</div>'."\n";
    include_once '../../sys/inc/tfoot.php';
}

$get_string = filter_input_array(INPUT_GET, FILTER_DEFAULT);

if (isset($get_id['id']) && isset($get_id['dir'])  && !isset($get_string['add']) && !isset($get_string['upload']) && !isset($get_id['id_file'])) {
    // Вывод папок
    include_once 'inc/folder.php';
} elseif (isset($get_id['id']) && isset($get_id['dir']) && isset($get_string['add']) && !isset($get_string['upload']) && !isset($get_id['id_file'])) {
    // Создание и редактирование папок
    include_once 'inc/folder.create.php';
} elseif (isset($get_id['id']) && isset($get_id['dir']) && isset($get_string['upload']) && !isset($get_id['id_file']) && !isset($get_string['add'])) {
    // Загрузка файла
    
    include_once 'inc/upload.wap.php';
} elseif (isset($get_id['id']) && isset($get_id['dir']) && isset($get_id['id_file']) && !isset($get_string['upload']) && !isset($get_string['add'])) {
    // Вывод файла пользователю
    include_once 'inc/file.php';
}
 // (c) Искатель
include_once '../../sys/inc/tfoot.php';
