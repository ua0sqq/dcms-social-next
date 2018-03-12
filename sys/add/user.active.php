<?php
// Начисление рейтинга и баллов за активность
$db->query("UPDATE `user` SET `balls` = '" . ($user['balls'] + 1) . "', `rating_tmp` = '" . ($user['rating_tmp'] + 1) . "' WHERE `id` = '$user[id]' LIMIT 1");
?>