<?php
$db->query(
    "DELETE FROM `ban` WHERE `id_user`=?i OR `id_ban`=?i",
           [$ank['id'], $ank['id']]
);
if (isset($_GET['all']) && count($collisions) > 1) {
    for ($i = 1; $i < count($collisions); $i++) {
        $db->query(
    "DELETE FROM `ban` WHERE `id_user`=?i OR `id_ban`=?i",
           [$collisions[$i], $collisions[$i]]
);
    }
}
