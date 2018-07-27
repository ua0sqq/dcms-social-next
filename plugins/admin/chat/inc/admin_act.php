<?php
if (user_access('guest_clear')) {
    if (isset($_POST['write']) && isset($_POST['write2'])) {
        $timeclear1 = 0;
        if ($_POST['write2']=='sut') {
            $timeclear1 = time()-intval($_POST['write'])*60*60*24;
        }
        if ($_POST['write2']=='mes') {
            $timeclear1 = time()-intval($_POST['write'])*60*60*24*30;
        }
        $q = $db->query(
            "SELECT `id` FROM `adm_chat` WHERE `time` < ?i",
                        [$timeclear1])->col();
        if (!empty($q)) {
            foreach ($q as $post) {
                $list[] = $post;
            }
            $del_th = $db->query(
            "DELETE FROM `adm_chat` WHERE `id` IN(?li)",
                   [$list])->ar();
            admin_log('Гостевая', 'Очистка', 'Удалено ' . $del_th . ' сообщений');
            $db->query("OPTIMIZE TABLE `adm_chat`");
            $_SESSION['message'] = 'Удалено ' . $del_th . ' постов';
        } else {
            $_SESSION['err'] = 'Empty Query!';
        }
        header('Location: index.php?');
        exit;
    }
}
