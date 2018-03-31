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
               [$_GET['delete'], $user['id'], 'file', 1]);
    
    $_SESSION['message'] = 'Закладка удалена';
    header("Location: ?page=" . intval($_GET['page']) . "" . SID);
    exit;
}
if (!$ank) {
    header("Location: /index.php?".SID);
    exit;
}
$set['title']='Закладки - Файлы - '. $ank['nick'] .''; // заголовок страницы
include_once '../../sys/inc/thead.php';
title();
err();
aut(); // форма авторизации

echo '<div class="foot">'."\n";
echo '<img src="/style/icons/str2.gif" alt="*" /> <a href="/user/bookmark/index.php?id=' . $ank['id'] . '">Закладки</a> | <b>Файлы</b>'."\n";
echo '</div>'."\n";
$k_post=$db->query(
    "SELECT COUNT(*) FROM `bookmarks` WHERE `id_user`=?i AND `type`=?",
                   [$ank['id'], 'file'])->el();

$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];

if (!$k_post) {
    echo '<div class="mess">'."\n";
    echo 'Нет файлов в закладках'."\n";
    echo '</div>'."\n";
} else {
    $q = $db->query(
    "SELECT bkm.`id`, bkm.`id_object`, obf.id AS id_file, obf.name, obf.ras, obd.dir FROM `bookmarks` bkm
JOIN `obmennik_files` obf ON obf.id=bkm.id_object
JOIN `obmennik_dir` obd ON obd.id=obf.id_dir
WHERE bkm.`id_user`=?i AND bkm.`type`=? ORDER BY bkm.`id` DESC LIMIT ?i OFFSET ?i",
                [$ank['id'], 'file', $set['p_str'], $start]);
    while ($post = $q->row()) {
        if ($num==0) {
            echo '<div class="nav1">'."\n";
            $num=1;
        } elseif ($num==1) {
            echo '<div class="nav2">'."\n";
            $num=0;
        }
        echo '<a href="/obmen' . $post['dir'] . $post['id_file'] . '.' . $post['ras'] . '?showinfo">' . htmlspecialchars($post['name']) . '.' . $post['ras'] . '</a>';
        if ($ank['id'] == $user['id']) {
            echo '<div style="text-align:right;">'."\n".
        '<a href="?delete=' . $post['id'] . '&amp;page=' . $page . '"><img src="/style/icons/delete.gif" alt="*" /></a>'."\n".
        '</div>'."\n";
        }
        echo '</div>'."\n";
    }

    if ($k_page>1) {
        str('?', $k_page, $page);
    } // Вывод страниц
}
echo '<div class="foot">'."\n";
echo '<img src="/style/icons/str2.gif" alt="*" /> <a href="/user/bookmark/index.php?id=' . $ank['id'] . '">Закладки</a> | <b>Файлы</b>'."\n";
echo '</div>'."\n";

include_once '../../sys/inc/tfoot.php';
