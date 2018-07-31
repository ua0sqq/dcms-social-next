<?php
include_once '../../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';
if (isset($_GET['id'])) {
    $sid = intval($_GET['id']);
} else {
    $sid = $user['id'];
}
$ank = get_user($sid);
$set['title']="Друзья ".$ank['nick'].""; // заголовок страницы
include_once H . 'sys/inc/thead.php';
title();
aut();

/*
==================================
Приватность станички пользователя
Запрещаем просмотр друзей
==================================
*/
$pattern = 'SELECT ust.privat_str FROM `user_set` ust WHERE ust.`id_user`=?i';
$data = [$ank['id']];
if (isset($user)) {
    $pattern = 'SELECT ust.privat_str, (
SELECT COUNT(*) FROM `frends` WHERE (`user`=?i AND `frend`=ust.`id_user`) OR (`user`=ust.`id_user` AND `frend`=?i)) frend, (
SELECT COUNT(*) FROM `frends_new` WHERE (`user`=?i AND `to`=ust.`id_user`) OR (`user`=ust.`id_user` AND `to`=?i)) new_frend
FROM `user_set` ust WHERE ust.`id_user`=?i';
    $data = [$user['id'], $user['id'], $user['id'], $user['id'], $ank['id']];
}

$uSet = $db->query($pattern, $data)->row();

if ($ank['id'] != $user['id'] && $user['group_access'] == 0) {
    // Начинаем вывод если стр имеет приват настройки
    if (($uSet['privat_str'] == 2 && $uSet['frend'] != 2) || $uSet['privat_str'] == 0) {
        if ($ank['group_access']>1) {
            echo "<div class='err'>".$ank['group_name']."</div>\n";
        }
        echo "<div class='nav1'>";
        echo group($ank['id'])." ";
        echo user::nick($ank['id'], 1, 1, 1);
        echo "</div>";
        echo "<div class='nav2'>";
        user::avatar($ank['id']);
        echo "</div>";
    }
    if ($uSet['privat_str'] == 2 && $uSet['frend'] != 2) { // Если только для друзей
        echo '<div class="mess">';
        echo 'Просматривать друзей пользователя могут только его друзья!';
        echo '</div>';
        // В друзья
        if (isset($user)) {
            echo '<div class="nav1">';
            if ($uSet['frend_new'] == 0 && $uSet['frend']==0) {
                echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?add=".$ank['id']."'>Добавить в друзья</a><br />\n";
            } elseif ($uSet['frend_new'] == 1) {
                echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?otm=$ank[id]'>Отклонить заявку</a><br />\n";
            } elseif ($uSet['frend'] == 2) {
                echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?del=$ank[id]'>Удалить из друзей</a><br />\n";
            }
            echo "</div>";
        }
        include_once H . 'sys/inc/tfoot.php';
        exit;
    }
    // Если закрыта
    if ($uSet['privat_str'] == 0) {
        echo '<div class="mess">';
        echo 'Пользователь запретил просматривать его друзей!';
        echo '</div>';
        include_once H . 'sys/inc/tfoot.php';
        exit;
    }
}
// отмеченные
if (isset($user) && $user['id']==$ank['id']) {
    if (isset($_GET['delete'])) {
        foreach ($_POST as $key => $value) {
            if (preg_match('#^post_([0-9]*)$#', $key, $postnum) && $value='1') {
                $delpost[]=$postnum[1];
            }
        }
        if (isset($_POST['delete'])) {
            if (isset($delpost) && is_array($delpost)) {
                echo "<div class='mess'>Друзья: ";
                for ($q=0; $q<=count($delpost)-1; $q++) {
                    if (!$db->query(
                        "SELECT COUNT( * ) FROM `frends` WHERE (`user`=?i AND `frend`=?i) OR (`user`=?i AND `frend`=?i)",
                                   [$user['id'], $delpost[$q], $delpost[$q], $user['id']])->el()) {
                        $warn[] = 'Этого пользователя нет в вашем списке контактов';
                    } else {
                        if ($db->query(
                            "SELECT COUNT( * ) FROM `frends` WHERE (`user`=?i AND `frend`=?i) OR (`user`=?i AND `frend`=?i)",
                                       [$user['id'], $delpost[$q], $delpost[$q], $user['id']])->el()) {
                            
                            // Uведомления друзьям
                            $db->query(
                                "INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES (?i, ?i, ?i, ?, ?i)",
                                       [$user['id'], $delpost[$q], $user['id'], 'del_frend', $time]);
                            $db->query(
                                "DELETE FROM `frends` WHERE `user`=?i AND `frend`=?i",
                                       [$user['id'], $delpost[$q]]);
                            $db->query(
                                "DELETE FROM `frends` WHERE `user`=?i AND `frend`=?i",
                                       [$delpost[$q], $user['id']]);
                            $db->query(
                                "DELETE FROM `frends_new` WHERE `user`=?i AND `to`=?i",
                                       [$delpost[$q], $user['id']]);
                            $db->query(
                                "DELETE FROM `frends_new` WHERE `user`=?i AND `to`=?i",
                                       [$user['id'], $delpost[$q]]);
                            $db->query("OPTIMIZE TABLE `frends`");
                            $db->query("OPTIMIZE TABLE `frends_new`");
                            $msgno = 'К сожалению, пользователь [b]' . $user['nick'] . '[/b] удалил вас из списка друзей. ';
                            $db->query(
                                "INSERT INTO `mail` (`id_user`, `id_kont`, `msg`, `time`) VALUES(?i, ?i, ?, ?i)",
                                       [0, $delpost[$q], $msgno, $time]);
                        }
                    }
                    $ank_del = get_user($delpost[$q]);
                    echo "<font color='#395aff'><b>".$ank_del['nick']."</b></font>, ";
                }
                echo " удален(ы) из списка ваших друзей</div>";
            } else {
                $_SESSION['err'] = 'Не выделено ни одного контакта';
            }
        }
    }
}
err();
// Panel
$sql = null;
if ($ank['id'] == $user['id']) {
    $sql = ', (
    SELECT COUNT( * ) new_frends FROM `frends_new` WHERE `to` =' . $ank['id'] . ')q3';
}
$cnt = $db->query(
    "SELECT * FROM (
    SELECT COUNT( * ) all_frends FROM `frends` WHERE `user`=?i AND `i`=?i)q1, (
    SELECT COUNT( * ) onl_frends FROM `frends`
    JOIN `user` ON `frends`.`frend`=`user`.`id`
    WHERE `frends`.`user`=?i AND `frends`.`i`=?i AND `user`.`date_last`>?i)q2?q",
                  [$ank['id'], 1, $ank['id'], 1, TIME_600, $sql])->row();

echo "<div id='comments' class='menus'>";
echo "<div class='webmenu'>";
echo "<a href='index.php?id=$ank[id]' class='activ'>Все (".$cnt['all_frends'].")</a>";
echo "</div>";
echo "<div class='webmenu last'>";
echo "<a href='online.php?id=$ank[id]'>Онлайн (".$cnt['onl_frends'].")</a>";
echo "</div>";
if ($ank['id'] == $user['id']) {
    echo "<div class='webmenu last'>";
    echo "<a href='new.php'>Заявки (".$cnt['new_frends'].")</a>";
    echo "</div>";
}
echo "</div>";
// End Panel

$k_post = $cnt['all_frends'];
$k_page = k_page($k_post, $set['p_str']);
$page = page($k_page);
$start = $set['p_str']*$page - $set['p_str'];

$q = $db->query(
    "SELECT usr.id, usr.date_last FROM `frends` frn
    JOIN `user` usr ON `frn`.`frend`=`usr`.`id`
    WHERE frn.`user`=?i AND frn.`i`=?i ORDER BY frn.`time` DESC LIMIT ?i OFFSET ?i",
                [$ank['id'], 1, $set['p_str'], $start]);

if (isset($user) && $user['id']==$ank['id']) {
    if ($k_post>0) {
        echo "<form method='post' action='?$page&amp;delete'>";
    }
}
if ($k_post==0) {
    echo '<div class="mess">';
    echo ' '.($ank['id']==$user['id'] ? 'У Вас ' : 'У '.$ank['nick'].' ').' нет друзей.';
    echo '</div>';
}
while ($frend = $q->row()) {
    /*-----------зебра-----------*/
    if ($num==0) {
        echo '<div class="nav1">';
        $num=1;
    } elseif ($num==1) {
        echo '<div class="nav2">';
        $num=0;
    }
    /*---------------------------*/
    echo '<table><td style="width:'.($webbrowser ? '85px;' : '55px;').'">';
    echo user::avatar($frend['id'], 1);
    echo '</td><td style="width:80%;">';
    if (isset($user) && $user['id']==$ank['id']) {
        echo " <input type='checkbox' name='post_$frend[id]' value='1' /> ";
    }
    echo " ".group($frend['id'])." \n";
    echo user::nick($frend['id'], 1, 1, 1);
    echo '<br/><img src="/style/icons/alarm.png"> '.($webbrowser ? 'Посл. активность:' : null).' '.vremja($frend['date_last']).' </td><td style="width:18px;">';
    if (isset($user)) {
        echo "<a href=\"/mail.php?id=$frend[id]\"><img src='/style/icons/pochta.gif' alt='*' /></a><br/>\n";
        if ($ank['id']==$user['id']) {
            echo "<a href='create.php?del=$frend[id]'><img src='/style/icons/delete.gif' alt='*' /></a>";
        }
    }
    echo '</td></table></div>';
}
if (isset($user) && $user['id']==$ank['id']) {
    if ($k_post>0) {
        echo "<div class='c2'>";
        echo " Отмеченных друзей:<br />";
        echo "<input value=\"Удалить\" type=\"submit\" name=\"delete\" />";
        echo "</div>";
        echo "</form>\n";
    }
}
if ($k_page>1) {
    str("?id=".$ank['id']."&amp;", $k_page, $page);
} // Вывод страниц

include_once H . 'sys/inc/tfoot.php';
