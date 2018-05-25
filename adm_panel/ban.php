<?php
include_once '../sys/inc/start.php';
include_once '../sys/inc/compress.php';
include_once '../sys/inc/sess.php';
include_once '../sys/inc/home.php';
include_once '../sys/inc/settings.php';
include_once '../sys/inc/db_connect.php';
include_once '../sys/inc/ipua.php';
include_once '../sys/inc/fnc.php';
include_once '../sys/inc/adm_check.php';
include_once '../sys/inc/user.php';

if (!user_access('user_ban_set') && !user_access('user_ban_set_h') && !user_access('user_ban_unset')) {
    header("Location: /index.php?".SID);
    exit;
}
if (isset($_GET['id'])) {
    $ank['id']=intval($_GET['id']);
} else {
    header("Location: /index.php?".SID);
    exit;
}
if (!$db->query("SELECT COUNT(*) FROM `user` WHERE `id`=?i",
                [$ank['id']])->el()) {
    header("Location: /index.php?".SID);
    exit;
}
$ank=get_user($ank['id']);
if ($user['level']<=$ank['level']) {
    header("Location: /index.php?".SID);
    exit;
}
$set['title']='Бан пользователя '.$ank['nick'];
include_once '../sys/inc/thead.php';
title();
if (isset($_GET['delete']) && $db->query("SELECT COUNT(*) FROM `ban` WHERE `id_user`=?i AND `id`=?i",
                                         [$ank['id'], $_GET['delete']])->el() && user_access('user_ban_unset')) {
    $ban_info=$db->query("SELECT * FROM `ban` WHERE `id_user`=?i AND `id`=?i",
                                         [$ank['id'], $_GET['delete']])->row();
    $ank2=$db->query("SELECT * FROM `user` WHERE `id`=?i",
                     [$ban_info['id_ban']])->row();
    if (($user['level']>$ank2['level'] || $user['id']==$ank2['id']) || $user['level']==4) {
        $db->query("DELETE FROM `ban` WHERE `id`=?i",
                   [$_GET['delete']]);
        admin_log('Пользователи', 'Бан', "Удаление нарушения с пользователя '[url=/amd_panel/ban.php?id=$ank[id]]$ank[nick][/url]'");
        $_SESSION['message'] = 'Нарушение удалено';
        header("Location: ?id=$ank[id]");
        exit;
    } else {
        $err[]='Нет прав';
    }
}
if (isset($_GET['unset']) && $db->query("SELECT COUNT(*) FROM `ban` WHERE `id_user`=?i AND `id`=?i",
                                        [$ank['id'], $_GET['unset']])->el() && user_access('user_ban_unset')) {
    $ban_info=$db->query("SELECT * FROM `ban` WHERE `id_user`=?i AND `id`=?i",
                                        [$ank['id'], $_GET['unset']])->row();
    $ank2=$db->query("SELECT * FROM `user` WHERE `id`=?i",
                     [$ban_info['id_ban']])->row();
    if (($user['level']>$ank2['level'] || $user['id']==$ank2['id']) || $user['level']==4) {
        $db->query("UPDATE `ban` SET `time`=?i, `navsegda`=?i WHERE `id`=?i",
                   [$time, 0, $_GET['unset']]);
        admin_log('Пользователи', 'Бан', "Снятие бана пользователя '[url=/amd_panel/ban.php?id=$ank[id]]$ank[nick][/url]'");
        $_SESSION['message'] = 'Время бана обнулено';
        header("Location: ?id=$ank[id]");
        exit;
    } else {
        $err[]='Нет прав';
    }
}
if (isset($_POST['ban_pr']) && isset($_POST['time']) && isset($_POST['vremja']) && (user_access('user_ban_set') || user_access('user_ban_set_h'))) {
    $timeban=$time;
    if ($_POST['vremja']=='min') {
        $timeban+=intval($_POST['time'])*60;
    }
    if ($_POST['vremja']=='chas') {
        $timeban+=intval($_POST['time'])*60*60;
    }
    if ($_POST['vremja']=='sut') {
        $timeban+=intval($_POST['time'])*60*60*24;
    }
    if ($_POST['vremja']=='mes') {
        $timeban+=intval($_POST['time'])*60*60*24*30;
    }
    if ($timeban<$time) {
        $err[]='Ошибка времени бана';
    }
    if (!user_access('user_ban_set')) {
        $timeban=min($timeban, $time+3600*24);
    }
    $pochemu=$_POST['pochemu'];
    $razdel=$_POST['razdel'];
    $post=$_POST['post'];
    $navsegda= isset($_POST['navsegda']) ? $_POST['navsegda'] : 0;
    $prich=$_POST['ban_pr'];
    if (strlen2($prich) > 1024) {
        $err[]='Сообщение слишком длинное';
    }
    if (strlen2($prich) < 10) {
        $err[]='Необходимо подробнее указать причину';
    }

    if (!isset($err)) {
        $db->query("INSERT INTO `ban` (`id_user`, `id_ban`, `prich`, `time`, `pochemu`, `razdel`, `post`, `navsegda`)
VALUES (?i, ?i, ?, ?i, ?i, ?, ?i, ?i)",
                   [$ank['id'], $user['id'], $prich, $timeban, $pochemu, $razdel, $post, $navsegda]);
        admin_log('Пользователи', 'Бан', "Бан пользователя '[url=/adm_panel/ban.php?id=$ank[id]]$ank[nick][/url]' до ".vremja($timeban)." по причине '$prich'");
        $_SESSION['message'] = 'Пользователь успешно забанен';
        header("Location: ?id=$ank[id]");
        exit;
    }
}
err();
aut();
$k_post=$db->query("SELECT COUNT(*) FROM `ban` WHERE `id_user`=?i",
                   [$ank['id']])->el();
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
echo "<table class='post'>\n";
if ($k_post==0) {
    echo "<div class='mess'>\n";
    echo "Нет нарушений\n";
    echo "</div>\n";
}
$q=$db->query("SELECT * FROM `ban` WHERE `id_user`=?i ORDER BY `time` DESC LIMIT ?i OFFSET ?i",
              [$ank['id'], $set['p_str'], $start]);
while ($post = $q->row()) {

    if ($num==0) {
        echo "  <div class='nav1'>\n";
        $num=1;
    } elseif ($num==1) {
        echo "  <div class='nav2'>\n";
        $num=0;
    }
    $ank2=$db->query("SELECT * FROM `user` WHERE `id`=?i",
                     [$post['id_ban']])->row();
    if ($set['set_show_icon']==2) {
        avatar($ank2['id']);
    } elseif ($set['set_show_icon']==1) {
        echo status($ank2['id'])." " ;
    }
    echo "<a href='/info.php?id=$ank2[id]'>$ank2[nick]</a> ".online($ank2['id']).": ";
    if ($post['navsegda']==1) {
        echo " бан <font color=red><b>навсегда</b></font><br />";
    } else {
        echo " до " . vremja($post['time']) . "<br />";
    }
    echo '<b>Причина:</b> '.$pBan[$post['pochemu']].'<br />';
    echo '<b>Раздел:</b> '.$rBan[$post['razdel']].'<br />';
    echo '<b>Комментарий:</b> '.output_text($post['prich'])."<br />\n";
    if ($post['time']>$time && user_access('user_ban_unset')) {
        echo "<font color=red><b>Активен</b></font> | <a href='?id=$ank[id]&amp;unset=$post[id]'>Снять бан</a><br />\n";
    }
    echo "<div style='text-align:right;'> <a href='?id=$ank[id]&amp;delete=$post[id]'><img src='/style/icons/delete.gif' alt='*'></a></div>";
    echo "</div>\n";
}
echo "</table>\n";
if ($k_page>1) {
    str('?id='.$ank['id'].'&amp;', $k_page, $page);
} // Вывод страниц
if (user_access('user_ban_set') || user_access('user_ban_set_h')) {
    echo "<form action=\"ban.php?id=$ank[id]&amp;$passgen\" method=\"post\">\n";
    echo "<div class='nav1'>Раздел:</div>";
    if ($user['group_access'] == 12 || $user['level'] > 1) {
        echo "<input name='razdel' type='radio' value='guest'  checked='checked'/>Гостевая <br />";
    }
    if ($user['group_access'] == 11 || $user['level'] > 1) {
        echo "<input name='razdel' type='radio' value='notes'  checked='checked'/>Дневники <br />";
    }
    if ($user['group_access'] == 3 || $user['level'] > 1) {
        echo "<input name='razdel' type='radio' value='forum'  checked='checked'/>Форум <br />";
    }
    if ($user['group_access'] == 4 || $user['level'] > 1) {
        echo "<input name='razdel' type='radio'  value='files'  checked='checked'/>Файлы <br />";
    }
    if ($user['group_access'] == 2 || $user['level'] > 1) {
        echo "<input name='razdel' type='radio'  value='chat'  checked='checked'/>Чат <br />";
    }
    if ($user['group_access'] == 5 || $user['level'] > 1) {
        echo "<input name='razdel' type='radio'  value='lib'  checked='checked'/>Библиотека<br />";
    }
    if ($user['group_access'] == 6 || $user['level'] > 1) {
        echo "<input name='razdel' type='radio'  value='foto'  checked='checked'/>Фотографии<br />";
    }
    if ($user['level'] > 1) {
        echo "<input name='razdel' type='radio' value='all' checked='checked'/>Весь сайт <br />";
    }
    echo "<div class='nav1'>Причина:</div>";
    echo "<input name='pochemu' type='radio' value='1' checked='checked'/>Спам/Реклама<br />";
    echo "<input name='pochemu' type='radio' value='2' />Мошенничество<br />";
    echo "<input name='pochemu' type='radio' value='3' />Нецензурная брань<br />";
    echo "<input name='pochemu' type='radio' value='4' />Клонирование ников<br />";
    echo "<input name='pochemu' type='radio' value='5' />Подстрекательство, провокация и побуждение к агрессии<br />";
    echo "<input name='pochemu' type='radio' value='6' />Флуд<br />";
    echo "<input name='pochemu' type='radio' value='7' />Флейм<br />";
    echo "<input name='pochemu' type='radio' value='0' />Другое<br />";
    echo "<div class='nav1'>Сообщения:</div>";
    echo "<input name='post' type='radio' value='0' checked='checked'/>Показывать <br />";
    echo "<input name='post' type='radio' value='1' />Скрыть<br />";
    echo "<div class='nav1'>Комментарий:</div>\n";
    echo "<textarea name=\"ban_pr\"></textarea><br />\n";
    echo "<div class='nav1'>Время бана ".(user_access('user_ban_set')?null:'(max 1 сутки)').":</div>\n";
    echo "<input type='text' name='time' title='Время бана' value='10' maxlength='11' size='3' />\n";
    echo "<select class='form' name=\"vremja\">\n";
    echo "<option value='min'>Минуты</option>\n";
    echo "<option ".(($k_post > 1)?'selected="selected" ':null)."value='chas'>Часы</option>\n";
    echo "<option value='sut'>Сутки</option>\n";
    echo "<option value='mes'".(user_access('user_ban_set')?null:' disabled="disabled"').">Месяцы</option>\n";
    echo "</select><br />\n";
    echo "<label><input type='checkbox' name='navsegda' value='1' /> Навсегда</label><br />";
    echo "<input type='submit' value='Забанить' />\n";
    echo "</form>\n";
} else {
    echo "<div class='err'>Нет прав для того, чтобы забанить пользователя</div>\n";
}
echo "<div class='foot'>\n";
echo "&raquo;<a href=\"/mail.php?id=$ank[id]\">Написать сообщение</a><br />\n";
echo "&laquo;<a href=\"/info.php?id=$ank[id]\">В анкету</a><br />\n";
if (user_access('adm_panel_show')) {
    echo "&laquo;<a href='/adm_panel/'>В админку</a><br />\n";
}
echo "</div>\n";
include_once '../sys/inc/tfoot.php';
