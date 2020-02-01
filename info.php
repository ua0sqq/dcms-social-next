<?php
include_once 'sys/inc/start.php';
include_once 'sys/inc/compress.php';
include_once 'sys/inc/sess.php';
include_once 'sys/inc/settings.php';
include_once 'sys/inc/db_connect.php';
include_once 'sys/inc/ipua.php';
include_once 'sys/inc/fnc.php';
include_once 'sys/inc/user.php';

$get_id = filter_input_array(INPUT_GET, FILTER_VALIDATE_INT);

if (isset($user)) {
    $ank['id'] = $user['id'];
}
if (isset($get_id['id'])) {
    $ank['id'] = $get_id['id'];
}
if (!isset($ank['id'])) {
    header('Location: /index.php?' . SID);
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
    $db->query(
        'SELECT COUNT(*) FROM `ban` WHERE `razdel`=? AND `id_user`=?i AND (`time`>?i OR `navsegda`=?i)',
                ['all', $ank['id'], $time, 1])->el()) {
    $set['title'] = $ank['nick'].' - страничка '; // заголовок страницы
    include_once 'sys/inc/thead.php';
    title();
    aut();

    echo '<div class="mess">'."\n";
    echo '<p style="text-align:center;color:red;"><b>Этот пользователь заблокирован!</b></p>'."\n";
    echo '</div>'."\n";

    include_once 'sys/inc/tfoot.php';
    exit;
}
// Удаление комментариев
if (isset($get_id['delete_post']) && $post_id = $db->query("SELECT `id` FROM `stena` WHERE `id`=?i",
                                                [$get_id['delete_post']])->el()) {
    if (user_access('guest_delete') || $ank['id'] == $user['id']) {
        $db->query("DELETE FROM `stena` WHERE `id`=?i", [$post_id]);
        $db->query("DELETE FROM `stena_like` WHERE `id_stena`=?i", [$post_id]);

        $_SESSION['message'] = 'Сообщение успешно удалено';
    }
}

// гости
if (isset($user) && $user['id'] != $ank['id'] && !isset($_SESSION['guest_' . $ank['id']])) {
    if (!$db->query("SELECT COUNT(*) FROM `my_guests` WHERE `id_ank`=?i AND `id_user`=?i",
                    [$ank['id'], $user['id']])->el()) {
        $db->query("INSERT INTO `my_guests` (`id_ank`, `id_user`, `time`) VALUES (?i, ?i, ?i)",
                   [$ank['id'], $user['id'], $time]);
        $db->query("UPDATE `user` SET `balls`=`balls`+1, `rating_tmp`=`rating_tmp`+1 WHERE `id`=?i",
                   [$ank['id']]);
        $_SESSION['guest_' . $ank['id']] = 1;
    } elseif (!isset($_SESSION['guest_' . $ank['id']])) {
        $guest_id = $db->query("SELECT `id` FROM `my_guests` WHERE `id_ank`=?i AND `id_user`=?i LIMIT ?i",
                            [$ank['id'], $user['id'], 1])->el();
        $db->query("UPDATE `my_guests` SET  `time`=?i, `read`=? WHERE `id`=?i",
                   [$time, '1', $guest_id]);
        $db->query("UPDATE `user` SET `rating_tmp`=`rating_tmp`+1 WHERE `id`=?i",
                   [$ank['id']]);
        $_SESSION['guest_' . $ank['id']] = 1;
    }
}

// стена
if (isset($user) && isset($get_id['wall']) && $get_id['wall']==1) {
    $db->query("UPDATE `user` SET `wall`=?i WHERE `id`=?i", [1, $user['id']]);
    header("Location: /info.php?id=$ank[id]");
} elseif (isset($user) && isset($get_id['wall']) && $get_id['wall']==0) {
    $db->query("UPDATE `user` SET `wall`=?i WHERE `id`=?i", [0, $user['id']]);
    header("Location: /info.php?id=$ank[id]");
}

if (isset($user)) {
    $db->query("UPDATE `notification` SET `read` = ?i WHERE `type` = ? AND `id_user` = ?i AND `id_object` = ?i",
               [1, 'stena_komm', $user['id'], $ank['id']]);
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
        $err[]='Сообщение слишком длинное';
    } elseif (strlen2($msg)<2) {
        $err[]='Короткое сообщение';
    } elseif ($db->query("SELECT COUNT(*) FROM `stena` WHERE `id_user` = ?i AND  `id_stena` = ?i AND `msg` = ?",
                         [$user['id'], $ank['id'], $msg])->el()) {
        $err='Ваше сообщение повторяет предыдущее';
    } elseif (!isset($err)) {
        // Уведомления об ответах
        if (isset($user) && $respons==true) {
            if ($ank_reply['id'] != $user['id'] && $db->query("SELECT `komm` FROM `notification_set` WHERE `id_user`=?i LIMIT ?i",
                           [$ank_reply['id'], 1])->el()) {
                $db->query("INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES (?i, ?i, ?i, ?, ?i)",
                           [$user['id'], $ank_reply['id'], $ank['id'], 'stena_komm', $time]);
            }
        }
        $db->query("INSERT INTO `stena` (id_user, time, msg, id_stena) VALUES(?i, ?i, ?, ?i)",
                   [$user['id'], $time, $msg, $ank['id']]);
        $db->query("UPDATE `user` SET `balls`=`balls`+?i, `rating_tmp`=`rating_tmp`+?i WHERE `id` = ?i",
                   [1, 1, $user['id']]);
        $_SESSION['message'] = 'Сообщение успешно добавлено';
        if (isset($user)) {
            if ($user['id'] != $ank['id'] && $db->query("SELECT `komm` FROM `notification_set` WHERE `id_user`=?i LIMIT ?i",
                           [$ank['id'], 1])->el()) {
                $db->query("INSERT INTO `notification` (`avtor`, `id_user`, `type`, `time`) VALUES (?i, ?i, ?, ?i)",
                           [$user['id'], $ank['id'], 'stena', $time]);
            }
        }
    }
}

// rating
if ((!isset($_SESSION['refer']) || $_SESSION['refer']==null) && isset($_SERVER['HTTP_REFERER'])
    && $_SERVER['HTTP_REFERER']!=null && !preg_match('#info\.php#', $_SERVER['HTTP_REFERER'])) {
    $_SESSION['refer']=str_replace('&', '&amp;', preg_replace('#^http://[^/]*/#', '/', $_SERVER['HTTP_REFERER']));
}
if (isset($_POST['rating']) && isset($user)  && $user['id']!=$ank['id'] && $user['balls']>=50
    && $db->query("SELECT SUM(`rating`) FROM `user_voice2` WHERE `id_kont`=?i", [$user['id']])->el() >= 0) {
    $new_r=min(max(@intval($_POST['rating']), -2), 2);
    $db->query("DELETE FROM `user_voice2` WHERE `id_user`=?i AND `id_kont`=?i LIMIT ?i",
               [$user['id'], $ank['id'], 1]);
    if ($new_r) {
        $db->query("INSERT INTO `user_voice2` (`rating`, `id_user`, `id_kont`) VALUES ( ?i, ?i, ?i)",
                   [$new_r, $user['id'], $ank['id']]);
    }
    $ank['rating']=intval($db->query("SELECT SUM(`rating`) FROM `user_voice2` WHERE `id_kont`=?i", [$ank['id']])->el());
    $db->query("UPDATE `user` SET `rating`=?i WHERE `id`=?i",
               [$ank['rating'], $ank['id']]);
    if ($new_r > 0) {
        $send = ' оставил положительный отзыв в [url=/who_rating.php]Вашей анкете[/url]';
    }
    if ($new_r < 0) {
        $send = ' оставил негативный отзыв в [url=/who_rating.php]Вашей анкете[/url]';
    }
    if ($new_r == 0) {
        $send = ' оставил нейтральный отзыв в [url=/who_rating.php]Вашей анкете[/url]';
    }
    $db->query("INSERT INTO `mail` (`id_user`, `id_kont`, `msg`, `time`) VALUES(?i, ?i, ?, ?i)",
                   [0, $ank['id'], $user['nick'] . $send, $time]);
    msg('Ваше мнение о пользователе успешно изменено');
}

// статус запись
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
    } elseif ($db->query("SELECT COUNT(*) FROM `status` WHERE `id_user`=?i AND `msg`=?",
                         [$user['id'], $msg])->el()) {
        $err='Ваше сообщение повторяет предыдущее';
    } elseif (!isset($err)) {
        $db->query("UPDATE `status` SET `pokaz`=?i WHERE `id_user`=?i",
                   [0, $user['id']]);
        $db->query("INSERT INTO `status` (`id_user`, `time`, `msg`, `pokaz`) VALUES(?i, ?i, ?, ?i)",
                   [$user['id'], $time, $msg, 1]);
        $status=$db->query("SELECT * FROM `status` WHERE `id_user`=?i AND `pokaz`=?i LIMIT ?i",
                           [$ank['id'], 1, 1])->row();
        // Лента
        $q = $db->query("SELECT fr.user, fr.lenta_status, ts.lenta_status as ts_status FROM `frends` fr
JOIN tape_set ts ON ts.id_user=fr.user
WHERE fr.`frend`=?i AND fr.`lenta_status`=?i AND ts.`lenta_status`=?i AND `i`=?i",
                        [$user['id'], 1, 1, 1]);
        while ($frend = $q->row()) {
            $db->query("INSERT INTO `tape` (`id_user`,`ot_kogo`,  `avtor`, `type`, `time`, `id_file`) VALUES(?i, ?i, ?i, ?, ?i, ?i)",
                           [$frend['user'], $user['id'], $status['id_user'], 'status', $time, $status['id']]);
        }

        $_SESSION['message'] = 'Статус добавлен';
        header("Location: ?id=$ank[id]");
        exit;
    }
}
if (isset($get_id['off']) && $ank['id'] == $user['id']) {
        $db->query("UPDATE `status` SET `pokaz`=?i WHERE `id_user`=?i",
                   [0, $user['id']]);
        $_SESSION['message'] = 'Статус отключен';
        header("Location: ?id=$ank[id]");
        exit;
}
//-------------------------------------//
// Статус пользователя
$status=$db->query("SELECT * FROM `status` WHERE `id_user`=?i AND `pokaz`=?i LIMIT ?i",
                   [$ank['id'], 1, 1])->row();
if (empty($status)) {
    $status = ['id' => 0];
}
/* Класс к статусу */
if (isset($get_id['like']) && $user['id']!=$ank['id'] &&
    !$db->query("SELECT COUNT(*) FROM `status_like` WHERE `id_status`=?i AND `id_user`=?i",
                [$status['id'], $user['id']])->el()) {
    $db->query("INSERT INTO `status_like` (`id_user`, `time`, `id_status`) values(?i, ?i, ?i)",
               [$user['id'], $time, $status['id']]);
    // Лента
        $q = $db->query("SELECT fr.user, fr.lenta_status_like, ts.lenta_status_like as ts_status FROM `frends` fr
JOIN tape_set ts ON ts.id_user=fr.user
WHERE fr.`frend`=?i AND fr.`lenta_status_like`=?i AND ts.`lenta_status_like`=?i AND `i`=?i",
                        [$user['id'], 1, 1, 1]);
    while ($frend = $q->row()) {
            $db->query("INSERT INTO `tape` (`id_user`,`ot_kogo`,  `avtor`, `type`, `time`, `id_file`) VALUES(?i, ?i, ?i, ?, ?i, ?i)",
                       [$frend['user'], $user['id'], $status['id_user'], 'status_like', $time, $status['id']]);
    }
    // Конец
    header("Location: ?id=$ank[id]");
    exit;
}

// добавляем в закладки
if (isset($get_id['fav']) && isset($user)) {
    if ($get_id['fav'] == 1 && !$db->query(
            'SELECT COUNT(*) FROM `bookmarks` WHERE `id_user`=?i AND `id_object`=?i AND `type`=?',
                    [$user['id'], $ank['id'], 'people'])->el()) {
        $db->query("INSERT INTO `bookmarks` (`id_object`, `id_user`, `time`,`type`) VALUES (?i, ?i, ?i, ?)",
                   [$ank['id'], $user['id'], $time, 'people']);
        $_SESSION['message'] = $ank['nick'] . ' добавлен в закладки';
    }
    if ($get_id['fav'] == 0 && $db->query(
            'SELECT COUNT(*) FROM `bookmarks` WHERE `id_user`=?i AND `id_object`=?i AND `type`=?',
                    [$user['id'], $ank['id'], 'people'])->el()) {
        $db->query(
            'DELETE FROM `bookmarks` WHERE `id_user`=?i AND  `id_object`=?i AND `type`=?',
                   [$user['id'], $ank['id'], 'people']);
        $_SESSION['message'] = $ank['nick'] . ' удален из закладок';
    }

    header('Location: /info.php?id=' . $ank['id']);
    exit;
}

// статус like
if (isset($user) && isset($get_id['like']) && ($get_id['like']==0 || $get_id['like']==1) && $user['id']!=$ank['id']
    && !$db->query("SELECT COUNT(*) FROM `status_like` WHERE `id_user`=?i AND `id_status`=?i",
                [$user['id'], $status['id']])->el()) {
    $db->query("INSERT INTO `status_like` (`id_user`, `id_status`, `like`) VALUES (?i, ?i, ?i)",
               [$user['id'], $status['id'], $get_id['like']]);
    $db->query("UPDATE `user` SET `balls`=`balls`+3, `rating_tmp`=`rating_tmp`+3 WHERE `id`=?i",
               [$ank['id']]);
}

/*
================================
Модуль жалобы на пользователя
и его сообщение либо контент
в зависимости от раздела
================================
*/
if (isset($get_id['spam'])  && $ank['id']!=0 && isset($user)) {
    $mess = $db->query("SELECT stn.*, u.id AS id_user, u.nick FROM `stena` stn
JOIN `user` u ON u.id=stn.id_user WHERE stn.`id`=?i",
                       [$get_id['spam']])->row();

    if (!$db->query("SELECT COUNT( * ) FROM `spamus` WHERE `id_user`=?i AND `id_spam`=?i AND `razdel`=?",
                    [$user['id'], $mess['id_user'], 'stena'])->el()) {
        if (isset($_POST['spamus'])) {
            if ($mess['id_user']!=$user['id']) {
                $msg=trim($_POST['spamus']);
                if (strlen2($msg)<3) {
                    $err='Укажите подробнее причину жалобы';
                }
                if (strlen2($msg)>1512) {
                    $err='Длина текста превышает предел в 512 символов';
                }
                if (isset($_POST['types'])) {
                    $types = intval($_POST['types']);
                } else {
                    $types = 0;
                }
                if (!isset($err)) {
                    $db->query(
                        'INSERT INTO `spamus` (`id_object`, `id_user`, `msg`, `id_spam`, `time`, `types`, `razdel`, `spam`) VALUES(?i, ?i, ?, ?i, ?i, ?i, ?, ?)',
                                [$ank['id'], $user['id'], $msg, $mess['id_user'], $time, $types, 'stena', $mess['msg']]);

                    $_SESSION['message'] = 'Заявка на рассмотрение отправлена';
                    header("Location: ?id=$ank[id]&spam=$mess[id]&page=".intval($get_id['page'])."");
                    exit;
                }
            }
        }
    }
    // заголовок страницы
    $set['title']=$ank['nick'].' - жалоба ';
    include_once 'sys/inc/thead.php';
    title();
    aut();
    err();

    if (!$db->query(
            'SELECT COUNT(*) FROM `spamus` WHERE `id_user`=?i AND `id_spam`=?i AND `razdel`=?',
                    [$user['id'], $mess['id_user'], 'stena'])->el()) {
        echo "<div class='mess'>\n Ложная информация может привести к блокировке ника.
Если вас постоянно достает один человек - пишет всякие гадости, вы можете добавить его в черный список.\n</div>\n";
        echo "<form class='nav1' method='post' action='/info.php?id=$ank[id]&amp;spam=$mess[id]&amp;page=".intval($get_id['page'])."'>\n";
        echo "<b>Пользователь:</b><br />";
        echo " ".avatar($mess['id_user'])." <a href=\"/info.php?id=" . $mess['id_user'] . "\">$mess[nick]</a>\n";
        echo "".medal($mess['id_user'])." ".online($mess['id_user'])." (".vremja($mess['time']).")<br />";
        if ($mess['msg']) { // TODO: что за хрень?
            echo "<b>Нарушение:</b> <font color='green'>".output_text($mess['msg'])."</font><br />";
        }
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
        echo "<div class='mess'>Жалоба на <font color='green'>$mess[nick]</font> будет рассмотрена в ближайшее время.</div>";
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
*/
$set['title']=$ank['nick'].' - страничка '; // заголовок страницы
include_once 'sys/inc/thead.php';
title();
aut();
/*
==================================
Приватность станички пользователя
==================================
*/
$pattern = 'SELECT ust.privat_str FROM `user_set` ust WHERE ust.`id_user`=?i';
$data = [$ank['id']];
if (isset($user)) {
    $pattern = 'SELECT ust.privat_str, (
SELECT COUNT(*) FROM `frends` WHERE (`user`=?i AND `frend`=ust.`id_user`) OR (`user`=ust.`id_user` AND `frend`=?i)) frend, (
SELECT COUNT(*) FROM `frends_new` WHERE (`user`=?i AND `to`=ust.`id_user`) OR (`user`=ust.`id_user` AND `to`=?i)) new_frend
FROM `user_set` ust WHERE ust.`id_user`=?i';
    $data = [$user['id'], $user['id'], $user['id'], $user['id'], $ank['id']];
}

$uSet = $db->query($pattern, $data)->row();

if (!isset($user) || ($ank['id'] != $user['id'] && $user['group_access'] == 0)) {
    if (($uSet['privat_str'] == 2 && (isset($user) && $uSet['frend'] != 2)) || $uSet['privat_str'] == 0) { // Начинаем вывод если стр имеет приват настройки
        if ($ank['group_access']>1) {
            echo "<div class='err'>$ank[group_name]</div>\n";
        }
        echo "<div class='nav1'>\n";
        echo group($ank['id'])." $ank[nick] ";
        echo medal($ank['id'])." ".online($ank['id'])." ";
        echo "</div>\n";

        echo "<div class='nav2'>\n";
        echo avatar($ank['id'], true, 128, false);
        //echo "<br />";
    }

    // Если только для друзей
    if ((!isset($user) && $uSet['privat_str'] == 2)  || (isset($user) && $uSet['privat_str'] == 2 && $uSet['frend'] != 2)) {
        echo '<div class="mess">'."\n";
        echo 'Просматривать страничку пользователя могут только его друзья!'."\n";
        echo '</div>'."\n";

        // В друзья
        if (isset($user)) {
            echo '<div class="nav1">'."\n";
            if ($uSet['frend_new'] == 0 && $uSet['frend']==0) {
                echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?add=".$ank['id']."'>Добавить в друзья</a><br />\n";
            } elseif ($uSet['frend_new'] == 1) {
                echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?otm=$ank[id]'>Отклонить заявку</a><br />\n";
            } elseif ($uSet['frend'] == 2) {
                echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?del=$ank[id]'>Удалить из друзей</a><br />\n";
            }
            echo "</div>\n";
        }
        include_once 'sys/inc/tfoot.php';
        exit;
    }

    if ($uSet['privat_str'] == 0) { // Если закрыта
        echo '<div class="mess">'."\n";
        echo 'Пользователь запретил просматривать его страничку!'."\n";
        echo '</div>'."\n";

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
