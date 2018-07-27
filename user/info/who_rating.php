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

$ank['id'] = isset($user) ? $user['id'] : 0;

if (isset($_GET['id'])) {
    $ank['id']=intval($_GET['id']);
}

$ank=get_user($ank['id']);

if (!$ank) {
    header("Location: /index.php?".SID);
    exit;
}

if (isset($_POST['rating']) && isset($user) && isset($_POST['msg']) && $user['id']!=$ank['id'] && $user['rating'] > 1
    && (int)$db->query("SELECT SUM(`rating`) FROM `user_voice2` WHERE `id_kont`=?i",
                       [$user['id']])->el() > (-1)) {
    $msg = trim($_POST['msg']);
    if (mb_strlen($msg)<3) {
        $err='Короткий Отзыв';
    }
    if (mb_strlen($msg)>256) {
        $err='Длиный Отзыв';
    } elseif ($db->query("SELECT COUNT( * ) FROM `user_voice2` WHERE `id_user`=?i AND `msg`=?",
                         [$user['id'], $msg])->el()) {
        $err='Ваш отзыв повторяется';
    }
    if (!isset($err)) {
        $new_r=min(max(@intval($_POST['rating']), -2), 2);
        $db->query("DELETE FROM `user_voice2` WHERE `id_user`=?i AND `id_kont`=?i",
                   [$user['id'], $ank['id']]);
        
        if ($new_r) {
            if (!$db->query("SELECT COUNT( * ) FROM `user_voice2` WHERE `id_user`=?i AND `id_kont`=?i",
                            [$user['id'], $ank['id']])->el()) {
                $db->query("INSERT INTO `user_voice2` (`rating`, `id_user`, `id_kont`, `msg`, `time`) VALUES ( ?i, ?i, ?i, ?, ?i)",
                           [$new_r, $user['id'], $ank['id'], $msg, time()]);
                $db->query("UPDATE `user` SET `rating`=`rating`+?i WHERE `id`=?i",
                           [$new_r, $ank['id']]);
            } else {
                $db->query("UPDATE `user_voice2` SET `rating`=?i, `msg`=?, `time`=?i WHERE `id_user`=?i AND `id_kont`=?i",
                           [$new_r, $msg, time(), $user['id'], $ank['id']]);
            }
        }
        if ($new_r>0) {
            $send = $user['nick'] . ' оставил о Вас [url=/user/info/who_rating.php]положительный отзыв[/url]';
        }
        if ($new_r<0) {
            $send = $user['nick'] . ' оставил о Вас [url=/user/info/who_rating.php]негативный отзыв[/url]';
        }
        if ($new_r==0) {
            $send = $user['nick'] . ' оставил о Вас [url=/user/info/who_rating.php]нейтральный отзыв[/url]';
        }
        $db->query("INSERT INTO `mail` (`id_user`, `id_kont`, `msg`, `time`) VALUES(?i, ?i, ?, ?i)",
                   [0, $ank['id'], $send, time()]);
        $db->query("UPDATE `user` SET `rating_tmp`=`rating_tmp`+1 WHERE `id`=?i",
                   [$user['id']]);
        $_SESSION['message'] = 'Ваше мнение о пользователе успешно изменено';
    }
}
$set['title']=$ank['nick'].' - отзывы '; // заголовок страницы
include_once '../../sys/inc/thead.php';
title();
aut();
err();

if (isset($user)) {
    $ank['id']=$user['id'];
}
if (isset($_GET['id'])) {
    $ank['id']=intval($_GET['id']);
}
if (isset($user) && $user['id']!=$ank['id'] && $user['rating'] > 1 && (int)$db->query("SELECT SUM(`rating`) FROM `user_voice2` WHERE `id_kont`=?i",
                       [$user['id']])->el() > (-1)) {
    echo "<b>Ваше отношение:</b><br />\n";
    // мое отношение к пользователю
    $my_r = (int)($db->query("SELECT `rating` FROM `user_voice2` WHERE `id_user`=?i AND `id_kont`=?i",
                             [$user['id'], $ank['id']])->el());
    echo "<form method='post' action='?id=$ank[id]&amp;$passgen'>\n";
    echo "<select name='rating'>\n";
    echo "<option value='2' ".($my_r==2?'selected="selected"':null).">Замечательное</option>\n";
    echo "<option value='1' ".($my_r==1?'selected="selected"':null).">Положительное</option>\n";
    echo "<option value='0' ".($my_r==0?'selected="selected"':null).">Нейтральное</option>\n";
    echo "<option value='-1' ".($my_r==-1?'selected="selected"':null).">Не очень...</option>\n";
    echo "<option value='-2' ".($my_r==-2?'selected="selected"':null).">Негативное</option>\n";
    echo "</select><br />\n";
    echo "Текст: <br />";
    echo "<textarea name=\"msg\"></textarea><br />";
    echo "<input type='submit' value='GO' />\n";
    echo "</form>\n";
//echo "<br />\n";
} elseif (isset($user) && $user['id'] != $ank['id']) {
    echo "<div class='mess'>";
    echo 'Чтобы оставить отзыв, вам необходимо набрать 2 или более % рейтинга.';
    echo "</div>";
}

$k_post=$db->query("SELECT COUNT( * ) FROM `user_voice2` WHERE `id_kont`=?i",
                   [$ank['id']])->el();
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];

if ($k_post==0) {
    echo '<div class="mess">';
    echo "Нет положительных отзывов\n";
    echo '</div>';
}
$q=$db->query("SELECT * FROM `user_voice2` WHERE `id_kont`=?i ORDER BY `time` DESC LIMIT ?i, ?i",
              [$ank['id'], $start, $set['p_str']]);
while ($post = $q->row()) {
    $ank=get_user($post['id_user']);
    // Лесенка дивов
    if ($num == 0) {
        echo '<div class="nav1">';
        $num = 1;
    } elseif ($num == 1) {
        echo '<div class="nav2">';
        $num = 0;
    }
    echo group($ank['id']) . " <a href='/info.php?id=$ank[id]'>$ank[nick]</a> \n";
    echo "".medal($ank['id'])." ".online($ank['id'])." (".vremja($post['time']).") <br />";
    echo "Отзыв:<br />\n";
    switch ($post['rating']) {
        case 2:
        echo "Замечательный<br />\n";
        break;
        case 1:
        echo "Положительный<br />\n";
        break;
        case 0:
        echo "Нейтральный<br />\n";
        break;
        case -1:
        echo "Не очень...<br />\n";
        break;
        case -2:
        echo "Негативный<br />\n";
        break;
    }
    $msg = htmlspecialchars($post['msg']);
    echo "<br />$msg\n";
    echo '</div>';
}

if ($k_page>1) {
    str('who_rating.php?id='.$ank['id'].'&amp;', $k_page, $page);
} // Вывод страниц

include_once '../../sys/inc/tfoot.php';
