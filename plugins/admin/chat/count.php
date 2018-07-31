<?php
$cnt = $db->query("SELECT (
SELECT COUNT( * ) FROM `adm_chat`) k_p, (
SELECT COUNT( * ) FROM `adm_chat` WHERE `time`>?i) k_n",
            [START_DAY])->row();

if (!$cnt['k_n']) {
    $cnt['k_n'] = null;
} else {
    $cnt['k_n'] = ' <span class="off">+'.$cnt['k_n'] . '</span>';
}
echo '(' . $cnt['k_p'] . ')' . $cnt['k_n'];
