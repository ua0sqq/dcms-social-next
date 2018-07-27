<?php

if (isset($input_get['act']) && $input_get['act'] == 'txt') {
    ob_clean();
    ob_implicit_flush();
    header('Content-Type: text/plain; charset=utf-8', true);
    header('Content-Disposition: attachment; filename="' . retranslit($them['name']) . '.txt";');

    echo 'Тема: ' . $them['name'] . ' (' . $forum['name'] . '/' . $razdel['name'] . ')' . PHP_EOL;
    $q = $db->query(
        "SELECT `pst`.*, `u`.nick AS nick_post, `pst2`.msg AS msg_cit, `pst2`.`time` AS time_cit, `cit`.nick AS nick_cit 
FROM `forum_p` `pst`
LEFT JOIN `user` `u` ON `pst`.id_user=`u`.id
LEFT JOIN forum_p `pst2` ON `pst`.cit=`pst2`.id
LEFT JOIN `user` `cit` ON `pst2`.id_user=`cit`.id
WHERE `pst`.`id_them`=?i AND `pst`.`id_forum`=?i  AND `pst`.`id_razdel`=?i ORDER BY `pst`.`time` ASC",
[$them['id'], $forum['id'], $razdel['id']]
    );
    while ($post = $q->row()) {
        echo PHP_EOL;
        echo $post['nick_post'] . '(' . date("j M Y в H:i", $post['time']) . ')' . PHP_EOL;
        if (!empty($post['cit'])) {
            echo '--Цитата--' . PHP_EOL;
            echo $post['nick_cit'] . '(' . date("j M Y в H:i", $post['time_cit']) . '):' . PHP_EOL;
            // Удаляем теги нахрен
            $msg_cit = preg_replace('/\[\/?(\w+).*?\]/is', '', $post['msg_cit']);
            echo trim(br($msg_cit, PHP_EOL)) . PHP_EOL;
            echo '----------' . PHP_EOL;
        }
        // Удаляем теги нахрен
        $msg = preg_replace('/\[\/?(\w+).*?\]/is', '', $post['msg']);
        echo trim(br($msg, PHP_EOL)) . PHP_EOL;
    }
    echo PHP_EOL . 'Источник: http://' . $_SERVER['SERVER_NAME'] . '/forum/' . $forum['id'] . '/' . $razdel['id'] . '/' . $them['id'] . '/' . PHP_EOL;
    exit;
}
    
if (isset($user) && isset($input_get['f_del']) && /*is_numeric($input_get['f_del']) && */isset($_SESSION['file'][$input_get['f_del']])) {
    @unlink($_SESSION['file'][$input_get['f_del']]['tmp_name']);
}
if (isset($user) && isset($input_get['zakl']) && $input_get['zakl'] == 1) {
    if ($db->query(
        "SELECT COUNT( * ) FROM `bookmarks` WHERE `id_user`=?i AND `type`=? AND `id_object`=?i",
                   [$user['id'], 'forum', $them['id']]
    )->el()) {
        $err[] = 'Тема уже есть в ваших закладках';
    } else {
        $db->query(
            "INSERT INTO `bookmarks` (`id_user`, `time`,  `id_object`, `type`) VALUES(?i, ?i, ?i, ?)",
                   [$user['id'], $time, $them['id'], 'forum']
        );
        msg('Тема добавлена в закладки');
    }
} elseif (isset($user) && isset($input_get['zakl']) && $input_get['zakl'] == 0) {
    $db->query(
        "DELETE FROM `bookmarks` WHERE `id_user`=?i AND `type`=? AND `id_object`=?i",
               [$user['id'], 'forum', $them['id']]
    );
    msg('Тема удалена из закладок');
}
if (isset($user) && isset($input_get['act']) && $input_get['act'] == 'new' && isset($_FILES['file_f'])
    && preg_match('#\.#', $_FILES['file_f']['name']) && isset($_POST['file_s'])) {
    
    copy($_FILES['file_f']['tmp_name'], H . 'sys/tmp/' . $user['id'] . '_' . md5_file($_FILES['file_f']['tmp_name']) . '.forum.tmp');
    
    if (isset($_SESSION['file'])) {
        $next_f = count($_SESSION['file']);
    } else {
        $next_f=0;
    }
    $file = esc($_FILES['file_f']['name']);
    $_SESSION['file'][$next_f]['name'] = preg_replace('#\.[^\.]*$#i', null, $file); // имя файла без расширения
    $_SESSION['file'][$next_f]['ras'] = strtolower(preg_replace('#^.*\.#i', null, $file));
    $_SESSION['file'][$next_f]['tmp_name'] = H . 'sys/tmp/' . $user['id'] . '_' . md5_file($_FILES['file_f']['tmp_name']) . '.forum.tmp';
    $_SESSION['file'][$next_f]['size'] = filesize(H . 'sys/tmp/' . $user['id'] . '_' . md5_file($_FILES['file_f']['tmp_name']) . '.forum.tmp');
    $_SESSION['file'][$next_f]['type'] = $_FILES['file_f']['type'];
}

if (isset($user) && ($them['close'] == 0 || $them['close'] == 1 && user_access('forum_post_close')) && isset($input_get['act']) && $input_get['act'] == 'new' && isset($_POST['msg']) && !isset($_POST['file_s'])) {
    $msg = trim($_POST['msg']);           
    if (strlen2($msg) < 2) {
        $err = 'Короткое сообщение';
    }
    if (strlen2($msg) > 1024) {
        $err = 'Длина сообщения превышает предел в 1024 символа';
    }
    $mat = antimat($msg);
    if ($mat) {
        $err[] = 'В тексте сообщения обнаружен мат: ' . $mat;
    }
    if ($db->query(
        'SELECT COUNT( * ) FROM `forum_p` WHERE `id_them`=?i AND `id_forum`=?i AND `id_razdel`=?i AND `id_user`=?i AND `msg`=?',
                   [$them['id'], $forum['id'], $razdel['id'], $user['id'], $msg]
    )->el()) {
        $err = 'Ваше сообщение повторяет предыдущее';
    }
    if (!isset($err)) {
        if (isset($_POST['cit']) && is_numeric($_POST['cit'])
            && $db->query(
                "SELECT COUNT( * ) FROM `forum_p` WHERE `id`=?i AND `id_them`=?i AND `id_razdel`=?i AND `id_forum`=?i",
                          [$_POST['cit'], $input_get['id_them'], $input_get['id_razdel'], $input_get['id_forum']]
            )->el()) {
            $cit = $_POST['cit'];
        } else {
            $cit = null;
        }
        $db->query('UPDATE `user` SET `balls`=`balls`+?i WHERE `id`=?i', [1, $user['id']]);
        $db->query(
            'UPDATE `forum_zakl` SET `time_obn`=?i WHERE `id_them`=?i',
                   [$time, $them['id']]
        );
        $post_id = $db->query(
            "INSERT INTO `forum_p` (`id_forum`, `id_razdel`, `id_them`, `id_user`, `msg`, `time`, `cit`) VALUES(?i, ?i, ?i, ?i, ?, ?i, ?in)",
                              [$forum['id'], $razdel['id'], $them['id'], $user['id'], $msg, $time, $cit]
        )->id();

        if (isset($_SESSION['file']) && isset($user)) {
            for ($i = 0; $i < count($_SESSION['file']); $i++) {
                if (isset($_SESSION['file'][$i]) && is_file($_SESSION['file'][$i]['tmp_name'])) {
                    $file_id = $db->query(
                        "INSERT INTO `forum_files` (`id_post`, `name`, `ras`, `size`, `type`) VALUES(?i, ?, ?, ?i, ?)",
                                          [$post_id, $_SESSION['file'][$i]['name'], $_SESSION['file'][$i]['ras'], $_SESSION['file'][$i]['size'], $_SESSION['file'][$i]['type']]
                    )->id();
                    copy($_SESSION['file'][$i]['tmp_name'], H . 'sys/forum/files/' . $file_id . '.frf');
                    unlink($_SESSION['file'][$i]['tmp_name']);
                }
            }
            unset($_SESSION['file']);
        }
        unset($_SESSION['msg']);

        $db->query("UPDATE `user` SET `rating_tmp`=`rating_tmp`+1 WHERE `id`=?i", [$user['id']]);
        $db->query("UPDATE `forum_t` SET `time`=?i WHERE `id`=?i", [time(), $them['id']]);
        $db->query(
            "UPDATE `forum_r` SET `time`=?i WHERE `id`=?i",
                   [$time, $razdel['id']]
        );
   
        // Обсуждения
        $q = $db->query(
            "SELECT `frn`.`disc_forum`, `u`.`id`, `dsc`.`disc_forum` AS `dscforum`
FROM `frends` `frn` JOIN `user` `u` ON `u`.`id`=`frn`.`frend`
JOIN `discussions_set` `dsc` ON `dsc`.`id_user`=`u`.`id` WHERE `frn`.`user`=?i AND `frn`.`i`=?i AND `frn`.`frend`<>?i",
                        [$them['id_user'], 1, $user['id']]
        );
        while ($frend = $q->row()) {
            // Фильтр рассылкi
            if ($frend['disc_forum'] == 1 && $frend['dscforum'] == 1) {
                // друзьям автора
                if ($them['id_user'] != $frend['id']/* || $frend['id'] != $user['id']*/) {
                    if (!$db->query(
                    "SELECT COUNT( * ) FROM `discussions` WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i",
                                [$frend['id'], 'them', $them['id']]
                )->el()) {
                        $db->query(
                        "INSERT INTO `discussions` (`id_user`, `avtor`, `type`, `time`, `id_sim`, `count`) VALUES(?i, ?i, ?, ?i, ?i, ?i)",
                                   [$frend['id'], $them['id_user'], 'them', $time, $them['id'], 1]
                    );
                    } else {
                        $db->query(
                        "UPDATE `discussions` SET `count`=`count`+1, `time`=?i WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i LIMIT ?i",
                                   [$time, $frend['id'], 'them', $them['id'], 1]
                    );
                    }
                }
            }
        }
        // отправляем автору
        if (!$db->query(
            "SELECT COUNT( * ) FROM `discussions` WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i",
                        [$them['id_user'], 'them', $them['id']]
        )->el()) {
            if ($them['id_user'] != $user['id'] && $them['id_user'] != $ank_reply['id']) {
                $db->query(
                    "INSERT INTO `discussions` (`id_user`, `avtor`, `type`, `time`, `id_sim`, `count`) VALUES(?i, ?i, ?, ?i, ?i, ?i)",
                           [$them['id_user'], $them['id_user'], 'them', $time, $them['id'], 1]
                );
            }
        } else {
            if ($them['id_user'] != $user['id'] && $them['id_user'] != $ank_reply['id']) {
                $db->query(
                    "UPDATE `discussions` SET `count`=`count`+1, `time`=?i WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i LIMIT ?i",
                           [$time, $them['id_user'], 'them', $them['id'], 1]
                );
            }
        }
        
        // Уведомления об ответах
        if (isset($user) && ($respons == true || isset($_POST['cit']))) {
            // 	Уведомление при цитате
            if (isset($_POST['cit'])) {
                $cit2=$db->query("SELECT * FROM `forum_p` WHERE `id`=?i",
                                 [$cit])->row();
                $ank_reply['id'] = $cit2['id_user'];
            }
        
            if ($db->query(
                "SELECT COUNT( * ) FROM `notification_set` WHERE `komm`=1 AND `id_user`=?i",
                           [$ank_reply['id']]
            )->el()) {
                $db->query(
                    "INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES (?i, ?i, ?i, ?, ?i)",
                           [$user['id'], $ank_reply['id'], $them['id'], 'them_komm', $time]
                );
            }
        }
    
        $_SESSION['message'] = 'Сообщение успешно добавлено';
        header('Location: ?page=' . $input_get['page']);
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
if (isset($input_get['spam']) && isset($user)) {
    $mess = $db->query(
        "SELECT pst.id, pst.id_user, pst.msg, pst.`time`, u.nick FROM `forum_p` pst
JOIN `user` u ON u.id=pst.id_user WHERE `pst`.`id`=?i",
                       [$input_get['spam']]
    )->row();
    if (!$db->query(
        "SELECT COUNT( * ) FROM `spamus` WHERE `id_user`=?i AND `id_spam`=?i AND `razdel`=? AND `spam`=?",
                    [$user['id'], $mess['id_user'], 'forum', $mess['msg']]
    )->el()) {
        if (isset($_POST['spamus'])) {
            if ($mess['id_user'] != $user['id']) {
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
                        "INSERT INTO `spamus` (`id_object`, `id_user`, `msg`, `id_spam`, `time`, `types`, `razdel`, `spam`)
                               VALUES(?i, ?i, ?, ?i, ?i, ?i, ?, ?)",
                               [$them['id'], $user['id'], $msg, $mess['id_user'], $time, $types, 'forum', $mess['msg']]
                    );
                    $_SESSION['message'] = 'Заявка на рассмотрение отправлена';
                    header('Location: /forum/' . $forum['id'] . '/' . $razdel['id'] . '/' . $them['id'] . '/?page=' . $pageEnd);
                    exit;
                }
            }
        }
    } else {
        $_SESSION['err'] = 'Хватит стучать!';
        header('Location: /forum/' . $forum['id'] . '/' . $razdel['id'] . '/' . $them['id'] . '/?page=' . $pageEnd);
    }
    aut();
    err();
    if (!$db->query(
        'SELECT COUNT( * ) FROM `spamus` WHERE `id_user`=?i AND `id_spam`=?i AND `razdel`=?',
                    [$user['id'], $mess['id_user'], 'forum']
    )->el()) {
        echo "<div class='mess'>Ложная информация может привести к блокировке ника. 
Если вас постоянно достает один человек - пишет всякие гадости, вы можете добавить его в черный список.</div>";
        echo "<form class='nav1' method='post' action='/forum/$forum[id]/$razdel[id]/$them[id]/?spam=$mess[id]&amp;page=".$input_get['page']."'>\n";
        echo "<b>Пользователь:</b> ";
        echo " ".avatar($mess['id_user'])."  ".group($mess['id_user'])." <a href=\"/info.php?id={$mess['id_user']}\">$mess[nick]</a>\n";
        echo "".medal($mess['id_user'])." ".online($mess['id_user'])." (".vremja($mess['time']).")<br />";
        echo "<b>Нарушение:</b> <font color='green'>".output_text($mess['msg'])."</font><br />";
        echo "Причина:<br />\n<select name='types'>\n";
        echo "<option value='1' selected='selected'>Спам/Реклама</option>\n";
        echo "<option value='2' selected='selected'>Мошенничество</option>\n";
        echo "<option value='3' selected='selected'>Оскорбление</option>\n";
        echo "<option value='0' selected='selected'>Другое</option>\n";
        echo "</select><br />\n";
        echo "Комментарий:";
        echo $tPanel."<textarea name=\"spamus\"></textarea><br />";
        echo "<input value=\"Отправить\" type=\"submit\" />\n";
        echo "</form>\n";
    } else {
        echo "<div class='mess'>Жалоба на <font color='green'>$mess[nick]</font> будет рассмотрена в ближайшее время.</div>";
    }
    echo "<div class='foot'>\n";
    echo "<img src='/style/icons/str2.gif' alt='*'> <a href='?page=".$input_get['page']."'>Назад</a><br />\n";
    echo "</div>\n";
    include_once '../sys/inc/tfoot.php';
    exit;
}

if ($them['close']==1) {
    $err = 'Тема закрыта для обсуждения';
}
// rating files
if (isset($input_get['rating']) && isset($user) &&  $user['balls']>=50 && $user['rating']>=0
    && !$db->query(
        "SELECT COUNT( * ) FROM `forum_files_rating` WHERE `id_user`=?i AND `id_file`=?i",
                   [$user['id'], $input_get['id_file']]
    )->el()) {
    if ($input_get['rating'] == 'down') {
        $data_rating = [$user['id'], $input_get['id_file'], -1];
        $send = 'Ваш отрицательный отзыв принят';
    } elseif ($input_get['rating'] == 'up') {
        $data_rating = [$user['id'], $input_get['id_file'], 1];
        $send = 'Ваш положительный отзыв принят';
    }
    $db->query(
        'INSERT INTO `forum_files_rating` (`id_user`, `id_file`, `rating`) VALUES(?i, ?i, ?i)',
               $data_rating
    );
    msg($send);
}

// BODY THEM
$k_post = $db->query(
    
    "SELECT COUNT( * ) FROM `forum_p` WHERE `id_them`=?i AND `id_forum`=?i AND `id_razdel`=?i",
                     [$them['id'], $forum['id'], $razdel['id']]
    
)->el();
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];

err();
aut();
echo "<div class='foot'>";
echo '<a href="/forum/'.$forum['id'].'/'.$razdel['id'].'/">'.text($razdel['name']).'</a> | <b>'.output_text($them['name']).'</b>';
echo "</div>\n";
/*
======================================
Перемещение темы
======================================
*/
if (isset($input_get['act']) && $input_get['act'] == 'mesto' && (user_access('forum_them_edit') || $ank2['id'] == $user['id'])) {
    echo "<form method=\"post\" action=\"/forum/$forum[id]/$razdel[id]/$them[id]/?act=mesto&amp;ok\">\n";
    echo "<div class='mess'>";
    echo "Перемещение темы <b>".output_text($them['name'])."</b>\n";
    echo "</div>";
    echo "<div class='main'>";
    echo "Раздел:<br />\n";
    echo "<select name=\"razdel\">\n";
    if (user_access('forum_them_edit')) {
        $q = $db->query("SELECT * FROM `forum_f` ORDER BY `pos` ASC");
        while ($forums = $q->row()) {
            echo "<optgroup label='$forums[name]'>\n";
            $q2 = $db->query(
                "SELECT * FROM `forum_r` WHERE `id_forum`=?i ORDER BY `time` DESC",
                             [$forums['id']]
            );
            while ($razdels = $q2->row()) {
                echo "<option".($razdel['id']==$razdels['id']?' selected="selected"':null)." value=\"$razdels[id]\">" . text($razdels['name']) . "</option>\n";
            }
            echo "</optgroup>\n";
        }
    } else {
        $q2 = $db->query(
            "SELECT * FROM `forum_r` WHERE `id_forum`=?i ORDER BY `time` DESC",
                         [$forum['id']]
        );
        while ($razdels = $q2->row()) {
            echo "<option".($razdel['id']==$razdels['id']?' selected="selected"':null)." value='$razdels[id]'>" . text($razdels['name']) . "</option>\n";
        }
    }
    echo "</select><br />\n";
    echo "<input value=\"Переместить\" type=\"submit\" /> \n";
    echo "<img src='/style/icons/delete.gif' alt='*'> <a href='/forum/$forum[id]/$razdel[id]/$them[id]/'>Отмена</a><br />\n";
    echo "</form>\n";
    echo "</div>";
    echo "<div class='foot'>";
    echo "<img src='/style/icons/str2.gif' alt='*'> <a href='/forum/$forum[id]/$razdel[id]/$them[id]/?'>В тему</a><br />\n";
    echo "</div>";
    include_once '../sys/inc/tfoot.php';
    exit;
}
/*
======================================
Редактирование темы
======================================
*/
if (isset($input_get['act']) && $input_get['act']=='set' && (user_access('forum_them_edit') || $ank2['id']==$user['id'])) {
    echo "<form method='post' action='/forum/$forum[id]/$razdel[id]/$them[id]/?act=set&amp;ok'>\n";
    echo "<div class='mess'>";
    echo "Редактирование темы <b>".output_text($them['name'])."</b>\n";
    echo "</div>";
    echo "<div class=\"main\">\n";
    echo "Название:<br />\n";
    echo "<input name='name' type='text' maxlength='32' value='".text($them['name'])."' /><br />\n";
    echo "Сообщение:$tPanel<textarea name=\"msg\">".text($them['text'])."</textarea><br />\n";
    if ($user['level']>0) {
        if ($them['up']==1) {
            $check=' checked="checked"';
        } else {
            $check=null;
        }
        echo "<label><input type=\"checkbox\"$check name=\"up\" value=\"1\" /> Всегда наверху</label><br />\n";
    }
    if ($them['close']==1) {
        $check=' checked="checked"';
    } else {
        $check=null;
    }
    echo "<label><input type=\"checkbox\"$check name=\"close\" value=\"1\" /> Закрыть</label><br />\n";
    if ($ank2['id']!=$user['id']) {
        echo "<label><input type=\"checkbox\" name=\"autor\" value=\"1\" /> Забрать у автора права</label><br />\n";
    }
    echo "<input value=\"Изменить\" type=\"submit\" /> \n";
    echo "<img src='/style/icons/delete.gif' alt='*'> <a href='/forum/$forum[id]/$razdel[id]/$them[id]/'>Отмена</a><br />\n";
    echo "</form>\n";
    echo "</div>";
    echo "<div class='foot'>";
    echo "<img src='/style/icons/str2.gif' alt='*'> <a href='/forum/$forum[id]/$razdel[id]/$them[id]/?'>В тему</a><br />\n";
    echo "</div>";
    include_once '../sys/inc/tfoot.php';
    exit;
}
// удаление поста
if (user_access('forum_post_ed') && isset($input_get['del'])) {
    $db->query(
        "DELETE FROM `forum_p` WHERE `id`=?i",
               [$input_get['del']]
    );
    $res = $db->query('SELECT `id` FROM `forum_files` WHERE `id_post` NOT IN(SELECT `id` FROM `forum_p`)')->col();
    if (count($res)) {
        foreach ($res as $id) {
            if (is_file(H . 'sys/forum/files/'.$id.'.frf')) {
                unlink(H . 'sys/forum/files/'.$id.'.frf');
            }
        }
        $db->query('DELETE FROM `forum_files` WHERE `id` IN(?li)', [$res]);
        $db->query('DELETE FROM `forum_files_rating` WHERE `id_file` NOT IN(SELECT `id` FROM `forum_files`)');
    }
    $db->query('OPTIMIZE TABLE `forum_p`, `forum_files`, `forum_files_rating`');
    
    $_SESSION['message'] = 'Сообщение успешно удалено';
    header('Location: /forum/' . $forum['id'] . '/' . $razdel['id'] . '/' . $them['id'] . '/?page=' . $input_get['page']);
    exit;
}

// Удаление темы
if (isset($input_get['act']) && $input_get['act']=='del' && user_access('forum_them_del') && ($ank2['level']<=$user['level'] || $ank2['id']==$user['id'])) {
    echo "<div class=\"mess\">\n";
    echo "Подтвердите удаление темы <b>".output_text($them['name'])."</b><br />\n";
    echo "</div>\n";
    echo "<div class=\"main\">\n";
    echo "[<a href=\"/forum/$forum[id]/$razdel[id]/$them[id]/?act=delete&amp;ok\"><img src='/style/icons/ok.gif' alt='*'> Да</a>] [<a href=\"/forum/$forum[id]/$razdel[id]/$them[id]/\"><img src='/style/icons/delete.gif' alt='*'> Нет</a>]<br />\n";
    echo "</div>\n";
    echo "<div class='foot'>";
    echo "<img src='/style/icons/fav.gif' alt='*'> <a href='/forum/$forum[id]/$razdel[id]/$them[id]/?'>В тему</a><br />\n";
    echo "</div>";
    include_once '../sys/inc/tfoot.php';
    exit;
}
/*
=========
Опрос от VoronoZ
=========
*/
if (isset($input_get['act']) && $input_get['act'] == 'vote' && (user_access('forum_them_edit') || $ank2['id'] == $user['id'])) {
    if ($db->query("SELECT COUNT(`id`) FROM `votes_forum` WHERE `them`=?i", [$them['id']])->el()) {
        if (isset($_POST['del']) && isset($user)) {
            $db->query('UPDATE `forum_t` SET `vote`="", `vote_close`="0" WHERE `id`=?i', [$them['id']]);
            $db->query('DELETE FROM `votes_forum` WHERE `them`=?i', [$them['id']]);
            $db->query('DELETE FROM `votes_user` WHERE `them`=?i', [$them['id']]);
            
            $_SESSION['message'] = 'Опрос удалён!';
            header("Location:/forum/$forum[id]/$razdel[id]/$them[id]/");
        }
        
        if (isset($_POST['send']) && isset($user)) {
            $close=(isset($_POST['close']) ? 1 : 0);
            $text=trim($_POST['text']);
            if (strlen2($text)<3) {
                $err[] = 'Короткая тема опроса';
            }
            if (strlen2($text)>42) {
                $err[] = 'Тема опроса должна быть короче 40 символов';
            }
            $mat = antimat($text);
            if ($mat) {
                $err[] = 'В теме опроса  обнаружен мат: '.$mat;
            }
            if (!isset($err)) {
                $db->query(
                    "UPDATE `forum_t` SET `vote`=?,`vote_close`=? WHERE `id`=?i",
                           [$text, $close, $them['id']]
                );
            }
            for ($x=1; $x<7; $x++) {
                $add=trim($_POST['vote_'.$x.'']);
                if (strlen2($add)>23) {
                    $err = 'Вариант опроса № '.$x.' слишком длинный';
                }
                if ($_POST['vote_1']==null || $_POST['vote_2']==null) {
                    $err = 'Два первых варианта должны быть заполнены';
                }
                $mat = antimat($add);
                if ($mat) {
                    $err = 'В варианте опроса № '.$x.'  обнаружен мат: '.$mat;
                }
                if (!isset($err)) {
                    $db->query(
                        "UPDATE `votes_forum` SET `var`=? WHERE `num`=? LIMIT ?i",
                               [$add, $x, 1]
                    );
                    $_SESSION['message'] = 'Опрос изменён!';
                    header("Location:/forum/$forum[id]/$razdel[id]/$them[id]/");
                }
            }
        }
        err();
        function sub($str, $ch)
        {
            if ($ch < strlen($str)) {
                $str = iconv('UTF-8', 'windows-1251', $str);
                $str = substr($str, 0, $ch);
                $str = iconv('windows-1251', 'UTF-8', $str);
                $str .='...';
            }
            return $str;
        }
        echo "<form method='post' action='/forum/$forum[id]/$razdel[id]/$them[id]/?act=vote'>";
        echo "<div class='nav1'>";
        echo "<img src='/style/icons/rating.png' alt='*'> Опрос: <b>" .(mb_strlen($them['vote']) < 16 ?
                                                                        output_text($them['vote']) : output_text(sub($them['vote'], 15))). "</b><br/>";
        echo "</div>";
        echo "<div class='main'>";
        echo "<b>Тема опроса</b>: <div style='border-top: 1px dashed red; padding: 2px;'>".$tPanel."<textarea name='text'>" .
        output_text($them['vote']) . "</textarea></div><br/>";
        $q=$db->query(
            "SELECT * FROM `votes_forum` WHERE `them`=?i ORDER BY `id` ASC  LIMIT ?i",
                      [$them['id'], 6]
        );
        while ($row = $q->row()) {
            echo "Вариант № $row[num] <div style='border-top: 1px dashed red; padding: 2px;'><input name='vote_$row[num]' type='text' value='" .
            (isset($row['var']) ? output_text($row['var']) : null)."' maxlength='24' placeholder='Не заполнено'  /></div>";
        }
        echo "<label><input type='checkbox' name='close' ".($them['vote_close']=='1'? "checked='checked' value='1' /> Открыть опроc" : "value='1'/> Закрыть опрос")." </label>";
        echo '<input value="Изменить" name="send" type="submit" />  
      <input value="Удалить опрос" name="del" type="submit" /> 
  </form>';
    } else {
        if (isset($_POST['send']) && isset($user)) {
            $text = trim($_POST['text']);
            if (strlen2($text)<3) {
                $err[] = 'Короткая тема опроса';
            }
            if (strlen2($text)>42) {
                $err[] = 'Тема опроса должна быть короче 40 символов';
            }
            $mat = antimat($text);
            if ($mat) {
                $err[] = 'В теме опроса  обнаружен мат: '.$mat;
            }
            if (!isset($err)) {
                $db->query(
                    "UPDATE `forum_t` SET `vote`=?, `vote_close` =? WHERE `id`=?i",
                           [$text, '0', $them['id']]
                );
            }
            for ($x = 1; $x < 7; $x++) {
                $add = trim($_POST['add_' . $x]);
                if (strlen2($add) > 23) {
                    $err = 'Вариант опроса № ' . $x . ' слишком длинный';
                }
                if ($_POST['add_1'] == null || $_POST['add_2'] == null) {
                    $err = 'Два первых варианта должны быть заполнены';
                }
                $mat = antimat($add);
                if ($mat) {
                    $err = 'В варианте опроса № '.$x.'  обнаружен мат: '.$mat;
                }
                if (!isset($err)) {
                    $db->query(
                        "INSERT INTO `votes_forum` (`them`,`var`,`num`) values(?i, ?, ?)",
                               [$them['id'], $add, $x]
                    );
                    
                    $_SESSION['message'] = 'Опрос добавлен!';
                    header('Location:/forum/' . $forum['id'] . '/' . $razdel['id'] . '/' . $them['id'] . '/');
                }
            }
        }
        err();
        echo "<form method='post' action='/forum/$forum[id]/$razdel[id]/$them[id]/?act=vote'>";
        echo "<div class='main'>";
        echo 'Тема опроса:'.$tPanel.'<textarea name="text"></textarea><br/>';
        for ($x=1; $x<7; $x++) {
            echo "Вариант № $x <div style='border-top: 1px dashed red; padding: 2px;'><input name='add_$x' type='text' maxlength='15' placeholder='Не заполнено' /></div>";
        }
        echo '<input value="Добавить" type="submit" name="send" /> </form>';
    }
    echo "<img src='/style/icons/delete.gif' alt='*'> <a href='/forum/$forum[id]/$razdel[id]/$them[id]/'>Отмена</a>";
    echo "</form>";
    echo "</div>";
    echo "<div class='foot'>";
    echo "<img src='/style/icons/str2.gif' alt='*'> <a href='/forum/$forum[id]/$razdel[id]/$them[id]/?'>В тему</a>";
    echo "</div>";
    include_once '../sys/inc/tfoot.php';
    exit;
}
if (isset($input_get['vote_user']) && $db->query(
    "SELECT COUNT( * ) FROM `votes_user` WHERE `var`=?i AND `them`=?i",
                                                 [$input_get['vote_user'], $them['id']]
)->el()) {
    $us = $input_get['vote_user'];
    
    $k_post = $db->query(
    
        "SELECT * FROM `votes_user` WHERE  `var`=?i AND `them`=?i",
                         [$us, $them['id']]
    
    )->el();
    $k_page=k_page($k_post, $set['p_str']);
    $page=page($k_page);
    $start=$set['p_str']*$page-$set['p_str'];
    $q = $db->query(
        "SELECT vts.`id_user`, vts.`time` FROM `votes_user` vts WHERE  vts.`var`=?i AND vts.`them`=?i ORDER BY vts.`time`  LIMIT ?i OFFSET ?i",
                    [$us, $them['id'], $set['p_str'], $start]
    );
    while ($row = $q->row()) {
        ?><table class="post"><?php
    #Div Block's
    if ($num==0) {
        ?><div class="nav1"><?php
   $num=1;
    } elseif ($num==1) {
        ?><div class="nav2"><?php
$num=0;
    }
        echo avatar($row['id_user']) . group($row['id_user']);
        echo user::nick($row['id_user'], 1, 1, 1) . ' ' . vremja($row['time']) . '</div>';
    }
    if ($k_page > 1) {
        str("/forum/$forum[id]/$razdel[id]/$them[id]/?vote_user=$us&amp;", $k_page, $page);
    } ?><div class="foot">
<img src="/style/icons/fav.gif" alt="*"> <a href="/forum/<?=$forum['id']; ?>/<?=$razdel['id']; ?>/<?=$them['id']; ?>/?">В тему</a>
</div><?php
include_once '../sys/inc/tfoot.php';
    exit;
}
/* End Vote */
/* Голосование в опросе*/
 if (isset($_POST['go']) && isset($_POST['vote']) && isset($user)) {
     $vote=abs($_POST['vote']);
     if (!$db->query(
         "SELECT COUNT( * ) FROM `votes_user` WHERE `them`=?i  AND `id_user`=?i",
                     [$them['id'], $user['id']]
     )->el()  && $them['vote_close'] != '1' && $them['close']=='0') {
         $db->query(
             "INSERT INTO `votes_user` (`them`, `id_user`, `var`, `time`) VALUES(?i, ?i, ?i, ?i)",
                    [$them['id'], $user['id'], $vote, time()]
         );
         $_SESSION['message'] = 'Ваш голос принят!';
         header("Location:/forum/$forum[id]/$razdel[id]/$them[id]/");
     } else {
         $_SESSION['message'] = 'Ошибка !';
         header("Location:/forum/$forum[id]/$razdel[id]/$them[id]/");
     }
 }
/*
======================================
Время и содержание темы
======================================
*/
echo "<div class='mess'><img src='/style/icons/blogi.png'> Автор: ".group($them['id_user'])." ";
echo user::nick($them['id_user'], 1, 1, 1)." <br/>\n";
echo "<img src='/style/icons/alarm.png' alt='*' /> Создана: ".vremja($them['time'])." <br/>";
echo "<img src='/style/icons/kumr.gif'> Название: <b>".text($them['name'])."</b></div>";
echo "<div class='nav2'>".output_text($them['text'])." ";
/*
==========
Опрос
==========
*/
$vote_c = $db->query(
    'SELECT COUNT( * ) FROM `votes_forum` WHERE `them`=?i',
                     [$them['id']]
)->el();
 if ($vote_c != 0 && !isset($input_get['act'])) {
     ?>
<div class="round_corners poll_block stnd_padd">
    <div style="font-size:14px;">
        Опрос: <strong><?= output_text($them['vote']); ?></strong>
    </div><?php
$q = $db->query(
         "SELECT vtf.*, (
SELECT COUNT( * ) FROM `votes_user` WHERE `them`=vtf.them) vote_sum, (
SELECT COUNT( * ) FROM `votes_user` WHERE `them`=vtf.them AND `var`=vtf.num) votec_var, (
SELECT COUNT( * ) FROM `votes_user` WHERE `them`=?i  AND `id_user`=?i) vote_user
FROM `votes_forum` vtf WHERE vtf.`them`=?i AND vtf.`var`<>'' LIMIT ?i",
                [$them['id'], $user['id'], $them['id'], 6]
     ); ?>
<form action="" method="post">
<?php 
while ($row = $q->row()) {
    
    if ($row['vote_sum'] == 0) {
        $poll=0;
    } elseif ($row['votec_var'] == 0) {
        $poll=0;
    } else {
        $poll=($row['votec_var']/$row['vote_sum'])*100;
    }
    
    if ($row['vote_user'] == 0 && isset($user)) {
        ?>
        <input type="radio" value="<?= $row['num']; ?>" name="vote"/>
        &nbsp;<?= output_text($row['var']); ?></a> - <a href="?vote_user=<?=$row['num']; ?>"><?=$row['votec_var']; ?> чел.</a><br />
<?php
    } else {
        ?>
<?= output_text($row['var']);
    if ($row['votec_var']) {
        ?> <a href="?vote_user=<?= $row['num']; ?>"><?= $row['votec_var']; ?></a><br /><?php
    } else {
        echo ' 0<br />';
    }
?><img src="/forum/img.php?img=<?= $poll; ?>" alt="*"/><br /> 
<?php
    }
}
     if (isset($user) && $row['vote_user'] == 0 && $them['vote_close'] != 1 && $them['close'] == 0) {
         ?>
         <input type="submit" name="go" value="Голосовать"/><?php
     }
     echo '</form></div>';
 }
echo "</div>";
/*
======================================
В закладки и поделиться
======================================
*/
if (!empty($them['id_edit'])) {
    echo "<div class='nav2'>";
    echo "<span style='color:#666;'><img src='/style/icons/edit.gif'> Изменено ".user::nick($them['id_edit'])." ".vremja($them['time_edit'])."</span></div>";
} elseif (!empty($them['id_close'])) {
    echo "<div class='nav2'>";
    echo "<span style='color:#666;'><img src='/style/icons/topic_locked.gif'> Тема закрыта ".user::nick($them['id_edit'])." ".vremja($them['time_edit'])."</span></div>";
}
$parent = 'SELECT * FROM (
SELECT COUNT( * ) share_note FROM `notes` WHERE `share_id`=?i AND `share_type`="forum")q, (
SELECT COUNT( * ) user_note FROM `notes` WHERE `id_user`=?i AND `share_type`="forum" AND `share_id`=?i)q2';
$data_note = [$them['id'], $user['id'], $them['id']];
if (isset($user)) {
    $parent = 'SELECT * FROM (
SELECT COUNT( * ) share_note FROM `notes` WHERE `share_id`=?i AND `share_type`="forum")q, (
SELECT COUNT( * ) user_note FROM `notes` WHERE `id_user`=?i AND `share_type`="forum" AND `share_id`=?i)q2, (
SELECT COUNT( * ) marks FROM `bookmarks` WHERE `id_object`=?i AND `id_user`=?i AND `type`="forum")q4';
    $data_note = [$them['id'], $user['id'], $them['id'], $them['id'], $user['id']];
}
$cnt = $db->query($parent, $data_note)->row();
echo "<div class='mess'>";

if (isset($user) && !$cnt['user_note']) {
    echo " <a href='/forum/share.php?id=".$them['id']."'><img src='/style/icons/action_share_color.gif'> Поделиться: (" . $cnt['share_note'] . ")</a>";
} else {
    echo "<img src='/style/icons/action_share_color.gif'> Поделились  (" . $cnt['share_note'] . ")";
}
if (isset($user)) {
    
    echo "<br/><img src='/style/icons/add_fav.gif' alt='*' /> ";
    if (!$cnt['marks']) {
        echo " <a href=\"?page=$page&amp;zakl=1\" title='Добавить в закладки'>Добавить в закладки</a><br />\n";
    } else {
        echo " <a href=\"?page=$page&amp;zakl=0\" title='Удалить из закладок'>Удалить из закладок</a><br />\n";
    }
}
echo "</div>";
/*
======================================
Кнопки действия с темой
======================================
*/
if (isset($user) && (((!isset($input_get['act']) || $input_get['act']!='post_delete') && (user_access('forum_post_ed') || $ank2['id']==$user['id']))
|| ((user_access('forum_them_edit') || $ank2['id']==$user['id']))
|| (user_access('forum_them_del') || $ank2['id']==$user['id']))) {
    echo "<div class=\"foot\">\n";
    if (user_access('forum_them_edit') || $them['id_user']==$user['id']) {
        echo "<img src='/style/icons/settings.gif' width='16'> <a href='/forum/$forum[id]/$razdel[id]/$them[id]/?act=set'><font color='darkred'>Редактировать</font></a><br/>\n";
        echo "<img src='/style/icons/glavnaya.gif' width='16'> <a href='/forum/$forum[id]/$razdel[id]/$them[id]/?act=mesto'><font color='darkred'>Переместить</font></a>\n";
        if ($vote_c==0) {
            ?><br/><img src="/style/icons/top10.png"> <a href="/forum/<?=$forum['id']; ?>/<?=$razdel['id']; ?>/<?=$them['id']; ?>/?act=vote"> <font color="darkred">Добавить опрос</font></a> <?php
        } else {
            echo '<br/><img src="/style/icons/diary.gif"> <a href="?act=vote"><font color="darkred">Редактировать опрос</font></a>';
        }
    }
    if (user_access('forum_them_del') || $ank2['id']==$user['id']) {
        echo "<br/><img src='/style/icons/delete.gif' width='16'> <a href='/forum/$forum[id]/$razdel[id]/$them[id]/?act=del'><font color='darkred'>Удалить тему</font></a>\n";
    }
    echo "</div>\n";
}
echo "<div class='foot'>Комментарии:</div>";

// сортировка по времени
if (isset($user)) {
    echo "<div id='comments' class='menus'>";
    echo "<div class='webmenu'>";
    echo "<a href='/forum/$forum[id]/$razdel[id]/$them[id]/?page=$page&amp;sort=1' class='".($user['sort']==1?'activ':'')."'>Внизу</a>";
    echo "</div>";
    echo "<div class='webmenu'>";
    echo "<a href='/forum/$forum[id]/$razdel[id]/$them[id]/?page=$page&amp;sort=0' class='".($user['sort']==0?'activ':'')."'>Вверху</a>";
    echo "</div>";
    echo "</div>";
}

// alex-borisi
if ((user_access('forum_post_ed') || isset($user) && $ank2['id']==$user['id']) && isset($input_get['act']) && $input_get['act']=='post_delete') {
    $lim=null;
} else {
    $lim=" LIMIT $start, $set[p_str]";
}

$q=$db->query(
    'SELECT pst.*, u.id AS id_user, u.nick, u.`level`, (
SELECT msg FROM `status` WHERE id_user=pst.id_user ORDER BY id DESC LIMIT 1) as "status", (
SELECT COUNT( * ) FROM `ban` WHERE (`razdel`="all" OR `razdel`="forum") AND `post`=1 AND `id_user`=`pst`.`id_user` AND (`time`>?i OR `navsegda`=1)) ban, (
SELECT COUNT( * ) FROM forum_files WHERE id_post=pst.id) file
FROM `forum_p` pst 
LEFT JOIN `user` u ON u.id=pst.id_user
WHERE pst.`id_them`=?i ORDER BY pst.`time`?q;?q',
[$time, $them['id'], $sort, $lim])->assoc();

if (!count($q)) {
    echo "<div class='mess'>";
    echo "Нет сообщений в теме\n";
    echo "</div>";
}
foreach ($q as $post) {
    if ($num==0) {
        echo '<div class="nav1">';
        $num=1;
    } elseif ($num==1) {
        echo '<div class="nav2">';
        $num=0;
    }

    if ((user_access('forum_post_ed') || isset($user) && $ank2['id']==$user['id']) && isset($input_get['act']) && $input_get['act']=='post_delete') {
        echo '<input type="checkbox" name="post_'.$post['id'].'" value="1" />';
    }
    echo user::avatar($post['id_user']);
    echo user::nick($post['id_user'], 1, 1, 1).' <span style="float:right;color:#666;">'.vremja($post['time']).'</span><br/>';
    
    if ($post['ban'] == 0) { // Блок сообщения
    if ($them['id_user'] == $post['id_user']) { // Отмечаем автора темы
        echo '<font color="#999">Автор темы</font><br />';
    }
        // Вывод статуса
        if ($set['st'] == 1 && !empty($post['status'])) {
            echo "<div class='st_1'></div>";
            echo "<div class='st_2'>";
            echo "".output_text($post['status'])."";
            echo "</div>\n";
        }
        
        // Цитирование поста
        if ($post['cit']!=null && $db->query("SELECT COUNT( * ) FROM `forum_p` WHERE `id`=?i",
                                             [$post['cit']])->el()) {
            $cit=$db->query("SELECT * FROM `forum_p` WHERE `id`=?i",
                            [$post['cit']])->row();
            $ank_c=get_user($cit['id_user']);
            echo '<div class="cit">
		  <b>'.$ank_c['nick'].' ('.vremja($cit['time']).'):</b><br />
		  '.output_text($cit['msg']).'<br />
		  </div>';
        }
        echo output_text($post['msg']).'<br />'; // Посты темы
        if ($post['file']) {
            echo '<table>';
            include H.'/forum/inc/file.php'; // Прекрепленные файлы
            echo '</table>';
        }
    } else {
        echo output_text($banMess).'<br />';
    }
    if (isset($user)) {
        if ($them['close']==0) {
            if (isset($user) &&  $user['id']!=$post['id_user'] && $post['id_user']!=0) {
                echo '<a href="/forum/'.$forum['id'].'/'.$razdel['id'].'/'.$them['id'].'/?response='.$post['id_user'].'&amp;page='.$page.'" title="Ответить '.$post['nick'].'">Ответ</a> | ';
                echo '<a href="/forum/'.$forum['id'].'/'.$razdel['id'].'/'.$them['id'].'/'.$post['id'].'/cit" title="Цитировать '.$post['nick'].'">Цитата</a>';
            }
        }
        echo '<span style="float:right;">';
        if ($them['close']==0) { // если тема закрыта, то скрываем кнопки
            if (user_access('forum_post_ed') && ($post['level']<=$user['level'] || $post['level']==$user['level'] &&  $post['id_user']==$user['id'])) {
                echo "<a href=\"/forum/$forum[id]/$razdel[id]/$them[id]/$post[id]/edit\" title='Изменить пост {$post['nick']}'  class='link_s'><img src='/style/icons/edit.gif' alt='*'> </a> \n";
            } elseif ($user['id']==$post['id_user'] && $post['time']>time()-600) {
                echo "<a href=\"/forum/$forum[id]/$razdel[id]/$them[id]/$post[id]/edit\" title='Изменить мой пост'  class='link_s'><img src='/style/icons/edit.gif' alt='*'> (".($post['time']+600-time())." сек)</a> \n";
            }
            
            if ($user['id']!=$post['id_user'] && $post['id_user']!=0) { // Кроме автора поста и системы
                echo "<a href=\"/forum/$forum[id]/$razdel[id]/$them[id]/?spam=$post[id]&amp;page=$page\" title='Это спам'  class='link_s'><img src='/style/icons/blicon.gif' alt='*' title='Это спам'></a>\n";
            }
        }
        if (user_access('forum_post_ed')) { // удаление поста
            echo "<a href=\"/forum/$forum[id]/$razdel[id]/$them[id]/?del=$post[id]&amp;page=$page\" title='Удалить'  class='link_s'><img src='/style/icons/delete.gif' alt='*' title='Удалить'></a>\n";
        }
        echo "&nbsp;\n";
        echo '</span><br/>';
    }
    echo ' '.($webbrowser ? null : '<br/>').' </div>';// TODO: хуй разберешь что у индусов в голове:)
}

if ((user_access('forum_post_ed') || isset($user) && $ank2['id'] == $user['id']) && isset($input_get['act']) && $input_get['act'] == 'post_delete') {
} elseif ($k_page > 1) {
    str("/forum/$forum[id]/$razdel[id]/$them[id]/?", $k_page, $page);
} // Вывод страниц

if ((user_access('forum_post_ed') || isset($user) && $ank2['id'] == $user['id']) && isset($input_get['act']) && $input_get['act'] == 'post_delete') {
} elseif (isset($user) && ($them['close'] == 0 || $them['close'] == 1 && user_access('forum_post_close'))) {
    if (isset($user)) {
        echo "<div class='foot'>";
        echo 'Новое сообщение:';
        echo "</div>";
    }
    if ($user['set_files'] == 1) {
        echo "<form method='post' name='message' enctype='multipart/form-data' action='/forum/$forum[id]/$razdel[id]/$them[id]/new?page=$page&amp;$passgen&amp;" . $go_link . "'>\n";
    } else {
        echo "<form method='post' name='message' action='/forum/$forum[id]/$razdel[id]/$them[id]/new?page=$page&amp;$passgen&amp;" . $go_link . "'>\n";
    }
    if (isset($_POST['msg']) && isset($_POST['file_s'])) {
        $msg2 = output_text($_POST['msg'], false, true, false, false, false);
    } else {
        $msg2 = null;
    }
    if ($set['web'] && is_file(H . 'style/themes/' . $set['set_them'] . '/altername_post_form.php')) {
        include H . 'style/themes/' . $set['set_them'] . '/altername_post_form.php';
    } else {
        echo "$tPanel<textarea name=\"msg\">$insert$msg2</textarea><br />\n";
    }
    if ($user['set_files'] == 1) {
        if (isset($_SESSION['file'])) {
            echo "Прикрепленные файлы:<br />\n";
            for ($i = 0; $i < count($_SESSION['file']); $i++) {
                if (isset($_SESSION['file'][$i]) && is_file($_SESSION['file'][$i]['tmp_name'])) {
                    echo "<img src='/style/themes/$set[set_them]/forum/14/file.png' alt='' />\n";
                    echo $_SESSION['file'][$i]['name'] . '.' . $_SESSION['file'][$i]['ras'] . ' (';
                    echo size_file($_SESSION['file'][$i]['size']);
                    echo ") <a href='/forum/$forum[id]/$razdel[id]/$them[id]/d_file$i' title='Удалить из списка'><img src='/style/themes/$set[set_them]/forum/14/del_file.png' alt='' /></a>\n";
                    echo "<br />\n";
                }
            }
        }
        echo "<input name='file_f' type='file' /><br />\n";
        echo "<input name='file_s' value='Прикрепить файл' type='submit' /><br />\n";
    }
    echo '<input name="post" value="Отправить" type="submit" /><br />
	 </form>';
}
?>