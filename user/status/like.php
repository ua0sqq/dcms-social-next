<?php
include_once '../../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

$set['title']='Like к статусу';
include_once H . 'sys/inc/thead.php';
title();

if (!$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)) {
    header('Location: /');
    exit;
}

if (!$db->query(
    "SELECT COUNT( * ) FROM `status` WHERE `id`=?i",
                [$id])->el()) {
    header('Location: index.php?' . SID);
    exit;
}
 // Статус
$status = $db->query(
    "SELECT st.id, st.msg, u.id AS id_user, u.nick FROM `status` st JOIN `user` u ON u.id=st.id_user WHERE st.`id`=?i",
                [$id])->row();

err();
aut(); // форма авторизации
?>
<div class="foot">
    <img src="/style/icons/str2.gif" alt="*"> <a href="/info.php?id=<?php
    echo $status['id_user'];?>"><?php echo $status['nick'];?></a> | <a href="index.php?id=<?php
    echo $status['id_user'];?>">Статусы</a> | <b>Оценки</b>
</div>
<?php

$k_post=$db->query("SELECT COUNT( * ) FROM `status_like` WHERE `id_status`=?i",
                   [$id])->el();

if (!$k_post) {
    ?>
<div class="mess">
    За статус еще не голосовали
</div>
<?php
} else {
        $k_page=k_page($k_post, $set['p_str']);
        $page=page($k_page);
        $start=$set['p_str']*$page-$set['p_str'];

        $q=$db->query(
    "SELECT stl.id, stl.`time`, u.id AS id_user, u.nick, st.id AS id_status, st.msg
FROM `status_like` stl
JOIN `user` u ON u.id=stl.id_user
LEFT JOIN `status` st ON (st.id_user=u.id AND pokaz=1)
WHERE stl.`id_status`=?i ORDER BY `id` DESC LIMIT ?i, ?i",
                [$id, $start, $set['p_str']]);

        while ($post = $q->row()) {
            if ($num==0) {
                ?>
<div class="nav1">
<?php
        $num=1;
            } elseif ($num==1) {
                ?>
<div class="nav2">
<?php
        $num=0;
            }

            echo status($post['id_user'])." <a href='/info.php?id={$post['id_user']}'>$post[nick]</a> \n";
            echo medal($post['id_user']) . online($post['id_user']) . " (".vremja($post['time']).")"; ?>
    <div class="st_1"></div>
    <div class="st_2">
        <?php
        if ($post['id_status']) {
            ?><a href="/user/status/komm.php?id=<?php echo $post['id_status']; ?>"><?php echo output_text($post['msg']); ?></a>
<?php
        } else {
            ?><p>Я <?= $post['nick']; ?></p>
<?php
        } ?>
    </div>
</div>
<?php
        }

        if ($k_page>1) {
            str('like.php?id=' . $id . '&amp;', $k_page, $page);
        } // Вывод страниц
    }
?>
<div class="foot">
    <img src="/style/icons/str2.gif" alt="*"> <a href="/info.php?id=<?php
    echo $status['id_user'];?>"><?php echo $status['nick'];?></a> | <a href="index.php?id=<?php
    echo $status['id_user'];?>">Статусы</a> | <b>Оценки</b>
</div>
<?php

include_once H . 'sys/inc/tfoot.php';
