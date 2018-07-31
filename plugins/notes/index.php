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

// Поле поиска
echo "<div class='foot'><form method=\"post\" action=\"search.php?go\">";
echo "<table><td><input style='width:95%;' type=\"text\" name=\"usearch\" maxlength=\"16\" /></td><td> \n";
echo "<input type=\"submit\" value=\"Поиск\" /></td></table>";
echo "</form></div>\n";
//  Панель навигации
echo "<div id='comments' class='menus'>";
echo "<div class='webmenu'>";
echo "<a href='index.php' class='activ'>Дневники</a>";
echo "</div>";
echo "<div class='webmenu last'>";
echo "<a href='/plugins/notes/dir.php'>Категории</a>";
echo "</div>";
if (isset($user)) {
    echo "<div class='webmenu last'>";
    echo "<a href='user.php?id=".$user['id']."'>Мои</a>";
    echo "</div>";
}
echo "</div>";
// Сортировка
$input_get = filter_input_array(INPUT_GET, ['sort' => FILTER_DEFAULT, 'new' => FILTER_DEFAULT]);
$new=null;
switch ($input_get['sort']) {
    case 't':
        $order= ['time' => false];
        echo"<div class='foot'><b>Новые</b> | <a href='?sort=c'>Популярные</a></div>\n";
        break;
    case 'c':
        $order=['count' => false];
        echo"<div class='foot'><a href='?sort=t'>Новые</a> | <b>Популярные</b></div>\n";
        // Сортировка популярных дневников по времени
        echo "<div class='nav2'>";
        switch ($input_get['new']) {
            case 't':
                echo"<b>Новые</b> | <a href='?sort=c&amp;new=m'>За месяц</a> | <a href='?sort=c&amp;new=v'>За всё время</a>\n";
                $new='AND `time`>' . (time() - 60*60*24);
                break;
            case 'm':
                echo"<a href='?sort=c&amp;new=t'>Новые</a> | <b>За месяц</b> | <a href='?sort=c&amp;new=v'>За всё время</a>\n";
                $new='AND `time`>' . (time()-2592000);
                break;
            case 'v':
                echo"<a href='?sort=c&amp;new=t'>Новые</a> | <a href='?sort=c&amp;new=m'>За месяц</a> | <b>За всё время</b>\n";
                $new=null;
                break;
            default:
                echo"<b>Новые</b> | <a href='?sort=c&amp;new=m'>За месяц</a> | <a href='?sort=c&amp;new=v'>За всё время</a>\n";
                $new='AND `time`>' . TIME_600;
        }
        echo "</div>";
        // Сортировка популярных дневников по времени
        break;
    default:
        $order= ['time' => false];
         echo "<div class='foot'><b>Новые</b> | <a href='?sort=c'>Популярные</a></div>";
}

$k_post = $db->query(
                    'SELECT COUNT( * ) FROM `notes` WHERE `private`="0" ?q;',
                    [$new])->el();

if (!$k_post) {
    echo '<div class="mess">'."\n";
    echo '   Нет записей'."\n";
    echo '</div>'."\n";
} else {
    $k_page=k_page($k_post, $set['p_str']);
    $page=page($k_page);
    $start=$set['p_str']*$page-$set['p_str'];
    $q=$db->query(
            'SELECT n.id, n.id_user, n.name, left(n.msg, 200) AS msg, n.`time`, (
SELECT COUNT( * ) FROM `notes_komm` WHERE `id_notes`=n.id) komm, (
SELECT COUNT( * ) FROM `bookmarks` WHERE `id_object`=n.id AND `type`="notes") marks, (
SELECT COUNT( * ) FROM `notes` WHERE `share_id`=n.id AND `share_type`="notes") share
FROM `notes` n WHERE n.`private`="0" ?q ORDER BY ?o LIMIT ?i OFFSET ?i',
                    [$new, $order, $set['p_str'], $start]);

    while ($post = $q->row()) {

        if ($num==0) {
            echo "  <div class='nav1'>\n";
            $num=1;
        } elseif ($num==1) {
            echo "  <div class='nav2'>\n";
            $num=0;
        }

        echo group($post['id_user'])." ";
        echo user::nick($post['id_user'], 1, 1, 1)." : <a href='/plugins/notes/list.php?id=".$post['id']."'>".text($post['name'])."</a>";
        echo '<span style="float:right;color:#666;">'.vremja($post['time']).'</span><br/>';
        echo crop_text($post['msg'])." <br/>\n";
        notes_sh($post['id']);
        echo "<br/><img src='/style/icons/uv.png'> <span style=\"color:#666;\">(".$post['komm'].") &bull;";
        echo " <a href='/plugins/notes/fav.php?id=".$post['id']."'><img src='/style/icons/add_fav.gif'> (".$post['marks'].")</a> &bull; ";
        echo " <img src='/style/icons/action_share_color.gif'> (".$post['share'].") </span>";
        echo "  </div>\n";
    }

    if (isset($_GET['sort'])) {
        $dop="sort=".htmlspecialchars($_GET['sort'])."&amp;";
    } else {
        $dop='';
    }
    if ($k_page>1) {
        str('?'.$dop, $k_page, $page);
    }
}
if (isset($user)) {
    echo "<div class='foot'><a href='add.php'> Создать запись</a></div>";
}

include_once H . 'sys/inc/tfoot.php';
