<?php
include_once '../../sys/inc/start.php';
include_once '../../sys/inc/compress.php';
include_once '../../sys/inc/sess.php';
include_once '../../sys/inc/home.php';
include_once '../../sys/inc/settings.php';
include_once '../../sys/inc/db_connect.php';
include_once '../../sys/inc/ipua.php';
include_once '../../sys/inc/fnc.php';
include_once '../../sys/inc/user.php';
/* Бан пользователя */
if (isset($user) && $db->query("SELECT COUNT(*) FROM `ban` WHERE `razdel` = 'notes' AND `id_user` = '$user[id]' AND (`time` > '$time' OR `view` = '0')")!=0) {
    header('Location: /ban.php?'.SID);
    exit;
}
$notes = $db->query("SELECT * FROM `notes` WHERE `id` = '". intval($_GET['id']) ."' LIMIT 1")->row();
$avtor=get_user($notes['id_user']);
if (isset($user)) {
    $count=$db->query("SELECT COUNT(*) FROM `notes_count` WHERE `id_user` = '".$user['id']."' AND `id_notes` = '".$notes['id']."' LIMIT 1");
}
 // Закладки
$markinfo=$db->query("SELECT COUNT(*) FROM `mark_notes` WHERE `id_list` = '".$notes['id']."'");
if (isset($user)) {
    $db->query("UPDATE `notification` SET `read` = '1' WHERE `type` = 'notes_komm' AND `id_user` = '$user[id]' AND `id_object` = '$notes[id]'");
}
/*
================================
Модуль жалобы на пользователя
и его сообщение либо контент
в зависимости от раздела
================================
*/
if (isset($_GET['spam'])  &&  isset($user)) {
    $mess = $db->query("SELECT * FROM `notes_komm` WHERE `id` = '".intval($_GET['spam'])."' limit 1")->row();
    $spamer = get_user($mess['id_user']);
    if ($db->query("SELECT COUNT(*) FROM `spamus` WHERE `id_user` = '$user[id]' AND `id_spam` = '$spamer[id]' AND `razdel` = 'notes_komm' AND `spam` = '".$mess['msg']."'")==0) {
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
                    $db->query("INSERT INTO `spamus` (`id_object`, `id_user`, `msg`, `id_spam`, `time`, `types`, `razdel`, `spam`) values('$notes[id]', '$user[id]', '$msg', '$spamer[id]', '$time', '$types', 'notes_komm', '".my_esc($mess['msg'])."')");
                    $_SESSION['message'] = 'Заявка на рассмотрение отправлена';
                    header("Location: ?id=$notes[id]&page=".intval($_GET['page'])."&spam=$mess[id]");
                    exit;
                }
            }
        }
    }
    $set['title']='Дневник ' . htmlspecialchars($notes['name']) . '';
    include_once '../../sys/inc/thead.php';
    title();
    aut();
    err();
    if ($db->query("SELECT COUNT(*) FROM `spamus` WHERE `id_user` = '$user[id]' AND `id_spam` = '$spamer[id]' AND `razdel` = 'notes_komm'")==0) {
        echo "<div class='mess'>Ложная информация может привести к блокировке ника. 
Если вас постоянно достает один человек - пишет всякие гадости, вы можете добавить его в черный список.</div>";
        echo "<form class='nav1' method='post' action='?id=$notes[id]&amp;page=".intval($_GET['page'])."&amp;spam=$mess[id]'>\n";
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
    echo "<img src='/style/icons/str2.gif' alt='*'> <a href='?id=$notes[id]&amp;page=".intval($_GET['page'])."'>Назад</a><br />\n";
    echo "</div>\n";
    include_once '../../sys/inc/tfoot.php';
    exit;
}
/*
==================================
The End
==================================
*/
 // Запись просмотра
if (isset($user) && $db->query("SELECT COUNT(*) FROM `notes_count` WHERE `id_user` = '".$user['id']."' AND `id_notes` = '".$notes['id']."' LIMIT 1")==0) {
    $db->query("INSERT INTO `notes_count` (`id_notes`, `id_user`) VALUES ('$notes[id]', '$user[id]')");
    $db->query("UPDATE `notes` SET `count` = '".($notes['count']+1)."' WHERE `id` = '$notes[id]' LIMIT 1");
}
/*------------очищаем счетчик этого обсуждения-------------*/
if (isset($user)) {
    $db->query("UPDATE `discussions` SET `count` = '0' WHERE `id_user` = '$user[id]' AND `type` = 'notes' AND `id_sim` = '$notes[id]' LIMIT 1");
}
/*---------------------------------------------------------*/
$set['title']='Дневник - ' . htmlspecialchars($notes['name']) . '';
$set['meta_description'] = htmlspecialchars($notes['msg']);
include_once '../../sys/inc/thead.php';
if (isset($_POST['msg']) && isset($user)) {
    $msg=$_POST['msg'];
    if (strlen2($msg)>1024) {
        $err='Сообщение слишком длинное';
    } elseif (strlen2($msg)<2) {
        $err='Короткое сообщение';
    } elseif ($db->query("SELECT COUNT(*) FROM `notes_komm` WHERE `id_notes` = '".intval($_GET['id'])."' AND `id_user` = '$user[id]' AND `msg` = '".my_esc($msg)."' LIMIT 1")!=0) {
        $err='Ваше сообщение повторяет предыдущее';
    } elseif (!isset($err)) {
        /*
        ==========================
        Уведомления об ответах
        ==========================
        */
        if (isset($user) && $respons==true) {
            $notifiacation=$db->query("SELECT * FROM `notification_set` WHERE `id_user` = '".$ank_reply['id']."' LIMIT 1")->row();
            
            if ($notifiacation['komm'] == 1 && $ank_reply['id'] != $user['id']) {
                $db->query("INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES ('$user[id]', '$ank_reply[id]', '$notes[id]', 'notes_komm', '$time')");
            }
        }
        /*
        ====================================
        Обсуждения
        ====================================
        */
        $q = $db->query("SELECT * FROM `frends` WHERE `user` = '".$notes['id_user']."' AND `i` = '1'");
        while ($f = $q->row()) {
            $a=get_user($f['frend']);
            $discSet = $db->query("SELECT * FROM `discussions_set` WHERE `id_user` = '".$a['id']."' LIMIT 1")->row(); // Общая настройка обсуждений
            if ($f['disc_notes']==1 && $discSet['disc_notes']==1) /* Фильтр рассылки */
{
//---------друзьям автора--------------//
if ($db->query("SELECT COUNT(*) FROM `discussions` WHERE `id_user` = '$a[id]' AND `type` = 'notes' AND `id_sim` = '$notes[id]' LIMIT 1")==0) {
    if ($notes['id_user'] != $a['id']  || $a['id'] != $user['id']) {
        $db->query("INSERT INTO `discussions` (`id_user`, `avtor`, `type`, `time`, `id_sim`, `count`) values('$a[id]', '$notes[id_user]', 'notes', '$time', '$notes[id]', '1')");
    }
} else {
    $disc = $db->query("SELECT * FROM `discussions` WHERE `id_user` = '$a[id]' AND `type` = 'notes' AND `id_sim` = '$notes[id]' LIMIT 1")->row();
    if ($notes['id_user'] != $a['id'] || $a['id'] != $user['id']) {
        $db->query("UPDATE `discussions` SET `count` = '".($disc['count']+1)."', `time` = '$time' WHERE `id_user` = '$a[id]' AND `type` = 'notes' AND `id_sim` = '$notes[id]' LIMIT 1");
    }
}
//-------------------------------------//
}
        }
        //-------------отправляем автору------------//
        if ($db->query("SELECT COUNT(*) FROM `discussions` WHERE `id_user` = '$notes[id_user]' AND `type` = 'notes' AND `id_sim` = '$notes[id]' LIMIT 1")==0) {
            if ($notes['id_user'] != $user['id']) {
                $db->query("INSERT INTO `discussions` (`id_user`, `avtor`, `type`, `time`, `id_sim`, `count`) values('$notes[id_user]', '$notes[id_user]', 'notes', '$time', '$notes[id]', '1')");
            }
        } else {
            $disc = $db->query("SELECT * FROM `discussions` WHERE `id_user` = '$notes[id_user]' AND `type` = 'notes' AND `id_sim` = '$notes[id]' LIMIT 1")->row();
            if ($notes['id_user'] != $user['id']) {
                $db->query("UPDATE `discussions` SET `count` = '".($disc['count']+1)."', `time` = '$time' WHERE `id_user` = '$notes[id_user]' AND `type` = 'notes' AND `id_sim` = '$notes[id]' LIMIT 1");
            }
        }
        $db->query("INSERT INTO `notes_komm` (`id_user`, `time`, `msg`, `id_notes`) values('$user[id]', '$time', '".my_esc($msg)."', '".intval($_GET['id'])."')");
        $db->query("UPDATE `user` SET `balls` = '".($user['balls']+1)."' WHERE `id` = '$user[id]' LIMIT 1");
        $_SESSION['message'] = 'Сообщение успешно отправлено';
        header("Location: list.php?id=$notes[id]&page=".intval($_GET['page'])."");
        exit;
    }
}
if (isset($user)) {
    $frend=$db->query("SELECT COUNT(*) FROM `frends` WHERE (`user` = '$user[id]' AND `frend` = '$avtor[id]') OR (`user` = '$avtor[id]' AND `frend` = '$user[id]') LIMIT 1");
}
title();
aut(); // форма авторизации
err();
if ($notes['private']==1 && $user['id']!=$avtor['id'] && $frend!=2  && !user_access('notes_delete')) {
    msg('Дневник доступен только для друзей');
    echo "  <div class='foot'>\n";
    echo "<a href='index.php'>Назад</a><br />\n";
    echo "   </div>\n";
    include_once '../../sys/inc/tfoot.php';
    exit;
}
if ($notes['private']==2 && $user['id']!=$avtor['id']  && !user_access('notes_delete')) {
    msg('Пользователь запретил просмотр дневника');
    echo "  <div class='foot'>\n";
    echo "<a href='index.php'>Назад</a><br />\n";
    echo "   </div>\n";
    include_once '../../sys/inc/tfoot.php';
    exit;
}
if (isset($_GET['delete']) && ($user['id']==$avtor['id'] || user_access('notes_delete'))) {
    echo "<center>";
    echo "Вы действительно хотите удалить дневник " . output_text($notes['name']) . "?<br />";
    echo "[<a href='delete.php?id=$notes[id]'><img src='/style/icons/ok.gif'> удалить</a>] [<a href='list.php?id=$notes[id]'><img src='/style/icons/delete.gif'> отмена</a>] \n";
    echo "</center>";
    include_once '../../sys/inc/tfoot.php';
}
if (isset($user)) {
    //------------------------like-----------------//
    if (isset($_GET['like']) && $_GET['like'] == 1) {
        if ($db->query("SELECT COUNT(*) FROM `notes_like` WHERE `id_user` = '".$user['id']."' AND `id_notes` = '".$notes['id']."' LIMIT 1")==0) {
            $db->query("INSERT INTO `notes_like` (`id_notes`, `id_user`, `like`) VALUES ('$notes[id]', '$user[id]', '1')");
            $db->query("UPDATE `notes` SET `count` = '".($notes['count']+1)."' WHERE `id` = '$notes[id]' LIMIT 1");
            $_SESSION['message'] = 'Ваш голос засчитан';
            header("Location: list.php?id=$notes[id]&page=".intval($_GET['page'])."");
            exit;
        }
    }
    //-----------------------------------------------//
    //------------------------dlike-----------------//
    if (isset($_GET['like']) && $_GET['like'] == 0) {
        if ($db->query("SELECT COUNT(*) FROM `notes_like` WHERE `id_user` = '".$user['id']."' AND `id_notes` = '".$notes['id']."' LIMIT 1")==0) {
            $db->query("INSERT INTO `notes_like` (`id_notes`, `id_user`, `like`) VALUES ('$notes[id]', '$user[id]', '0')");
            $db->query("UPDATE `notes` SET `count` = '".($notes['count']-1)."' WHERE `id` = '$notes[id]' LIMIT 1");
            $_SESSION['message'] = 'Ваш голос засчитан';
            header("Location: list.php?id=$notes[id]&page=".intval($_GET['page'])."");
            exit;
        }
    }
    //-----------------------------------------------//
    //-----------------------добавляем в закладки------------//
    if (isset($_GET['fav']) && $_GET['fav']==1) {
        if ($db->query("SELECT COUNT(*) FROM `mark_notes` WHERE `id_user` = '".$user['id']."' AND `id_list` = '".$notes['id']."' LIMIT 1")==0) {
            $db->query("INSERT INTO `mark_notes` (`id_list`, `id_user`, `time`) VALUES ('$notes[id]', '$user[id]', '$time')");
            $_SESSION['message'] = 'Дневник добавлен в закладки';
            header("Location: list.php?id=$notes[id]&page=".intval($_GET['page'])."");
            exit;
        }
    }
    //-------------------------------------------------------//
    //-----------------------удаляем из закладок------------//
    if (isset($_GET['fav']) && $_GET['fav']==0) {
        if ($db->query("SELECT COUNT(*) FROM `mark_notes` WHERE `id_user` = '".$user['id']."' AND `id_list` = '".$notes['id']."' LIMIT 1")==1) {
            $db->query("DELETE FROM `mark_notes` WHERE `id_user` = '$user[id]' AND  `id_list` = '$notes[id]' ");
            $_SESSION['message'] = 'Дневник удален из закладок';
            header("Location: list.php?id=$notes[id]&page=".intval($_GET['page'])."");
            exit;
        }
    }
    //-------------------------------------------------------//
}
echo "<div class=\"foot\">\n";
echo "<img src='/style/icons/str2.gif' alt='*'> <a href='index.php'>Дневники</a> | <a href='/info.php?id=$avtor[id]'>$avtor[nick]</a>\n";
echo ' | <b>' . output_text($notes['name']) . '</b>';
echo "</div>\n";
echo "<div class=\"main\">\n";
echo "Cоздан: (".vremja($notes['time']).")\n";
echo "</div>\n";
$stat1 = $notes['msg'];
if (!$set['web']) {
    $mn=10;
} else {
    $mn=30;
} // количество слов выводится в зависимости от браузера
$stat=explode(' ', $stat1); // деление статьи на отдельные слова
$k_page=k_page(count($stat), $set['p_str']*$mn);
$page=page($k_page);
$start=$set['p_str']*$mn*($page-1);
$stat_1=null;
for ($i=$start;$i<$set['p_str']*$mn*$page && $i<count($stat);$i++) {
    $stat_1.=$stat[$i].' ';
}
echo output_text($stat_1) , '<br />'; // вывод статьи со всем форматированием
if ($k_page>1) {
    str("?id=$notes[id]&amp;", $k_page, $page);
} // Вывод страниц
/*----------------------листинг-------------------*/
$listr = $db->query("SELECT * FROM `notes` WHERE `id` < '$notes[id]' ORDER BY `id` DESC LIMIT 1")->row();
$list = $db->query("SELECT * FROM `notes` WHERE `id` > '$notes[id]' ORDER BY `id`  ASC LIMIT 1")->row();
echo '<div class="c2" style="text-align: center;">';
echo '<span class="page">'.($list['id']?'<a href="list.php?id='.$list['id'].'">&laquo; Пред.</a> ':'&laquo; Пред. ').'</span>';
$k_1=$db->query("SELECT COUNT(*) FROM `notes` WHERE `id` > '$notes[id]'")+1;
$k_2=$db->query("SELECT COUNT(*) FROM `notes`");
echo ' ('.$k_1.' из '.$k_2.') ';
echo '<span class="page">' . ($listr['id'] ? '<a href="list.php?id=' . $listr['id'] . '">След. &raquo;</a>' : ' След. &raquo;') . '</span>';
echo '</div>';
/*----------------------alex-borisi---------------*/
if (isset($user) && (user_access('notes_delete') || $user['id']==$avtor['id'])) {
    echo "<div class='main2'>";
    echo "Инструменты: ";
    echo " [<a href='edit.php?id=$notes[id]'><img src='/style/icons/edit.gif'> ред</a>] [<a href='?id=$notes[id]&amp;delete'><img src='/style/icons/delete.gif'> удл</a>]\n";
    echo "</div>";
}
echo "<div class='main'>";
echo "<b>Рейтинг: $notes[count]</b><br />";
echo "</div>";
if (isset($user) && $user['id']!=$avtor['id']) {
    echo "<div class='main'>";
    if ($db->query("SELECT COUNT(*) FROM `notes_like` WHERE `id_user` = '".$user['id']."' AND `id_notes` = '".$notes['id']."' LIMIT 1")==0) {
        echo "[<img src='/style/icons/like.gif' alt='*' /> <a href='list.php?id=$notes[id]&amp;like=1'>Нравится</a>] [<a href='list.php?id=$notes[id]&amp;like=0'><img src='/style/icons/dlike.gif' alt='*' /></a>]<br />\n";
    } else {
        echo "[<img src='/style/icons/like.gif' alt='*' /> ".$db->query("SELECT COUNT(*) FROM `notes_like` WHERE `like` = '1' AND `id_notes` = '".$notes['id']."' LIMIT 1")->el()."] [<img src='/style/icons/dlike.gif' alt='*' /> ".
$db->query("SELECT COUNT(*) FROM `notes_like` WHERE `like` = '0' AND `id_notes` = '".$notes['id']."' LIMIT 1")->el()."]\n";
    }
    echo "</div>";
} else {
    echo "<div class='main'>";
    echo "[<img src='/style/icons/like.gif' alt='*' /> ".$db->query("SELECT COUNT(*) FROM `notes_like` WHERE `like` = '1' AND `id_notes` = '".$notes['id']."' LIMIT 1")->el()."] [<img src='/style/icons/dlike.gif' alt='*' /> ".
$db->query("SELECT COUNT(*) FROM `notes_like` WHERE `like` = '0' AND `id_notes` = '".$notes['id']."' LIMIT 1")->el()."]\n";
    echo "</div>";
}
//--------------------------В закладки-----------------------------//
if (isset($user)) {
    echo "<div class='main_seriy'>";
    echo "<div class='main'>";
    echo "<img src='/style/icons/fav.gif' alt='*' /> ";
    if ($db->query("SELECT COUNT(*) FROM `mark_notes` WHERE `id_user` = '".$user['id']."' AND `id_list` = '".$notes['id']."' LIMIT 1")==0) {
        echo "<a href='list.php?id=$notes[id]&amp;fav=1'>Добавить в закладки</a><br />\n";
    } else {
        echo "<a href='list.php?id=$notes[id]&amp;fav=0'>Удалить из закладок</a><br />\n";
    }
    echo "В закладках у <a href='list.php?id=$notes[id]&amp;markinfo'>$markinfo</a> чел.";
    echo "</div>";
    echo "</div>";
}
//-------------------------------------------------------------//
echo "<div class='main'>";
echo 'Поделится:<script type="text/javascript" src="//yandex.st/share/share.js" charset="utf-8"></script>
<span class="yashare-auto-init" data-yashareL10n="ru" data-yashareType="none" data-yashareQuickServices="vkontakte,twitter,odnoklassniki,moimir"></span>';
echo "</div>";
/*
===================================
Комментарии дневников
===================================
*/
$k_post=$db->query("SELECT COUNT(*) FROM `notes_komm` WHERE `id_notes` = '".intval($_GET['id'])."'");
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
echo '<div class="foot">';
echo "Комментарии\n";
echo '</div>';
if ($k_post==0) {
    echo '<div class="mess">';
    echo "Нет сообщений\n";
    echo '</div>';
} elseif (isset($user)) {
    /*------------сортировка по времени--------------*/
    if (isset($user)) {
        echo "<div id='comments' class='menus'>";
        echo "<div class='webmenu'>";
        echo "<a href='list.php?id=$notes[id]&amp;page=$page&amp;sort=1' class='".($user['sort']==1?'activ':'')."'>Внизу</a>";
        echo "</div>";
        echo "<div class='webmenu'>";
        echo "<a href='list.php?id=$notes[id]&amp;page=$page&amp;sort=0' class='".($user['sort']==0?'activ':'')."'>Вверху</a>";
        echo "</div>";
        echo "</div>";
    }
    /*---------------alex-borisi---------------------*/
}
$q=$db->query("SELECT * FROM `notes_komm` WHERE `id_notes` = '".intval($_GET['id'])."' ORDER BY `time` $sort LIMIT $start, $set[p_str]");
echo "<table class='post'>\n";
while ($post = $q->row()) {
    $ank=$db->query("SELECT * FROM `user` WHERE `id` = $post[id_user] LIMIT 1")->row();
    /*-----------зебра-----------*/
    if ($num==0) {
        echo '<div class="nav1">';
        $num=1;
    } elseif ($num==1) {
        echo '<div class="nav2">';
        $num=0;
    }
    /*---------------------------*/
    echo group($ank['id'])." <a href='/info.php?id=$ank[id]'>$ank[nick]</a> ";
    if (isset($user) && $ank['id'] != $user['id']) {
        echo "<a href='?id=$notes[id]&amp;response=$ank[id]'>[*]</a> \n";
    }
    echo "".medal($ank['id'])." ".online($ank['id'])." (".vremja($post['time']).")<br />";
    $postBan = $db->query("SELECT COUNT(*) FROM `ban` WHERE (`razdel` = 'all' OR `razdel` = 'notes') AND `post` = '1' AND `id_user` = '$ank[id]' AND (`time` > '$time' OR `navsegda` = '1')");
    if ($postBan == 0) { // Блок сообщения
        echo output_text($post['msg'])."<br />\n";
    } else {
        echo output_text($banMess).'<br />';
    }
    if (isset($user)) {
        echo '<div style="text-align:right;">';
        if ($ank['id']!=$user['id']) {
            echo "<a href=\"?id=$notes[id]&amp;page=$page&amp;spam=$post[id]\"><img src='/style/icons/blicon.gif' alt='*' title='Это спам'></a> ";
        }
        if (isset($user) && (user_access('notes_delete') || $user['id']==$notes['id_user'])) {
            echo '<a href="delete.php?komm='.$post['id'].'"><img src="/style/icons/delete.gif" alt="*"></a>';
        }
    
        echo "</div>\n";
    }
    echo "</div>\n";
}
echo "</table>\n";
if ($k_page>1) {
    str("list.php?id=".intval($_GET['id']).'&amp;', $k_page, $page);
} // Вывод страниц
if ($notes['private_komm']==1 && $user['id']!=$avtor['id'] && $frend!=2  && !user_access('notes_delete')) {
    msg('Комментировать могут только друзья');
    echo "  <div class='foot'>\n";
    echo "<a href='index.php'>Назад</a><br />\n";
    echo "   </div>\n";
    include_once '../../sys/inc/tfoot.php';
    exit;
}
if ($notes['private_komm']==2 && $user['id']!=$avtor['id'] && !user_access('notes_delete')) {
    msg('Пользователь запретил комментирование дневника');
    echo "  <div class='foot'>\n";
    echo "<a href='index.php'>Назад</a><br />\n";
    echo "   </div>\n";
    include_once '../../sys/inc/tfoot.php';
    exit;
}
if (isset($user)) {
    echo "<form method=\"post\" name='message' action=\"?id=".intval($_GET['id'])."&amp;page=$page".$go_link."\">\n";
    if ($set['web'] && is_file(H.'style/themes/'.$set['set_them'].'/altername_post_form.php')) {
        include_once H.'style/themes/'.$set['set_them'].'/altername_post_form.php';
    } else {
        echo "$tPanel<textarea name=\"msg\">$insert</textarea><br />\n";
    }
    echo "<input value=\"Отправить\" type=\"submit\" />\n";
    echo "</form>\n";
}
echo "<div class=\"foot\">\n";
echo "<img src='/style/icons/str2.gif' alt='*'> <a href='index.php'>Дневники</a> | <a href='/info.php?id=$avtor[id]'>$avtor[nick]</a>\n";
echo ' | <b>' . output_text($notes['name']) . '</b>';
echo "</div>\n";
include_once '../../sys/inc/tfoot.php';
