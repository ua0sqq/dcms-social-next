<?php
// $name описание действий объекта
if ($type == 'them' && $post['avtor'] != $user['id']) {
    $name = 'создал' . ($avtor['pol'] == 1 ? null : "а") . ' в форуме тему ';
}
// Вывод блока с содержимым
if ($type == 'them') {
    if ($post['id_them']) {
        echo '<div class="nav1">';
        echo avatar($avtor['id']) . group($avtor['id']) . user::nick($avtor['id']) . medal($avtor['id']) . online($avtor['id']) . ' <a href="user.settings.php?id=' . $avtor['id'] . '">[!]</a> ' . $name .  $s1 . vremja($post['time']) . $s2;
        echo '</div>';
        echo '<div class="nav2">';
        echo ' <a href="/forum/' . $post['id_forum'] . '/' . $post['id_razdel'] . '/' . $post['id_them'] . '/"> ' . text($post['name_them']) . '</a> ';
        echo '<div class="text">' . output_text($post['text']) . '</div>';
    } else {
        echo '<div class="nav1">';
        echo avatar($avtor['id']) . group($avtor['id']) . user::nick($avtor['id']) . " <a href='user.settings.php?id=$avtor[id]'>[!]</a>";
        echo medal($avtor['id']) . online($avtor['id']);
        echo '</div>';
        
        echo '<div class="nav2">';
        echo 'Тема уже удалена =( ' . $s1 . vremja($post['time']) . $s2;
    }
}
