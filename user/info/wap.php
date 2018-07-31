<?php
// статус форма
if (isset($user) && isset($_GET['status'])) {
    if ($user['id'] == $ank['id']) {
        echo '<div class="main">Статус [512 символов]</div>';
        echo '<form action="/info.php?id=' . $ank['id'] . '" method="post">';
        echo "$tPanel<textarea type=\"text\" style='' name=\"status\" value=\"\"/></textarea><br /> ";
        echo "<input class=\"submit\" style='' type=\"submit\" value=\"Установить\" />";
        echo " <a href='/info.php?id=$ank[id]'>Отмена</a><br />";
        echo "</form>";
        include_once 'sys/inc/tfoot.php';
        exit;
    }
}

if ($ank['group_access']>1) {
    echo "<div class='err'>$ank[group_name]</div>";
}
echo "<div class='nav1'>";
echo group($ank['id']) . " $ank[nick] ";
echo medal($ank['id']) . " " . online($ank['id']) . " ";
if ((user_access('user_ban_set') || user_access('user_ban_set_h') || user_access('user_ban_unset')) && $ank['id'] != $user['id']) {
    echo "<a href='/adm_panel/ban.php?id=$ank[id]'><font color=red>[Бан]</font></a>";
}
echo "</div>";
// Аватар
echo "<div class='nav2'>";
echo avatar($ank['id'], true, 128, false);
echo "<br />";
if (isset($user) && isset($_GET['like']) && $user['id']!=$ank['id']
    && !$db->query(
        "SELECT COUNT( * ) FROM `status_like` WHERE `id_status`=?i AND `id_user`=?i",
                   [$status['id'], $user['id']])->el()) {
    $db->query(
        "INSERT INTO `status_like` (`id_user`, `id_status`) VALUES(?i, ?i)",
               [$user['id'], $status['id']]);
}
if ($status['id'] || $ank['id'] == $user['id']) {
    echo "<div class='st_1'></div>";
    echo "<div class='st_2'>";
    if ($status['id']) {
        echo output_text($status['msg']) . ' <span style="font-size:11px; color:gray;">' . vremja($status['time']) . '</span>';
        if ($ank['id'] == $user['id']) {
            echo " [<a href='?id=$ank[id]&amp;status'><img src='/style/icons/edit.gif' alt='*'> нов</a>]";
        }
        echo '<br />';
    } elseif ($ank['id']==$user['id']) {
        echo "Ваш статус [<a href='?id=$ank[id]&status'><img src='/style/icons/edit.gif' alt='*'> ред</a>]";
    }
    echo "</div>";
    // Если статус установлен
    if ($status['id']) {
        if (isset($user)) {
            $sql_like = ', (
SELECT COUNT( * ) FROM `status_like` WHERE `id_status`='.$status['id'].' AND `id_user`='.$user['id'].') user_like';
        } else {
            $sql_like = null;
        }
        $cnt = $db->query(
                    'SELECT (
SELECT COUNT( * ) FROM `status` WHERE `id_user`=?i) all_user_status, (
SELECT COUNT( * ) FROM `status_komm` WHERE `id_status`=?i) status_komm, (
SELECT COUNT( * ) FROM `status_like` WHERE `id_status`=?i) all_like ?q;',
                            [$ank['id'], $status['id'], $status['id'], $sql_like])->row();
        echo " <a href='/user/status/komm.php?id=$status[id]'><img src='/style/icons/bbl4.png' alt=''/> " . $cnt['status_komm'] . " </a> ";
        $l=$cnt['all_like'];
        if (isset($user) && $user['id']!=$ank['id'] && !$cnt['user_like']) {
            echo " <a href='/info.php?id=$ank[id]&amp;like'><img src='/style/icons/like.gif' alt='*'/> Класс!</a> • ";
            $like = $l;
        } elseif (isset($user) && $user['id']!=$ank['id']) {
            echo " <img src='/style/icons/like.gif' alt=''/> Вы и ";
            $like = $l-1;
        } else {
            echo " <img src='/style/icons/like.gif' alt=''/> ";
            $like = $l;
        }
        echo "<a href='/user/status/like.php?id=$status[id]'> $like чел. </a>";
    }
    // Общее колличество статусов
    if ($status['id'] && $cnt['all_user_status']) {
        echo "<br /> &rarr; <a href='/user/status/index.php?id=$ank[id]'>Все статусы</a> (" . $cnt['all_user_status'] . ")";
    }
}
echo "</div>";
  
// Подарки
$width = ($webbrowser == 'web' ? '60' : '45'); // Размер подарков при выводе в браузер
if ($gifts = $db->query(
                    'SELECT COUNT( * ) FROM `gifts_user` WHERE `id_user`=?i AND `status`=?i',
                            [$ank['id'], 1])->el()) {
    $q = $db->query(
                'SELECT `sts`.`id`, `sts`.`status`, `stl`.`id` AS id_gift FROM `gifts_user` `sts`
JOIN `gift_list` stl ON stl.id=sts.id_gift
WHERE `sts`.`id_user`=?i AND `sts`.`status`=?i ORDER BY `sts`.`id` DESC LIMIT ?i',
                        [$ank['id'], 1, 5]);
    echo '<div class="nav2">';
    while ($post = $q->row()) {
        echo '<a href="/user/gift/gift.php?id=' . $post['id'] . '"><img src="/sys/gift/' . $post['id_gift'] . '.png" style="max-width:' . $width . 'px;" alt="Подарок" /></a> ';
    }
    echo '</div>';
    
    echo '<div class="nav2">';
    echo '&rarr; <a href="/user/gift/index.php?id=' . $ank['id'] . '">Все подарки</a> (' . $gifts . ')';
    echo '</div>';
}
 
// Анкета
echo "<div class='nav1'>";
echo "<img src='/style/icons/anketa.gif' alt='*' /> <a href='/user/info/anketa.php?id=$ank[id]'>Анкета</a> ";
if (isset($user) && $user['id']==$ank['id']) {
    echo "[<img src='/style/icons/edit.gif' alt='*' /> <a href='/user/info/edit.php'>ред</a>]";
}
echo "</div>";

// Гости
if (isset($user) && $user['id'] == $ank['id']) {
    echo '<div class="nav2">';
    
    $cnt = $db->query(
                        'SELECT (
SELECT COUNT( * ) FROM `my_guests` WHERE `id_ank`=?i AND `read`=?) my_guest, (
SELECT COUNT( * ) FROM `gallery_rating` WHERE `avtor`=?i  AND `read`=?) glr_rating',
                                [$user['id'], '1', $ank['id'], '1'])->row();
    
    echo '<img src="/style/icons/guests.gif" alt="*" /> ';
    
    if ($cnt['my_guest'] != 0) {
        echo '<a href="/user/myguest/index.php"><font color="red">Гости +' . $cnt['my_guest'] . '</font></a>'."\n";
    } else {
        echo "<a href='/user/myguest/index.php'>Гости</a> ";
    }
    echo ' | ';
    if ($cnt['glr_rating'] != 0) {
        echo '<a href="/user/info/ocenky.php"><font color="red">Оценки +' . $cnt['glr_rating'] . '</font></a>'."\n";
    } else {
        echo "<a href='/user/info/ocenky.php'>Оценки</a> ";
    }
    echo "</div>";
}

// Друзья
$k_fr = $db->query("SELECT (
SELECT COUNT( * ) FROM `frends_new` WHERE `to`=?i) new_frend, (
SELECT COUNT( * ) FROM `frends` WHERE `user`=?i AND `i`=1) all_frend, (
SELECT COUNT( * ) FROM `frends` WHERE `user`=?i AND `i`=1 AND `frend` IN(
SELECT `id` FROM `user` WHERE `date_last`>?i)) online_frend",
            [$ank['id'], $ank['id'], $ank['id'], TIME_600])->row();

echo '<div class="nav2">';
echo '<img src="/style/icons/druzya.png" alt="*" /> ';
echo '<a href="/user/frends/?id=' . $ank['id'] . '">Друзья</a> (' . $k_fr['all_frend'] . '</b>/';
echo '<a href="/user/frends/online.php?id='.$ank['id'].'"><span style="color:green;">'.$k_fr['online_frend'].'</span></a>)';

if ($k_fr['new_frend'] > 0 && $ank['id'] == $user['id']) {
    echo ' <a href="/user/frends/new.php"><span class="off">+' . $k_fr['new_frend'] . '</span></a>';
}
echo '</div>';
if (isset($user) && $user['id'] == $ank['id']) {
    echo '<div class="nav2">';

    // Уведомления
    if (isset($user) && $user['id']==$ank['id']) {
        $k_notif = $db->query(
                        'SELECT COUNT( * ) FROM `notification` WHERE `id_user`=?i AND `read`=?',
                                [$user['id'], '0'])->el();
        
        if ($k_notif > 0) {
            echo "<img src='/style/icons/notif.png' alt='*' /> ";
            echo "<a href='/user/notification/index.php'><font color='red'>Уведомления</font></a> ";
            echo "<font color=\"red\">+$k_notif</font> ";
            echo "<br />";
        }
    }

    // Обсуждения
    if (isset($user) && $user['id']==$ank['id']) {
        $new_g=$db->query(
                        'SELECT (
SELECT COUNT( * ) FROM `discussions` WHERE `id_user`=?i AND `count`>?i) discut, (
SELECT COUNT( * ) FROM `tape` WHERE `id_user`=?i  AND  `read`=?) tape',
                                [$user['id'], 0, $user['id'], '0'])->row();
        echo '<img src="/style/icons/chat.gif" alt="*" /> ';
        if ($new_g['discut']) {
            echo "<a href='/user/discussions/index.php'><font color='red'>Обсуждения</font></a> ";
            echo '<span class="off">+'.$new_g['discut'].'</span>';
        } else {
            echo '<a href="/user/discussions/index.php">Обсуждения</a>';
        }
        echo '<br />';

        //Лента
        if ($new_g['tape']) {
            $color = "<font color='red'>";
            $color2 = "</font>";
        } else {
            $color = null;
            $color2 = null;
        }
        echo "<img src='/style/icons/lenta.gif' alt='*' /> <a href='/user/tape/'>".$color."Лента".$color2."</a> ";

        if ($new_g['tape']) {
            echo '<span class="off">+'.$new_g['tape'].'</span>';
        }

        echo "<br />";
    }
    echo "</div>";
}

// TODO: охуеть сколько ненужного хлама
$dir_osn = $db->query(
    "SELECT (
SELECT COUNT( * ) FROM `gallery_foto` WHERE `id_user`=?i) photo, (
SELECT COUNT( * ) FROM `user_files` WHERE `id_user`=?i AND `osn`=1) user_file, (
SELECT COUNT( * ) FROM `obmennik_files` WHERE `id_user`=?i) user_obmen_files, (
SELECT COUNT( * ) FROM `user_music` WHERE `id_user`=?i) music, (
SELECT `id` FROM `user_files` WHERE `id_user`=?i AND `osn`=?i) id",
                      [$ank['id'], $ank['id'], $ank['id'], $ank['id'], $ank['id'], 1])->row();

echo "<div class='nav1'>";

// Фото
echo "<img src='/style/icons/foto.png' alt='*' /> ";
echo "<a href='/foto/$ank[id]/'>Фотографии</a> ";
echo "(" . $dir_osn['photo'] . ")<br />";

// Файлы
if (isset($user) && $ank['id'] == $user['id'] && !$dir_osn['user_file']) {
    $dir_id = $db->query(
        "INSERT INTO `user_files` (`id_user`, `name`,  `osn`) VALUES(?i, ?, ?i)",
               [$ank['id'], 'Файлы', 1])->id();
echo "<img src='/style/icons/files.gif' alt='*' /> ";
echo "<a href='/user/personalfiles/$ank[id]/$dir_id/'>Файлы</a> ";
} elseif ($dir_osn['user_file']) {
echo "<img src='/style/icons/files.gif' alt='*' /> ";
echo "<a href='/user/personalfiles/$ank[id]/$dir_osn[id]/'>Файлы</a> ";
echo "(" . $dir_osn['user_file'] . "/" . $dir_osn['user_obmen_files'] . ")<br />";

// Музыка
echo "<img src='/style/icons/play.png' alt='*' width='16'/> ";
echo "<a href='/user/music/index.php?id=$ank[id]'>Музыка</a> ";
echo "(" . $dir_osn['music'] . ")";
echo "</div>";
}
// Темы и комментарии
echo "<div class='nav2'><img src='/style/icons/blogi.png' alt='*' width='16'/> ";
echo "<a href='/user/info/them_p.php?id=".$ank['id']."'>Темы и комментарии</a> ";
echo "</div>";

$counters = $db->query('SELECT * FROM (
SELECT COUNT( * ) user_notes FROM `notes` WHERE `id_user`=?i)q, (
SELECT COUNT( * ) user_marks FROM `bookmarks` WHERE `id_user`=?i)q2, (
SELECT COUNT( * ) user_voice FROM `user_voice2` WHERE `id_kont`=?i)q3', [$ank['id'],$ank['id'], $ank['id']])->row();
// Дневники
echo "<div class='nav2'>";
echo "<img src='/style/icons/zametki.gif' alt='*' /> ";
echo '<a href="/plugins/notes/user.php?id='.$ank['id'].'">Дневники</a> ('.$counters['user_notes'].')<br />';

// Закладки
echo "<img src='/style/icons/fav.gif' alt='*' /> ";
echo '<a href="/user/bookmark/index.php?id='.$ank['id'].'">Закладки</a> ('.$counters['user_marks'].')<br />';

// Отзывы
echo "<img src='/style/my_menu/who_rating.png' alt='*' /> <a href='/user/info/who_rating.php?id=$ank[id]'>Отзывы</a> (".$counters['user_voice'].")<br />";
 echo "</div>";

// Сообщение
if (isset($user) && $ank['id'] != $user['id']) {
    echo "<div class='nav1'>";
    echo " <a href=\"/mail.php?id=$ank[id]\"><img src='/style/icons/pochta.gif' alt='*' /> Сообщение</a><br />";

    // В друзья
    if ($uSet['new_frend']==0 && $uSet['frend']==0) {
        echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?add=".$ank['id']."'>Добавить в друзья</a><br />";
    } elseif ($uSet['new_frend']==1) {
        echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?otm=$ank[id]'>Отклонить заявку</a><br />";
    } elseif ($uSet['frend']==2) {
        echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?del=$ank[id]'>Удалить из друзей</a><br />";
    }

    // В закладки
    echo '<img src="/style/icons/fav.gif" alt="*" /> ';
    if (!$db->query(
    "SELECT COUNT( * ) FROM `bookmarks` WHERE `id_user`=?i AND `id_object`=?i AND `type`=?",
                    [$user['id'], $ank['id'], 'people'])->el()) {
        echo '<a href="?id=' . $ank['id'] . '&amp;fav=1">В закладки</a><br />';
    } else {
        echo '<a href="?id=' . $ank['id'] . '&amp;fav=0">Удалить из закладок</a><br />';
    }
    echo "</div>";
    echo "<div class='nav2'>";

    // Монеты перевод
    echo "<img src='/style/icons/uslugi.gif' alt='*' /> <a href=\"/user/money/translate.php?id=$ank[id]\">Перевести $sMonet[0]</a><br />";

    // Сделать подарок
    echo "<img src='/style/icons/present.gif' alt='*' /> <a href=\"/user/gift/categories.php?id=$ank[id]\">Сделать подарок</a><br />";
    echo "</div>";
}

// Hастройки
if (isset($user) && $ank['id']==$user['id']) {
    echo "<div class='main'>";
    echo "<img src='/style/icons/uslugi.gif' alt='*' /> <a href=\"/user/money/index.php\">Дополнительные услуги</a><br /> ";
    echo "<img src='/style/icons/settings.png' alt='*' /> <a href=\"/user/info/settings.php\">Мои настройки</a> | <a href=\"/umenu.php\">Меню</a>";
    echo "</div>";
}

// Стена
echo "<div class='foot'>\n";
echo "<img src='/style/icons/stena.gif' alt='*' /> ";
if (isset($user) && $user['wall']==0) {
    echo "<a href='/info.php?id=$ank[id]&amp;wall=1'>Стена</a>\n";
} elseif (isset($user)) {
    echo "<a href='/info.php?id=$ank[id]&amp;wall=0'>Стена</a>\n";
} else {
    echo "Стена\n";
}
echo "</div>\n";
if ($user['wall']==0) {
    include_once H.'user/stena/index.php';
}
// The End
