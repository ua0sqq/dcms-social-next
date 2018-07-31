<?php

$k_n= $db->query(
    "SELECT COUNT( * ) FROM `adm_chat` WHERE `time`>?i",
                 [START_DAY])->el();

if ($k_n==0) {
    $k_n=null;
} else {
    $k_n=' <span class="off">+'.$k_n . '</span>';
}
echo $k_n;
