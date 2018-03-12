<?php

$list=null;
if ($l=='/') {
    $set['title']='Файловый обменник';
} // заголовок страницы
else {
    $set['title']='Обменник - '.$dir_id['name'];
} // заголовок страницы
$_SESSION['page']=1;
include_once '../sys/inc/thead.php';
title();
 // Файл который перемещаем
if (isset($_GET['trans'])) {
    $trans = $db->query("SELECT * FROM `obmennik_files` WHERE `id` = '".intval($_GET['trans'])."' AND `id_user` = '$user[id]' LIMIT 1")->row();
}
 // Загрузка файла
include 'inc/upload_act.php';
 // Действие над папкой
include 'inc/admin_act.php';
err();
aut(); // форма авторизации
if ($l!='/') {
    echo '<div class="foot">';
    echo '<img src="/style/icons/up_dir.gif" alt="*"> <a href="/obmen/">Обменник</a> &gt; '.obmen_path($l).'<br />';
    echo '</div>';
}
if (!isset($_GET['act']) && !isset($_GET['trans'])) {
    echo '<div class="foot">';
    echo '<img src="/style/icons/search.gif" alt="*"> <a href="/obmen/search.php">Поиск файлов</a> ';
    
    if (isset($user) && $dir_id['upload'] == 1) {
        $dir_user = $db->query("SELECT * FROM `user_files`  WHERE `id_user` = '$user[id]' AND `osn` = '1'")->row();
        echo ' | <a href="/user/personalfiles/' . $user['id'] . '/' . $dir_user['id'] . '/?obmen_dir=' . $dir_id['id'] . '">Добавить файл</a>';
    }
    
    echo '</div>';
}
echo '<table class="post">';
$q=$db->query("SELECT * FROM `obmennik_dir` WHERE `dir_osn` = '/$l' OR `dir_osn` = '$l/' OR `dir_osn` = '$l' ".(user_access('obmen_dir_edit')?"":"AND `my` = '0'")." ORDER BY `name`,`num` ASC");
while ($post = $q->row()) {
    $set['p_str']=50;
    $list[]=array('dir'=>1,'post'=>$post);
}
$q=$db->query("SELECT * FROM `obmennik_files` WHERE `id_dir` = '$id_dir' ORDER BY `$sort_files` DESC");
while ($post = $q->row()) {
    $list[]=array('dir'=>0,'post'=>$post);
}
$k_post=sizeof($list);
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
if ($dir_id['upload']==1 && $k_post > 1 && !isset($_GET['trans'])) {
    /*------------сортировка файлов--------------*/
    echo "<div id='comments' class='menus'>";
    echo "<div class='webmenu'>";
    echo "<a href='?komm&amp;page=$page&amp;sort_files=0' class='".($_SESSION['sort']==0?'activ':'')."'>Новые</a>";
    echo "</div>";
    echo "<div class='webmenu'>";
    echo "<a href='?komm&amp;page=$page&amp;sort_files=1' class='".($_SESSION['sort']==1?'activ':'')."'>Популярные</a>";
    echo "</div>";
    echo "</div>";
    /*---------------alex-borisi---------------------*/
}
if (isset($user) && $dir_id['upload']==1 && isset($_GET['trans'])) {
    echo '<div class="mess">';
    echo '<img src="/style/icons/ok.gif" alt="*"> <b><a href="?act=upload&amp;trans='.$trans['id'].'&amp;ok">Добавить сюда</a></b><br />';
    echo '</div>';
}
if ($k_post == 0) {
    echo '<div class="mess">';
    echo 'Папка пуста';
    echo '</div>';
}
for ($i=$start;$i<$k_post && $i<$set['p_str']*$page;$i++) {
    if ($list[$i]['dir']==1) { // папка
        $post=$list[$i]['post'];
        /*-----------зебра-----------*/
        if ($num==0) {
            echo '<div class="nav1">';
            $num=1;
        } elseif ($num==1) {
            echo '<div class="nav2">';
            $num=0;
        }
        /*---------------------------*/
        echo '<img src="/style/themes/'.$set['set_them'].'/loads/14/dir.png" alt="" /> ';
        if (!isset($_GET['trans'])) {
            echo '<a href="/obmen'.$post['dir'].'">'.htmlspecialchars($post['name']).'</a>';
            $k_f=0;
            $k_n=0;
            $q3=$db->query("SELECT * FROM `obmennik_dir` WHERE `dir_osn` like '$post[dir]%'");
            while ($post2 = $q3->row()) {
                $k_f=$k_f+$db->query("SELECT COUNT(*) FROM `obmennik_files` WHERE `id_dir` = '$post2[id]'")->el();
                $k_n=$k_n+$db->query("SELECT COUNT(*) FROM `obmennik_files` WHERE `id_dir` = '$post2[id]' AND `time_go` > '" . $ftime . "'")->el();
            }
            $k_f=$k_f+$db->query("SELECT COUNT(*) FROM `obmennik_files` WHERE `id_dir` = '$post[id]'")->el();
            $k_n=$k_n+$db->query("SELECT COUNT(*) FROM `obmennik_files` WHERE `id_dir` = '$post[id]' AND `time_go` > '" . $ftime . "'")->el();
            if ($k_n==0) {
                $k_n=null;
            } else {
                $k_n='<font color="red">+'.$k_n.'</font>';
            }
            echo ' ('.$k_f.') '.$k_n.'<br />';
        } else {
            echo '<a href="/obmen'.$post['dir'].'?trans='.$trans['id'].'">'.htmlspecialchars($post['name']).'</a>';
        }
        echo '</div>';
    } elseif (!isset($_GET['trans'])) {
        $post=$list[$i]['post'];
        $k_p=$db->query("SELECT COUNT(*) FROM `obmennik_komm` WHERE `id_file` = '$post[id]'")->el();
        $ras=$post['ras'];
        $file=H."sys/obmen/files/$post[id].dat";
        $name=$post['name'];
        $size=$post['size'];
        /*-----------зебра-----------*/
        if ($num==0) {
            echo '<div class="nav1">';
            $num=1;
        } elseif ($num==1) {
            echo '<div class="nav2">';
            $num=0;
        }
        /*---------------------------*/
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
        echo '<a href="/obmen'.$dir_id['dir'] . $post['id'].'.'.$post['ras'].'?showinfo"><b>'.htmlspecialchars($post['name']).'.'.$ras.'</b></a> ('.size_file($post['size']).') ';
        if ($post['metka'] == 1) {
            echo '<font color=red><b>(18+)</b></font> ';
        }
        echo '<br />';
        if ($post['opis']) {
            echo rez_text(htmlspecialchars($post['opis'])).'<br />';
        }
        echo '<a href="/obmen'.$dir_id['dir'] . $post['id'].'.'.$post['ras'].'?showinfo&amp;komm">Комментарии</a> ('.$k_p.')<br />';
        echo '</div>';
    }
}
echo '</table>';
if ($k_page>1 && !isset($_GET['trans'])) {
    str('?', $k_page, $page);
} // Вывод страниц
 
if ($l!='/') {
    echo '<div class="foot">';
    echo '<img src="/style/icons/up_dir.gif" alt="*"> <a href="/obmen/">Обменник</a> &gt; '.obmen_path($l).'<br />';
    echo '</div>';
}
include 'inc/admin_form.php';
