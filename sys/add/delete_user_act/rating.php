<?php
$db->query(
    "DELETE FROM `user_voice2` WHERE `id_user`=?i OR `id_kont`=?i",
           [$ank['id'], $ank['id']]
);
if (isset($_GET['all']) && count($collisions) > 1) {
    for ($i = 1; $i < count($collisions); $i++) {
        $db->query(
    "DELETE FROM `user_voice2` WHERE `id_user`=?i OR `id_kont`=?i",
           [$collisions[$i], $collisions[$i]]
);
    }
}
