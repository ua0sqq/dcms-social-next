<?php
include_once 'sys/inc/start.php';
include_once 'sys/inc/compress.php';
include_once 'sys/inc/sess.php';
include_once 'sys/inc/home.php';
include_once 'sys/inc/settings.php';
include_once 'sys/inc/db_connect.php';
include_once 'sys/inc/ipua.php';
include_once 'sys/inc/fnc.php';
$banpage=true;
include_once 'sys/inc/user.php';
only_reg();
$set['title']='БАН';
include_once 'sys/inc/thead.php';
title();
err();
aut();
if (!isset($user)) {
    header("Location: /index.php?".SID);
    exit;
}
if (!$db->query("SELECT COUNT(*) FROM `ban` WHERE `id_user`=?i AND (`time`>?i OR `view`=?)",
                [$user['id'], $time, '0'])->el()) {
    header('Location: /index.php?'.SID);
    exit;
}
$db->query("UPDATE `ban` SET `view`=? WHERE `id_user`=?i",
           ['1', $user['id']]); // увидел причину бана
$k_post=$db->query("SELECT COUNT(*) FROM `ban` WHERE `id_user`=?i",
                   [$user['id']])->el();
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];

$q=$db->query("SELECT * FROM `ban` WHERE `id_user`=?i ORDER BY `time` DESC LIMIT ?i, ?i",
              [$user['id'], $start, $set['p_str']]);
while ($post = $q->row()) {
    $ank=get_user($post['id_ban']);
    /*-----------зебра-----------*/
    if ($num==0) {
        echo "  <div class='nav1'>\n";
        $num=1;
    } elseif ($num==1) {
        echo "  <div class='nav2'>\n";
        $num=0;
    }
    /*---------------------------*/
    echo "Бан выдал".($ank['pol']==0?"а":"")." $ank[nick]: ";
    if ($post['navsegda']==1) {
        echo " бан <font color=red><b>навсегда</b></font><br />";
    } else {
        echo " до " . vremja($post['time']) . "<br />";
    }
    echo '<b>Причина:</b> '.$pBan[$post['pochemu']].'<br />';
    echo '<b>Раздел:</b> '.$rBan[$post['razdel']].'<br />';
    echo '<b>Комментарий:</b> '.output_text($post['prich'])."<br />\n";
    if ($post['time']>$time) {
        echo "<font color=red><b>Активен</b></font><br />\n";
    }
    echo "   </div>\n";
}

if ($k_page>1) {
    str('?', $k_page, $page);
} // Вывод страниц
echo "Чтобы больше не возникало подобных ситуаций, рекомендуем Вам изучить <a href=\"/rules.php\">правила</a> нашего сайта<br />\n";
include_once 'sys/inc/tfoot.php';
