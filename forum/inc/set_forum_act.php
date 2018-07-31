<?php
if (user_access('forum_for_edit') && isset($_GET['act']) && isset($_GET['ok']) && $_GET['act']=='set' && isset($_POST['name']) && isset($_POST['opis']) && isset($_POST['pos'])) {
    $name=esc(trim($_POST['name']));
    if (strlen2($name)<3) {
        $err='Слишком короткое название';
    }
    if (strlen2($name)>32) {
        $err='Слишком днинное название';
    }

    if (!isset($_POST['icon']) || $_POST['icon']==null) {
        $FIcon='default';
    } else {
        $FIcon=preg_replace('#[^a-z0-9 _\-\.]#i', null, $_POST['icon']);
    }
    $opis=esc(trim($_POST['opis']));
    if (isset($_POST['translit2']) && $_POST['translit2']==1) {
        $opis=translit($opis);
    }
    if (strlen2($opis)>512) {
        $err='Слишком длинное описание';
    }

    $pos=intval($_POST['pos']);
    if (!isset($err)) {
        if ($user['level']>=3) {
            if (isset($_POST['adm']) && $_POST['adm']==1) {
                admin_log('Форум', 'Подфорумы', "Подфорум '" . $forum['name'] . "' только для администрации");
                $adm=1;
            } else {
                $adm=0;
            }
            $db->query(
    "UPDATE `forum_f` SET `adm`=?string WHERE `id`=?i",
           [$adm, $forum['id']]);
        }
        if ($forum['name']!=$name) {
            admin_log('Форум', 'Подфорумы', "Подфорум '" . $forum['name'] . "' переименован в '$name'");
        }
        if ($forum['opis']!=$opis) {
            admin_log('Форум', 'Подфорумы', "Изменено описание подфорума '$name'");
        }
        $db->query(
    "UPDATE `forum_f` SET `name`=?, `opis`=?,`icon`=?, `pos`=?i WHERE `id`=?i",
           [$name, $opis, $FIcon, $pos, $forum['id']]);
        $forum=$db->query("SELECT * FROM `forum_f` WHERE `id`=?i", [$forum['id']])->row();
        msg('Изменения успешно приняты');
    }
}

if (isset($_GET['act']) && isset($_GET['ok']) && $_GET['act']=='forum_delete' && user_access('forum_for_delete')) {
    $db->query("DELETE FROM `forum_f` WHERE `id`=?i", [$forum['id']]);
    $db->query("DELETE FROM `forum_r` WHERE `id_forum`=?i", [$forum['id']]);
    $db->query("DELETE FROM `forum_t` WHERE `id_forum`=?i", [$forum['id']]);
    $db->query("DELETE FROM `forum_p` WHERE `id_forum`=?i", [$forum['id']]);
    
	admin_log('Форум', 'Подфорумы', "Удаление подфорума '" . $forum['name'] . "'");
    msg('Подфорум успешно удален');
    err();
    aut();
    echo "<a href=\"/forum/\">В форум</a><br />\n";
    include_once H . 'sys/inc/tfoot.php';
}

if (user_access('forum_razd_create') && (isset($_GET['act']) && isset($_GET['ok']) && $_GET['act']=='new' && isset($_POST['name']))) {
    $name=esc(trim($_POST['name']));
    if (strlen2($name)<2) {
        $err='Слишком короткое название';
    }
    if (strlen2($name)>32) {
        $err='Слишком длинное название';
    }
    if (!isset($err)) {
        admin_log('Форум', 'Разделы', 'Создание раздела "' . $name . '" в подфоруме "' . $forum['name'] . '"');
        $db->query(
        "INSERT INTO `forum_r` (`id_forum`, `opis`,`name`, `time`) VALUES(?i, ?, ?, ?i)",
               [$forum['id'], $_POST['opis'], $name, $time]);
        msg('Раздел успешно создан');
    }
}
