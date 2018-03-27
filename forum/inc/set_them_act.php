<?php
// Перемещение темы
if (isset($input_get['act']) && isset($input_get['ok']) && $input_get['act'] == 'mesto' && isset($_POST['razdel']) && is_numeric($_POST['razdel'])
&& ($db->query(
    "SELECT COUNT( * ) FROM `forum_r` WHERE `id`=?i",
               [$_POST['razdel']])->el() && user_access('forum_them_edit')
|| $db->query(
    "SELECT COUNT( * ) FROM `forum_r` WHERE `id`=?i WHERE `id_forum`=?i",
              [$_POST['razdel'], $forum['id']])->el() && $ank2['id'] == $user['id'])) {
    $razdel_new = $db->query(
        "SELECT `id`, `id_forum` FROM `forum_r` WHERE `id`=?i",
                             [$_POST['razdel']])->row();
    $named = [
               'id_forum' => $razdel_new['id_forum'],
               'id_razdel' => $razdel_new['id'],
               'old_id_forum' => $forum['id'],
               'old_id_razdel' => $razdel['id'],
               'id_them' => $them['id'],
               ];
    $db->query("UPDATE `forum_p` SET `id_forum`=?i:id_forum, `id_razdel`=?i:id_razdel
WHERE `id_forum`=?i:old_id_forum AND `id_razdel`=?i:old_id_razdel AND `id_them`=?i:id_them", $named);
    $db->query("UPDATE `forum_t` SET `id_forum`=?i:id_forum, `id_razdel`=?i:id_razdel
WHERE `id_forum`=?i:old_id_forum AND `id_razdel`=?i:old_id_razdel AND `id`=?i:id_them", $named);
    
    $old_razdel = $razdel;
    $forum=$db->query("SELECT `id` FROM `forum_f` WHERE `id` = ?i:id_forum", $named)->row();
    $razdel=$db->query("SELECT `id`, `name` FROM `forum_r` WHERE `id` = ?i:id_razdel", $named)->row();
    $them=$db->query(
        "SELECT `id`, `name` FROM `forum_t` WHERE `id_razdel`=?i AND `id`=?i",
                     [$razdel['id'], $them['id']])->row();

    /* PluginS Dcms-Social.Ru */
    $msgg='[red]Тему переместил '.$user['group_name'].' '.$user['nick'].' из раздела '.$old_razdel['name'].' в раздел '.$razdel['name'].'[/red]';
    $db->query(
        "INSERT INTO `forum_p` (`id_forum`, `id_razdel`, `id_them`, `id_user`, `msg`, `time`) VALUES(?i, ?i, ?i, ?i, ?, ?i)",
               [$forum['id'], $razdel['id'], $them['id'], 0, $msgg, $time]);
    /*тут конец*/
    if ($ank2['id'] != $user['id']) {
        admin_log('Форум', 'Перемещение темы',
                  'Перемещение темы "[url=/forum/' . $forum['id'] . '/' . $razdel['id'] . '/' . $them['id'] . '/]' .
                  $them['name'] . '[/url]" из раздела "[url=/forum/' . $forum['id'] . '/' . $old_razdel['id'] . '/]' .
                  $old_razdel['name'] . '[/url]" в раздел "[url=/forum/' . $forum['id'] . '/' . $old_razdel['id'] . '/]' . $razdel['name'] . '[/url]"');
    }
    $_SESSION['message'] = 'Тема успешно перемещена';
    header('Location: /forum/' . $forum['id'] . '/' . $razdel['id'] . '/' . $them['id'] . '/');
    exit;
}

// Удаление темы
if ((user_access('forum_them_del') || $ank2['id'] == $user['id']) &&  isset($input_get['act']) && isset($input_get['ok']) && $input_get['act'] == 'delete') {
    
    $db->query("DELETE FROM `forum_t` WHERE `id`=?i", [$them['id']]);
    $db->query("DELETE FROM `forum_p` WHERE `id_them`=?i", [$them['id']]);
    //Удаление файлов темы
    $res = $db->query('SELECT `id` FROM `forum_files` WHERE `id_post` NOT IN(
                   SELECT `id` FROM `forum_p`)')->col();
    if (count($res)) {
        foreach ($res as $id) {
            if (is_file(H . 'sys/forum/files/'.$id.'.frf')) {
                unlink(H . 'sys/forum/files/'.$id.'.frf');
            }
        }
        $db->query('DELETE FROM `forum_files` WHERE `id_post` NOT IN(SELECT `id` FROM `forum_p`)');
        $db->query('DELETE FROM `forum_files_rating` WHERE `id_file` NOT IN(SELECT `id` FROM `forum_files`)');
    }
        
    $db->query('OPTIMIZE TABLE `forum_t`, `forum_p`, `forum_files`, `forum_files_rating`');
    
    if ($ank2['id'] != $user['id']) {
        admin_log('Форум', 'Удаление темы',
                  'Удаление темы "' . $them['name'] . ' (автор "[url=/info.php?id=' . $ank2['id'] . ']' . $ank2['nick'] . '[/url]")');
    }
    
    $_SESSION['message'] = 'Тема успешно удалена';
    header('Location: /forum/' . $forum['id'] . '/' . $razdel['id'] . '/' . $them['id'] . '/');
    exit;
}

// Изменение темы
if (isset($input_get['act']) && isset($input_get['ok']) && $input_get['act']=='set' && isset($_POST['name']) && (user_access('forum_them_edit') || $ank2['id']==$user['id'])) {
    $name = esc(trim($_POST['name']));
    $msg = esc(trim($_POST['msg']));
    
    if (strlen2($name)<3) {
        $err='Слишком короткое название';
    }
    if (strlen2($name)>32) {
        $err='Слишком длинное название';
    }

    if ($user['level'] > 0) {
        if (isset($_POST['up']) && $_POST['up'] == 1 && $them['up'] != 1) {
            if ($ank2['id'] != $user['id']) {
                admin_log('Форум', 'Параметры темы',
                          'Закрепление темы "[url=/forum/' . $forum['id'] . '/' . $razdel['id'] . '/' . $them['id'] . '/]' .
                          $them['name'] . '[/url]" (автор "[url=/info.php?id=' . $ank2['id'] . ']' . $ank2['nick'] . '[/url]", раздел "' . $razdel['name'] . ')');
            }
            $up = 1;
            /* PluginS Dcms-Social.Ru */
            $msgg='[red]Тему закрепил '.$user['group_name'].' '.$user['nick'].'[/red]';
            $db->query(
                "INSERT INTO `forum_p` (`id_forum`, `id_razdel`, `id_them`, `id_user`, `msg`, `time`) VALUES(?i, ?i, ?i, ?i, ?, ?i)",
                       [$forum['id'], $razdel['id'], $them['id'], 0, $msgg, $time]);
        // тут конец
        } else {
            $up = 0;
        }
        $add_q = ' `up` = "' . $up . '",';
    } else {
        $add_q = '';
    }
    if (isset($_POST['close']) && $_POST['close'] == 1 && $them['close'] == 0) {
        $close = 1;
        if ($ank2['id']!=$user['id']) {
            admin_log('Форум', 'Параметры темы',
                      'Закрытие темы "[url=/forum/' . $forum['id'] . '/' . $razdel['id'] . '/' . $them['id'] . ']' .
                      $them['name'] . '[/url]" (автор "[url=/info.php?id=' . $ank2['id'] . ']' . $ank2['nick'] . '[/url]")');
        }
        /* PluginS Dcms-Social.Ru */
        $msgg='[red]Тему закрыл '.$user['group_name'].' '.$user['nick'].'[/red]';
        $db->query(
            "INSERT INTO `forum_p` (`id_forum`, `id_razdel`, `id_them`, `id_user`, `msg`, `time`) VALUES(?i, ?i, ?i, ?i, ?, ?i)",
                   [$forum['id'], $razdel['id'], $them['id'], 0, $msgg, $time]);
    /*тут конец*/
    } elseif ($them['close'] == 1 && (!isset($_POST['close']) || $_POST['close'] == 0)) {
        $close = 0;
        if ($ank2['id'] != $user['id']) {
            admin_log('Форум', 'Параметры темы',
                      'Открытие темы "[url=/forum/' . $forum['id'] . '/' . $razdel['id'] . '/' . $them['id'] . ']' .
                      $them['name'] . '[/url]" (автор "[url=/info.php?id=' . $ank2['id'] . ']' . $ank2['nick'] . '[/url]")');
        }
        $msgg='[red]Тему открыл '.$user['group_name'].' '.$user['nick'].'[/red]';
        $db->query(
            "INSERT INTO `forum_p` (`id_forum`, `id_razdel`, `id_them`, `id_user`, `msg`, `time`) VALUES(?i, ?i, ?i, ?i, ?, ?i)",
                   [$forum['id'], $razdel['id'], $them['id'], 0, $msgg, $time]);
    /*тут конец*/
    } else {
        $close = $them['close'];
    }
    if (isset($_POST['autor']) && $_POST['autor'] == 1) {
        $autor = $user['id'];
    } else {
        $autor = $ank2['id'];
    }
    if (!isset($err)) {
        if (isset($_POST['close']) && $_POST['close'] == 1 && $them['close'] == 0) {
            $cl = ',`id_close`=' . $user['id'] . ' ';
        } elseif (isset($_POST['close']) && $_POST['close'] == 0 && $them['close'] == 1) {
            $cl = '';
        } else {
            $cl = '';
        }
        $db->query(
            "UPDATE `forum_t` SET `name`=?, `text`=?, `id_user`=?i,?q `close`=?string, `id_edit`=?i,`time_edit`=?i ?q WHERE `id`=?i",
                   [$name, $msg, $autor, $add_q, $close, $user['id'], $time, $cl, $them['id']]);
        
        $them = $db->query("SELECT * FROM `forum_t` WHERE `id`=?i", [$them['id']])->row();
        $ank2 = $db->query("SELECT * FROM `user` WHERE `id`=?i", [$them['id_user']])->row();
        
        $_SESSION['message'] = 'Изменения успешно приняты';
        header("Location: /forum/$forum[id]/$razdel[id]/$them[id]/");
        exit;
    }
}

// Удаление отмеченных кАмментов
if ((user_access('forum_post_ed') || isset($user) && $ank2['id']==$user['id']) && isset($input_get['act']) && $input_get['act']=='post_delete' && isset($input_get['ok'])) {
    foreach ($_POST as $key => $value) {
        if (preg_match('~^post_([0-9]*)$~', $key, $postnum) && $value = '1') {
            $delpost[] = $postnum[1];
        }
    }
    if (isset($delpost) && is_array($delpost) && $db->query(
        "SELECT COUNT(*) FROM `forum_p` WHERE `id_them`=?i AND `id_forum`=?i AND `id_razdel`=?i",
                                                            [$them['id'], $forum['id'], $razdel['id']])->el() > count($delpost)) {
        $db->query(
            "DELETE FROM `forum_p` WHERE `id_them`=?i AND `id` IN(?li)",
                   [$them['id'], $delpost]);
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

        if ($ank2['id'] != $user['id']) {
            admin_log('Форум', 'Очистка темы',
                      'Очистка темы "[url=/forum/' . $forum['id'] . '/' . $razdel['id'] . '/' . $them['id'] . '/]' .
                      $them['name'] . '[/url]" (автор "[url=/info.php?id=' . $ank2['id'] . ']' . $ank2['nick'] . '[/url]", удалено ' . count($delpost) . ' постов)');
        }

        msg('Успешно удалено ' . count($delpost) . ' постов');
        err();
        aut();
        echo "<div class='mess'>\n";
        echo "<a href=\"/forum/$forum[id]/$razdel[id]/$them[id]/\">$them[name]</a><br />\n";
        echo "<a href=\"/forum/$forum[id]/$razdel[id]/\">$razdel[name]</a><br />\n";
        echo "<a href=\"/forum/$forum[id]/\">$forum[name]</a><br />\n";
        echo "<a href=\"/forum/\">В форум</a><br />\n";
        echo "</div>\n";
        include_once H . 'sys/inc/tfoot.php';
    } else {
        $err = 'Нельзя удалить 0 или все посты из темы';
    }
}

if (isset($input_get['act']) && $input_get['act'] == 'post_delete' && (user_access('forum_post_ed') || isset($user) && $ank2['id'] == $user['id'])) {
    echo "<form method='post' action='/forum/$forum[id]/$razdel[id]/$them[id]/?act=post_delete&amp;ok'>\n";
}
