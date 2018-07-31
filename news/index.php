<?php
include_once '../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

// Заголовок
$set['title'] = 'Новости';
include_once H . 'sys/inc/thead.php';
title();
aut();

// Колличество новостей
$k_post = $db->query("SELECT COUNT(*) FROM `news`")->el();

if (!$k_post) {
    echo '<div class="mess">';
    echo 'Нет новостей';
    echo '</div>';
} else {
    $k_page = k_page($k_post, $set['p_str']);
    $page = page($k_page);
    $start = $set['p_str'] * $page - $set['p_str'];
    // Выборка новостей
    $q = $db->query(
    "SELECT nws.*, (
SELECT COUNT( * ) FROM `news_komm` WHERE `id_news` =`nws`.`id`) cnt
FROM `news` nws ORDER BY nws.`id` DESC LIMIT ?i OFFSET ?i",
                [$set['p_str'], $start]);

    while ($post = $q->row()) {
        // Лесенка
        echo '<div class="' . ($num % 2 ? "nav1" : "nav2") . '">';
        $num++;
        // Заголовок новости
        echo '<a id="link_menu" href="/news/news.php?id=' . $post['id'] . '"><img src="/style/icons/rss.png" alt="*" /> ' . text($post['title']) . '</a> ';
        // Колличество комментариев
        echo '(' . $post['cnt'] . ')<br />';
        // Часть текста
        echo '<div class="text">' . output_text($post['msg']) . '</div>';
        echo '<a href="/news/news.php?id=' . $post['id'] . '">Читать далее &gt;&gt;&gt;</a>';
        echo '</div>';
    }

    // Вывод страниц
    if ($k_page) {
        str('index.php?', $k_page, $page);
    }
}
if (user_access('adm_news')) {
    echo '<div class="foot">';
    echo '<img src="/style/icons/ok.gif" alt="*" />  <a href="add.php">Создать новость</a><br />';
    echo '</div>';
}
include_once H . 'sys/inc/tfoot.php';
