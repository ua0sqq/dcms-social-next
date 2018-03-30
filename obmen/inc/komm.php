<?php

$k_post=$db->query("SELECT COUNT(*) FROM `obmennik_komm` WHERE `id_file`=?i",
                   [$file_id['id']])->el();
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
echo "<table class='post'>\n";
echo "<div class='foot'>";
echo "Комментарии:\n";
echo '</div>';
if (!$k_post) {
    echo "<div class='mess'>";
    echo "Нет сообщений\n";
    echo '</div>';
} else {
    /*------------сортировка по времени--------------*/
    $sort = ['id' => false];
    if (isset($user)) {
        $sort = ($user['sort'] == 1 ? ['id' => true] : ['id' => false]);
        echo "<div id='comments' class='menus'>";
        echo "<div class='webmenu'>";
        echo "<a href='?komm&amp;page=$page&amp;sort=1' class='".($user['sort']==1?'activ':'')."'>Внизу</a>";
        echo "</div>";
        echo "<div class='webmenu'>";
        echo "<a href='?komm&amp;page=$page&amp;sort=0' class='".($user['sort']==0?'activ':'')."'>Вверху</a>";
        echo "</div>";
        echo "</div>";
    }

$q=$db->query("SELECT obk.*, u.id as id_user, u.nick, (
SELECT COUNT(*) FROM `ban` WHERE (`razdel`='all' OR `razdel`='files') AND `post`=1 AND `id_user`=`obk`.`id_user` AND (`time`>" . $time . " OR `navsegda` = 1)) ban
FROM `obmennik_komm` obk
JOIN `user` u ON u.id=obk.id_user WHERE obk.`id_file`=?i ORDER BY ?o LIMIT ?i OFFSET ?i",
              [$file_id['id'], $sort, $set['p_str'], $start]);
while ($post = $q->row()) {
    
    if ($num==0) {
        echo '<div class="nav1">';
        $num=1;
    } elseif ($num==1) {
        echo '<div class="nav2">';
        $num=0;
    }

    echo group($post['id_user']) . " <a href='/info.php?id=$post[id_user]'>$post[nick]</a>";
    
    if (isset($user) && $user['id'] != $post['id_user']) {
        echo " <a href='?showinfo&amp;page=$page&amp;response=$post[id_user]'>[*]</a> \n";
    }
    echo online($post['id_user']) . " (" . vremja($post['time']) . ")<br />\n";
    // Блок сообщения
    if ($post['ban'] == 0) { 
        echo output_text($post['msg']);
    } else {
        echo output_text($banMess).'<br />';
    }
    if (isset($user)) {
        echo '<div style="text-align:right;">';
        if ($post['id_user'] != $user['id']) {
            echo "<a href=\"?showinfo&amp;page=$page&amp;spam=$post[id]\"><img src='/style/icons/blicon.gif' alt='*' title='Это спам'></a> ";
        }
        if (user_access('obmen_komm_del') || $avtor['id'] == $user['id']) {
            echo '<a href="?showinfo&amp;page=' . $page . '&amp;del_post=' . $post['id'] . '"><img src="/style/icons/delete.gif" alt="*"></a>';
        }
        echo "   </div>\n";
    }
    echo "   </div>\n";
}
}
echo "</table>\n";
if ($k_page>1) {
    str('?showinfo&amp;', $k_page, $page);
} // Вывод страниц
if (isset($user)) {
    echo "<form method=\"post\" name='message' action=\"?showinfo&amp;" . $go_link . "\">\n";
    if ($set['web'] && is_file(H . 'style/themes/' . $set['set_them'] . '/altername_post_form.php')) {
        include_once H . 'style/themes/' . $set['set_them'] . '/altername_post_form.php';
    } else {
        echo "$tPanel<textarea name=\"msg\">$insert</textarea><br />\n";
    }
    echo "<input value=\"Отправить\" type=\"submit\" />\n";
    echo "</form>\n";
}
