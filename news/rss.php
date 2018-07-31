<?php
include_once '../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

if (!$db->query("SELECT COUNT(*) FROM `news`")->el()) {
    exit;
}
header("Content-type: application/rss+xml");
echo "<rss version=\"2.0\">\n";
echo "<channel>\n";
echo "<title>Новости ".$_SERVER['SERVER_NAME']."</title>\n";
echo "<link>http://".$_SERVER['SERVER_NAME']."</link>\n";
echo "<description>Новости ".$_SERVER['SERVER_NAME']."</description>\n";
echo "<language>ru-RU</language>\n";
//echo "<webMaster>$set[adm_mail]</webMaster>\n";
echo "<lastBuildDate>".date("r", $db->query("SELECT MAX(time) FROM `news`")->el())."</lastBuildDate>\n";
$q=$db->query("SELECT * FROM `news` ORDER BY `id` DESC LIMIT $set[p_str]");
while ($post = $q->row()) {
    echo "<item>\n";
    echo "<title>$post[title]</title>\n";
    if ($post['link']!=null) {
        if (!preg_match('#^https?://#', $post['link'])) {
            echo "<link>".htmlentities("http://$_SERVER[SERVER_NAME]$post[link]", ENT_QUOTES, 'UTF-8')."</link>\n";
        } else {
            echo "<link>".htmlentities("$post[link]", ENT_QUOTES, 'UTF-8')."</link>\n";
        }
    }
    echo "<description><![CDATA[";
    echo output_text($post['msg'], true, true, false)."\n";
    echo "]]></description>\n";
    echo "<pubDate>".date("r", $post['time'])."</pubDate>\n";
    echo "</item>\n";
}
echo "</channel>\n";
echo "</rss>\n";
