<?php
// Начисление рейтинга и баллов за активность
$db->query(
    "UPDATE `user` SET `balls`=`balls`+1, `rating_tmp`=`rating_tmp`+1 WHERE `id`=?i",
           [$user['id']]
);
