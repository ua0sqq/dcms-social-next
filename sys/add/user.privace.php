<?php
$pattern = 'SELECT ust.privat_str FROM `user_set` ust WHERE ust.`id_user`=?i';
$data = [$ank['id']];
if (isset($user)) {
    $pattern = 'SELECT ust.privat_str, (
SELECT COUNT(*) FROM `frends` WHERE (`user`=?i AND `frend`=ust.`id_user`) OR (`user`=ust.`id_user` AND `frend`=?i)) frend, (
SELECT COUNT(*) FROM `frends_new` WHERE (`user`=?i AND `to`=ust.`id_user`) OR (`user`=ust.`id_user` AND `to`=?i)) new_frend
FROM `user_set` ust WHERE ust.`id_user`=?i';
    $data = [$user['id'], $user['id'], $user['id'], $user['id'], $ank['id']];
}

$frends = $db->query($pattern, $data)->row();
if ($ank['id'] != $user['id'] && ($user['group_access'] == 0 || $user['group_access'] <= $ank['group_access'])) {
    // Начинаем вывод если стр имеет приват настройки
    if (($frends['privat_str'] == 2 && $frends['frend'] != 2) || $frends['privat_str'] == 0) {
        if ($ank['group_access'] > 1) {
            echo '<div class="err">' . $ank['group_name'] . '</div>';
        }
        
        echo '<div class="nav1">';
        echo group($ank['id']) . user::nick($ank['id'], 0) . medal($ank['id']) . online($ank['id']);
        echo '</div>';
        
        echo '<div class="nav2">';
        echo avatar($ank['id']);
        echo '</div>';
    }
    // Если только для друзей
    if ($frends['privat_str'] == 2 && $frends['frend'] != 2) { 
        echo '<div class="mess">';
        echo 'Просматривать альбом пользователя могут только его друзья';
        echo '</div>';
        
        // В друзья
        if (isset($user)) {
            echo '<div class="nav1">';
            echo '<img src="/style/icons/druzya.png" alt="*"/>';
            
            if ($frends['new_frend'] == 0 && $frends['frend']==0) {
                echo '<a href="/user/frends/create.php?add=' . $ank['id'] . '">Добавить в друзья</a><br />';
            } elseif ($frends['new_frend'] == 1) {
                echo '<a href="/user/frends/create.php?otm=' . $ank['id'] . '">Отклонить заявку</a><br />';
            } elseif ($frends['frend'] == 2) {
                echo '<a href="/user/frends/create.php?del=' . $ank['id'] . '">Удалить из друзей</a><br />';
            }
            
            echo '</div>';
        }
        include_once H.'sys/inc/tfoot.php';
        exit;
    }
    
    // Если cтраница закрыта
    if ($frends['privat_str'] == 0) {
        echo '<div class="mess">';
        echo 'Пользователь полностью ограничил доступ к своей странице!';
        echo '</div>';
        http_response_code(403);
        include_once H.'sys/inc/tfoot.php';
        exit;
    }
}
