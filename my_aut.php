<?php
include_once 'sys/inc/start.php';
include_once H.'sys/inc/compress.php';
include_once H.'sys/inc/sess.php';
include_once H.'sys/inc/settings.php';
include_once H.'sys/inc/db_connect.php';
include_once H.'sys/inc/ipua.php';
include_once H.'sys/inc/fnc.php';
include_once H.'sys/inc/user.php';

only_reg();

$set['title'] = 'История входов';
include_once H.'sys/inc/thead.php';
title();
aut();

$k_post = $db->query(
                    'SELECT COUNT(*) FROM `user_log` WHERE `id_user`=?i',
                    [$user['id']])->el();
$k_page = k_page($k_post, $set['p_str']);
$page = page($k_page);
$start = $set['p_str']*$page-$set['p_str'];

if (empty($k_post)) {
    echo '<div class="mess">';
    echo 'Нет записаных авторизаций';
    echo '</div>';
}
$q = $db->query(
            'SELECT * FROM `user_log` WHERE `id_user`=?i ORDER BY `id` DESC  LIMIT ?i OFFSET ?i',
            [$user['id'], $set['p_str'], $start]);
while ($post = $q->row()) {
    // Лесенка
    echo '<div class="' . ($num % 2 ? "nav1" : "nav2") . '">';
    $num++;
    echo '<img src="/style/my_menu/logout_16.png" alt="" />';
    if ($post['method'] != 1) {
        echo ' Автовход<br />';
    } else {
        echo ' Ввод логина и пароля (' . vremja($post['time']) . ')<br />';
    }
            
    echo 'IP: ' . long2ip($post['ip']) . '<br />';
    echo 'Браузер: ' . output_text($post['ua']);
    echo '</div>';
}

// Вывод страниц
if ($k_page > 1) {
    str("?", $k_page, $page);
}

echo '<div class="foot">';
echo '<img src="/style/icons/str.gif" alt="*" /> <a href="/info.php">Моя cтраница</a><br />';
echo '<img src="/style/icons/str.gif" alt="*" /> <a href="/umenu.php">Мое меню</a><br />';
echo '</div>';

include_once H.'sys/inc/tfoot.php';
