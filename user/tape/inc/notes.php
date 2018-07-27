<?php
// $name описание действий объекта
if ($type == 'notes' && $post['avtor'] != $user['id']) { // дневники
    $name = 'создал' . ($avtor['pol'] == 1 ? null : "а") . ' новый дневник';
}
// Вывод блока с содержимым
if ($type  ==  'notes') {
    if ($post['id_note']) {
        echo '<div class="nav1">';
        echo avatar($avtor['id']) . group($avtor['id']) . user::nick($avtor['id']) . medal($avtor['id']) . online($avtor['id']) .
        ' <a href="user.settings.php?id=' . $avtor['id'] . '">[!]</a> ' . $name . '
		<b>' . text($post['name_note']) . '</b> ' . $s1 . vremja($post['time']) . $s2 . '<br />';
        echo '</div>';
        
        echo '<div class="nav2" ><div class="text" >';
        echo output_text($post['msg_note']) . '<br /></div>';
        
        echo '<a href="/plugins/notes/list.php?id=' . $post['id_note'] . '"><img src="/style/icons/bbl5.png" alt="*"/> (' . $post['komm_note'] . ')</a>';
    } else {
        echo '<div class="nav1">';
        echo avatar($avtor['id']) . group($avtor['id']) . user::nick($avtor['id']) . ' <a href="user.settings.php?id=' . $avtor['id'] . '">[!]</a>';
        echo medal($avtor['id']) . online($avtor['id']) . "";
        echo "</div>";
        echo '<div class="nav2">';
        echo "Дневник уже удален =( $s1 " . vremja($post['time']) . " $s2";
    }
}
