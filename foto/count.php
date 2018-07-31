<?php

$cnt = $db->query('SELECT * FROM (
SELECT COUNT( * ) all_foto FROM `gallery_foto`)q, (
SELECT COUNT( * ) new_foto FROM `gallery_foto` WHERE `time`>?i)q2', [START_DAY])->row();

if ($cnt['new_foto'] == 0) {
    $cnt['new_foto'] = null;
} else {
    $cnt['new_foto'] = ' <span class="off">+' . $cnt['new_foto'] . '</span>';
}
echo '(' . $cnt['all_foto'] . ')' . $cnt['new_foto'];
