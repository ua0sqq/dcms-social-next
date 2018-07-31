<?php

$cnt = $db->query('SELECT * FROM (
SELECT COUNT(*)  all_cnt FROM `obmennik_files` WHERE `id_dir` NOT IN(SELECT id FROM obmennik_dir WHERE `my`=1))q, (
SELECT COUNT(*) new_cnt FROM `obmennik_files` WHERE `id_dir` NOT IN(SELECT id FROM obmennik_dir WHERE `my`=1) AND `time` > '.START_DAY.')q2')->row();

if ($cnt['new_cnt']==0) {
    $cnt['new_cnt']=null;
} else {
    $cnt['new_cnt']='+'.$cnt['new_cnt'];
}
echo '(' . $cnt['all_cnt'] . ') <span class="off">' . $cnt['new_cnt'] . '</span>';
