<?php
echo "\n".'<!-- /web.php -->'."\n";
// в друзья
if (!isset($user)) {
    $cnt = ['frend_new' => 0, 'frend' => 0];
} else {
$cnt = $db->query('SELECT * FROM (
SELECT COUNT( * ) frend_new FROM `frends_new` WHERE (`user`=?i AND `to`=?i) OR (`user`=?i AND `to`=?i)) q1, (
SELECT COUNT( * ) frend FROM `frends` WHERE (`user`=?i AND `frend`=?i) OR (`user`=?i AND `frend`=?i)) q2',
[$user['id'], $ank['id'], $ank['id'], $user['id'], $user['id'], $ank['id'], $ank['id'], $user['id']])->row();
}

if (isset($user) && $user['id'] != $ank['id']) {
    if (isset($_GET['fok'])) {
        echo '<center>';
        echo "<div class='foot'><form action='/info.php?id=".$ank['id']."' method=\"post\">";
        echo "<input class=\"submit\" type=\"submit\" value=\"Закрыть\" />\n";
        echo "</form></div>";
        echo '</center>';
    }
}
if (isset($user) && isset($_GET['frends'])  && $cnt['frend_new'] == 0 && $cnt['frend'] == 0) {
    if ($user['id'] != $ank['id']) {
        echo '<center>';
        echo "<div class='err'>Пользователь должен будет подтвердить, что вы друзья.</div><div class='foot'><form action='/user/frends/create.php?add=".$ank['id']."' method=\"post\">";
        echo "<input class=\"submit\" type=\"submit\" value=\"Пригласить\" />\n";
        echo " <a href='/info.php?id=$ank[id]'>Отмена</a><br />\n";
        echo "</form></div>";
        echo '</center>';
    }
}

 // Должность на сайте
if ($ank['group_access']>1) {
    echo "<div class='err'>$ank[group_name]</div>\n";
}

// друзья онлайн
$online_frend = $db->query("SELECT COUNT( * ) FROM `frends` f
JOIN `user` u ON `f`.`frend`=`u`.`id` WHERE `f`.`user`=?i AND `f`.`i`=1 AND `u`.`date_last`>?i",
                [$ank['id'], TIME_600])->el();

$private_photo = false;
if (isset($user) && $user['id'] != $ank['id']) {
    if ($cnt['frend'] == 2) {
        $private_photo = true;
    }
}

if (isset($user) && $user['id'] == $ank['id']) {
$query = ', (
SELECT COUNT( * ) new_frend FROM `frends_new` WHERE `to`=' . $ank['id'] . ') q3';
} else {
    $query = '';
}

$cnt3 = $db->query('SELECT * FROM (
SELECT COUNT( * ) user_voice FROM `user_voice2` WHERE `id_kont`=?i) q1, (
SELECT COUNT( * ) all_frend FROM `frends` WHERE `user`=?i AND `i`=1) q2?q',
                   [$ank['id'], $ank['id'], $query])->row();

                       $foto_sql = ' AND `id_gallery` IN (
SELECT `id` FROM `gallery` WHERE `privat`="0")';
    if (isset($user)) {
        if ($user['id'] == $ank['id']) {
            $foto_sql = null;
        } elseif ($private_photo && $user['id'] <> $ank['id']) {
            $foto_sql = ' AND `id_gallery` IN (
SELECT `id` FROM `gallery` WHERE `privat`<"2")';
        }
    }
$cnt4 = $db->query('SELECT (
SELECT COUNT( * ) FROM `gallery_foto` WHERE `id_user`=?i;?q) cnt_foto, (
SELECT COUNT( * ) FROM `user_files` WHERE `id_user`=?i AND `osn`>1) cnt_user_files, (
SELECT COUNT( * ) FROM `obmennik_files` WHERE `id_user`=?i) obmen_files, (
SELECT COUNT( * ) FROM `user_music` WHERE `id_user`=?i) cnt_user_music, (
SELECT COUNT( * ) FROM `notes` WHERE `id_user`=?i) cnt_note, (
SELECT COUNT( * ) FROM `bookmarks` WHERE `id_user`=?i) cnt_marks, (
SELECT COUNT( * ) FROM `gifts_user` WHERE `id_user`=?i AND `status`=1) cnt_gift, (
SELECT `id` FROM `user_files` WHERE `id_user`=?i AND `osn`=1) id_foto_osn',
                   [$ank['id'], $foto_sql, $ank['id'], $ank['id'], $ank['id'], $ank['id'], $ank['id'], $ank['id'], $ank['id']])->row();

if (!$cnt4['id_foto_osn']) {
    $cnt4['id_foto_osn'] = $db->query("INSERT INTO `user_files` (`id_user`, `name`,  `osn`) VALUES(?i, ?, ?i)",
               [$ank['id'], 'Файлы', 1])->id();
}

?>
<nav style="margin-top: -10px;">
    <ul class="gorisontal">
        <li><a href="/user/frends/?id=<?php echo $ank['id'];?>">Друзья (<?php echo $cnt3['all_frend'] . '/' . $online_frend;
if (isset($user) && $ank['id'] == $user['id'] && $cnt3['new_frend'] ) {
    echo ' <span class="off">+' . $cnt3['new_frend'] . '</span>';
}        ?>)</a></li>
        <li><a href="/foto/<?php echo $ank['id'];?>/">Альбом (<?php echo $cnt4['cnt_foto'];?>)</a></li>
        <li><a href="/user/personalfiles/<?php echo $ank['id'] . '/' . $cnt4['id_foto_osn'];?>/">Файлы (<?php
        echo $cnt4['cnt_user_files'] . '/' . $cnt4['obmen_files'];?>)</a></li>
        <li><a href="/user/info/them_p.php?id=<?php echo $ank['id'];?>">Темы</a></li>
        <li><a href="/user/music/index.php?id=<?php echo $ank['id'];?>">Музыка (<?php echo $cnt4['cnt_user_music'] ;?>)</a></li>
        <li><a href="/plugins/notes/user.php?id=<?php echo $ank['id'];?>">Блоги (<?php echo $cnt4['cnt_note'];?>)</a></li>
        <li><a href="/user/bookmark/index.php?id=<?php echo $ank['id'];?>">Закладки (<?php echo $cnt4['cnt_marks'];?>)</a></li>
    </ul>
</nav>
<table class='table_info' cellspacing="0" cellpadding="0">
<tr><td class='block_menu'>
<?php

 // Аватар
echo "<div class='mains'>";
echo avatar($ank['id'], false, 640, 200);
echo "</div>";

 // Рейтинг
echo "<div class='main'>";
if ($ank['rating'] >= 0 && $ank['rating'] <= 100) {
    echo "<div style='background-color: #73a8c7; width: 200px; height: 17px;'>
<div style=' background-color: #064a91; height:17px; width:$ank[rating]%;'></div>
<span style='position:relative; top:-17px; left:45%; right:57%; color:#ffffff;'>$ank[rating]%</span>
</div>";
} elseif ($ank['rating'] >= 100 && $ank['rating'] <= 200) {
    $rat=$ank['rating']-100;
    echo "<div style='background-color: #73a8c7; width: 200px; height: 17px;'>
<div style=' background-color: #064a91; height:17px; width:$rat%;'></div>
<span style='position:relative; top:-17px; left:45%; right:57%; color:#ffffff;'>$ank[rating]%</span>
</div>";
} elseif ($ank['rating'] >= 200 && $ank['rating'] <= 300) {
    $rat=$ank['rating']-200;
    echo "<div style='background-color: #73a8c7; width: 200px; height: 17px;'>
<div style=' background-color: #064a91; height:17px; width:$rat%;'></div>
<span style='position:relative; top:-17px; left:45%; right:57%; color:#ffffff;'>$ank[rating]%</span>
</div>";
} elseif ($ank['rating'] >= 300 && $ank['rating'] <= 400) {
    $rat=$ank['rating']-300;
    echo "<div style='background-color: #73a8c7; width: 200px; height: 17px;'>
<div style=' background-color: #064a91; height:17px; width:$rat%;'></div>
<span style='position:relative; top:-17px; left:45%; right:57%; color:#ffffff;'>$ank[rating]%</span>
</div>";
} elseif ($ank['rating'] >= 400 && $ank['rating'] <= 500) {
    $rat=$ank['rating']-400;
    echo "<div style='background-color: #73a8c7; width: 200px; height: 17px;'>
<div style=' background-color: #064a91; height:17px; width:$rat%;'></div>
<span style='position:relative; top:-17px; left:45%; right:57%; color:#ffffff;'>$ank[rating]%</span>
</div>";
} elseif ($ank['rating'] >= 500 && $ank['rating'] <= 600) {
    $rat=$ank['rating']-500;
    echo "<div style='background-color: #73a8c7; width: 200px; height: 17px;'>
<div style=' background-color: #064a91; height:17px; width:$rat%;'></div>
<span style='position:relative; top:-17px; left:45%; right:57%; color:#ffffff;'>$ank[rating]%</span>
</div>";
} elseif ($ank['rating'] >= 600 && $ank['rating'] <= 700) {
    $rat=$ank['rating']-600;
    echo "<div style='background-color: #73a8c7; width: 200px; height: 17px;'>
<div style=' background-color: #064a91; height:17px; width:$rat%;'></div>
<span style='position:relative; top:-17px; left:45%; right:57%; color:#ffffff;'>$ank[rating]%</span>
</div>";
} elseif ($ank['rating'] >= 700 && $ank['rating'] <= 800) {
    $rat=$ank['rating']-700;
    echo "<div style='background-color: #73a8c7; width: 200px; height: 17px;'>
<div style=' background-color: #064a91; height:17px; width:$rat%;'></div>
<span style='position:relative; top:-17px; left:45%; right:57%; color:#ffffff;'>$ank[rating]%</span>
</div>";
} elseif ($ank['rating'] >= 800 && $ank['rating'] <= 900) {
    $rat=$ank['rating']-800;
    echo "<div style='background-color: #73a8c7; width: 200px; height: 17px;'>
<div style=' background-color: #064a91; height:17px; width:$rat%;'></div>
<span style='position:relative; top:-17px; left:45%; right:57%; color:#ffffff;'>$ank[rating]%</span>
</div>";
} elseif ($ank['rating'] >= 900 && $ank['rating'] <= 1000) {
    $rat=$ank['rating']-900;
    echo "<div style='background-color: #73a8c7; width: 200px; height: 17px;'>
<div style=' background-color: #064a91; height:17px; width:$rat%;'></div>
<span style='position:relative; top:-17px; left:45%; right:57%; color:#ffffff;'>$ank[rating]%</span>
</div>";
}
echo "</div>";
echo "<div class='main'>";
echo "<b>ID номер: $ank[id]</b>";
echo "</div>";

// анкета
echo "<div class='main_menu'>";
echo "<img src='/style/icons/anketa.gif' alt='*' /> <a href='/user/info/anketa.php?id=$ank[id]'>Анкета</a> ";
if (isset($user) && $user['id'] == $ank['id']) {
    echo "[<img src='/style/icons/edit.gif' alt='*' /> <a href='/user/info/edit.php'>ред</a>]";
}
echo "</div>";

// гости
if (isset($user) && $user['id'] == $ank['id']) {
    $cnt2 = $db->query('SELECT * FROM (
SELECT COUNT( * ) guests FROM `my_guests` WHERE `id_ank`=?i AND `read`=?string) q1, (
SELECT COUNT( * ) notif FROM `notification` WHERE `id_user`=?i AND `read`=?string) q2, (
SELECT COUNT( * ) discus FROM `discussions` WHERE `id_user`=?i AND `count`<>?string) q3, (
SELECT COUNT( * ) tape FROM `tape` WHERE `id_user`=?i  AND  `read`=?string) q4',
                       [$user['id'], 1, $user['id'], 0, $user['id'], 0, $user['id'], 0])->row();
    echo "<div class='main'>";
    echo "<img src='/style/icons/guests.gif' alt='*' /> ";
    if ($cnt2['guests']) {
        $color = "<span class='off'>";
        $color2 = "</span>";
    } else {
        $color = null;
        $color2 = null;
    }
    echo "<a href='/user/myguest/index.php'>".$color."Гости".$color2."</a> \n";
    if ($cnt2['guests']) {
        echo "<span class='off'>+{$cnt2['guests']}</span>\n";
    }
    echo "</div>";

// лентa
    echo "<div class='main'>";
    // Уведомления
    if ($cnt2['notif']) {
        echo "<img src='/style/icons/notif.png' alt='*' /> ";
        echo "<a href='/user/notification/index.php'><span class=\"off\">Уведомления</span></a> \n";
        echo "<span class=\"off\">+{$cnt2['notif']}</span> \n";
        echo "<br />";
    }
        
    // Обсуждения
    echo "<img src='/style/icons/chat.gif' alt='*' /> ";
    if ($cnt2['discus']) {
        echo "<a href='/user/discussions/index.php'><span class=\"off\">Обсуждения</span></a> \n";
        echo "<span class=\"off\">+{$cnt2['discus']}</span> \n";
    } else {
        echo "<a href='/user/discussions/index.php'>Обсуждения</a> \n";
    }
    echo "<br />";
    if ($cnt2['tape']) {
        $color = "<span class=\"off\">";
        $color2 = "</span>";
    } else {
        $color = null;
        $color2 = null;
    }
    echo "<img src='/style/icons/lenta.gif' alt='*' /> <a href='/user/tape/'>".$color."Лента".$color2."</a> \n";
    if ($cnt2['tape']) {
        echo "<span class=\"off\">+{$cnt2['tape']}</span>\n";
    }
    echo "</div>";
}

echo "<div class='main_menu'>";
echo "<img src='/style/my_menu/who_rating.png' alt='*' /> <a href='/user/info/who_rating.php?id=$ank[id]'><b>Отзывы</b></a> (".
$cnt3['user_voice'].")<br />\n";
echo "</div>";

// в друзья
if (isset($user) && $user['id'] != $ank['id']) {
    echo "<div class='main'>";
    if ($cnt['frend_new'] == 0 && $cnt['frend'] == 0) {
        echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/info.php?id=$ank[id]&amp;frends'>Добавить в друзья</a><br />\n";
    } elseif ($cnt['frend_new'] == 1) {
        echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?otm=$ank[id]'>Отклонить заявку</a><br />\n";
    } elseif ($cnt['frend'] == 2) {
        $private_photo = true;
        echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?del=$ank[id]'>Удалить из друзей</a><br />\n";
    }
    echo "</div>";

    // Сообщение
    echo "<div class='main'>";
    echo " <a href=\"/mail.php?id=$ank[id]\"><img src='/style/icons/pochta.gif' alt='*' /> Сообщение</a> \n";
    echo "</div>";

    // Монеты перевод
    echo "<div class='main_menu'>";
    echo "<img src='/style/icons/many.gif' alt='*' /> <a href=\"/user/money/translate.php?id=$ank[id]\">Перевести $sMonet[0]</a> \n";
    echo "</div>";
    
    //Сделать подарок
    echo "<div class='main_menu'>";
    echo "<img src='/style/icons/present.gif' alt='*' /> <a href=\"/user/gift/categories.php?id=$ank[id]\">Сделать подарок</a><br />\n";
    echo "</div>";
}
// настройки
if (isset($user) && $ank['id'] == $user['id']) {
    echo "<div class='main_menu'>";
    echo "<img src='/style/icons/uslugi.gif' alt='*' /> <a href=\"/user/money/index.php\">Дополнительные услуги</a><br /> \n";
    echo "<img src='/style/icons/settings.png' alt='*' /> <a href=\"/user/info/settings.php\">Мои настройки</a> | <a href=\"/umenu.php\">Меню</a>\n";
    echo "</div>";
}

// друзья онлайн
if ($online_frend) {
    echo "<div class='foot'>Друзья онлайн ($online_frend)</div>";

$set['p_str'] =  ($online_frend < 20 ? $online_frend : 20);
$q = $db->query("SELECT u.id, u.nick, u.pol FROM `frends` f
JOIN `user` u ON `f`.`frend`=`u`.`id`
WHERE `f`.`user`=?i AND `f`.`i`=1 AND `u`.`date_last`>?i
ORDER BY `u`.`date_last` DESC LIMIT ?i",
                [$ank['id'], TIME_600, $set['p_str']]);

while ($post3 = $q->row()) {
    if ($num==0) {
        echo "  <div class='nav1'>\n";
        $num=1;
    } elseif ($num==1) {
        echo "  <div class='nav2'>\n";
        $num=0;
    }
    echo avatar($post3['id']);
    echo ' <a href="/info.php?id='.$post3['id'].'">'.$post3['nick'].'</a>'.medal($post3['id']).' '.online($post3['id']).' ('.(($post3['pol'] == 1)?'М':'Ж').')<br />';
    echo '<a href="/mail.php?id='.$post3['id'].'"><img src="/style/icons/pochta.gif" alt="*" /> Сообщение</a> ';
    echo "</div>";
}
}
// the end
?>
</td><!-- / info block -->
<td class='block_info'>
<?php

// Вывод анкеты, фото, и стены
echo "<div class='accordion-group'>
<div class='accordion-heading'>";
echo " ".group($ank['id'])." ";
echo user::nick($ank['id'], 1, 1, 1). " <span style='float:right;color:#666;'>Заходил".($ank['pol'] == 0 ? 'a': null)." ".vremja($ank['date_last'])."</span> ";
if ((user_access('user_ban_set') || user_access('user_ban_set_h') || user_access('user_ban_unset')) && $ank['id'] != $user['id']) {
    echo "<a href='/adm_panel/ban.php?id=$ank[id]'><span class=\"off\">[Бан]</span></a>\n";
}
echo "</div></div>";

// статус вывод
if ($status['id'] || $ank['id'] == $user['id']) {
    if ($status['id']) {
        $sql = ', (
SELECT COUNT( * ) FROM `status_komm` WHERE `id_status`=' . $status['id'] . ') komm_status, (
SELECT COUNT( * ) FROM `status_like` WHERE `id_status`=' . $status['id'] . ') like_status';
        if (isset($user)) {
            $sql .= ', (
SELECT COUNT( * ) FROM `status_like` WHERE `id_status`=' . $status['id'] . ' AND `id_user`=' . $user['id'] . ') like_current_user';
        }
    } else {
        $sql = null;
    }
    $cnt_status = $db->query('SELECT (
SELECT COUNT( * ) FROM `status` WHERE `id_user`=?i) all_status ?q',
            [$ank['id'], $sql])->row();
    echo '<div class="st_1"></div>';
    echo '<div class="st_2">';
    if (isset($user) && $user['id'] == $ank['id']) {
        echo '<form style="border:none;" action="?id=' . $ank['id'] . '" method="post">'."\n";
        echo '<input type="text" style="width:80%;" placeholder="Что у Вас нового?" name="status" value=""/>'."\n";
        echo '<input class="submit" style="width:15%;" type="submit" value="+" />'."\n";
        echo '</form>'."\n";
    }
    if ($status['id']) {
        echo output_text($status['msg']) . ' <span style="font-size:10px; color:gray;">' . vremja($status['time']) . '</span>';
    }
    echo "</div>";
    
    if ($status['id']) {
        echo " <a href='/user/status/komm.php?id=$status[id]'><img src='/style/icons/bbl4.png' alt=''/> " . $cnt_status['komm_status'] . " </a> ";
        if (isset($user) && $user['id'] != $ank['id'] && !$cnt_status['like_current_user']) {
            echo " <a href='/info.php?id=$ank[id]&amp;like'><img src='/style/icons/like.gif' alt='*'/> Класс!</a> • ";
            $like = $cnt_status['like_status'];
        } elseif (isset($user) && $user['id'] != $ank['id']) {
            echo " <img src='/style/icons/like.gif' alt=''/> Вы и ";
            $like = $cnt_status['like_status'] - 1;
        } else {
            echo " <img src='/style/icons/like.gif' alt=''/> ";
            $like = $cnt_status['like_status'];
        }
        echo "<a href='/user/status/like.php?id=$status[id]'> $like чел. </a>";
    }
    
    // Общее колличество статусов
    if ($cnt_status['all_status']) {
        echo "<div class='main_menu'>"; // пишем свой див
        echo " &rarr; <a href='/user/status/index.php?id=$ank[id]'>Все статусы</a> (" . $cnt_status['all_status'] . ")\n";
        echo "</div>";
    }
}

// Последние добавленные фото
if ($cnt4['cnt_foto']) {
    $limit_foto = ($cnt4['cnt_foto'] < 5 ? $cnt4['cnt_foto'] : 5);
    $pattern = 'SELECT `id`, `id_gallery`, `ras` FROM `gallery_foto` WHERE `id_user`=?i AND `id_gallery` IN (
SELECT `id` FROM `gallery` WHERE `privat`=?string) ORDER BY `id` DESC LIMIT ?i';
    $data = [$ank['id'], 0, $limit_foto];
    if (isset($user)) {
        if ($user['id'] == $ank['id']) {
            $pattern = 'SELECT `id`, `id_gallery`, `ras` FROM `gallery_foto` WHERE `id_user`=?i ORDER BY `id` DESC LIMIT ?i';
            $data = [$ank['id'], $limit_foto];
        } elseif ($private_photo && $user['id'] <> $ank['id']) {
            $pattern = 'SELECT `id`, `id_gallery`, `ras` FROM `gallery_foto` WHERE `id_user`=?i AND `id_gallery` IN (
SELECT `id` FROM `gallery` WHERE `privat`<?string) ORDER BY `id` DESC LIMIT ?i';
            $data = [$ank['id'], 2, $limit_foto];
        }
    }

    $sql = $db->query($pattern, $data);
    echo "<div class='slim_header'>";
    echo "<img src='/style/icons/pht2.png' alt='*' /> ";
    echo "<a href='/foto/$ank[id]/'><b>Фотографии</b></a> ";
    echo " <span class='mm_counter'>" . $cnt4['cnt_foto'] . "</span>";
    echo "</div>";
    echo "<div class='nav3'>";
    
    while ($photo = $sql->row()) {
        echo "<a href='/foto/$ank[id]/$photo[id_gallery]/$photo[id]/'><img class='sto500' style='width:103px; height:103px; background-image:url(/foto/foto128/$photo[id].$photo[ras]);' src=''/></a>";
    }
    echo "</div>";
}

// Анкета пользователя
if (isset($user) && $ank['id'] == $user['id']) {
    $name = "<a href='/user/info/edit.php?act=ank_web&amp;set=name'>";
    $date = "<a href='/user/info/edit.php?act=ank_web&amp;set=date'>";
    $gorod = "<a href='/user/info/edit.php?act=ank_web&amp;set=gorod'>";
    $pol = "<a href='/user/info/edit.php?act=ank_web&amp;set=pol'>";
    $a = "</a>";
} else {
    $name = "<span style='padding:1px; color : #005ba8; padding:1px;'>";
    $date =  "<span style='padding:1px; color : #005ba8; padding:1px;'>";
    $gorod =  "<span style='padding:1px; color : #005ba8; padding:1px;'>";
    $pol =   "<span style='padding:1px; color : #005ba8; padding:1px;'>";
    $a = "</span>";
}

// Основное
echo "<div class='nav1'>";
if ($ank['ank_name'] != null) {
    echo "$name<span class=\"ank_n\">Имя:</span>$a <span class=\"ank_d\">$ank[ank_name]</span><br />\n";
} else {
    echo "$name<span class=\"ank_n\">Имя:</span>$a<br />\n";
}
echo "$pol<span class=\"ank_n\">Пол:</span>$a <span class=\"ank_d\">".(($ank['pol'] == 1)?'Мужской':'Женский')."</span><br />\n";
if ($ank['ank_city'] != null) {
    echo "$gorod<span class=\"ank_n\">Город:</span>$a <span class=\"ank_d\">".output_text($ank['ank_city'])."</span><br />\n";
} else {
    echo "$gorod<span class=\"ank_n\">Город:</span>$a<br />\n";
}
if ($ank['ank_d_r'] != null && $ank['ank_m_r'] != null && $ank['ank_g_r'] != null) {
    if ($ank['ank_m_r'] == 1) {
        $ank['mes']='Января';
    } elseif ($ank['ank_m_r'] == 2) {
        $ank['mes']='Февраля';
    } elseif ($ank['ank_m_r'] == 3) {
        $ank['mes']='Марта';
    } elseif ($ank['ank_m_r'] == 4) {
        $ank['mes']='Апреля';
    } elseif ($ank['ank_m_r'] == 5) {
        $ank['mes']='Мая';
    } elseif ($ank['ank_m_r'] == 6) {
        $ank['mes']='Июня';
    } elseif ($ank['ank_m_r'] == 7) {
        $ank['mes']='Июля';
    } elseif ($ank['ank_m_r'] == 8) {
        $ank['mes']='Августа';
    } elseif ($ank['ank_m_r'] == 9) {
        $ank['mes']='Сентября';
    } elseif ($ank['ank_m_r'] == 10) {
        $ank['mes']='Октября';
    } elseif ($ank['ank_m_r'] == 11) {
        $ank['mes']='Ноября';
    } else {
        $ank['mes']='Декабря';
    }
    echo "$date<span class=\"ank_n\">Дата рождения:</span>$a $ank[ank_d_r] $ank[mes] $ank[ank_g_r]г. <br />\n";
    $ank['ank_age'] = date("Y") - $ank['ank_g_r'];
    if (date("n") < $ank['ank_m_r']) {
        $ank['ank_age'] = $ank['ank_age'] - 1;
    } elseif (date("n") == $ank['ank_m_r'] && date("j") < $ank['ank_d_r']) {
        $ank['ank_age'] = $ank['ank_age'] - 1;
    }
    echo "<span class=\"ank_n\">Возраст:</span> $ank[ank_age] \n";
} elseif ($ank['ank_d_r'] != null && $ank['ank_m_r'] != null) {
    if ($ank['ank_m_r'] == 1) {
        $ank['mes']='Января';
    } elseif ($ank['ank_m_r'] == 2) {
        $ank['mes']='Февраля';
    } elseif ($ank['ank_m_r'] == 3) {
        $ank['mes']='Марта';
    } elseif ($ank['ank_m_r'] == 4) {
        $ank['mes']='Апреля';
    } elseif ($ank['ank_m_r'] == 5) {
        $ank['mes']='Мая';
    } elseif ($ank['ank_m_r'] == 6) {
        $ank['mes']='Июня';
    } elseif ($ank['ank_m_r'] == 7) {
        $ank['mes']='Июля';
    } elseif ($ank['ank_m_r'] == 8) {
        $ank['mes']='Августа';
    } elseif ($ank['ank_m_r'] == 9) {
        $ank['mes']='Сентября';
    } elseif ($ank['ank_m_r'] == 10) {
        $ank['mes']='Октября';
    } elseif ($ank['ank_m_r'] == 11) {
        $ank['mes']='Ноября';
    } else {
        $ank['mes']='Декабря';
    }
    echo "$date<span class=\"ank_n\">День рождения:</span>$a $ank[ank_d_r] $ank[mes] \n";
} if ($ank['ank_d_r'] >= 19 && $ank['ank_m_r'] == 1) {
    echo "| Водолей<br />";
} elseif ($ank['ank_d_r'] <= 19 && $ank['ank_m_r'] == 2) {
    echo "| Водолей<br />";
} elseif ($ank['ank_d_r'] >= 18 && $ank['ank_m_r'] == 2) {
    echo "| Рыбы<br />";
} elseif ($ank['ank_d_r'] <= 21 && $ank['ank_m_r'] == 3) {
    echo "| Рыбы<br />";
} elseif ($ank['ank_d_r'] >= 20 && $ank['ank_m_r'] == 3) {
    echo "| Овен<br />";
} elseif ($ank['ank_d_r'] <= 21 && $ank['ank_m_r'] == 4) {
    echo "| Овен<br />";
} elseif ($ank['ank_d_r'] >= 20 && $ank['ank_m_r'] == 4) {
    echo "| Телец<br />";
} elseif ($ank['ank_d_r'] <= 21 && $ank['ank_m_r'] == 5) {
    echo "| Телец<br />";
} elseif ($ank['ank_d_r'] >= 20 && $ank['ank_m_r'] == 5) {
    echo "| Близнецы<br />";
} elseif ($ank['ank_d_r'] <= 22 && $ank['ank_m_r'] == 6) {
    echo "| Близнецы<br />";
} elseif ($ank['ank_d_r'] >= 21 && $ank['ank_m_r'] == 6) {
    echo "| Рак<br />";
} elseif ($ank['ank_d_r'] <= 22 && $ank['ank_m_r'] == 7) {
    echo "| Рак<br />";
} elseif ($ank['ank_d_r'] >= 23 && $ank['ank_m_r'] == 7) {
    echo "| Лев<br />";
} elseif ($ank['ank_d_r'] <= 22 && $ank['ank_m_r'] == 8) {
    echo "| Лев<br />";
} elseif ($ank['ank_d_r'] >= 22 && $ank['ank_m_r'] == 8) {
    echo "| Дева<br />";
} elseif ($ank['ank_d_r'] <= 23 && $ank['ank_m_r'] == 9) {
    echo "| Дева<br />";
} elseif ($ank['ank_d_r'] >= 22 && $ank['ank_m_r'] == 9) {
    echo "| Весы<br />";
} elseif ($ank['ank_d_r'] <= 23 && $ank['ank_m_r'] == 10) {
    echo "| Весы<br />";
} elseif ($ank['ank_d_r'] >= 22 && $ank['ank_m_r'] == 10) {
    echo "| Скорпион<br />";
} elseif ($ank['ank_d_r'] <= 22 && $ank['ank_m_r'] == 11) {
    echo "| Скорпион<br />";
} elseif ($ank['ank_d_r'] >= 21 && $ank['ank_m_r'] == 11) {
    echo "| Стрелец<br />";
} elseif ($ank['ank_d_r'] <= 22 && $ank['ank_m_r'] == 12) {
    echo "| Стрелец<br />";
} elseif ($ank['ank_d_r'] >= 21 && $ank['ank_m_r'] == 12) {
    echo "| Козерог<br />";
} elseif ($ank['ank_d_r'] <= 20 && $ank['ank_m_r'] == 1) {
    echo "| Козерог<br />";
}
echo "</div>\n";
echo '<form action="someplace.html" method="post" name="myForm"><div id="formResponse">';
echo ' <a onclick="anketa.submit()" name="myForm"><div class="form_info">Показать подробную информацию</div></a>';
echo '</div></form>';
echo "<script type='text/javascript'>	
var anketa = new DHTMLSuite.form({ formRef:'myForm',action:'/ajax/php/anketa.php?id=$ank[id]',responseEl:'formResponse'});	
var anketaClose = new DHTMLSuite.form({ formRef:'myForm',action:'/ajax/php/anketa.php',responseEl:'formResponse'});
</script>";

// Подарки
$width = ($webbrowser == 'web' ? '60' : '45'); // Размер подарков при выводе в браузер

if ($cnt4['cnt_gift']) {
    echo '<div class="foot">';
    echo '&rarr; <a href="/user/gift/index.php?id=' . $ank['id'] . '">Все подарки</a> (' . $cnt4['cnt_gift'] . ')';
    echo '</div>';
    
    $gift_lim = ($cnt4['cnt_gift'] < 4 ? $cnt4['cnt_gift'] : 4);
    $q = $db->query("SELECT `id`, `id_gift`, `status` FROM `gifts_user` WHERE `id_user`=?i AND `status`=?i ORDER BY `id` DESC LIMIT ?i",
                    [$ank['id'], 1, $gift_lim]);
    echo '<div class="nav2">';
    while ($post = $q->row()) {
        echo '<a href="/user/gift/gift.php?id=' . $post['id'] . '"><img src="/sys/gift/' . $post['id_gift'] . '.png" style="max-width:' . $width . 'px;" alt="Подарок" /></a> ';
    }
    echo '</div>';
}
    
// Стена юзера
if (isset($user)) {
    echo "<div class='accordion-group'>
<div class='accordion-heading'>\n";
    if ($user['wall'] == 1) {
        echo '<a class="accordion-toggle decoration-none collapsed" href="/info.php?id='.$ank['id'].'&amp;wall=0"><img src="/style/icons/stena.gif" alt="*" /> Стена</a>'."\n";
        include_once 'user/stena/index.php';
    } else {
        echo '<a class="accordion-toggle decoration-none collapsed" href="/info.php?id='.$ank['id'].'&amp;wall=1"><img src="/style/icons/stena.gif" alt="*" /> Стена</a>'."\n";
    }
    echo '</div></div>';
}

?>
<!--/td>
</tr>
</table--><?php
echo "\n".'<!-- /end web.php -->'."\n";