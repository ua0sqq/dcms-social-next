<?php
$set['p_str']=5;
if ($likepost = filter_input(INPUT_GET, 'likepost', FILTER_VALIDATE_INT)) {
    $stena=$db->query(
        "SELECT `id`, `id_user` FROM `stena` WHERE `id`=?i",
                      [$likepost])->row();
    if ($stena && !$db->query(
        "SELECT COUNT( * ) FROM `stena_like` WHERE `id_stena`=?i AND `id_user`=?i",
                                                [$stena['id'], $user['id']])->el()) {
        $db->query(
            "INSERT INTO `stena_like` (`id_user`, `id_stena`) VALUES(?i, ?i)",
                   [$user['id'], $stena['id']]);
        $db->query(
            "UPDATE `user` SET `balls`=`balls`+1 WHERE `id`=?i",
                   [$stena['id']]);
    }
}

$k_post=$db->query(
    "SELECT COUNT( * ) FROM `stena` WHERE `id_stena`=?i",
                   [$ank['id']])->el();

if (!$k_post) {
    ?>
<div class="mess">
    Нет сообщений
</div><?php
} else {
        $k_page=k_page($k_post, $set['p_str']);
        $page=page($k_page);
        $start=$set['p_str']*$page-$set['p_str'];
        // сортировка по времени
        if (isset($user)) {
            ?>
<!-- ./stena -->
<div id="comments" class="menus">
    <a id="bottom"></a>
    <div class="webmenu">
        <a href="/info.php?id=<?php echo $ank['id'] . '&amp;page=' . $page . '&amp;sort=1#bottom" class="' . ($user['sort']==1 ? 'activ' : ''); ?>">Внизу</a>
    </div>
    <div class="webmenu">
        <a href="/info.php?id=<?php echo $ank['id'] . '&amp;page=' . $page . '&amp;sort=0#bottom" class="' . ($user['sort']==0 ? 'activ' : ''); ?>">Вверху</a>
    </div>
</div>
<?php
        }
        $sql ='';
        $stena_data = [$sql, $ank['id'], $sort, $start, $set['p_str']];
        if (isset($user)) {
            $sql = ', (
SELECT COUNT( * ) FROM `stena_like` WHERE `id_stena`=st.id AND `id_user`=' . $user['id'] . ') AS `user_like`';
            $stena_data = [$sql, $ank['id'], $sort, $start, $set['p_str']];
        }
        $q=$db->query(
    "SELECT `st`.`id`, `st`.`id_user`, `st`.`msg`, `st`.`time`, `u`.`nick`, (
SELECT COUNT( * ) FROM `stena_like` WHERE `id_stena`=st.id) AS `like`, (
SELECT COUNT( * ) FROM `stena_komm` WHERE `id_stena`=st.id) AS `komm`?q
FROM `stena` st
JOIN `user` u ON `u`.`id`=`st`.`id_user`
WHERE `id_stena`=?i ORDER BY st.`id` ?q LIMIT ?i, ?i",
            $stena_data);
        $num=0;
        while ($post = $q->row()) {
            if ($num==0) {
                ?>
<div class="nav1">
    <p><?php
        $num=1;
            } elseif ($num==1) {
                ?>
<div class="nav2">
    <p><?php
        $num=0;
            }

            if ($set['set_show_icon'] == 2) {
                echo avatar($post['id_user']);
            } elseif ($set['set_show_icon'] == 1) {
                echo group($post['id_user']);
            } ?>
    <a href="/info.php?id=<?php echo $post['id_user']; ?>"><?php echo $post['nick']; ?></a><?php
    echo medal($post['id_user']) . ' ' . online($post['id_user']);
            if (isset($user) && $post['id_user'] != $user['id']) {
                ?>
    <a href="/info.php?id=<?php echo $ank['id'] . '&amp;response=' . $post['id_user']; ?>">[*]</a><?php
            }
            echo ' (' . vremja($post['time']) . ')</p>';
            echo '<p>' . stena($post['id_user'], $post['id']) . '</p>';
            echo '<p style="padding:5px;">' . output_text($post['msg']) . '</p>'."\n";
            //if (isset($user)) {
                ?>
    <p><a href="/user/komm.php?id=<?php echo $post['id']; ?>"><img src="/style/icons/uv.png"> (<?php echo $post['komm']; ?>) </a>
    <span style="float:right;"><?php

            if (isset($user) && isset($post['user_like']) && $post['user_like'] == 0 && $post['id_user'] != $user['id']) {
                ?><a href="/id<?php echo $ank['id'] . '?likepost=' . $post['id'] . '&amp;page=' . $page;?>">&hearts; <?php echo $post['like']; ?></a><?php
            } else {
                ?><span class="on">&hearts; <?php echo $post['like'];?></span><?php
            }
            if (isset($user) && $post['id_user'] != $user['id']) {
                ?><a href="/info.php?id=<?php echo $ank['id'] . '&amp;page=' . $page . '&amp;spam=' . $post['id']; ?>"><img src="/style/icons/blicon.gif" alt="*" title="Это спам"></a><?php
            }
                if (user_access('guest_delete') || $ank['id']==$user['id']) {
                    ?><a href="?id=<?php echo $ank['id'] . '&amp;delete_post=' . $post['id']; ?>"><img src="/style/icons/delete.gif" alt="удалить" /></a><?php
                } ?></span></p>
    <span style="clear:both;"></span>
</div><?php
        }

        if ($k_page>1) {
            str('?id='.$ank['id'].'&amp;', $k_page, $page);
        }
    }

if (isset($user) || (isset($set['write_guest']) && $set['write_guest']==1 && (!isset($_SESSION['antiflood']) || $_SESSION['antiflood']<$time-300))) {
?>
<form method="post" name="message" action="?id=<?php echo $ank['id'] . $go_link;?>"><?php
    if ($set['web'] && is_file(H . 'style/themes/' . $set['set_them'] . '/altername_post_form.php')) {
        include_once H . 'style/themes/' . $set['set_them'] . '/altername_post_form.php';
    } else {
        echo $tPanel. '<textarea name="msg">' . $insert . '</textarea><br />'."\n";
    }
?>
    <input value="Отправить" type="submit" />
</form>
<!-- ./end stena --><?php
    if ($set['web']) {
        echo '<!-- table style="width:99%"> // TODO: ??? что за хрень? -->';
    } 
}
