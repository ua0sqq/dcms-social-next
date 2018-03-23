<?php
if (!$set['web'] && (!isset($user) || $user['news_read'] == 0)) {
$q = $db->query(
    "SELECT n.*, (
				SELECT COUNT(*) FROM `news_komm` WHERE `id_news` = n.id) cnt
				FROM `news` n WHERE n.`main_time` > ?i ORDER BY n.`id` DESC LIMIT ?i",
                [time(), 1]
)->assoc();
if (!empty($q)) {
    foreach ($q as $news);
    echo '<div class="mess">';
    echo '<img src="/style/icons/blogi.png" alt="*" /> <a href="/news/news.php?id=' . $news['id'] . '">' . text($news['title']) . '</a><br/> ';
    echo output_text($news['msg']) . '<br />';
    
    if ($news['link']!=null) {
        echo '<a href="' . htmlentities($news['link'], ENT_QUOTES, 'UTF-8') . '">Подробности</a><br />';
    }
    echo 'Опубликовал: '.group($news['id_user']).' ';
    echo user::nick($news['id_user'], 1, 1, 1).' '.vremja($news['time']).' ';
    echo ' <img src="/style/icons/komm.png" alt="*" /> (' . $news['cnt'] . ')<br />';
    
    if (isset($user)) {
        echo '<div style="text-align:right;"><a href="?news_read">Скрыть</a></div>';
    }
    echo '</div>';
}
}
