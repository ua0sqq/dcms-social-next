<?php
include_once '../../../sys/inc/start.php';
include_once '../../../sys/inc/compress.php';
include_once '../../../sys/inc/sess.php';
include_once '../../../sys/inc/home.php';
include_once '../../../sys/inc/settings.php';
include_once '../../../sys/inc/db_connect.php';
include_once '../../../sys/inc/ipua.php';
include_once '../../../sys/inc/fnc.php';
include_once '../../../sys/inc/user.php';

$set['title']='Жалобы'; // заголовок страницы
include_once '../../../sys/inc/thead.php';
title();
err();
aut();

if (user_access('adm_panel_show')) {
    if ($user['group_access']==2) {
        $types = " where `types` = 'chat' ";
    } elseif ($user['group_access']==3) {
        $types =" where `types` = 'forum' ";
    } elseif ($user['group_access']==4) {
        $types = " where (`types` = 'obmen_komm' OR `types` = 'files_komm') ";
    } elseif ($user['group_access']==5) {
        $types = " where `types` = 'lib_komm' ";
    } elseif ($user['group_access']==6) {
        $types = " where `types` = 'foto_komm' ";
    } elseif ($user['group_access']==11) {
        $types = " where `types` = 'notes_komm' ";
    } elseif ($user['group_access']==12) {
        $types = " where `types` = 'guest' ";
    } elseif (($user['group_access']>6 && $user['group_access']<10) || $user['group_access']==15) {
        $types = null;
    }
    $k_post=$db->query(
                    "SELECT COUNT(*) FROM `spamus` ?q",
                            [$types])->el();
    $k_page=k_page($k_post, $set['p_str']);
    $page=page($k_page);
    $start=$set['p_str']*$page-$set['p_str'];

    if ($k_post==0) {
        echo "<div class='mess'>\n";
        echo "Нет новых жалоб\n";
        echo "</div>\n";
    } else {
        echo "<div class='mess'>\n";
        echo "Внимание! После рассмотрения жалобы не забывайте ее удалить!";
        echo "</div>\n";
    }
    $q=$db->query(
            "SELECT * FROM `spamus`?q ORDER BY id DESC LIMIT ?i OFFSET ?i",
                    [$types, $set['p_str'], $start]);
    while ($post = $q->row()) {

        if ($num==0) {
            echo "  <div class='nav1'>\n";
            $num=1;
        } elseif ($num==1) {
            echo "  <div class='nav2'>\n";
            $num=0;
        }

        $ank=get_user($post['id_user']);
        $spamer = get_user($post['id_spam']);
        echo "<strong>Раздел:</strong> ";
        if ($post['razdel'] == 'mail') {
            echo "<font color='red'>Почта</font><br />";
        }
        if ($post['razdel'] == 'guest') {
            echo '<a href="/guest/"><span class="off">Гостевая</span></a><br />';
        }
        if ($post['razdel'] == 'files_komm') {  // Файлы юзеров
            $file_id = $db->query(
        "SELECT * FROM `obmennik_files` WHERE `id`=?i",
                          [$post['id_object']])->row();
            $dir = $db->query(
        "SELECT * FROM `user_files` WHERE `id`=?i",
                      [$file_id['my_dir']])->row();
            echo '<span class="off">Личные файлы</span> | ';
            echo " <a href='/user/personalfiles/$file_id[id_user]/$dir[id]/?id_file=$file_id[id]'>".htmlspecialchars($file_id['name'])."</a><br />\n";
        }
        if ($post['razdel'] == 'obmen_komm') {  // Обменник
            $file_id=$db->query(
        "SELECT * FROM `obmennik_files` WHERE `id`=?i",
                        [$post['id_object']])->row();
            $dir_id=$db->query(
        "SELECT * FROM `obmennik_dir` WHERE `id`=?i",
                       [$file_id['id_dir']])->row();
    
            echo '<span class="off">Зона обмена</font> | ';
            echo " <a href='/obmen$dir_id[dir]$file_id[id].$file_id[ras]?showinfo'>".htmlspecialchars($file_id['name'])."</a><br />\n";
        }
        if ($post['razdel'] == 'notes_komm') {  // Дневники
            $notes=$db->query(
        "SELECT * FROM `notes` WHERE `id`=?i",
                      [$post['id_object']])->row();
            echo '<span class="off">Дневники</font> | ';
            echo " <a href='/plugins/notes/list.php?id=$notes[id]'>".htmlspecialchars($notes['name'])."</a><br />\n";
        }
        if ($post['razdel'] == 'forum') {  // Тема форума
            $them=$db->query(
        "SELECT * FROM `forum_t` WHERE `id`=?i",
                      [$post['id_object']])->row();
            echo '<span class="off">Форум</font> | ';
            echo " <a href='/forum/$them[id_forum]/$them[id_razdel]/$them[id]/'>".htmlspecialchars($them['name'])."</a><br />\n";
        }

        if ($post['razdel'] == 'foto_komm') {  // Фотографии
            $foto=$db->query(
        "SELECT * FROM `gallery_foto` WHERE `id`=?i",
                      [$post['id_object']])->row();
            echo '<span class="off">Фото</font> | ';
            echo " <a href='/foto/$foto[id_user]/$foto[id_gallery]/$foto[id]/'>".htmlspecialchars($foto['name'])."</a><br />\n";
        }
        if ($post['razdel'] == 'stena') { // Стена юзера
            echo '<span class="off">Стена</font> | ';
            $anketa = get_user($post['id_object']);
            echo " <a href='/info.php?id=$anketa[id]'>$anketa[nick]</a>\n";
            echo " ".medal($anketa['id'])." ".online($anketa['id'])."<br />";
        }
        if ($post['razdel'] == 'status_komm') {	// Статус
            $status=$db->query(
                            "SELECT * FROM `status` WHERE `id`=?i",
                                    [$post['id_object']])->row();
            echo "<a href='/user/status/komm.php?id=$status[id]'><font color='red'>Статус</font></a> | ";
            $anketa = get_user($status['id_user']);
            echo " <a href='/info.php?id=$anketa[id]'>$anketa[nick]</a>\n";
            echo " ".medal($anketa['id'])." ".online($anketa['id'])."<br />";
        }
        echo "<strong>Жалоба от:</strong> <a href='/info.php?id=$ank[id]'>$ank[nick]</a>\n";
        echo " ".medal($ank['id'])." ".online($ank['id'])." (".vremja($post['time']).")<br />";
        if ($post['razdel']=='mail' || $post['razdel']=='guest' || $post['razdel']=='forum' || $post['razdel']=='stena') {
            echo "<strong>На сообщение:</strong> <font color='red' style='border-bottom: 1px solid green;'>".output_text($post['spam'])."<br /></font>\n";
        }
        echo "<strong>Комментарий:</strong> ".output_text($post['msg'])."<br />";
        echo "<strong>Нарушитель:</strong>  <a href='/info.php?id=$spamer[id]'>$spamer[nick]</a>";
        echo "".medal($spamer['id'])." ".online($spamer['id'])."<br />";
        echo "   </div>\n";
        if (($user['id']!=$spamer['id'] && $user['group_access']>=$spamer['group_access']) || ($user['id']==1)) {
            echo "<div class='mess'>[<a href='/adm_panel/ban.php?id=$spamer[id]'><img src='/style/icons/blicon.gif' alt='*'> бан</a>] 
[<a href='./delete.php?id=$post[id]&amp;otkl=1'><img src='/style/icons/delete.gif' alt='*'> отклонить</a>] [<a href='./delete.php?id=$post[id]'><img src='/style/icons/ok.gif' alt='*'> рассмотрена</a>] </div>\n";
        } elseif ($user['id']==$spamer['id']) {
            echo "<div class='mess'>На вас поступила жалоба от <font color='green'>$ank[nick]</font> 
пожалуста дождитесь администратора для выяснения обстоятельств.</div>\n";
        } else {
            echo "<div class='mess'>У вас не достаточно полномочий, для рассмотрения этой жалобы.</div>\n";
        }
    }
    if ($k_page>1) {
        str('?', $k_page, $page);
    } // Вывод страниц
    echo "<div class='foot'>\n";
    echo "<img src='/style/icons/str2.gif' alt='*'> <a href='/plugins/admin/'>Админ раздел</a><br />\n";
    echo "</div>\n";
}
include_once '../../../sys/inc/tfoot.php';
