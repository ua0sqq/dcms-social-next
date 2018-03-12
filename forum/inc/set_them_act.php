<?php
/* PluginS Dcms-Social.Ru */
/*==== Перемещение темы *****/
if (isset($_GET['act']) && isset($_GET['ok']) && $_GET['act']=='mesto' && isset($_POST['razdel']) && is_numeric($_POST['razdel'])
&& ($db->query("SELECT COUNT(`id`) FROM `forum_r` WHERE `id` = '".intval($_POST['razdel'])."'")->el() && user_access('forum_them_edit')
|| $db->query("SELECT COUNT(`id`) FROM `forum_r` WHERE `id` = '".intval($_POST['razdel'])."' WHERE `id_forum` = '$forum[id]'")->el() && $ank2['id']==$user['id'])) {
    $razdel_new=$db->query("SELECT * FROM `forum_r` WHERE `id` = '".intval($_POST['razdel'])."' LIMIT 1")->row();
    $db->query("UPDATE `forum_p` SET `id_forum` = '$razdel_new[id_forum]', `id_razdel` = '$razdel_new[id]' WHERE `id_forum` = '$forum[id]' AND `id_razdel` = '$razdel[id]' AND `id_them` = '$them[id]'");
    $db->query("UPDATE `forum_t` SET `id_forum` = '$razdel_new[id_forum]', `id_razdel` = '$razdel_new[id]' WHERE `id_forum` = '$forum[id]' AND `id_razdel` = '$razdel[id]' AND `id` = '$them[id]'");
    $old_razdel=$razdel;
    $forum=$db->query("SELECT * FROM `forum_f` WHERE `id` = '$razdel_new[id_forum]' LIMIT 1")->row();
    $razdel=$db->query("SELECT * FROM `forum_r` WHERE `id` = '$razdel_new[id]' LIMIT 1")->row();
    $them=$db->query("SELECT * FROM `forum_t` WHERE `id_razdel` = '$razdel[id]' AND `id` = '$them[id]' LIMIT 1")->row();
    /* PluginS Dcms-Social.Ru */
    $msgg='[red]Тему переместил '.$user['group_name'].' '.$user['nick'].' из раздела '.$old_razdel['name'].' в раздел '.$razdel['name'].'[/red]';
    $db->query("INSERT INTO `forum_p` (`id_forum`, `id_razdel`, `id_them`, `id_user`, `msg`, `time`) values('$forum[id]', '$razdel[id]', '$them[id]', '0', '".my_esc($msgg)."', '$time')");
    /*тут конец*/
    if ($ank2['id']!=$user['id']) {
        admin_log('Форум', 'Перемещение темы', "Перемещение темы '[url=/forum/$forum[id]/$razdel[id]/$them[id]/]$them[name][/url]' из раздела '[url=/forum/$forum[id]/$old_razdel[id]/]$old_razdel[name][/url]' в раздел '[url=/forum/$forum[id]/$old_razdel[id]/]$razdel[name][/url]'");
    }
    $_SESSION['message'] = 'Тема успешно перемещена';
    header("Location: /forum/$forum[id]/$razdel[id]/$them[id]/");
    exit;
}
/**** Удаление темы ****/
if ((user_access('forum_them_del') || $ank2['id']==$user['id']) &&  isset($_GET['act']) && isset($_GET['ok']) && $_GET['act']=='delete') {
    /*
    * Удаление файлов темы
    */
    
    $qf=$db->query("SELECT * FROM `forum_p` WHERE `id_them` = '$them[id]'");
    while ($postf = $qf->row()) {
        if ($db->query("SELECT COUNT(*) FROM `forum_files` WHERE `id_post` = '$postf[id]'")->el()) {
            $qS=$db->query("SELECT * FROM `forum_files` WHERE `id_post` = '$postf[id]'");
        
            while ($postS = $qS->row()) {
                $db->query("DELETE FROM `forum_files` WHERE `id` = '$postS[id]'");
                @unlink(H.'sys/forum/files/'.$postS['id'].'.frf');
            }
        }
    }
    $db->query("DELETE FROM `forum_t` WHERE `id` = '$them[id]'");
    $db->query("DELETE FROM `forum_p` WHERE `id_them` = '$them[id]'");
    if ($ank2['id']!=$user['id']) {
        admin_log('Форум', 'Удаление темы', "Удаление темы '$them[name]' (автор '[url=/info.php?id=$ank2[id]]$ank2[nick][/url]')");
    }
    $_SESSION['message'] = 'Тема успешно удалена';
    header("Location: /forum/$forum[id]/$razdel[id]/$them[id]/");
    exit;
}
/**** Изменение темы ****/
if (isset($_GET['act']) && isset($_GET['ok']) && $_GET['act']=='set' && isset($_POST['name']) && (user_access('forum_them_edit') || $ank2['id']==$user['id'])) {
    $name=esc(stripslashes(htmlspecialchars($_POST['name'])));
    $msg=esc(stripslashes(htmlspecialchars($_POST['msg'])));
    if (strlen2($name)<3) {
        $err='Слишком короткое название';
    }
    if (strlen2($name)>32) {
        $err='Слишком длинное название';
    }
    $name=my_esc($_POST['name']);
    $msg=my_esc($_POST['msg']);
    if ($user['level']>0) {
        if (isset($_POST['up']) && $_POST['up']==1 and $them['up']!=1) {
            if ($ank2['id']!=$user['id']) {
                admin_log('Форум', 'Параметры темы', "Закрепление темы '[url=/forum/$forum[id]/$razdel[id]/$them[id]/]$them[name][/url]' (автор '[url=/info.php?id=$ank2[id]]$ank2[nick][/url]', раздел '$razdel[name]')");
            }
            $up=1;
            /* PluginS Dcms-Social.Ru */
            $msgg='[red]Тему закрепил '.$user['group_name'].' '.$user['nick'].'[/red]';
            $db->query("INSERT INTO `forum_p` (`id_forum`, `id_razdel`, `id_them`, `id_user`, `msg`, `time`) values('$forum[id]', '$razdel[id]', '$them[id]', '0', '".my_esc($msgg)."', '$time')");
        /*тут конец*/
        } else {
            $up=0;
        }
        $add_q=" `up` = '$up',";
    } else {
        $add_q=null;
    }
    if (isset($_POST['close']) && $_POST['close']==1 && $them['close']==0) {
        $close=1;
        if ($ank2['id']!=$user['id']) {
            admin_log('Форум', 'Параметры темы', "Закрытие темы '[url=/forum/$forum[id]/$razdel[id]/$them[id]]$them[name][/url]' (автор '[url=/info.php?id=$ank2[id]]$ank2[nick][/url]')");
        }
        /* PluginS Dcms-Social.Ru */
        $msgg='[red]Тему закрыл '.$user['group_name'].' '.$user['nick'].'[/red]';
        $db->query("INSERT INTO `forum_p` (`id_forum`, `id_razdel`, `id_them`, `id_user`, `msg`, `time`) values('$forum[id]', '$razdel[id]', '$them[id]', '0', '".my_esc($msgg)."', '$time')");
    /*тут конец*/
    } elseif ($them['close']==1 && (!isset($_POST['close']) || $_POST['close']==0)) {
        $close=0;
        if ($ank2['id']!=$user['id']) {
            admin_log('Форум', 'Параметры темы', "Открытие темы '[url=/forum/$forum[id]/$razdel[id]/$them[id]]$them[name][/url]' (автор '[url=/info.php?id=$ank2[id]]$ank2[nick][/url]')");
        }
        $msgg='[red]Тему открыл '.$user['group_name'].' '.$user['nick'].'[/red]';
        $db->query("INSERT INTO `forum_p` (`id_forum`, `id_razdel`, `id_them`, `id_user`, `msg`, `time`) values('$forum[id]', '$razdel[id]', '$them[id]', '0', '".my_esc($msgg)."', '$time')");
    /*тут конец*/
    } else {
        $close=$them['close'];
    }
    if (isset($_POST['autor']) && $_POST['autor']==1) {
        $autor=$user['id'];
    } else {
        $autor=$ank2['id'];
    }
    if (!isset($err)) {
        if (isset($_POST['close']) && $_POST['close']==1 && $them['close']==0) {
            $cl=",`id_close`='".$user['id']."' ";
        } elseif (isset($_POST['close']) && $_POST['close']==0 && $them['close']==1) {
            $cl=null;
        } else {
            $cl=null;
        }
        $db->query("UPDATE `forum_t` SET `name` = '$name', `text` = '$msg', `id_user` = '$autor',".$add_q." `close` = '$close',`id_edit`='".$user['id']."',`time_edit`='".$time."' ".$cl." WHERE `id` = '$them[id]' LIMIT 1");
        $them=$db->query("SELECT * FROM `forum_t` WHERE `id` = '$them[id]' LIMIT 1")->row();
        $ank2=$db->query("SELECT * FROM `user` WHERE `id` = '$them[id_user]' LIMIT 1")->row();
        $_SESSION['message'] = 'Изменения успешно приняты';
        header("Location: /forum/$forum[id]/$razdel[id]/$them[id]/");
        exit;
    }
}
/***** Удаление отмеченных кАмментов ****/
if ((user_access('forum_post_ed') || isset($user) && $ank2['id']==$user['id']) && isset($_GET['act']) && $_GET['act']=='post_delete' && isset($_GET['ok'])) {
    foreach ($_POST as $key => $value) {
        if (preg_match('#^post_([0-9]*)$#', $key, $postnum) && $value='1') {
            $delpost[]=$postnum[1];
        }
    }
    if (isset($delpost) && is_array($delpost)) {
        $db->query("DELETE FROM `forum_p` WHERE `id_them` = '$them[id]' AND (`id` = '".implode("'".' OR `id` = '."'", $delpost)."') LIMIT ".count($delpost));
        if ($ank2['id']!=$user['id']) {
            admin_log('Форум', 'Очистка темы', "Очистка темы '[url=/forum/$forum[id]/$razdel[id]/$them[id]/]$them[name][/url]' (автор '[url=/info.php?id=$ank2[id]]$ank2[nick][/url]', удалено '".count($delpost)."' постов)");
        }
        $msgg='[red]Тему почистил '.$user['group_name'].' '.$user['nick'].'[/red]';
        $db->query("INSERT INTO `forum_p` (`id_forum`, `id_razdel`, `id_them`, `id_user`, `msg`, `time`) values('$forum[id]', '$razdel[id]', '$them[id]', '0', '".my_esc($msgg)."', '$time')");
        $_SESSION['message'] = 'Успешно удалено '.count($delpost).' постов';
        header("Location: /forum/$forum[id]/$razdel[id]/$them[id]/");
        exit;
    }
}
if (isset($_GET['act']) && $_GET['act']=='post_delete' && (user_access('forum_post_ed') || isset($user) && $ank2['id']==$user['id'])) {
    echo "<form method='post' action='/forum/$forum[id]/$razdel[id]/$them[id]/?act=post_delete&amp;ok'>\n";
}
