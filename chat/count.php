<?php

if (isset($user)) {
    $db->query("DELETE FROM `chat_who` WHERE `id_user` = '$user[id]'");
}

$db->query("DELETE FROM `chat_who` WHERE `time` < '".($time-120)."'");
echo '('.$db->query("SELECT COUNT(*) FROM `chat_who`")->el().' человек)';
