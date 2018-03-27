<?php

$cnt = $db->query('SELECT * FROM (
SELECT COUNT(*) all_cnt FROM `guest`)q, (
SELECT COUNT(*) new_cnt FROM `guest` WHERE `time`>?i)q2', [$ftime])->row();

if ($cnt['new_cnt'] == 0) {
    $cnt['new_cnt'] = null;
} else {
    $cnt['new_cnt'] = '+' . $cnt['new_cnt'];
}

echo '(' . $cnt['all_cnt'] . ') <span style="color:red;">' . $cnt['new_cnt'] . '</span>';
