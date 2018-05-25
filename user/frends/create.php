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

$inp_get = filter_input_array(INPUT_GET, FILTER_VALIDATE_INT);

if (isset($inp_get['no'])) {
    if (!$db->query("SELECT COUNT(*) FROM `user` WHERE `id`=?i", [$inp_get['no']])->el()) {
        $_SESSION['err'] = "Пользователь не найден";
        header("Location: index.php?");
        exit;
    }
    $db->query(
        "DELETE FROM `frends` WHERE (`user`=?i AND `frend`=?i) OR (`user`=?i AND `frend`=?i)",
               [$user['id'], $inp_get['no'], $inp_get['no'], $user['id']]);
    $db->query(
        "DELETE FROM `frends_new` WHERE (`user`=?i AND `to`=?i) OR (`user`=?i AND `to`=?i)",
               [$inp_get['no'], $user['id'], $user['id'], $inp_get['no']]);
    $db->query("OPTIMIZE TABLE `frends`");
    $db->query("OPTIMIZE TABLE `frends_new`");
    
    /*
    ==========================
    Уведомления друзьях
    ==========================
    */
    $db->query(
        "INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES (?i, ?i, ?i, ?, ?i)",
               [$user['id'], $inp_get['no'], $user['id'], 'no_frend', $time]);
        
    $_SESSION['message'] = "Заявка отклонена";
    header("Location: new.php?".SID);
    exit;
}
if (isset($inp_get['ok'])) {
    if (!$db->query("SELECT COUNT(*) FROM `user` WHERE `id`=?i", [$inp_get['ok']])->el()) {
        $_SESSION['err'] = "Пользователь не найден";
        header("Location: index.php?");
        exit;
    }
    if ($db->query(
        "SELECT COUNT(*) FROM `frends_new` WHERE `user`=?i AND `to`=?i",
                         [$inp_get['ok'], $user['id']])->el()) {
  
    // Лента
        $q = $db->query(
            "SELECT * FROM `frends` WHERE `user`=?i AND `i`=?i",
                        [$user['id'], 1]);
        // Список друзей принимающего заявку
        while ($f = $q->row()) {
            $a=get_user($f['frend']);
            // Общая настройка ленты
            $lentaSet = $db->query(
                "SELECT * FROM `tape_set` WHERE `id_user`=?i LIMIT ?i",
                                   [$a['id'], 1])->row();
        
            if ($f['lenta_frends']==1 && $lentaSet['lenta_frends']==1) {
                if (!$db->query(
                "SELECT COUNT( * ) FROM `tape` WHERE `id_user`=?i AND `type`=? AND `id_file`=?i",
                            [$a['id'], 'frends', $inp_get['ok']])->el()) {
                    /* Отправляем друзьям принявшего дружбу в ленту нового друга */
                    $db->query(
                    "INSERT INTO `tape` (`id_user`, `avtor`, `type`, `time`, `id_file`, `count`) VALUES(?i, ?i, ?, ?i, ?i, ?i)",
                           [$a['id'], $user['id'], 'frends', $time, $inp_get['ok'], 1]);
                }
            }
        }
        
        $q = $db->query(
            "SELECT * FROM `frends` WHERE `user`=?i AND `i`=?i",
                        [$inp_get['ok'], 1]);
        
        // Список друзей подавщего заявку
        while ($f = $q->row()) {
            $a=get_user($f['frend']);
            // Общая настройка ленты
            $lentaSet = $db->query("SELECT * FROM `tape_set` WHERE `id_user` = '".$a['id']."' LIMIT 1")->row();
            
            if ($f['lenta_frends']==1 && $lentaSet['lenta_frends']==1) {
                if (!$db->query(
                        "SELECT COUNT( * ) FROM `tape` WHERE `id_user`=?i AND `type`=? AND `id_file`=?i",
                                    [$a['id'], 'frends', $user['id']])->el()) {
                    /* Отправляем друзьям отправившего заявку в ленту нового друга */
                    $db->query(
                            "INSERT INTO `tape` (`id_user`, `avtor`, `type`, `time`, `id_file`, `count`) VALUES(?i, ?i, ?, ?i, ?i, ?i)",
                                   [$a['id'], $inp_get['ok'], 'frends', $time, $user['id'], 1]);
                }
            }
        }
        
        if ($db->query(
            "SELECT COUNT( * ) FROM `frends_new` WHERE (`user`=?i AND `to`=?i) OR (`user`=?i AND `to`=?i)",
                       [$user['id'], $inp_get['ok'], $inp_get['ok'], $user['id']])->el()) {
            /*
            ==========================
            Уведомления друзьях
            ==========================
            */
            $db->query(
                "INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES (?i, ?i, ?i, ?, ?i)",
                       [$user['id'], $inp_get['ok'], $user['id'], 'ok_frend', $time]);
        
            $db->query(
                "INSERT INTO `frends` (`user`, `frend`, `time`, `i`) VALUES(?i, ?i, ?i, ?i)",
                       [$user['id'], $inp_get['ok'], $time, 1]);
            $db->query(
                "INSERT INTO `frends` (`user`, `frend`, `time`, `i`) VALUES(?i, ?i, ?i, ?i)",
                       [$inp_get['ok'], $user['id'], $time, 1]);
            $db->query(
                "DELETE FROM `frends_new` WHERE (`user`=?i AND `to`=?i) OR (`user`=?i AND `to`=?i)",
                       [$inp_get['ok'], $user['id'], $user['id'], $inp_get['ok']]);
            $db->query("OPTIMIZE TABLE `frends`");
            $db->query("OPTIMIZE TABLE `frends_new`");
        }
        $_SESSION['message'] = "Пользователь добавлен в список ваших друзей";
        header("Location: new.php?".SID);
        exit;
    }
}
if (isset($inp_get['del'])) {
    if (!$db->query("SELECT COUNT( * ) FROM `user` WHERE `id`=?i", [$inp_get['del']])->el()) {
        $_SESSION['err'] = "Пользователь не найден";
        header("Location: index.php?");
        exit;
    }
    if ($db->query(
        "SELECT COUNT( * ) FROM `frends` WHERE (`user`=?i AND `frend`=?i) OR (`user` =?i AND `frend`=?i)",
                   [$user['id'], $inp_get['del'], $inp_get['del'], $user['id']])->el()) {
        /*
        ==========================
        Уведомления друзьях
        ==========================
        */
        $db->query(
            "INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES (?i, ?i, ?i, ?, ?i)",
                   [$user['id'], $inp_get['del'], $user['id'], 'del_frend', $time]);
        
        $db->query(       
            "DELETE FROM `frends` WHERE (`user`=?i AND `frend`=?i) OR (`user`=?i AND `frend`=?i)",
                   [$user['id'], $inp_get['del'], $inp_get['del'], $user['id']]);
        $db->query(
            "DELETE FROM `frends_new` WHERE (`user`=?i AND `to`=?i) OR (`user`=?i AND `to`=?i)",
                   [$inp_get['del'], $user['id'], $user['id'], $inp_get['del']]);
        $db->query("OPTIMIZE TABLE `frends`");
        $db->query("OPTIMIZE TABLE `frends_new`");
        
        $_SESSION['message']="Пользователь удален из списка ваших друзей";
        header("location:  " . htmlspecialchars($_SERVER['HTTP_REFERER']) . "");
    }
    exit;
}

if (isset($inp_get['otm'])) {
    $no = $inp_get['otm'];
    if (!$db->query("SELECT COUNT( * ) FROM `user` WHERE `id`=?i", [$no])->el()) {
        $_SESSION['err'] = "Пользователь не найден";
        header("Location: index.php?");
        exit;
    }
    if ($db->query(
        "SELECT COUNT( * ) FROM `frends_new` WHERE (`user`=?i AND `to`=?i) OR (`user`=?i AND `to`=?i)",
                   [$user['id'], $no, $no, $user['id']])->el()) {
        /*
        ==========================
        Уведомления друзьях
        ==========================
        */
        $db->query(
            "INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES (?i, ?i, ?i, ?, ?i)",
                   [$user['id'], $no, $user['id'], 'otm_frend', $time]);
  
        $db->query( 
            "DELETE FROM `frends` WHERE (`user`=?i AND `frend` =?i) OR (`user`=?i AND `frend` =?i)",
                   [$user['id'], $no, $no, $user['id']]);
        $db->query(
            "DELETE FROM `frends_new` WHERE (`user`=?i AND `to`=?i) OR (`user`=?i AND `to`=?i)",
                   [$no, $user['id'], $user['id'], $no]);
        $db->query("OPTIMIZE TABLE `frends`");
        $db->query("OPTIMIZE TABLE `frends_new`");
        
        $_SESSION['message']="Заявка отклонена";
        header("location:  " . htmlspecialchars($_SERVER['HTTP_REFERER']) . "");
    }
    exit;
}

if (isset($inp_get['add'])) {
    $ank['id']=$inp_get['add'];
    if (!$db->query("SELECT COUNT( * ) FROM `user` WHERE `id`=?i", [$ank['id']])->el()) {
        $_SESSION['err'] = "Пользователь не найден";
        header("Location: index.php?".SID);
        exit;
    }
    if ($db->query(
        "SELECT COUNT( * ) FROM `frends` WHERE (`user`=?i AND `frend`=?i) OR (`user`=?i AND `frend`=?i)",
                   [$user['id'], $ank['id'], $ank['id'], $user['id']])->el()) {
        $_SESSION['err'] = 'Пользователь уже у вас в друзьях';
        header("Location: index.php?".SID);
        exit;
    }
    if ($db->query(
        "SELECT COUNT( * ) FROM `frends_new` WHERE (`user`=?i AND `to`=?i) OR (`user`=?i AND `to`=?i)",
                   [$user['id'], $ank['id'], $ank['id'], $user['id']])->el()) {
        $_SESSION['err'] = 'Заявка уже отправлена';
        header("Location: index.php?".SID);
        exit;
    }
    if ($ank['id'] == $user['id']) {
        $_SESSION['message'] = 'I know that feel bro :-)';
        header("Location: index.php?".SID);
        exit;
    }
    
    $db->query(    
        "INSERT INTO `frends_new` (`user`, `to`, `time`) VALUES(?i, ?i, ?i)",
               [$user['id'], $ank['id'], $time]);
    $db->query("OPTIMIZE TABLE `frends_new`");
    
    $_SESSION['message']="Заявка отправлена";
    header("location:  " . htmlspecialchars($_SERVER['HTTP_REFERER']) . "");
    exit;
}
header('location:  ' . htmlspecialchars($_SERVER['HTTP_REFERER']));

include_once '../../sys/inc/tfoot.php';
