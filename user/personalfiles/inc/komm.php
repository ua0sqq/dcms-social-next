<?php
/*
=======================================
Личные файлы юзеров для Dcms-Social
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
$k_post  = $db->query(
    'SELECT COUNT(*) FROM `obmennik_komm` WHERE `id_file`=?i',
                   [$file_id['id']]
)->el();
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];

echo '<div class="foot">'."\n";
echo "Комментарии:\n";
echo '</div>'."\n";
if ($k_post==0) {
    echo '<div class="mess">'."\n";
    echo "Нет сообщений\n";
    echo '</div>'."\n";
} elseif (isset($user)) {
    // сортировка по времени
    if (isset($user)) {
        echo "<div id='comments' class='menus'>\n";
        echo "<div class='webmenu'>\n";
        echo "<a href='?id_file=$file_id[id]&amp;sort=1' class='".($user['sort']==1?'activ':'')."'>Внизу</a>\n";
        echo "</div>\n";
        echo "<div class='webmenu'>\n";
        echo "<a href='?id_file=$file_id[id]&amp;sort=0' class='".($user['sort']==0?'activ':'')."'>Вверху</a>\n";
        echo "</div>\n";
        echo "</div>\n";
    }
}
$q=$db->query(
    'SELECT obk.*, u.id AS id_user, u.nick, (
SELECT COUNT(*) FROM `ban` WHERE (`razdel`="all" OR `razdel`="files") AND `post`=1 AND `id_user`=obk.id_user AND (`time` > '.$time.' OR `navsegda`=1)) ban
FROM `obmennik_komm` obk
JOIN `user` u ON u.id=obk.id_user
WHERE obk.`id_file`=?i ORDER BY obk.`id` ?q LIMIT ?i OFFSET ?i',
              [$file_id['id'], $sort, $set['p_str'], $start]);

while ($post = $q->row()) {
    if ($num==0) {
        echo '<div class="nav1">'."\n";
        $num=1;
    } elseif ($num==1) {
        echo '<div class="nav2">'."\n";
        $num=0;
    }
    echo " ".group($post['id_user'])." <a href='/info.php?id={$post['id_user']}'>{$post['nick']}</a>\n";
    if (isset($user) && $post['id_user'] != $user['id']) {
        echo ' <a href="?id_file='.$file_id['id'].'&amp;page='.$page.'&amp;response='.$post['id_user'].'">[*]</a> ';
    }
    echo "".online($post['id_user'])." (".vremja($post['time']).")<br />\n";
    
    // Блок сообщения
    if ($post['ban'] == 0) {
        echo output_text($post['msg'])."<br />\n";
    } else {
        echo output_text($banMess).'<br />';
    }
    if (isset($user)) {
        echo '<div style="text-align:right;">'."\n";
        if ($post['id_user']!=$user['id']) {
            echo "<a href=\"?id_file=$file_id[id]&amp;page=$page&amp;spam=$post[id]\"><img src='/style/icons/blicon.gif' alt='*' title='Это спам'></a>\n";
        }
        if (user_access('obmen_komm_del') || $post['id_user'] == $user['id']) {
            echo '<a href="?id_file='.$file_id['id'].'&amp;page='.$page.'&amp;del_post='.$post['id'].'"><img src="/style/icons/delete.gif" alt="*"></a>'."\n";
        }
        echo "   </div>\n";
    }
    echo "   </div>\n";
}

if ($k_page>1) {
    str('?id_file='.$file_id['id'].'&amp;', $k_page, $page);
} // Вывод страниц
if (isset($user)) {
    echo "<form method=\"post\" name='message' action=\"?id_file=$file_id[id]".$go_link."\">\n";

    if ($set['web'] && is_file(H.'style/themes/'.$set['set_them'].'/altername_post_form.php')) {
        include_once H.'style/themes/'.$set['set_them'].'/altername_post_form.php';
    } else {
        echo "$tPanel<textarea name=\"msg\">$insert</textarea><br />\n";
    }

    echo "<input value=\"Отправить\" type=\"submit\" />\n";
    echo "</form>\n";
}
