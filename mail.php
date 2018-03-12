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
only_reg();
if ((!isset($_SESSION['refer']) || $_SESSION['refer']==null)
&& isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']!=null &&
!preg_match('#mail\.php#', $_SERVER['HTTP_REFERER'])) {
    $_SESSION['refer']=str_replace('&', '&amp;', preg_replace('#^http://[^/]*/#', '/', $_SERVER['HTTP_REFERER']));
}
if (!isset($_GET['id'])) {
    header("Location: /konts.php?".SID);
    exit;
}
$ank=get_user($_GET['id']);
if (!$ank) {
    header("Location: /konts.php?".SID);
    exit;
}
// помечаем сообщения как прочитанные
$db->query("UPDATE `mail` SET `read` = '1' WHERE `id_kont` = '$user[id]' AND `id_user` = '$ank[id]'");
$set['title']='Почта: '.$ank['nick'];
include_once 'sys/inc/thead.php';
title();
/* Бан пользователя */
if ($user['group_access'] < 1 && $db->query("SELECT COUNT(*) FROM `ban` WHERE `razdel` = 'all' AND `id_user` = '$ank[id]' AND (`time` > '$time' OR `view` = '0')")->el()) {
    $ank=get_user($ank['id']);
    $set['title']=$ank['nick'].' - страничка '; // заголовок страницы
    include_once 'sys/inc/thead.php';
    title();
    aut();
    echo "<div class='nav2'>";
    echo "<b><font color=red>Этот пользователь заблокирован!</font></b><br /> \n";
    echo "</div>\n";
    include_once 'sys/inc/tfoot.php';
    exit;
}
/*
================================
Модуль жалобы на пользователя
и его сообщение либо контент
в зависимости от раздела
================================
*/
if (isset($_GET['spam'])  &&  $ank['id'] != 0) {
    $mess = $db->query("SELECT * FROM `mail` WHERE `id` = '".intval($_GET['spam'])."' limit 1")->row();
    $spamer = get_user($mess['id_user']);
    if (!$db->query("SELECT COUNT(*) FROM `spamus` WHERE `id_user` = '$user[id]' AND `id_spam` = '$spamer[id]' AND `razdel` = 'mail'")->el()) {
        if (isset($_POST['msg'])) {
            if ($mess['id_kont']==$user['id']) {
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
                    $db->query("INSERT INTO `spamus` (`id_user`, `msg`, `id_spam`, `time`, `types`, `razdel`, `spam`) values('$user[id]', '$msg', '$spamer[id]', '$time', '$types', 'mail', '".my_esc($mess['msg'])."')");
                    $_SESSION['message'] = 'Заявка на рассмотрение отправлена';
                    header("Location: ?id=$ank[id]&spam=$mess[id]");
                    exit;
                }
            }
        }
    }
    aut();
    err();
    if (!$db->query("SELECT COUNT(*) FROM `spamus` WHERE `id_user` = '$user[id]' AND `id_spam` = '$spamer[id]' AND `razdel` = 'mail'")->el()) {
        echo "<div class='mess'>Ложная информация может привести к блокировке ника. 
Если вас постоянно достает один человек - пишет всякие гадости, вы можете добавить его в черный список.</div>";
        echo "<form class='nav1' method='post' action='/mail.php?id=$ank[id]&amp;spam=$mess[id]'>\n";
        echo "<b>Пользователь:</b> ";
        echo " ".status($spamer['id'])." <a href=\"/info.php?id=$spamer[id]\">$spamer[nick]</a>\n";
        echo "".medal($spamer['id'])." ".online($spamer['id'])." (".vremja($mess['time']).")<br />";
        echo "<b>Нарушение:</b> <font color='green'>".output_text($mess['msg'])."</font><br />";
        echo "Причина:<br />\n<select name='types'>\n";
        echo "<option value='1' selected='selected'>Спам/Реклама</option>\n";
        echo "<option value='2' selected='selected'>Мошенничество</option>\n";
        echo "<option value='3' selected='selected'>Оскорбление</option>\n";
        echo "<option value='0' selected='selected'>Другое</option>\n";
        echo "</select><br />\n";
        echo "Комментарий:";
        echo $tPanel."<textarea name=\"msg\"></textarea><br />";
        echo "<input value=\"Отправить\" type=\"submit\" />\n";
        echo "</form>\n";
    } else {
        echo "<div class='mess'>Жалоба на <font color='green'>$spamer[nick]</font> будет рассмотрена в ближайшее время.</div>";
    }
    echo "<div class='foot'>\n";
    echo "<img src='/style/icons/str2.gif' alt='*'> <a href='/mail.php?id=$ank[id]'>Назад</a><br />\n";
    echo "</div>\n";
    include_once 'sys/inc/tfoot.php';
}
/*
==================================
The End
==================================
*/
// добавляем в контакты
if ($user['add_konts']==2 && !$db->query("SELECT COUNT(*) FROM `users_konts` WHERE `id_user` = '$user[id]' AND `id_kont` = '$ank[id]'")->el()) {
    $db->query("INSERT INTO `users_konts` (`id_user`, `id_kont`, `time`) VALUES ('$user[id]', '$ank[id]', '$time')");
}
// обновление сведений о контакте
$db->query("UPDATE `users_konts` SET `new_msg` = '0' WHERE `id_kont` = '$ank[id]' AND `id_user` = '$user[id]' LIMIT 1");
    if (isset($_POST['refresh'])) {
        header("Location: /mail.php?id=$ank[id]".SID);
        exit;
    }
if (isset($_POST['msg']) && $ank['id']!=0 && !isset($_GET['spam'])) {
    if ($user['level']==0 && !$db->query("SELECT COUNT(*) FROM `users_konts` WHERE `id_kont` = '$user[id]' AND `id_user` = '$ank[id]'")->el()) {
        if (!isset($_SESSION['captcha'])) {
            $err[]='Ошибка проверочного числа';
        }
        if (!isset($_POST['chislo'])) {
            $err[]='Введите проверочное число';
        } elseif ($_POST['chislo']==null) {
            $err[]='Введите проверочное число';
        } elseif ($_POST['chislo']!=$_SESSION['captcha']) {
            $err[]='Проверьте правильность ввода проверочного числа';
        }
    }
    $msg=$_POST['msg'];
    if (isset($_POST['translit']) && $_POST['translit']==1) {
        $msg=translit($msg);
    }
    if (strlen2($msg)>1024) {
        $err[]='Сообщение превышает 1024 символа';
    }
    if (strlen2($msg)<2) {
        $err[]='Слишком короткое сообщение';
    }
    $mat=antimat($msg);
    if ($mat) {
        $err[]='В тексте сообщения обнаружен мат: '.$mat;
    }
    if (!isset($err) && !$db->query("SELECT COUNT(*) FROM `mail` WHERE `id_user` = '$user[id]' AND `id_kont` = '$ank[id]' AND `time` > '".($time-360)."' AND `msg` = '".my_esc($msg)."'")->el()) {
        // отправка сообщения
        $db->query("INSERT INTO `mail` (`id_user`, `id_kont`, `msg`, `time`) values('$user[id]', '$ank[id]', '".my_esc($msg)."', '$time')");
        // добавляем в контакты
        if ($user['add_konts']==1 && !$db->query("SELECT COUNT(*) FROM `users_konts` WHERE `id_user` = '$user[id]' AND `id_kont` = '$ank[id]'")->el()) {
            $db->query("INSERT INTO `users_konts` (`id_user`, `id_kont`, `time`) VALUES ('$user[id]', '$ank[id]', '$time')");
        }
        // обновление сведений о контакте
        $db->query("UPDATE `users_konts` SET `time` = '$time' WHERE `id_user` = '$user[id]' AND `id_kont` = '$ank[id]' OR `id_user` = '$ank[id]' AND `id_kont` = '$user[id]'");
        $_SESSION['message'] = 'Сообщение успешно отправлено';
        header("Location: ?id=$ank[id]");
        exit;
    }
}
if (isset($_GET['delete'])  && $_GET['delete']!='add') {
    $mess = $db->query("SELECT * FROM `mail` WHERE `id` = '".intval($_GET['delete'])."' limit 1")->row();
    if ($mess['id_user']==$user['id'] || $mess['id_kont']==$user['id']) {
        if ($mess['unlink']!=$user['id'] && $mess['unlink']!=0) {
            $db->query("DELETE FROM `mail` WHERE `id` = '".$mess['id']."'");
        } else {
            $db->query("UPDATE `mail` SET `unlink` = '$user[id]' WHERE `id` = '$mess[id]' LIMIT 1");
        }
        $_SESSION['message'] = 'Сообщение удалено';
        header("Location: ?id=$ank[id]");
        exit;
    }
}
if (isset($_GET['delete']) && $_GET['delete']=='add') {
    $db->query("DELETE FROM `mail` WHERE `unlink` = '$ank[id]'  AND `id_user` = '$user[id]' AND `id_kont` = '$ank[id]' OR `id_user` = '$ank[id]' AND `id_kont` = '$user[id]' AND `unlink` = '$ank[id]'  ");
    $db->query("UPDATE `mail` SET `unlink` = '$user[id]' WHERE  `id_user` = '$user[id]' AND `id_kont` = '$ank[id]' OR `id_user` = '$ank[id]' AND `id_kont` = '$user[id]'");
    $_SESSION['message'] = 'Сообщения удалены';
    header("Location: ?id=$ank[id]");
    exit;
}
aut();
err();
/*
==================================
Приватность почты пользователя
==================================
*/
    $block = true;
    $uSet = $db->query("SELECT * FROM `user_set` WHERE `id_user` = '$ank[id]'  LIMIT 1")->row();
    $frend=$db->query("SELECT COUNT(*) FROM `frends` WHERE (`user` = '$user[id]' AND `frend` = '$ank[id]') OR (`user` = '$ank[id]' AND `frend` = '$user[id]') LIMIT 1")->el();
    $frend_new=$db->query("SELECT COUNT(*) FROM `frends_new` WHERE (`user` = '$user[id]' AND `to` = '$ank[id]') OR (`user` = '$ank[id]' AND `to` = '$user[id]') LIMIT 1")->el();
if ($user['group_access'] == 0) {
    if ($uSet['privat_mail'] == 2 && $frend != 2) { // Если только для друзей
        echo '<div class="mess">';
        echo 'Писать сообщения пользователю, могут только его друзья!';
        echo '</div>';
        
        
        echo '<div class="nav1">';
        if ($frend_new == 0 && $frend==0) {
            echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?add=".$ank['id']."'>Добавить в друзья</a><br />\n";
        } elseif ($frend_new == 1) {
            echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?otm=$ank[id]'>Отклонить заявку</a><br />\n";
        } elseif ($frend == 2) {
            echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?del=$ank[id]'>Удалить из друзей</a><br />\n";
        }
        echo "</div>";
        
        $block = false;
    }
    
    if ($uSet['privat_mail'] == 0) { // Если закрыта
        echo '<div class="mess">';
        echo 'Пользователь запретил писать ему сообщения!';
        echo '</div>';
        
        $block = false;
    }
}
echo "<div class='nav2'>";
echo "Переписка с ".group($ank['id'])."
 <a href='/id".$ank['id']."'>".$ank['nick']."</a> ".medal($ank['id']). online($ank['id'])." <span style='float:right;'>";
if ($db->query("SELECT COUNT(*) FROM `users_konts` WHERE `id_user` = '$user[id]' AND `id_kont` = '$ank[id]'")->el()) {
    $kont=$db->query("SELECT * FROM `users_konts` WHERE `id_user` = '$user[id]' AND `id_kont` = '$ank[id]'")->row();
    echo "<a href='/konts.php?type=$kont[type]&amp;act=del&amp;id=$ank[id]'><img src='/style/icons/cross_r.gif' alt='*'></a></span><br/></div>\n";
} else {
    echo "<a href='/konts.php?type=common&amp;act=add&amp;id=$ank[id]'><img src='/style/icons/lj.gif' alt='*'> Добавить в контакты</a></span><br/></div>\n";
}
$rt=time()-600;
if ($ank['date_last']<$rt) {
    echo "<div class='plug'>";
    echo "Пользователь ".$ank['nick']." не в сети. Оставьте свое сообщение и он прочтет его позже.";
    echo "</div>";
}
if ($ank['id']!=0 && $block == true) {
    echo "<form method='post' name='message' action='/mail.php?id=$ank[id]'>\n";
    if ($set['web'] && is_file(H.'style/themes/'.$set['set_them'].'/altername_post_form.php')) {
        include_once H.'style/themes/'.$set['set_them'].'/altername_post_form.php';
    } else {
        echo $tPanel."<textarea name='msg'></textarea><br />\n";
    }
    if ($user['level']==0 && !$db->query("SELECT COUNT(*) FROM `users_konts` WHERE `id_kont` = '$user[id]' AND `id_user` = '$ank[id]'")->el()) {
        echo "<img src='/captcha.php?SESS=$sess' width='100' height='30' alt='Проверочное число' /><br />\n<input name='chislo' size='5' maxlength='5' value='' type='text' /><br/>\n";
    }
    echo "<input type='submit' name='send' value='Отправить' />\n";
    echo "<input type='submit' name='refresh' value='Обновить' />";
    echo "</form>";
    if ($db->query("SELECT COUNT(*) FROM `users_konts` WHERE `id_user` = '$user[id]' AND `id_kont` = '$ank[id]'")->el()) {
        $kont=$db->query("SELECT * FROM `users_konts` WHERE `id_user` = '$user[id]' AND `id_kont` = '$ank[id]'")->row();
        echo "<div class='foot'><img src='/style/icons/str.gif' alt='*'>  <a href='/konts.php?type=$kont[type]&amp;act=del&amp;id=$ank[id]'>Удалить контакт из списка</a></div>\n";
    } else {
        echo "<div class='foot'><img src='/style/icons/str.gif' alt='*'> 
	<a href='/konts.php?type=common&amp;act=add&amp;id=$ank[id]'>Добавить в список контактов</a></div>\n";
    }
}
echo "<div class='foot'><img src='/style/icons/str.gif' alt='*'> 
	<a href='/konts.php?".(isset($kont)?'type='.$kont['type']:null)."'>Все контакты</a></div>\n";
echo "<table class='post'>\n";
$k_post=$db->query("SELECT COUNT(*) FROM `mail` WHERE `unlink` != '$user[id]' AND `id_user` = '$user[id]' AND `id_kont` = '$ank[id]' OR `id_user` = '$ank[id]' AND `id_kont` = '$user[id]' AND  `unlink` != '$user[id]'")->el();
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
if ($k_post==0) {
    echo "  <div class='mess'>\n";
    echo "Нет сообщений\n";
    echo "  </div>\n";
}
$num=0;
$q=$db->query("SELECT * FROM `mail` WHERE `unlink` != '$user[id]' AND `id_user` = '$user[id]' AND `id_kont` = '$ank[id]' OR `id_user` = '$ank[id]' AND `id_kont` = '$user[id]' AND `unlink` != '$user[id]' ORDER BY id DESC LIMIT $start, $set[p_str]");
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
    $ank2=get_user($post['id_user']);
    if ($set['set_show_icon']==2) {
        avatar($ank2['id']);
    } elseif ($set['set_show_icon']==1) {
        //echo "".status($ank2['id'])."";
    }
    if ($ank2 && $ank2['id']) {
        if ($ank2['id']==$user['id']) {
            echo ' <b><span style="color:green">От меня</span> к </b><a href="/id'.$ank2['id'].'"><b>'.$ank['nick'].'</b></a>';
        } else {
            echo " ".group($ank2['id'])." <a href=\"/info.php?id=$ank2[id]\">$ank2[nick]</a>\n";
            echo "".medal($ank2['id'])." ".online($ank2['id'])." ";
        }
    } elseif ($ank2['id']==0) {
        echo "<b>Система</b>\n";
    } else {
        echo "[Удален!]\n";
    }
    echo '<span style="float:right;color:#666;font-size:small;"> '.vremja($post['time']).'</span> ';
    if ($post['read']==0) {
        echo "(не прочитано)<br />\n";
    }
    echo "<br/>".output_text($post['msg'])."\n";
    echo "<div style='text-align:right;'>";
    if ($ank2['id']!=$user['id']) {
        echo "<a href=\"mail.php?id=$ank[id]&amp;page=$page&amp;spam=$post[id]\"><img src='/style/icons/blicon.gif' alt='*' title='Это спам'> Спам!</a>";
    }
    echo "<a href=\"mail.php?id=$ank[id]&amp;page=$page&amp;delete=$post[id]\"><img src='/style/icons/delete.gif' alt='*' title='Удалить это сообщение'> Удалить</a>\n";
    echo "   </div>\n";
    echo "   </div>\n";
}
echo "</table>\n";
if ($k_page>1) {
    str("mail.php?id=$ank[id]&amp;", $k_page, $page);
} // Вывод страниц
echo "<div class='foot'>\n";
echo "<img src='/style/icons/str.gif' alt='*'> <a href='mail.php?id=$ank[id]&amp;page=$page&amp;delete=add'>Очистить почту</a><br />\n";
echo "</div>\n";

include_once 'sys/inc/tfoot.php';
