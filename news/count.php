<?php
$cnt = $db->query('SELECT * FROM (
SELECT COUNT(*) all_news FROM `news`)q, (
SELECT COUNT(*) new_news FROM `news` WHERE `time`>?i)q2', [$ftime])->row();
if (!$cnt['new_news']) {
    $cnt['new_news'] = null;
} else {
    $cnt['new_news'] = '+' . $cnt['new_news'];
}
echo '(' . $cnt['all_news'] . ') <span class="off">' . $cnt['new_news'] . '</span>';
