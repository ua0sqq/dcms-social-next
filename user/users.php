<?php
include_once '../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

$set['title']='Пользователи'; // заголовок страницы
include_once H . 'sys/inc/thead.php';
title();
aut();

$sql_sort = ['u', 'id'];
$por = 'DESC';

$get_input = filter_input_array(INPUT_GET, FILTER_DEFAULT);
if (isset($get_input['ASC'])) {
	$por = 'ASC';
}
if (isset($get_input['DESC'])) {
	$por = 'DESC';
}
switch (filter_input(INPUT_GET, 'sort', FILTER_DEFAULT)) {
    case 'balls':
	$sql_sort=['u', 'balls'];
	$sort='balls'; // баллы
    break;
    case 'level':
	$sql_sort=['ugr', 'level']; // уровень
    break;
    case 'rating':
	$sql_sort=['u', 'rating']; // рейтинг
    break;
    case 'pol':
	$sql_sort=['u', 'pol']; // пол
    break;
    default:
	$sql_sort=['u', 'id']; // ID
    break;
}
if (!isset($get_input['go'])) {
    $k_post = $db->query("SELECT COUNT( * ) FROM `user`")->el();
	$k_page = k_page($k_post, $set['p_str']);
    $page = page($k_page);
    $start = $set['p_str']*$page-$set['p_str'];
?>
<div class="main">
	<form method="post" action="">
		<p>Сортировать по-: </p>
		<p><select name="menu" onchange="top.location.href = this.options[this.selectedIndex].value;"> 
			<option selected>-Выбрать-</option>
			<option value="?sort=balls&amp;DESC&amp;page=<?php echo $page;?>">баллы</option>
			<option value="?sort=level&amp;DESC&amp;page=<?php echo $page;?>">статус</option>
			<option value="?sort=rating&amp;DESC&amp;page=<?php echo $page;?>">рейтинг</option>
			<option value="?sort=id&amp;ASC&amp;page=<?php echo $page;?>">id</option>
			<option value="?sort=pol&amp;ASC&amp;page=<?php echo $page;?>">пол</option>
			<option value="?sort=id&amp;DESC&amp;page=<?php echo $page;?>">новые</option>
		</select></p>
	</form>
</div>
<?php
    if (!$k_post) {
?>
<div class="mess">
	Нет результатов
</div>
<?php
    } else {

    $q=$db->query("SELECT `u`.`id`, u.nick, u.pol, u.group_access, u.`level`, u.date_reg, u.date_last, u.balls, u.rating, ugr.name AS group_name
FROM `user` `u`
LEFT JOIN `user_group` `ugr` ON `u`.`group_access` = `ugr`.`id` ORDER BY ?col ?q LIMIT ?i OFFSET ?i",
				[$sql_sort, $por, $set['p_str'], $start]);
    while ($ank = $q->row()) {
        if ($num==0) {
?>
<div class="nav1">
	<?php
            $num=1;
        } elseif ($num==1) {
?>
<div class="nav2">
	<?php
            $num=0;
        }
        echo status($ank['id']) , group($ank['id']);
        echo " <a href='/info.php?id=$ank[id]'>$ank[nick]</a> \n";
        echo "".medal($ank['id'])." ".online($ank['id'])."<br />";
        if ($ank['group_access']>1) {
            echo "<span class='status'>$ank[group_name]</span><br />\n";
        }
        if ($sql_sort[1]=='rating') {
            echo "<span class=\"ank_n\">Рейтинг:</span> <span class=\"ank_d\">$ank[rating]</span><br />\n";
        }
        if ($sql_sort[1]=='balls') {
            echo "<span class=\"ank_n\">Баллы:</span> <span class=\"ank_d\">$ank[balls]</span><br />\n";
        }
        if ($sql_sort[1]=='pol') {
            echo "<span class=\"ank_n\">Пол:</span> <span class=\"ank_d\">".(($ank['pol']==1)?'Мужской':'Женский')."</span><br />\n";
        }
        if ($sql_sort[1]=='id') {
            echo "<span class=\"ank_n\">Регистрация:</span> <span class=\"ank_d\">".vremja($ank['date_reg'])."</span><br />\n";
        }
        echo "<span class=\"ank_n\">Посл. посещение:</span> <span class=\"ank_d\">".vremja($ank['date_last'])."</span><br />\n";
        if (user_access('user_prof_edit') && $user['level']>$ank['level']) {
            echo "<a href='/adm_panel/user.php?id=$ank[id]'>Редактировать профиль</a><br />\n";
        }
        echo '</div>';
    }
    if ($k_page>1) {
        str('/user/users.php?sort=' . $sql_sort[1] . '&amp;' . $por . '&amp;', $k_page, $page);
    } // Вывод страниц
}
}
$usearch = null;
if (isset($_SESSION['usearch'])) {
    $usearch = $_SESSION['usearch'];
}
if (isset($_POST['usearch'])) {
    $usearch = $_POST['usearch'];
}
if ($usearch == null) {
    unset($_SESSION['usearch']);
} else {
    $_SESSION['usearch'] = $usearch;
}
$usearch = trim($usearch);
if (isset($get_input['go']) && $usearch != null) {
    $k_post = $db->query("SELECT COUNT( * ) FROM `user` WHERE `nick` LIKE '%?e%' OR `id`=?i",
					   [$usearch, $usearch])->el();
    $k_page = k_page($k_post, $set['p_str']);
    $page = page($k_page);
    $start = $set['p_str']*$page-$set['p_str'];
?>
<div class="main">
	<form method="post" action="">
		<p>Сортировать по: </p>
		<p><select name="menu" onchange="top.location.href = this.options[this.selectedIndex].value;"> 
		<option selected>-Выбрать-</option>
		<option value="?go&amp;sort=balls&amp;DESC&amp;page=<?php echo $page;?>">баллы</option>
		<option value="?go&amp;sort=level&amp;DESC&amp;page=<?php echo $page;?>">статус</option>
		<option value="?go&amp;sort=rating&amp;DESC&amp;page=<?php echo $page;?>">рейтинг</option>
		<option value="?go&amp;sort=id&amp;ASC&amp;page=<?php echo $page;?>">id</option>
		<option value="?go&amp;sort=pol&amp;ASC&amp;page=<?php echo $page;?>">пол</option>
		<option value="?go&amp;sort=id&amp;DESC&amp;page=<?php echo $page;?>">новые</option>
		</select></p>
	</form>
</div>
<?php
    if (!$k_post) {
?>
<div class="mess">
	Нет результатов
</div>
<?php
    }
    $q=$db->query("SELECT `u`.`id`, u.nick, u.pol, u.group_access, u.`level`, u.date_reg, u.date_last, u.balls, u.rating, ugr.name AS group_name
FROM `user` u
LEFT JOIN `user_group` `ugr` ON `u`.`group_access` = `ugr`.`id`
WHERE u.`nick` LIKE '%?e%' OR u.`id`=?i ORDER BY ?col ?q LIMIT ?i, ?i",
				  [$usearch, $usearch, $sql_sort, $por, $start, $set['p_str']]);
    while ($ank = $q->row()) {
        if ($num==0) {
            echo '<div class="nav1">';
            $num=1;
        } elseif ($num==1) {
            echo '<div class="nav2">';
            $num=0;
        }
        echo "".status($ank['id'])." ".group($ank['id'])."";
        echo "<a href='/info.php?id=$ank[id]'>$ank[nick]</a>\n";
        echo "".medal($ank['id'])." ".online($ank['id'])."<br />";
        if ($ank['level']!=0) {
            echo "<span class=\"status\">$ank[group_name]</span><br />\n";
        }
        if ($sql_sort[1]=='rating') {
            echo "<span class=\"ank_n\">Рейтинг:</span> <span class=\"ank_d\">$ank[rating]</span><br />\n";
        }
        if ($sql_sort[1]=='balls') {
            echo "<span class=\"ank_n\">Баллы</span> <span class=\"ank_d\">$ank[balls]</span><br />\n";
        }
        if ($sql_sort[1]=='pol') {
            echo "<span class=\"ank_n\">Пол:</span> <span class=\"ank_d\">".(($ank['pol']==1)?'Мужской':'Женский')."</span><br />\n";
        }
        if ($sql_sort[1]=='id') {
            echo "<span class=\"ank_n\">Регистрация:</span> <span class=\"ank_d\">".vremja($ank['date_reg'])."</span><br />\n";
        }
        echo "<span class=\"ank_n\">Посл. посещение:</span> <span class=\"ank_d\">".vremja($ank['date_last'])."</span><br />\n";
        if (user_access('user_prof_edit') && $user['level']>$ank['level']) {
            echo "<a href='/adm_panel/user.php?id=$ank[id]'>Редактировать профиль</a><br />\n";
        }
        echo '</div>';
    }

    if ($k_page>1) {
        str('/user/users.php?go&amp;sort=' . $sql_sort[1] . '&amp;' . $por . '&amp;', $k_page, $page);
    } // Вывод страниц
} else {
    echo "<div class=\"post\">\nВведите ID или НИК юзера</div>\n";
}
?>
<form class="foot" method="post" action="?go&amp;sort=<?php echo $sql_sort[1] . '&amp;' . $por;?>">
	<?php
$usearch=stripcslashes(htmlspecialchars($usearch));
?>
	<p><input type="text" name="usearch" maxlength="16" value="<?php echo $usearch;?>" /></p>
	<p><input type="submit" value="Найти юзера" />
<script type="text/javascript">
  document.write('<input type="button" value="Назад" onClick=\'location.href="/user/users.php"\'/>');
</script>
        <noscript>&nbsp;&nbsp;<a class="button" href="/user/users.php">Назад</a></noscript></p>
</form>
<?php

include_once H . 'sys/inc/tfoot.php';
