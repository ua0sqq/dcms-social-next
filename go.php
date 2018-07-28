<?php
include_once 'sys/inc/start.php';
include_once 'sys/inc/compress.php';
include_once 'sys/inc/sess.php';
include_once 'sys/inc/home.php';
include_once 'sys/inc/settings.php';
include_once 'sys/inc/db_connect.php';
include_once 'sys/inc/ipua.php';
include_once 'sys/inc/fnc.php';
include_once 'sys/inc/user.php';

$set['title']='Перенаправление';
include_once 'sys/inc/thead.php';
title();

$go = filter_input(INPUT_GET, 'go', FILTER_DEFAULT);
if (!$go || (!$db->query('SELECT COUNT( * ) FROM `rekl` WHERE `id` = ?i',
                                        [$go])->el() && !preg_match('#^https?://#', base64_decode($go)))) {
    header('Location: index.php?' . SID);
    exit;
}
if (preg_match('#^(ht|f)tps?://#', base64_decode($go))) {
    if (isset($_SESSION['adm_auth'])) {
        unset($_SESSION['adm_auth']);
    }
    header("Location: " . base64_decode($go));
    exit;
} else {
    $rekl = $db->query('SELECT `id`, `link`, `count` FROM `rekl` WHERE `id`=?i', [$go])->row();
    $db->query('UPDATE `rekl` SET `count`=`count`+?i WHERE `id` =?i', [1, $rekl['id']]);
    if (isset($_SESSION['adm_auth'])) {
        unset($_SESSION['adm_auth']);
    }
    header('Refresh: 10; url=' . $rekl['link']);
    echo "<div class=\"mess\"><p>За содержание рекламируемого ресурса\n";
    echo "<p>администрация сайта ".strtoupper($_SERVER['HTTP_HOST'])." ответственности не несёт.\n";
    echo "<p><b><a href=\"$rekl[link]\">Переход</a></b>\n";
    echo "<p>Переходов: $rekl[count]</p></div>\n";
}
include_once 'sys/inc/tfoot.php';
