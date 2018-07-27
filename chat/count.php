<?php
$cnt = $db->query("SELECT * FROM (
SELECT COUNT( * ) user FROM `chat_who`)q1, (
SELECT COUNT( * ) post FROM `chat_post` )q2")->row();
if (isset($user) && $cnt['user'] > 0) {
    $db->query(
        "DELETE FROM `chat_who` WHERE `id_user`=?i OR `time`<?i",
                [$user['id'], (time() - 120)]);
}
echo '(' . $cnt['post'] . '/' . $cnt['user'] . ') человек';
