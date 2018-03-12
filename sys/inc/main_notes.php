<div style="padding: 6px 10px;" class="foot"><a href="/plugins/notes/">
 <b>Дневники</b> <?php echo $db->query("SELECT COUNT(`id`)FROM `notes`")->el();?>+<?php
 echo $db->query("SELECT COUNT(`id`)FROM `notes` WHERE `time`>'".($time-86000)."'")->el();?></span></a></div><?php
$q=$db->query("SELECT * FROM `notes` ORDER BY `time` DESC LIMIT 3")->assoc();
if (!count($q)) {
    ?><div class="nav2" style="color:#666;">Записей нет</div><?php
} else {
        foreach ($q as $post) {
            $note_name = '<a href="/plugins/notes/list.php?id='.$post['id'].'"><span style="color:#06f">'.text($post['name']).'</span></a>';
            $note_text = $post['msg'];
            $count_comm =$db->query("SELECT COUNT(`id`) FROM `notes_komm` WHERE `id_notes`='".$post['id']."'")->el();
            echo "<div style='border-bottom:1px #d5dde5 solid;' class='nav2'>\n"; ?><?php echo group($post['id_user']); ?> 
<?php echo user::nick($post['id_user'], 1, 1, 1); ?> : <?php echo $note_name; ?>
<br/>
<?php echo rez_text($note_text, 80); ?><br/><?php
echo($post['share']==1 ? "(!!) <i>Репостнутая запись</i><br/>" : null); ?><img src="/style/icons/comm_num_gray.png"><?php echo $count_comm; ?>
<span style="float:right;color:#666;"><small>
<?php echo vremja($post['time']); ?></small></div><?php
        }
    }
?><div class='nav1'> 
<?php if (isset($user)) {
    ?><a href="/plugins/notes/add.php">Написать</a><?php
} ?><span style="float:right;"><a href="/plugins/notes/">
Все записи &rarr;</a></span><br/>
</div>