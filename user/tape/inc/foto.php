<?php
// $name описание действий объекта
if ($type == 'album' && $post['avtor'] != $user['id']) {
    $name = 'новые фото в альбоме';
}
// Вывод блока с содержимым
if ($type  ==  'album') {
    
    if ($post['count'] > 5) {
        $kol = '5';
        $kol2 = $post['count'] - 5;
    } else {
        $kol = $post['count'];
    }
    
    if ($post['id_gallery']) {
        echo '<div class="nav1">';
        echo avatar($avtor['id']) . group($avtor['id']) . user::nick($avtor['id']);
        echo medal($avtor['id']) . online($avtor['id']) . ' <a href="user.settings.php?id=' . $avtor['id'] . '">[!]</a> ' . $name . ' <img src="/style/icons/camera.png" alt=""/>  <a href="/foto/' . $avtor['id'] . '/' . $post['id_gallery'] . '/"><b>' . text($post['name_gallery']) . '</b></a> ';
        echo $s1 . vremja($post['time']) . $s2;
        echo '</div>';
        echo '<div class="nav2">';
        $as = $db->query("SELECT `id`, `ras` FROM `gallery_foto` WHERE `id_gallery`=?i ORDER BY `id` DESC LIMIT ?i",
                         [$post['id_gallery'], $kol]);
        
        while ($xx = $as->row()) {
            echo '<a href="/foto/' . $avtor['id'] . '/' . $post['id_gallery'] . '/' . $xx['id'] . '/"><img style=" margin: 2px;" src="/foto/foto50/' . $xx['id'] . '.' . $xx['ras'] . '" alt="*"/></a>';
        }
        
        if (isset($kol2)) {
            echo 'и еще ' . $kol2 . ' фото';
        }
    } else {
        echo '<div class="nav1">';
        echo "Альбом удален =(";
    }
}
