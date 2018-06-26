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

if (isset($user)) {
    $db->query(
        "DELETE FROM `chat_who` WHERE `id_user`=?i",
               [$user['id']]);
}

$db->query("DELETE FROM `chat_who` WHERE `time`<?i", [$time-120]);

if (isset($user) && isset($_GET['id'])
    && $db->query(
        "SELECT COUNT(*) FROM `chat_rooms` WHERE `id`=?i",
                                                     [$_GET['id']])->el() && isset($_GET['msg'])
    && $db->query(
        "SELECT COUNT(*) FROM `user` WHERE `id`=?i",
                  [$_GET['msg']]
    )->el()) {
    $room=$db->query(
        "SELECT * FROM `chat_rooms` WHERE `id`=?i",
                     [$_GET['id']])->row();
    $ank=$db->query(
        "SELECT * FROM `user` WHERE `id`=?i",
                    [$_GET['msg']]
    )->row();
    if (isset($user)) {
        $db->query(
            "INSERT INTO `chat_who` (`id_user`, `time`,  `room`) values(?i, ?i, ?i)",
                   [$user['id'], $time, $room['id']]);
    }
    if ($set['time_chat']!=0) {
        header("Refresh: $set[time_chat]; url=/chat/room/$room[id]/".rand(1000, 9999).'/');
    } // автообновление
    $set['title']='Чат - '.$room['name'].' ('.$db->query(
    "SELECT COUNT(*) FROM `chat_who` WHERE `room`=?i",
            [$room['id']])->el().')'; // заголовок страницы
    include_once '../sys/inc/thead.php';
    title();
    echo "<a href='/info.php?id=$ank[id]'>Посмотреть анкету</a><br />\n";
    echo "<form method=\"post\" action=\"/chat/room/$room[id]/".rand(1000, 9999)."/\">\n";
    echo "Сообщение:<br />\n<textarea name=\"msg\">$ank[nick], </textarea><br />\n";
    echo "<label><input type=\"checkbox\" name=\"privat\" value=\"$ank[id]\" /> Приватно</label><br />\n";
    if ($user['set_translit']==1) {
        echo "<label><input type=\"checkbox\" name=\"translit\" value=\"1\" /> Транслит</label><br />\n";
    }
    echo "<input value=\"Отправить\" type=\"submit\" />\n";
    echo "</form>\n";
    echo "<div class=\"foot\">\n";
    echo " <img src='/style/icons/str2.gif' alt='*'><a href=\"/chat/room/$room[id]/".rand(1000, 9999)."/\">В комнату</a><br />\n";
    echo " <img src='/style/icons/str2.gif' alt='*'><a href=\"/chat/\">Прихожая</a><br />\n";
    echo "</div>\n";
    include_once '../sys/inc/tfoot.php';
}
if (isset($_GET['id']) && $db->query(
    "SELECT COUNT(*) FROM `chat_rooms` WHERE `id`=?i",
                                     [$_GET['id']])->el()) {
    $room=$db->query(
        "SELECT * FROM `chat_rooms` WHERE `id`=?i",
                                     [$_GET['id']])->row();
    if (isset($user)) {
        $db->query(
            "INSERT INTO `chat_who` (`id_user`, `time`,  `room`) VALUES(?i, ?i, ?i)",
                   [$user['id'], $time, $room['id']]);
    }
    if ($set['time_chat'] !=0) {
        // автообновление
        header("Refresh: $set[time_chat]; url=/chat/room/$room[id]/".rand(1000, 9999).'/');
    }
    $set['title']='Чат - '.$room['name'].' ('.$db->query(
        "SELECT COUNT(*) FROM `chat_who` WHERE `room`=?i",
                                                         [$room['id']])->el().')';
    // заголовок страницы
    include_once '../sys/inc/thead.php';
    title();
    include 'inc/room.php';
    echo "<div class=\"foot\">\n";
    echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/chat/\">Прихожая</a><br />\n";
    echo "</div>\n";
    include_once '../sys/inc/tfoot.php';
}
$set['title']='Чат - прихожая'; // заголовок страницы
include_once '../sys/inc/thead.php';
title();
include 'inc/admin_act.php';
err();
aut();

$q = $db->query("SELECT ch.*, (
SELECT COUNT(*) FROM `chat_who` WHERE `room`=`ch`.`id`) cnt
FROM `chat_rooms` ch ORDER BY `ch`.`pos` ASC")->assoc();

if (!count($q)) {
    echo "  <div class='mess'>\n";
    echo "Нет комнат\n";
    echo "  </div>\n";
}
foreach ($q as $room) {
    /*-----------зебра-----------*/
    if ($num==0) {
        echo '<div class="nav1">'."\n";
        $num=1;
    } elseif ($num==1) {
        echo '<div class="nav2">'."\n";
        $num=0;
    }
    /*---------------------------*/
    echo "<img src='/style/themes/$set[set_them]/chat/14/room.png' alt='' /> ";
    echo "<a href='/chat/room/$room[id]/".rand(1000, 9999)."/'>$room[name] (".$room['cnt'].")</a> \n";
    if (user_access('chat_room')) {
        echo "<a href='?set=$room[id]'><img src='/style/icons/edit.gif' alt='*' /></a> \n";
    }
    if ($room['opis']!=null) {
        echo '<br />'.output_text($room['opis'])."<br />\n";
    }
    echo "   </div>\n";
}

echo "<div class=\"foot\">\n";
echo "<img src='/style/icons/str.gif' alt='*'> <a href='/chat/who.php'>Кто в чате?</a><br />\n";
echo "</div>\n";

include 'inc/admin_form.php';
include_once '../sys/inc/tfoot.php';
