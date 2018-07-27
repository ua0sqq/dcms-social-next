<?php
$gallery_q = $db->query("SELECT `id` FROM `gallery` WHERE `id_user`=?i",
                        [$ank['id']])->col();
$foto = 0;
foreach ($gallery_q as $gallery_id) {
    $foto += $db->query(
        "SELECT COUNT(*) FROM `gallery_foto` WHERE `id_gallery`=?i",
                        [$gallery_id])->el();
}
if (count($collisions) > 1 && isset($_GET['all'])) {
    $foto_coll = 0;
    for ($i = 1; $i < count($collisions); $i++) {
        $gallery_q = $db->query(
            "SELECT `id` FROM `gallery` WHERE `id_user`=?i",
                                [$collisions[$i]])->col();
        foreach ($gallery_q as $gallery_id) {
            $foto_coll += $db->query(
                "SELECT COUNT(*) FROM `gallery_foto` WHERE `id_gallery`=?i",
                                     [$gallery_id])->el();
        }
    }
    if ($obmennik_coll != 0) {
        $foto = $foto . ' +' . $foto_coll . '*';
    }
}
echo '<span class="ank_n">Фотографии:</span> <span class="ank_d">' . $foto . '</span><br />' . "\n";
