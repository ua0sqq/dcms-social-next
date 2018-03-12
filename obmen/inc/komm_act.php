<?php
if (user_access('obmen_komm_del') && isset($_GET['del_post']) && $db->query("SELECT COUNT(*) FROM `obmennik_komm` WHERE `id` = '".intval($_GET['del_post'])."' AND `id_file` = '$file_id[id]'"))
{
$db->query("DELETE FROM `obmennik_komm` WHERE `id` = '".intval($_GET['del_post'])."' LIMIT 1");
msg ('Комментарий успешно удален');
}
?>