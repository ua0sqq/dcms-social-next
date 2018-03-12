<?php
$gallery_q=$db->query("SELECT * FROM `gallery` WHERE `id_user` = '$ank[id]'");
while ($gallery = $gallery_q->row())
{
$q=$db->query("SELECT * FROM `gallery_foto` WHERE `id_gallery` = '$gallery[id]'");
while ($post = $q->row())
{
@unlink(H."sys/gallery/48/$post[id].jpg");
@unlink(H."sys/gallery/128/$post[id].jpg");
@unlink(H."sys/gallery/640/$post[id].jpg");
@unlink(H."sys/gallery/foto/$post[id].jpg");
$db->query("DELETE FROM `gallery_foto` WHERE `id` = '$post[id]' LIMIT 1");
$db->query("DELETE FROM `gallery_komm` WHERE `id_foto` = '$post[id]'");
$db->query("DELETE FROM `gallery_rating` WHERE `id_foto` = '$post[id]'");
}
}
$db->query("DELETE FROM `gallery` WHERE `id_user` = '$ank[id]'");
$db->query("DELETE FROM `gallery_komm` WHERE `id_user` = '$ank[id]'");
if (isset($_GET['all']) && count($collisions)>1)
{
for ($i=1;$i<count($collisions);$i++)
{
$gallery_q=$db->query("SELECT * FROM `gallery` WHERE `id_user` = '$collisions[$i]'");
while ($gallery = $gallery_q->row())
{
$q=$db->query("SELECT * FROM `gallery_foto` WHERE `id_gallery` = '$gallery[id]'");
while ($post = $q->row())
{
@unlink(H."sys/gallery/48/$post[id].jpg");
@unlink(H."sys/gallery/128/$post[id].jpg");
@unlink(H."sys/gallery/640/$post[id].jpg");
@unlink(H."sys/gallery/foto/$post[id].jpg");
$db->query("DELETE FROM `gallery_foto` WHERE `id` = '$post[id]' LIMIT 1");
$db->query("DELETE FROM `gallery_komm` WHERE `id_foto` = '$post[$i]'");
$db->query("DELETE FROM `gallery_rating` WHERE `id_foto` = '$post[$i]'");
}
}
$db->query("DELETE FROM `gallery` WHERE `id_user` = '$collisions[$i]'");
}
}
?>