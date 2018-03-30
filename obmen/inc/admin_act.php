<?php
// start delete dir & files
if (user_access('obmen_dir_delete') && isset($_GET['act']) && $_GET['act']=='delete' && isset($_GET['ok']) && $l!='/') {
    $res = $db->query('SELECT `id` FROM `obmennik_dir` WHERE `dir_osn` like "?e%"', [$l])->col();
    if (count($res)) {
        foreach ($res as $post_id) {
            $res2 = $db->query('SELECT `id` FROM `obmennik_files` WHERE `id_dir` = ?i', [$post_id])->col();
            foreach ($res2 as $post2_id) {
                if (is_file(H.'sys/obmen/files/'.$post2_id.'.dat')) {
                    unlink(H.'sys/obmen/files/'.$post2_id.'.dat');
                    array_map('unlink', glob(H . 'sys/obmen/screens/*/' . $post2_id . '.*'));
                }
            }
            $db->query('DELETE FROM `obmennik_dir` WHERE `id` = ?i', [$post_id]);
            $db->query('DELETE FROM `obmennik_files` WHERE `id_dir` NOT IN (
                   SELECT `id` FROM `obmennik_dir`)');
            $db->query('DELETE FROM `obmennik_komm` WHERE `id_file` NOT IN (
                   SELECT `id` FROM `obmennik_files`)');
            $db->query('DELETE FROM `user_music` WHERE `id_file` NOT IN (
                   SELECT `id` FROM `obmennik_files`) AND `dir`="obmen"');
            $db->query('OPTIMIZE TABLE `obmennik_dir` ,`obmennik_files` ,`obmennik_komm`, `user_music`;');
        }
    }
    $res = $db->query('SELECT `id` FROM `obmennik_files` WHERE `id_dir` = ?i', [$dir_id['id']])->col();
    foreach ($res as $post_id) {
        if (is_file(H.'sys/obmen/files/'.$post_id.'.dat')) {
            unlink(H.'sys/obmen/files/'.$post_id.'.dat');
        }
        array_map('unlink', glob(H . 'sys/obmen/screens/*/' . $post_id . '.*'));
    }
    $db->query('DELETE FROM `obmennik_dir` WHERE `id` = ?i', [$dir_id['id']]);
    $db->query('DELETE FROM `obmennik_files` WHERE `id_dir` NOT IN (
                   SELECT `id` FROM `obmennik_dir`)');
    $db->query('DELETE FROM `obmennik_komm` WHERE `id_file` NOT IN (
                   SELECT `id` FROM `obmennik_files`)');
    $db->query('DELETE FROM `user_music` WHERE `id_file` NOT IN (
                   SELECT `id` FROM `obmennik_files`) AND `dir`="obmen"');
    $db->query('OPTIMIZE TABLE `obmennik_dir` ,`obmennik_files` ,`obmennik_komm`, `user_music`;');

    $l = $dir_id['dir_osn'];
    msg('Папка успешно удалена');
    admin_log('Обменник', 'Удаление папки', 'Папка [b]' . $dir_id['name'] . '[/b] удалена');

    $dir_id = $db->query(
        'SELECT * FROM `obmennik_dir` WHERE `dir` = ? OR `dir` = ? OR `dir` = ? LIMIT ?i',
                       ['/' . $l, $l . '/', $l, 1])->row();
    $id_dir = (int)$dir_id['id'];
}
// start move dir
if (user_access('obmen_dir_edit') && isset($_GET['act']) && $_GET['act']=='mesto' && isset($_GET['ok']) && isset($_POST['dir_osn']) && $l!='/') {
    if ($_POST['dir_osn'] == null) {
        $err[] = 'Не выбран конечный путь';
    } else {
        $res = $db->query('SELECT * FROM `obmennik_dir` WHERE `dir_osn` LIKE "?e%"', [$l])->assoc();
        foreach ($res as $post) {
            $new_dir_osn = preg_replace('#^' . $l . '/#', $_POST['dir_osn'], $post['dir_osn']) . $dir_id['name'] . '/';
            $new_dir = $new_dir_osn . $post['name'];
            $db->query(
                'UPDATE `obmennik_dir` SET `dir`=?, `dir_osn`=? WHERE `id` = ?i',
                       [$new_dir . '/', $new_dir_osn, $post['id']]);
        }

        $l = $_POST['dir_osn'];
        $new_path = $l . $dir_id['name'] . '/';
        $db->query(
            'UPDATE `obmennik_dir` SET `dir`=?, `dir_osn`=? WHERE `id` =?i',
                   [$l . $dir_id['name'] . '/', $l, $dir_id['id']]);
        admin_log('Обменник', 'Изменение папки', 'Папка ' . $dir_id['name'] . ' перемещена');
        $dir_id = $db->query('SELECT * FROM `obmennik_dir` WHERE `id`=?i', [$dir_id['id']])->row();
        $id_dir = (int)$dir_id['id'];
        msg('Папка успешно перемещена');
        header('Refresh: 2; url=/obmen' . $new_path);
    }
}
// start rename dir
if (user_access('obmen_dir_edit') && isset($_GET['act']) && $_GET['act']=='rename' && isset($_GET['ok']) && isset($_POST['name']) && $l!='/') {
    if ($_POST['name'] == null) {
        $err[] = 'Введите название папки';
    } elseif (!preg_match("#^([A-zА-я0-9\-\_\(\)\ ])+$#ui", $_POST['name'])) {
        $err[] = 'В названии присутствуют запрещенные символы';
    } else {// Тут конец моих дум
        $newdir = retranslit(esc($_POST['name'], 1));

        if (!isset($err)) {
            if ($l != '/') {
                $l .= '/';
            }

            $downpath = preg_replace('#[^/]*/$#', null, $l);
            $db->query(
                'UPDATE `obmennik_dir` SET `name`=? WHERE `dir`=? OR `dir`=? OR `dir`=? LIMIT ?i',
                   [esc($_POST['name'], 1), '/' . $l, $l . '/', $l, 1]);
            msg('Папка успешно переименована');
            admin_log('Обменник', 'Изменение папки', 'Папка [b]' . $dir_id['name'] . '[/b] переименована в [b]' . esc($_POST['name'], 1) . '[/b]');

            $l=$downpath.$newdir;
            $dir_id = $db->query(
                'SELECT * FROM `obmennik_dir` WHERE `dir`=? OR `dir`=? OR `dir`=? LIMIT ?i',
                             ['/' . $l, $l . '/', $l, 1])->row();
            $id_dir = (int)$dir_id['id'];
            header('Refresh: 2; url=?');
        }
    }
}
// start create dir
if (user_access('obmen_dir_create') && isset($_GET['act']) && $_GET['act']=='mkdir' && isset($_GET['ok']) && isset($_POST['name'])) {
    if ($dir_id['upload'] == 0) {
        if ($_POST['name']==null) {
            $err= "Введите название папки";
        } elseif (!preg_match("#^([A-zА-я0-9\-\_\(\)\ ])+$#ui", $_POST['name'])) {
            $err[]='В названии присутствуют запрещенные символы';
        } else {
            $newdir=retranslit(esc($_POST['name'], 1));
            if (isset($_POST['upload']) && $_POST['upload']=='1') {
                $upload=1;
            } else {
                $upload=0;
            }

            if (!isset($_POST['ras']) || $_POST['ras']==null) {
                $upload=0;
            }
            $size=0;
            if ($upload==1 && isset($_POST['size']) && isset($_POST['mn'])) {
                $size=intval($_POST['size'])*intval($_POST['mn']);
                if ($upload_max_filesize<$size) {
                    $size=$upload_max_filesize;
                }
            } else {
                $upload=0;
            }
            $ras=esc(stripcslashes(htmlspecialchars($_POST['ras'], 1)));

            if (!isset($err)) {
                if ($l != '/') {
                    $l .= '/';
                }
                $db->query(
        'INSERT INTO `obmennik_dir` (`name`, `ras`, `maxfilesize`, `dir`, `dir_osn`, `upload`) VALUES (?, ?, ?i, ?, ?, ?string)',
               [esc($_POST['name'], 1), $ras, $size, $l . $newdir . '/', $l, $upload]);
                admin_log('Обменник', 'Создание папки', 'Создана папка [b]' . esc($_POST['name'], 1) . '[/b]');
                msg('Папка '.esc($_POST['name'], 1).' успешно создана');
                header('Refresh: 2; url=?');
            }
        }
    }
}
// start properties
if (user_access('obmen_dir_edit') && isset($_GET['act']) && $_GET['act']=='set' && isset($_GET['ok'])) {
    if (isset($_POST['upload']) && $_POST['upload'] == '1') {
        $upload = 1;
    } else {
        $upload = 0;
    }
    $name = trim($_POST['name']);
    if (empty($name)) {
        $err= "Введите название папки";
    } elseif (!preg_match("#^([A-zА-я0-9\-\_\(\)\ ])+$#ui", $_POST['name'])) {
        $err[]='В названии присутствуют запрещенные символы';
    }
    if (!isset($_POST['ras']) || $_POST['ras']==null) {
        $upload = 0;
    }
    $size = 0;
    if ($upload == 1 && isset($_POST['size']) && isset($_POST['mn'])) {
        $size=intval($_POST['size'])*intval($_POST['mn']);
        if ($upload_max_filesize < $size) {
            $size = $upload_max_filesize;
        }
    } else {
        $upload = 0;
    }

    $ras = esc(stripcslashes(htmlspecialchars($_POST['ras'], 1)));

    if (!isset($err)) {
        if ($l != '/') {
            $l .= '/';
        }
        $db->query(
        'UPDATE `obmennik_dir` SET `name`=?, `ras`=?, `maxfilesize`=?i, `upload`=?string WHERE `id`=?i',
               [$name, $ras, $size, $upload, $dir_id['id']]);
        admin_log('Обменник', 'Изменение папки', 'Изменены параметры папки [b]' . $dir_id['name'] . '[/b]');
        $dir_id = $db->query('SELECT * FROM `obmennik_dir` WHERE `id` =?i', [$dir_id['id']])->row();
        $id_dir = (int)$dir_id['id'];
        msg('Параметры папки успешно изменены');
        header('Refresh: 2; url=?');
    }
}
