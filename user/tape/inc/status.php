<?php
// $name описание действий объекта
if ($type == 'status_like' && $post['avtor'] != $user['id']) { // статус like
    $name = 'считает классным статус';
} elseif ($type=='status_like' && $post['avtor'] == $user['id']) {
    $name = 'считает классным ваш статус';
} elseif ($type=='status' && $post['avtor'] != $user['id']) {
    $name = 'установил' . ($avtor['pol'] == 1 ? null : "а") . ' новый статус';
}
// Вывод блока с содержимым
if ($type == 'status_like' || $type == 'status') {
    if ($post['msg_status']) {
        echo '<div class="nav1">';
        
        if ($post['ot_kogo']) {
            echo avatar($post['ot_kogo']) . group($post['ot_kogo']);
            echo user::nick($post['ot_kogo']) . medal($post['ot_kogo']) . online($post['ot_kogo']) . '  <a href="user.settings.php?id=' . $post['ot_kogo'] . '">[!]</a>';
        } else {
            echo avatar($avtor['id']) . group($avtor['id']);
            echo user::nick($avtor['id']) . medal($avtor['id']) . online($avtor['id']) . '  <a href="user.settings.php?id=' . $avtor['id'] . '">[!]</a>';
        }
        
        echo $name;
        
        if ($type != 'status') {
            echo avatar($avtor['id']) . group($avtor['id']);
            echo '<a href="/id' . $avtor['id'] . '">' . $avtor['nick'] . '</a>  ' . medal($avtor['id']) . online($avtor['id']) . ' ';
        }
        
        echo $s1 . vremja($post['time']) . $s2;
        echo '</div>';
        echo '<div class="nav2">';
        
        echo '<div class="st_1"></div>';
        echo '<div class="st_2">';
        echo output_text($post['msg_status']) . '<br />';
        echo '</div>';
        
        echo '<a href="/user/status/komm.php?id=' . $post['id_file'] . '"><img src="/style/icons/bbl4.png" alt=""/> ' .
        $post['komm_status'] . '</a>';
        
        $l = $post['like_status'];

        if (isset($user) && $user['id'] != $avtor['id']) {
            if ($user['id'] != $avtor['id'] && !$post['user_like_status']) {
                echo ' <a href="?likestatus=' . $post['id_file'] . '&amp;page=' . $page . '"><img src="/style/icons/like.gif" alt=""/>Класс!</a> &bull; ';
                $like = $l;
            } else {
                echo ' <img src="/style/icons/like.gif" alt=""/> Вы и ';
                $like = $l - 1;
            }
        } else {
            echo ' <img src="/style/icons/like.gif" alt=""/> ';
            $like = $l;
        }
        
        echo '<a href="/user/status/like.php?id=' . $post['id_file'] . '">' . $like . ' чел.</a>';
    } else {
        echo '<div class="nav1">';
        echo avatar($avtor['id']) . group($avtor['id']) . user::nick($avtor['id']);
        echo medal($avtor['id']) . online($avtor['id']) . ' <a href="user.settings.php?id=' . $avtor['id'] . '">[!]</a><br />';
        echo smiles('Статус уже удален :(');
    }
}
