<?php
include_once '../../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

if (isset($user)) {
    $ank['id'] = $user['id'];
}
if (isset($_GET['id'])) {
    $ank['id'] = intval($_GET['id']);
}

$ank = $db->query(
    'SELECT `id`, `nick` FROM `user` WHERE `id`=?i',
                        [$ank['id']])->row();
if (!$ank) {
    header("Location: /index.php?" . SID);
    exit;
}

if (isset($user) && isset($_GET['delete']) && $user['id'] == $ank['id']) {
    $db->query(
    "DELETE FROM `bookmarks` WHERE `id_object`=?i AND `id_user`=?i AND `type`=? LIMIT ?i",
           [$_GET['delete'], $user['id'], 'forum'], 1);
    
    $_SESSION['message'] = 'Закладка удалена';
    header("Location: ?page=" . intval($_GET['page']) . "" . SID);
    exit;
}

$set['title'] = 'Закладки - Форум';
include_once H . 'sys/inc/thead.php';
title();
aut();

echo '<div class="foot">';
echo '<img src="/style/icons/str2.gif" alt="*" /> <a href="/user/bookmark/index.php?id=' . $ank['id'] . '">Закладки</a> | <b>Форум</b>';
echo '</div>';
$k_post=$db->query(
    "SELECT COUNT(*) FROM `bookmarks` WHERE `id_user`=?i AND `type`=?",
                   [$ank['id'], 'forum'])->el();

$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];

if (!$k_post) {
    echo '<div class="mess">';
    echo 'Нет тем в закладках';
    echo '</div>';
} else {
    $q=$db->query(
            'SELECT bkm.`id_object`, thm.*, rzd.id AS id_razdel, rzd.name AS name_razdel, frm.id AS id_forum, frm.name AS name_forum, a.id AS autor_id, (
SELECT COUNT(*) FROM `forum_p` WHERE `id_them`=thm.id) cnt
FROM `bookmarks` bkm
JOIN `forum_t` thm ON thm.id=bkm.id_object
JOIN `forum_r` rzd ON rzd.id=thm.id_razdel
JOIN `forum_f` frm ON frm.id=thm.id_forum
JOIN `user` a ON a.`id`=thm.id_user
WHERE bkm.`id_user`=?i AND bkm.`type`=? ORDER BY bkm.`time` DESC LIMIT ?i OFFSET ?i',
                    [$ank['id'], 'forum', $set['p_str'], $start]);
    while ($them = $q->row()) {
        // Лесенка дивов
        if ($num == 0) {
            echo '<div class="nav1">';
            $num = 1;
        } elseif ($num == 1) {
            echo '<div class="nav2">';
            $num = 0;
        }
        // Иконка темы
        echo '<img src="/style/themes/' . $set['set_them'] . '/forum/14/them_' . $them['up'] . $them['close'] . '.png" alt="" /> ';
        // Ссылка на тему
        echo '<a href="/forum/' . $them['id_forum'] . '/' . $them['id_razdel'] . '/' . $them['id'] . '/">' . htmlspecialchars($them['name']) . '</a> 
	<a href="/forum/' . $them['id_forum'] . '/' . $them['id_razdel'] . '/' . $them['id'] . '/?page=' . $pageEnd . '"> (' . $them['cnt'] . ')</a><br/>';
        // Подфорум и раздел
        echo '<a href="/forum/' . $them['id_forum'] . '/">' . htmlspecialchars($them['name_forum']) . '</a> &gt; <a href="/forum/' . $them['id_forum'] . '/' . $them['id_razdel'] . '/">' . htmlspecialchars($them['name_razdel']) . '</a><br />';
        // Автор темы
        echo 'Автор: ' . user::nick($them['autor_id']) . ' (' . vremja($them['time_create']) . ')<br />';
        $post = $db->query(
                    'SELECT p.time, u.id, u.nick FROM `forum_p` p
JOIN `user` u ON u.id=p.id_user
WHERE `id_them`=?i AND `id_razdel`=?i AND `id_forum`=?i ORDER BY p.`time` DESC LIMIT ?i',
                            [$them['id'], $them['id_razdel'], $them['id_forum'], 1])->row();
        if ($post) {
            echo 'Посл.: ' . user::nick($post['id']) . ' (' . vremja($post['time']) . ')<br />';
        }
    
        echo '</div>';
    }
}
echo '</table>';echo '<div class="foot">';
echo '<img src="/style/icons/str2.gif" alt="*" /> <a href="/user/bookmark/index.php?id=' . $ank['id'] . '">Закладки</a> | <b>Форум</b>';
echo '</div>';

include_once H . 'sys/inc/tfoot.php';
