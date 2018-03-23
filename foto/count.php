<?php

$cnt = $db->query('SELECT * FROM (
SELECT COUNT(*) all_foto FROM `gallery_foto`)q, (
SELECT COUNT(*) new_foto FROM `gallery_foto` WHERE `time`>?i)q2', [$ftime])->row();

if ($cnt['new_foto'] == 0) {
    $cnt['new_foto'] = null;
} else {
    $cnt['new_foto'] = '+' . $cnt['new_foto'];
}
echo '(' . $cnt['all_foto'] . ') <span style="color:red;">' . $cnt['new_foto'] . '</span>';
