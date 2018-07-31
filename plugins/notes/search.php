<?php
include_once '../../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

$set['title']='Дневники';
include_once H . 'sys/inc/thead.php';
title();
aut(); // форма авторизации
err();
echo "<div id='comments' class='menus'>";
echo "<div class='webmenu'>";
echo "<a href='index.php' >Дневники</a>";
echo "</div>";
echo "<div class='webmenu last'>";
echo "<a href='/plugins/notes/dir.php'>Категории</a>";
echo "</div>";
        
echo "<div class='webmenu'>";
echo "<a href='search.php' class='activ'>Поиск</a>";
echo "</div>";
echo "</div>";
$usearch=null;
if (isset($_SESSION['usearch'])) {
    $usearch=$_SESSION['usearch'];
}
if (isset($_POST['usearch'])) {
    $usearch=$_POST['usearch'];
}
if ($usearch==null) {
    unset($_SESSION['usearch']);
} else {
    $_SESSION['usearch']=$usearch;
}

$usearch=preg_replace("#( ){1,}#", "", $usearch);

echo "<form method=\"post\" action=\"search.php?go\">Поиск по дневникам<br />";
//$usearch=stripcslashes(htmlspecialchars($usearch));
echo '<p><input type="text" name="usearch" maxlength="16" value="'.htmlspecialchars($usearch).'" /></p>'."\n";
echo "<p><input type=\"submit\" value=\"Искать\" /></p>";
echo "</form>\n";

if (isset($_GET['go']) && !empty($usearch)) {
    $k_post=$db->query(
                        'SELECT COUNT(*) FROM `notes` WHERE `name` LIKE "%?e%"',
                                [$usearch])->el();


    if (!$k_post) {
        echo "<div class='mess'>\n";
        echo "Нет записей\n";
        echo "</div>\n";
    } else {
        $k_page=k_page($k_post, $set['p_str']);
        $page=page($k_page);
        $start=$set['p_str']*$page-$set['p_str'];
        $q=$db->query(
                'SELECT n.*, (
SELECT COUNT(*) FROM `notes` WHERE `id`=n.id AND `time` > '.START_DAY.') new_note
FROM `notes` n WHERE n.`name` LIKE "%?e%" ORDER BY n.`time` DESC LIMIT ?i OFFSET ?i',
                        [$usearch, $set['p_str'], $start]);
        $num=0;
        while ($post = $q->row()) {
            /*-----------зебра-----------*/
            if ($num==0) {
                echo '<div class="nav1">';
                $num=1;
            } elseif ($num==1) {
                echo '<div class="nav2">';
                $num=0;
            }
            /*---------------------------*/
            echo "<img src='/style/icons/dnev.png' alt='*'> ";
            echo "<a href='/plugins/notes/list.php?id=$post[id]'>" . text($post['name']) . "</a> \n";
            echo " <span style='time'>(".vremja($post['time']).")</span>\n";
            
            if ($post['new_note']) {
                echo " <img src='/style/icons/new.gif' alt='*'>";
            }
            echo "  </div>\n";
        }

        if ($k_page>1) {
            str('?go&amp;', $k_page, $page);
        }
    }
} else {
    $err = 'Пустой запрос!';
}
include_once H . 'sys/inc/tfoot.php';
