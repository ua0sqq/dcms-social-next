<?php
// Онлайн пользователи
$k_post=$db->query("SELECT COUNT( * ) FROM `user` WHERE `date_last`>?i",
				   [TIME_600])->el();

if ($k_post) {
    echo "<a href='/online.php'><div class='main'>";
    echo "Сейчас на сайте ($k_post) чел.</div></a>";
	$q = $db->query("SELECT `id`, `nick` FROM `user` WHERE `date_last`>?i ORDER BY `rating` DESC LIMIT ?i",
				[TIME_600, 10]);
    echo "<div class='nav3'>";
    echo '<table>';
    echo '<tr>';
    while ($ank = $q->row()) {
        //$ank=get_user($ank['id']);
        echo '<td style="width:60px; height:70px; vertical-align:top; border:1px solid black; text-align:center; display:inline-table; margin:2px;">';
        echo "<a href='/info.php?id=$ank[id]'>".avatar($ank['id']).'<br />';
        echo "<b><small>$ank[nick]</small></b></a>";
        echo '</td>';
    }
    echo '</tr>';
    echo '</table>';
    echo '</div>';
}
/* Новости */
$k_post=$db->query("SELECT COUNT( * ) FROM `news`")->el();

echo "<a href='/news'><div class='my'>";
echo "<img src='/style/icons/news.png' alt='*' /> Новости ";
include H.'news/count.php';
echo "</div></a>";
if ($k_post) {
	$q=$db->query("SELECT n.*, (
SELECT COUNT( * ) FROM `news_komm` WHERE `id_news`=`n`.`id`) cnt_komm
FROM `news` n ORDER BY n.`id` DESC LIMIT 2");
    echo "<div class='mess'>";
    echo '<table>';
    echo '<tr>';
    while ($post = $q->row()) {
        echo '<td style="width:350px; height:70px; vertical-align:top; display:inline-table; margin:2px;">';
        echo "<a href='/news/news.php?id=$post[id]'>".htmlspecialchars($post['title'])."</a>\n";
        echo "(".vremja($post['time']).")<br />\n";
        echo rez_text2(output_text($post['msg']));
        if ($post['link']!=null) {
            echo "<br /><a href='".htmlentities($post['link'], ENT_QUOTES, 'UTF-8')."'>Подробности &rarr;</a><br />\n";
        }
        echo "<img src='/style/icons/bbl4.png' alt='*' /> (".$post['cnt_komm'].")<br />\n";
        echo '</td>';
    }
    echo '</tr>';
    echo '</table>';
    echo "   </div>\n";
}
/* Форум */
echo "<a href='/forum'><div class='my'>";
echo "<img src='/style/icons/forum.png' alt='*' /> Форум ";
include H.'forum/count.php';
echo "</div></a>";
$k_post=$db->query("SELECT COUNT( * ) FROM `forum_t`")->el();
if ($k_post) {
    echo "<div class='mess'>";
    $q=$db->query("SELECT t.*, (
SELECT COUNT( * ) FROM `forum_p` WHERE `id_forum`=t.id_forum AND `id_razdel`=t.id_razdel AND `id_them`=t.id) cnt_post
FROM `forum_t` t ORDER BY t.`time_create` DESC LIMIT 5");
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
        echo '<a href="/forum/' . $them['id_forum'] . '/' . $them['id_razdel'] . '/' . $them['id'] . '/"><b>' . htmlspecialchars($them['name']) . '</b></a> 
	<a href="/forum/' .$them['id_forum'] . '/' . $them['id_razdel']  . '/' . $them['id'] . '/?page=' . $pageEnd . '">(' . $them['cnt_post'] . ')</a><br/>';
        echo rez_text($them['text'], 112).'<br/>';
        // Автор темы
        echo group($them['id_user']).' ';
        echo user::nick($them['id_user'], 1, 1, 1).' (' . vremja($them['time_create']) . ') ';
        // Последний пост
        $post = $db->query("SELECT `id`,`time`,`id_user` FROM `forum_p` WHERE `id_them`=?i AND `id_forum`=?i AND `id_razdel`=?i  ORDER BY `time` DESC LIMIT ?i",
						   [$them['id'], $them['id_forum'], $them['id_razdel'], 1])->row();
        if ($post['id']) {
            // Автор последнего поста
            echo '/ '.user::nick($post['id_user'], 1, 1, 1).' (' . vremja($post['time']) . ')<br />';
        }
    
        echo '</div>';
    }
    echo "</div>";
}
//  Чат комнаты TODO: ??? на хера оно надо?
echo "<a href='/chat'><div class='my'>";
echo "<img src='/style/icons/chat.png' alt='*' /> Чат ";
include H.'chat/count.php';
echo "</div></a>";
$q=$db->query("SELECT ch.`id`, ch.`name`, ch.`opis`, (
SELECT COUNT( * ) FROM `chat_who` WHERE `room`=`ch`.`id`) cnt
FROM `chat_rooms` ch ORDER BY ch.`pos` ASC")->assoc();
if (!empty($q)) {
    echo "<div class='mess'>";
    foreach ($q as $room) {
        if ($num==0) {
            echo "  <div class='nav1'>\n";
            $num=1;
        } elseif ($num==1) {
            echo "  <div class='nav2'>\n";
            $num=0;
        }
        echo "<img src='/style/themes/$set[set_them]/chat/14/room.png' alt='*' /> ";
        echo "<a href='/chat/room/$room[id]/".rand(1000, 9999)."/'>$room[name] (".$room['cnt'].")</a><br />\n";
        if ($room['opis'] != null) {
            echo output_text($room['opis'])."<br />\n";
        }
        echo "</div>";
    }
    echo "</div>";
}
