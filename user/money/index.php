<?php
include_once '../../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/adm_check.php';
include_once H . 'sys/inc/user.php';

if (!isset($user)) {
    header("location: /index.php?");
}

$set['title']='Дополнительные услуги';
include_once H . 'sys/inc/thead.php';
title();
err();
aut();
?>
<div class="foot">
    <p><img src="/style/icons/str2.gif" alt="*"> <a href="/info.php"><?php echo $user['nick'];?></a> | Доп. услуги</p>
</div>
<div class="nav1">
    <p><b>Личный счет:</b>
    <p> - <b><span class="off"><?php echo $user['balls'];?></span></b> баллов.
    <p> - <b><span class="on"><?php echo $user['money'];?></span></b> <?php echo $sMonet[0];?></p>
</div>
<div class="nav2">
    <span class="off">&rarr; <a href="money.php">Получить <?php echo $sMonet[2];?></a></span>
</div>
<div class="foot">
    <b><span style="color:blue;">Услуги за</span> <?php echo $sMonet[2];?></b>
</div>
<div class="nav1">
    <?php
$c = $db->query("SELECT (
SELECT COUNT( * ) FROM `liders` WHERE `id_user`=?i AND `time`>?i) c, (
SELECT COUNT( * ) FROM `user_set` WHERE `id_user`=?i AND `ocenka`>?i) cc",
        [$user['id'], time(), $user['id'], time()])->row();
?>
    &rarr; <a href="liders.php">Лидер сайта</a> <?php echo ($c['c'] == 0 ? '<span class="off">[отключена]</span> ' : '<span class="on">[включена]</span>');?>
</div>
<div class="nav2">
    &rarr; <a href="plus5.php">Оценка</a> <img src="/style/icons/6.png" alt="*"> <?php echo ($c['cc']==0?'<span class="off">[отключена]</span> ':'<span class="on">[включена]</span>')."";?>
</div>
<div class="foot">
    <img src="/style/icons/str2.gif" alt="*"> <a href="/info.php"><?php echo $user['nick'];?></a> | Доп. услуги
</div>
<?php

include_once H . 'sys/inc/tfoot.php';
