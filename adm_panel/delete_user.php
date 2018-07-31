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

user_access('user_delete', null, 'index.php?'.SID);
adm_check();

if (isset($_GET['id'])) {
    $ank['id'] = intval($_GET['id']);
} else {
    header('Location: index.php?' . SID);
    exit;
}
if (!$db->query("SELECT COUNT( * ) FROM `user` WHERE `id`=?i",
                [$ank['id']])->el()) {
    header('Location: index.php?' . SID);
    exit;
}
$ank = get_user($ank['id']);
if ($user['level'] <= $ank['level']) {
    header('Location: index.php?' . SID);
    exit;
}
$set['title'] = 'Удаление пользователя ' . $ank['nick'];
include_once H . 'sys/inc/thead.php';
title();
if (isset($_POST['delete'])) {
    if (function_exists('set_time_limit')) {
        @set_time_limit(600);
    }
    $mass[0] = $ank['id'];
    $collisions = user_collision($mass, 1);
    $db->query("DELETE FROM `user` WHERE `id`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `chat_post` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `gifts_user` WHERE `id_user`=?i OR `id_ank`=?i",
               [$ank['id'], $ank['id']]);
    $db->query("DELETE FROM `frends` WHERE `user`=?i OR `frend`=?i",
               [$ank['id'], $ank['id']]);
    $db->query("DELETE FROM `frends_new` WHERE `user`=?i OR `to`=?i",
               [$ank['id'], $ank['id']]);
    
    $db->query("DELETE FROM `stena` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `stena_like` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `status_like` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `status` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `status_komm` WHERE `id_user`=?i",
               [$ank['id']]);
    $q5 = $db->query("SELECT `id` FROM `forum_t` WHERE `id_user`=?i",
                [$ank['id']])->col();
    foreach ($q5 as $post_id) {
        $db->query("DELETE FROM `forum_p` WHERE `id_them`=?i",
                   [$post_id]);
    }
    $db->query("DELETE FROM `forum_t` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `user_set` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `notification` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `notification_set` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `discussions` WHERE `id_user`=?i OR `id_user`=?i",
               [$ank['id'], $ank['id']]);
    $db->query("DELETE FROM `discussions_set` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `forum_p` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `forum_zakl` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `guest` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `news_komm` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `user_files` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `user_music` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `like_object` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `status` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `status_like` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `status_komm` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `mark_people` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `tape_set` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `tape` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `tape` WHERE `avtor`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `tape` WHERE `id_file`=?i AND `type`=?",
               [$ank['id'], 'frend']);
    $opdirbase = @opendir(H.'sys/add/delete_user_act');
    while ($filebase = @readdir($opdirbase)) {
        if (preg_match('#\.php$#i', $filebase)) {
            include_once(H.'sys/add/delete_user_act/'.$filebase);
        }
    }
    $q5=$db->query("SELECT `id` FROM `obmennik_files` WHERE `id_user`=?i",
                   [$ank['id']])->col();
    if (!empty($q5)) {
        foreach ($q5 as $post_id) {
            if (is_file(H . 'sys/obmen/files/' . $post_id . '.dat')) {
                unlink(H . 'sys/obmen/files/' . $post_id . '.dat');
            }
            array_map('unlink', glob(H . 'sys/obmen/screens/*/' . $post_id . '.*'));
        }
    }
    $db->query("DELETE FROM `obmennik_files` WHERE `id_user`=?i",
               [$ank['id']]);
    $db->query("DELETE FROM `users_konts` WHERE `id_user`=?i OR `id_kont`=?i",
               [$ank['id'], $ank['id']]);
    $db->query("DELETE FROM `mail` WHERE `id_user`=?i OR `id_kont`=?i",
               [$ank['id'], $ank['id']]);
    $db->query("DELETE FROM `user_voice2` WHERE `id_user`=?i OR `id_kont`=?i",
               [$ank['id'], $ank['id']]);
    $db->query("DELETE FROM `user_collision` WHERE `id_user`=?i OR `id_user2`=?i",
               [$ank['id'], $ank['id']]);
    $db->query("DELETE FROM `votes_user` WHERE `id_user`=?i",
               [$ank['id']]);
    if (count($collisions) > 1 && isset($_GET['all'])) {
        for ($i = 1; $i < count($collisions); $i++) {
            $db->query("DELETE FROM `user` WHERE `id`=?i",
                       [$collisions[$i]]);
            $db->query("DELETE FROM `chat_post` WHERE `id_user`=?i",
                       [$collisions[$i]]);
            $db->query("DELETE FROM `forum_t` WHERE `id_user`=?i",
                       [$collisions[$i]]);
            $q5 = $db->query("SELECT `id` FROM `forum_t` WHERE `id_user`=?i",
                       [$collisions[$i]])->col();
            foreach ($q5 as $post_id) {
                $db->query("DELETE FROM `forum_p` WHERE `id_them`=?i",
                           [$post_id]);
            }
            $db->query("DELETE FROM `forum_p` WHERE `id_user`=?i",
                       [$collisions[$i]]);
            $db->query("DELETE FROM `forum_zakl` WHERE `id_user`=?i",
                       [$collisions[$i]]);
            $db->query("DELETE FROM `guest` WHERE `id_user`=?i",
                       [$collisions[$i]]);
            $db->query("DELETE FROM `news_komm` WHERE `id_user`=?i",
                       [$collisions[$i]]);
            $q5=$db->query("SELECT `id` FROM `obmennik_files` WHERE `id_user`=?i",
                       [$collisions[$i]])->col();
    if (!empty($q5)) {
        foreach ($q5 as $post_id) {
            if (is_file(H . 'sys/obmen/files/' . $post_id . '.dat')) {
                unlink(H . 'sys/obmen/files/' . $post_id . '.dat');
            }
            array_map('unlink', glob(H . 'sys/obmen/screens/*/' . $post_id . '.*'));
        }
    }
            $db->query("DELETE FROM `obmennik_files` WHERE `id_user`=?i",
                       [$collisions[$i]]);
            $db->query("DELETE FROM `users_konts` WHERE `id_user`=?i OR `id_kont`=?i",
                       [$collisions[$i], $collisions[$i]]);
            $db->query("DELETE FROM `mail` WHERE `id_user`=?i OR `id_kont`=?i",
                       [$collisions[$i], $collisions[$i]]);
            $db->query("DELETE FROM `user_voice2` WHERE `id_user`=?i OR `id_kont`=?i",
                       [$collisions[$i], $collisions[$i]]);
            $db->query("DELETE FROM `user_collision` WHERE `id_user`=?i OR `id_user2`=?i",
                       [$collisions[$i], $collisions[$i]]);
            $db->query("DELETE FROM `votes_user` WHERE `id_user`=?i",
                       [$collisions[$i]]);
        }
        admin_log('Пользователи', 'Удаление', 'Удаление группы пользователей "' . $ank['nick'] . '" (id#' . implode(',id#', $collisions) . ')');
        msg('Все данные о пользователях удалены');
    } else {
        admin_log('Пользователи', 'Удаление', 'Удаление пользователя "' . $ank['nick'] . '" (id#' . $ank['id'] . ')');
        msg("Все данные о пользователе $ank[nick] удалены");
    }
    $tab = $db->query('SHOW TABLE STATUS') ;
    while ($tables = $tab->row()) {
        if ($tables['Engine'] == 'MyISAM' && $tables['Data_free'] > '0') {
            $db->query('OPTIMIZE TABLE `' . $tables['Name'] . '`');
        }
    }
    echo '<div class="foot">'."\n\t";
    echo '&laquo;<a href="/user/users.php">Пользователи</a>'."\n";
    echo '</div>'."\n";
    include_once H . 'sys/inc/tfoot.php';
}
$mass[0] = $ank['id'];
$collisions = user_collision($mass, 1);
$chat_post = $db->query("SELECT COUNT( * ) FROM `chat_post` WHERE `id_user`=?i",
                      [$ank['id']])->el();
if (count($collisions)>1 && isset($_GET['all'])) {
    $chat_post_coll=0;
    for ($i = 1; $i < count($collisions); $i++) {
        $chat_post_coll += $db->query("SELECT COUNT( * ) FROM `chat_post` WHERE `id_user`=?i",
                                    [$collisions[$i]])->el();
    }
    if ($chat_post_coll != 0) {
        $chat_post = $chat_post . ' +' . $chat_post_coll . '*';
    }
}
echo "<span class=\"ank_n\">Сообщений в чате:</span> <span class=\"ank_d\">$chat_post</span><br />\n";
$k_them=$db->query("SELECT COUNT( * ) FROM `forum_t` WHERE `id_user`=?i",
                      [$ank['id']])->el();
if (count($collisions)>1 && isset($_GET['all'])) {
    $k_them_coll=0;
    for ($i=1;$i<count($collisions);$i++) {
        $k_them_coll+=$db->query("SELECT COUNT( * ) FROM `forum_t` WHERE `id_user`=?i",
                                    [$collisions[$i]])->el();
    }
    if ($k_them_coll!=0) {
        $k_them="$k_them +$k_them_coll*";
    }
}
echo "<span class=\"ank_n\">Тем в форуме:</span> <span class=\"ank_d\">$k_them</span><br />\n";
$k_p_forum=$db->query("SELECT COUNT( * ) FROM `forum_p` WHERE `id_user`=?i",
                      [$ank['id']])->el();
if (count($collisions)>1 && isset($_GET['all'])) {
    $k_p_forum_coll=0;
    for ($i=1;$i<count($collisions);$i++) {
        $k_p_forum_coll+=$db->query("SELECT COUNT( * ) FROM `forum_p` WHERE `id_user`=?i",
                                    [$collisions[$i]])->el();
    }
    if ($k_p_forum_coll!=0) {
        $k_p_forum="$k_p_forum +$k_p_forum_coll*";
    }
}
echo "<span class=\"ank_n\">Соощений в форуме:</span> <span class=\"ank_d\">$k_p_forum</span><br />\n";
$zakl=$db->query("SELECT COUNT( * ) FROM `forum_zakl` WHERE `id_user`=?i",
                      [$ank['id']])->el();
if (count($collisions)>1 && isset($_GET['all'])) {
    $zakl_coll=0;
    for ($i=1;$i<count($collisions);$i++) {
        $zakl_coll+=$db->query("SELECT COUNT( * ) FROM `forum_zakl` WHERE `id_user`=?i",
                                    [$collisions[$i]])->el();
    }
    if ($zakl_coll!=0) {
        $zakl="$zakl +$zakl_coll*";
    }
}
echo "<span class=\"ank_n\">Закладок:</span> <span class=\"ank_d\">$zakl</span><br />\n";
$guest=$db->query("SELECT COUNT( * ) FROM `guest` WHERE `id_user`=?i",
                      [$ank['id']])->el();
if (count($collisions)>1 && isset($_GET['all'])) {
    $guest_coll=0;
    for ($i=1;$i<count($collisions);$i++) {
        $guest_coll+=$db->query("SELECT COUNT( * ) FROM `guest` WHERE `id_user`=?i",
                                    [$collisions[$i]])->el();
    }
    if ($guest_coll!=0) {
        $guest="$guest +$guest_coll*";
    }
}
echo "<span class=\"ank_n\">Гостевая:</span> <span class=\"ank_d\">$guest</span><br />\n";
$konts=$db->query("SELECT COUNT( * ) FROM `users_konts` WHERE `id_user`=?i OR `id_kont`=?i",
                  [$ank['id'], $ank['id']])->el();
if (count($collisions)>1 && isset($_GET['all'])) {
    $konts_coll=0;
    for ($i=1;$i<count($collisions);$i++) {
        $konts_coll+=$db->query("SELECT COUNT( * ) FROM `users_konts` WHERE `id_user`=?i OR `id_kont`=?i",
                                [$collisions[$i], $collisions[$i]])->el();
    }
    if ($konts_coll!=0) {
        $konts="$konts +$konts_coll*";
    }
}
echo "<span class=\"ank_n\">Контакты:</span> <span class=\"ank_d\">$konts</span><br />\n";
$mail=$db->query("SELECT COUNT( * ) FROM `mail` WHERE `id_user`=?i OR `id_kont`=?i",
                  [$ank['id'], $ank['id']])->el();
if (count($collisions)>1 && isset($_GET['all'])) {
    $mail_coll=0;
    for ($i=1;$i<count($collisions);$i++) {
        $mail_coll+=$db->query("SELECT COUNT( * ) FROM `mail` WHERE `id_user`=?i OR `id_kont`=?i",
                                [$collisions[$i], $collisions[$i]])->el();
    }
    if ($mail_coll!=0) {
        $mail="$mail +$mail_coll*";
    }
}
echo "<span class=\"ank_n\">Приватные сообщения:</span> <span class=\"ank_d\">$mail</span><br />\n";

$news_komm=$db->query("SELECT COUNT( * ) FROM `news_komm` WHERE `id_user`=?i",
                      [$ank['id']])->el();
if (count($collisions)>1 && isset($_GET['all'])) {
    $news_komm_coll=0;
    for ($i=1;$i<count($collisions);$i++) {
        $news_komm_coll+=$db->query("SELECT COUNT( * ) FROM `news_komm` WHERE `id_user`=?i",
                                    [$collisions[$i]])->el();
    }
    if ($news_komm_coll!=0) {
        $news_komm="$news_komm +$news_komm_coll*";
    }
}
echo "<span class=\"ank_n\">Комментарии новостей:</span> <span class=\"ank_d\">$news_komm</span><br />\n";
$user_voice=$db->query("SELECT COUNT( * ) FROM `user_voice2` WHERE `id_user`=?i OR `id_kont`=?i",
                  [$ank['id'], $ank['id']])->el();
if (count($collisions)>1 && isset($_GET['all'])) {
    $user_voice_coll=0;
    for ($i=1;$i<count($collisions);$i++) {
        $user_voice_coll+=$db->query("SELECT COUNT( * ) FROM `user_voice2` WHERE `id_user`=?i OR `id_kont`=?i",
                                [$collisions[$i], $collisions[$i]])->el();
    }
    if ($user_voice_coll!=0) {
        $user_voice="$user_voice +$user_voice_coll*";
    }
}
echo "<span class=\"ank_n\">Рейтинги:</span> <span class=\"ank_d\">$user_voice</span><br />\n";
$obmennik=$db->query("SELECT COUNT( * ) FROM `obmennik_files` WHERE `id_user`=?i",
                      [$ank['id']])->el();
if (count($collisions)>1 && isset($_GET['all'])) {
    $obmennik_coll=0;
    for ($i=1;$i<count($collisions);$i++) {
        $obmennik_coll+=$db->query("SELECT COUNT( * ) FROM `obmennik_files` WHERE `id_user`=?i",
                                    [$collisions[$i]])->el();
    }
    if ($obmennik_coll!=0) {
        $obmennik="$obmennik +$obmennik_coll*";
    }
}
echo "<span class=\"ank_n\">Файлы в обменнике:</span> <span class=\"ank_d\">$obmennik</span><br />\n";
$opdirbase=@opendir(H.'sys/add/delete_user_info');
while ($filebase=@readdir($opdirbase)) {
    if (preg_match('#\.php$#i', $filebase)) {
        include_once(H.'sys/add/delete_user_info/'.$filebase);
    }
}
echo "<form method=\"post\" action=\"\">\n";
echo "<input value=\"Удалить\" type=\"submit\" name='delete' />\n";
echo "</form>\n";
if (count($collisions)>1 && isset($_GET['all'])) {
    echo "* Также будут удалены пользователи:\n";
    for ($i=1;$i<count($collisions);$i++) {
        $ank_coll=$db->query("SELECT * FROM `user` WHERE `id`=?i",
                                    [$collisions[$i]])->row();
        echo "$ank_coll[nick]";
        if ($i==count($collisions)-1) {
            echo '.';
        } else {
            echo '; ';
        }
    }
    echo "<br />\n";
}
echo "Удаленные данные невозможно будет восстановить<br />\n";
echo "<div class='foot'>\n";
echo "&laquo;<a href='/info.php?id=$ank[id]'>В анкету</a><br />\n";
echo "&laquo;<a href='/user/users.php'>Пользователи</a><br />\n";
echo "</div>\n";
include_once H . 'sys/inc/tfoot.php';
