<?php
include_once 'sys/inc/start.php';
include_once 'sys/inc/compress.php';
include_once 'sys/inc/sess.php';
include_once 'sys/inc/home.php';
include_once 'sys/inc/settings.php';
include_once 'sys/inc/db_connect.php';
include_once 'sys/inc/ipua.php';
include_once 'sys/inc/fnc.php';
include_once 'sys/inc/user.php';

only_reg();
 
$kont=$db->query(
                 'SELECT `id_kont` FROM `users_konts` WHERE `type`="deleted" AND `id_user`=?i AND `time`>=?i',
                        [$user['id'], $_SERVER['REQUEST_TIME']])->assoc();
if (count($kont)) {
    foreach ($kont as $konts) {
        $list = [$konts['id_kont']];
    }
    $db->query("DELETE FROM `users_konts` WHERE `id_kont` IN(?li)", [$list]);
    $db->query("DELETE FROM `mail` WHERE `id_user`=?i AND `id_kont` IN(?li)", [$user['id'], $list]);
}
switch (filter_input(INPUT_GET, 'type', FILTER_DEFAULT)) {
    case 'favorite':
        $type='favorite';
        $type_name='Избранные';
    break;
    case 'ignor':
        $type='ignor';
        $type_name='Игнорируемые';
    break;
    case 'deleted':
        $type='deleted';
        $type_name='Корзина';
    break;
    default:
        $type='common';
        $type_name='Активные';
    break;
}

$set['title']=$type_name.' контакты';
include_once 'sys/inc/thead.php';
title();

if (isset($_GET['id'])) {
    $ank=get_user($_GET['id']);
    if ($ank) {
        if (isset($_GET['act'])) {
            switch ($_GET['act']) {
case 'add':
if ($db->query(
    "SELECT COUNT(*) FROM `users_konts` WHERE `id_user`=?i AND `id_kont`=?i",
               [$user['id'], $ank['id']])->el()) {
    $err[]='Этот пользователь уже есть в вашем списке контактов';
} else {
    $db->query(
        "INSERT INTO `users_konts` (`id_user`, `id_kont`, `time`) VALUES (?i, ?i, ?i)",
               [$user['id'], $ank['id'], $time]);
    $_SESSION['message'] = 'Контакт успешно добавлен';
    header("Location: ?");
    exit;
}break;
case 'del':
if (!$db->query(
    "SELECT COUNT(*) FROM `users_konts` WHERE `id_user`=?i AND `id_kont`=?i",
                [$user['id'], $ank['id']])->el()) {
    $warn[]='Этого пользователя нет в вашем списке контактов';
} else {
    $db->query(
        "UPDATE `users_konts` SET `type`=?, `time`=?i WHERE `id_user`=?i AND `id_kont`=?i LIMIT ?i",
               ['deleted', $time+2592000, $user['id'], $ank['id'],  1]);
    $_SESSION['message'] = 'Контакт перенесен в корзину';
    header("Location: ?");
    exit;
    $type='deleted';
}
break;
}
        }
    } else {
        $err[]='Пользователь не найден';
    }
}
if (isset($_GET['act']) && $_GET['act'] == 'edit_ok' && isset($_GET['id'])
    && $db->query(
        "SELECT COUNT(*) FROM `user` WHERE `id`=?i",
                  [$_GET['id']])->el()) {
    $ank=get_user(intval($_GET['id']));
    
    if ($db->query(
        "SELECT COUNT(*) FROM `users_konts` WHERE `id_user`=?i AND `id_kont`=?i",
                   [$user['id'], $ank['id']])->el()) {
        
        $kont=$db->query(
            "SELECT * FROM `users_konts` WHERE `id_user`=?i AND `id_kont`=?i",
                   [$user['id'], $ank['id']])->row();
        
        if (isset($_POST['name']) && $_POST['name']!=($kont['name']!=null?$kont['name']:$ank['nick'])) {
            $name = trim($_POST['name']);
            if (preg_match('#[^A-z0-9\-_\.,\[\]\(\) ]#i', $name)) {
                $err[]='В названии контакта присутствуют запрещенные символы';
            }
            if (strlen($name)>64) {
                $err[]='Название контакта длиннее 64-х символов';
            }
            if (!isset($err)) {
                $db->query(
                    "UPDATE `users_konts` SET `name`=? WHERE `id_user`=?i AND `id_kont`=?i LIMIT ?i",
                           [$name, $user['id'], $ank['id'],  1]
                );
                $_SESSION['message'] = 'Контакт успешно переименован';
                header("Location: ?");
                exit;
            }
        }
        if (isset($_POST['type']) && preg_match('#^(common|ignor|favorite|deleted)$#', $_POST['type']) && $_POST['type']!=$type) {
            if ($_POST['type']=='deleted') {
                $lol=$time+2592000;
            } else {
                $lol=$time;
            }
            $db->query(
                "UPDATE `users_konts` SET `type`=?, `time`=?i WHERE `id_user`=?i AND `id_kont`=?i LIMIT ?i",
                       [$_POST['type'], $lol, $user['id'], $ank['id'],  1]);
            
            $_SESSION['message'] = 'Контакт успешно перенесен';
            header("Location: ?");
            exit;
        }
    } else {
        $err[]='Контакт не найден';
    }
}
aut();
// ОТМЕЧЕННЫЕ
if (is_array($_POST)) {
    foreach ($_POST as $key => $value) {
        if (preg_match('#^post_([0-9]*)$#', $key, $postnum) && $value='1') {
            $delpost[] = $postnum[1];
        }
    }
}
// игнор
if (isset($_POST['ignor'])) {
    if (isset($delpost) && is_array($delpost)) {
        echo '<div class="mess">Контакт(ы): ';
        for ($q=0; $q<=count($delpost)-1; $q++) {
            if (!$db->query(
                            'SELECT COUNT(*) FROM `users_konts` WHERE `id_user`=?i AND `id_kont`=?i',
                            [$user['id'], $delpost[$q]])->el()) {
                $warn[]='Этого пользователя нет в вашем списке контактов';
            } else {
                $db->query("UPDATE `users_konts` SET `type`=?, `time`=?i WHERE `id_user`=?i AND `id_kont`=?i LIMIT ?i",
                           ['ignor', $time, $user['id'], $delpost[$q], 1]);
            }
            $ank_del = get_user($delpost[$q]);
            echo '<font color="#395aff"><b>' . $ank_del['nick'] . '</b></font>, ';
        }
        echo ' добавлен(ы) в черный список</div>';
    } else {
        $err[] = 'Не выделено ни одного контакта';
    }
}
// активные
if (isset($_POST['common'])) {
    if (isset($delpost) && is_array($delpost)) {
        echo '<div class="mess">Контакт(ы): ';
        for ($q=0; $q<=count($delpost)-1; $q++) {
            if (!$db->query("SELECT COUNT(*) FROM `users_konts` WHERE `id_user`=?i AND `id_kont`=?i",
                           [$user['id'], $delpost[$q]])->el()) {
                $warn[]='Этого пользователя нет в вашем списке контактов';
            } else {
                $db->query("UPDATE `users_konts` SET `type`=?, `time`=?i WHERE `id_user`=?i AND `id_kont`=?i LIMIT ?i",
                           ['common', $time, $user['id'], $delpost[$q], 1]);
            }
            $ank_del = get_user($delpost[$q]);
            echo '<font color="#395aff"><b>' . $ank_del['nick'] . '</b></font>, ';
        }
        echo ' успешно перенесен(ы) в активные контакты</div>';
    } else {
        $err[] = 'Не выделено ни одного контакта';
    }
}
// избранное
if (isset($_POST['favorite'])) {
    if (isset($delpost) && is_array($delpost)) {
        echo '<div class="mess">Контакт(ы): ';
        for ($q=0; $q<=count($delpost)-1; $q++) {
            if (!$db->query("SELECT COUNT(*) FROM `users_konts` WHERE `id_user`=?i AND `id_kont`=?i",
                           [$user['id'], $delpost[$q]])->el()) {
                $warn[]='Этого пользователя нет в вашем списке контактов';
            } else {
                $db->query("UPDATE `users_konts` SET `type`=?, `time`=?i WHERE `id_user`=?i AND `id_kont`=?i LIMIT ?i",
                           ['favorite', $time, $user['id'], $delpost[$q], 1]);
            }
            $ank_del_nick = $db->query('SELECT `nick` FROM `user` WHERE `id`=?i', [$delpost[$q]])->el();
            echo '<font color="#395aff"><b>' . $ank_del_nick . '</b></font>, ';
        }
        echo ' успешно перенесен(ы) в избранное</div>';
    } else {
        $err[] = 'Не выделено ни одного контакта';
    }
}
// удаляем
if (isset($_POST['deleted'])) {
    if (isset($delpost) && is_array($delpost)) {
        echo '<div class="mess">Контакт(ы): ';
        for ($q=0; $q<=count($delpost)-1; $q++) {
            if (!$db->query("SELECT COUNT( * ) FROM `users_konts` WHERE `id_user`=?i AND `id_kont`=?i",
                           [$user['id'], $delpost[$q]])->el()) {
                $warn[]='Этого пользователя нет в вашем списке контактов';
            } else {
                $db->query("UPDATE `users_konts` SET `type`=?, `time`=?i WHERE `id_user`=?i AND `id_kont`=?i LIMIT ?i",
                           ['deleted', $time, $user['id'], $delpost[$q], 1]);
            }
            $ank_del_nick = $db->query('SELECT `nick` FROM `user` WHERE `id`=?i', [$delpost[$q]])->el();
            echo '<font color="#395aff"><b>' . $ank_del_nick . '</b></font>, ';
        }
        echo ' успешно перенесен(ы) в корзину</div>';
    } else {
        $err[] = 'Не выделено ни одного контакта';
    }
}
err();
echo "\n<div class='nav2'>\n<span style='float:right;'><a href='/mails.php'><img src='/style/icons/mails.png'> Написать сообщение</a></span><br/>\n</div>\n";
$k_post = $db->query(
                            'SELECT COUNT( * ) FROM `users_konts` WHERE `id_user`=?i AND `type`=?',
                            [$user['id'], $type]
)->el();
if ($k_post) {
    $k_page=k_page($k_post, $set['p_str']);
    $page=page($k_page);
    $start=$set['p_str']*$page-$set['p_str'];

    $q = $db->query(
                        'SELECT usk.*, u.id AS id_kont, u.nick, (
SELECT COUNT( * ) FROM `mail` WHERE `unlink`<>`usk`.`id_user` AND `id_user`=`usk`.id_kont AND `id_kont`=`usk`.`id_user`) k_mess, (
SELECT COUNT( * ) FROM `mail` WHERE `unlink`<>`usk`.`id_user` AND `id_user`=`usk`.`id_user` AND `id_kont`=`usk`.id_kont) k_mess2, (
SELECT COUNT( * ) FROM `mail` WHERE `unlink`<>`usk`.`id_user` AND `id_user`=`usk`.`id_user` AND `id_kont`=`usk`.id_kont AND `read`="0") k_mess_to, (
SELECT COUNT( * ) FROM `mail` WHERE `id_user`=`usk`.id_kont AND `id_kont`=`usk`.`id_user` AND `read`="0") k_new_mess
FROM `users_konts` usk
JOIN `user` u ON u.id=`usk`.id_kont
WHERE `usk`.`id_user`=?i AND `usk`.`type`=? ORDER BY `usk`.`time` DESC, `usk`.`new_msg` DESC LIMIT ?i OFFSET ?i',
                        [$user['id'], $type, $set['p_str'], $start]);
    
    echo '<form method="post" action="">';

    while ($post = $q->row()) {
        if ($post['k_mess_to'] > 0) {
            $post['k_mess_to'] = ' <font color=red><b>&uarr;</b></font> [<font color=red>' . $post['k_mess_to'] . '</font>]';
        } else {
            $post['k_mess_to'] = null;
        }
        /*-----------зебра-----------*/
        if ($num == 0) {
            echo "  <div class='nav1'>\n";
            $num=1;
        } elseif ($num == 1) {
            echo "  <div class='nav2'>\n";
            $num=0;
        }
        /*---------------------------*/
        if ($set['set_show_icon'] == 2) {
            avatar($post['id_kont']);
        } elseif ($set['set_show_icon'] == 1) {
            echo status($post['id_kont']);
        }
        echo group($post['id_kont']) . ' ' . user::nick($post['id_kont']) . '';
        echo online($post['id_kont']) . medal($post['id_kont']) . '<br />';
    
        echo '<input type="checkbox" name="post_' . $post['id_kont'] . '" value="1" />'."\n";
        echo($post['k_new_mess'] != 0 ? '<img src="/style/icons/new_mess.gif" alt="*" /> ' : '<img src="/style/icons/msg.gif" alt="*" /> ') . '<a href="/mail.php?id=' . $post['id_kont'] . '">' . ($post['name'] != null ? $post['name'] : 'Сообщения') . '</a> ';
        echo($post['k_new_mess'] != 0 ? '<font color="red">' : null) . ($post['k_new_mess'] != 0 ? '+' . $post['k_new_mess'] : '(' . $post['k_mess'] . '/' . $post['k_mess2'] . ')' . $post['k_mess_to']) . ($post['k_new_mess'] != 0 ? '</font> ' : null);
    
        echo "\n".'</div>'."\n";
    }
    echo '<div class="nav2">'."\n";
    if ($type != 'deleted') {
        echo '<input value="Удалить" type="submit" name="deleted" /> '."\n";
    }
    if ($type != 'common') {
        echo '<input value="Активные" type="submit" name="common" /> '."\n";
    }
    if ($type != 'favorite') {
        echo '<input value="Избранное" type="submit" name="favorite" /> '."\n";
    }
    if ($type != 'ignor') {
        echo '<input value="Игнор" type="submit" name="ignor" /> '."\n";
    }
    
    echo '</div>'."\n";
    echo '</form>'."\n";
    if ($k_page > 1) {
        str("?type=$type&amp;", $k_page, $page);
    } // Вывод страниц
} else {
    echo '<div class="mess">'."\n";
    echo 'Ваш список контактов пуст'."\n";
    echo '</div>'."\n";
}
if ($type == 'deleted') {
    echo '<div class="mess">'."\n".
    'Внимание. Контакты хранятся в корзине не более 1 месяца.<br />После этого они полностью удаляются.'."\n".
    '</div>'."\n";
}
if ($type == 'ignor') {
    echo '<div class="mess">'."\n".
    'Уведомления о сообщениях от этих контактов не появляются'."\n".
    '</div>'."\n";
}
if ($type == 'favorite') {
    echo '<div class="mess">'."\n".
    'Уведомления о сообщениях от этих контактов выделяются'."\n".
    '</div>'."\n";
}
echo '<div class="main">'."\n";
$cnt = $db->query(
                        'SELECT (
SELECT COUNT( * ) FROM `users_konts` WHERE `id_user`=?i AND `type`="common") common, (
SELECT COUNT( * ) FROM `users_konts` WHERE `id_user`=?i AND `type`="favorite") favorite, (
SELECT COUNT( * ) FROM `users_konts` WHERE `id_user`=?i AND `type`="ignor") ignor, (
SELECT COUNT( * ) FROM `users_konts` WHERE `id_user`=?i AND `type`="deleted") deleted',
                        [$user['id'], $user['id'], $user['id'], $user['id']])->row();

echo($type == 'common' ? '<b>' : null) . '<img style="padding:2px;" src="/style/icons/activ.gif" alt="*" /> <a href="?type=common">Активные</a>' . ($type == 'common' ? '</b>' : null) . ' (' .
$cnt['common'] . ')<br />';
echo($type == 'favorite' ? '<b>' : null) . '<img style="padding:2px;" src="/style/icons/star_fav.gif" alt="*" /> <a href="?type=favorite">Избранные</a>' . ($type == 'favorite' ? '</b>' : null) . ' (' .
$cnt['favorite'] . ')<br />';
echo($type == 'ignor' ? '<b>' : null) . '<img style="padding:2px;" src="/style/icons/spam.gif" alt="*" /> <a href="?type=ignor">Игнорируемые</a>' . ($type == 'ignor' ? '</b>' : null) . ' (' .
$cnt['ignor'] . ')<br />';
echo($type == 'deleted' ? '<b>' : null) . '<img style="padding:2px;" src="/style/icons/trash.gif" alt="*" /> <a href="?type=deleted">Корзина</a>' . ($type == 'deleted' ? '</b>' : null) . ' (' .
$cnt['deleted'] .')<br />'."\n";
echo '</div>'."\n";

include_once 'sys/inc/tfoot.php';
