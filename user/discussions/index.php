<?php
/*
=======================================
Обсуждения для Dcms-Social
Автор: Искатель
---------------------------------------
Этот скрипт распостроняется по лицензии
движка Dcms-Social.
При использовании указывать ссылку на
оф. сайт http://dcms-social.ru
---------------------------------------
Контакты
ICQ: 587863132
http://dcms-social.ru
=======================================
*/
include_once '../../sys/inc/start.php';
include_once '../../sys/inc/compress.php';
include_once '../../sys/inc/sess.php';
include_once '../../sys/inc/home.php';
include_once '../../sys/inc/settings.php';
include_once '../../sys/inc/db_connect.php';
include_once '../../sys/inc/ipua.php';
include_once '../../sys/inc/fnc.php';
include_once '../../sys/inc/adm_check.php';
include_once '../../sys/inc/user.php';
only_reg();
$my = null;
$frend = null;
$all = null;
if (isset($_GET['read']) && $_GET['read'] == 'all') {
    if (isset($user)) {
        $db->query("UPDATE `discussions` SET `count` = '0' WHERE `id_user` = '$user[id]'");
        $_SESSION['message'] = __('Список непрочитанных очищен');
        header("Location: ?");
        exit;
    }
}
if (isset($_GET['delete']) && $_GET['delete']=='all') {
    if (isset($user)) {
        $db->query("DELETE FROM `discussions` WHERE `id_user` = '$user[id]'");
        $_SESSION['message'] = __('Список обсуждений очищен');
        header("Location: ?");
        exit;
    }
}
//------------------------like к статусу-------------------------//
if (isset($_GET['likestatus'])) {
    $status = $db->query("SELECT * FROM `status` WHERE `id` = '".intval($_GET['likestatus'])."' LIMIT 1")->row();
    $ank = get_user(intval($_GET['likestatus']));
    
    if (isset($user) && $user['id'] != $ank['id'] &&
    !$db->query("SELECT COUNT(*) FROM `status_like` WHERE `id_status` = '$status[id]' AND `id_user` = '$user[id]' LIMIT 1")->el()) {
        $db->query("INSERT INTO `status_like` (`id_user`, `time`, `id_status`) values('$user[id]', '$time', '$status[id]')");
        $q = $db->query("SELECT * FROM `frends` WHERE `user` = '".$user['id']."' AND `i` = '1'");
        
        while ($f = $q->row()) {
            $a = get_user($f['frend']);
            $db->query("INSERT INTO `tape` (`id_user`,`ot_kogo`,  `avtor`, `type`, `time`, `id_file`) 
			values('$a[id]', '$user[id]', '$status[id_user]', 'status_like', '$time', '$status[id]')");
        }
        header("Location: ?page=".intval($_GET['page']));
        exit;
    }
}
if ($db->query("SELECT COUNT(*) FROM `discussions`  WHERE `id_user` = '$user[id]' AND `count` > '0' AND `avtor` = '$user[id]'")->el()) {
    $count_my = " <img src='/style/icons/tochka.png' alt='*'/>";
} else {
    $count_my = null;
}
if ($db->query("SELECT COUNT(*) FROM `discussions`  WHERE `id_user` = '$user[id]' AND `count` > '0' AND `avtor` <> '$user[id]'")->el()) {
    $count_f = " <img src='/style/icons/tochka.png' alt='*'/>";
} else {
    $count_f = null;
}
$set['title'] = __('Обсуждения');
include_once '../../sys/inc/thead.php';
title();
err();
aut();
if (isset($_GET['order']) && $_GET['order']=='my') {
    $order = "AND `avtor` = '$user[id]'";
    $sort = "order=my&amp;";
    $my = 'activ';
} elseif (isset($_GET['order']) && $_GET['order']=='frends') {
    $order = "AND `avtor` != '$user[id]'";
    $sort = "order=frends&amp;";
    $frend = 'activ';
} else {
    $order = null;
    $sort = null;
    $all = 'activ';
}
// Уведомления
$k_notif = $db->query("SELECT COUNT(`read`) FROM `notification` WHERE `id_user` = '$user[id]' AND `read` = '0'")->el();
if ($k_notif > 0) {
    $k_notif = '<font color=red>(' . $k_notif . ')</font>';
} else {
    $k_notif = null;
}
// Обсуждения
$discuss = $db->query("SELECT COUNT(`count`) FROM `discussions` WHERE `id_user` = '$user[id]' AND `count` > '0' ")->el();
if ($discuss > 0) {
    $discuss = '<font color=red>(' . $discuss . ')</font>';
} else {
    $discuss = null;
}
// Лента
$lenta = $db->query("SELECT COUNT(`read`) FROM `tape` WHERE `id_user` = '$user[id]' AND `read` = '0' ")->el();
if ($lenta > 0) {
    $lenta = '<font color=red>(' . $lenta . ')</font>';
} else {
    $lenta = null;
}
?>
<div id="comments" class="menus">
<div class="webmenu">
<a href="/user/tape/"><?= __('Лента')?> <?= $lenta?></a>
</div>
<div class="webmenu">
<a href="/user/discussions/" class="activ"><?= __('Обсуждения')?> <?= $discuss?></a>
</div>
<div class="webmenu">
<a href="/user/notification/"><?= __('Уведомления')?> <?= $k_notif?></a>
</div>
</div>
<div class="foot">
<?= __('Сортировать')?>: 
<a href="?"> <?= __('Все')?> </a>  | 
<a href="?order=my"> <?= __('Мои')?><?= $count_my?> </a>  | 
<a href="?order=frends"> <?= __('Друзья')?><?= $count_f?> </a> 
</div>
<?php
$k_post = $db->query("SELECT COUNT(*) FROM `discussions`  WHERE `id_user` = '$user[id]' $order")->el();
$k_page = k_page($k_post, $set['p_str']);
$page = page($k_page);
$start = $set['p_str'] * $page - $set['p_str'];
$q = $db->query("SELECT * FROM `discussions` WHERE `id_user` = '$user[id]' $order ORDER BY `time` DESC LIMIT $start, $set[p_str]");
if ($k_post == 0) {
    ?>
	<div class="mess">
	<?= __('Нет новых обсуждений')?>
	</div>
	<?php
}
while ($post = $q->row()) {
    $type = $post['type'];
    $avtor = user::get_user($post['avtor']);
    
    if ($post['count'] > 0) {
        $s1 = '<font color="red">';
        $s2 = '</font>';
    } else {
        $s1 = null;
        $s2 = null;
    }
    // Подгружаем типы обсуждений
    $d = opendir('inc/');
    while ($dname = readdir($d)) {
        if ($dname != '.' && $dname != '..') {
            include 'inc/' . $dname;
        }
    }
}
// Вывод страниц
if ($k_page > 1) {
    str('?' . $sort, $k_page, $page);
}
?>
<div class='foot'>
<a href='?read=all'><img src='/style/icons/ok.gif'> Отметить всё как прочитанное</a>
</div>
<div class='foot'>
<a href='?delete=all'><img src='/style/icons/delete.gif'> Удалить все обсуждения</a> | <a href='settings.php'>Настройки</a>
</div>
<?php
include_once '../../sys/inc/tfoot.php';
?>