<?php
// $name описание действий объекта
if ($type=='avatar' && $post['avtor'] != $user['id']) { // аватар
    if ($post['avatar']) {
        $name = 'сменил' . ($avtor['pol'] == 1 ? null : "а") . ' фото на главной';
    } else {
        $name = 'установил' . ($avtor['pol'] == 1 ? null : "а") . ' фото на главной';
    }
}
// Вывод блока с содержимым
if ($type == 'avatar') {

?>
<div class="nav1"><?php
    echo avatar($avtor['id']) . group($avtor['id']) . user::nick($avtor['id']);
    echo medal($avtor['id']) . online($avtor['id']) . ' <a href="user.settings.php?id=' . $avtor['id'] . '">[!]</a> ' . $name;
    echo $s1 . vremja($post['time']) . $s2;
?>
</div>
<div class="nav2"><?php
    if ($post['id_foto']) {
        echo '<strong>' . text($post['name_foto']) . '</strong>';
    }
    if ($post['id_foto_avatar']) {
        echo ' &raquo; <strong>' . text($post['name_foto_avatar']) . '</strong>';
    }
    if ($post['id_foto_avatar'] || $post['id_foto']) {
        echo '<br />';
    }
    
    
    if ($post['id_foto']) {
        echo '<a href="/foto/' . $avtor['id'] . '/' . $post['id_gallery_foto'] . '/' . $post['id_foto'] . '/">';
    }
    echo '<img style="max-width:50px; margin:3px;" src="/foto/foto50/' . $post['id_foto'] . '.jpg" alt="*" />';
    if ($post['id_foto']) {
        echo '</a>';
    }
    
    if ($post['avatar']) {
        echo '&nbsp;<img src="/style/icons/arRt2.png" alt="*"/> ';
        if ($post['id_foto_avatar']) {
            echo '<a href="/foto/' . $avtor['id'] . '/' . $post['id_gallery_avatar'] . '/' . $post['id_foto_avatar'] . '/">';
        }
        echo '<img style="max-width:50px; margin:3px;" src="/foto/foto50/' . $post['avatar'] . '.jpg" alt="*" />';
        if ($post['id_foto_avatar']) {
            echo '</a>';
        }
    }
    
    echo '<br />';
    
    if ($post['id_foto']) {
        echo '<a href="/foto/' . $avtor['id'] . '/' . $post['id_gallery_foto'] . '/' . $post['id_foto'] . '/"><img src="/style/icons/bbl5.png" alt="*"/> (' . $post['cnt_komm_foto'] . ')</a> ';
    }
}
