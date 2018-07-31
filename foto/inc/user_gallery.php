<?php
if (!isset($user) && !isset($input_get['id_user'])) {
    header('Location: /foto/?' . SID);
    exit;
}
if (isset($user)) {
    $ank['id'] = $user['id'];
}
if (isset($input_get['id_user'])) {
    $ank['id'] = $input_get['id_user'];
}
// Автор альбома
$ank = $db->query('SELECT `id`, `nick`, `level`, `group_access` FROM `user` WHERE `id`=?i', [$ank['id']])->row();
if (!$ank) {
    header('Location: /foto/?' . SID);
    exit;
}
// Бан пользователя
if (isset($user) && $db->query(
    "SELECT COUNT(*) FROM `ban` WHERE `razdel`=? AND `id_user`=?i AND (`time`>?i OR `view`=? OR `navsegda`=?i)",
                               ['foto', $user['id'], $time, '0', 1]
)->el()) {
    $_SESSION['message'] = 'Доступ к альбомам запрещен';
    header('Location: /ban.php?'.SID);
    exit;
}

// заголовок страницы
$set['title'] = $ank['nick'] . ' - Фотоальбомы';

// Это при создании нового альбома
if (isset($user)) {
    include 'inc/gallery_act.php';
}
include_once H . 'sys/inc/thead.php';
title();
aut();
err();
// Создание альбомов
if (isset($user)) {
    include 'inc/gallery_form.php';
}

echo '<div class="foot">';
echo '<img src="/style/icons/str2.gif" alt="*"> ' . user::nick($ank['id']) . ' | <b>Альбомы</b></div>';
if ($ank['id'] == $user['id']) {
    echo '<div class="mess"><a href="/foto/' . $ank['id'] . '/?act=create"><img src="/style/icons/apply14.png"> Новый альбом</a></div>';
}
// Подключаем приватность стр.
include H.'sys/add/user.privace.php';

$k_post = $db->query("SELECT COUNT(*) FROM `gallery` WHERE `id_user`=?i", [$ank['id']])->el();
$k_page = k_page($k_post, $set['p_str']);
$page = page($k_page);
$start = $set['p_str']*$page-$set['p_str'];

echo '<table class="post">';
if ($k_post == 0) {
    echo '<div class="mess">';
    echo 'Фотоальбомов нет';
    echo '</div>';
}

$q = $db->query(
    "SELECT glr.*, (
SELECT COUNT(*) FROM `gallery_foto` WHERE `id_gallery`=`glr`.`id`) cnt
FROM `gallery` glr WHERE glr.`id_user`=?i ORDER BY glr.`time` DESC LIMIT ?i OFFSET ?i",
                [$ank['id'], $set['p_str'], $start]
);
while ($post = $q->row()) {
    // Лесенка
    echo '<div class="' . ($num % 2 ? "nav1" : "nav2") . '">';
    $num++;
    
    echo '<img src="/style/themes/' . $set['set_them'] . '/loads/14/' . ($post['pass'] != null || $post['privat'] != 0 ? 'lock.gif' : 'dir.png') . '" alt="*" /> ';
    echo '<a href="/foto/' . $ank['id'] . '/' . $post['id'] . '/">' . text($post['name']) . '</a> (' . $post['cnt'] . ' фото) ';
    
    if (isset($user) && (user_access('foto_alb_del') || $user['id'] == $ank['id'])) {
        echo '[<a href="/foto/' . $ank['id'] . '/' . $post['id'] . '/?edit=rename"><img src="/style/icons/edit.gif" alt="*" /> ред</a>] ';
        echo '[<a href="/foto/' . $ank['id'] . '/' . $post['id'] . '/?act=delete"><img src="/style/icons/delete.gif" alt="*" /> удл</a>]';
    }
    
    echo '<br />';
    
    if ($post['opis'] == null) {
        echo 'Без описания<br />';
    } else {
        echo '<div class="text">' . output_text($post['opis']) . '</div>';
    }
    echo 'Создан: ' . vremja($post['time_create']);
    
    echo '</div>';
}
echo '</table>';
// Вывод страниц
if ($k_page > 1) {
    str('?', $k_page, $page);
}
echo '<div class="foot">';
echo '<img src="/style/icons/str2.gif" alt="*"> ' . user::nick($ank['id']) . ' | <b>Альбомы</b>';
echo '</div>';

include_once H . 'sys/inc/tfoot.php';
exit;
