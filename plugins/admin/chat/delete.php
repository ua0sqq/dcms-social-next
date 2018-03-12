<?php
include_once '../../../sys/inc/start.php';
include_once '../../../sys/inc/compress.php';
include_once '../../../sys/inc/sess.php';
include_once '../../../sys/inc/home.php';
include_once '../../../sys/inc/settings.php';
include_once '../../../sys/inc/db_connect.php';
include_once '../../../sys/inc/ipua.php';
include_once '../../../sys/inc/fnc.php';
include_once '../../../sys/inc/user.php';

if (isset($_GET['id']) && $db->query("SELECT COUNT(*) FROM `adm_chat` WHERE `id` = '".intval($_GET['id'])."'")->el()) {
    $post=$db->query("SELECT * FROM `adm_chat` WHERE `id` = '".intval($_GET['id'])."' LIMIT 1")->row();
    if ($post['id_user']==0) {
        $ank['id']=0;
        $ank['pol']='guest';
        $ank['level']=0;
        $ank['nick']='Гость';
    } else {
        $ank=get_user($post['id_user']);
    }
    if (user_access('guest_delete')) {
        admin_log('Гостевая', 'Удаление сообщения', "Удаление сообщения от $ank[nick]");
        $db->query("DELETE FROM `adm_chat` WHERE `id` = '$post[id]'");
    }
    $_SESSION['message'] = 'Сообщение успешно удалено';
    header("Location: index.php?");
    exit;
}
