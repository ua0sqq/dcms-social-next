<?php
/*
==================================
Приватность станички пользователя
При использовании в других модулях
определяйте переменную $ank
::
$ank = get_user(object);
include H.'sys/add/user.privace.php';
==================================
*/

// Настройки юзера
$uSet = $db->query("SELECT * FROM `user_set` WHERE `id_user`=?i  LIMIT ?i", [$ank['id'], 1])->row();

// Статус друг ли вы
$frends = $db->query(
    'SELECT * FROM (
SELECT COUNT(*) frend FROM `frends` WHERE (`user`=?i AND `frend`=?i) OR (`user`=?i AND `frend`=?i))q, (
SELECT COUNT(*) new_frend FROM `frends_new` WHERE (`user`=?i AND `to`=?i) OR (`user`=?i AND `to`=?i))q1',
            [$user['id'], $ank['id'], $ank['id'], $user['id'], $user['id'], $ank['id'], $ank['id'], $user['id']]
)->row();

/*
* Если вы не выше по должности хозяина альбома,
* и вы не являетесь хозяином альбома
* и ваша должность равна или меньше должности хозяина альбома
* то приватность работает, либо она игнорируется
*/
if ($ank['id'] != $user['id'] && ($user['group_access'] == 0 || $user['group_access'] <= $ank['group_access'])) {
    // Начинаем вывод если стр имеет приват настройки
    if (($uSet['privat_str'] == 2 && $frends['frend'] != 2) || $uSet['privat_str'] == 0) {
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
    
    if ($uSet['privat_str'] == 2 && $frends['frend'] != 2) { // Если только для друзей
        echo '<div class="mess">';
        echo 'Просматривать страницу пользователя могут только его друзья!';
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
    if ($uSet['privat_str'] == 0) {
        echo '<div class="mess">';
        echo 'Пользователь полностью ограничил доступ к своей странице!';
        echo '</div>';
        
        include_once H.'sys/inc/tfoot.php';
        exit;
    }
}
