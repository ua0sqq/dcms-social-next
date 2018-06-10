<?php

$set['title']='Форум - '.text($forum['name']).' - '.text($razdel['name']).' - Новая тема'; // заголовок страницы
include_once '../sys/inc/thead.php';
title();
if (isset($_POST['name']) && isset($_POST['msg'])) {
    if (isset($_SESSION['time_c_t_forum']) && $_SESSION['time_c_t_forum']>$time-600 && $user['level']==0) {
        $err='Нельзя так часто создавать темы';
    }
    $name=trim($_POST['name']);
    if (strlen2($name)<3) {
        $err[] = 'Короткое название для темы';
    }
    if (strlen2($name)>32) {
        $err[] = 'Название темы не должно быть длиннее 32-х символов';
    }
    $mat = antimat($name);
    if ($mat) {
        $err[] = 'В названии темы обнаружен мат: '.$mat;
    }
    $msg = esc($_POST['msg']);
    if (strlen2($msg)<10) {
        $err[]='Короткое сообщение';
    }
    if (strlen2($msg)>30000) {
        $err[]='Длина сообщения превышает предел в 30 000 символа';
    }
    $mat = antimat($msg);
    if ($mat) {
        $err[]='В тексте сообщения обнаружен мат: '.$mat;
    }

    if (!isset($err)) {
        $_SESSION['time_c_t_forum']=$time;
        $them['id'] = $db->query("INSERT INTO `forum_t` (`id_forum`, `id_razdel`, `time_create`, `id_user`, `name`, `time`, `text`) VALUES(?i, ?i, ?i, ?i, ?, ?i, ?)",
                                 [$forum['id'], $razdel['id'], $time, $user['id'], $name, $time, $msg])->id();

        if ($forum['adm']!=1) { 
            $db->query("INSERT INTO `stena`(`id_user`,`id_stena`,`time`,`info`,`info_1`,`type`) VALUES(?i, ?i, ?i, ?, ?i, ?)",
                       [$user['id'], $user['id'], $time, 'new them in forum', $them['id'], 'them']);
        }
        $q = $db->query("SELECT * FROM `frends` WHERE `user`=?i AND `i`=?i",
                        [$user['id'], 1]);
        while ($f = $q->row()) {
            $a=get_user($f['frend']);
            $lentaSet = $db->query("SELECT * FROM `tape_set` WHERE `id_user`=?i LIMIT ?i",
                                   [$a['id'], 1])->row(); // Общая настройка ленты
            if ($f['lenta_forum'] == 1 && $lentaSet['lenta_forum'] == 1) {
                $db->query("INSERT INTO `tape` (`id_user`, `avtor`, `type`, `time`, `id_file`) VALUES(?i, ?i, ?, ?i, ?i)",
                           [$a['id'], $user['id'], 'them', $time, $them['id']]);
            }
        }
        $db->query("UPDATE `user` SET `rating_tmp`=`rating_tmp`+?i WHERE `id`=?i",
                   [1, $user['id']]);
        $db->query("UPDATE `forum_r` SET `time`=?i WHERE `id`=?i",
                   [$time, $razdel['id']]);
        $_SESSION['message'] = 'Тема успешно создана';
        header("Location: /forum/$forum[id]/$razdel[id]/$them[id]/");
        exit;
    }
}
err();
aut();
echo "<form method=\"post\" name='message' action=\"/forum/$forum[id]/$razdel[id]/?act=new\">";
echo "Название темы:<br />\n";
echo "<input name=\"name\" type=\"text\" maxlength='32' value='' /><br />\n";
if ($set['web'] && is_file(H.'style/themes/'.$set['set_them'].'/altername_post_form.php')) {
    include_once H.'style/themes/'.$set['set_them'].'/altername_post_form.php';
} else {
    echo "Сообщение:$tPanel<textarea name=\"msg\"></textarea><br />\n";
}
echo "<input value=\"Создать\" type=\"submit\" /><br />\n";
echo "</form>\n";
echo "<div class=\"foot\">\n";
echo "<a href=\"/forum/$forum[id]/$razdel[id]/\" title='Вернуться в раздел'>Назад</a><br />\n";
echo "<a href=\"/forum/$forum[id]/\">" . text($forum['name']) . "</a><br />\n";
echo "<a href=\"/forum/\">Форум</a><br />\n";
echo "</div>\n";
