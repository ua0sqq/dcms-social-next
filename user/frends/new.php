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

only_reg('/');

$set['title'] = "Заявки"; // заголовок страницы
include_once '../../sys/inc/thead.php';
title();
aut();
err();

// Panel
$cnt = $db->query(
    "SELECT * FROM (
    SELECT COUNT( * ) all_frends FROM `frends` WHERE `user`=?i AND `i`=?i)q1, (
    SELECT COUNT( * ) onl_frends FROM `frends`
    JOIN `user` ON `frends`.`frend`=`user`.`id`
    WHERE `frends`.`user`=?i AND `frends`.`i`=?i AND `user`.`date_last`>?i)q2, (
    SELECT COUNT( * ) new_frends FROM `frends_new` WHERE `to` =?i)q3",
                  [$user['id'], 1, $user['id'], 1, (time()-600), $user['id']])->row();

echo "<div id='comments' class='menus'>";
echo "<div class='webmenu'>";
echo "<a href='index.php?id=$user[id]'>Все (".$cnt['all_frends'].")</a>";
echo "</div>";
echo "<div class='webmenu last'>";
echo "<a href='online.php?id=$user[id]'>Онлайн (".$cnt['onl_frends'].")</a>";
echo "</div>";
if ($user['id'] == $user['id']) {
    echo "<div class='webmenu last'>";
    echo "<a href='new.php' class='activ'>Заявки (".$cnt['new_frends'].")</a>";
    echo "</div>";
}
echo "</div>";
// End Panel

$k_post = $cnt['new_frends'];
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
$q = $db->query(
    "SELECT usr.id, usr.nick FROM `frends_new` frn
	JOIN `user` usr ON  `frn`.`user`=`usr`.`id`
	WHERE `to`=?i ORDER BY frn.`time` DESC",
                [$user['id']]);

if ($k_post==0) {
    echo '<div class="mess">';
    echo 'Новых заявок нет';
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
    if ($set['set_show_icon']==2) {
        avatar($frend['id']);
    } elseif ($set['set_show_icon']==1) {
        echo "".status($frend['id'])."";
    }
    echo " ".group($frend['id'])." <a href='/info.php?id=$frend[id]'>$frend[nick]</a>\n";
    echo "".medal($frend['id'])." ".online($frend['id'])." <br />";
    echo "[<img src='/style/icons/ok.gif' alt='*'/> <a href='/user/frends/create.php?ok=$frend[id]'>Принять</a>] ";
    echo "[<img src='/style/icons/delete.gif' alt='*'/> <a href='create.php?no=$frend[id]'>Отклонить</a>]";
    echo "   </div>\n";
}

if ($k_page>1) {
    str("?id=".$user['id']."&amp;", $k_page, $page);
} // Вывод страниц

include_once '../../sys/inc/tfoot.php';
