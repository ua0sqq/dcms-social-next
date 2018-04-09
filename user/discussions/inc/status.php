<?php
/*
* Заголовок обсуждения
*/
if ($type == 'status' && $post['avtor'] != $user['id']) {
    $name = __('Статус друга');
} elseif ($type == 'status' && $post['avtor'] == $user['id']) {
    $name = __('Ваш статус');
}
 
/*
* Выводим на экран
*/
if ($type == 'status') {
    $status = $db->query(
                         'SELECT st.*, (
SELECT COUNT( * ) FROM `status_komm` WHERE `id_status`=st.id) cnt_komm, (
SELECT COUNT( * ) FROM `status_like` WHERE `id_status`=st.id) cnt_like, (
SELECT COUNT( * ) FROM `status_like` WHERE `id_status`=st.id AND `id_user`=?i) is_like
FROM `status` st WHERE st.`id`=?i',
                         [$user['id'], $post['id_sim']])->row();
    if ($status['id']) {
        ?>
		<div class="nav1">
		<span class="time"><?= $s1 . vremja($post['time']) . $s2?></span>
		<img src="/style/icons/comment.png" alt="*" /> <a href="/user/status/komm.php?id=<?= $status['id']?>"><?= $name?></a> 
		
		<?php
        if ($post['count'] > 0) {
            ?><b><font color='red'>+<?= $post['count']?></font></b><?php
        } ?>
		</div>
		
		<div class="nav2">
		<b><font color='green'><?= $avtor['nick']?></font></b> 
		<?= ($avtor['id'] != $user['id'] ? '<a href="user.settings.php?id=' . $avtor['id'] . '">[!]</a>' : '')?> 
		<?= $avtor['medal']?> <?= $avtor['online']?> <br />
		
		<div class="st_1"></div>
		<div class="st_2">	
		<span class="text"><?= output_text($status['msg'])?></span><br />
		</div>
		
		<a href="/user/status/komm.php?id=<?= $status['id']?>"><img src="/style/icons/bbl4.png" alt="*" /> 
		<?= $status['cnt_komm']; ?></a>
		
		<?php
        $l = $status['cnt_like'];
        
        if (isset($user) && $user['id'] != $avtor['id']) {
            if ($user['id'] != $avtor['id'] &&
            !$status['is_like']) {
                ?><a href="?likestatus=<?= $status['id']?>&amp;page=<?= $page?>"><img src="/style/icons/like.gif" alt="*" />Класс!</a> &bull; <?php
                $like = $l;
            } else {
                ?><img src="/style/icons/like.gif" alt="*" /> <?= __('Вы и')?> <?php
                $like = $l - 1;
            }
        } else {
            ?><img src="/style/icons/like.gif" alt="*" /> <?php
            $like = $l;
        } ?>
		<a href="/user/status/like.php?id=<?= $status['id']?>"><?= $like?> <?= __('чел.')?></a>
		</div>
		<?php
    } else {
        ?>
		<div class="mess">
		<?= __('Статус уже удален =(')?> 
		<span class="time"><?= $s1 . vremja($post['time']) . $s2?></span>
		</div>
		<?php
    }
}
?>