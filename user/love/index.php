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

$set['title'] = 'Знакомства'; // заголовок страницы
include_once '../../sys/inc/thead.php';
title();
aut();

if (isset($_GET['orders']) && $_GET['orders']=='man') {
    $_SESSION['orders'] = " AND `pol` = '1'";
    $_SESSION['nav1'] = 'activ';
    $_SESSION['nav2'] = null;
    $_SESSION['nav3'] = null;
} elseif (isset($_GET['orders']) && $_GET['orders']=='woman') {
    $_SESSION['orders'] = " AND `pol` = '0'";
    $_SESSION['nav1'] = null;
    $_SESSION['nav2'] = 'activ';
    $_SESSION['nav3'] = null;
} elseif (isset($_GET['orders']) && $_GET['orders']=='all') {
    $_SESSION['orders'] = null;
    $_SESSION['nav1'] = null;
    $_SESSION['nav2'] = null;
    $_SESSION['nav3'] = 'activ';
} elseif (!isset($_SESSION['orders'])) {
    $_SESSION['orders'] = null;
    $_SESSION['nav1'] = null;
    $_SESSION['nav2'] = null;
    $_SESSION['nav3'] = 'activ';
}
    $cel = "(
	`ank_lov_1` = '1' OR 
	`ank_lov_2` = '1' OR 
	`ank_lov_3` = '1' OR 
	`ank_lov_4` = '1' OR 
	`ank_lov_5` = '1' OR 
	`ank_lov_6` = '1' OR 
	`ank_lov_7` = '1' OR 
	`ank_lov_8` = '1' OR 
	`ank_lov_9` = '1' OR 
	`ank_lov_10` = '1' OR 
	`ank_lov_11` = '1' OR 
	`ank_lov_12` = '1' OR 
	`ank_lov_13` = '1' OR 
	`ank_lov_14` = '1'
	)";
    $orien = "(
	`ank_orien` = '1' OR 
	`ank_orien` = '2' OR 
	`ank_orien` = '3'
	)";
    $opar = "(
	`ank_o_par` IS NOT NULL 
	)";
    $osebe = "(
	`ank_o_sebe` IS NOT NULL 
	)";
    echo "<div id='comments' class='menus'>";
    echo "<div class='webmenu'>";
    echo "<a href='?orders=all' class='$_SESSION[nav3]'>Все</a>";
    echo "</div>";
    echo "<div class='webmenu last'>";
    echo "<a href='?orders=woman' class='$_SESSION[nav2]'>Девушки</a>";
    echo "</div>";
    echo "<div class='webmenu last'>";
    echo "<a href='?orders=man' class='$_SESSION[nav1]'>Парни</a>";
    echo "</div>";
    echo "</div>";
    /* Этот скрипт выводит 1 случайного "Лидера" и ссылку на весь их список.(с) DCMS-Social */
$k_lider = $db->query("SELECT COUNT(*) FROM `liders` WHERE `time` > '$time'")->el();
$liders = $db->query("SELECT * FROM `liders` WHERE `time` > '$time' ORDER BY rand() LIMIT 1")->row();
if ($k_lider) {
    echo '<div class="main">';
    $lider = get_user($liders['id_user']);
    echo status($lider['id']);
    echo group($lider['id']) , ' <a href="/info.php?id=' . $lider['id'] . '">' . $lider['nick'] . '</a> ';
    echo medal($lider['id']) , online($lider['id']) , '<br />';
    if ($liders['msg']) {
        echo output_text($liders['msg']) . '<br />';
    }
    echo '<img src="/style/icons/lider.gif" alt="S"/> <a href="/user/liders/">Все лидеры</a> (' . $k_lider . '->row()';
    echo '</div>';
}
$k_post=$db->query("SELECT COUNT(*) FROM `user` WHERE $cel AND $orien AND $opar AND $osebe $_SESSION[orders] AND `date_last` > '".(time()-259200)."'")->el();
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
$q = $db->query("SELECT ank_o_sebe,nick,id FROM `user` WHERE $cel AND $orien AND $opar AND $osebe $_SESSION[orders] AND `date_last` > '".(time()-259200)."' order BY `date_last` DESC LIMIT $start, $set[p_str]");
echo '<table class="post">';
if (!$k_post) {
    echo '<div class="mess">';
    echo 'Список анкет пуст';
    echo '</div>';
}
while ($ank = $q->row()) {
    $ank=get_user($ank['id']);
    /*-----------зебра-----------*/
    if ($num==0) {
        echo '<div class="nav1">';
        $num=1;
    } elseif ($num==1) {
        echo '<div class="nav2">';
        $num=0;
    }
    /*---------------------------*/
    echo status($ank['id']);
    echo group($ank['id']) . ' <a href="/info.php?id=' . $ank['id'] . '">' . $ank['nick'] . '</a> ';
    echo medal($ank['id']) . online($ank['id']) . '<br />';
    echo output_text($ank['ank_o_sebe']);
    echo '</div>';
}
echo '</table>';
if ($k_page>1) {
    str("?", $k_page, $page);
} // Вывод страниц
include_once '../../sys/inc/tfoot.php';
