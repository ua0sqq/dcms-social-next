<div class="foot">
        <a href="/plugins/notes/"><b>Дневники</b> <?php
        $cnt = $db->query('SELECT * FROM (
                          SELECT COUNT(*) all_notes FROM `notes`)q, (
                          SELECT COUNT(*) new_notes FROM `notes` WHERE `time`>?i)q2', [$time-86000])->row();
        echo $cnt['all_notes'] . ($cnt['new_notes'] ? '+' . $cnt['new_notes'] : '');?></a>
</div><?php

$q=$db->query("SELECT n.*, (
              SELECT COUNT(*) FROM `notes_komm` WHERE `id_notes`=n.id) cnt
              FROM `notes` n ORDER BY n.`time` DESC LIMIT 3")->assoc();
if (!count($q)) {
    ?><div class="err">Записей нет</div><?php
} else {
        foreach ($q as $post) {
            echo '<div class="nav2">' . "\n"; ?><?php echo group($post['id_user']); ?> 
<?php echo user::nick($post['id_user'], 1, 1, 1); ?> : <?php
echo '<a href="/plugins/notes/list.php?id='.$post['id'].'"><span class="on">'.text($post['name']).'</span></a>'; ?>
<br />
<?php echo rez_text($post['msg'], 80); ?><br /><?php
echo($post['share']==1 ? "(!!) <i>Репостнутая запись</i><br/>" : null); ?><img src="/style/icons/comm_num_gray.png"><?php echo $post['cnt']; ?>
<span class="text-right"><small><?php echo vremja($post['time']); ?></small></span>
</div><?php
        }
    }
?><div class="nav1"> 
<?php
if (isset($user)) {
    ?><a href="/plugins/notes/add.php">Написать</a><?php
} ?><span class="text-right"><a href="/plugins/notes/">
Все записи &rarr;</a></span><br />
</div>