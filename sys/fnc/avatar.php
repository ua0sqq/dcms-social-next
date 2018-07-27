<?php
/**
 * Аватар, модифицировали функцию с целью облегчения кода
 *
 * @param  int  $ID   user ID
 * @param  boolean $link
 * @param  int  $dir  директория
 * @param  string  $w    длинна картинки
 * @return string        аватарка
 */
function avatar($ID, $link = false, $dir = 50, $w = 50)
{
    $avatar = go\DB\query(
        'SELECT id, id_gallery, ras FROM `gallery_foto` WHERE `id_user`=?i AND `avatar`="1"',
                          [$ID])->row();
    if (is_file(H . 'sys/gallery/' . $dir . '/' . $avatar['id'] . '.' . $avatar['ras'])) {
        return ($link == true ? '<a href="/foto/' . $ID . '/' . $avatar['id_gallery'] . '/' . $avatar['id'] . '/">' : false) .
		'<img class="avatar" src="/foto/foto' . $dir . '/' . $avatar['id'] . '.' . $avatar['ras'] . '" style="width:' . $w . 'px;" alt="avatar" />' . ($link == true ? '</a>' : false);
    } else {
        return '<img class="avatar" src="/style/user/avatar.gif" style="width:' . $w . 'px;" alt="avatar" />';
    }
}
