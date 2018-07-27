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

$get_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$ank['id'] = null;
if (isset($user)) {
    $ank['id'] = $user['id'];
}
if ($get_id) {
    $ank['id'] = abs($get_id);
}
if (!$ank['id']) {
    header("Location: /index.php?" . SID);
    exit;
}

$ank = $db->query(
                'SELECT `id`, `nick` FROM `user` WHERE `id`=?i',
                        [$ank['id']])->row();
if (!$ank) {
    header("Location: /index.php?" . SID);
    exit;
}
$set['title'] = 'Закладки ' . $ank['nick']; // заголовок страницы
include_once '../../sys/inc/thead.php';
title();
err();
aut(); // форма авторизации
echo '<div class="foot">';
echo '<img src="/style/icons/str2.gif" alt="*" /> <a href="/info.php?id=' . $ank['id'] . '">' . $ank['nick'] . '</a> | <b>Закладки</b>';
echo '</div>';
if (isset($user) && $ank['id'] == $user['id']) {
    echo '<div class="mess">';
    echo 'С помощью функции закладок вы можете сохранить ссылку на интересного вам человека, файл, фото, фотоальбом, заметки, обсуждения<br />';
    echo '</div>';
}
echo "<table>";
if (!isset($_GET['metki'])) {
    echo "<td class='nav1'><b>Закладки</b></td><td class='nav1'><a href='?id=".$ank['id']."&amp;metki'>Метки</a></td>";
} elseif (isset($_GET['metki'])) {
    echo "<td class='nav1'><a href='index.php'>Закладки</a></td><td class='nav1'><b>Метки</b></td>";
} echo "</table>";

if (isset($_GET['metki'])) {
    $cnt = $db->query('SELECT (
SELECT COUNT( * ) FROM `bookmarks` WHERE `id_user`=u.id AND `type`="people") people, (
SELECT COUNT( * ) FROM `bookmarks` WHERE `id_user`=u.id AND `type`="file") files, (
SELECT COUNT( * ) FROM `bookmarks` WHERE `id_user`=u.id AND `type`="foto") foto, (
SELECT COUNT( * ) FROM `bookmarks` WHERE `id_user`=u.id AND `type`="forum") forum, (
SELECT COUNT( * ) FROM `bookmarks` WHERE `id_user`=u.id AND `type`="notes") notes
FROM `user` u WHERE `u`.`id`=?i', [$ank['id']])->row();
    echo '<div class="nav1">';
    echo '<img src="/style/icons/druzya.png" alt="*" /> ';
    echo '<a href="/user/bookmark/people.php?id=' . $ank['id'] . '">Люди</a> (' . $cnt['people'] . ')';
    echo '</div>';
    echo '<div class="nav2">';
    echo '<img src="/style/icons/files.gif" alt="*" /> ';
    echo '<a href="/user/bookmark/files.php?id=' . $ank['id'] . '">Файлы</a> (' . $cnt['files'] . ')';
    echo '</div>';
    echo '<div class="nav1">';
    echo '<img src="/style/icons/foto.png" alt="*" /> ';
    echo '<a href="/user/bookmark/foto.php?id=' . $ank['id'] . '">Фотографии</a> (' . $cnt['foto'] . ')';
    echo '</div>';
    echo '<div class="nav2">';
    echo '<img src="/style/icons/forum.png" alt="*" /> ';
    echo '<a href="/user/bookmark/forum.php?id=' . $ank['id'] . '">Форум</a> (' . $cnt['forum'] . ')';
    echo '</div>';
    echo '<div class="nav1">';
    echo '<img src="/style/icons/zametki.gif" alt="*" /> ';
    echo '<a href="/user/bookmark/notes.php?id=' . $ank['id'] . '">Дневники</a> (' . $cnt['notes'] . ')';
    echo '</div>';
    echo '<div class="foot">';
    echo '<img src="/style/icons/str2.gif" alt="*" /> <a href="/info.php?id=' . $ank['id'] . '">' . $ank['nick'] . '</a> | <b>Закладки</b>';
    echo '</div>';
} else {
    $k_post=$db->query(
        'SELECT COUNT( * ) FROM `bookmarks` WHERE `id_user`=?i',
                       [$ank['id']])->el();
    
    $k_page=k_page($k_post, $set['p_str']);
    $page=page($k_page);
    $start=$set['p_str']*$page-$set['p_str'];
    $q=$db->query(
        'SELECT * FROM `bookmarks` WHERE `id_user`=?i ORDER BY `time` DESC LIMIT ?i OFFSET ?i',
                       [$ank['id'], $set['p_str'], $start]); // TODO: ???
    while ($post=$q->row()) {
        echo "<div class='nav1'>";
        if ($post['type']=='forum') {
            $them=$db->query(
                            'SELECT * FROM `forum_t` WHERE `id`=?i',
                                    [$post['id_object']])->row();
            echo "<a href='/forum/".$them['id_forum']."/".$them['id_razdel']."/".$them['id']."/'><img src='/style/icons/Forum.gif'> ".htmlspecialchars($them['name'])."</a><br/>";
            echo mb_substr(htmlspecialchars($them['text']), 0, 40)." (Добавлено ".vremja($post['time']).")";
        } elseif ($post['type']=='notes') {
            $notes=$db->query(
                            'SELECT * FROM `notes` WHERE `id`=?i',
                                    [$post['id_object']])->row();
            echo "<a href='/plugins/notes/list.php?id=".$notes['id']."'><img src='/style/icons/diary.gif'> ".htmlspecialchars($notes['name'])."</a><br/>";
            echo mb_substr(output_text($notes['msg']), 0, 40)."[...] (Добавлено ".vremja($post['time']).")";
        } elseif ($post['type']=='people') {
            echo "<img src='/style/icons/icon_stranica.gif'> ";
            echo group($post['id_object'])." ";
            echo user::nick($post['id_object'], 1, 1, 1)." <br/>";
            echo " (Добавлено ".vremja($post['time']).")";
        } elseif ($post['type']=='foto') {
            $foto=$db->query(
                            'SELECT * FROM `gallery_foto` WHERE `id`=?i',
                                    [$post['id_object']])->row();
            echo "<a href='/foto/".$foto['id_user']."/".$foto['id_gallery']."/".$foto['id']."/'><img src='/style/icons/photo.png'> ".htmlspecialchars($foto['name'])."</a><br/>";
            echo "<img style='height:60px;' src='/foto/foto0/".$foto['id'].".".$foto['ras']."'>";
            echo !empty($foto['opis']) ? mb_substr(htmlspecialchars($foto['opis']), 0, 40) .'[...]' : '' . ' (Добавлено '.vremja($post['time']).')';
        } elseif ($post['type']=='file') {
            $file_id = $db->query(
                                'SELECT  obf.id, obf.name, obf.id_dir, obf.ras, obd.dir  FROM `obmennik_files` obf
JOIN `obmennik_dir` obd ON obd.id=obf.id_dir WHERE obf.`id`=?i',
                                        [$post['id_object']])->row();
            echo '<img src="/style/icons/files.gif"> <a href="/obmen' . $file_id['dir'] . $file_id['id'] . '.' . $file_id['ras'] . '?showinfo">' . htmlspecialchars($file_id['name']) . '.' . $file_id['ras'] . '</a>';
            echo" (Добавлено ".vremja($post['time']).")";
        }
        echo "</div>";
    }
}
include_once '../../sys/inc/tfoot.php';
