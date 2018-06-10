<?php
include_once '../sys/inc/start.php';
include_once '../sys/inc/compress.php';
include_once '../sys/inc/sess.php';
include_once '../sys/inc/home.php';
include_once '../sys/inc/settings.php';
include_once '../sys/inc/db_connect.php';
include_once '../sys/inc/ipua.php';
include_once '../sys/inc/fnc.php';
include_once '../sys/inc/user.php';

$set['title']='Поиск файлов'; // заголовок страницы
include_once '../sys/inc/thead.php';
title();
aut();
$db->setDebug('mydebug');
echo "<div class='foot'>";echo '<img src="/style/icons/up_dir.gif" alt="*"> <a href="/obmen/">Обменник</a><br />';
echo "</div>\n";
$search=null;
if (isset($_SESSION['search'])) {
    $search=$_SESSION['search'];
}
if (isset($_POST['search'])) {
    $search=$_POST['search'];
}
$_SESSION['search']=$search;
$search=preg_replace("#( ){2,}#", " ", $search);
$search=preg_replace("#^( ){1,}|( ){1,}$#", "", $search);
if (isset($_GET['go']) && $search!=null) {
    //$search_a=explode(' ', $search);
    //for ($i=0;$i<count($search_a);$i++) {
    //    $search_a2[$i]='<span class="search_c">'.stripcslashes(htmlspecialchars($search_a[$i])).'</span>';
    //    $search_a[$i]=stripcslashes(htmlspecialchars($search_a[$i]));
    //}
    $q_search=str_replace('%', '', $search);
    $q_search=str_replace(' ', '%', $q_search);
    $k_post=$db->query(
        'SELECT COUNT( * ) FROM `obmennik_files` WHERE `opis` LIKE "%?e%" OR `name` LIKE "%?e%"',
                       [$q_search, $q_search])->el();
    $k_page=k_page($k_post, $set['p_str']);
    $page=page($k_page);
    $start=$set['p_str']*$page-$set['p_str'];
    if ($k_post==0) {
        echo "<div class=\"p_t\">\nНет результатов</div>\n";
    }
    $q=$db->query(
        'SELECT obf.*, obd.dir, (
        SELECT COUNT( * ) FROM `obmennik_komm` WHERE `id_file`=obf.id) cnt_komm
        FROM `obmennik_files` obf
        LEFT JOIN `obmennik_dir` obd ON obd.id=obf.id_dir
        WHERE MATCH (obf.name, obf.opis) AGAINST ("?e" IN BOOLEAN MODE) ORDER BY obf.`time` DESC LIMIT ?i OFFSET ?i',
                  [$q_search, $set['p_str'], $start]);
    $i=0;
    while ($post = $q->row()) {
        $ras=$post['ras'];
        $file=H."sys/obmen/files/$post[id].dat";
        $name=$post['name'];
        $size=$post['size'];
        if ($num==0) {
            echo '<div class="nav1">';
            $num=1;
        } elseif ($num==1) {
            echo '<div class="nav2">';
            $num=0;
        }
        include 'inc/icon48.php';
        if (is_file(H.'style/themes/'.$set['set_them'].'/loads/14/'.$ras.'.png')) {
            echo "<img src='/style/themes/$set[set_them]/loads/14/$ras.png' alt='$ras' /> \n";
        } else {
            echo "<img src='/style/themes/$set[set_them]/loads/14/file.png' alt='file' /> \n";
        }
        if ($set['echo_rassh']==1) {
            $ras=$post['ras'];
        } else {
            $ras=null;
        }
        echo '<a href="/obmen'.$post['dir'] . $post['id'].'.'.$post['ras'].'?showinfo"><b>'.$post['name'].'.'.$ras.'</b></a> ('.size_file($post['size']).')<br />';
        if ($post['opis']) {
            echo rez_text(htmlspecialchars($post['opis'])).'<br />';
        }
        echo '<a href="/obmen'.$post['dir'] . $post['id'].'.'.$post['ras'].'?showinfo&amp;komm">Комментарии</a> ('.$post['cnt_komm'].')<br />';
        echo '</div>';
    }
    if ($k_page>1) {
        str("search.php?go&amp;", $k_page, $page);
        echo '<br />';
    } // Вывод страниц
} else {
    echo '<div class="foot">';
}
echo 'Поиск файлов';
echo '</div>';
echo "<form method=\"post\" action=\"search.php?go\" class=\"search\">\n";
$search=stripcslashes(htmlspecialchars($search));
echo "<input type=\"text\" name=\"search\" maxlength=\"64\" value=\"$search\" /><br />\n";
echo "<input type=\"submit\" value=\"Поиск\" />\n";
echo "</form>\n";
echo "<div class='foot'>";
echo '<img src="/style/icons/up_dir.gif" alt="*"> <a href="/obmen/">Обменник</a><br />';
echo "</div>\n";
include_once '../sys/inc/tfoot.php';
