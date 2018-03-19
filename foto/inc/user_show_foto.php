<?php
if (!isset($user) && !isset($_GET['id_user'])) {
    header("Location: /foto/?".SID);
    exit;
}
if (isset($user)) {
    $ank['id'] = $user['id'];
}
if (isset($_GET['id_user'])) {
    $ank['id'] = intval($_GET['id_user']);
}
$ank = get_user($ank['id']);
if (!$ank) {
    header("Location: /foto/?".SID);
    exit;
}
/* Бан пользователя */
if ($db->query("SELECT COUNT(*) FROM `ban` WHERE `razdel` = 'foto' AND `id_user` = '$user[id]' AND (`time` > '$time' OR `view` = '0' OR `navsegda` = '1')")->el()) {
    header('Location: /ban.php?'.SID);
    exit;
}
$gallery['id'] = intval($_GET['id_gallery']);
if (!$db->query("SELECT COUNT(*) FROM `gallery` WHERE `id` = '$gallery[id]' AND `id_user` = '$ank[id]' LIMIT 1")->el()) {
    header("Location: /foto/$ank[id]/?".SID);
    exit;
}
$gallery = $db->query("SELECT * FROM `gallery` WHERE `id` = '$gallery[id]' AND `id_user` = '$ank[id]' LIMIT 1")->row();
$foto['id'] = intval($_GET['id_foto']);
if (!$db->query("SELECT COUNT(*) FROM `gallery_foto` WHERE `id` = '$foto[id]' LIMIT 1")->el()) {
    header("Location: /foto/$ank[id]/$gallery[id]/?".SID);
    exit;
}
$foto = $db->query("SELECT * FROM `gallery_foto` WHERE `id` = '$foto[id]'  LIMIT 1")->row();
/*
================================
Закладки
================================
*/
// Добавляем в закладки
if (isset($_GET['fav']) && $_GET['fav'] == 1) {
    if (!$db->query("SELECT COUNT(`id`) FROM `bookmarks` WHERE `id_user` = '" . $user['id'] . "' AND `id_object` = '" . $foto['id'] . "' AND `type`='foto' LIMIT 1")->el()) {
        $db->query("INSERT INTO `bookmarks` (`type`,`id_object`, `id_user`, `time`) VALUES ('foto','$foto[id]', '$user[id]', '$time')");
        $_SESSION['message'] = 'Фото добавлено в закладки';
        header("Location: /foto/$ank[id]/$gallery[id]/$foto[id]/?page=" . intval($_GET['page']));
        exit;
    }
}
// Удаляем из закладок
if (isset($_GET['fav']) && $_GET['fav'] == 0) {
    if ($db->query("SELECT COUNT(`id`) FROM `bookmarks` WHERE `id_user` = '" . $user['id'] . "' AND `id_object` = '" . $foto['id'] . "' `type`='foto' LIMIT 1")->el()) {
        $db->query("DELETE FROM `bookmarks` WHERE `id_user` = '$user[id]' AND  `id_object` = '$foto[id]' AND `type`='foto'");
        $_SESSION['message'] = 'Фото удалено из закладок';
        header("Location: /foto/$ank[id]/$gallery[id]/$foto[id]/?page=" . intval($_GET['page']));
        exit;
    }
}
$IS = [0,0];
if (is_file(H.'sys/gallery/foto/'.$foto['id'].'.'.$foto['ras'])) {
    $IS = getimagesize(H.'sys/gallery/foto/'.$foto['id'].'.'.$foto['ras']);
}
printf("", $IS[0], $IS[1]);
$w = $IS[0];
$h = $IS[1];
if ((user_access('foto_foto_edit')) || (isset($user) && $ank['id'] == $user['id'])) {
    include 'inc/gallery_show_foto_act.php';
}
/*------------очищаем счетчик этого обсуждения-------------*/
if (isset($user)) {
    $db->query("UPDATE `discussions` SET `count` = '0' WHERE `id_user` = '$user[id]' AND `type` = 'foto' AND `id_sim` = '$foto[id]' LIMIT 1");
    $db->query("UPDATE `notification` SET `read` = '1' WHERE `type` = 'foto_komm' AND `id_user` = '$user[id]' AND `id_object` = '$foto[id]'");
}
/*---------------------------------------------------------*/
/*
==========================
Оценка к фото
==========================
*/
    
if (isset($user) && $user['id'] != $ank['id'] && !$db->query("SELECT COUNT(*) FROM `gallery_rating` WHERE `id_user` = '$user[id]' AND `id_foto` = '$foto[id]'")->el()->el()) {
    if (isset($_GET['rating']) && $_GET['rating'] > 0 && $_GET['rating'] < 7) {
        $c =$db->query("SELECT COUNT(*) FROM `user_set` WHERE `id_user` = '$user[id]' AND `ocenka` > '$time'")->el();
        
        if ($c == 0 && $_GET['rating'] == 6) {
            $_SESSION['message'] = 'Необходимо активировать услугу';
            header("Location: /user/money/plus5.php");
            exit;
        }
    
        $db->query("INSERT INTO `gallery_rating` (`id_user`, `id_foto`, `like`, `time`, `avtor`) values('$user[id]', '$foto[id]', '" . intval($_GET['rating']) . "', '$time', $foto[id_user])", $db);
        $db->query("UPDATE `gallery_foto` SET `rating` = '" . ($foto['rating'] + intval($_GET['rating'])) . "' WHERE `id` = '$foto[id]' LIMIT 1", $db);
        $_SESSION['message'] = 'Ваша оценка принята';
        header("Location: ?");
        exit;
    }
}
/*
==========================
Комментарий
==========================
*/
if (isset($_POST['msg']) && isset($user)) {
    $msg = $_POST['msg'];
    if ($mat) {
        $err[] = 'В тексте сообщения обнаружен мат: '.$mat;
    }
    if (strlen2($msg) > 1024) {
        $err = 'Сообщение слишком длинное';
    } elseif (strlen2($msg) < 2) {
        $err = 'Короткое сообщение';
    } elseif ($db->query("SELECT COUNT(*) FROM `gallery_komm` WHERE `id_foto` = '$foto[id]' AND `id_user` = '$user[id]' AND `msg` = '".my_esc($msg)."' LIMIT 1")->el()) {
        $err = 'Ваше сообщение повторяет предыдущее';
    } elseif (!isset($err)) {
        
        // Начисление баллов за активность
        include_once H.'sys/add/user.active.php';
        /*
        ==========================
        Уведомления об ответах
        ==========================
        */
        
        if (isset($ank_reply['id'])) {
            $notifiacation =$db->query("SELECT * FROM `notification_set` WHERE `id_user` = '" . $ank_reply['id'] . "' LIMIT 1")->row();
            
            if ($notifiacation['komm'] == 1 && $ank_reply['id'] != $user['id']) {
                $db->query("INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES ('$user[id]', '$ank_reply[id]', '$foto[id]', 'foto_komm', '$time')");
            }
        }
                
        /*
        ====================================
        Обсуждения
        ====================================
        */
        // Отправляем друзьям
        $q =$db->query("SELECT * FROM `frends` WHERE `user` = '".$gallery['id_user']."' AND `i` = '1'");
        while ($f = $q->row()) {
            $a = get_user($f['frend']);
            $discSet =$db->query("SELECT * FROM `discussions_set` WHERE `id_user` = '".$a['id']."' LIMIT 1")->row(); // Общая настройка обсуждений
            
            if ($f['disc_foto'] == 1 && $discSet['disc_foto'] == 1) {
                if (!$db->query("SELECT COUNT(*) FROM `discussions` WHERE `id_user` = '$a[id]' AND `type` = 'foto' AND `id_sim` = '$foto[id]' LIMIT 1")->el()->el()) {
                    if ($a['id'] != $user['id'] || $a['id'] != $foto['id_user']) {
                        $db->query("INSERT INTO `discussions` (`id_user`, `avtor`, `type`, `time`, `id_sim`, `count`) values('$a[id]', '$gallery[id_user]', 'foto', '$time', '$foto[id]', '1')");
                    }
                } else {
                    $disc =$db->query("SELECT * FROM `discussions` WHERE `id_user` = '$a[id]' AND `type` = 'foto' AND `id_sim` = '$foto[id]' LIMIT 1")->row();
                    
                    if ($gallery['id_user'] != $user['id'] || $a['id'] != $foto['id_user']) {
                        $db->query("UPDATE `discussions` SET `count` = '" . ($disc['count'] + 1) . "', `time` = '$time' WHERE `id_user` = '$a[id]' AND `type` = 'foto' AND `id_sim` = '$foto[id]' LIMIT 1");
                    }
                }
            }
        }
        // Отправляем автору
        if (!$db->query("SELECT COUNT(*) FROM `discussions` WHERE `id_user` = '$gallery[id_user]' AND `type` = 'foto' AND `id_sim` = '$foto[id]' LIMIT 1")->el()->el()) {
            if ($gallery['id_user'] != $user['id']) {
                $db->query("INSERT INTO `discussions` (`id_user`, `avtor`, `type`, `time`, `id_sim`, `count`) values('$gallery[id_user]', '$gallery[id_user]', 'foto', '$time', '$foto[id]', '1')");
            }
        } else {
            $disc2 =$db->query("SELECT * FROM `discussions` WHERE `id_user` = '$gallery[id_user]' AND `type` = 'foto' AND `id_sim` = '$foto[id]' LIMIT 1")->row();
            if ($gallery['id_user'] != $user['id']) {
                $db->query("UPDATE `discussions` SET `count` = '".($disc2['count']+1)."', `time` = '$time' WHERE `id_user` = '$gallery[id_user]' AND `type` = 'foto' AND `id_sim` = '$foto[id]' LIMIT 1");
            }
        }
        
        
        $db->query("INSERT INTO `gallery_komm` (`id_foto`, `id_user`, `time`, `msg`) values('$foto[id]', '$user[id]', '$time', '".my_esc($msg)."')");
        $_SESSION['message'] = 'Сообщение успешно добавлено';
        header("Location: ?page=".intval($_GET['page']));
        exit;
    }
}
if ((user_access('foto_komm_del') || $ank['id'] == $user['id']) && isset($_GET['delete']) && $db->query("SELECT COUNT(*) FROM `gallery_komm` WHERE `id`='".intval($_GET['delete'])."' AND `id_foto`='$foto[id]' LIMIT 1")->el()) {
    $db->query("DELETE FROM `gallery_komm` WHERE `id`='".intval($_GET['delete'])."' LIMIT 1");
    admin_log('Фотогалерея', 'Фотографии', "Удаление комментария к фото [url=/id$ank[id]]" . user::nick($ank['id'], 0) . "[/url]");
    $_SESSION['message'] = 'Комментарий успешно удален';
    header("Location: ?page=".intval($_GET['page']));
    exit;
}
$set['title'] = text($gallery['name']) . ' - ' . text($foto['name']); // заголовок страницы
include_once '../sys/inc/thead.php';
title();
err();
aut();
echo '<div class="foot">';
echo '<img src="/style/icons/str2.gif" alt="*"> ' . user::nick($ank['id']) . ' | <a href="/foto/' . $ank['id'] . '/">Альбомы</a> | ';
echo '<a href="/foto/' . $ank['id'] . '/' . $gallery['id'] . '/">' . text($gallery['name']) . '</a> | ';
echo '<b>' . text($foto['name']) . '</b>';
if ($foto['metka'] == 1) {
    echo ' <font color=red>(18+)</font>';
}
echo '</div>';
// Подключаем приватность стр.
include H.'sys/add/user.privace.php';
/*
* Если установлена приватность альбома
*/
if ($gallery['privat'] == 1 && ($frend != 2 || !isset($user)) && $user['level'] <= $ank['level'] && $user['id'] != $ank['id']) {
    echo '<div class="mess">';
    echo 'Просматривать альбом пользователя могут только его друзья!';
    echo '</div>';
    $block_foto = true;
} elseif ($gallery['privat'] == 2 && $user['id'] != $ank['id'] && $user['level'] <= $ank['level']) {
    echo '<div class="mess">';
    echo 'Пользователь запретил просмотр этого альбома!';
    echo '</div>';
    
    $block_foto = true;
}
/*--------------------Альбом под паролем-------------------*/
if ($user['id'] != $ank['id'] && $gallery['pass'] != null) {
    if (isset($_POST['password'])) {
        $_SESSION['pass'] = my_esc($_POST['password']);
        
        if ($_SESSION['pass'] != $gallery['pass']) {
            $_SESSION['message'] = 'Неверный пароль';
            $_SESSION['pass'] = null;
        }
        header("Location: ?");
    }
    if (!isset($_SESSION['pass']) || $_SESSION['pass'] != $gallery['pass']) {
        echo '<form action="?" method="POST">Пароль:<br /><input type="pass" name="password" value="" /><br />		
		<input type="submit" value="Войти"/></form>';
        
        echo '<div class="foot">';
        echo '<img src="/style/icons/str2.gif" alt="*"> ' . user::nick($ank['id']) . ' | <a href="/foto/' . $ank['id'] . '/">Альбомы</a> | <b>' . text($gallery['name']) . '</b>';
        echo '</div>';
        include_once '../sys/inc/tfoot.php';
        exit;
    }
}
/*---------------------------------------------------------*/
if (!isset($block_foto)) {
    // +5 оценка
    $rat = $db->query("SELECT COUNT(*) FROM `gallery_rating` WHERE `id_foto` = $foto[id] AND `like` = '6'")->el();
    if (($user['abuld'] == 1 || $foto['metka'] == 0 || $foto['id_user'] == $user['id'])) { // Метка 18+
        echo '<div class="nav2">';
        if ($webbrowser == 'web' && $w > 128) {
            echo "<a href='/foto/foto0/$foto[id].$foto[ras]' title='Скачать оригинал'><img style='max-width:90%' src='/foto/foto640/$foto[id].$foto[ras]'/></a>";
            if ($rat > 0) {
                echo "<div style='display:inline;margin-left:-45px;vertical-align:top;'><img style='padding-top:15px;' src='/style/icons/5_plus.png'/></div>";
            }
        } else {
            echo "<a href='/foto/foto0/$foto[id].$foto[ras]' title='Скачать оригинал'><img src='/foto/foto128/$foto[id].$foto[ras]'/></a>";
            if ($rat > 0) {
                echo "<div style='display:inline;margin-left:-25px;vertical-align:top;'><img style='padding-top:10px;' src='/style/icons/6.png'/></div>";
            }
        }
        echo '</div>';
        
        /*
        ===============================
        Оценка фото
        ===============================
        */
        
        if (isset($user) && $user['id'] != $ank['id']) {
            echo '<div class="nav2">';
            if ($user['id']!=$ank['id'] && !$db->query("SELECT COUNT(*) FROM `gallery_rating` WHERE `id_user` = '$user[id]' AND `id_foto` = '$foto[id]'")->el()) {
                echo "<a href=\"?rating=6\" title=\"5+\"><img src='/style/icons/6.png' alt=''/></a>";
                echo "<a href=\"?rating=5\" title=\"5\"><img src='/style/icons/5.png' alt=''/></a>";
                echo "<a href=\"?rating=4\" title=\"4\"><img src='/style/icons/4.png' alt=''/></a>";
                echo "<a href=\"?rating=3\" title=\"3\"><img src='/style/icons/3.png' alt=''/></a>";
                echo "<a href=\"?rating=2\" title=\"2\"><img src='/style/icons/2.png' alt=''/></a>";
                echo "<a href=\"?rating=1\" title=\"1\"><img src='/style/icons/1.png' alt=''/></a>";
            } else {
                $rate =$db->query("SELECT * FROM `gallery_rating` WHERE `id_foto` = $foto[id] AND `id_user` = '$user[id]' LIMIT 1")->row();
            
                if (isset($user) && $user['id'] != $ank['id']) {
                    echo 'Ваша оценка <img src="/style/icons/' . $rate['like'] . '.png" alt=""/></a>';
                }
            }
            echo '</div>';
        }
    } elseif (!isset($user)) {
        echo '<div class="mess">';
        echo '<img src="/style/icons/small_adult.gif" alt="*"><br /> Данный файл содержит изображения эротического характера. Только зарегистрированные пользователи старше 18 лет могут просматривать такие файлы. <br />';
        echo '<a href="/aut.php">Вход</a> | <a href="/reg.php">Регистрация</a>';
        echo '</div>';
    } else {
        echo '<div class="mess">';
        echo '<img src="/style/icons/small_adult.gif" alt="*"><br /> 
		Данный файл содержит изображения эротического характера. 
		Если Вас это не смущает и Вам 18 или более лет, то можете <a href="?sess_abuld=1">продолжить просмотр</a>. 
		Или Вы можете отключить предупреждения в <a href="/user/info/settings.php">настройках</a>.';
        echo '</div>';
    }
    /*----------------------листинг-------------------*/
    $listr =$db->query("SELECT * FROM `gallery_foto` WHERE `id_gallery` = '$gallery[id]' AND `id` < '$foto[id]' ORDER BY `id` DESC LIMIT 1")->row();
    $list =$db->query("SELECT * FROM `gallery_foto` WHERE `id_gallery` = '$gallery[id]' AND `id` > '$foto[id]' ORDER BY `id`  ASC LIMIT 1")->row();
    echo '<div class="c2" style="text-align: center;">';
    echo '<span class="page">' . ($list['id'] ? "<a href='/foto/$ank[id]/$gallery[id]/$list[id]/'>&laquo; Пред.</a>" : "&laquo; Пред.") . '</span>';
    $k_1 =$db->query("SELECT COUNT(*) FROM `gallery_foto` WHERE `id` > '$foto[id]' AND `id_gallery` = '$gallery[id]'")->el() + 1;
    $k_2 =$db->query("SELECT COUNT(*) FROM `gallery_foto` WHERE `id_gallery` = '$gallery[id]'")->el();
    echo ' (' . $k_1 . ' из ' . $k_2 . ') ';
    echo '<span class="page">' . ($listr['id'] ? "<a href='/foto/$ank[id]/$gallery[id]/$listr[id]/'>След. &raquo;</a>" : "След. &raquo;") . '</span>';
    echo '</div>';
    /*----------------------alex-borisi---------------*/
    if (($user['abuld'] == 1 || $foto['metka'] == 0 || $foto['id_user'] == $user['id'])) {
        if (isset($user)) {
            echo '<div class="nav1">';
            echo '<img src="/style/icons/fav.gif" alt="*" /> ';
            if (!$db->query("SELECT COUNT(*) FROM `bookmarks` WHERE `id_user` = '" . $user['id'] . "' AND `id_object` = '" . $foto['id'] . "' AND `type`='fot' LIMIT 1")->el()) {
                echo '<a href="?fav=1&amp;page=' . $pageEnd . '">Добавить в закладки</a><br />';
            } else {
                echo '<a href="?fav=0&amp;page=' . $pageEnd . '">Удалить из закладок</a><br />';
            }
            echo 'В закладках у (' .$db->query("SELECT COUNT(*) FROM `bookmarks` WHERE `id_user` = '" . $user['id'] . "' AND `id_object` = '" . $foto['id'] . "' AND `type`='foto' LIMIT 1")->el() . ') чел.';
            echo '</div>';
        }
        echo '<div class="main">';
        echo 'Тип: <b>' . $foto['ras'] . '</b>, ' . $w . 'x' . $h . ' <br />';
        if ($foto['opis'] != null) {
            echo output_text($foto['opis']) . '<br />';
        }
        
        $size_file = is_file(H.'sys/gallery/foto/'.$foto['id'].'.jpg') ? size_file(filesize(H.'sys/gallery/foto/'.$foto['id'].'.jpg')) : 0;
        
        echo '<img src="/style/icons/d.gif" alt="*"> <a href="/foto/foto0/' . $foto['id'] . '.' . $foto['ras'] . '" title="Скачать оригинал">';
        echo 'Скачать';
        echo ' (' . $size_file . ')';
        echo '</a><br />';
        echo '</div>';
        if (user_access('foto_foto_edit') && $ank['level'] < $user['level'] || isset($user) && $ank['id'] == $user['id']) {
            include 'inc/gallery_show_foto_form.php';
        }
    }
    $k_post =$db->query("SELECT COUNT(*) FROM `gallery_komm` WHERE `id_foto` = '$foto[id]'")->el();
    $k_page = k_page($k_post, $set['p_str']);
    $page = page($k_page);
    $start = $set['p_str']*$page-$set['p_str'];
    echo '<div class="foot">';
    echo 'Комментарии:';
    echo '</div>';
    if ($k_post == 0) {
        echo '<div class="mess">';
        echo 'Нет сообщений';
        echo '</div>';
    } else {
        /*------------сортировка по времени--------------*/
        if (isset($user)) {
            echo '<div id="comments" class="menus">';
            echo '<div class="webmenu">';
            echo '<a href="?page=' . $page . '&amp;sort=1" class="' . ($user['sort'] == 1 ? 'activ' : null) . '">Внизу</a>';
            echo '</div>';
            
            echo '<div class="webmenu">';
            echo '<a href="?page=' . $page . '&amp;sort=0" class="' . ($user['sort'] == 0 ? 'activ' : null) . '">Вверху</a>';
            echo '</div>';
            echo '</div>';
        }
        /*---------------alex-borisi---------------------*/
    }
    $q =$db->query("SELECT * FROM `gallery_komm` WHERE `id_foto` = '$foto[id]' ORDER BY `id` $sort LIMIT $start, $set[p_str]");
    while ($post = $q->row()) {
        $ank2 =$db->query("SELECT * FROM `user` WHERE `id` = '$post[id_user]' LIMIT 1")->row();
        // Лесенка
        echo '<div class="' . ($num % 2 ? "nav1" : "nav2") . '">';
        $num++;
        
        echo group($ank2['id']) . user::nick($ank2['id']);
        
        if (isset($user) && $user['id'] != $ank2['id']) {
            echo ' <a href="?response=' . $ank2['id'] . '&amp;page=' . $page . '">[*]</a> ';
        }
        
        echo medal($ank2['id']) . online($ank2['id']) . ' (' . vremja($post['time']) . ')<br />';
        
        $postBan =$db->query("SELECT COUNT(*) FROM `ban` WHERE (`razdel` = 'all' OR `razdel` = 'foto') AND `post` = '1' AND `id_user` = '$ank2[id]' AND (`time` > '$time' OR `navsegda` = '1')")->el();
        
        // Блок сообщения
        if ($postBan == 0) {
            echo output_text($post['msg']);
        } else {
            echo output_text($banMess) . '<br />';
        }
        if (isset($user)) {
            echo '<div class="right">';
            if (user_access('foto_komm_del') || $ank['id'] == $user['id']) {
                echo '<a rel="delete" href="?delete=' . $post['id'] . '&amp;page=' . $page . '" title="Удалить комментарий"><img src="/style/icons/delete.gif" alt="*"></a>';
            }
            echo '</div>';
        }
        echo '</div>';
    }
    if ($k_page > 1) {
        str('?', $k_page, $page);
    } // Вывод страниц
    if (isset($user)) {
        echo '<form method="post" name="message" action="?page=' . $pageEnd . '&amp;' . $go_link . '">';
        if (is_file(H.'style/themes/'.$set['set_them'].'/altername_post_form.php')) {
            include_once H.'style/themes/'.$set['set_them'].'/altername_post_form.php';
        } else {
            echo $tPanel . '<textarea name="msg">' . $insert . '</textarea><br />';
        }
        echo '<input value="Отправить" type="submit" />';
        echo '</form>';
    }
}
echo '<div class="foot">';
echo '<img src="/style/icons/str2.gif" alt="*"> ' . user::nick($ank['id']) . ' | <a href="/foto/' . $ank['id'] . '/">Альбомы</a> | ';
echo '<a href="/foto/' . $ank['id'] . '/' . $gallery['id'] . '/">' . text($gallery['name']) . '</a> | ';
echo '<b>' . text($foto['name']) . '</b>';
if ($foto['metka'] == 1) {
    echo ' <font color=red>(18+)</font>';
}
echo '</div>';
include_once '../sys/inc/tfoot.php';
exit;
