<?php
/*
=======================================
Друзья для Dcms-Social
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
include_once '../../sys/inc/compress.php';
include_once '../../sys/inc/sess.php';
include_once '../../sys/inc/home.php';
include_once '../../sys/inc/settings.php';
include_once '../../sys/inc/db_connect.php';
include_once '../../sys/inc/ipua.php';
include_once '../../sys/inc/fnc.php';
include_once '../../sys/inc/user.php';
only_reg();
if (isset($_GET['no'])) {
    $no = intval($_GET['no']);
    if ($db->query("SELECT COUNT(*) FROM `user` WHERE `id` = '$no' LIMIT 1")==0) {
        header("Location: index.php?");
        exit;
    }
    $db->query("DELETE FROM `frends` WHERE `user` = '$user[id]' AND `frend` = '$no' LIMIT 1");
    $db->query("DELETE FROM `frends` WHERE `user` = '$no' AND `frend` = '$user[id]' LIMIT 1");
    $db->query("DELETE FROM `frends_new` WHERE `user` = '$no' AND `to` = '$user[id]' LIMIT 1");
    $db->query("DELETE FROM `frends_new` WHERE `user` = '$user[id]' AND `to` = '$no' LIMIT 1");
    $db->query("OPTIMIZE TABLE `frends`");
    $db->query("OPTIMIZE TABLE `frends_new`");
    
    /*
    ==========================
    Уведомления друзьях
    ==========================
    */
    $db->query("INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES ('$user[id]', '$no', '$user[id]', 'no_frend', '$time')");
        
    $_SESSION['message']="Заявка отклонена";
    header("Location: new.php?".SID);
    exit;
}
if (isset($_GET['ok'])) {
    $ok = intval($_GET['ok']);
    if (!$db->query("SELECT COUNT(*) FROM `user` WHERE `id` = '$ok' LIMIT 1")->el()) {
        header("Location: index.php?");
        exit;
    }
    $a = $db->query("SELECT COUNT(*) FROM `frends_new` WHERE `user`='$ok' AND `to`='$user[id]'")->el();
    //$as = $db->query("SELECT * FROM `user` WHERE `id` = '".$ok."' LIMIT 1")->row();
    if ($a==0) {
        die("Ошибка");
    } else {
    
    
    /*----------------------Лента------------------------*/
        $q = $db->query("SELECT * FROM `frends` WHERE `user` = '".$user['id']."' AND `i` = '1'");
    
        /* Список друзей принимающего заявку */
        while ($f = $q->row()) {
            $a=get_user($f['frend']);
        
            $lentaSet = $db->query("SELECT * FROM `tape_set` WHERE `id_user` = '".$a['id']."' LIMIT 1")->row(); // Общая настройка ленты
        
            if ($f['lenta_frends']==1 && $lenaSet['lenta_frends']==1) /* Фильтр рассылки */
        {
            if (!$db->query("SELECT COUNT(*) FROM `tape` WHERE `id_user` = '$a[id]' AND `type` = 'frends' AND `id_file` = '$ok'")->el()) {
            
                /* Отправляем друзьям принявшего дружбу в ленту нового друга */
                $db->query("INSERT INTO `tape` (`id_user`, `avtor`, `type`, `time`, `id_file`, `count`) values('$a[id]', '$user[id]', 'frends', '$time', '$ok', '1')");
            }
        }
        }
        
        $q = $db->query("SELECT * FROM `frends` WHERE `user` = '$ok' AND `i` = '1'");
        
        /* Список друзей подавщего заявку */
        while ($f = $q->row()) {
            $a=get_user($f['frend']);
            
            $lentaSet = $db->query("SELECT * FROM `tape_set` WHERE `id_user` = '".$a['id']."' LIMIT 1")->row(); // Общая настройка ленты
            
            if ($f['lenta_frends']==1 && $lentaSet['lenta_frends']==1) /* Фильтр рассылки */
                {
                    if (!$db->query("SELECT COUNT(*) FROM `tape` WHERE `id_user` = '$a[id]' AND `type` = 'frends' AND `id_file` = '$user[id]'")->el()) {
                        
                        /* Отправляем друзьям отправившего заявку в ленту нового друга */
                        $db->query("INSERT INTO `tape` (`id_user`, `avtor`, `type`, `time`, `id_file`, `count`) values('$a[id]', '$ok', 'frends', '$time', '$user[id]', '1')");
                    }
                }
        }
        
        /*-------------------alex-borisi--------------------*/
        
        if ($db->query("SELECT COUNT(*) FROM `frends_new` WHERE (`user` = '$user[id]' AND `to` = '$ok') OR (`user` = '$ok' AND `to` = '$user[id]')")->el()) {
            /*
            ==========================
            Уведомления друзьях
            ==========================
            */
            $db->query("INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES ('$user[id]', '$ok', '$user[id]', 'ok_frend', '$time')");
        
            $db->query("INSERT INTO `frends` (`user`, `frend`, `time`, `i`) values('$user[id]', '$ok', '$time', '1')");
            $db->query("INSERT INTO `frends` (`user`, `frend`, `time`, `i`) values('$ok', '$user[id]', '$time', '1')");
            $db->query("DELETE FROM `frends_new` WHERE `user` = '$ok' AND `to` = '$user[id]' LIMIT 1");
            $db->query("DELETE FROM `frends_new` WHERE `user` = '$user[id]' AND `to` = '$ok' LIMIT 1");
            $db->query("OPTIMIZE TABLE `frends`");
            $db->query("OPTIMIZE TABLE `frends_new`");
        }
        $_SESSION['message']="Пользователь добавлен в список ваших друзей";
        header("Location: new.php?".SID);
        exit;
    }
}
if (isset($_GET['del'])) {
    $no = intval($_GET['del']);
    if (!$db->query("SELECT COUNT(*) FROM `user` WHERE `id` = '$no' LIMIT 1")->el()) {
        header("Location: index.php?");
        exit;
    }
    if ($db->query("SELECT COUNT(*) FROM `frends` WHERE (`user` = '$user[id]' AND `frend` = '$no') OR (`user` = '$no' AND `frend` = '$user[id]') LIMIT 1")->el()) {
        /*
        ==========================
        Уведомления друзьях
        ==========================
        */
        $db->query("INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES ('$user[id]', '$no', '$user[id]', 'del_frend', '$time')");
        
        $db->query("DELETE FROM `frends` WHERE `user` = '$user[id]' AND `frend` = '$no' LIMIT 1");
        $db->query("DELETE FROM `frends` WHERE `user` = '$no' AND `frend` = '$user[id]' LIMIT 1");
        $db->query("DELETE FROM `frends_new` WHERE `user` = '$no' AND `to` = '$user[id]' LIMIT 1");
        $db->query("DELETE FROM `frends_new` WHERE `user` = '$user[id]' AND `to` = '$no' LIMIT 1");
        $db->query("OPTIMIZE TABLE `frends`");
        $db->query("OPTIMIZE TABLE `frends_new`");
        $_SESSION['message']="Пользователь удален из списка ваших друзей";
        header("location:  " . htmlspecialchars($_SERVER['HTTP_REFERER']) . "");
    }
    exit;
}
if (isset($_GET['otm'])) {
    $no = intval($_GET['otm']);
    if (!$db->query("SELECT COUNT(*) FROM `user` WHERE `id` = '$no' LIMIT 1")->el()) {
        header("Location: index.php?");
        exit;
    }
    if ($db->query("SELECT COUNT(*) FROM `frends_new` WHERE (`user` = '$user[id]' AND `to` = '$no') OR (`user` = '$no' AND `to` = '$user[id]') LIMIT 1")->el()) {
        /*
        ==========================
        Уведомления друзьях
        ==========================
        */
        $db->query("INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES ('$user[id]', '$no', '$user[id]', 'otm_frend', '$time')");
  
        $db->query("DELETE FROM `frends` WHERE `user` = '$user[id]' AND `frend` = '$no' LIMIT 1");
        $db->query("DELETE FROM `frends` WHERE `user` = '$no' AND `frend` = '$user[id]' LIMIT 1");
        $db->query("DELETE FROM `frends_new` WHERE `user` = '$no' AND `to` = '$user[id]' LIMIT 1");
        $db->query("DELETE FROM `frends_new` WHERE `user` = '$user[id]' AND `to` = '$no' LIMIT 1");
        $db->query("OPTIMIZE TABLE `frends`");
        $db->query("OPTIMIZE TABLE `frends_new`");
        $_SESSION['message']="Заявка отклонена";
        header("location:  " . htmlspecialchars($_SERVER['HTTP_REFERER']) . "");
    }
    exit;
}
if (isset($_GET['add'])) {
    $ank['id']=intval($_GET['add']);
    if (!$db->query("SELECT COUNT(*) FROM `user` WHERE `id` = '$ank[id]' LIMIT 1")->el()) {
        header("Location: index.php?".SID);
        exit;
    }
    if ($db->query("SELECT COUNT(*) FROM `frends` WHERE (`user` = '$user[id]' AND `frend` = '$ank[id]') OR (`user` = '$ank[id]' AND `frend` = '$user[id]') LIMIT 1")->el()) {
        header("Location: index.php?".SID);
        exit;
    }
    if ($db->query("SELECT COUNT(*) FROM `frends_new` WHERE (`user` = '$user[id]' AND `to` = '$ank[id]') OR (`user` = '$ank[id]' AND `to` = '$user[id]') LIMIT 1")->el()) {
        header("Location: index.php?".SID);
        exit;
    }
    if ($ank['id']==$user['id']) {
        header("Location: index.php?".SID);
        exit;
    }
    
    $db->query("INSERT INTO `frends_new` (`user`, `to`, `time`) values('$user[id]', '$ank[id]', '$time')");
    $db->query("OPTIMIZE TABLE `frends_new`");
    $_SESSION['message']="Заявка отправлена";
    header("location:  " . htmlspecialchars($_SERVER['HTTP_REFERER']) . "");
    exit;
}

include_once '../../sys/inc/tfoot.php';
