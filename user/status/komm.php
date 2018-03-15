<?php
/*
=======================================
Статусы юзеров для Dcms-Social
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

$set['title']='Статус - комментарии';
include_once '../../sys/inc/thead.php';
title();

if (!$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)) {
    header('Location: /');
}
if (!$db->query("SELECT COUNT(*) FROM `status` WHERE `id` = '".$id."' LIMIT 1")->el()) {
    header("Location: index.php?".SID);
    exit;
}

 // Статус
$status=$db->query("SELECT * FROM `status` WHERE `id` = '".$id."' LIMIT 1")->row();
 // Автор
$anketa=$db->query("SELECT * FROM `user` WHERE `id` = $status[id_user] LIMIT 1")->row();
/*
==================================
Приватность станички пользователя
Запрещаем просмотр статусов
==================================
*/
    $uSet = $db->query("SELECT * FROM `user_set` WHERE `id_user` = '$anketa[id]'  LIMIT 1")->row();
    $frend=$db->query("SELECT COUNT(*) FROM `frends` WHERE (`user` = '$user[id]' AND `frend` = '$anketa[id]') OR (`user` = '$anketa[id]' AND `frend` = '$user[id]') LIMIT 1")->el();
    $frend_new=$db->query("SELECT COUNT(*) FROM `frends_new` WHERE (`user` = '$user[id]' AND `to` = '$anketa[id]') OR (`user` = '$anketa[id]' AND `to` = '$user[id]') LIMIT 1")->el();
if ($anketa['id'] != $user['id'] && $user['group_access'] == 0) {
    if (($uSet['privat_str'] == 2 && $frend != 2) || $uSet['privat_str'] == 0) { // Начинаем вывод если стр имеет приват настройки
        if ($anketa['group_access']>1) {
            echo "<div class='err'>$anketa[group_name]</div>\n";
        }
        echo "<div class='nav1'>";
        echo group($anketa['id'])." $anketa[nick] ";
        echo medal($anketa['id'])." ".online($anketa['id'])." ";
        echo "</div>";
        echo "<div class='nav2'>";
        avatar_ank($anketa['id']);
        echo "</div>";
    }
    
    
    if ($uSet['privat_str'] == 2 && $frend != 2) { // Если только для друзей
        echo '<div class="mess">';
        echo 'Комментировать статус пользователя могут только его друзья!';
        echo '</div>';
        
        // В друзья
        if (isset($user)) {
            echo '<div class="nav1">';
            if ($frend_new == 0 && $frend==0) {
                echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?add=".$anketa['id']."'>Добавить в друзья</a><br />\n";
            } elseif ($frend_new == 1) {
                echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?otm=$anketa[id]'>Отклонить заявку</a><br />\n";
            } elseif ($frend == 2) {
                echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?del=$anketa[id]'>Удалить из друзей</a><br />\n";
            }
            echo "</div>";
        }
        include_once '../../sys/inc/tfoot.php';
        exit;
    }
    
    if ($uSet['privat_str'] == 0) { // Если закрыта
        echo '<div class="mess">';
        echo 'Пользователь запретил комментировать его статусы!';
        echo '</div>';
        
        include_once '../../sys/inc/tfoot.php';
        exit;
    }
}
/*
================================
Модуль жалобы на пользователя
и его сообщение либо контент
в зависимости от раздела
================================
*/
if (isset($_GET['spam'])  && isset($user)) {
    $mess = $db->query("SELECT * FROM `status_komm` WHERE `id` = '".intval($_GET['spam'])."' limit 1")->row();
    $spamer = get_user($mess['id_user']);
    if (!$db->query("SELECT COUNT(*) FROM `spamus` WHERE `id_user` = '$user[id]' AND `id_spam` = '$spamer[id]' AND `razdel` = 'status_komm' AND `spam` = '".$mess['msg']."'")->el()) {
        if (isset($_POST['msg'])) {
            if ($mess['id_user']!=$user['id']) {
                $msg=my_esc($_POST['msg']);
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
                    $db->query("INSERT INTO `spamus` (`id_object`, `id_user`, `msg`, `id_spam`, `time`, `types`, `razdel`, `spam`) values('$status[id]', '$user[id]', '$msg', '$spamer[id]', '$time', '$types', 'status_komm', '".my_esc($mess['msg'])."')");
                    $_SESSION['message'] = 'Заявка на рассмотрение отправлена';
                    header("Location: ?id=$status[id]&spam=$mess[id]&page=".intval($_GET['page'])."");
                    exit;
                }
            }
        }
    }
    aut();
    err();
    if (!$db->query("SELECT COUNT(*) FROM `spamus` WHERE `id_user` = '$user[id]' AND `id_spam` = '$spamer[id]' AND `razdel` = 'status_komm'")->el()) {
        echo "<div class='mess'>Ложная информация может привести к блокировке ника. 
Если вас постоянно достает один человек - пишет всякие гадости, вы можете добавить его в черный список.</div>";
        echo "<form class='nav1' method='post' action='?id=$status[id]&amp;spam=$mess[id]&amp;page=".intval($_GET['page'])."'>\n";
        echo "<b>Пользователь:</b> ";
        echo " ".status($spamer['id'])."  ".group($spamer['id'])." <a href=\"/info.php?id=$spamer[id]\">$spamer[nick]</a>\n";
        echo "".medal($spamer['id'])." ".online($spamer['id'])." (".vremja($mess['time']).")<br />";
        echo "<b>Нарушение:</b> <font color='green'>".output_text($mess['msg'])."</font><br />";
        echo "Причина:<br />\n<select name='types'>\n";
        echo "<option value='1' selected='selected'>Спам/Реклама</option>\n";
        echo "<option value='2' selected='selected'>Мошенничество</option>\n";
        echo "<option value='3' selected='selected'>Оскорбление</option>\n";
        echo "<option value='0' selected='selected'>Другое</option>\n";
        echo "</select><br />\n";
        echo "Комментарий:$tPanel";
        echo "<textarea name=\"msg\"></textarea><br />";
        echo "<input value=\"Отправить\" type=\"submit\" />\n";
        echo "</form>\n";
    } else {
        echo "<div class='mess'>Жалоба на <font color='green'>$spamer[nick]</font> будет рассмотрена в ближайшее время.</div>";
    }
    echo "<div class='foot'>\n";
    echo "<img src='/style/icons/str2.gif' alt='*'> <a href='?id=$status[id]&page=".intval($_GET['page'])."'>Назад</a><br />\n";
    echo "</div>\n";
    include_once '../../sys/inc/tfoot.php';
    exit;
}
/*
==================================
The End
==================================
*/
/*------------очищаем счетчик этого обсуждения-------------*/
if (isset($user)) {
    $db->query("UPDATE `discussions` SET `count` = '0' WHERE `id_user` = '$user[id]' AND `type` = 'status' AND `id_sim` = '$status[id]' LIMIT 1");
}
/*---------------------------------------------------------*/
if (isset($user)) {
    $db->query("UPDATE `notification` SET `read` = '1' WHERE `type` = 'status_komm' AND `id_user` = '$user[id]' AND `id_object` = '$status[id]'");
}
if (isset($_POST['msg']) && isset($user)) {
    $msg=$_POST['msg'];
    if (isset($_POST['translit']) && $_POST['translit']==1) {
        $msg=translit($msg);
    }
    $mat=antimat($msg);
    if ($mat) {
        $err[]='В тексте сообщения обнаружен мат: '.$mat;
    }
    if (strlen2($msg)>1024) {
        $err='Сообщение слишком длинное';
    } elseif (strlen2($msg)<2) {
        $err='Короткое сообщение';
    } elseif ($db->query("SELECT COUNT(*) FROM `status_komm` WHERE `id_status` = '".$id."' AND `id_user` = '$user[id]' AND `msg` = '".my_esc($msg)."' LIMIT 1")->el()) {
        $err='Ваше сообщение повторяет предыдущее';
    } elseif (!isset($err)) {
        /*
        ==========================
        Уведомления об ответах
        ==========================
        */
        if (isset($user) && $respons==true) {
            $notifiacation=$db->query("SELECT * FROM `notification_set` WHERE `id_user` = '".$ank_otv['id']."' LIMIT 1")->row();
            
            if ($notifiacation['komm'] == 1 && $ank_otv['id'] != $user['id']) {
                $db->query("INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES ('$user[id]', '$ank_otv[id]', '$status[id]', 'status_komm', '$time')");
            }
        }
        /*
        ====================================
        Обсуждения
        ====================================
        */
        $q = $db->query("SELECT * FROM `frends` WHERE `user` = '".$status['id_user']."' AND `i` = '1'");
        while ($f = $q->row()) {
            $a=get_user($f['frend']);
            $discSet = $db->query("SELECT * FROM `discussions_set` WHERE `id_user` = '".$a['id']."' LIMIT 1")->row(); // Общая настройка обсуждений
            if ($f['disc_status']==1 && $discSet['disc_status']==1) /* Фильтр рассылки */
{
    // друзьям автора
    if (!$db->query("SELECT COUNT(*) FROM `discussions` WHERE `id_user` = '$a[id]' AND `type` = 'status' AND `id_sim` = '$status[id]' LIMIT 1")->el()) {
        if ($status['id_user']!=$a['id'] || $a['id'] != $user['id']) {
            $db->query("INSERT INTO `discussions` (`id_user`, `avtor`, `type`, `time`, `id_sim`, `count`) values('$a[id]', '$status[id_user]', 'status', '$time', '$status[id]', '1')");
        }
    } else {
        $disc = $db->query("SELECT * FROM `discussions` WHERE `id_user` = '$status[id_user]' AND `type` = 'status' AND `id_sim` = '$status[id]' LIMIT 1")->row();
        if ($status['id_user']!=$a['id'] || $a['id']!= $user['id']) {
            $db->query("UPDATE `discussions` SET `count` = '".($disc['count']+1)."', `time` = '$time' WHERE `id_user` = '$a[id]' AND `type` = 'status' AND `id_sim` = '$status[id]' LIMIT 1");
        }
    }
}
        }
        // отправляем автору
        if (!$db->query("SELECT COUNT(*) FROM `discussions` WHERE `id_user` = '$status[id_user]' AND `type` = 'status' AND `id_sim` = '$status[id]' LIMIT 1")->el()) {
            if ($status['id_user'] != $user['id']) {
                $db->query("INSERT INTO `discussions` (`id_user`, `avtor`, `type`, `time`, `id_sim`, `count`) values('$status[id_user]', '$status[id_user]', 'status', '$time', '$status[id]', '1')");
            }
        } else {
            $disc = $db->query("SELECT * FROM `discussions` WHERE `id_user` = '$status[id_user]' AND `type` = 'status' AND `id_sim` = '$status[id]' LIMIT 1")->row();
            if ($status['id_user'] != $user['id']) {
                $db->query("UPDATE `discussions` SET `count` = '".($disc['count']+1)."', `time` = '$time' WHERE `id_user` = '$status[id_user]' AND `type` = 'status' AND `id_sim` = '$status[id]' LIMIT 1");
            }
        }
        $db->query("INSERT INTO `status_komm` (`id_user`, `time`, `msg`, `id_status`) values('$user[id]', '$time', '".my_esc($msg)."', '".$id."')");
        $db->query("UPDATE `user` SET `balls` = '".($user['balls']+1)."' WHERE `id` = '$user[id]' LIMIT 1");
        $_SESSION['message'] = 'Сообщение упешно отправлено';
        header("Location: komm.php?id=$status[id]");
        exit;
    }
}
err();
aut(); // форма авторизации
echo "<div class='foot'>";
echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/info.php?id=$anketa[id]\">$anketa[nick]</a> | <a href='index.php?id=".$status['id_user']."'>Статусы</a> | <b>Комментарии</b>";
echo "</div>";
echo '<div class="main">';
status($anketa['id']);
group($anketa['id']);
echo " <a href='/info.php?id=$anketa[id]'>$anketa[nick]</a>";
echo " ".medal($anketa['id'])." ".online($anketa['id'])." <br />\n";
if ($status['id']) {
    echo '<div class="st_1"></div>';
    echo '<div class="st_2">';
    echo output_text($status['msg']).' <font size="small">'.vremja($status['time']).'</font>';
    echo "</div>";
}
echo "</div>";
echo "<div class='foot'>\n";
echo "Комментарии:\n";
echo "</div>";
$k_post=$db->query("SELECT COUNT(*) FROM `status_komm` WHERE `id_status` = '".$id."'")->el();
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
$q=$db->query("SELECT * FROM `status_komm` WHERE `id_status` = '".$id."' ORDER BY `id` DESC LIMIT $start, $set[p_str]");
echo "<table class='post'>\n";
if ($k_post==0) {
    echo "<div class='mess'>\n";
    echo "Нет сообщений\n";
    echo "</div>";
}
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
    $ank=$db->query("SELECT * FROM `user` WHERE `id` = $post[id_user] LIMIT 1")->row();
    group($ank['id']);
    echo " <a href='/info.php?id=$ank[id]'>$ank[nick]</a> ";
    if (isset($user) && $ank['id'] != $user['id']) {
        echo "<a href='?id=$status[id]&amp;response=$ank[id]'>[*]</a> ";
    }
    echo "".medal($ank['id'])." ".online($ank['id'])." (".vremja($post['time']).")<br />\n";
    $postBan = $db->query("SELECT COUNT(*) FROM `ban` WHERE (`razdel` = 'all') AND `post` = '1' AND `id_user` = '$ank[id]' AND (`time` > '$time' OR `navsegda` = '1')")->el();
    if ($postBan == 0) { // Блок сообщения
        echo output_text($post['msg'])."<br />\n";
    } else {
        echo output_text($banMess).'<br />';
    }
    if (isset($user) && ($user['level']>$ank['level'] ||  $user['id']==$ank['id'])) {
        echo "<div style='text-align:right;'>";
        if ($ank['id']!=$user['id']) {
            echo "<a href=\"?id=$status[id]&amp;spam=$post[id]&amp;page=$page\"><img src='/style/icons/blicon.gif' alt='*' title='Это спам'></a> ";
        }
        echo " <a href='delete_komm.php?id=$post[id]'><img src='/style/icons/delete.gif' alt='*'></a>";
        echo "</div>";
    }
    echo "</div>";
}
if ($k_page>1) {
    str("komm.php?id=".$id.'&amp;', $k_page, $page);
} // Вывод страниц
if (isset($user)) {
    echo "<form method=\"post\" name='message' action=\"?id=".$id."&amp;page=$page" . $go_otv . "\">\n";
    if ($set['web'] && is_file(H.'style/themes/'.$set['set_them'].'/altername_post_form.php')) {
        include_once H.'style/themes/'.$set['set_them'].'/altername_post_form.php';
    } else {
        echo "$tPanel<textarea name=\"msg\">$otvet</textarea><br />\n";
    }
    echo "<input value=\"Отправить\" type=\"submit\" />\n";
    echo "</form>\n";
}
echo "<div class='foot'>";
echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/info.php?id=$anketa[id]\">$anketa[nick]</a> | <a href='index.php?id=".$status['id_user']."'>Статусы</a> | <b>Комментарии</b>";
echo "</div>";

include_once '../../sys/inc/tfoot.php';
