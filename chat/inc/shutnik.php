<?php
$shutnik_last = $db->query("SELECT * FROM `chat_post` WHERE `room` = '$room[id]' AND `shutnik` = '1' ORDER BY id DESC LIMIT 1")->row();
if ($shutnik_last==null || $shutnik_last['time']<time()-$set['shutnik_new']) {
    $k_vopr=$db->query("SELECT COUNT(*) FROM `chat_shutnik`")->el();
    $shutnik = $db->query("SELECT * FROM `chat_shutnik` LIMIT ".rand(0, $k_vopr).",1")->row();
    $db->query("INSERT INTO `chat_post` (`shutnik`, `time`, `msg`, `room`, `privat`) values('1', '$time', '$shutnik[anek]', '$room[id]', '0')");
}
