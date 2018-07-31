<?php
include_once '../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/adm_check.php';
include_once H . 'sys/inc/user.php';

user_access('adm_forum_sinc', null, 'index.php?'.SID);
adm_check();

$set['title']='Синхронизация таблиц форума';
include_once H . 'sys/inc/thead.php';
title();
err();
aut();

if (isset($_GET['ok']) && isset($_POST['accept'])) {
    $d_r = 0;
    $d_t = 0;
    $d_p = 0;
    // удаление разделов
    $list_id_razdel = $db->query("SELECT `id` FROM `forum_r` WHERE `id_forum` NOT IN(SELECT `id` FROM `forum_f`)")->col();
    if (!empty($list_id_razdel)) {
        $d_r = $db->query("DELETE FROM `forum_r` WHERE `id` IN(?li)",
                   [$list_id_razdel])->ar();
    }
    // удаление тем
    $list_id_them = $db->query("SELECT `id` FROM `forum_t`
WHERE `id_razdel` NOT IN(SELECT `id` FROM `forum_r`) OR `id_user` NOT IN (SELECT `id` FROM `user`)")->col();
    if (!empty($list_id_them)) {
        $d_t = $db->query("DELETE FROM `forum_t` WHERE `id` IN(?li)",
                   [$list_id_them])->ar();
    }
    // удаление постов
    $list_id_post = $db->query("SELECT `id` FROM `forum_p`
WHERE `id_them` NOT IN(SELECT `id` FROM `forum_t`) OR `id_user` NOT IN(SELECT `id` FROM `user`)")->col();
    if (!empty($list_id_post)) {
        $d_p = $db->query("DELETE FROM `forum_p` WHERE `id` IN(?li)",
                          [$list_id_post])->ar();
    }
    msg("Удалено разделов: $d_r, тем: $d_t, постов: $d_p");
}
?>
<form class="foot" method="post" action="?ok">
    <p><input value="Начать" name="accept" type="submit" /></p>
</form>
<div class="mess">
    * В зависимости от количества сообщений и тем, данное действие может занять длительное время.
</div>
<div class="mess">
    ** Рекомендуется использовать только в случах расхождений счетчиков форума с реальными данными
</div>
<?php
if (user_access('adm_panel_show')) {
?>
<div class="foot">
    &laquo;<a href="/adm_panel/">В админку</a>
</div>
<?php
}
include_once H . 'sys/inc/tfoot.php';
