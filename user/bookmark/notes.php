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
    'DELETE FROM `bookmarks` WHERE `id`=?i AND `id_user`=?i AND `type`=? LIMIT ?i',
            [$_GET['delete'], $user['id'], 'notes', 1]);
    
    $_SESSION['message'] = 'Закладка удалена';
    header("Location: ?page=" . intval($_GET['page']) . "" . SID);
    exit;
    exit;
}

$set['title']='Закладки - Дневники - '. $ank['nick'] .''; // заголовок страницы
include_once H . 'sys/inc/thead.php';
title();
err();
aut();

echo '<div class="foot">';
echo '<img src="/style/icons/str2.gif" alt="*" /> <a href="/user/bookmark/index.php?id=' . $ank['id'] . '">Закладки</a> | <b>Дневники</b>';
echo '</div>';

$k_post=$db->query(
                   'SELECT COUNT(*) FROM `bookmarks` WHERE `id_user`=?i AND `type`=?',
                            [$ank['id'], 'notes'])->el();


if (!$k_post) {
    echo '<div class="mess">'."\n";
    echo 'Нет дневников в закладках'."\n";
    echo '</div>'."\n";
} else {
    $k_page=k_page($k_post, $set['p_str']);
    $page=page($k_page);
    $start=$set['p_str']*$page-$set['p_str'];

    $q=$db->query(
            'SELECT bkm.id, bkm.`time`, n.id AS id_notes, n.name, n.id_user FROM `bookmarks` bkm
JOIN `notes` n ON n.id=bkm.id_object
WHERE bkm.`id_user`=?i AND bkm.`type`=? ORDER BY bkm.id DESC LIMIT ?i OFFSET ?i',
                    [$ank['id'], 'notes', $set['p_str'], $start]);

    while ($post = $q->row()) {
        if ($num==0) {
            echo '<div class="nav1">'."\n";
            $num=1;
        } elseif ($num==1) {
            echo '<div class="nav2">'."\n";
            $num=0;
        }

        echo '<img src="/style/icons/dnev.png" alt="S" /> <a href="/plugins/notes/list.php?id=' . $post['id_notes'] . '">' . htmlspecialchars($post['name']) . '</a> ' . vremja($post['time']) . '<br />'."\n";
        echo group($post['id_user']) , user::nick($post['id_user']);
        echo medal($post['id_user']) , online($post['id_user'])."\n";
        
		if ($ank['id'] == $user['id']) {
            echo '<div style="text-align:right;">'."\n".
			'<a href="?delete=' . $post['id'] . '&amp;page=' . $page . '"><img src="/style/icons/delete.gif" alt="*" /></a>'."\n".
			'</div>'."\n";
        }
        echo '</div>'."\n";
    }

    if ($k_page>1) {
        str('?', $k_page, $page);
    }
}
echo '<div class="foot">'."\n";
echo '<img src="/style/icons/str2.gif" alt="*" /> <a href="/user/bookmark/index.php?id=' . $ank['id'] . '">Закладки</a> | <b>Дневники</b>'."\n";
echo '</div>'."\n";

include_once H . 'sys/inc/tfoot.php';
