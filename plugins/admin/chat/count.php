<?php

$k_p=$db->query("SELECT COUNT(*) FROM `adm_chat`")->el();
$k_n= $db->query("SELECT COUNT(*) FROM `adm_chat` WHERE `time` > '".(time()-86400)."'")->el();
if ($k_n==0) {
    $k_n=null;
} else {
    $k_n='+'.$k_n;
}
echo '(' . $k_p . ') <span class="off">' . $k_n . '</span>';
