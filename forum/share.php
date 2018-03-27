<?php
/*
=======================================
Модуль "Поделиться темой форума" от PluginS
=======================================
*/
include_once '../sys/inc/start.php';
include_once '../sys/inc/compress.php';
include_once '../sys/inc/sess.php';
include_once '../sys/inc/home.php';
include_once '../sys/inc/settings.php';
include_once '../sys/inc/db_connect.php';
include_once '../sys/inc/ipua.php';
include_once '../sys/inc/fnc.php';
include_once '../sys/inc/user.php';

$set['title']='Share Them';
include_once '../sys/inc/thead.php';
title();
aut();

$them_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$not = $db->query("SELECT thm.*, u.id AS id_user, u.nick FROM `forum_t` thm JOIN `user` u ON u.id=thm.id_user WHERE thm.`id`=?i", [$them_id])->assoc();
if (!count($not)) {
    echo "<div class='error'>Такой темы не существует</div>";
    include_once '../sys/inc/tfoot.php';
    exit;
}
if ($db->query("SELECT COUNT(`id`)FROM `notes` WHERE `id_user`=?i AND `share_id`=?i AND `share_type`=?",
               [$user['id'], $them_id, 'forum'])->el()) {
    echo "<div class='error'>Вы уже поделились данной темой</div>";
    include_once '../sys/inc/tfoot.php';
    exit;
} else {
    foreach ($not as $notes);

    if (isset($_POST['ok'])) {
        $id = $db->query("INSERT INTO `notes`(`id_user`,`name`,`msg`,`share`,`share_text`,`share_id`,`share_id_user`,`share_name`,`time`,`share_type`) VALUES(?i, ?, ?, ?, ?, ?i, ?i, ?, ?i, ?)",
                         [$user['id'], $notes['name'], $_POST['share_text'], '1', $notes['text'], $notes['id'], $notes['id_user'], $notes['name'], $time,'forum'])->id();
        msg('Ок всё крч');
        header('Location:/plugins/notes/list.php?id='.$id);
        exit;
    } ?>
<div class='nav2'><div class="friends_access_list attach_block mt_0 grey"> <?php echo group($notes['id_user'])." "; ?> <a href="/info.php?id=<?=$notes['id_user']?>"><span style="color:#79358c"><b><?php echo " ".$notes['nick']." "; ?> </b></span></a> : <?php echo '<a href="/forum/'.$notes['id_forum'].'/'.$notes['id_razdel'].'/'.$notes['id'].'/">'; ?>
<span style="color:#06F;"><?php echo $notes['name']; ?></span></a></div>
<?php
echo "<form method='post' action='share.php?id=".intval($_GET['id'])."'>";
    echo $tPanel;
    echo "<textarea name='share_text'></textarea>";
    echo "<br/><input type='submit' name='ok' value='Поделиться'>";
    echo "</form></div>";
}
include_once '../sys/inc/tfoot.php';
?>