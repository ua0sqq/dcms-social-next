<?php
if (user_access('obmen_komm_del') && isset($_GET['del_post']) &&
    $db->query(
        "SELECT COUNT(*) FROM `obmennik_komm` WHERE `id`=?i AND `id_file`=?i",
               [$_GET['del_post'], $file_id['id']])) {
    
    $db->query("DELETE FROM `obmennik_komm` WHERE `id`=?i", [$_GET['del_post']]);
    msg('Комментарий успешно удален');
}
