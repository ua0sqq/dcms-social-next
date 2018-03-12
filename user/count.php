<?php

$koll = $db->query("SELECT COUNT(*) FROM `user`")->el();
$k_new = $db->query("SELECT COUNT(*) FROM `user` WHERE `date_reg` > '$ftime' ")->el();

if ($k_new > 0) {
    $k_new = '<font color="red">+' . $k_new . '</font>';
} else {
    $k_new = null;
}
echo '(' . $koll . ') ' . $k_new;
