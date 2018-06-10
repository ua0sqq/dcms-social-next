<?php
/*
=======================================
Подарки для Dcms-Social
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

only_reg();

$width = ($webbrowser == 'web' ? '100' : '70'); // Размер подарков при выводе в браузер
    if (isset($_GET['id'])) {
        $ank['id'] = intval($_GET['id']);
    } else {
        $ank['id'] = $user['id'];
    } // Определяем юзера
    
    $ank = get_user($ank['id']);
    if (!$ank || $ank['id'] == 0) {
        header("Location: /index.php?".SID);
        exit;
    }
    $set['title'] = 'Подарки ' . $ank['nick'];
    
    include_once '../../sys/inc/thead.php';
    title();
    aut();   
/*
==================================
Вывод подарков пользователя
==================================
*/
    echo '<div class="foot">';
    echo '<img src="/style/icons/str2.gif" alt="*" /> <a href="/info.php?id=' . $ank['id'] . '">' . $ank['nick'] . '</a> | <b>Подарки</b>';
    echo '</div>';
    
    // Список подарков
    $sql = ($ank['id'] != $user['id'] ? ' AND `status` ="1"' : '');
    $k_post = $db->query(
        "SELECT COUNT( * ) FROM `gifts_user` WHERE `id_user`=?i;?q",
                         [$ank['id'], $sql])->el();
    if ($k_post == 0) {
        echo '<div class="mess">';
        echo 'Нет подарков';
        echo '</div>';
    }
    
    $k_page=k_page($k_post, $set['p_str']);
    $page=page($k_page);
    $start=$set['p_str']*$page-$set['p_str'];
    $q = $db->query(
        "SELECT t1.`id`, t1.`status`, t1.`coment`, t1.`id_gift`, t1.`id_ank`, t1.`time`, t2.name AS gft_name
FROM `gifts_user` t1
JOIN `gift_list` t2 ON t2.id=t1.id_gift
WHERE t1.`id_user`=?i;?q ORDER BY t1.`time` DESC LIMIT ?i OFFSET ?i",
                         [$ank['id'], $sql, $set['p_str'], $start]);
    
    while ($post = $q->row()) {
        $anketa = get_user($post['id_ank']);    
        /*-----------зебра-----------*/
        if ($num==0) {
            echo '<div class="nav1">';
            $num=1;
        } elseif ($num==1) {
            echo '<div class="nav2">';
            $num=0;
        }
        /*---------------------------*/   
        echo '<img src="/sys/gift/' . $post['id_gift'] . '.png" style="max-width:' . $width . 'px;" alt="*" /><br />';
        echo '<img src="/style/icons/present.gif" alt="*" /> <a href="gift.php?id=' . $post['id'] . '"><b>' . htmlspecialchars($post['gft_name']) . '</b></a> :: ';
        echo 'от ' . group($anketa['id']) , '<a href="/info.php?id=' . $anketa['id'] . '">' . $anketa['nick'] . '</a>' ,  medal($anketa['id']) ,  online($anketa['id']) , ' ' . vremja($post['time']);
        if ($post['status'] == 0) {
            echo ' <font color=red>NEW</font> ';
        }
        echo '</div>';
    }
    if ($k_page>1) {
        str('index.php?id=' . intval($_GET['id']) . '&amp;', $k_page, $page);
    } // Вывод страниц
    echo '<div class="foot">';
    echo '<img src="/style/icons/str2.gif" alt="*" /> <a href="/info.php?id=' . $ank['id'] . '">' . $ank['nick'] . '</a> | <b>Подарки</b>';
    echo '</div>';
	
include_once '../../sys/inc/tfoot.php';
