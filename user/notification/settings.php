<?php
/*
=======================================
Уведомления для Dcms-Social
Автор: Искатель
---------------------------------------
Этот скрипт распостроняется по лицензии
движка Dcms-Social.
При использовании указывать ссылку на
оф. сайт http://dcms-social.ru
---------------------------------------
Контакты
ICQ: 587863132
http://dcms-social.ru
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
$set['title']='Настройка уведомлений';
include_once H . 'sys/inc/thead.php';
title();
$notSet = $db->query(
    "SELECT * FROM `notification_set` WHERE `id_user`=?i LIMIT ?i",
                     [$user['id'], 1])->row();
if (isset($_POST['save'])) {
    // Комментарии
    if (isset($_POST['komm']) && ($_POST['komm']==0 || $_POST['komm']==1)) {
        $db->query(
    "UPDATE `notification_set` SET `komm`=?i WHERE `id_user`=?i",
           [$_POST['komm'], $user['id']]);
    }
    $_SESSION['message'] = 'Изменения успешно приняты';
    header('Location: settings.php');
    exit;
}
err();
aut();
?>
<div id="comments" class="menus">
    <div class="webmenu">
        <a href="/user/info/settings.php">Общие</a>
    </div>
    <div class="webmenu last">
        <a href="/user/tape/settings.php">Лента</a>
    </div>
    <div class="webmenu last">
        <a href="/user/discussions/settings.php">Обсуждения</a>
    </div>
    <div class="webmenu last">
        <a href="/user/notification/settings.php" class="activ">Уведомления</a>
    </div>
    <div class="webmenu last">
        <a href="/user/info/settings.privacy.php" >Приватность</a>
    </div>
    <div class="webmenu last">
        <a href="/user/info/secure.php" >Пароль</a>
    </div>
</div>
<!--  // Лента фото -->
<form action="?" method="post">
    <div class="mess">
        Уведомления о ответах в комментариях
    </div>
    <div class="nav1">
        <input name="komm" type="radio" <?php echo ($notSet['komm']==1?' checked="checked"':null);?> value="1" /> Да
        <input name="komm" type="radio" <?php echo ($notSet['komm']==0?' checked="checked"':null);?> value="0" /> Нет
    </div>
    <div class="main">
        <input type="submit" name="save" value="Сохранить" />
    </div>
</form>
<div class="foot">
    <img src="/style/icons/str2.gif" alt="ico" /> <a href="index.php">Уведомления</a> | <b>Настройки</b><br />
</div>
<?php
    
include_once H . 'sys/inc/tfoot.php';
