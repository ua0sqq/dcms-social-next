<?php
include_once '../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/adm_check.php';
include_once H . 'sys/inc/user.php';

user_access('adm_accesses', null, 'index.php?'.SID);
adm_check();

if ($id_group = filter_input(INPUT_GET, 'id_group', FILTER_VALIDATE_INT))  {
if ($db->query('SELECT COUNT(*) FROM `user_group` WHERE `id`=?i',
                                           [$id_group])->el()) {
    $group = $db->query('SELECT * FROM `user_group` WHERE `id`=?i',
                                           [$id_group])->row();

    $set['title'] = 'Группа "' . $group['name'] . '" - привилегии'; // заголовок страницы
include_once H . 'sys/inc/thead.php';
    title();

    if (isset($_POST['accesses'])) {
        $db->query('DELETE FROM `user_group_access` WHERE `id_group`=?i',
                   [$group['id']]);

        $q = $db->query('SELECT * FROM `all_accesses`');

        while ($post = $q->row()) {
            $type = $post['type'];
            if (isset($_POST[$type]) && $_POST[$type] == 1) {
                $db->query('INSERT INTO `user_group_access` (`id_group`, `id_access`) VALUES (?i, ?)',
                           [$group['id'], $post['type']]);
            }
        }
        msg('Привилегии успешно изменены');
    }
    aut();
    echo "<form method='post' action='?id_group=$group[id]&amp;$passgen'>\n";
    $q = $db->query('SELECT ac.*, (
SELECT COUNT( * ) FROM `user_group_access` WHERE `id_group`=' . $group['id'] . ' AND `id_access`=ac.`type`) cnt
FROM `all_accesses` ac ORDER BY ac.`name` ASC');
    while ($post = $q->row()) {
        echo "<label>";
        echo "<input type='checkbox'".($post['cnt'] == 1 ? " checked='checked'":null)." name='$post[type]' value='1' />";
        echo $post['name'];
        echo "</label><br />\n";
    }
    echo "<input value='Применить' name='accesses' type='submit' />\n";
    echo "</form>\n";

    echo "<div class='foot'>\n";
    echo "&laquo;<a href='accesses.php'>Группы</a><br />";
    echo "&laquo;<a href='index.php'>Админка</a><br />";
    echo "</div>\n";
    include_once H . 'sys/inc/tfoot.php';
}
}

$set['title']='Группы пользователей'; // заголовок страницы
include_once H . 'sys/inc/thead.php';
title() . aut();


$accesses = $db->query('SELECT gr.*, (
SELECT COUNT( * ) FROM `user_group_access` WHERE `id_group`=`gr`.`id`) cnt
FROM `user_group` gr ORDER BY gr.`id` ASC')->assoc();
if (count($accesses)) {
 echo "<div class='menu'>\n";
foreach ($accesses as $res) {
    echo "<div class=\"main\"><a href='?id_group=$res[id]'>$res[name] (L$res[level], ".$res['cnt'].")</a></div>\n";
}
echo "</div>\n";
}
if (user_access('adm_panel_show')) {
     echo "<div class='foot'>\n";
    echo " &laquo;<a href='index.php'>Админка</a>";
     echo "</div>\n";
}

include_once H . 'sys/inc/tfoot.php';
