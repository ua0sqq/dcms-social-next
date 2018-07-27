<?php

$gallery_q = $db->query(
                        "SELECT `id` FROM `gallery` WHERE `id_user`=?i",
                                [$ank['id']])->assoc();
if (count($gallery_q)) {
    foreach ($gallery_q as $gallery) {
        $q = $db->query(
                    "SELECT `id` FROM `gallery_foto` WHERE `id_gallery`=?i",
                            [$gallery['id']])->col();
        foreach ($q as $post_id) {
            array_map('unlink', glob(H . 'sys/gallery/*/' . $post_id . '.jpg'));
            $list_id[] = $post_id;
        }
    }
}
if (!empty($list_id)) {
    $db->query("DELETE FROM `gallery_foto` WHERE `id` IN(?li)",
                    [$list_id]);
    $db->query("DELETE FROM `gallery_komm` WHERE `id_foto` IN(?li)",
                    [$list_id]);
    $db->query("DELETE FROM `gallery_rating` WHERE `id_foto` IN(?li)",
                    [$list_id]);
    $db->query("DELETE FROM `gallery` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `gallery_komm` WHERE `id_user`=?i",
               [$ank['id']]);
}

if (isset($_GET['all']) && count($collisions) > 1) {
    for ($i = 1; $i < count($collisions); $i++) {
        $gallery_q = $db->query("SELECT `id` FROM `gallery` WHERE `id_user`=?i",
                                [$collisions[$i]]);
        while ($gallery = $gallery_q->row()) {
            $q = $db->query("SELECT `id` FROM `gallery_foto` WHERE `id_gallery`=?i",
                            [$gallery['id']])->col();
            foreach ($q as $post_id) {
                array_map('unlink', glob(H . 'sys/gallery/*/' . $post_id . '.jpg'));
                $list_id[] = $post_id;
            }
        }
    }
    if (!empty($list_id)) {
        $db->query("DELETE FROM `gallery_foto` WHERE `id` IN(?li)",
                    [$list_id]);
        $db->query("DELETE FROM `gallery_komm` WHERE `id_foto` IN(?li)",
                    [$list_id]);
        $db->query("DELETE FROM `gallery_rating` WHERE `id_foto` IN(?li)",
                    [$list_id]);
        $db->query("DELETE FROM `gallery` WHERE `id_user`=?i",
                    [$collisions[$i]]);
    }
}
