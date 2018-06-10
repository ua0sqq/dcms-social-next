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

$set['title'] = 'Лидеры'; // заголовок страницы
include_once '../../sys/inc/thead.php';
title();
aut();
err();

echo '<div class="foot">';
echo '<img src="/style/icons/lider.gif" alt="S"/> <a href="/user/money/liders.php">Стать лидером</a>';
echo '</div>';

$k_post=$db->query(
                   'SELECT COUNT( * ) FROM `liders` WHERE `time` > ?i',
                            [$time])->el();

if (!$k_post) {
    echo '<div class="mess">';
    echo 'Нет лидеров';
    echo '</div>';
} else {
    $k_page=k_page($k_post, $set['p_str']);
    $page=page($k_page);
    $start=$set['p_str']*$page-$set['p_str'];

    $q=$db->query(
            'SELECT ldr.*, u.id AS id_user FROM `liders` ldr
JOIN `user` u ON u.id=ldr.id_user
WHERE ldr.`time` > ?i ORDER BY ldr.stav DESC LIMIT ?i OFFSET ?i',
                    [$time, $set['p_str'], $start]);
    while ($post = $q->row()) {
        if ($num==0) {
            echo '<div class="nav1">';
            $num=1;
        } elseif ($num==1) {
            echo '<div class="nav2">';
            $num=0;
        }

        echo status($post['id_user']); // Аватарка
        echo group($post['id_user']) , ' ' . user::nick($post['id_user']) . ' ';
        echo medal($post['id_user']) , online($post['id_user']) . ' (' . vremja($post['time']) . ')<br />';
        echo 'Ставка: <b class="off">' . $post['stav'] . '</b> <b class="on">' . $sMonet[0] . '</b><br />';
        echo output_text($post['msg']) . '<br />';
        if (isset($user) && $user['level'] > 2) {
            echo '<div style="text-align:right;"><a href="delete.php?id=' . $post['id_user'] . '"><img src="/style/icons/delete.gif" alt="*"/></a></div>';
        }
        echo '</div>';
    }

    if ($k_page > 1) {
        str('?', $k_page, $page);
    }
}
echo '<div class="foot">';
echo '<img src="/style/icons/lider.gif" alt="S"/> <a href="/user/money/liders.php">Стать лидером</a>';
echo '</div>';

include_once '../../sys/inc/tfoot.php';
