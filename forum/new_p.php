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

// Заголовок страницы
$set['title']='Форум - новое в темах';
include_once '../sys/inc/thead.php';
title();
aut(); // форма авторизации
// Меню возврата
echo '<div class="foot">';
echo '<img src="/style/icons/str2.gif" /> <a href="/forum/">Форум</a> | <b>Новые посты</b>';
echo '</div>';
$id_forum=[0];

if (!isset($user) || $user['level'] == 0) {
    $q222=$db->query('SELECT * FROM `forum_f` WHERE `adm`="1"')->assoc();
    if (count($q222)) {
        unset($id_forum[0]);
        foreach ($q222 as $adm_f) {
            //$adm_add[] = '`id_forum` <> ' . $adm_f['id'];
            $id_forum[] = $adm_f['id'];
        }
    }
}
$k_post=$db->query('SELECT COUNT(*) FROM `forum_t` WHERE `id_forum` NOT IN(?li)', [$id_forum])->el();
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];

echo '<table class="post">';

$q=$db->query(
    'SELECT thm.*, frm.name AS frm_name, rzd.name AS rzd_name, a.id AS autor_id, (
	SELECT COUNT(*) FROM `forum_p` WHERE `id_forum` =thm.id_forum AND `id_razdel` =thm.id_razdel AND `id_them` =thm.id) as cnt
FROM `forum_t` thm
JOIN `forum_f` frm ON frm.id=thm.id_forum
JOIN `forum_r` rzd ON rzd.id=thm.id_razdel
JOIN `user` a ON a.`id`=thm.id_user
WHERE thm.`id_forum` NOT IN(?li) ORDER BY thm.`time` DESC  LIMIT ?i OFFSET ?i',
              [$id_forum, $set['p_str'], $start]);
// Если список пуст
if ($k_post == 0) {
    echo '<div class="mess">';
    echo 'Ваших тем нет в форуме';
    echo '</div>';
}
while ($them = $q->row()) {
    echo '<div class="' . ($num % 2 ? "nav1" : "nav2") . '">';
    $num++;
    // Иконка темы
    echo '<img src="/style/themes/' . $set['set_them'] . '/forum/14/them_' . $them['up'] . $them['close'] . '.png" alt="" /> ';
    // Ссылка на тему
    echo '<a href="/forum/' . $them['id_forum'] . '/' . $them['id_razdel'] . '/' . $them['id'] . '/">' . text($them['name']) . '</a> 
	<a href="/forum/' . $them['id_forum'] . '/' . $them['id_razdel'] . '/' . $them['id'] . '/?page=' . $pageEnd . '"> (' . $them['cnt'] . ')</a><br/>';
    
    // Подфорум и раздел
    echo '<a href="/forum/' . $them['id_forum'] . '/">' . text($them['frm_name']) . '</a> &gt; <a href="/forum/' . $them['id_forum'] . '/' . $them['id_razdel'] . '/">' . text($them['rzd_name']) . '</a><br />';
    echo 'Автор: <a href="/info.php?id=' . $them['autor_id'] . '">' . user::nick($them['autor_id']) . '</a> (' . vremja($them['time_create']) . ')<br />';
    $post = $db->query(
        "SELECT p.time, u.id, u.nick FROM `forum_p` p
JOIN `user` u ON u.id=p.id_user
WHERE `id_them`=?i AND `id_razdel`=?i AND `id_forum`=?i ORDER BY p.`time` DESC LIMIT ?i",
[$them['id'], $them['id_razdel'], $them['id_forum'], 1])->row();
	
    if ($post) {
        echo 'Посл.: <a href="/info.php?id=' . $post['id'] . '">' . $post['nick'] . '</a> (' . vremja($post['time']) . ')<br />';
    }
    echo '</div>';
}
echo '</table>';
// Вывод cтраниц
if ($k_page>1) {
    str("?", $k_page, $page);
}
// Меню возврата
echo '<div class="foot">';
echo '<img src="/style/icons/str2.gif" /> <a href="/forum/">Форум</a> | <b>Новые посты</b>';
echo '</div>';
include_once '../sys/inc/tfoot.php';
