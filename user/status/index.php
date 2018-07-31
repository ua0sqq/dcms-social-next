<?php
include_once '../../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

 // Автор статусов
if ($id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)) {
    $anketa = $db->query('SELECT u.id, u.nick, u.group_access, g.`name` AS group_name FROM `user` u
LEFT JOIN `user_group` g ON g.id=u.group_access WHERE u.id=?', [$id])->row();
} else {
    $anketa = isset($user) ? $user : null;
}
if (!$anketa) {
    http_response_code(404);
    header('Location: /err.php?err=404');
    exit;
}
if ($reset = filter_input(INPUT_GET, 'reset', FILTER_VALIDATE_INT)) {
    $status = $db->query("SELECT id, id_user FROM `status` WHERE `id`=?i",
                       [$reset])->row();
    if ($status['id_user'] == $user['id']) {
        $db->query("UPDATE `status` SET `pokaz`=0 WHERE `id_user`=?i",
                   [$user['id']]);
        $db->query("UPDATE `status` SET `pokaz`=1 WHERE `id`=?i",
                   [$status['id']]);
        $_SESSION['message'] = 'Статус упешно включен';
        header('Location: index.php?id=' . $anketa['id']);
        exit;
    }
}
$set['title'] = 'Статусы ' . $anketa['nick'];
include_once H . 'sys/inc/thead.php';
title();
err();
aut(); // форма авторизации

// Приватность станички пользователя
$uSet = $db->query(
        "SELECT uset.privat_str, (
SELECT COUNT( * ) FROM `frends` WHERE (`user`=?i AND `frend`=uset.id_user) OR (`user`=uset.id_user AND `frend`=?i)) frend, (
SELECT COUNT( * ) FROM `frends_new` WHERE (`user`=?i AND `to`=uset.id_user) OR (`user`=uset.id_user AND `to`=?i)) frend_new
FROM `user_set` uset WHERE uset.`id_user`=?i  LIMIT ?i",
                       [$user['id'], $user['id'], $user['id'], $user['id'], $anketa['id'], 1])->row();
    
if ($anketa['id'] != $user['id'] && $user['group_access'] == 0) {
    if (($uSet['privat_str'] == 2 && $uSet['frend'] != 2) || $uSet['privat_str'] == 0) { // Начинаем вывод если стр имеет приват настройки
        if ($anketa['group_access'] > 1) {
            echo "<div class='err'>$anketa[group_name]</div>\n";
        }
?>
<div class="nav1">
    <?php echo group($anketa['id']) . ' ' . $anketa['nick'] . ' ' . medal($anketa['id']) . ' ' . online($anketa['id']);?>

</div>
<div class="nav2">
    <?= avatar($anketa['id']);?>
</div>
<?php
    }
    // Если только для друзей
    if ($uSet['privat_str'] == 2 && $uSet['frend'] != 2) {
?>
<div class="mess">
    Комментировать статус пользователя могут только его друзья!
</div>
<?php
        
        // В друзья
        if (isset($user)) {
?>
<div class="nav1">
<?php
            if ($uSet['frend_new'] == 0 && $uSet['frend'] == 0) {
?>
    <p><img src='/style/icons/druzya.png' alt=""/> <a href="/user/frends/create.php?add=<?php echo $anketa['id'];?>">Добавить в друзья</a></p>
<?php
            } elseif ($uSet['frend_new'] == 1) {
?>
    <p><img src="/style/icons/druzya.png" alt=""/> <a href="/user/frends/create.php?otm=<?php echo $anketa['id'];?>">Отклонить заявку</a></p>
<?php
            } elseif ($uSet['frend'] == 2) {
?>
    <p><img src="/style/icons/druzya.png" alt=""/> <a href="/user/frends/create.php?del=<?php echo $anketa['id'];?>">Удалить из друзей</a></p>
<?php
            }
?>
</div>
<?php
        }
        include_once H . 'sys/inc/tfoot.php';
        exit;
    }
    
    if ($uSet['privat_str'] == 0) { // Если закрыта
?>
<div class="mess">
    Пользователь запретил комментировать его статусы!
</div>
<?php        
        include_once H . 'sys/inc/tfoot.php';
        exit;
    }
}
    
echo "<div class='foot'>";
echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/info.php?id=$anketa[id]\">$anketa[nick]</a> | <b>Статусы</b>";
echo "</div>";

$k_post=$db->query("SELECT COUNT(*) FROM `status` WHERE `id_user`=?i",
                   [$anketa['id']])->el();

if (!$k_post) {
    echo "<div class='mess'>\n";
    echo "Нет статусов\n";
    echo "</div>\n";
} else {
    
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
$q=$db->query("SELECT st.id, st.msg, st.pokaz, u.id AS id_user, u.`level`, (
SELECT COUNT( * ) FROM `status_komm` WHERE `id_status`=st.id) cnt
FROM `status` st
JOIN `user` u ON u.id=st.id_user
WHERE st.`id_user`=?i ORDER BY `pokaz` DESC, `id` DESC LIMIT ?i, ?i",
                   [$anketa['id'], $start, $set['p_str']]);

while ($post = $q->row()) {

    if ($num==0) {
        echo '<div class="nav1">';
        $num=1;
    } elseif ($num==1) {
        echo '<div class="nav2">';
        $num=0;
    }

    echo '<div class="st_1"></div>';
    echo '<div class="st_2">';
    echo output_text($post['msg']);
    echo "</div>";
    echo "<a href='komm.php?id=$post[id]'><img src='/style/icons/bbl4.png' alt=''/>" . $post['cnt'] . "</a> ";
    if ($post['pokaz']==0) {
        if (isset($user) && ($user['level']!=0 || $user['id']==$post['id_user'])) {
            echo "[<a href=\"index.php?id=".$anketa['id']."&amp;reset=$post[id]\"><img src='/style/icons/ok.gif' alt=''/> вкл</a>]\n";
        }
        if (isset($user) && ($user['level']>$post['level'] || $user['level']!=0 || $user['id']==$post['id_user'])) {
            echo " [<a href=\"./delete.php?id=$post[id]\"><img src='/style/icons/delete.gif' alt=''/> удл</a>]\n";
        }
    } else {
        if (isset($user) && ($user['level']>$post['level'] || $user['level']!=0 || $user['id']==$post['id_user'])) {
            echo " <font color='green'>Установлен</font>\n";
        }
        if (isset($user) && ($user['level']>$post['level'] || $user['level']!=0 || $user['id']==$post['id_user'])) {
            echo " [<a href=\"./delete.php?id=$post[id]\"><img src='/style/icons/delete.gif' alt=''/> удл</a>]\n";
        }
    }
    echo '</div>';
}

if ($k_page>1) {
    str("index.php?id=".$anketa['id'].'&amp;', $k_page, $page);
} // Вывод страниц
}
echo "<div class='foot'>";
echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/info.php?id=$anketa[id]\">$anketa[nick]</a> | <b>Статусы</b>";
echo "</div>";

include_once H . 'sys/inc/tfoot.php';
