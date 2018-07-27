<?php
// $name описание действий объекта
if ($type == 'obmen' && $post['avtor'] != $user['id']) {
    $name = 'новые файлы в папке';
}
// Вывод блока с содержимым
if ($type == 'obmen') {
    
    if ($post['count'] > 5) {
        $kol = '5';
        $kol2 = $post['count'] - 5;
    } else {
        $kol = $post['count'];
    }
    
    echo '<div class="nav1">';
    echo avatar($avtor['id']) . group($avtor['id']) . user::nick($avtor['id']) . medal($avtor['id']) . online($avtor['id']) .
    ' <a href="user.settings.php?id=' . $avtor['id'] . '">[!]</a> ' . $name .
	' <img src="/style/themes/' . $set['set_them'] . '/loads/14/dir.png" alt="*"/> <a href="/user/personalfiles/' . $avtor['id'] . '/' . $post['id_file'] . '/">' .
	text($post['user_dir_name']) . '</a>  ' . $s1 . vremja($post['time']) . $s2;
    echo '</div>';        
    
    echo '<div class="nav2">';
    $files = $db->query("SELECT * FROM `obmennik_files` WHERE `my_dir`=?i ORDER BY `id` DESC LIMIT ?i",
						[$post['id_file'], $kol]);
    
    while ($file = $files->row()) {
        if ($file['id']) {
            $ras = $file['ras'];
            
            if (is_file(H.'style/themes/' . $set['set_them'] . '/loads/14/' . $ras . '.png')) { // Иконка файла
                echo '<img src="/style/themes/' . $set['set_them'] . '/loads/14/' . $ras . '.png" alt="*" /> ';
            } else {
                echo '<img src="/style/themes/' . $set['set_them'] . '/loads/14/file.png" alt="*" /> ';
            }
            echo '<a href="/user/personalfiles/' . $file['id_user'] . '/' . $post['id_file'] . '/?id_file=' . $file['id'] . '&amp;page=1"><b>' . text($file['name']) . '.' . $ras . '</b></a> (' . size_file($file['size']) . ')<br />';
        } else {
            echo avatar($avtor['id']) . group($avtor['id']) . user::nick($avtor['id']) . '  <a href="user.settings.php?id=' . $avtor['id'] . '">[!]</a>';
            echo medal($avtor['id']) . online($avtor['id']) . '<br />';
            echo 'Файл уже удален =(<br />';
        }
    }
    if (isset($kol2)) {
        echo 'и еще ' . $kol2 . ' файлов';
    }
}
