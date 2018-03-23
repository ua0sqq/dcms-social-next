<?php
function status($ID)
{
    $avatar = go\DB\query(
        "SELECT id, id_gallery, ras FROM `gallery_foto` WHERE `id_user`=?i AND `avatar`=? LIMIT ?i",
                          [$ID, '1', 1]
    )->row();
    if (is_file(H . 'sys/gallery/50/' . $avatar['id'] . '.' . $avatar['ras'])) {
        echo '<img class="avatar" src="/foto/foto50/' . $avatar['id'] . '.' . $avatar['ras'] . '" style="width:50px;" alt="" />';
    } else {
        echo '<img class="avatar" src="/style/user/avatar.gif" style="width:50px;" alt="" />';
    }
}
