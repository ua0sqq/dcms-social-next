<?php
include_once '../sys/inc/start.php';
include_once '../sys/inc/compress.php';
include_once '../sys/inc/sess.php';
include_once '../sys/inc/home.php';
include_once '../sys/inc/settings.php';
include_once '../sys/inc/db_connect.php';
include_once '../sys/inc/ipua.php';
include_once '../sys/inc/fnc.php';
include_once '../sys/inc/user.php';

$set['title']='Пользователи'; // заголовок страницы
include_once '../sys/inc/thead.php';
title();
aut();

$sort='id';
$por='DESC';

if (isset($_GET['ASC'])) {
    $por='ASC';
} // прямой порядок
if (isset($_GET['DESC'])) {
    $por='DESC';
} // обратный порядок
switch (filter_input(INPUT_GET, 'sort', FILTER_DEFAULT)) {
    case 'balls':
	$sql_sort='`u`.`balls`';
	$sort='balls'; // баллы
    break;
    case 'level':
	$sql_sort='`ugr`.`level`';
	$sort='level'; // уровень
    break;
    case 'rating':
	$sql_sort='`u`.`rating`';
	$sort='rating'; // рейтинг
    break;
    case 'pol':
	$sql_sort='`u`.`pol`';
	$sort='pol'; // пол
    break;
    default:
	$sql_sort='`u`.`id`';
	$sort='id'; // ID
    break;
}
if (!isset($_GET['go'])) {
    $k_post=$db->query("SELECT COUNT(*) FROM `user`")->el();
    $k_page=k_page($k_post, $set['p_str']);
    $page=page($k_page);
    $start=$set['p_str']*$page-$set['p_str'];
    echo "<div class='main'>
	Сортировать по: <br />
	<select name='menu' onchange='top.location.href = this.options[this.selectedIndex].value;'> 
	<option selected>-Выбрать-
	<option value='?sort=balls&amp;DESC&amp;page=$page'>баллы</option>
	<option value='?sort=level&amp;DESC&amp;page=$page'>статус</option>
	<option value='?sort=rating&amp;DESC&amp;page=$page'>рейтинг</option>
	<option value='?sort=id&amp;ASC&amp;page=$page'>id</option>
	<option value='?sort=pol&amp;ASC&amp;page=$page'>пол</option>
	<option value='?sort=id&amp;DESC&amp;page=$page'>новые</option>
	</select></option>
	</div>
	<table class='post'>\n";
    if ($k_post==0) {
        echo '<div class="mess">';
        echo 'Нет результатов';
        echo '</div>';
    }
    $q=$db->query("SELECT `u`.`id`, u.nick, u.pol, u.group_access, u.`level`, u.date_reg, u.date_last, u.balls, u.rating, ugr.name AS group_name
FROM `user` `u`
LEFT JOIN `user_group` `ugr` ON `u`.`group_access` = `ugr`.`id` ORDER BY ?q ?q LIMIT ?i OFFSET ?i",
[$sql_sort, $por, $set['p_str'], $start]);
    while ($ank = $q->row()) {
        //$ank=get_user($ank['id']);
        /*-----------зебра-----------*/
        if ($num==0) {
            echo '<div class="nav1">';
            $num=1;
        } elseif ($num==1) {
            echo '<div class="nav2">';
            $num=0;
        }
        /*---------------------------*/
        echo status($ank['id']) , group($ank['id']);
        echo " <a href='/info.php?id=$ank[id]'>$ank[nick]</a> \n";
        echo "".medal($ank['id'])." ".online($ank['id'])."<br />";
        if ($ank['group_access']>1) {
            echo "<span class='status'>$ank[group_name]</span><br />\n";
        }
        if ($sort=='rating') {
            echo "<span class=\"ank_n\">Рейтинг:</span> <span class=\"ank_d\">$ank[rating]</span><br />\n";
        }
        if ($sort=='balls') {
            echo "<span class=\"ank_n\">Баллы:</span> <span class=\"ank_d\">$ank[balls]</span><br />\n";
        }
        if ($sort=='pol') {
            echo "<span class=\"ank_n\">Пол:</span> <span class=\"ank_d\">".(($ank['pol']==1)?'Мужской':'Женский')."</span><br />\n";
        }
        if ($sort=='id') {
            echo "<span class=\"ank_n\">Регистрация:</span> <span class=\"ank_d\">".vremja($ank['date_reg'])."</span><br />\n";
        }
        echo "<span class=\"ank_n\">Посл. посещение:</span> <span class=\"ank_d\">".vremja($ank['date_last'])."</span><br />\n";
        if (user_access('user_prof_edit') && $user['level']>$ank['level']) {
            echo "<a href='/adm_panel/user.php?id=$ank[id]'>Редактировать профиль</a><br />\n";
        }
        echo '</div>';
    }
    echo "</table>\n";
    if ($k_page>1) {
        str("/user/users.php?sort=$sort&amp;$por&amp;", $k_page, $page);
    } // Вывод страниц
}
$usearch=null;
if (isset($_SESSION['usearch'])) {
    $usearch=$_SESSION['usearch'];
}
if (isset($_POST['usearch'])) {
    $usearch=$_POST['usearch'];
}
if ($usearch==null) {
    unset($_SESSION['usearch']);
} else {
    $_SESSION['usearch']=$usearch;
}
$usearch=preg_replace("#( ){1,}#", "", $usearch);
if (isset($_GET['go']) && $usearch!=null) {
    $k_post=$db->query("SELECT COUNT(*) FROM `user` WHERE `nick` like '%".my_esc($usearch)."%' OR `id` = '".intval($usearch)."'")->el();
    $k_page=k_page($k_post, $set['p_str']);
    $page=page($k_page);
    $start=$set['p_str']*$page-$set['p_str'];
    echo "<table class='post'>
	<div class='main'>
	Сортировать по: <br />
	 <select name='menu' onchange='top.location.href = this.options[this.selectedIndex].value;'> 
	<option selected>-Выбрать-
	<option value='?sort=balls&amp;DESC&amp;page=$page'>баллы</option>
	<option value='?sort=level&amp;DESC&amp;page=$page'>статус</option>
	<option value='?sort=rating&amp;DESC&amp;page=$page'>рейтинг</option>
	<option value='?sort=id&amp;ASC&amp;page=$page'>id</option>
	<option value='?sort=pol&amp;ASC&amp;page=$page>пол</option>
	<option value='?sort=id&amp;DESC&amp;page=$page>новые</option>
	</select></option>
	</div>";
    if ($k_post==0) {
        echo "   <tr>
	<td class='p_t'>
	Нет результатов
	</td>
	</tr>\n";
    }
    $q=$db->query("SELECT `id` FROM `user` WHERE `nick` like '%".my_esc($usearch)."%' OR `id` = '".intval($usearch)."' ORDER BY `$sort` $por LIMIT $start, $set[p_str]");
    while ($ank = $q->row()) {
        $ank=get_user($ank['id']);
        /*-----------зебра-----------*/if ($num==0) {
            echo '<div class="nav1">';
            $num=1;
        } elseif ($num==1) {
            echo '<div class="nav2">';
            $num=0;
        }/*---------------------------*/
        echo "".status($ank['id'])." ".group($ank['id'])."";
        echo "<a href='/info.php?id=$ank[id]'>$ank[nick]</a>\n";
        echo "".medal($ank['id'])." ".online($ank['id'])."";
        if ($ank['level']!=0) {
            echo "<span class=\"status\">$ank[group_name]</span><br />\n";
        }
        if ($sort=='rating') {
            echo "<span class=\"ank_n\">Рейтинг:</span> <span class=\"ank_d\">$ank[rating]</span><br />\n";
        }
        if ($sort=='balls') {
            echo "<span class=\"ank_n\">Баллы</span> <span class=\"ank_d\">$ank[balls]</span><br />\n";
        }
        if ($sort=='pol') {
            echo "<span class=\"ank_n\">Пол:</span> <span class=\"ank_d\">".(($ank['pol']==1)?'Мужской':'Женский')."</span><br />\n";
        }
        if ($sort=='id') {
            echo "<span class=\"ank_n\">Регистрация:</span> <span class=\"ank_d\">".vremja($ank['date_reg'])."</span><br />\n";
        }
        echo "<span class=\"ank_n\">Посл. посещение:</span> <span class=\"ank_d\">".vremja($ank['date_last'])."</span><br />\n";
        if (user_access('user_prof_edit') && $user['level']>$ank['level']) {
            echo "<a href='/adm_panel/user.php?id=$ank[id]'>Редактировать профиль</a><br />\n";
        }
        echo '</div>';
    }
    echo "</table>\n";
    if ($k_page>1) {
        str("/user/users.php?go&amp;sort=$sort&amp;$por&amp;", $k_page, $page);
    } // Вывод страниц
} else {
    echo "<div class=\"post\">\nВведите ID или НИК юзера</div>\n";
}
echo "<form method=\"post\" action=\"?go&amp;sort=$sort&amp;$por\">";
// ShaMan
$usearch=stripcslashes(htmlspecialchars($usearch));
// Тут конец моих дум
echo "<input type=\"text\" name=\"usearch\" maxlength=\"16\" value=\"$usearch\" /><br />\n";
echo "<input type=\"submit\" value=\"Найти юзера\" />";
echo "</form>\n";
include_once '../sys/inc/tfoot.php';
