<?php
include_once '../../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

only_reg();

// Очистка гостей
if (isset($_GET['truncate'])) {
    if ($db->query(
        "DELETE FROM `my_guests` WHERE `id_ank`=?i",
                  [$user['id']])->ar()) {
        $_SESSION['message'] = 'Список гостей очищен';
    } else {
        $_SESSION['err'] = 'Список пуст!';
    }
}
// заголовок страницы
$set['title'] = 'Гости';
include_once H . 'sys/inc/thead.php';
title();
aut();

?>
<!-- ./ guests list -->
<div class="foot">
    <img src="/style/icons/str2.gif" alt=""> <a href="/info.php?id=<?php echo $user['id'];?>"><?php echo $user['nick'];?></a> | <strong>Гости</strong>
</div>
<?php

$k_post = $db->query(
    "SELECT COUNT( * ) FROM `my_guests` WHERE `id_ank`=?i",
                  [$user['id']])->el();

if (!$k_post) {
    ?>
<div class="mess">
    Вашу страничку еще не посещали
</div>
<?php
} else {
        $k_page = k_page($k_post, $set['p_str']);
        $page = page($k_page);
        $start = $set['p_str'] * $page - $set['p_str'];

        $q = $db->query(
        "SELECT `mg`.`id`, `mg`.`id_user`, `mg`.`read`, `mg`.`time`, `u`.`nick`
FROM `my_guests` mg
JOIN `user` u ON `u`.`id`=`mg`.`id_user`
WHERE `id_ank`=?i LIMIT ?i, ?i",
                    [$user['id'], $start, $set['p_str']]);

        while ($post = $q->row()) {
            if ($num == 0) {
                ?>
<div class="nav1">
    <?php
            $num = 1;
            } elseif ($num == 1) {
                ?>
<div class="nav2">
    <?php
            $num = 0;
            }
            echo avatar($post['id_user']) . group($post['id_user']) . ' <a href="/info.php?id='.$post['id_user'].'">' . $post['nick'] . '</a> ' . medal($post['id_user']) . ' ' . online($post['id_user']) . "\n";
    
            if ($post['read'] == 1) {
                // Список непрочитанных постов
                $guest_list[] = $post['id'];
?>
    &nbsp;<span class="time" style="color:red"><?php echo vremja($post['time']);?></span><br />
<?php
            } else {
?>
    &nbsp;<span><?php echo vremja($post['time']);?></span><br />
<?php
            }
?>
    <a href="/mail.php?id=<?php echo $post['id_user'];?>"><img src="/style/icons/pochta.gif" alt="" /> Сообщение</a>
</div>
<?php

        }
        // Помечаем пост прочитанным
        if (!empty($guest_list)) {
            $db->query(
        "UPDATE `my_guests` SET `read`=?string WHERE `id` IN(?li)",
            [0, $guest_list]);
        }

        if ($k_page>1) {
            str('?', $k_page, $page);
        }
    }
?>
<div class="foot">
    <img src="/style/icons/delete.gif" alt="*"> <a href="?truncate">Очистить список гостей</a>
</div>
<div class="foot">
    <img src="/style/icons/str2.gif" alt="*"> <a href="/info.php?id=<?php echo $user['id'];?>"><?php echo $user['nick'];?></a> | <strong>Гости</strong>
</div>
<!-- ./ end guests -->
<?php

include_once H . 'sys/inc/tfoot.php';
