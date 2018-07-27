<?php
// Заголовок обсуждения
if ($type == 'obmen' && $post['avtor'] != $user['id']) { // обмен
    $name = __('Файлы | Файл друга');
} elseif ($type == 'obmen' && $post['avtor'] == $user['id']) {
    $name = __('Файлы | Ваш файл');
}
 
// Выводим на экран
if ($type == 'obmen') {
    $file = $db->query(
					'SELECT * FROM `obmennik_files` WHERE `id`=?i',
							[$post['id_sim']])->row();
    
    if ($file['id']) {
        ?>
		<div class="nav1">
		<img src="/style/icons/disk.png" alt="*"/> 
		<a href="/user/personalfiles/<?= $file['id_user']?>/<?= $file['my_dir']?>/?id_file=<?= $file['id']?>&amp;page=<?= $pageEnd?>"><?= $name?></a> 
		<?php
        if ($post['count'] > 0) {
            ?><strong><span class='off'>+<?= $post['count']?></span></strong><?php
        } ?>
		<?= $s1 . vremja($post['time']) . $s2?>
		</div>		
		<div class="nav2">
		<strong><span class='on'><?= $avtor['nick']?></span></strong> 
		<?= ($avtor['id'] != $user['id'] ? '<a href="user.settings.php?id=' . $avtor['id'] . '">[!]</a>' : '')?> 
		<?= $avtor['medal']?> <?= $avtor['online']?> &raquo; <strong><?= text($file['name'])?></strong><br />
		<?= output_text($file['opis'])?>
		</div>
		<?php
    } else {
        ?>
		<div class="mess">
			<?= __('Файл уже удален =(')?> <?= $s1 . vremja($post['time']) . $s2?>
		</div>
		<?php
    }
}
?>