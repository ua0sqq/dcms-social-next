<?php
$k_p = $db->query("SELECT COUNT(*) FROM `liders` WHERE `time` > '$time'")->el();
$k_n = $db->query("SELECT COUNT(*) FROM `liders` WHERE `time` > '$time' AND `time_p` > '$ftime'")->el();
if ($k_n == 0)$k_n = NULL;
else $k_n = '+' . $k_n;
echo '(' . $k_p . ') <font color="red">' . $k_n . '</font>';
?>