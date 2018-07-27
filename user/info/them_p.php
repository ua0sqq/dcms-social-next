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

only_reg('/aut.php');
$get_input = filter_input_array(INPUT_GET, [
    'id' => FILTER_VALIDATE_INT,
    'komm' => FILTER_DEFAULT
]);
if (isset($get_input['id'])) {
    if (isset($get_input['komm'])) {
        $tbl = 'forum_p';
    } else {
        $tbl = 'forum_t';
    }
    $data = [$tbl, $get_input['id'], $get_input['id']];
} else {
    if (isset($get_input['komm'])) {
        $tbl = 'forum_p';
    } else {
        $tbl = 'forum_t';
    }
    $data = [$tbl, $user['id'], $user['id']];
}

$ank = $db->query(
    'SELECT `id`, `nick`, (
SELECT COUNT( * ) FROM ?t WHERE `id_user`=?i) AS cnt
FROM `user` WHERE `id`=?i',
                         $data)->row();
if (!$ank) {
    $set['title'] = 'Пользователь не найден!';
    include_once '../../sys/inc/thead.php';
    title();
    aut();
    echo '<div class="err">Ошибка! Такой пользователь несуществует.</div>';
    include_once '../../sys/inc/tfoot.php';
}
$set['title'] = 'Темы и комментарии '.$ank['nick'];
include_once '../../sys/inc/thead.php';
title();
aut();

echo "<div class='nav1'>Автор: ";
echo group($ank['id']);
echo " ".user::nick($ank['id'], 1, 1, 1)."</div>";
// Sort themes OR comments
echo "<div class='nav1'>";
if (isset($get_input['komm'])) {
    echo "<a href='?id=".$ank['id']."'>Темы</a> | <b>Комментарии</b>";
} else {
    echo "<b>Темы</b> | <a href='?id=".$ank['id']."&komm'>Комментарии</a>";
}
echo "</div>";
// Если коммы смотрим
if (isset($get_input['komm'])) {
    $k_post = $ank['cnt'];
    $k_page = k_page($k_post, $set['p_str']);
    $page = page($k_page);
    $start = $set['p_str'] * $page - $set['p_str'];
    $q=$db->query(
        "SELECT id_them, msg, id, id_razdel, id_forum, id_them FROM `forum_p` WHERE `id_user`=?i ORDER BY `time` DESC LIMIT ?i, ?i",
                  [$ank['id'], $start, $set['p_str']]);
    while ($post=$q->row()) {
        echo "<div class='nav1'><a href='/forum/".$post['id_forum']."/".$post['id_razdel']."/".$post['id_them']."/'>";
        echo rez_text($post['msg'], 80)." ...";
        echo "</a></div>";
    }
    if ($k_page > 1) {
        str('them.php?id='.$ank['id'].'&amp;komm&amp;', $k_page, $page);
    } // Вывод страниц
} else {
    //Если темы смотрим
    $k_post = $ank['cnt'];
    $k_page = k_page($k_post, $set['p_str']);
    $page = page($k_page);
    $start = $set['p_str'] * $page - $set['p_str'];
    $q=$db->query(
        "SELECT t.id, t.`name`, t.id_forum, t.id_razdel, (
SELECT COUNT( * ) FROM `forum_p` WHERE `id_them`=t.id) post_cnt
FROM `forum_t` t
WHERE t.`id_user`=?i ORDER BY t.`time` DESC LIMIT ?i, ?i",
                  [$ank['id'], $start, $set['p_str']]);
    while ($them=$q->row()) {
        echo "<div class='nav1'><a href='/forum/" . $them['id_forum'] . "/" . $them['id_razdel'] . "/" . $them['id'] . "/'>";
        echo htmlspecialchars($them['name']) . " </a> (" . $them['post_cnt'] . ")";
        echo "</div>";
    }
    if ($k_page > 1) {
        str('them.php?id='.$ank['id'].'&', $k_page, $page);
    } // Вывод страниц
}
//Конец, ёптить
include_once '../../sys/inc/tfoot.php';
