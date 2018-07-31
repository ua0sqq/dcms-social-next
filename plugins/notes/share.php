<?php
/*
=======================================
Модуль "Поделиться дневником" от PluginS
=======================================
*/
include_once '../../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

only_reg();

$set['title']='Поделиться';
include_once H . 'sys/inc/thead.php';
title();
aut();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$not=$db->query("SELECT n.*, u.id AS id_user FROM `notes` n JOIN `user` u ON u.id=n.id_user WHERE n.`id`=?i",[$id])->assoc();
if (!count($not)) {
    echo "<div class='mess'>Такой записи не существует</div>";
} elseif ($db->query("SELECT COUNT( * ) FROM `notes` WHERE `id_user`=?i AND `share_id`=?i AND `share_type`=?",
                     [$user['id'], $id, 'notes'])->el()) {
    echo "<div class='error'>Вы уже поделились данной записью</div>";
} else {
    foreach ($not as $notes);
    if ($notes['id_user'] != $user['id']) {
        $avtor=get_user($notes['id_user']);
        if (isset($_POST['ok'])) {
            $id = $db->query(
                            'INSERT INTO `notes`(`id_user`,`name`,`msg`,`share`,`share_text`,`share_id`,`share_id_user`,`share_name`,`time`)
VALUES(?i, ?, ?, ?, ?, ?i, ?i, ?, ?i)',
                                    [$user['id'], $notes['name'], $_POST['share_text'], '1', $notes['msg'], $notes['id'], $notes['id_user'], $notes['name'], $time])->id();

            msg('Ок всё крч');
            header('Location: /plugins/notes/list.php?id='.$id);
            exit;
        } ?>
<div class='nav2'>
    <div class="friends_access_list attach_block mt_0 grey">
        <?php echo group($notes['id_user'])." "; ?> <a href="/info.php?id=<?=$notes['id_user']?>"><span style="color:#79358c"><b><?php echo " ".user::nick($notes['id_user'])." "; ?> </b></span></a> : <a href="/plugins/notes/list.php?id=<?=$notes['id']?>">
        <span style="color:#06F;"><?php echo $notes['name']; ?></span></a>
    </div>
<?php
echo "<form method='post' action='share.php?id=".intval($_GET['id'])."'>";
        echo $tPanel;
        echo "<p><textarea name='share_text'></textarea>";
        echo "<p><input type='submit' name='ok' value='Поделиться'></p>";
        echo "</form></div>";
    } else {
        echo "<div class='mess'>Нельзя репостить свои записи</div>";
    }
}
include_once H . 'sys/inc/tfoot.php';
?>