<?php

$k_post=$db->query("SELECT COUNT(*) FROM `obmennik_komm` WHERE `id_file` = '$file_id[id]'")->el();
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
echo "<table class='post'>\n";
echo "<div class='foot'>";
echo "Комментарии:\n";
echo '</div>';
if ($k_post==0) {
    echo "<div class='mess'>";
    echo "Нет сообщений\n";
    echo '</div>';
} else {
    /*------------сортировка по времени--------------*/
    if (isset($user)) {
        echo "<div id='comments' class='menus'>";
        echo "<div class='webmenu'>";
        echo "<a href='?komm&amp;page=$page&amp;sort=1' class='".($user['sort']==1?'activ':'')."'>Внизу</a>";
        echo "</div>";
        echo "<div class='webmenu'>";
        echo "<a href='?komm&amp;page=$page&amp;sort=0' class='".($user['sort']==0?'activ':'')."'>Вверху</a>";
        echo "</div>";
        echo "</div>";
    }
    /*---------------alex-borisi---------------------*/
}
$q=$db->query("SELECT * FROM `obmennik_komm` WHERE `id_file` = '$file_id[id]' ORDER BY `id` $sort LIMIT $start, $set[p_str]");
while ($post = $q->row()) {
    $ank=$db->query("SELECT * FROM `user` WHERE `id` = '$post[id_user]' LIMIT 1")->row();
    /*-----------зебра-----------*/
    if ($num==0) {
        echo '<div class="nav1">';
        $num=1;
    } elseif ($num==1) {
        echo '<div class="nav2">';
        $num=0;
    }
    /*---------------------------*/
    echo " ".group($ank['id'])." <a href='/info.php?id=$ank[id]'>$ank[nick]</a>";
    if (isset($user) && $user['id'] != $ank['id']) {
        echo " <a href='?showinfo&amp;page=$page&amp;response=$ank[id]'>[*]</a> \n";
    }
    echo "".online($ank['id'])." (".vremja($post['time']).")<br />\n";
    $postBan = $db->query("SELECT COUNT(*) FROM `ban` WHERE (`razdel` = 'all' OR `razdel` = 'files') AND `post` = '1' AND `id_user` = '$ank[id]' AND (`time` > '$time' OR `navsegda` = '1')")->el();
    if ($postBan == 0) { // Блок сообщения
        echo output_text($post['msg']);
    } else {
        echo output_text($banMess).'<br />'; // Текст при блоке поста
    }
    if (isset($user)) {
        echo '<div style="text-align:right;">';
        if ($ank['id']!=$user['id']) {
            echo "<a href=\"?showinfo&amp;page=$page&amp;spam=$post[id]\"><img src='/style/icons/blicon.gif' alt='*' title='Это спам'></a> ";
        }
        if (user_access('obmen_komm_del') || $avtor['id'] == $user['id']) {
            echo '<a href="?showinfo&amp;page='.$page.'&amp;del_post='.$post['id'].'"><img src="/style/icons/delete.gif" alt="*"></a>';
        }
        echo "   </div>\n";
    }
    echo "   </div>\n";
}
echo "</table>\n";
if ($k_page>1) {
    str('?showinfo&amp;', $k_page, $page);
} // Вывод страниц
if (isset($user)) {
    echo "<form method=\"post\" name='message' action=\"?showinfo&amp;".$go_otv."\">\n";
    if ($set['web'] && is_file(H.'style/themes/'.$set['set_them'].'/altername_post_form.php')) {
        include_once H.'style/themes/'.$set['set_them'].'/altername_post_form.php';
    } else {
        echo "$tPanel<textarea name=\"msg\">$respons_msg</textarea><br />\n";
    }
    echo "<input value=\"Отправить\" type=\"submit\" />\n";
    echo "</form>\n";
}
