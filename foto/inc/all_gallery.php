<?php
// TODO: ???
/* Бан пользователя */
if (isset($user) && $db->query("SELECT COUNT(*) FROM `ban` WHERE `razdel` = 'foto' AND `id_user` = '$user[id]' AND (`time` > '$time' OR `view` = '0' OR `navsegda` = '1')")->el()) {
    header('Location: /ban.php?'.SID);
    exit;
}

$set['title'] = 'Фотоальбомы'; // заголовок страницы
include_once '../sys/inc/thead.php';
title();
aut();

$k_post = $db->query("SELECT COUNT(*) FROM `gallery`")->el();
$k_page = k_page($k_post, $set['p_str']);
$page = page($k_page);
$start = $set['p_str']*$page-$set['p_str'];

echo '<table class="post">';
if ($k_post == 0) {
    echo '<div class="mess">';
    echo 'Нет фотоальбомов';
    echo '</div>';
}
$q = $db->query("SELECT glr.*, (
SELECT COUNT(*) FROM `gallery_foto` WHERE `id_gallery` =`glr`.`id`) cnt
FROM `gallery` glr ORDER BY glr.`time` DESC LIMIT $start, $set[p_str]");
while ($post = $q->row()) {
    // Лесенка
    echo '<div class="' . ($num % 2 ? "nav1" : "nav2") . '">';
    $num++;
    echo '<img src="/style/themes/' . $set['set_them'] . '/loads/14/' . ($post['pass'] != null || $post['privat'] != 0 ? 'lock.gif' : 'dir.png') . '" alt="*" /> ';
    echo '<a href="/foto/' . $post['id_user'] . '/' . $post['id'] . '/">' . text($post['name']) . '</a> (' . $post['cnt'] . ' фото)<br />';
    if ($post['opis'] == null) {
        echo 'Без описания<br />';
    } else {
        echo output_text($post['opis']) . '<br />';
    }
    echo 'Создан: ' . vremja($post['time_create']) . '<br />';
    echo 'Автор: ';
    echo user::avatar($post['id_user'], 2) . user::nick($post['id_user'], 1, 1, 1);
    echo '</div>';
}
echo '</table>';
if ($k_page>1) {
    str('?', $k_page, $page);
} // Вывод страниц
if (isset($user)) {
    echo '<div class="foot">';
    echo '<img src="/style/icons/str.gif" alt="*"> <a href="/foto/' . $user['id'] . '/">Мои альбомы</a><br />';
    echo '</div>';
}
include_once '../sys/inc/tfoot.php';
exit;
