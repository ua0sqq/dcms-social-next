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
if (isset($_GET['delete_folder'])) {
    // Удаление папки
    if (!isset($_GET['ok'])) {
        $folder = $db->query(
    'SELECT * FROM `user_files`  WHERE `id`=?i',
                     [$_GET['delete_folder']])->row();
        echo "<div class='mess'><center>";
        echo "Вы действительно желаете удалить <b>".htmlspecialchars($folder['name'])."</b><br />";
        echo "[<a href='?delete_folder=$folder[id]&amp;ok'><img src='/style/icons/ok.gif' alt='*'> Да</a>] [<a href='?'><img src='/style/icons/delete.gif' alt='*'> Нет</a>] \n";
        echo "</center></div>";

        include_once H . 'sys/inc/tfoot.php';
    }
    $a = 0;
    $b = 0;
    if (isset($_GET['ok'])) {
        $q=$db->query(
    'SELECT `id` FROM `user_files` WHERE `id_dires` LIKE "%?e%"',
              ['/' . $dir['id'] . '/' . intval($_GET['delete_folder']) . '/'])->col();
        if (!empty($q)) {
            foreach ($q as $post) {
                $a++;
                $q2=$db->query(
                'SELECT `id` FROM `obmennik_files` WHERE `my_dir`=?i',
                           [$post])->col();
                foreach ($q2 as $post2) {
                    unlink(H . 'sys/obmen/files/' . $post2 . '.dat');
                    array_map('unlink', glob(H . 'sys/obmen/screens/*/' . $post2 . '.*'));
                    $del_file_id[] = $post2;
                    $b++;
                }
                $del_user_file_id[] = $post;
            }

            if (!empty($del_file_id)) {
                $db->query(
                    'DELETE FROM `user_music` WHERE `id_file` IN (?li) AND `dir`="obmen"',
                           [$del_file_id]);
                $db->query(
                    "DELETE FROM `obmennik_files` WHERE `id` IN (?li)",
                           [$del_file_id]);
            }
            if (!empty($del_user_file_id)) {
                $db->query(
                    'DELETE FROM `user_files` WHERE `id` IN (?li)',
                           [$del_user_file_id]);
            }
        }
    
        $q2=$db->query(
            'SELECT id FROM `user_files` WHERE `id`=?i',
                       [$_GET['delete_folder']])->col();
        if (!empty($q2)) {
            foreach ($q2 as $post) {
                $a++;
                $q3=$db->query(
                'SELECT * FROM `obmennik_files` WHERE `my_dir`=?i',
                           [$post])->col();
                foreach ($q3 as $post2) {
                    unlink(H . 'sys/obmen/files/' . $post2 . '.dat');
                    array_map('unlink', glob(H . 'sys/obmen/screens/*/' . $post2 . '.*'));
                    $b++;
                }
                $del_user_file[] = $post;
            }

            if (!empty($del_user_file)) {
                $db->query(
                'DELETE FROM `user_files` WHERE `id` IN (?li)',
                       [$del_user_file]);
            }
        }
        $_SESSION['message']="Удалено \"папок $a \" и \"файлов $b\"";
        header("Location: ?".SID);
        exit;
    }
}
