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
				[$_GET['delete'], $user['id'], 'foto',  1]);
    
    $_SESSION['message'] = 'Закладка удалена';
    header("Location: ?page=" . intval($_GET['page']) . "" . SID);
    exit;
}

$set['title']='Закладки - Фото - ' . $ank['nick']; // заголовок страницы
include_once '../../sys/inc/thead.php';
title();
err();
aut();

echo '<div class="foot">'."\n";
echo '<img src="/style/icons/str2.gif" alt="*" /> <a href="/user/bookmark/index.php?id=' . $ank['id'] . '">Закладки</a> | <b>Фото</b>'."\n";
echo '</div>'."\n";

$k_post=$db->query(
                    'SELECT COUNT(*) FROM `bookmarks` WHERE `id_user`=?i AND `type`=?',
                            [$ank['id'], 'foto'])->el();

if (!$k_post) {
    echo '<div class="mess">'."\n";
    echo 'Нет Фотографий в закладках'."\n";
    echo '</div>'."\n";
} else {
    $k_page=k_page($k_post, $set['p_str']);
    $page=page($k_page);
    $start=$set['p_str']*$page-$set['p_str'];

    $q=$db->query(
				'SELECT bkm.id, gf.id AS id_foto, gf.id_gallery, gf.name, gf.ras, gf.id_user, gf.`time`
FROM `bookmarks` bkm
JOIN `gallery_foto` gf ON gf.id=bkm.id_object
WHERE bkm.`id_user`=?i AND bkm.`type`=? ORDER BY id DESC LIMIT ?i OFFSET ?i',
						[$ank['id'], 'foto', $set['p_str'], $start]);
    while ($post = $q->row()) {
        if ($num==0) {
            echo '<div class="nav1">'."\n";
            $num=1;
        } elseif ($num==1) {
            echo '<div class="nav2">'."\n";
            $num=0;
        }

        echo '<a href="/foto/' . $post['id_user'] . '/' . $post['id_gallery'] . '/' . $post['id_foto'] . '/" title="Перейти к фото"><img style=" padding: 2px; height: 45px; width: 45px;" src="/foto/foto48/' . $post['id_foto'] . '.' . $post['ras'] . '" alt="*" /> ' .
htmlspecialchars($post['name']) . '</a>  (' . vremja($post['time']) . ')';
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
echo '<img src="/style/icons/str2.gif" alt="*" /> <a href="/user/bookmark/index.php?id=' . $ank['id'] . '">Закладки</a> | <b>Фото</b>'."\n";
echo '</div>'."\n";

include_once '../../sys/inc/tfoot.php';
