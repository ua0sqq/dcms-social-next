<?php
include_once 'sys/inc/start.php';
include_once 'sys/inc/compress.php';
include_once 'sys/inc/sess.php';
include_once 'sys/inc/home.php';
include_once 'sys/inc/settings.php';
include_once 'sys/inc/db_connect.php';
include_once 'sys/inc/ipua.php';
include_once 'sys/inc/fnc.php';
include_once 'sys/inc/user.php';

if (isset($user)) {
    $ank['id'] = $user['id'];
}
if (isset($_GET['id'])) {
    $ank['id']=intval($_GET['id']);
}
if (!isset($ank['id'])) {
    header("Location: /index.php?".SID);
    exit;
}
$ank = get_user($ank['id']);

if ($ank['id']==0) {
    $ank = get_user($ank['id']);
    $set['title'] = $ank['nick'].' - страничка '; // заголовок страницы
    include_once 'sys/inc/thead.php';
    title();
    aut();
    echo "<span class=\"status\">$ank[group_name]</span><br />\n";
    if ($ank['ank_o_sebe']!=null) {
        echo "<span class=\"ank_n\">О себе:</span> <span class=\"ank_d\">$ank[ank_o_sebe]</span><br />\n";
    }
    if (isset($_SESSION['refer']) && $_SESSION['refer']!=null && otkuda($_SESSION['refer'])) {
        echo "<div class='foot'>&laquo;<a href='$_SESSION[refer]'>".otkuda($_SESSION['refer'])."</a><br />\n</div>\n";
    }
    include_once 'sys/inc/tfoot.php';
    exit;
}
/* Бан пользователя */
if ((!isset($user) || $user['group_access'] == 0) &&
    $db->query("SELECT COUNT(*) FROM `ban` WHERE `razdel` = 'all' AND `id_user` = '$ank[id]' AND (`time` > '$time' OR `navsegda` = '1')")->el()) {
    $set['title'] = $ank['nick'].' - страничка '; // заголовок страницы
    include_once 'sys/inc/thead.php';
    title();
    aut();
    
    echo '<div class="mess">';
    echo '<b><font color=red>Этот пользователь заблокирован!</font></b><br /> ';
    echo '</div>';
    
    include_once 'sys/inc/tfoot.php';
    exit;
}
// Удаление комментариев
if (isset($_GET['delete_post']) && $db->query("SELECT COUNT(*) FROM `stena` WHERE `id` = '".intval($_GET['delete_post'])."'")->el()) {
    $post = $db->query("SELECT * FROM `stena` WHERE `id` = '".intval($_GET['delete_post'])."' LIMIT 1")->row();
    
    if (user_access('guest_delete') || $ank['id'] == $user['id']) {
        $db->query("DELETE FROM `stena` WHERE `id` = '$post[id]'");
        $db->query("DELETE FROM `stena_like` WHERE `id_stena` = '$post[id]'");
        $_SESSION['message'] = 'Сообщение успешно удалено';
    }
}
/*-------------------------гости----------------------*/
if (isset($user) && $user['id'] != $ank['id'] && !isset($_SESSION['guest_' . $ank['id']])) {
    if (!$db->query("SELECT COUNT(*) FROM `my_guests` WHERE `id_ank` = '$ank[id]' AND `id_user` = '$user[id]' LIMIT 1")->el()) {
        $db->query("INSERT INTO `my_guests` (`id_ank`, `id_user`, `time`) VALUES ('$ank[id]', '$user[id]', '$time')");
        $db->query("UPDATE `user` SET `balls` = '".($ank['balls']+1)."' ,`rating_tmp` = '".($ank['rating_tmp']+1)."' WHERE `id` = '$ank[id]' LIMIT 1");
        $_SESSION['guest_' . $ank['id']] = 1;
    } elseif (!isset($_SESSION['guest_' . $ank['id']])) {
        $guest = $db->query("SELECT * FROM `my_guests` WHERE `id_ank` = '$ank[id]' AND `id_user` = '$user[id]' LIMIT 1")->row();
        $db->query("UPDATE `my_guests` SET  `time` = '$time', `read` = '1' WHERE `id` = '$guest[id]' LIMIT 1");
        $db->query("UPDATE `user` SET `rating_tmp` = '".($ank['rating_tmp']+1)."' WHERE `id` = '$ank[id]' LIMIT 1");
        $_SESSION['guest_' . $ank['id']] = 1;
    }
}
/*----------------------------------------------------*/
/*------------------------стена-----------------------*/
if (isset($user) && isset($_GET['wall']) && $_GET['wall']==1) {
    $db->query("UPDATE `user` SET `wall` = '1' WHERE `id` = '$user[id]'");
    header("Location: /info.php?id=$ank[id]");
} elseif (isset($user) && isset($_GET['wall']) && $_GET['wall']==0) {
    $db->query("UPDATE `user` SET `wall` = '0' WHERE `id` = '$user[id]'");
    header("Location: /info.php?id=$ank[id]");
} if (isset($user)) {
    $db->query("UPDATE `notification` SET `read` = '1' WHERE `type` = 'stena_komm' AND `id_user` = '$user[id]' AND `id_object` = '$ank[id]'");
} if (isset($_POST['msg']) && isset($user)) {
    $msg=$_POST['msg'];
    if (isset($_POST['translit']) && $_POST['translit']==1) {
        $msg=translit($msg);
    }
    $mat=antimat($msg);
    if ($mat) {
        $err[]='В тексте сообщения обнаружен мат: '.$mat;
    }
    if (strlen2($msg)>1024) {
        $err[]='Сообщение слишком длинное';
    } elseif (strlen2($msg)<2) {
        $err[]='Короткое сообщение';
    } elseif ($db->query("SELECT COUNT(*) FROM `stena` WHERE `id_user` = '$user[id]' AND  `id_stena` = '$ank[id]' AND `msg` = '".my_esc($msg)."' LIMIT 1")->el()) {
        $err='Ваше сообщение повторяет предыдущее';
    } elseif (!isset($err)) {		/*
        ==========================
        Уведомления об ответах
        ==========================
        */
        if (isset($user) && $respons==true) {
            $notifiacation=$db->query("SELECT * FROM `notification_set` WHERE `id_user` = '".$ank_otv['id']."' LIMIT 1")->row();
            
            if ($notifiacation['komm'] == 1 && $ank_otv['id'] != $user['id']) {
                $db->query("INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES ('$user[id]', '$ank_otv[id]', '$ank[id]', 'stena_komm', '$time')");
            }
        }
        $db->query("INSERT INTO `stena` (id_user, time, msg, id_stena) values('$user[id]', '$time', '".my_esc($msg)."', '$ank[id]')");
        $db->query("UPDATE `user` SET `balls` = '".($user['balls']+1)."' ,`rating_tmp` = '".($user['rating_tmp']+1)."' WHERE `id` = '$user[id]' LIMIT 1");
        $_SESSION['message'] = 'Сообщение успешно добавлено';
        if (isset($user)) {
            $notifiacation=$db->query("SELECT * FROM `notification_set` WHERE `id_user` = '".$post['id_user']."' LIMIT 1")->row();
            
            if ($notifiacation['komm'] == 1 && $user['id_user'] != $ank['id']) {
                $db->query("INSERT INTO `notification` (`avtor`, `id_user`, `type`, `time`) VALUES ('$user[id]', '$ank[id]', 'stena', '$time')");
            }
        }
    }
}/*---------------------------------------------------*/if ((!isset($_SESSION['refer']) || $_SESSION['refer']==null)
&& isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']!=null &&
!preg_match('#info\.php#', $_SERVER['HTTP_REFERER'])) {
    $_SESSION['refer']=str_replace('&', '&amp;', preg_replace('#^http://[^/]*/#', '/', $_SERVER['HTTP_REFERER']));
}
if (isset($_POST['rating']) && isset($user)  && $user['id']!=$ank['id'] && $user['balls']>=50 && $db->query("SELECT SUM(`rating`) FROM `user_voice2` WHERE `id_kont` = '$user[id]'")>=0) {
    $new_r=min(max(@intval($_POST['rating']), -2), 2);
    $db->query("DELETE FROM `user_voice2` WHERE `id_user` = '$user[id]' AND `id_kont` = '$ank[id]' LIMIT 1");
    if ($new_r) {
        $db->query("INSERT INTO `user_voice2` (`rating`, `id_user`, `id_kont`) VALUES ('$new_r','$user[id]','$ank[id]')");
    }
    $ank['rating']=intval($db->query("SELECT SUM(`rating`) FROM `user_voice2` WHERE `id_kont` = '$ank[id]'"));
    $db->query("UPDATE `user` SET `rating` = '$ank[rating]' WHERE `id` = '$ank[id]' LIMIT 1");
    if ($new_r>0) {
        $db->query("INSERT INTO `mail` (`id_user`, `id_kont`, `msg`, `time`) values('0', '$ank[id]', '$user[nick] оставил положительный отзыв в [url=/who_rating.php]Вашей анкете[/url]', '$time')");
    }
    if ($new_r<0) {
        $db->query("INSERT INTO `mail` (`id_user`, `id_kont`, `msg`, `time`) values('0', '$ank[id]', '$user[nick] оставил негативный отзыв в [url=/who_rating.php]Вашей анкете[/url]', '$time')");
    }
    if ($new_r==0) {
        $db->query("INSERT INTO `mail` (`id_user`, `id_kont`, `msg`, `time`) values('0', '$ank[id]', '$user[nick] оставил нейтральный отзыв в [url=/who_rating.php]Вашей анкете[/url]', '$time')");
    }
    msg('Ваше мнение о пользователе успешно изменено');
}//-------------статус запись-----------//
if (isset($_POST['status']) && isset($user) && $user['id'] == $ank['id']) {
    $msg=$_POST['status'];
    if (isset($_POST['translit']) && $_POST['translit']==1) {
        $msg=translit($msg);
    }
    $mat=antimat($msg);
    if ($mat) {
        $err[]='В тексте сообщения обнаружен мат: '.$mat;
    }
    if (strlen2($msg)>512) {
        $err='Сообщение слишком длинное';
    } elseif (strlen2($msg)<2) {
        $err='Короткое сообщение';
    } elseif ($db->query("SELECT COUNT(*) FROM `status` WHERE `id_user` = '$user[id]' AND `msg` = '".my_esc($msg)."' LIMIT 1")->el()) {
        $err='Ваше сообщение повторяет предыдущее';
    } elseif (!isset($err)) {
        $db->query("UPDATE `status` SET `pokaz` = '0' WHERE `id_user` = '$user[id]'");
        $db->query("INSERT INTO `status` (`id_user`, `time`, `msg`, `pokaz`) values('$user[id]', '$time', '".my_esc($msg)."', '1')");
        $status=$db->query("SELECT * FROM `status` WHERE `id_user` = '$ank[id]' AND `pokaz` = '1' LIMIT 1")->row();
        ######################Лента
        $q = $db->query("SELECT * FROM `frends` WHERE `user` = '".$user['id']."' AND `i` = '1'");
        while ($f = $q->row()) {
            $a=get_user($f['frend']);
            $lentaSet = $db->query("SELECT * FROM `tape_set` WHERE `id_user` = '".$a['id']."' LIMIT 1")->row(); // Общая настройка ленты
            if ($f['lenta_status'] == 1 && $lentaSet['lenta_status'] == 1) {
                $db->query("INSERT INTO `tape` (`id_user`,`ot_kogo`,  `avtor`, `type`, `time`, `id_file`) values('$a[id]', '$user[id]', '$status[id_user]', 'status', '$time', '$status[id]')");
            }
        }
        #######################Конец
        $_SESSION['message'] = 'Статус добавлен';
        header("Location: ?id=$ank[id]");
        exit;
    }
}
if (isset($_GET['off'])) {
    if ($ank['id']==$user['id']) {
        $db->query("UPDATE `status` SET `pokaz` = '0' WHERE `id_user` = '$user[id]'");
        $_SESSION['message'] = 'Статус отключен';
        header("Location: ?id=$ank[id]");
        exit;
    }
}
//-------------------------------------//
// Статус пользователя
$status=$db->query("SELECT * FROM `status` WHERE `id_user` = '$ank[id]' AND `pokaz` = '1' LIMIT 1")->row();
/* Класс к статусу */
if (isset($_GET['like']) && $user['id']!=$ank['id'] &&
    !$db->query("SELECT COUNT(*) FROM `status_like` WHERE `id_status` = '$status[id]' AND `id_user` = '$user[id]' LIMIT 1")->el()) {
    $db->query("INSERT INTO `status_like` (`id_user`, `time`, `id_status`) values('$user[id]', '$time', '$status[id]')");######################Лента
    $q = $db->query("SELECT * FROM `frends` WHERE `user` = '".$user['id']."' AND `i` = '1'");
    while ($f = $q->row()) {
        $a=get_user($f['frend']);
        $lentaSet = $db->query("SELECT * FROM `tape_set` WHERE `id_user` = '".$a['id']."' LIMIT 1")->row(); // Общая настройка ленты
        if ($a['id'] != $ank['id'] && $f['lenta_status_like']==1 && $lentaSet['lenta_status_like']==1) {
            $db->query("INSERT INTO `tape` (`id_user`,`ot_kogo`,  `avtor`, `type`, `time`, `id_file`) values('$a[id]', '$user[id]', '$status[id_user]', 'status_like', '$time', '$status[id]')");
        }
    }
    #######################Конец
    header("Location: ?id=$ank[id]");
    exit;
}
/*
=================================
добавляем в закладки
=================================
*/
if (isset($_GET['fav']) && isset($user)) {
    if (!$db->query("SELECT COUNT(*) FROM `bookmarks` WHERE `id_user` = '".$user['id']."' AND `id_object` = '".$ank['id']."' AND `type`='people' LIMIT 1")->el() && $_GET['fav'] == 1) {
        $db->query("INSERT INTO `bookmarks` (`id_object`, `id_user`, `time`,`type`) VALUES ('$ank[id]', '$user[id]', '$time','notes')");
        $_SESSION['message'] = $ank['nick'] . ' добавлен в закладки';
    }
    if ($db->query("SELECT COUNT(*) FROM `bookmarks` WHERE `id_user` = '".$user['id']."' AND `id_object` = '".$ank['id']."' AND `type`='people' LIMIT 1")->el() && $_GET['fav'] == 0) {
        $db->query("DELETE FROM `mark_people` WHERE `id_user` = '$user[id]' AND  `id_object` = '$ank[id]' AND `type`='people'");
        $_SESSION['message'] = $ank['nick'] . ' удален из закладок';
    }
    
    header("Location: /info.php?id=$ank[id]");
    exit;
}
/*------------------------статус like-----------------------*/
if (isset($user) && isset($_GET['like']) && ($_GET['like']==0 || $_GET['like']==1) &&
    !$db->query("SELECT COUNT(*) FROM `status_like` WHERE `id_user` = '$user[id]' AND `id_status`='$status[id]' LIMIT 1")->el() && $user['id']!=$ank['id']) {
    $db->query("INSERT INTO `status_like` (`id_user`, `id_status`, `like`) VALUES ('$user[id]', '$status[id]', '".intval($_GET['like'])."')");
    $db->query("UPDATE `user` SET `balls` = '".($ank['balls']+3)."' ,`rating_tmp` = '".($ank['rating_tmp']+3)."' WHERE `id` = '$ank[id]' LIMIT 1");
}
/*----------------------------------------------------------*/
/*
================================
Модуль жалобы на пользователя
и его сообщение либо контент
в зависимости от раздела
================================
*/
if (isset($_GET['spam'])  && $ank['id']!=0 && isset($user)) {
    $mess = $db->query("SELECT * FROM `stena` WHERE `id` = '".intval($_GET['spam'])."' limit 1")->row();
    $spamer = get_user($mess['id_user']);
    if (!$db->query("SELECT COUNT(*) FROM `spamus` WHERE `id_user` = '$user[id]' AND `id_spam` = '$spamer[id]' AND `razdel` = 'stena'")->el()) {
        if (isset($_POST['spamus'])) {
            if ($mess['id_user']!=$user['id']) {
                $msg=my_esc($_POST['spamus']);
                if (strlen2($msg)<3) {
                    $err='Укажите подробнее причину жалобы';
                }
                if (strlen2($msg)>1512) {
                    $err='Длина текста превышает предел в 512 символов';
                }
                if (isset($_POST['types'])) {
                    $types=intval($_POST['types']);
                } else {
                    $types='0';
                }
                if (!isset($err)) {
                    $db->query("INSERT INTO `spamus` (`id_object`, `id_user`, `msg`, `id_spam`, `time`, `types`, `razdel`, `spam`) values('$ank[id]', '$user[id]', '$msg', '$spamer[id]', '$time', '$types', 'stena', '".my_esc($mess['msg'])."')");
                    $_SESSION['message'] = 'Заявка на рассмотрение отправлена';
                    header("Location: ?id=$ank[id]&spam=$mess[id]&page=".intval($_GET['page'])."");
                    exit;
                }
            }
        }
    }
    $set['title']=$ank['nick'].' - жалоба '; // заголовок страницы
    include_once 'sys/inc/thead.php';
    title();
    aut();
    err();
    if (!$db->query("SELECT COUNT(*) FROM `spamus` WHERE `id_user` = '$user[id]' AND `id_spam` = '$spamer[id]' AND `razdel` = 'stena'")->el()) {
        echo "<div class='mess'>Ложная информация может привести к блокировке ника.
Если вас постоянно достает один человек - пишет всякие гадости, вы можете добавить его в черный список.</div>";
        echo "<form class='nav1' method='post' action='/info.php?id=$ank[id]&amp;spam=$mess[id]&amp;page=".intval($_GET['page'])."'>\n";
        echo "<b>Пользователь:</b> ";
        echo " ".avatar($spamer['id'])." <a href=\"/info.php?id=$spamer[id]\">$spamer[nick]</a>\n";
        echo "".medal($spamer['id'])." ".online($spamer['id'])." (".vremja($mess['time']).")<br />";
        echo "<b>Нарушение:</b> <font color='green'>".output_text($mess['msg'])."</font><br />";
        echo "Причина:<br />\n<select name='types'>\n";
        echo "<option value='1' selected='selected'>Спам/Реклама</option>\n";
        echo "<option value='2' selected='selected'>Мошенничество</option>\n";
        echo "<option value='3' selected='selected'>Оскорбление</option>\n";
        echo "<option value='0' selected='selected'>Другое</option>\n";
        echo "</select><br />\n";
        echo "Комментарий:$tPanel";
        echo "<textarea name=\"spamus\"></textarea><br />";
        echo "<input value=\"Отправить\" type=\"submit\" />\n";
        echo "</form>\n";
    } else {
        echo "<div class='mess'>Жалоба на <font color='green'>$spamer[nick]</font> будет рассмотрена в ближайшее время.</div>";
    }
    echo "<div class='foot'>\n";
    echo "<img src='/style/icons/str2.gif' alt='*'> <a href='/info.php?id=$ank[id]'>Назад</a><br />\n";
    echo "</div>\n";
    include_once 'sys/inc/tfoot.php';
}
/*
==================================
The End
==================================
*/$set['title']=$ank['nick'].' - страничка '; // заголовок страницы
include_once 'sys/inc/thead.php';
title();
aut();
/*
==================================
Приватность станички пользователя
==================================
*/
$uSet = $db->query("SELECT * FROM `user_set` WHERE `id_user` = '$ank[id]'  LIMIT 1")->row();
$frend = $db->query("SELECT COUNT(*) FROM `frends` WHERE (`user` = '$user[id]' AND `frend` = '$ank[id]') OR (`user` = '$ank[id]' AND `frend` = '$user[id]') LIMIT 1")->el();
$frend_new = $db->query("SELECT COUNT(*) FROM `frends_new` WHERE (`user` = '$user[id]' AND `to` = '$ank[id]') OR (`user` = '$ank[id]' AND `to` = '$user[id]') LIMIT 1")->el();
if ($ank['id'] != $user['id'] && $user['group_access'] == 0) {
    if (($uSet['privat_str'] == 2 && $frend != 2) || $uSet['privat_str'] == 0) { // Начинаем вывод если стр имеет приват настройки
        if ($ank['group_access']>1) {
            echo "<div class='err'>$ank[group_name]</div>\n";
        }
        echo "<div class='nav1'>";
        echo group($ank['id'])." $ank[nick] ";
        echo medal($ank['id'])." ".online($ank['id'])." ";
        echo "</div>";
        
        echo "<div class='nav2'>";
        echo avatar($ank['id'], true, 128, false);
        echo "<br />";
    }
    
    
    if ($uSet['privat_str'] == 2 && $frend != 2) { // Если только для друзей
        echo '<div class="mess">';
        echo 'Просматривать страничку пользователя могут только его друзья!';
        echo '</div>';
        
        // В друзья
        if (isset($user)) {
            echo '<div class="nav1">';
            if ($frend_new == 0 && $frend==0) {
                echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?add=".$ank['id']."'>Добавить в друзья</a><br />\n";
            } elseif ($frend_new == 1) {
                echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?otm=$ank[id]'>Отклонить заявку</a><br />\n";
            } elseif ($frend == 2) {
                echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?del=$ank[id]'>Удалить из друзей</a><br />\n";
            }
            echo "</div>";
        }
        include_once 'sys/inc/tfoot.php';
        exit;
    }
    
    if ($uSet['privat_str'] == 0) { // Если закрыта
        echo '<div class="mess">';
        echo 'Пользователь запретил просматривать его страничку!';
        echo '</div>';
        
        include_once 'sys/inc/tfoot.php';
        exit;
    }
}
if ($set['web']==true) {
    include_once H."user/info/web.php";
} else {
    include_once H."user/info/wap.php";
}
include_once 'sys/inc/tfoot.php';
