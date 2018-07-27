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

$set['title']='Правила';
include_once 'sys/inc/thead.php';
title();
err();
aut();

if ((!isset($_SESSION['refer']) || $_SESSION['refer']==null)
&& isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']!=null &&
!preg_match('#info\.php#', $_SERVER['HTTP_REFERER'])) {
    $_SESSION['refer']=str_replace('&', '&amp;', preg_replace('#^http://[^/]*/#', '/', $_SERVER['HTTP_REFERER']));
}
if (is_file(H.'sys/add/rules.txt')) {
    $f=file(H.'sys/add/rules.txt');
    $k_page=k_page(count($f), $set['p_str']);
    $page=page($k_page);
    $start=$set['p_str']*($page-1);
    $end=$set['p_str']*$page;
    echo '<div class="foot">'."\n";
    for ($i=$start;$i<$end && $i<count($f);$i++) {
        echo "\t".'<p>' . ($i+1) . ') ' . trim(stripcslashes(htmlspecialchars($f[$i]))) . '</p>'."\n";
    }
    echo '</div>'."\n";
    if ($k_page>1) {
        str("?", $k_page, $page);
    } // Вывод страниц
}
if (isset($_SESSION['refer']) && $_SESSION['refer']!=null && otkuda($_SESSION['refer'])) {
    echo '<div class="foot">'."\n\t";
    echo '&laquo;' . otkuda($_SESSION['refer']) . "\n";
    echo '</div>'."\n";
}
include_once 'sys/inc/tfoot.php';
