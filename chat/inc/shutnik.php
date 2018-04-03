<?php
$shutnik_last = $db->query(
    "SELECT * FROM `chat_post` WHERE `room`=?i AND `shutnik`=? ORDER BY id DESC LIMIT ?i",
                           [$room['id'], '1', 1])->row();
if ($shutnik_last == null || $shutnik_last['time'] < time()-$set['shutnik_new']) {
    $k_vopr=$db->query("SELECT COUNT(*) FROM `chat_shutnik`")->el();
    $shutnik = $db->query(
        "SELECT * FROM `chat_shutnik` LIMIT ?i OFFSET ?i",
                          [1, rand(0, $k_vopr)])->row();
    $db->query(
        "INSERT INTO `chat_post` (`shutnik`, `time`, `msg`, `room`, `privat`) values(?, ?i, ?, ?i, ?i)",
               ['1', $time, $shutnik['anek'], $room['id'], 0]);
}
