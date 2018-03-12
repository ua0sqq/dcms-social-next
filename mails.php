<?php
include_once 'sys/inc/start.php';
include_once 'sys/inc/compress.php';
include_once 'sys/inc/home.php';
include_once 'sys/inc/sess.php';
include_once 'sys/inc/settings.php';
include_once 'sys/inc/db_connect.php';
include_once 'sys/inc/ipua.php';
include_once 'sys/inc/fnc.php';
include_once 'sys/inc/user.php';

only_reg();

$set['title']='Написать сообщение';
include_once 'sys/inc/thead.php';
title();
aut();

if (isset($_GET['send']) && isset($_POST['send'])) {
    if (!$db->query("SELECT COUNT(`id`)FROM `user` WHERE `nick`='".my_esc($_POST['komu'])."' LIMIT 1")->el()) {
        /* Проверка наличия пол-ля с таким ником */ ?><div class="nav2">Пользователя с ником <?=text($_POST['komu']); ?> на сайте нет. Возможно, вы допустили ошибку.</div>
<div class="foot"> <a href="/mails.php">Назад</a></div><?php
include_once 'sys/inc/tfoot.php';
        exit;
    } elseif ((strlen2($_POST['msg'])<3) || (strlen2($_POST['msg'])>1024)) {
        /* Проверка кол-ва симоволов */ ?><div class="nav2">Допустимое количество символов в сообщении от 2-ух до 1024. Вы ввели: <?=strlen2($_POST['msg']); ?></div>
<div class="foot"><a href="/mails.php">Назад</a></div><?php
include_once 'sys/inc/tfoot.php';
    } else {
        $ank=$db->query("SELECT `id` FROM `user` WHERE `nick`='".my_esc($_POST['komu'])."' LIMIT 1")->row();
        /* Если выше всё норм, то проверяем на приватнось почты */
        $block = true;
        $uSet = $db->query("SELECT `privat_mail` FROM `user_set` WHERE `id_user` = '$ank[id]'  LIMIT 1")->row();
        $frend=$db->query("SELECT COUNT(*) FROM `frends` WHERE (`user` = '$user[id]' AND `frend` = '$ank[id]') OR (`user` = '$ank[id]' AND `frend` = '$user[id]') LIMIT 1")->el();
        $frend_new=$db->query("SELECT COUNT(*) FROM `frends_new` WHERE (`user` = '$user[id]' AND `to` = '$ank[id]') OR (`user` = '$ank[id]' AND `to` = '$user[id]') LIMIT 1")->el();
        if ($user['group_access'] == 0) {
            // Если только для друзей
            if ($uSet['privat_mail'] == 2 && $frend != 2) {
                ?><div class="mess">Писать сообщения пользователю, могут только его друзья!</div>
	<div class="nav1"><?php
    if ($frend_new == 0 && $frend==0) {
        ?><img src="/style/icons/druzya.png" alt="*"/> <a href="/user/frends/create.php?add=<?=$ank['id']; ?>">Добавить в друзья</a><br /><?php
    } elseif ($frend_new == 1) {
        ?><img src="/style/icons/druzya.png" alt="*"/> <a href="/user/frends/create.php?otm=<?=$ank['id']; ?>">Отклонить заявку</a><br /><?php
    } elseif ($frend == 2) {
        ?><img src="/style/icons/druzya.png" alt="*"/> <a href="/user/frends/create.php?del=<?=$ank['id']; ?>">Удалить из друзей</a><br /><?php
    } ?></div><?php
        $block = false;
            }// Если закрыта
            elseif ($uSet['privat_mail'] == 0) {
                ?><div class="mess">Пользователь запретил писать ему сообщения!</div><?php
        $block = false;
            }
        }
        if ($block==true and $ank['id']!=0) {
            /* если вообще всё норм, то отправляем */
            $db->query("INSERT INTO `mail`(`id_user`,`id_kont`,`time`,`msg`) values('$user[id]','$ank[id]','$time','".my_esc($_POST['msg'])."')");
            header("Location: /mail.php?id=$ank[id]");
            $_SESSION['message']='Сообщение успешно отправлено';
        }
    }
}
/* Поле воода сообщения */
?><form class="nav2" action="/mails.php?send" method="post">Кому (ник):<br/><input type="text" name="komu"><br/><?=$tPanel;?><textarea name="msg"></textarea>
<br/><input type="submit" value="Отправить" name="send"></form><?php

include_once 'sys/inc/tfoot.php';

?>