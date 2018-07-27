<?php // \$db->query\(.*('\$.*]').*\)
err();
aut();
if (isset($user) && (!isset($_SESSION['time_c_t_forum']) || $_SESSION['time_c_t_forum']<$time-600 || $user['level']>0)) {
    echo '<div class="foot">';
    echo '<img src="/style/icons/plus.gif" alt="*"> <a href="/forum/' . $forum['id'] . '/' . $razdel['id'] . '/?act=new" title="Создать новую тему">Новая тема</a><br />';
    echo '</div>';
}
$k_post=$db->query("SELECT COUNT( * ) FROM `forum_t` WHERE `id_forum`=?i AND `id_razdel`=?i",
                   [$forum['id'], $razdel['id']])->el();

if (!$k_post) {
    echo '<div class="mess">';
    echo 'Нет тем в разделе ' . text($razdel['name']);
    echo '</div>';
} else {

$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];

$q=$db->query("SELECT frt.*, (
SELECT COUNT( * ) FROM `forum_p` WHERE `id_them`=frt.`id`) cnt_post, (
SELECT `id_user` FROM `forum_p` WHERE `id_them`=frt.`id` ORDER BY `time` DESC LIMIT 1) AS id_user_post, (
SELECT max(`time`) FROM `forum_p` WHERE `id_them`=frt.`id`) AS time_post
FROM `forum_t` frt
WHERE frt.`id_forum`=?i AND frt.`id_razdel`=?i ORDER BY `up` DESC, frt.`time` DESC LIMIT ?i, ?i",
              [$forum['id'], $razdel['id'], $start, $set['p_str']]);

while ($them = $q->row()) {
    if ($num == 0) {
        echo '<div class="nav1">';
        $num = 1;
    } elseif ($num == 1) {
        echo '<div class="nav2">';
        $num = 0;
    }
    if ($them['close']==1) {
        $closed='<img src="/style/icons/topic_locked.gif">';
    } else {
        $closed=null;
    }
    if ($them['up']==1) {
        $up='<img src="/style/icons/stick.gif">';
    } else {
        $up=null;
    }
    echo $up." ";
    echo '<a href="/forum/' . $forum['id'] . '/' . $razdel['id'] . '/' . $them['id'] . '/">' . text($them['name']) . '</a> <font color="#666">(' . $them['cnt_post'] . ')';
    echo ' '.$closed.' ';
    echo '<span style="float:right;">'.vremja($them['time_create']).'</span></font><br/>';
    echo user::nick($them['id_user']).'';

    if (!empty($them['id_user_post'])) {
        echo ' / '.user::nick($them['id_user_post']).' (' . vremja($them['time_post']) . ')';
    }
    echo '</div>';
}

if ($k_page>1) {
    str("/forum/$forum[id]/$razdel[id]/?", $k_page, $page);
} // Вывод страниц
}