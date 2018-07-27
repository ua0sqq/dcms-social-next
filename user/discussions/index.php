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
        $db->query(
            'UPDATE `discussions` SET `count`=?i WHERE `id_user`=?i',
                   [0, $user['id']]);
        $_SESSION['message'] = __('Список непрочитанных очищен');
        header("Location: ?");
        exit;
    }
}
if (isset($_GET['delete']) && $_GET['delete']=='all') {
    if (isset($user)) {
        $db->query(
            'DELETE FROM `discussions` WHERE `id_user`=?i',
                   [$user['id']]);
        $_SESSION['message'] = __('Список обсуждений очищен');
        header("Location: ?");
        exit;
    }
}
//------------------------like к статусу-------------------------//
if (isset($_GET['likestatus'])) {
    $status = $db->query(
        'SELECT * FROM `status` WHERE `id`=?i',
                         [$_GET['likestatus']])->row();
    
	if (isset($user) && $user['id'] != $status['id_user'] &&
    !$db->query(
        'SELECT COUNT( * ) FROM `status_like` WHERE `id_status`=?i AND `id_user`=?i',
                [$status['id'], $user['id']])->el()) {
        $db->query(
			'INSERT INTO `status_like` (`id_user`, `time`, `id_status`) VALUES(?i, ?i, ?i)',
					[$user['id'], $time, $status['id']]);
        
        $q = $db->query(
                    'SELECT * FROM `frends` WHERE `user`=?i AND `i`=?i',
							[$user['id'], 1]);
        
        while ($frend = $q->row()) {
            $db->query(
                'INSERT INTO `tape` (`id_user`,`ot_kogo`,  `avtor`, `type`, `time`, `id_file`) VALUES(?i, ?i, ?i, ?, ?i, ?i)',
                       [$frend['frend'], $user['id'], $status['id_user'], 'status_like', $time, $status['id']]);
        }
        header("Location: ?page=".intval($_GET['page']));
        exit;
    }
}

$cnt = $db->query(
    'SELECT (
SELECT COUNT( * ) FROM `discussions`  WHERE `id_user`=?i AND `count`>0 AND `avtor`=?i) count_my, (
SELECT COUNT( * ) FROM `discussions`  WHERE `id_user`=?i AND `count`>0 AND `avtor`<>?i) count_f',
			[$user['id'], $user['id'], $user['id'], $user['id']])->row();

if ($cnt['count_my']) {
    $count_my = ' <img src="/style/icons/tochka.png" alt="" />';
} else {
    $count_my = null;
}
if ($cnt['count_f']) {
    $count_f = ' <img src="/style/icons/tochka.png" alt="" />';
} else {
    $count_f = null;
}

$set['title'] = __('Обсуждения');
include_once '../../sys/inc/thead.php';
title();
err();
aut();

if (isset($_GET['order']) && $_GET['order'] == 'my') {
    $order = 'AND `avtor`=' . $user['id'];
    $sort = 'order=my&amp';
    $my = 'activ';
} elseif (isset($_GET['order']) && $_GET['order'] == 'frends') {
    $order = 'AND `avtor`<>' . $user['id'];
    $sort = 'order=frends&amp;';
    $frend = 'activ';
} else {
    $order = null;
    $sort = null;
    $all = 'activ';
}
$cnt = $db->query(
                'SELECT (
SELECT COUNT( * ) FROM `notification` WHERE `id_user`=?i AND `read`="0") is_notice, (
SELECT COUNT( * ) FROM `discussions` WHERE `id_user`=?i AND `count`>0) is_dispute, (
SELECT COUNT( * ) FROM `discussions`  WHERE `id_user`=?i ?q) cnt_dispute, (
SELECT COUNT( * ) FROM `tape` WHERE `id_user`=?i AND `read`="0") is_tape',
                        [$user['id'], $user['id'], $user['id'], $order, $user['id']])->row();
// Уведомления
if ($cnt['is_notice']) {
    $k_notif = '<span class="off">(' . $cnt['is_notice'] . ')</span>';
} else {
    $k_notif = null;
}
// Обсуждения
if ($cnt['is_dispute']) {
    $discuss = '<span class="off">(' . $cnt['is_dispute'] . ')</span>';
} else {
    $discuss = null;
}
// Лента
if ($cnt['is_tape']) {
    $lenta = '<span class="off">(' . $cnt['is_tape'] . ')</span>';
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
$k_post = $cnt['cnt_dispute'];

if (!$k_post) {
    ?>
	<div class="mess">
	<?= __('Нет новых обсуждений')?>
	</div>
	<?php
} else {

$k_page = k_page($k_post, $set['p_str']);
$page = page($k_page);
$start = $set['p_str'] * $page - $set['p_str'];
$q = $db->query(
    'SELECT * FROM `discussions` WHERE `id_user`=?i ?q ORDER BY `time` DESC LIMIT ?i OFFSET ?i',
                [$user['id'], $order, $set['p_str'], $start]); // TODO: ???

while ($post = $q->row()) {
    $type = $post['type'];
    $avtor = user::get_user($post['avtor']);
    
    if ($post['count'] > 0) {
        $s1 = '<span class="off">';
        $s2 = '</span>';
    } else {
        $s1 = null;
        $s2 = null;
    }
    // Подгружаем типы обсуждений
    if (is_file(__dir__ . '/inc/' . $post['type'] . '.php')) {
        include __dir__ . '/inc/' . $post['type'] . '.php';
    }
}
// Вывод страниц
if ($k_page > 1) {
    str('?' . $sort, $k_page, $page);
}
}
?>
<div class="foot">
	<a href="?read=all"><img src="/style/icons/ok.gif"> Отметить всё как прочитанное</a>
</div>
<div class="foot">
	<a href="?delete=all"><img src="/style/icons/delete.gif"> Удалить все обсуждения</a> | <a href="settings.php">Настройки</a>
</div>
<?php

include_once '../../sys/inc/tfoot.php';