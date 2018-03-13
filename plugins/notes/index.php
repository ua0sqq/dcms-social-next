<?php
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
if (isset($user) && $db->query("SELECT COUNT(*) FROM `ban` WHERE `razdel` = 'notes' AND `id_user` = '$user[id]' AND (`time` > '$time' OR `view` = '0' OR `navsegda` = '1')")->el()) {
    header('Location: /ban.php?'.SID);
    exit;
}
$set['title']='Дневники';
include_once '../../sys/inc/thead.php';
title();
aut(); // форма авторизации

/*** Поле поиска ****/
echo "<div class='foot'><form method=\"post\" action=\"search.php?go\">";
echo "<table><td><input style='width:95%;' type=\"text\" name=\"usearch\" maxlength=\"16\" /></td><td> \n";
echo "<input type=\"submit\" value=\"Поиск\" /></td></table>";
echo "</form></div>\n";
/**** Панель навигации ****/
echo "<div id='comments' class='menus'>";
echo "<div class='webmenu'>";
echo "<a href='index.php' class='activ'>Дневники</a>";
echo "</div>";
echo "<div class='webmenu last'>";
echo "<a href='dir.php'>Категории</a>";
echo "</div>";
        if (isset($user)) {
            echo "<div class='webmenu last'>";
            echo "<a href='user.php?id=".$user['id']."'>Мои</a>";
            echo "</div>";
        }
echo "</div>";
/**** Сортировка*****/
$sortir = isset($_GET['sort']) ? $_GET['sort'] : null;
switch ($sortir) {
case 't':
$order='order by `time` desc';
echo"<div class='foot'><b>Новые</b> | <a href='?sort=c'>Популярные</a></div>\n";
break;
case 'c':
$order='order by `count` desc';
echo"<div class='foot'><a href='?sort=t'>Новые</a> | <b>Популярные</b></div>\n";
/* Сортировка популярных дневников по времени */
echo "<div class='nav2'>";
if (isset($_GET['new']) && $_GET['new']=='t') {
    echo"<b>Новые</b> | <a href='?sort=c&new=m'>За месяц</a> | <a href='?sort=c&new=v'>За всё время</a>\n";
    $new=" AND `time`>'".(time()-600)."' ";
} elseif (isset($_GET['new']) && $_GET['new']=='m') {
    echo"<a href='?sort=c&new=t'>Новые</a> | <b>За месяц</b> | <a href='?sort=c&new=v'>За всё время</a>\n";
    $new=" AND `time`>'".(time()-2592000)."' ";
} elseif (isset($_GET['new']) && $_GET['new']=='v') {
    echo"<a href='?sort=c&new=t'>Новые</a> | <a href='?sort=c&new=m'>За месяц</a> | <b>За всё время</b>\n";
    $new=null;
} elseif (isset($_GET['sort']) && $_GET['sort']=='c') {
    echo"<b>Новые</b> | <a href='?sort=c&new=m'>За месяц</a> | <a href='?sort=c&new=v'>За всё время</a>\n";
    $new=" AND `time`>'".(time()-600)."' ";
} else {
    $new=null;
}
echo "</div>";
/* Сортировка популярных дневников по времени */
break;
default:
$order='order by `time` desc';
 echo "<div class='foot'><b>Новые</b> | <a href='?sort=c'>Популярные</a></div>";
}
if (!isset($_GET['sort']) or $_GET['sort']!='c') {
    $new=null;
}
$k_post=$db->query("SELECT COUNT(*) FROM `notes` WHERE `private`='0'")->el();
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
$q=$db->query("SELECT * FROM `notes` $order LIMIT $start, $set[p_str]");
echo "<table class='post'>\n";
if ($k_post==0) {
    echo "  <div class='mess'>\n";
    echo "Нет записей\n";
    echo "  </div>\n";
}
while ($post = $q->row()) {
    /*-----------зебра-----------*/
    if ($num==0) {
        echo "  <div class='nav1'>\n";
        $num=1;
    } elseif ($num==1) {
        echo "  <div class='nav2'>\n";
        $num=0;
    }
    /*---------------------------*/
    echo group($post['id_user'])." ";
    echo user::nick($post['id_user'], 1, 1, 1)." : <a href='/plugins/notes/list.php?id=".$post['id']."'>".text($post['name'])."</a>";
    echo '<span style="float:right;color:#666;">'.vremja($post['time']).'</span><br/>';
    echo rez_text($post['msg'], 80)." <br/>\n";
    notes_sh($post['id']);
    echo "<br/><img src='/style/icons/uv.png'> <font color=#666>(".
    $db->query("SELECT COUNT(`id`)FROM `notes_komm` WHERE `id_notes`='$post[id]'")->el().") &bull;";
    echo " <a href='fav.php?id=".$post['id']."'><img src='/style/icons/add_fav.gif'> (".
    $db->query("SELECT COUNT(`id`)FROM `bookmarks` WHERE `id_object`='".$post['id']."' AND `type`='notes'")->el().")</a> &bull; ";
    echo " <img src='/style/icons/action_share_color.gif'> (".
    $db->query("SELECT COUNT(`id`)FROM `notes` WHERE `share_id`='".$post['id']."' AND `share_type`='notes'")->el().") </font>";
    echo "  </div>\n";
}
echo "</table>\n";
if (isset($_GET['sort'])) {
    $dop="sort=".my_esc($_GET['sort'])."&amp;";
} else {
    $dop='';
}
if ($k_page>1) {
    str('?'.$dop.'', $k_page, $page);
} // Вывод страниц
if (isset($user)) {
    echo "<div class='foot'><a href='add.php'> Создать запись</a></div>";
}
include_once '../../sys/inc/tfoot.php';
