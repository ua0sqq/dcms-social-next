<?php
// Заголовок обсуждения
if ($type == 'foto' && $post['avtor'] != $user['id']) {
    $name = __('Фотография друга');
} elseif ($type == 'foto' && $post['avtor'] == $user['id']) {
    $name = __('Ваша фотография');
}
 
// Выводим на экран
if ($type == 'foto') {
    $foto = $db->query(
        "SELECT * FROM `gallery_foto` WHERE `id`=?i",
                       [$post['id_sim']])->row();
    
    if ($foto['id']) {
        ?>
		<div class="nav1">
		<img src="/style/icons/camera.png" alt="*"/> <a href="/foto/<?= $avtor['id']?>/<?= $foto['id_gallery']?>/<?= $foto['id']?>/?page=<?= $pageEnd?>"><?= $name?></a> 
		<?php
        if ($post['count'] > 0) {
            ?><strong><span class='off'>+<?= $post['count']?></span></strong><?php
        } ?>
		<?= $s1 . vremja($post['time']) . $s2?>
		</div>
		
		<div class="nav2">
		<p><strong><span class='on'><?= $avtor['nick']?></span></strong> 
		<?= ($avtor['id'] != $user['id'] ? '<a href="user.settings.php?id=' . $avtor['id'] . '">[!]</a>' : '')?> 
		<?= $avtor['medal']?> <?= $avtor['online']?> &raquo; <strong><?= text($foto['name'])?></strong></p>
		<p><img src="/foto/foto50/<?= $foto['id']?>.<?= $foto['ras']?>" alt="Image" /></p>
		</div>
		<?php
    } else {
        ?>
		<div class="mess">
		<?= __('Фотография уже удалена =(')?> <?= $s1 . vremja($post['time']) . $s2?>
		</div>
		<?php
    }
}
?>