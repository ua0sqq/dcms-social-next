
<!-- ./plugin notes -->
<div class="foot">
        <a href="/plugins/notes/"><strong>Дневники</strong> <?php
        $cnt = $db->query('SELECT * FROM (
SELECT COUNT( * ) all_notes FROM `notes`)q, (
SELECT COUNT( * ) new_notes FROM `notes` WHERE `time`>?i)q2', [$time-86000])->row();
        echo $cnt['all_notes'] . ($cnt['new_notes'] ? '+' . $cnt['new_notes'] : '');?></a>
</div>
<?php

$q=$db->query("SELECT n.*, (
SELECT COUNT( * ) FROM `notes_komm` WHERE `id_notes`=n.id) cnt
FROM `notes` n ORDER BY n.`time` DESC LIMIT 3")->assoc();
if (count($q)) {
        foreach ($q as $post) {
?>
<div class="nav2">
        <p><?php echo group($post['id_user']); ?> <?php echo user::nick($post['id_user'], 1, 1, 1); ?> : <?php
echo '<a href="/plugins/notes/list.php?id='.$post['id'].'"><span class="on">'.text($post['name']).'</span></a>'; ?></p>
        <p><?php echo rez_text($post['msg'], 80); ?></p><?php
echo($post['share']==1 ? "<p>(!!) <i>Репостнутая запись</i></p>" : null); ?><img src="/style/icons/comm_num_gray.png"><?php echo $post['cnt']; ?>
        <span class="text-right"><small><?php echo vremja($post['time']); ?></small></span>
</div>
<?php
        }
    }
    if (isset($user)) {
?>
<div class="nav1">
        <a href="/plugins/notes/add.php">Написать</a>
</div>
<?php
} ?>
<!-- ./end plugin notes -->
