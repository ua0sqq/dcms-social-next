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

/* Бан пользователя */
// ЗАЕБАЛ ЭТОТ ГОВНОКОД
if (isset($user) && $db->query("SELECT COUNT(*) FROM `ban` WHERE `razdel` = 'notes' AND `id_user` = '$user[id]' AND (`time` > '$time' OR `view` = '0' OR `navsegda` = '1')")->el()) {
    header('Location: /ban.php?'.SID);
    exit;
}

$ank['id'] = isset($user) ? $user['id'] : 0;

if (isset($_GET['id'])) {
    $ank['id'] = intval($_GET['id']);
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

if (isset($_GET['sort']) && $_GET['sort'] =='t') {
    $order='order by `time` desc';
} elseif (isset($_GET['sort']) && $_GET['sort'] =='c') {
    $order='order by `count` desc';
} else {
    $order='order by `time` desc';
}
if (isset($user) && $user['id']==$ank['id']) {
    echo'<div class="foot">';
    echo "<a href=\"add.php\">Создать дневник</a>";
    echo '</div>';
}
if (isset($_GET['sort']) && $_GET['sort'] =='t') {
    echo'<div class="foot">';
    echo"<b>Новые</b> | <a href='?id=$ank[id]&amp;sort=c'>Популярные</a>\n";
    echo '</div>';
} elseif (isset($_GET['sort']) && $_GET['sort'] =='c') {
    echo'<div class="foot">';
    echo"<a href='?id=$ank[id]&amp;sort=t'>Новые</a> | <b>Популярные</b>\n";
    echo '</div>';
} else {
    echo'<div class="foot">';
    echo"<b>Новые</b> | <a href='?id=$ank[id]&amp;sort=c'>Популярные</a>\n";
    echo '</div>';
}
$k_post=$db->query("SELECT COUNT(*) FROM `notes` WHERE `id_user` = '$ank[id]' ")->el();
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
$q=$db->query("SELECT * FROM `notes` WHERE `id_user` = '$ank[id]' $order LIMIT $start, $set[p_str]");
echo "<table class='post'>\n";
if ($k_post==0) {
    echo "  <div class='mess'>\n";
    echo "Нет записей\n";
    echo "  </div>\n";
}
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
    echo "<a href='list.php?id=$post[id]'>" . text($post['name']) . "</a>\n";
    echo " <span style='time'>(".vremja($post['time']).")</span> <br />\n";
    $k_n= $db->query("SELECT COUNT(*) FROM `notes` WHERE `id` = '$post[id]' AND `time` > '".$ftime."'")->el();
    echo "   </div>\n";
}
echo "</table>\n";
if (isset($_GET['sort'])) {
    $dop="sort=$_GET[sort]&amp;";
} else {
    $dop='';
}
if ($k_page>1) {
    str('?id=' . $ank['id'] . '&amp;'.$dop.'', $k_page, $page);
} // Вывод страниц
include_once '../../sys/inc/tfoot.php';
