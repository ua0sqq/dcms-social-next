<?php
include_once '../sys/inc/start.php';
include_once '../sys/inc/compress.php';
include_once '../sys/inc/sess.php';
include_once '../sys/inc/home.php';
include_once '../sys/inc/settings.php';
include_once '../sys/inc/db_connect.php';
include_once '../sys/inc/ipua.php';
include_once '../sys/inc/fnc.php';
include_once '../sys/inc/adm_check.php';
include_once '../sys/inc/user.php';

user_access('adm_log_read', null, 'index.php?'.SID);
adm_check();

$set['title']='Действия администрации';
include_once '../sys/inc/thead.php';
title();
err();
aut();
$db->query("set sql_mode = 'ONLY_FULL_GROUP_BY';");
if (isset($_GET['id'])) {
    $ank=get_user($_GET['id']);
} else {
    $ank=false;
}
if ($ank && user_access('adm_log_read') && ($ank['id']==$user['id'] || $ank['level']<$user['level'])) {
    echo "<a href='/info.php?id=$ank[id]'>$ank[nick]</a> ($ank[group_name])<br />\n";
    $adm_log_c_all=$db->query("SELECT COUNT(*) FROM `admin_log` WHERE `id_user` = '$ank[id]'")->el();
    // время месяц назад
    $mes=mktime(0, 0, 0, date('m')-1);
    $adm_log_c_mes=$db->query("SELECT COUNT(*) FROM `admin_log` WHERE `id_user` = '$ank[id]' AND `time` > '$mes'")->el();
    echo "<span class='ank_n'>Вся активность:</span> <span class='ank_d'>$adm_log_c_all</span><br />\n";
    echo "<span class='ank_n'>Активность за месяц:</span> <span class='ank_d'>$adm_log_c_mes</span><br />\n";
} else {
    $adm_log_c_all=$db->query("SELECT COUNT(*) FROM `admin_log`")->el();
    $mes=mktime(0, 0, 0, date('m')-1); // время месяц назад
    $adm_log_c_mes=$db->query("SELECT COUNT(*) FROM `admin_log` WHERE `time` > '$mes'")->el();
    echo "<span class='ank_n'>Вся активность:</span> <span class='ank_d'>$adm_log_c_all</span><br />\n";
    echo "<span class='ank_n'>Активность за месяц:</span> <span class='ank_d'>$adm_log_c_mes</span><br />\n";
}
if (isset($_GET['id_mod']) && isset($_GET['id_act']) && $db->query("SELECT COUNT(*) FROM `admin_log` WHERE `mod` = '".intval($_GET['id_mod'])."' AND `act` = '".intval($_GET['id_act'])."'".($ank?" AND `id_user` = '$ank[id]'":null))->el()) {
    $mod=$db->query("SELECT * FROM `admin_log_mod` WHERE `id` = '".intval($_GET['id_mod'])."' LIMIT 1")->row();
    $act=$db->query("SELECT * FROM `admin_log_act` WHERE `id` = '".intval($_GET['id_act'])."' LIMIT 1")->row();
    $k_post=$db->query("SELECT COUNT(*) FROM `admin_log` WHERE `mod` = '$mod[id]' AND `act` = '$act[id]'".($ank?" AND `admin_log`.`id_user` = '$ank[id]'":null))->el();
    $k_page=k_page($k_post, $set['p_str']);
    $page=page($k_page);
    $start=$set['p_str']*$page-$set['p_str'];
    echo "<table class='post'>\n";
    if ($k_post==0) {
        echo "   <tr>\n";
        echo "  <td class='p_t'>\n";
        echo "Нет действий\n";
        echo "  </td>\n";
        echo "   </tr>\n";
    }
    $q=$db->query("SELECT * FROM `admin_log` WHERE `mod` = '$mod[id]' AND `act` = '$act[id]'".($ank?" AND `admin_log`.`id_user` = '$ank[id]'":null)." ORDER BY id DESC LIMIT $start, $set[p_str]");
    while ($post = $q->row()) {
        $ank2=get_user($post['id_user']);
        echo "   <tr>\n";
        if ($set['set_show_icon']==2) {
            echo "  <td class='icon48' rowspan='2'>\n";
            avatar($ank2['id']);
            echo "  </td>\n";
        } elseif ($set['set_show_icon']==1) {
            echo "  <td class='icon14'>\n";
            echo "".status($ank2['id'])."";
            echo "  </td>\n";
        }
        echo "  <td class='p_t'>\n";
        echo "<a href='/info.php?id=$ank2[id]'>$ank2[nick]</a>".online($ank2['id'])." (".vremja($post['time']).")\n";
        echo "  </td>\n";
        echo "   </tr>\n";
        echo "   <tr>\n";
        if ($set['set_show_icon']==1) {
            echo "  <td class='p_m' colspan='2'>\n";
        } else {
            echo "  <td class='p_m'>\n";
        }
        echo output_text($post['opis'])."<br />\n";
        echo "  </td>\n";
        echo "   </tr>\n";
    }
    echo "</table>\n";
    if ($k_page>1) {
        str('?id_mod='.$mod['id'].'&amp;id_act='.$act['id'].'&amp;', $k_page, $page);
    } // Вывод страниц
    echo "&laquo;<a href='?id_mod=$mod[id]".($ank?"&amp;id=$ank[id]":null)."'>Список действий</a><br />\n";
    echo "&laquo;<a href='?$passgen".($ank?"&amp;id=$ank[id]":null)."'>Список модулей</a><br />\n";
} elseif (isset($_GET['id_mod']) && $db->query("SELECT COUNT(*) FROM `admin_log` WHERE `mod` = '".intval($_GET['id_mod'])."'".($ank?" AND `id_user` = '$ank[id]'":null))->el()) {
    // действия в модуле
    $mod=$db->query("SELECT * FROM `admin_log_mod` WHERE `id` = '".intval($_GET['id_mod'])."' LIMIT 1")->row();
    $q=$db->query("SELECT `admin_log_act`.`name`, `admin_log_act`.`id`, COUNT(`admin_log`.`id`) AS `count` FROM `admin_log`
LEFT JOIN `admin_log_act` ON `admin_log`.`act` = `admin_log_act`.`id`
WHERE `admin_log`.`mod` = '$mod[id]'".($ank?" AND `admin_log`.`id_user` = '$ank[id]'":null)." GROUP BY `admin_log`.`act`")->assoc();
    echo "<div class='menu'>\n";
    if (!count($q)) {
        echo "Нет действий в модуле '$mod[name]'";
    }
    foreach ($q as $act) {
        echo "<a href='?id_mod=$mod[id]&amp;id_act=$act[id]".($ank?"&amp;id=$ank[id]":null)."'>$act[name]</a> ($act[count])<br />\n";
    }
    echo "</div>\n";
    echo "&laquo;<a href='?$passgen".($ank?"&amp;id=$ank[id]":null)."'>Список модулей</a><br />\n";
} else {
    // действия по модулям
    $q=$db->query("SELECT `admin_log_mod`.`name`, `admin_log_mod`.`id`, COUNT(`admin_log`.`id`) AS `count` FROM `admin_log` LEFT JOIN `admin_log_mod` ON `admin_log`.`mod` = `admin_log_mod`.`id`".($ank?" WHERE `admin_log`.`id_user` = '$ank[id]'":null)." GROUP BY `admin_log`.`mod`")->assoc();
    echo "<div class='menu'>\n";
    if (!count($q)) {
        echo "Нет действий в модулях";
    }
    foreach ($q as $mod) {
        echo "<a href='?id_mod=$mod[id]".($ank?"&amp;id=$ank[id]":null)."'>$mod[name]</a> ($mod[count])<br />\n";
    }
    echo "</div>\n";
}
if (user_access('adm_panel_show')) {
    echo "<div class='foot'>\n";
    if (user_access('adm_show_adm')) {
        echo "&raquo;<a href='administration.php'>Администрация</a><br />\n";
    }
    echo "&laquo;<a href='/adm_panel/'>В админку</a><br />\n";
    echo "</div>\n";
}
include_once '../sys/inc/tfoot.php';
