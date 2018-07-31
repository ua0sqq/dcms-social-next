<?php
include_once '../../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

only_reg('/aut.php');

if (isset($_GET['id'])) {
    $sid = intval($_GET['id']);
} else {
    $sid = $user['id'];
}

$ank = get_user($sid);
$set['title'] = 'Друзья ' . $ank['nick'] . ' онлайн'; // заголовок страницы
include_once H . 'sys/inc/thead.php';
title();
aut();
/*
==================================
Приватность станички пользователя
Запрещаем просмотр друзей
==================================
*/
$pattern = 'SELECT ust.privat_str FROM `user_set` ust WHERE ust.`id_user`=?i';
$data = [$ank['id']];
if (isset($user)) {
    $pattern = 'SELECT ust.privat_str, (
SELECT COUNT(*) FROM `frends` WHERE (`user`=?i AND `frend`=ust.`id_user`) OR (`user`=ust.`id_user` AND `frend`=?i)) frend, (
SELECT COUNT(*) FROM `frends_new` WHERE (`user`=?i AND `to`=ust.`id_user`) OR (`user`=ust.`id_user` AND `to`=?i)) new_frend
FROM `user_set` ust WHERE ust.`id_user`=?i';
    $data = [$user['id'], $user['id'], $user['id'], $user['id'], $ank['id']];
}

$uSet = $db->query($pattern, $data)->row();

if ($ank['id'] != $user['id'] && $user['group_access'] == 0) {
    // Начинаем вывод если стр имеет приват настройки
    if (($uSet['privat_str'] == 2 && $uSet['frend'] != 2) || $uSet['privat_str'] == 0) {
        if ($ank['group_access']>1) {
            echo "<div class='err'>".$ank['group_name']."</div>\n";
        }
        echo "<div class='nav1'>";
        echo group($ank['id'])." ";
        echo user::nick($ank['id'], 1, 1, 1);
        echo "</div>";
        echo "<div class='nav2'>";
        user::avatar($ank['id']);
        echo "</div>";
    }
    if ($uSet['privat_str'] == 2 && $uSet['frend'] != 2) { // Если только для друзей
        echo '<div class="mess">';
        echo 'Просматривать друзей пользователя могут только его друзья!';
        echo '</div>';
        // В друзья
        if (isset($user)) {
            echo '<div class="nav1">';
            if ($uSet['frend_new'] == 0 && $uSet['frend']==0) {
                echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?add=".$ank['id']."'>Добавить в друзья</a><br />\n";
            } elseif ($uSet['frend_new'] == 1) {
                echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?otm=$ank[id]'>Отклонить заявку</a><br />\n";
            } elseif ($uSet['frend'] == 2) {
                echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?del=$ank[id]'>Удалить из друзей</a><br />\n";
            }
            echo "</div>";
        }
        include_once H . 'sys/inc/tfoot.php';
        exit;
    }
    // Если закрыта
    if ($uSet['privat_str'] == 0) {
        echo '<div class="mess">';
        echo 'Пользователь запретил просматривать его друзей!';
        echo '</div>';
        include_once H . 'sys/inc/tfoot.php';
        exit;
    }
}

err();
// Panel
$sql = null;
if ($ank['id'] == $user['id']) {
    $sql = ', (
    SELECT COUNT( * ) new_frends FROM `frends_new` WHERE `to` =' . $ank['id'] . ')q3';
}
$cnt = $db->query(
    "SELECT * FROM (
    SELECT COUNT( * ) all_frends FROM `frends` WHERE `user`=?i AND `i`=?i)q1, (
    SELECT COUNT( * ) onl_frends FROM `frends`
    JOIN `user` ON `frends`.`frend`=`user`.`id` WHERE `frends`.`user`=?i AND `frends`.`i`=?i AND `user`.`date_last`>?i)q2?q",
                  [$ank['id'], 1, $ank['id'], 1, TIME_600, $sql])->row();

echo "<div id='comments' class='menus'>";
echo "<div class='webmenu'>";
echo "<a href='index.php?id=$ank[id]'>Все (".$cnt['all_frends'].")</a>";
echo "</div>";
echo "<div class='webmenu last'>";
echo "<a href='online.php?id=$ank[id]' class='activ'>Онлайн (".$cnt['onl_frends'].")</a>";
echo "</div>";
if ($ank['id'] == $user['id']) {
    echo "<div class='webmenu last'>";
    echo "<a href='new.php'>Заявки (".$cnt['new_frends'].")</a>";
    echo "</div>";
}
echo "</div>";
// End Panel

$k_post = $cnt['onl_frends'];
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
$q = $db->query(
    "SELECT usr.id, usr.date_last FROM `frends` frn
    JOIN `user` usr ON `frn`.`frend`=`usr`.`id`
    WHERE `frn`.`user`=?i AND `frn`.`i`=?i AND `usr`.`date_last`>?i ORDER BY `usr`.`date_last` DESC LIMIT ?i OFFSET ?i",
                [$ank['id'], 1, TIME_600, $set['p_str'], $start]);
if ($k_post==0) {
    echo '<div class="mess">';
    echo 'У вас нет друзей которые в сети';
    echo '</div>';
}
while ($frend = $q->row()) {
    /*-----------зебра-----------*/
    if ($num==0) {
        echo '<div class="nav1">';
        $num=1;
    } elseif ($num==1) {
        echo '<div class="nav2">';
        $num=0;
    }
    /*---------------------------*/
    echo '<table><td style="width:'.($webbrowser ? '85px;' : '55px;').'">';
    echo user::avatar($frend['id'], 1);
    echo '</td><td style="width:80%;">';
    echo " ".group($frend['id'])." \n";
    echo user::nick($frend['id'], 1, 1, 1);
    echo '<br/><img src="/style/icons/alarm.png"> '.($webbrowser ? 'Посл. активность:' : null).' '.vremja($frend['date_last']).' </td><td style="width:18px;">';
    if (isset($user)) {
        echo "<a href=\"/mail.php?id=$frend[id]\"><img src='/style/icons/pochta.gif' alt='*' /></a><br/>\n";
        if ($ank['id']==$user['id']) {
            echo "<a href='create.php?del=$frend[id]'><img src='/style/icons/delete.gif' alt='*' /></a>";
        }
    }
    echo '</td></table></div>';
}
if ($k_page>1) {
    str("?id=".$ank['id']."&amp;", $k_page, $page);
} // Вывод страниц
include_once H . 'sys/inc/tfoot.php';
