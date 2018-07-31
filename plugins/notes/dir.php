<?php
/*
=======================================
Дневники для Dcms-Social
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
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/adm_check.php';
include_once H . 'sys/inc/user.php';

$set['title']='Категории';
include_once H . 'sys/inc/thead.php';
title();

if (isset($_POST['title']) && user_access('notes_edit')) {
    $title=trim($_POST['title'], 1);
    $msg=trim($_POST['msg']);
    if (strlen2($title)>32) {
        $err='Название не может превышать больше 32 символов';
    }
    if (strlen2($title)<3) {
        $err='Короткое название';
    }
    if (strlen2($msg)>10024) {
        $err='Содержание не может превышать больше 10024 символов';
    }
    if (strlen2($msg)<2) {
        $err='Содержание слишком короткое';
    }
    if (!isset($err)) {
        $db->query(
            "INSERT INTO `notes_dir` (`msg`, `name`) VALUES(?, ?)",
                   [$msg, $title]
        );
        
        $_SESSION['message']='Категория успешно создана';
        header("Location: /plugins/notes/dir.php?".SID);
        exit;
    }
}
err();
aut();
echo "<div id='comments' class='menus'>";
echo "<div class='webmenu'>";
echo "<a href='index.php'>Дневники</a>";
echo "</div>";
        
echo "<div class='webmenu last'>";
echo "<a href='/plugins/notes/dir.php' class='activ'>Категории</a>";
echo "</div>";
        
echo "<div class='webmenu last'>";
echo "<a href='/plugins/notes/search.php'>Поиск</a>";
echo "</div>";
echo "</div>";
/*
==================================
Дневники
==================================
*/
if (isset($_GET['id'])) {
    $id_dir=intval($_GET['id']);
    $kount=$db->query(
        "SELECT COUNT(*) FROM `notes_dir` WHERE `id`=?i",
                      [$id_dir]
    )->el();
}
if (isset($_GET['id']) && $kount==1) {
    if (isset($_GET['sort']) && $_GET['sort'] =='t') {
        $order = ['time' => false];
    } elseif (isset($_GET['sort']) && $_GET['sort'] =='c') {
        $order = ['count' => false];
    } else {
        $order = ['time' => false];
    }
    if (isset($user)) {
        echo'<div class="foot">';
        echo "<a href=\"user.php\">Мои дневники</a> | ";
        echo "<a href=\"add.php?id_dir=$id_dir\">Создать дневник</a>";
        echo '</div>';
    }
    if (isset($_GET['sort']) && $_GET['sort'] =='t') {
        echo'<div class="foot">';
        echo"<b>Новые</b> | <a href='?id=$id_dir&amp;sort=c'>Популярные</a>\n";
        echo '</div>';
    } elseif (isset($_GET['sort']) && $_GET['sort'] =='c') {
        echo'<div class="foot">';
        echo"<a href='?id=$id_dir&amp;sort=t'>Новые</a> | <b>Популярные</b>\n";
        echo '</div>';
    } else {
        echo'<div class="foot">';
        echo"<b>Новые</b> | <a href='?id=$id_dir&amp;sort=c'>Популярные</a>\n";
        echo '</div>';
    }
    $k_post = $db->query(
        "SELECT COUNT(*) FROM `notes`  WHERE `id_dir`=?i",
                       [$id_dir]
    )->el();

    if (!$k_post) {
        echo "  <div class='mess'>\n";
        echo "Нет записей\n";
        echo "  </div>\n";
    } else {
        $k_page=k_page($k_post, $set['p_str']);
        $page=page($k_page);
        $start=$set['p_str']*$page-$set['p_str'];
        $q=$db->query(
        "SELECT n.*, (
SELECT COUNT(*) FROM `notes` WHERE `id` =n.id AND `time`>?i) new_notes
FROM `notes` n WHERE n.`id_dir`=?i ORDER BY ?o LIMIT ?i OFFSET ?i",
                  [START_DAY, $id_dir, $order, $set['p_str'], $start]
    );
        $num=0;
        while ($post = $q->row()) {
            /*-----------зебра-----------*/
            if ($num==0) {
                echo "  <div class='nav1'>\n";
                $num=1;
            } elseif ($num==1) {
                echo "  <div class='nav2'>\n";
                $num=0;
            }
            /*---------------------------*/
            echo "<img src='/style/icons/dnev.png' alt='*'> ";
            echo "<a href='/plugins/notes/list.php?id=$post[id]&amp;dir=$post[id_dir]'>" . htmlspecialchars($post['name']) . "</a> \n";
            echo " <span style='time'>(".vremja($post['time']).")</span>\n";
        
            if ($post['new_notes']) {
                echo " <img src='/style/icons/new.gif' alt='*'>";
            }
            echo "   </div>\n";
        }
        if (isset($_GET['sort'])) {
            $dop="sort=" . htmlspecialchars($_GET['sort']) . "&amp;";
        } else {
            $dop='';
        }
        if ($k_page>1) {
            str('?id='.$id_dir.'&amp;'.$dop.'', $k_page, $page);
        }
    }
    include_once H . 'sys/inc/tfoot.php';
    exit;
}
/*
==================================
Категории
==================================
*/
$k_post=$db->query("SELECT COUNT( * ) FROM `notes_dir`")->el();
$q=$db->query(
              'SELECT dr.*, (
SELECT COUNT( * ) FROM `notes`  WHERE `id_dir`=dr.id) all_notes, (
SELECT COUNT( * ) FROM `notes`  WHERE `id_dir`=dr.id AND `time` >?i) new_notes
FROM `notes_dir` dr ORDER BY dr.`id` DESC',
                    [START_DAY]);

if ($k_post==0) {
    echo "  <div class='mess'>\n";
    echo "Нет категорий\n";
    echo "  </div>\n";
}
$num=0;
while ($post = $q->row()) {
    if ($num==0) {
        echo "  <div class='nav1'>\n";
        $num=1;
    } elseif ($num==1) {
        echo "  <div class='nav2'>\n";
        $num=0;
    }

    echo "<img src='/style/themes/$set[set_them]/loads/14/dir.png' alt='*'> ";
    if ($post['new_notes']) {
        $post['new_notes'] = '<font color="red">+' . $post['new_notes'] . '</font>';
    } else {
        $post['new_notes'] = null;
    }
    echo "<a href='/plugins/notes/dir.php?id=$post[id]'>" . output_text($post['name']) . "</a> (" . $post['all_notes'] . ") " . $post['new_notes'] . "\n";
    if (isset($user) && ($user['level']>3)) {
        echo "<a href='./delete.php?dir=$post[id]'><img src='/style/icons/delete.gif' alt='*'></a><br />\n";
    }
    echo output_text($post['msg'])."<br />\n";
    echo "   </div>\n";
}

if (isset($user) && user_access('notes_edit')) {
    if (isset($_GET['create'])) {
        echo "<form method=\"post\" action=\"/plugins/notes/dir.php\">\n";
        echo "Название:<br />\n<input name=\"title\" size=\"16\" maxlength=\"32\" value=\"\" type=\"text\" /><br />\n";
        echo "Описание:<br />\n<textarea name=\"msg\" ></textarea><br />\n";
        echo "<input value=\"Создать\" type=\"submit\" />\n";
        echo "</form>\n";
    } else {
        echo "<div class='foot'>\n";
        echo "<img src='/style/icons/str2.gif' alt='*'> <a href='/plugins/notes/dir.php?create'>Добавить категорию</a><br />\n";
        echo "</div>\n";
    }
}
echo "<div class='foot'>\n";
echo "<img src='/style/icons/str2.gif' alt='*'> <a href='index.php'>Все дневники</a><br />\n";
echo "</div>\n";
include_once H . 'sys/inc/tfoot.php';
