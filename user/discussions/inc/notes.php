<?php
/*
* Заголовок обсуждения
*/
if ($type == 'notes' && $post['avtor'] != $user['id']) {
    $name = __('Дневник друга');
} elseif ($type == 'notes' && $post['avtor'] == $user['id']) {
    $name = __('Ваш дневник');
}
 
/*
* Выводим на экран
*/
if ($type == 'notes') {
    $notes = $db->query(
                        'SELECT * FROM `notes` WHERE `id`=?i',
                                [$post['id_sim']])->row();
    
    if ($notes['id']) {
        ?>
		<div class="nav1">
		<img src="/style/icons/dnev.png" alt="*"/> <a href="/plugins/notes/list.php?id=<?= $notes['id']?>&amp;page=<?= $pageEnd?>"><?= $name?></a> 
		<?php
        if ($post['count'] > 0) {
            ?><b><font color='red'>+<?= $post['count']?></font></b><?php
        } ?>
		<span class="time"><?= $s1 . vremja($post['time']) . $s2?></span>
		</div>
		
		<div class="nav2">
		<b><font color='green'><?= $avtor['nick']?></font></b> 
		<?= ($avtor['id'] != $user['id'] ? '<a href="user.settings.php?id=' . $avtor['id'] . '">[!]</a>' : '')?> 
		<?= $avtor['medal']?> <?= $avtor['online']?> &raquo; <b><?= text($notes['name'])?></b><br />
		<span class="text"><?= output_text($notes['msg'])?></span>
		</div>
		<?php
    } else {
        ?>
		<div class="mess">
		<?= __('Тема форума уже удалена =(')?>
		<span class="time"><?= $s1 . vremja($post['time']) . $s2?></span>
		</div>
		<?php
    }
}
?>