<?php

$k_n= $db->query("SELECT COUNT(*) FROM `adm_chat` WHERE `time` > '$ftime'")->el();

if ($k_n==0) {
    $k_n=null;
} else {
    $k_n='+'.$k_n;
}
echo " <font color='red'>$k_n</font> ";
