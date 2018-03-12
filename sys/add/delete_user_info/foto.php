<?php
$gallery_q=$db->query("SELECT * FROM `gallery` WHERE `id_user` = '$ank[id]'");
$foto=0;
while ($gallery = $gallery_q->row())
{
$foto+=$db->query("SELECT COUNT(*) FROM `gallery_foto` WHERE `id_gallery` = '$gallery[id]'");
}
if (count($collisions)>1 && isset($_GET['all']))
{
$foto_coll=0;
for ($i=1;$i<count($collisions);$i++)
{
$gallery_q=$db->query("SELECT * FROM `gallery` WHERE `id_user` = '$collisions[$i]'");
while ($gallery = $gallery_q->row())
{
$foto_coll+=$db->query("SELECT COUNT(*) FROM `gallery_foto` WHERE `id_gallery` = '$gallery[id]'");
}
}
if ($obmennik_coll!=0)
$foto="$foto +$foto_coll*";
}
echo "<span class=\"ank_n\">Фотографии:</span> <span class=\"ank_d\">$foto</span><br />\n";
?>