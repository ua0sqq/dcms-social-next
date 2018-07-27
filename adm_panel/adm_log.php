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

if (isset($_GET['id'])) {
    $ank = get_user($_GET['id']);
} else {
    $ank = false;
}
if ($ank && user_access('adm_log_read') && ($ank['id'] == $user['id'] || $ank['level'] < $user['level'])) {
    echo "<a href='/info.php?id=$ank[id]'>$ank[nick]</a> ($ank[group_name])<br />\n";
    $mes=mktime(0, 0, 0, date('m') - 1); // время месяц назад
    $data = array($ank['id'], $ank['id'], $mes);
    $cnt = $db->query('SELECT * FROM (
        SELECT COUNT( * ) as adm_log_c_all FROM `admin_log` WHERE `id_user`=?i)q1, (
        SELECT COUNT( * ) as adm_log_c_mes FROM `admin_log` WHERE `id_user`=?i AND `time`>?i)q2', $data)->row();
    echo '<span class="ank_n">Вся активность:</span> <span class="ank_d">' . $cnt['adm_log_c_all'] . '</span><br />';
    echo '<span class="ank_n">Активность за месяц:</span> <span class="ank_d">' . $cnt['adm_log_c_mes'] . '</span><br />';
} else {
    $mes = mktime(0, 0, 0, date('m') - 1); // время месяц назад
    $cnt = $db->query('SELECT * FROM (
        SELECT COUNT( * ) as adm_log_c_all FROM `admin_log`)q1, (
        SELECT COUNT( * ) as adm_log_c_mes FROM `admin_log` WHERE `time`>?i)q2', array($mes))->row();
    echo '<div class="p_t">Вся активность: ' . $cnt['adm_log_c_all'] . '</span><br />';
    echo 'Активность за месяц: ' . $cnt['adm_log_c_mes'] . '</span><br /></div>';
}

$query = '';
if ($ank) {
    $query = ' AND `id_user`="' . $ank['id'] . '"';
}

if (isset($_GET['id_mod']) && isset($_GET['id_act']) && $db->query('SELECT COUNT( * ) FROM `admin_log`
WHERE `mod`=?i AND `act`=?i ?q', array($_GET['id_mod'], $_GET['id_act'], $query))->el()) {

    $mod = $db->query('SELECT * FROM `admin_log_mod` WHERE `id`=?i LIMIT ?i', array($_GET['id_mod'], 1))->row();
    $act = $db->query('SELECT * FROM `admin_log_act` WHERE `id`=?i LIMIT ?i', array($_GET['id_act'], 1))->row();
    $k_post = $db->query('SELECT COUNT( * ) FROM `admin_log` WHERE `mod`=?i AND `act`=?i ?q',
                         array($mod['id'], $act['id'], $query))->el();
    $k_page = k_page($k_post, $set['p_str']);
    $page = page($k_page);
    $start = $set['p_str'] * $page - $set['p_str'];
    echo "<table class='post'>\n";
    if ($k_post == 0) {
        echo "   <tr>\n";
        echo "  <td class='p_t'>\n";
        echo "Нет действий\n";
        echo "  </td>\n";
        echo "   </tr>\n";
    }

    $q = $db->query('SELECT al.*, u.id AS id_user, u.nick
FROM `admin_log` al
JOIN `user` u ON u.id=al.id_user
WHERE al.`mod`=?i AND al.`act`=?i ?q ORDER BY al.id DESC LIMIT ?i, ?i',
                     array($mod['id'], $act['id'], $query, $start, $set['p_str']));
    while ($post = $q->row()) {
        $ank2 = ['id' => $post['id_user'], 'nick' => $post['nick']];
        echo "   <tr>\n";
        if ($set['set_show_icon'] == 2) {
            echo "  <td class='icon48' rowspan='2'>\n";
            avatar($ank2['id']);
            echo "  </td>\n";
        } elseif ($set['set_show_icon'] == 1) {
            echo "  <td class='icon14'>\n";
            echo status($ank2['id']);
            echo "  </td>\n";
        }
        echo "  <td class='p_t'>\n";
        echo "<a href='/info.php?id=$ank2[id]'>$ank2[nick]</a>" . online($ank2['id']) . " (" . vremja($post['time']) . ")\n";
        echo "  </td>\n";
        echo "   </tr>\n";
        echo "   <tr>\n";
        if ($set['set_show_icon'] == 1) {
            echo "  <td class='p_m' colspan='2'>\n";
        } else {
            echo "  <td class='p_m'>\n";
        }
        echo output_text($post['opis']) . "<br />\n";
        echo "  </td>\n";
        echo "   </tr>\n";
    }
    echo "</table>\n";
    if ($k_page > 1) {
        str('?id_mod=' . $mod['id'] . '&amp;id_act=' . $act['id'] . '&amp;', $k_page, $page); // Вывод страниц
    }
    echo "<div class='menu'><div class=\"main\"><a href='?id_mod=$mod[id]" . ($ank ? "&amp;id=$ank[id]" : null) . "'>Список действий</a></div>\n";
    echo "<div class=\"main\"><a href='?$passgen" . ($ank ? "&amp;id=$ank[id]" : null) . "'>Список модулей</a></div></div>\n";
} elseif (isset($_GET['id_mod']) && $db->query('SELECT COUNT( * ) FROM `admin_log` WHERE `mod`=?i ?q',
                                               array($_GET['id_mod'], $query))->el()) {

    // действия в модуле
    $mod = $db->query('SELECT * FROM `admin_log_mod` WHERE `id`=?i LIMIT ?i', array($_GET['id_mod'], 1))->row();
    $res = $db->query('SELECT a.`name` , a.`id` , (
SELECT COUNT( * ) FROM `admin_log` WHERE `act` = a.`id` AND `mod` =?i ?q GROUP BY `act`) AS `count`
FROM `admin_log_act` a', array($mod['id'], $query))->assoc();

    if (!count($res)) {
        echo '<div class="err">Нет действий в модуле ' . $mod['name'] . '</div>';
    }
        echo "<div class='menu'>\n";
    foreach ($res as $act) {
        if ($act['count']) {
            echo "<div class=\"main\"><a href='?id_mod=$mod[id]&amp;id_act=$act[id]" . ($ank ? "&amp;id=$ank[id]" : null) . "'>$act[name] ($act[count])</a></div>\n";
        }
    }

    echo "<div class=\"main\"><a href='?$passgen" . ($ank ? "&amp;id=$ank[id]" : null) . "'>Список модулей</a></div>\n";
    echo "</div>\n";
} else {

    // действия по модулям
    $res = $db->query('SELECT m.`name` , m.`id` , (
    SELECT COUNT( * ) FROM `admin_log` WHERE `mod`= m.id ?q GROUP BY `mod`) `count`
FROM `admin_log_mod` m', array($query))->assoc();

    if (!count($res)) {
        echo '<div class="err">Нет действий в модулях</div>';
    }
    echo "<div class='menu'>\n";
    foreach ($res as $mod) {
        if ($mod['count']) {
            echo "<div class=\"main\"><a href='?id_mod=$mod[id]" . ($ank ? "&amp;id=$ank[id]" : null) . "'>$mod[name] ($mod[count])</a></div>\n";
        }
    }
    echo "</div>\n";
}
if (user_access('adm_panel_show')) {
    if (user_access('adm_show_adm')) {
        echo "<div class='foot'>\n";
        echo "<a href='administration.php'>Администрация</a>\n";
        echo "</div>\n";
    }
    echo "<div class='foot'>\n";
    echo "<a href='/adm_panel/'>В админку</a>\n";
    echo "</div>\n";
}

include_once '../sys/inc/tfoot.php';
