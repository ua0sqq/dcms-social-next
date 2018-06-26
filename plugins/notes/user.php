<?php
/*
=======================================
Дневники для Dcms-Social
Автор: Искатель
---------------------------------------
Этот скрипт распостроняется по лицензии
движка Dcms-Social.
При использовании указывать ссылку на
оф. сайт http://dcms-social.ru
---------------------------------------
Контакты
ICQ: 587863132
http://dcms-social.ru
=======================================
*/
include_once '../../sys/inc/start.php';
include_once '../../sys/inc/compress.php';
include_once '../../sys/inc/sess.php';
include_once '../../sys/inc/home.php';
include_once '../../sys/inc/settings.php';
include_once '../../sys/inc/db_connect.php';
include_once '../../sys/inc/ipua.php';
include_once '../../sys/inc/fnc.php';
include_once '../../sys/inc/user.php';

$ank['id'] = isset($user) ? $user['id'] : 0;
$input_get = filter_input_array(
    INPUT_GET,
                                [
                                'id' => FILTER_VALIDATE_INT,
                                'sort' => FILTER_DEFAULT
                                ]
);
if (isset($input_get['id'])) {
    $ank['id'] = abs($input_get['id']);
}

if ($ank['id'] < 1) {
    include_once '../../sys/inc/thead.php';
    echo "<div class=\"err\">Доступ запрещен!</div>\n";
    include_once '../../sys/inc/tfoot.php';
    exit;
}

$ank=get_user($ank['id']);

$set['title']='Дневники ' . $ank['nick'] . '';
include_once '../../sys/inc/thead.php';
title();
aut(); // форма авторизации

if (isset($input_get['sort']) && $input_get['sort'] =='t') {
    $order=['time' => false];
} elseif (isset($input_get['sort']) && $input_get['sort'] =='c') {
    $order=['count' => false];
} else {
    $order=['time' => false];
}
if (isset($user) && $user['id']==$ank['id']) {
    echo'<div class="foot">';
    echo "<a href=\"add.php\">Создать дневник</a>";
    echo '</div>';
}
if (isset($input_get['sort']) && $input_get['sort'] =='t') {
    echo'<div class="foot">';
    echo"<b>Новые</b> | <a href='?id=$ank[id]&amp;sort=c'>Популярные</a>\n";
    echo '</div>';
} elseif (isset($input_get['sort']) && $input_get['sort'] =='c') {
    echo'<div class="foot">';
    echo"<a href='?id=$ank[id]&amp;sort=t'>Новые</a> | <b>Популярные</b>\n";
    echo '</div>';
} else {
    echo'<div class="foot">';
    echo"<b>Новые</b> | <a href='?id=$ank[id]&amp;sort=c'>Популярные</a>\n";
    echo '</div>';
}
$k_post=$db->query(
    "SELECT COUNT( * ) FROM `notes` WHERE `id_user`=?i",
                   [$ank['id']])->el();

if (!$k_post) {
    echo "<div class='mess'>\n";
    echo "Нет записей\n";
    echo "</div>\n";
} else {
    
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
$q=$db->query(
    "SELECT n.*, (
SELECT COUNT( * ) FROM `notes` WHERE `id`=n.id AND `time`>?i) new_note
FROM `notes` n WHERE n.`id_user`=?i ORDER BY ?o LIMIT ?i OFFSET ?i",
              [$ftime, $ank['id'], $order, $set['p_str'], $start]);

$num=0;
while ($post = $q->row()) {
    /*-----------зебра-----------*/
    if ($num==0) {
        echo '<div class="nav1">';
        $num=1;
    } elseif ($num==1) {
        echo '<div class="nav2">';
        $num=0;
    }
    /*---------------------------*/
    echo "<img src='/style/icons/dnev.png' alt='*'> ";
    echo "<a href='/plugins/notes/list.php?id=$post[id]'>" . text($post['name']) . "</a>\n";
    echo " <span style='time'>(".vremja($post['time']).")</span>\n";
    if ($post['new_note']) {
        echo " <img src='/style/icons/new.gif' alt='*'>";
    }
    echo "   </div>\n";
}

if (isset($input_get['sort'])) {
    $dop="sort=$input_get[sort]&amp;";
} else {
    $dop='';
}

if ($k_page>1) {
    str('?id=' . $ank['id'] . '&amp;'.$dop.'', $k_page, $page);
}
}
include_once '../../sys/inc/tfoot.php';
