<?php
// аголовок обсуждения
if ($type == 'them' && $post['avtor'] != $user['id']) {
    $name = __('Форум | Тема форума');
} elseif ($type == 'them' && $post['avtor'] == $user['id']) {
    $name = __('Форум | Ваша тема');
}
 
// Выводим на экран
if ($type == 'them') {
    $them = $db->query(
                        'SELECT * FROM `forum_t` WHERE `id`=?i',
                                [$post['id_sim']])->row();
    
    if ($them['id']) {
        ?>
		<div class="nav1">
		<img src="/style/icons/forum.png" alt="*"/> <a href="/forum/<?= $them['id_forum']?>/<?= $them['id_razdel']?>/<?= $them['id']?>/?page=<?= $pageEnd?>"><?= $name?></a> 
		<?php
        if ($post['count'] > 0) {
            ?><strong><span class='off'>+<?= $post['count']?></span></strong><?php
        } ?>
		<?= $s1 . vremja($post['time']) . $s2?>
		</div>
		
		<div class="nav2">
		<strong><span class='on'><?= $avtor['nick']?></span></strong> 
		<?= ($avtor['id'] != $user['id'] ? '<a href="user.settings.php?id=' . $avtor['id'] . '">[!]</a>' : '')?> 
		<?= $avtor['medal']?> <?= $avtor['online']?> &raquo; <strong><?= text($them['name'])?></strong><br />
		<?= output_text($them['text'])?>
		</div>
		<?php
    } else {
        ?>
		<div class="mess">
		<?= __('Тема форума уже удалена =(')?> <?= $s1 . vremja($post['time']) . $s2?>
		</div>
		<?php
    }
}
?>