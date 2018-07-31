<?php
// Заголовок обсуждения
if ($type == 'notes' && $post['avtor'] != $user['id']) {
    $name = __('Дневник друга');
} elseif ($type == 'notes' && $post['avtor'] == $user['id']) {
    $name = __('Ваш дневник');
}
 
// Выводим на экран
if ($type == 'notes') {
    $notes = $db->query(
                        'SELECT `id`, `name`, left(`msg`, 300) as msg FROM `notes` WHERE `id`=?i',
                                [$post['id_sim']])->row();
    
    if ($notes['id']) {
        ?>
		<div class="nav1">
		<img src="/style/icons/dnev.png" alt="*"/> <a href="/plugins/notes/list.php?id=<?= $notes['id']?>&amp;page=<?= $pageEnd?>"><?= $name?></a> 
		<?php
        if ($post['count'] > 0) {
            ?><strong><span class='off'>+<?= $post['count']?></span></strong><?php
        } ?>
		<?= $s1 . vremja($post['time']) . $s2?>
		</div>
		
		<div class="nav2">
		<p><strong><span class='on'><?= $avtor['nick']?></span></strong> 
		<?= ($avtor['id'] != $user['id'] ? '<a href="user.settings.php?id=' . $avtor['id'] . '">[!]</a>' : '')?> 
		<?= $avtor['medal']?> <?= $avtor['online']?> &raquo; <strong><?= text($notes['name'])?></strong></p>
		<p><?= crop_text($notes['msg'], 200)?></p>
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