<?php
include_once '../../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/adm_check.php';
include_once H . 'sys/inc/user.php';

if (isset($user) && $user['level'] < 3) {
    header("Location: /");
}
$set['title']='Добавление записи';
include_once H . 'sys/inc/thead.php';
aut();
title();

if (isset($_GET['post'])) {
    if (isset($_POST['title'])) {
        
        $title=esc($_POST['title'], 1);
        $pos=$db->query("SELECT MAX(`pos`) FROM `rules`")->el()+1;
        
        if (!isset($err)) {
            $db->query("INSERT INTO `rules` (`time`, `title`, `id_user`, `pos`) VALUES(?i, ?, ?i, ?i)",
                       [$time, $title, $user['id'], $pos]);
            $db->query("OPTIMIZE TABLE `rules`");
            
            $_SESSION['message'] = 'Пункт успешно добавлен';
            header("Location: index.php?");
            exit;
        }
    }
    
    err();
    
    echo '<form method="post" action=""new.php?post">';
    echo 'Название (ссылка):<br /><input name="title" size="16" maxlength="32" value="" type="text" /><br />';
    echo '<input value="Добавить" type="submit" />';
    echo '</form>';
}
if (isset($_GET['msg'])) {
    if (isset($_POST['msg'])) {
        $msg=esc($_POST['msg'], 1);
        $pos=$db->query("SELECT MAX(`pos`) FROM `rules`")->el()+1;
        if (!isset($err)) {
            
            $db->query("INSERT INTO `rules` (`time`, `msg`, `id_user`, `pos`) VALUES(?i, ?, ?i, ?i)",
                       [$time, $msg, $user['id'], $pos]);
            $_SESSION['message'] = 'Текст успешно добавлен';
            header("Location: index.php?");
            exit;
        }
    }
    
    err();
    
    echo '<form method="post" action="new.php?msg">';
    echo 'Текст:<br /><textarea name="msg" ></textarea><br />';
    echo '<input value="Добавить" type="submit" />';
    echo '</form>';
}
if (isset($_GET['url'])) {
    if (isset($_POST['url']) && isset($_POST['name_url'])) {
        $url=esc($_POST['url'], 1);
        $name_url=esc($_POST['name_url'], 1);
        $pos=$db->query("SELECT MAX(`pos`) FROM `rules`")->el()+1;
        if (!isset($err)) {
            
            $db->query("INSERT INTO `rules` (`time`, `id_user`, `url`, `name_url`, `pos`) VALUES(?i, ?i, ?, ?, ?i)",
                       [$time, $user['id'], $url, $name_url, $pos]);
            $_SESSION['message'] = 'Ссылка успешно добавлена';
            header("Location: index.php?");
            exit;
        }
    }
    
    err();
    echo '<form method="post" action="new.php?url">';
    echo 'Название ссылки:<br /><input name="name_url" size="16" value="" type="text" /><br />';
    echo 'Адрес ссылки:<br /><input name="url" size="16" value="/" type="text" /><br />';
    echo '<input value="Добавить" type="submit" />';
    echo '</form>';
}

echo '<div class="foot"><img src="/style/icons/str2.gif" alt="*"/> <a href="index.php">Информация</a></div>';

include_once H . 'sys/inc/tfoot.php';
