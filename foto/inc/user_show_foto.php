<?php
if (!isset($user) && !$input_get['id_user']) {
    header('Location: /foto/?' . SID);
    exit;
}
if (isset($user)) {
    $ank['id'] = $user['id'];
}
if ($input_get['id_user']) {
    $ank['id'] = $input_get['id_user'];
}

$ank = $db->query('SELECT `id`, `nick`, `level`, `group_access` FROM `user` WHERE `id`=?i', [$ank['id']])->row();

if (!$ank) {
    header('Location: /foto/?' . SID);
    exit;
}

// Бан пользователя
if (isset($user) && $db->query(
    "SELECT COUNT(*) FROM `ban` WHERE `razdel`=? AND `id_user`=?i AND (`time`>?i OR `view`=? OR `navsegda`=?i)",
                               ['foto', $user['id'], $time, '0', 1]
)->el()) {
    $_SESSION['message'] = 'Доступ к альбомам запрещен';
    header('Location: /ban.php?'.SID);
    exit;
}

$gallery['id'] = $input_get['id_gallery'];

if (!$db->query(
    "SELECT COUNT(*) FROM `gallery` WHERE `id`=?i AND `id_user`=?i",
                [$gallery['id'], $ank['id']]
)->el()) {
    header('Location: /foto/' . $ank['id'] . '/?' . SID);
    exit;
}
$gallery = $db->query(
    "SELECT * FROM `gallery` WHERE `id`=?i AND `id_user`=?i",
                [$gallery['id'], $ank['id']]
)->row();

$foto['id'] = $input_get['id_foto'];

if (!$db->query("SELECT COUNT(*) FROM `gallery_foto` WHERE `id`=?i", [$foto['id']])->el()) {
    header('Location: /foto/' . $ank['id'] . '/' . $gallery['id'] . '/?' . SID);
    exit;
}

$foto = $db->query("SELECT * FROM `gallery_foto` WHERE `id`=?i", [$foto['id']])->row();

// Закладки

// Добавляем в закладки
if (isset($input_get['fav'])) {
    if ($input_get['fav'] <> 0 && !$db->query(
        "SELECT COUNT( * ) FROM `bookmarks` WHERE `id_user`=?i AND `id_object`=?i AND `type`=?",
                    [$user['id'], $foto['id'], 'foto']
    )->el()) {
        $db->query(
            "INSERT INTO `bookmarks` (`type`,`id_object`, `id_user`, `time`) VALUES (?, ?i, ?i, ?i)",
                   ['foto', $foto['id'], $user['id'], $time]
        );
        $_SESSION['message'] = 'Фото добавлено в закладки';
        header('Location: /foto/' . $ank['id'] . '/' . $gallery['id'] . '/' . $foto['id'] . '/?page=' . $input_get['page']);
        exit;
    } elseif ($input_get['fav'] == 0 && $db->query(
        "DELETE FROM `bookmarks` WHERE `id_user`=?i AND `id_object`=?i AND `type`=?",
                    [$user['id'], $foto['id'], 'foto']
    )) {
        $_SESSION['message'] = 'Фото удалено из закладок';
        header('Location: /foto/' . $ank['id'] . '/' . $gallery['id'] . '/' . $foto['id'] . '/?page=' . $input_get['page']);
        exit;
    }
}
// TODO: что еще за хрень?
$IS = [0, 0];
if (is_file(H . 'sys/gallery/foto/' . $foto['id'] . '.' . $foto['ras'])) {
    $IS = getimagesize(H . 'sys/gallery/foto/' . $foto['id'] . '.' . $foto['ras']);
}
printf("", $IS[0], $IS[1]);
$w = $IS[0];
$h = $IS[1];
if (isset($user) && (user_access('foto_foto_edit') ||  $ank['id'] == $user['id'])) {
    include 'inc/gallery_show_foto_act.php';
}

// очищаем счетчик этого обсуждения
if (isset($user)) {
    $db->query(
        "UPDATE `discussions` SET `count`=?i WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i LIMIT ?i",
               [0, $user['id'], 'foto', $foto['id'], 1]
    );
    $db->query(
        "UPDATE `notification` SET `read`=? WHERE `type`=? AND `id_user`=?i AND `id_object`=?i",
               ['1', 'foto_komm', $user['id'], $foto['id']]
    );
}
// Оценка к фото
if (isset($user) && $user['id'] != $ank['id'] && !$db->query(
    "SELECT COUNT(*) FROM `gallery_rating` WHERE `id_user`=?i AND `id_foto`=?i",
                                                             [$user['id'], $foto['id']]
)->el()) {
    if (isset($input_get['rating']) && $input_get['rating'] > 0 && $input_get['rating'] < 7) {
        if (!$db->query(
            "SELECT COUNT(*) FROM `user_set` WHERE `id_user`=?i AND `ocenka`>?i",
                       [$user['id'], $time]
        )->el() && $input_get['rating'] == 6) {
            $_SESSION['message'] = 'Необходимо активировать услугу';
            header('Location: /user/money/plus5.php');
            exit;
        }
    
        $db->query(
            "INSERT INTO `gallery_rating` (`id_user`, `id_foto`, `like`, `time`, `avtor`) VALUES(?i, ?i', ?i, ?i, ?i)",
                   [$user['id'], $foto['id'], $input_get['rating'], $time, $foto['id_user']]
        );
        $db->query(
            "UPDATE `gallery_foto` SET `rating`=`rating`+?i WHERE `id`=?i",
                   [$input_get['rating'], $foto['id']]
        );
        
        $_SESSION['message'] = 'Ваша оценка принята';
        header('Location: ?');
        exit;
    }
}

// Комментарий
if (isset($input_post['msg']) && isset($user)) {
    $msg = $input_post['msg'];
    $mat = antimat($msg);
    if ($mat) {
        $err[] = 'В тексте сообщения обнаружен мат: '.$mat;
    }
    if (strlen2($msg) > 1024) {
        $err = 'Сообщение слишком длинное';
    } elseif (strlen2($msg) < 2) {
        $err = 'Короткое сообщение';
    } elseif ($db->query(
        "SELECT COUNT(*) FROM `gallery_komm` WHERE `id_foto`=?i AND `id_user`=?i AND `msg`=?",
                         [$foto['id'], $user['id'], $msg]
    )->el()) {
        $err = 'Ваше сообщение повторяет предыдущее';
    } elseif (!isset($err)) {
        
        // Начисление баллов за активность
        include_once H.'sys/add/user.active.php';
        // Уведомления об ответах
        if (isset($ank_reply['id'])) {
            if ($db->query(
                "SELECT `komm` FROM `notification_set` WHERE `id_user`=?i LIMIT ?i",
                                       [$ank_reply['id'], 1]
            )->el() && $ank_reply['id'] != $user['id']) {
                $db->query(
                    "INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES (?i, ?i, ?i, ?, ?i)",
                           [$user['id'], $ank_reply['id'], $foto['id'], 'foto_komm', $time]
                );
            }
        }
                
        // Обсуждения
        
        // Отправляем друзьям
        $q = $db->query(
            "SELECT fr.user, fr.disc_foto, dsc.disc_foto as dsc_foto FROM `frends` fr 
JOIN discussions_set dsc ON dsc.id_user=fr.user
WHERE fr.`frend`=?i AND fr.`disc_foto`=?i AND `i`=?i",
                        [$gallery['id_user'], 1, 1]);
        while ($frend = $q->row()) {
            if ($frend['disc_foto'] == 1 && $frend['dsc_foto'] == 1) {
                if (!$db->query(
                    "SELECT COUNT(*) FROM `discussions` WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i",
                                [$frend['user'], 'foto', $foto['id']]
                )->el()) {
                    if ($frend['user'] != $user['id'] || $frend['user']  != $foto['id_user']) {
                        $db->query(
                            "INSERT INTO `discussions` (`id_user`, `avtor`, `type`, `time`, `id_sim`, `count`) VALUES(?i, ?i, ?, ?i, ?i, ?i)",
                                   [$frend['user'], $gallery['id_user'], 'foto', $time, $foto['id'], 1]
                        );
                    }
                } else {
                    if ($gallery['id_user'] != $user['id'] || $a['id'] != $foto['id_user']) {
                        $db->query(
                            "UPDATE `discussions` SET `count`=`count`+?i, `time`=?i WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i LIMIT ?i",
                                   [1, $time, $frend['user'], 'foto', $foto['id'],  1]
                        );
                    }
                }
            }
        }
        // Отправляем автору
        if (!$db->query(
            "SELECT COUNT(*) FROM `discussions` WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i",
                                [$gallery['id_user'], 'foto', $foto['id']]
        )->el()) {
            if ($gallery['id_user'] != $user['id']) {
                $db->query(
                            "INSERT INTO `discussions` (`id_user`, `avtor`, `type`, `time`, `id_sim`, `count`) VALUES(?i, ?i, ?, ?i, ?i, ?i)",
                                   [$gallery['id_user'], $gallery['id_user'], 'foto', $time, $foto['id'], 1]
                        );
            }
        } else {
            if ($gallery['id_user'] != $user['id']) {
                $db->query(
                            "UPDATE `discussions` SET `count`=`count`+?i, `time`=?i WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i LIMIT ?i",
                                   [1, $time, $gallery['id_user'], 'foto', $foto['id'],  1]
                        );
            }
        }
        
        
        $db->query(
            "INSERT INTO `gallery_komm` (`id_foto`, `id_user`, `time`, `msg`) VALUES(?i, ?i, ?i, ?)",
                   [$foto['id'], $user['id'], $time, $msg]
        );
        $_SESSION['message'] = 'Сообщение успешно добавлено';
        header("Location: ?page=".$input_get['page']);
        exit;
    }
}
if ((user_access('foto_komm_del') || $ank['id'] == $user['id']) && isset($input_get['delete'])
    && $db->query(
        "SELECT COUNT(*) FROM `gallery_komm` WHERE `id`=?i AND `id_foto`=?i",
                  [$input_get['delete'], $foto['id']]
    )->el()) {
    $db->query(
        "DELETE FROM `gallery_komm` WHERE `id`=?i",
               [$input_get['delete']]
    );
    
    admin_log('Фотогалерея', 'Фотографии', 'Удаление комментария к фото [url=/id' . $ank['id'] . ']' . $ank['nick'] . '[/url]');
    
    $_SESSION['message'] = 'Комментарий успешно удален';
    header('Location: ?page=' . $input_get['page']);
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
if ($gallery['privat'] == 1 && ($frends['frend'] != 2 || !isset($user)) && $user['level'] <= $ank['level'] && $user['id'] != $ank['id']) {
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
    if (isset($input_post['password'])) {
        $_SESSION['pass'] = trim($input_post['password']);
        
        if ($_SESSION['pass'] != $gallery['pass']) {
            $_SESSION['message'] = 'Неверный пароль';
            $_SESSION['pass'] = null;
        }
        header('Location: ?');
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
        
        // Оценка фото
        if (isset($user) && $user['id'] != $ank['id']) {
            echo '<div class="nav2">';
            if ($user['id'] != $ank['id'] && !$db->query(
                "SELECT COUNT(*) FROM `gallery_rating` WHERE `id_user`=?i AND `id_foto`=?i",
                                                         [$user['id'], $foto['id']]
            )->el()) {
                echo "<a href=\"?rating=6\" title=\"5+\"><img src='/style/icons/6.png' alt=''/></a>";
                echo "<a href=\"?rating=5\" title=\"5\"><img src='/style/icons/5.png' alt=''/></a>";
                echo "<a href=\"?rating=4\" title=\"4\"><img src='/style/icons/4.png' alt=''/></a>";
                echo "<a href=\"?rating=3\" title=\"3\"><img src='/style/icons/3.png' alt=''/></a>";
                echo "<a href=\"?rating=2\" title=\"2\"><img src='/style/icons/2.png' alt=''/></a>";
                echo "<a href=\"?rating=1\" title=\"1\"><img src='/style/icons/1.png' alt=''/></a>";
            } else {
                $rate =$db->query(
                    "SELECT * FROM `gallery_rating` WHERE `id_foto` = $foto[id] AND `id_user` = '$user[id]' LIMIT 1",
                                  [$foto['id'], $user['id'], 1]
                )->row();
            
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
    
    // листинг
    $listr =$db->query("SELECT * FROM `gallery_foto` WHERE `id_gallery` = '$gallery[id]' AND `id` < '$foto[id]' ORDER BY `id` DESC LIMIT 1")->row();
    $list =$db->query("SELECT * FROM `gallery_foto` WHERE `id_gallery` = '$gallery[id]' AND `id` > '$foto[id]' ORDER BY `id`  ASC LIMIT 1")->row();
    echo '<div class="c2" style="text-align: center;">';
    echo '<span class="page">' . ($list['id'] ? "<a href='/foto/$ank[id]/$gallery[id]/$list[id]/'>&laquo; Пред.</a>" : "&laquo; Пред.") . '</span>';
    $cnt = $db->query(
        'SELECT * FROM (
SELECT COUNT( * )+1 AS back FROM `gallery_foto` WHERE `id`>?i AND `id_gallery`=?i)q, (
SELECT COUNT( * ) AS next FROM `gallery_foto` WHERE `id_gallery`=?i)q2',
                    [$foto['id'], $gallery['id'], $gallery['id']]
    )->row();
    echo ' (' . $cnt['back'] . ' из ' . $cnt['next'] . ') ';
    echo '<span class="page">' . ($listr['id'] ? "<a href='/foto/$ank[id]/$gallery[id]/$listr[id]/'>След. &raquo;</a>" : "След. &raquo;") . '</span>';
    echo '</div>';
    
    // alex-borisi
    if (($user['abuld'] == 1 || $foto['metka'] == 0 || $foto['id_user'] == $user['id'])) {
        if (isset($user)) {
            echo '<div class="nav1">';
            echo '<img src="/style/icons/fav.gif" alt="*" /> ';
            if (!$db->query(
                "SELECT COUNT(*) FROM `bookmarks` WHERE `id_user`=?i AND `id_object`=?i AND `type`=?",
                            [$user['id'], $foto['id'], 'foto']
            )->el()) {
                echo '<a href="?fav=1&amp;page=' . $pageEnd . '">Добавить в закладки</a><br />';
            } else {
                echo '<a href="?fav=0&amp;page=' . $pageEnd . '">Удалить из закладок</a><br />';
            }
            echo 'В закладках у (' .$db->query(
                "SELECT COUNT(*) FROM `bookmarks` WHERE `id_user`=?i AND `id_object`=?i AND `type`=?",
                                               [$user['id'], $foto['id'], 'foto']
            )->el() . ') чел.';
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
    $k_post =$db->query("SELECT COUNT(*) FROM `gallery_komm` WHERE `id_foto`=?i", [$foto['id']])->el();
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
        // сортировка по времени
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
    }

    $q =$db->query(
        "SELECT glk.*, u.id AS id_user, (
SELECT COUNT(*) FROM `ban` WHERE (`razdel`='all' OR `razdel`='foto') AND `post`=1 AND `id_user`=glk.id_user AND (`time`>" . time() . " OR `navsegda`=1)) AS ban
FROM `gallery_komm` glk
JOIN `user` u ON u.id=glk.id_user
WHERE glk.`id_foto`=?i ORDER BY glk.`id`?q LIMIT ?i OFFSET ?i",
                   [$foto['id'], $sort, $set['p_str'], $start]
    );
    while ($post = $q->row()) {

        // Лесенка
        echo '<div class="' . ($num % 2 ? "nav1" : "nav2") . '">';
        $num++;
        
        echo group($post['id_user']) . user::nick($post['id_user']);
        
        if (isset($user) && $user['id'] != $post['id_user']) {
            echo ' <a href="?response=' . $post['id_user'] . '&amp;page=' . $page . '">[*]</a> ';
        }
        
        echo medal($post['id_user']) . online($post['id_user']) . ' (' . vremja($post['time']) . ')<br />';
    
        // Блок сообщения
        if ($post['ban'] == 0) {
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
    // Вывод страниц
    if ($k_page > 1) {
        str('?', $k_page, $page);
    }

    if (isset($user)) {
        if ($gallery['privat_komm'] == 1 && ($frends['frend'] != 2 || !isset($user)) && $user['level'] <= $ank['level'] && $user['id'] != $ank['id']) {
            echo '<div class="mess">';
            echo 'Комментировать альбом пользователя могут только его друзья!';
            echo '</div>';
            $block_foto = true;
        } elseif ($gallery['privat_komm'] == 2 && $user['id'] != $ank['id'] && $user['level'] <= $ank['level']) {
            echo '<div class="mess">';
            echo 'Пользователь запретил комментировать альбом!';
            echo '</div>';
    
            $block_foto = true;
        } else {
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
