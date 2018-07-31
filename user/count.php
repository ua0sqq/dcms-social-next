<?php
$cnt = $db->query("SELECT (
SELECT COUNT( * ) FROM `user`) koll, (
SELECT COUNT( * ) FROM `user` WHERE `date_reg`>?i) k_new",
                    [START_DAY])->row();

if ($cnt['k_new']) {
    $cnt['k_new'] = '<span class="off">+' . $cnt['k_new'] . '</span>';
} else {
    $cnt['k_new'] = null;
}
echo '(' . $cnt['koll'] . ') ' . $cnt['k_new'];
