<?php
$cnt = $db->query(
                'SELECT * FROM (
SELECT COUNT( * ) k_p FROM `liders` WHERE `time` > ?i)q, (
SELECT COUNT( * ) k_n FROM `liders` WHERE `time` > ?i AND `time_p` > ?i)q2',
                        [$time, $time, START_DAY])->row();

if ($cnt['k_n'] == 0) {
    $cnt['k_n'] = null;
} else {
    $cnt['k_n'] = '+' . $cnt['k_n'];
}
echo '(' . $cnt['k_p'] . ') <span class="off">' . $cnt['k_n'] . '</span>';
