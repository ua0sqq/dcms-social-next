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

user_access('user_mass_delete', null, 'index.php?'.SID);
adm_check();

$set['title']='Удаление пользователей';
include_once H . 'sys/inc/thead.php';
title();

if (isset($_POST['write']) && isset($_POST['write2'])) {
    $timeclear1=0;
    if ($_POST['write2']=='sut') {
        $timeclear1=$time-intval($_POST['write'])*60*60*24;
    } elseif ($_POST['write2']=='mes') {
        $timeclear1=$time-intval($_POST['write'])*60*60*24*30;
    } else {
        $err[]='Не выбран период';
    }
    $list_id_user = $db->query(
        "SELECT `id` FROM `user` WHERE `date_last`<?i",
                    [$timeclear1])->col();
    $del_th=0;
    if (count($list_id_user)) {
        foreach ($list_id_user as $post_id_user) {
            
            $ank['id'] = $post_id_user;
                
            $opdirbase=@opendir(H.'sys/add/delete_user_act');
            while ($filebase=@readdir($opdirbase)) {
                if (preg_match('#\.php$#', $filebase)) {
                    include_once(H.'sys/add/delete_user_act/'.$filebase);
                }
            }
        }

        if (!empty($list_id_user)) {
            $del_th = $db->query(
                "DELETE FROM `user` WHERE `id` IN(?li)",
                        [$list_id_user])->ar();
            $db->query(
                "DELETE FROM `chat_post` WHERE `id_user` IN(?li)",
                        [$list_id_user]);
            $db->query(
                "DELETE FROM `frends` WHERE `user` IN(?li) OR `frend` IN(?li)",
                        [$list_id_user, $list_id_user]);
            $db->query(
                "DELETE FROM `frends_new` WHERE `user` IN(?li)  OR `to` IN(?li)",
                        [$list_id_user, $list_id_user]);
            $db->query(
                "DELETE FROM `stena` WHERE `id_user` IN(?li)",
                         [$list_id_user]);
            $db->query(
                "DELETE FROM `stena_like` WHERE `id_user` IN(?li)",
                         [$list_id_user]);
            $db->query(
                "DELETE FROM `status_like` WHERE `id_user` IN(?li)",
                         [$list_id_user]);
            $db->query(
                "DELETE FROM `status` WHERE `id_user` IN(?li)",
                         [$list_id_user]);
            $db->query(
                "DELETE FROM `gifts_user` WHERE `id_user` IN(?li) OR `id_ank` IN(?li)",
                         [$list_id_user, $list_id_user]);
            $q5=$db->query(
                "SELECT * FROM `forum_t` WHERE `id_user` IN(?li)",
                         [$list_id_user]);
            while ($post5 = $q5->row()) {
                $list_id_them[] = $post5['id'];
            }
            if (!empty($list_id_them)) {
                $db->query(
                    "DELETE FROM `forum_p` WHERE `id_them` IN(?li)",
                           [$list_id_them]);
            }
            $db->query(
                "DELETE FROM `forum_t` WHERE `id_user` IN(?li)",
                         [$list_id_user]);
            $db->query(
                "DELETE FROM `forum_p` WHERE `id_user` IN(?li)",
                         [$list_id_user]);
            $db->query(
                "DELETE FROM `forum_zakl` WHERE `id_user` IN(?li)",
                         [$list_id_user]);
            $db->query(
                "DELETE FROM `guest` WHERE `id_user` IN(?li)",
                         [$list_id_user]);
            $db->query(
                "DELETE FROM `news_komm` WHERE `id_user` IN(?li)",
                         [$list_id_user]);
            $db->query(
                "DELETE FROM `user_files` WHERE `id_user` IN(?li)",
                         [$list_id_user]);
            $db->query(
                "DELETE FROM `user_music` WHERE `id_user` IN(?li)",
                         [$list_id_user]);
            $db->query(
                "DELETE FROM `like_object` WHERE `id_user` IN(?li)",
                         [$list_id_user]);
            $q5=$db->query(
                "SELECT * FROM `obmennik_files` WHERE `id_user` IN(?li)",
                         [$list_id_user]);
            while ($post5 = $q5->row()) {
                unlink(H . 'sys/obmen/files/' . $post5['id'] . '.dat');
            }
            $db->query(
                "DELETE FROM `obmennik_files` WHERE `id_user` IN(?li)",
                         [$list_id_user]);
            $db->query(
                "DELETE FROM `users_konts` WHERE `id_user` IN(?li) OR `id_kont` IN(?li)",
                         [$list_id_user, $list_id_user]);
            $db->query(
                "DELETE FROM `mail` WHERE `id_user` IN(?li) OR `id_kont` IN(?li)",
                         [$list_id_user, $list_id_user]);
            $db->query(
                "DELETE FROM `user_voice2` WHERE `id_user` IN(?li) OR `id_kont` IN(?li)",
                         [$list_id_user, $list_id_user]);
            $db->query(
                "DELETE FROM `user_collision` WHERE `id_user` IN(?li) OR `id_user2` IN(?li)",
                         [$list_id_user, $list_id_user] );
            $db->query(
                "DELETE FROM `votes_user` WHERE `id_user` IN(?li)",
                         [$list_id_user]);
        
            $tables = $db->query('SHOW TABLE STATUS') ;
            while ($table = $tables->row()) {
                if ($table['Engine'] == 'MyISAM' && $table['Data_free'] > '0') {
                    $list_table_defrag[] = '`' . $table['Name'] . '`';
                }
            }
            if (!empty($list_table_defrag)) {
                $db->query('OPTIMIZE TABLE ' . join(', ', $list_table_defrag) . ';');
            }
            msg('Удалено ' . $del_th . ' пользователей');
        }
    } else {
        $err = 'За этот период пользователи не найдены';
    }
}

err();
aut();

?>
<form method="post" class="foot" action="?">
    <p>Будут удалены пользователи, не посещавшие сайт
    <p><input name="write" value="6" type="text" size="3" /><p>
    <p><select name="write2">
        <option value="">Период</option>
        <option value="mes">Месяцев</option>
        <option value="sut">Суток</option>
    </select>
    <p><input value="Удалить" type="submit" />
<script type="text/javascript">
  document.write('<input type="button" value="Отмена" onClick=\'location.href="?"\'/>');
</script>
        <noscript>&nbsp;&nbsp;<a class="button" href="?">Отмена</a></noscript>
</form>
<?php
if (user_access('adm_panel_show')) {
    echo "<div class='foot'>\n";
    echo "&laquo;<a href='/adm_panel/'>В админку</a><br />\n";
    echo "</div>\n";
}
include_once H . 'sys/inc/tfoot.php';
