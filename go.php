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
if (!isset($_GET['go']) || (!$db->query(
    "SELECT COUNT(*) FROM `rekl` WHERE `id`=?i",
                                        [$_GET['go']])->el() && !preg_match('#^https?://#', @base64_decode($_GET['go'])))) {
    header("Location: index.php?".SID);
    exit;
}
if (preg_match('#^(ht|f)tps?://#', base64_decode($_GET['go']))) {
    if (isset($_SESSION['adm_auth'])) {
        unset($_SESSION['adm_auth']);
    }
    header("Location: ".base64_decode($_GET['go']));
    exit;
} else {
    //$rekl=$db->query("SELECT * FROM `rekl` WHERE `id`=?i", [$_GET['go']])->row();
    $db->query(
        'UPDATE `rekl` SET `count`=`count`+1 WHERE `id`=?i',
                [$_GET['go']]);
    if (isset($_SESSION['adm_auth'])) {
        unset($_SESSION['adm_auth']);
    }
    header("Refresh: 2; url=$rekl[link]");
    echo "За содержание рекламируемого ресурса<br />\n";
    echo "администрация сайта ".strtoupper($_SERVER['HTTP_HOST'])." ответственности не несёт.<br />\n";
    echo "<b><a href=\"$rekl[link]\">Переход</a></b><br />\n";
    echo "Переходов: $rekl[count]<br />\n";
}
include_once 'sys/inc/tfoot.php';
