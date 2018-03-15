<?php
/*
Модификация модуля закладок от PluginS
*/
include_once '../../sys/inc/start.php';
include_once '../../sys/inc/compress.php';
include_once '../../sys/inc/sess.php';
include_once '../../sys/inc/home.php';
include_once '../../sys/inc/settings.php';
include_once '../../sys/inc/db_connect.php';
include_once '../../sys/inc/ipua.php';
include_once '../../sys/inc/fnc.php';
include_once '../../sys/inc/user.php';

$ank['id'] = null;
if (isset($user)) {
    $ank['id'] = $user['id'];
}
if (isset($_GET['id'])) {
    $ank['id'] = intval($_GET['id']);
}
if (!$ank['id']) {
    header("Location: /index.php?" . SID);
    exit;
}

$ank = get_user($ank['id']);
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
    echo "<td class='nav1'><b>Закладки</b></td><td class='nav1'><a href='?id=".$ank['id']."&metki'>Метки</a></td>";
} elseif (isset($_GET['metki'])) {
    echo "<td class='nav1'><a href='index.php'>Закладки</a></td><td class='nav1'><b>Метки</b></td>";
} echo "</table>";
if (isset($_GET['metki'])) {
    echo '<div class="nav1">';
    $people = $db->query("SELECT COUNT(id_object) FROM `bookmarks` WHERE `id_user` = '" . $ank['id'] . "' AND `type`='people'")->el();
    echo '<img src="/style/icons/druzya.png" alt="*" /> ';
    echo '<a href="/user/bookmark/people.php?id=' . $ank['id'] . '">Люди</a> (' . $people . ')';
    echo '</div>';
    echo '<div class="nav2">';
    $files = $db->query("SELECT COUNT(id_object) FROM `bookmarks` WHERE `id_user` = '" . $ank['id'] . "' AND `type`='file'")->el();
    echo '<img src="/style/icons/files.gif" alt="*" /> ';
    echo '<a href="/user/bookmark/files.php?id=' . $ank['id'] . '">Файлы</a> (' . $files . ')';
    echo '</div>';
    echo '<div class="nav1">';
    $foto = $db->query("SELECT COUNT(id_object) FROM `bookmarks` WHERE `id_user` = '" . $ank['id'] . "' AND `type`='foto'")->el();
    echo '<img src="/style/icons/foto.png" alt="*" /> ';
    echo '<a href="/user/bookmark/foto.php?id=' . $ank['id'] . '">Фотографии</a> (' . $foto . ')';
    echo '</div>';
    echo '<div class="nav2">';
    $forum = $db->query("SELECT COUNT(id_object) FROM `bookmarks` WHERE `id_user` = '" . $ank['id'] . "' AND `type`='forum'")->el();
    echo '<img src="/style/icons/forum.png" alt="*" /> ';
    echo '<a href="/user/bookmark/forum.php?id=' . $ank['id'] . '">Форум</a> (' . $forum . ')';
    echo '</div>';
    echo '<div class="nav1">';
    $notes = $db->query("SELECT COUNT(id_object) FROM `bookmarks` WHERE `id_user` = '" . $ank['id'] . "' AND `type`='notes'")->el();
    echo '<img src="/style/icons/zametki.gif" alt="*" /> ';
    echo '<a href="/user/bookmark/notes.php?id=' . $ank['id'] . '">Дневники</a> (' . $notes . ')';
    echo '</div>';
    echo '<div class="foot">';
    echo '<img src="/style/icons/str2.gif" alt="*" /> <a href="/info.php?id=' . $ank['id'] . '">' . $ank['nick'] . '</a> | <b>Закладки</b>';
    echo '</div>';
} else {
    $k_post=$db->query("SELECT COUNT(id_object) FROM `bookmarks` WHERE `id_user` = '$ank[id]'")->el();
    $k_page=k_page($k_post, $set['p_str']);
    $page=page($k_page);
    $start=$set['p_str']*$page-$set['p_str'];
    $q=$db->query("SELECT * FROM `bookmarks` WHERE `id_user`='$ank[id]' ORDER BY `time` DESC LIMIT $start,$set[p_str]");
    while ($post=$q->row()) {
        echo "<div class='nav1'>";
        if ($post['type']=='forum') {
            $them=$db->query("SELECT * FROM `forum_t` WHERE `id`='$post[id_object]' LIMIT 1")->row();
            echo "<a href='/forum/".$them['id_forum']."/".$them['id_razdel']."/".$them['id']."/'><img src='/style/icons/Forum.gif'> ".htmlspecialchars($them['name'])."</a><br/>";
            echo substr(htmlspecialchars($them['text']), 0, 40)." (Добавлено ".vremja($post['time']).")";
        } elseif ($post['type']=='notes') {
            $notes=$db->query("SELECT * FROM `notes` WHERE `id`='$post[id_object]' LIMIT 1")->row();
            echo "<a href='/plugins/notes/list.php?id=".$notes['id']."'><img src='/style/icons/diary.gif'> ".htmlspecialchars($notes['name'])."</a><br/>";
            echo substr(htmlspecialchars($notes['msg']), 0, 40)."[...] (Добавлено ".vremja($post['time']).")";
        } elseif ($post['type']=='people') {
            $people=get_user($post['id_object']);
            echo "<img src='/style/icons/icon_readers.gif'> ";
            echo group($people['id'])." ";
            echo user::nick($people['id'], 1, 1, 1)." <br/>";
            echo " (Добавлено ".vremja($post['time']).")";
        } elseif ($post['type']=='foto') {
            $foto=$db->query("SELECT * FROM `gallery_foto` WHERE `id`='$post[id_object]' LIMIT 1")->row();
            echo "<a href='/foto/".$foto['id_user']."/".$foto['id_gallery']."/".$foto['id']."/'><img src='/style/icons/PhotoIcon.gif'> ".htmlspecialchars($foto['name'])."</a><br/>";
            echo "<img style='height:60px;' src='/foto/foto0/".$foto['id'].".".$foto['ras']."'>";
            echo substr(htmlspecialchars($foto['opis']), 0, 40)."[...] (Добавлено ".vremja($post['time']).")";
        } elseif ($post['type']=='file') {
            $file_id = $db->query("SELECT id_dir,id,name,ras  FROM `obmennik_files` WHERE `id` = '" . $post['id_object'] . "'  LIMIT 1")->row();
            $dir = $db->query("SELECT `dir` FROM `obmennik_dir` WHERE `id` = '$file_id[id_dir]' LIMIT 1")->row();
            echo '<img src="/style/icons/film.gif"> <a href="/obmen' . $dir['dir'] . $file_id['id'] . '.' . $file_id['ras'] . '?showinfo">' . htmlspecialchars($file_id['name']) . '.' . $file_id['ras'] . '</a>';
            echo" (Добавлено ".vremja($post['time']).")";
        }
        echo "</div>";
    }
}
include_once '../../sys/inc/tfoot.php';
