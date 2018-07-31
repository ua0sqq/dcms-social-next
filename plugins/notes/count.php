<?php
$cnt = $db->query('SELECT (
SELECT COUNT(*) FROM `notes`) all_notes, (
SELECT COUNT(*) FROM `notes` WHERE `time`>?i) new_notes', [START_DAY])->row();
if ($cnt['new_notes']==0) {
    $cnt['new_notes']=null;
} else {
    $cnt['new_notes']='+'.$cnt['new_notes'];
}
echo '(' . $cnt['all_notes'] . ') <span class="off">' . $cnt['new_notes'] . '</span>'."\n";
