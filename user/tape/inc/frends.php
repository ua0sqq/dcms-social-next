<?php
// $name описание действий объекта
if ($type == 'frends' && $post['avtor'] != $user['id']) {
    $name = 'добавил' . ($avtor['pol'] == 1 ? null : "а") . ' в друзья';
}
// Вывод блока с содержимым
if ($type == 'frends') {
    $frend = get_user($post['id_file']);
    
    if ($frend['id']) {
        echo '<div class="nav1">';
        echo avatar($avtor['id']) . group($avtor['id']) . user::nick($avtor['id']);
        echo ' ' . medal($avtor['id']) . ' ' . online($avtor['id']) . ' <a href="user.settings.php?id=' . $avtor['id'] . '">[!]</a> ' . $name . ' ';
        
        echo avatar($frend['id']) . group($frend['id']) . user::nick($frend['id']);
        echo ' ' . medal($frend['id']) . ' ' . online($frend['id']) . ' ';
        
        echo $s1 . vremja($post['time']). $s2;
        echo '</div>';
        echo '<div class="nav2">';
    } else {
        echo '<div class="nav1">';
        echo 'Запись уничтожена =(';
    }
}
