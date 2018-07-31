<?php

$cnt = $db->query('SELECT * FROM (
SELECT COUNT( * ) all_cnt FROM `guest`)q, (
SELECT COUNT( * ) new_cnt FROM `guest` WHERE `time`>?i)q2', [START_DAY])->row();

if ($cnt['new_cnt'] == 0) {
    $cnt['new_cnt'] = null;
} else {
    $cnt['new_cnt'] = ' <span class="off">+' . $cnt['new_cnt'] . '</span>';
}

echo '(' . $cnt['all_cnt'] . ')' . $cnt['new_cnt'];
