<?php
$umnik_last = $db->query(
    "SELECT * FROM `chat_post` WHERE `room`=?i AND `umnik_st`<>? ORDER BY id DESC",
                         [$room['id'], '0'])->row();
if ($umnik_last != null && $umnik_last['umnik_st'] != 4 && $umnik_last['umnik_st'] != 0) {
    $umnik_vopros = $db->query(
        "SELECT * FROM `chat_vopros` WHERE `id`=?i",
                               [$umnik_last['vopros']])->row();
    $umnik_post = $db->query(
        "SELECT u.id AS id_user, u.nick FROM `chat_post` ch
JOIN `user` u ON u.id=ch.id_user WHERE `room`=?i AND `msg` LIKE '%?e%' AND `umnik_st`=? AND ch.`time`>=?i ORDER BY ch.`id` ASC LIMIT ?i",
                             [$room['id'], $umnik_vopros['otvet'], '0', $time - $umnik_last['time'], 1])->row();
    if ($umnik_post!=null) {
        
        $add_balls=0;
        if ($umnik_last['umnik_st']==1) {
            $add_balls=25;
            $pods='не используя подсказок';
        }
        if ($umnik_last['umnik_st']==2) {
            $add_balls=10;
            $pods='используя одну подсказку';
        }
        if ($umnik_last['umnik_st']==3) {
            $add_balls=5;
            $pods='используя обе посказки';
        }
        $msg="Молодец, [b]$umnik_post[nick][/b].\nТы первым дал верный ответ: [b][green]$umnik_vopros[otvet][/green][/b] $pods.\n[b]$umnik_post[nick][/b] получает $add_balls баллов.\nСледующий вопрос через $set[umnik_new] сек.";
        $db->query(
            "INSERT INTO `chat_post` (`umnik_st`, `time`, `msg`, `room`, `vopros`, `privat`) VALUES(?, ?i, ?, ?i, ?i, ?i)",
                   ['4', $time, $msg, $room['id'], $umnik_vopros['id'], 0]);
        $db->query(
            "UPDATE `user` SET `balls`=`balls`+?i WHERE `id`=?i",
                   [$add_balls, $umnik_post['id_user']]);
    }
}
$umnik_last1 = $db->query(
    "SELECT * FROM `chat_post` WHERE `room`=?i AND `umnik_st`=? ORDER BY id DESC",
                          [$room['id'], '1'])->row();
if ($umnik_last1 != null && $umnik_last['umnik_st'] != 4 && $umnik_last1['time'] < time()-$set['umnik_time']) {
    $umnik_vopros = $db->query(
        "SELECT * FROM `chat_vopros` WHERE `id`=?i",
                               [$umnik_last1['vopros']])->row();
    $msg="На вопрос никто не ответил.\nПравильный ответ: [red]$umnik_vopros[otvet][/red].\nСледующий вопрос через $set[umnik_new] сек.";
    $db->query(
        "INSERT INTO `chat_post` (`umnik_st`, `time`, `msg`, `room`, `vopros`, `privat`) VALUES(?, ?i, ?, ?i, ?i, ?i)",
               ['4', $time, $msg, $room['id'], $umnik_vopros['id'], 0]);
}
$umnik_last = $db->query(
    "SELECT * FROM `chat_post` WHERE `room`=?i AND `umnik_st`<>? ORDER BY id DESC",
                         [$room['id'], '0'])->row();
if ($umnik_last == null || $umnik_last['umnik_st'] == 4 && $umnik_last['time'] < time()-$set['umnik_new']) {
    // задается вопрос
    $k_vopr=$db->query("SELECT COUNT(*) FROM `chat_vopros`")->el();
    $umnik_vopros = $db->query(
        "SELECT * FROM `chat_vopros` LIMIT ?i OFFSET ?i",
                               [1, rand(0, $k_vopr)])->row();
    $msg="[b]Вопрос:[/b] \"$umnik_vopros[vopros]\"\n[b]Ответ:[/b] слово из ".strlen2($umnik_vopros['otvet'])." букв";
    $db->query(
        "INSERT INTO `chat_post` (`umnik_st`, `time`, `msg`, `room`, `vopros`, `privat`) values(?, ?i, ?, ?i, ?i, ?i)",
               ['1', $time, $msg, $room['id'], $umnik_vopros['id'], 0]);
}
if ($umnik_last != null && $umnik_last['umnik_st'] == 1 && $umnik_last['time'] < time()-$set['umnik_help']) {
    $umnik_vopros = $db->query(
        "SELECT * FROM `chat_vopros` WHERE `id`=?i",
                               [$umnik_last['vopros']])->row();
    if (function_exists('iconv_substr')) {
        $help=iconv_substr($umnik_vopros['otvet'], 0, 1, 'utf-8');
    } else {
        $help=substr($umnik_vopros['otvet'], 0, 2);
    }
    for ($i=0;$i<strlen2($umnik_vopros['otvet'])-1 ;$i++) {
        $help .= '*';
    }
    $msg="[b]Вопрос:[/b] \"$umnik_vopros[vopros]\"\n[b]Первая подсказка:[/b] $help (".strlen2($umnik_vopros['otvet'])." букв)";
    $db->query(
        "INSERT INTO `chat_post` (`umnik_st`, `time`, `msg`, `room`, `vopros`, `privat`) values(?, ?i, ?, ?i, ?i, ?i)",
               ['2', $time, $msg, $room['id'], $umnik_vopros['id'], 0]);
}
if ($umnik_last != null && $umnik_last['umnik_st'] == 2 && $umnik_last['time'] < time()-$set['umnik_help']) {
    $umnik_vopros = $db->query(
        "SELECT * FROM `chat_vopros` WHERE `id`=?i",
                               [$umnik_last['vopros']])->row();
    if (function_exists('iconv_substr')) {
        $help=iconv_substr($umnik_vopros['otvet'], 0, 2, 'utf-8');
    } else {
        $help=substr($umnik_vopros['otvet'], 0, 4);
    }
    for ($i=0;$i<strlen2($umnik_vopros['otvet'])-2 ;$i++) {
        $help.='*';
    }
    $msg="[b]Вопрос:[/b] \"$umnik_vopros[vopros]\"\n[b]Вторая подсказка:[/b] $help (".strlen2($umnik_vopros['otvet'])." букв)";
    $db->query(
        "INSERT INTO `chat_post` (`umnik_st`, `time`, `msg`, `room`, `vopros`, `privat`) values(?, ?i, ?, ?i, ?i, ?i)",
               ['3', $time, $msg, $room['id'], $umnik_vopros['id'], 0]);
}
