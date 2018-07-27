<?php
include_once '../../sys/inc/start.php';
include_once '../../sys/inc/compress.php';
include_once '../../sys/inc/sess.php';
include_once '../../sys/inc/home.php';
include_once '../../sys/inc/settings.php';
include_once '../../sys/inc/db_connect.php';
include_once '../../sys/inc/ipua.php';
include_once '../../sys/inc/fnc.php';
include_once '../../sys/inc/user.php';

$input_get = filter_input_array(INPUT_GET, FILTER_VALIDATE_INT);
if (!isset($input_get['id'])) {
    http_response_code(302);
    header('Location: index.php');
    exit;
}

if (isset($user)) {
    $sql = ', (
SELECT COUNT( * ) FROM `notes_count` WHERE `id_user`=' . $user['id'] . ' AND `id_notes`=n.id) cnt, (
SELECT COUNT( * ) FROM `frends` WHERE (`user`=' . $user['id'] . ' AND `frend`=n.id_user) OR (`user`=n.id_user AND `frend`=' . $user['id'] . ')) is_frend, (
SELECT COUNT( * ) FROM `notification` WHERE `type`="notes_komm" AND `id_user`=' . $user['id'] . ' AND `id_object`=n.id AND `read`="0") notes_not_read, (
SELECT COUNT( * ) FROM `discussions` WHERE `id_user`=' . $user['id'] . ' AND `type`="notes" AND `id_sim`=n.id AND `count`>0) discut_not_read';
}
$notes = $db->query(
                    'SELECT n.*, u.id AS id_user, u.nick, (
SELECT COUNT( * ) FROM `bookmarks` WHERE `id_object`=n.id AND `type`="notes") mark?q;
FROM `notes` n
LEFT JOIN `user` u ON u.id=n.id_user WHERE n.`id`=?i',
                    [$sql, $input_get['id']])->row();

if (!$notes['id']) {
    header('Location: index.php');
    exit;
}

$avtor = ['id' => $notes['id_user'], 'nick' => $notes['nick']];

 // Закладки
$markinfo = $notes['mark'];
if (isset($user) && $notes['notes_not_read']) {
    $db->query(
        "UPDATE `notification` SET `read`=? WHERE `type`=? AND `id_user`=?i AND `id_object`=?i",
               ['1', 'notes_komm', $user['id'], $notes['id']]);
}
/*
================================
Модуль жалобы на пользователя
и его сообщение либо контент
в зависимости от раздела
================================
*/
if (isset($input_get['spam'])  &&  isset($user)) {
    $mess = $db->query(
        'SELECT nk.id, nk.`time`, nk.msg, u.id AS id_user, (
SELECT COUNT( * ) FROM `spamus` WHERE `id_object`=nk.id AND `id_user`=' . $user['id'] . ' AND `id_spam`=nk.id_user AND `razdel`="notes_komm") is_spam
FROM `notes_komm` nk
JOIN `user` u ON u.id=nk.id_user WHERE nk.`id`=?i',
                       [$input_get['spam']])->row();

    if (!$db->query(
        "SELECT COUNT( * ) FROM `spamus` WHERE `id_user`=?i AND `id_spam`=?i AND `razdel`=? AND `spam`=?",
                    [$user['id'], $mess['id_user'], 'notes_komm', $mess['msg']])->el()) {
        if (isset($_POST['msg'])) {
            if ($mess['id_user'] != $user['id']) {
                $msg=trim($_POST['msg']);
                if (strlen2($msg)<3) {
                    $err='Укажите подробнее причину жалобы';
                }
                if (strlen2($msg)>1512) {
                    $err='Длина текста превышает предел в 512 символов';
                }
                $types = 0;
                if (in_array($_POST['types'], [1,2,3])) {
                    $types = $_POST['types'];
                }

                if (!isset($err)) {
                    $db->query(
                        "INSERT INTO `spamus` (`id_object`, `id_user`, `msg`, `id_spam`, `time`, `types`, `razdel`, `spam`) VALUES(?i, ?i, ?, ?i, ?i, ?i, ?, ?)",
                               [$mess['id'], $user['id'], $msg, $mess['id_user'], $time, $types, 'notes_komm', $mess['msg']]);
                    
                    $_SESSION['message'] = 'Заявка на рассмотрение отправлена';
                    header("Location: ?id=$notes[id]&page=".intval($input_get['page'])."&spam=$mess[id]");
                    exit;
                }
            }
        }
    }
    $set['title']='Дневник ' . text($notes['name']) . '';
    include_once '../../sys/inc/thead.php';
    title();
    aut();
    err();
    if (!$mess['is_spam']) {
        echo "<div class='mess'>Ложная информация может привести к блокировке ника. 
Если вас постоянно достает один человек - пишет всякие гадости, вы можете добавить его в черный список.</div>";
        echo "<form class='nav1' method='post' action='?id=$notes[id]&amp;page=".$input_get['page']."&amp;spam=$mess[id]'>\n";
        echo "<p><b>Пользователь:</b></p>";
        echo "<p>".avatar($mess['id_user'])."  ".group($mess['id_user'])." ". user::nick($mess['id_user'])."\n";
        echo "".medal($mess['id_user'])." ".online($mess['id_user'])." (".vremja($mess['time']).")</p>";
        if (!empty($mess['msg'])) {
            echo "<p><b>Нарушение:</b></p>\n<p><font color='green'>".output_text($mess['msg'])."</font></p>";
        }
        echo "Причина:<br />\n<select name='types'>\n";
        echo "<option value='1' selected='selected'>Спам/Реклама</option>\n";
        echo "<option value='2' selected='selected'>Мошенничество</option>\n";
        echo "<option value='3' selected='selected'>Оскорбление</option>\n";
        echo "<option value='0' selected='selected'>Другое</option>\n";
        echo "</select><br />\n";
        echo "Комментарий:$tPanel";
        echo "<textarea name=\"msg\"></textarea><br />";
        echo "<input value=\"Отправить\" type=\"submit\" />\n";
        echo "</form>\n";
    } else {
        echo "<div class='mess'>Жалоба на ". user::nick($mess['id_user'])." будет рассмотрена в ближайшее время.</div>";
    }
    echo "<div class='foot'>\n";
    echo "<img src='/style/icons/str2.gif' alt='*'> <a href='?id=$notes[id]&amp;page=".intval($input_get['page'])."'>Назад</a><br />\n";
    echo "</div>\n";
    include_once '../../sys/inc/tfoot.php';
    exit;
}

// Запись просмотра
if (isset($user) && !$notes['cnt']) {
    $db->query(
        "INSERT INTO `notes_count` (`id_notes`, `id_user`) VALUES (?i, ?i)",
               [$notes['id'], $user['id']]);
    $db->query(
        "UPDATE `notes` SET `count`=`count`+1 WHERE `id`=?i",
               [$notes['id']]);
}
// очищаем счетчик этого обсуждения
if (isset($user) && $notes['discut_not_read']) {
    $db->query(
        "UPDATE `discussions` SET `count`=?i WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i LIMIT ?i",
               [0, $user['id'], 'notes', $notes['id'], 1]);
}

$set['title']='Дневник - ' . text($notes['name']) . '';
$set['meta_description'] = text($notes['msg']);
include_once '../../sys/inc/thead.php';
if (isset($_POST['msg']) && isset($user)) {
    $msg=$_POST['msg'];
    if (strlen2($msg)>1024) {
        $err='Сообщение слишком длинное';
    } elseif (strlen2($msg)<2) {
        $err='Короткое сообщение';
    } elseif ($db->query("SELECT COUNT( * ) FROM `notes_komm` WHERE `id_notes`=?i AND `id_user`=?i AND `msg`=?",
                         [$input_get['id'], $user['id'], $msg])->el()) {
        $err='Ваше сообщение повторяет предыдущее';
    } elseif (!isset($err)) {
        // Уведомления об ответах
        if (isset($user) && $respons==true) {
            $notifiacation=$db->query("SELECT * FROM `notification_set` WHERE `id_user`=?i LIMIT ?i",
                                      [$ank_reply['id'], 1])->row();
            
            if ($notifiacation['komm'] == 1 && $ank_reply['id'] != $user['id']) {
                $db->query("INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES (?i, ?i, ?i, ?, ?i)",
                           [$user['id'], $ank_reply['id'], $notes['id'], 'notes_komm', $time]);
            }
        }
        // Обсуждения
        $q = $db->query(
                "SELECT fr.user FROM `frends` fr 
JOIN discussions_set ds ON ds.id_user=fr.user
WHERE fr.`frend`=?i AND fr.`disc_notes`=?i AND ds.`disc_notes`=?i AND `i`=?i",
                            [$user['id'], 1, 1, 1]);
        while ($frend = $q->row()) {
            // друзьям автора
            if (!$db->query(
    "SELECT COUNT( * ) FROM `discussions` WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i",
                [$frend['user'], 'notes', $notes['id']])->el()) {
                if ($notes['id_user'] != $frend['user']  || $frend['user'] != $user['id']) {
                    $db->query(
            "INSERT INTO `discussions` (`id_user`, `avtor`, `type`, `time`, `id_sim`, `count`) VALUES(?i, ?i, ?, ?i, ?i, ?i)",
                   [$frend['user'], $notes['id_user'], 'notes', $time, $notes['id'], 1]);
                }
            } else {
                if ($notes['id_user'] != $frend['user'] || $frend['user'] != $user['id']) {
                    $db->query(
            "UPDATE `discussions` SET `count`=`count`+1, `time`=?i WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i LIMIT ?i",
                   [$time, $frend['user'], 'notes', $notes['id'],  1]);
                }
            }
        }
        // отправляем автору
        if (!$db->query(
            "SELECT COUNT( * ) FROM `discussions` WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i",
                        [$notes['id_user'], 'notes', $notes['id']])->el()) {
            if ($notes['id_user'] != $user['id']) {
                $db->query(
                    "INSERT INTO `discussions` (`id_user`, `avtor`, `type`, `time`, `id_sim`, `count`) VALUES(?i, ?i, ?, ?i, ?i, ?i)",
                           [$notes['id_user'], $notes['id_user'], 'notes', $time, $notes['id'], 1]);
            }
        } else {
            if ($notes['id_user'] != $user['id']) {
                $db->query(
                    "UPDATE `discussions` SET `count`=`count`+1, `time`=?i WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i LIMIT ?i",
                           [$time, $notes['id_user'], 'notes', $notes['id'], 1]);
            }
        }
        $db->query(
            "INSERT INTO `notes_komm` (`id_user`, `time`, `msg`, `id_notes`) VALUES(?i, ?i, ?, ?i)",
                   [$user['id'], $time, $msg, $input_get['id']]);
        $db->query(
            "UPDATE `user` SET `balls`=`balls`+1 WHERE `id`=?i",
                   [$user['id']]);
        
        $_SESSION['message'] = 'Сообщение успешно отправлено';
        header("Location: /plugins/notes/list.php?id=$notes[id]&page=".$input_get['page']."");
        exit;
    }
}
// листинг
$listing = $db->query('SELECT tbl2.id as start_id, tbl3.id as end_id, (
SELECT COUNT( * )+1 FROM notes WHERE id>tbl1.id) AS cnt, (SELECT COUNT( * ) FROM notes) AS all_cnt
FROM `notes` tbl1
LEFT JOIN `notes` tbl2 ON tbl1.id > tbl2.id
LEFT JOIN `notes` tbl3 ON tbl1.id < tbl3.id
WHERE tbl1.`id`=?i ORDER BY tbl2.`id` DESC, tbl3.id LIMIT ?i', [$notes['id'], 1])->row();

$listing_page = '<div class="c2" style="text-align: center;">'."\n".
'<span class="page">'.($listing['end_id']?'<a href="/plugins/notes/list.php?id='.$listing['end_id'].'">&laquo; Пред.</a> ':'&laquo; Пред. ').'</span>'."\n".
' ('.$listing['cnt'].' из '.$listing['all_cnt'].') '."\n".
'<span class="page">' . ($listing['start_id'] ? '<a href="/plugins/notes/list.php?id=' . $listing['start_id'] . '">След. &raquo;</a>' : ' След. &raquo;') . '</span>'."\n".
'</div>';

title();
aut(); // форма авторизации
err();

if ((!isset($user) && $notes['private'] == 1)
    || ($notes['private'] == 1 && $user['id'] != $avtor['id'] && $notes['is_frend'] != 2  && !user_access('notes_delete'))) {
    echo '<div class="mess">'."\n";
    echo '  Дневник доступен только для друзей'."\n";
    echo '</div>'."\n";
    echo $listing_page;
    echo '<div class="foot">'."\n";
    echo '  <a href="index.php">Назад</a>'."\n";
    echo '</div>'."\n";
    include_once '../../sys/inc/tfoot.php';
    exit;
}
if ((!isset($user) && $notes['private'] == 2)
    || ($notes['private'] == 2 && $user['id'] != $avtor['id'] && $notes['is_frend'] != 2  && !user_access('notes_delete'))) {
    echo '<div class="mess">'."\n";
    echo '  Пользователь запретил просмотр дневника'."\n";
    echo '</div>'."\n";
    echo $listing_page;
    echo '<div class="foot">'."\n";
    echo '  <a href="index.php">Назад</a>'."\n";
    echo '</div>'."\n";
    include_once '../../sys/inc/tfoot.php';
    exit;
}
if (isset($input_get['delete']) && ($user['id']==$avtor['id'] || user_access('notes_delete'))) {
    echo '<div class="mess" style="text-align:center;">'."\n";
    echo '<p>Вы действительно хотите удалить дневник ' . output_text($notes['name']) . '?</p>';
    echo "[<a href='./delete.php?id=$notes[id]'><img src='/style/icons/ok.gif'> удалить</a>] [<a href='/plugins/notes/list.php?id=$notes[id]'><img src='/style/icons/delete.gif'> отмена</a>] \n";
    echo "</div>";
    include_once '../../sys/inc/tfoot.php';
}
if (isset($user)) {
    if (isset($input_get['like']) && $input_get['like'] == 1) {
        if (!$db->query(
            "SELECT COUNT( * ) FROM `notes_like` WHERE `id_user`=?i AND `id_notes`=?i",
                        [$user['id'], $notes['id']])->el()) {
            $db->query(
                "INSERT INTO `notes_like` (`id_notes`, `id_user`, `like`) VALUES (?i, ?i, ?i)",
                       [$notes['id'], $user['id'], 1]);
            $db->query(
                "UPDATE `notes` SET `count`=`count`+1 WHERE `id`=?i",
                       [$notes['id']]);
            
            $_SESSION['message'] = 'Ваш голос засчитан';
            header("Location: /plugins/notes/list.php?id=$notes[id]&page=".intval($input_get['page'])."");
            exit;
        }
    }
    if (isset($input_get['like']) && $input_get['like'] == 0) {
        if (!$db->query(
            "SELECT COUNT( * ) FROM `notes_like` WHERE `id_user`=?i AND `id_notes`=?i",
                        [$user['id'], $notes['id']])->el()) {
            $db->query(
                "INSERT INTO `notes_like` (`id_notes`, `id_user`, `like`) VALUES (?i, ?i, ?i)",
                       [$notes['id'], $user['id'], 0]);
            $db->query(
                "UPDATE `notes` SET `count`=`count`-1 WHERE `id`=?i",
                       [$notes['id']]);
            
            $_SESSION['message'] = 'Ваш голос засчитан';
            header("Location: /plugins/notes/list.php?id=$notes[id]&page=".intval($input_get['page'])."");
            exit;
        }
    }
    if (isset($input_get['fav']) && $input_get['fav']==1) {
        if (!$db->query(
            "SELECT COUNT( * ) FROM `bookmarks` WHERE `id_user`=?i AND `id_object`=?i AND `type`=?",
                        [$user['id'], $notes['id'], 'notes'])->el()) {
            $db->query(
                "INSERT INTO `bookmarks` (`type`,`id_object`, `id_user`, `time`) VALUES (?, ?i, ?i, ?i)",
                       ['notes', $notes['id'], $user['id'], $time]);
            $_SESSION['message'] = 'Дневник добавлен в закладки';
            header("Location: /plugins/notes/list.php?id=$notes[id]&page=".intval($input_get['page'])."");
            exit;
        }
    }
    if (isset($input_get['fav']) && $input_get['fav']==0) {
        if ($db->query(
            "SELECT COUNT( * ) FROM `bookmarks` WHERE `id_user`=?i AND `id_object`=?i AND `type`=?",
                        [$user['id'], $notes['id'], 'notes'])->el()) {
            $db->query(
                "DELETE FROM `bookmarks` WHERE `id_user`=?i AND  `id_object`=?i AND `type`=?",
                       [$user['id'], $notes['id'], 'notes']);
            $_SESSION['message'] = 'Дневник удален из закладок';
            header('Location: /plugins/notes/list.php?id='.$notes['id'].'&page='.$input_get['page']);
            exit;
        }
    }
}
echo "<div class=\"foot\">\n";
echo "<img src='/style/icons/str2.gif' alt='*'> <a href='index.php'>Дневники</a> | <a href='/info.php?id=$avtor[id]'>$avtor[nick]</a>\n";
echo ' | <b>' . output_text($notes['name']) . '</b>';
echo "</div>\n";
echo "<div class='main'>";
echo "<table style='width:110%;'><td style='width:4%;'>".avatar($avtor['id'])."</td>";
echo "<td style='width:96%;'> Автор: ";
echo group($avtor['id']);
echo " ".user::nick($avtor['id'], 1, 1, 1)." ";
echo "(<img src='/style/icons/them_00.png'>  ".vremja($notes['time']).")<br/>";
echo "<img src='/style/icons/eye.png'> Просмотров: ".$notes['count']."</td></table></div>";
$stat1 = $notes['msg'];
if (!$set['web']) {
    $mn=20;
} else {
    $mn=90;
}
// количество слов выводится в зависимости от браузера
$stat=explode(' ', $stat1);
// деление статьи на отдельные слова
$k_page=k_page(count($stat), $set['p_str']*$mn);
$page=page($k_page);
$start=$set['p_str']*$mn*($page-1);
$stat_1=null;
for ($i=$start;$i<$set['p_str']*$mn*$page && $i<count($stat);$i++) {
    $stat_1.=$stat[$i].' ';
}
// вывод статьи со всем форматированием
echo '<div class="mess">' . output_text($stat_1) , ''; 
notes_share($notes['id']); echo '</div>';
if ($k_page>1) {
    str("?id=$notes[id]&amp;", $k_page, $page);
}
// листинг HTML
echo $listing_page;
// plugins
$sql = null;
if (isset($user)) {
    $sql = ', (
SELECT COUNT( * ) FROM `notes` WHERE `id_user`=' . $user['id'] . ' AND `share_type`="notes" AND `share_id`=' . $notes['id'] . ') is_user_share, (
SELECT COUNT( * ) FROM `notes_like` WHERE `id_user` = ' . $user['id'] . ' AND `id_notes` = ' . $notes['id'] . ') is_user_like, (
SELECT COUNT( * ) FROM `bookmarks` WHERE `id_user` = ' . $user['id'] . ' AND `id_object` = ' . $notes['id'] . ' AND `type`="notes") is_user_bookmark';
}
$cnt = $db->query('SELECT (
SELECT COUNT( * ) FROM `notes` WHERE `share_id`=?i AND `share_type`="notes") is_share?q;, ((
SELECT COUNT( * ) FROM `notes_like` WHERE `like`="1" AND `id_notes`=?i) - (
SELECT COUNT( * ) FROM `notes_like` WHERE `like`="0" AND `id_notes`=?i)) like_notes', [$notes['id'], $sql, $notes['id'], $notes['id']])->row();

echo "<div class='main2'>";

if (isset($user) && !$cnt['is_user_share'] && $user['id'] != $notes['id_user']) {
    echo " <a href='share.php?id=".$notes['id']."'><img src='/style/icons/action_share_color.gif'> Поделиться: (".$cnt['is_share'].")</a>";
} else {
    echo '<img src="/style/icons/action_share_color.gif"> Поделились:  (' . $cnt['is_share'] . ')';
}
if (isset($user) && (user_access('notes_delete') || $user['id']==$avtor['id'])) {
    echo "<br/><a href='edit.php?id=$notes[id]'><img src='/style/icons/edit.gif'> Изменить</a> <a href='?id=$notes[id]&amp;delete'><img src='/style/icons/delete.gif'> Удалить</a>\n";
}
echo "</div><div class='main'>";

if (isset($user) && $user['id']!=$avtor['id']) {
    if (!$cnt['is_user_like']) {
        echo "<a href='/plugins/notes/list.php?id=$notes[id]&amp;like=1'><img src='/style/icons/thumbu.png' alt='*' /> </a> (".$cnt['like_notes'].") <a href='/plugins/notes/list.php?id=$notes[id]&amp;like=0'><img src='/style/icons/thumbd.png' alt='*' /></a>\n";
    } else {
        echo " <img src='/style/icons/thumbu.png' alt='*' /> (".$cnt['like_notes'].") <img src='/style/icons/thumbd.png' alt='*' /> \n";
    }
} else {
    echo " <img src='/style/icons/thumbu.png' alt='*' />  (".$cnt['like_notes'].") <img src='/style/icons/thumbd.png' alt='*' /> \n";
}
// В закладки
if (isset($user)) {
    echo "".($webbrowser ? "&bull;" : null)." <img src='/style/icons/add_fav.gif' alt='*' /> ";
    if (!$cnt['is_user_bookmark']) {
        echo "<a href='/plugins/notes/list.php?id=$notes[id]&amp;fav=1'>B закладки</a><br />\n";
    } else {
        echo "<a href='/plugins/notes/list.php?id=$notes[id]&amp;fav=0'>Из закладок</a><br />\n";
    }
    echo "<img src='/style/icons/add_fav.gif' alt='*' />  <a href='/plugins/notes/fav.php?id=".$notes['id']."'>Кто добавил? </a> (".$markinfo.")";
}
echo '</div>';

echo "<div class='main'>";
echo 'В соц. сети: ';
echo '<script type="text/javascript" src="/style/share/share.js" charset="utf-8"></script>
<span class="yashare-auto-init" data-yashareL10n="ru" data-yashareType="none" data-yashareQuickServices="vkontakte,twitter,odnoklassniki,moimir"></span>';
echo "</div>";

// Комментарии дневников
$k_post=$db->query(
    "SELECT COUNT( * ) FROM `notes_komm` WHERE `id_notes`=?i",
                   [$input_get['id']])->el();
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
echo '<div class="foot">';
echo "<b>Комментарии</b>: (".$k_post.")\n";
echo '</div>';
if (!$k_post) {
    echo '<div class="mess">';
    echo "Нет сообщений\n";
    echo '</div>';
} else {
    if (isset($user)) {
        // сортировка по времени
        if (isset($user)) {
            echo "<div id='comments' class='menus'>";
            echo "<div class='webmenu'>";
            echo "<a href='/plugins/notes/list.php?id=$notes[id]&amp;page=$page&amp;sort=1' class='".($user['sort']==1?'activ':'')."'>Внизу</a>";
            echo "</div>";
            echo "<div class='webmenu'>";
            echo "<a href='/plugins/notes/list.php?id=$notes[id]&amp;page=$page&amp;sort=0' class='".($user['sort']==0?'activ':'')."'>Вверху</a>";
            echo "</div>";
            echo "</div>";
        }
    }
    $q=$db->query(
    'SELECT nk.*, u.id AS id_user, (
SELECT COUNT( * ) FROM `ban` WHERE (`razdel`="all" OR `razdel`="notes") AND `post`=1 AND `id_user`=nk.id_user AND (`time`>' . $time . ' OR `navsegda`="1")) is_post_ban
FROM `notes_komm` nk
JOIN `user` u ON u.id=nk.id_user
WHERE nk.`id_notes`=?i ORDER BY nk.`time` ?q LIMIT ?i OFFSET ?i',
              [$input_get['id'], $sort, $set['p_str'], $start]);

    while ($post = $q->row()) {
        if ($num==0) {
            echo '<div class="nav1">';
            $num=1;
        } elseif ($num==1) {
            echo '<div class="nav2">';
            $num=0;
        }

        echo group($post['id_user']) .' ' . user::nick($post['id_user']) . ' ';
        if (isset($user) && $post['id_user'] != $user['id']) {
            echo '<a href="?id=' . $notes['id'] . '&amp;response=' . $post['id_user'] . '">[*]</a> '."\n";
        }
        echo "".medal($post['id_user'])." ".online($post['id_user'])." (".vremja($post['time']).")<br />";
        // Блок сообщения
        if (!$post['is_post_ban']) {
            echo output_text($post['msg'])."<br />\n";
        } else {
            echo output_text($banMess).'<br />';
        }
        if (isset($user)) {
            echo '<div style="text-align:right;">';
            if ($post['id_user']!=$user['id']) {
                echo "<a href=\"?id=$notes[id]&amp;page=$page&amp;spam=$post[id]\"><img src='/style/icons/blicon.gif' alt='*' title='Это спам'></a> ";
            }
            if (isset($user) && (user_access('notes_delete') || $user['id']==$notes['id_user'])) {
                echo '<a href="./delete.php?komm='.$post['id'].'"><img src="/style/icons/delete.gif" alt="*"></a>';
            }
    
            echo "</div>\n";
        }
        echo "</div>\n";
    }

    if ($k_page>1) {
        str('/plugins/notes/list.php?id=' . $input_get['id'] . '&amp;', $k_page, $page);
    }
}
if ($notes['private_komm']==1 && $user['id']!=$avtor['id'] && $notes['is_frend']!=2  && !user_access('notes_delete')) {
    echo '<div class="mess">Комментировать могут только друзья</div>';
    echo "  <div class='foot'>\n";
    echo "<a href='index.php'>Назад</a><br />\n";
    echo "   </div>\n";
    include_once '../../sys/inc/tfoot.php';
    exit;
}
if ($notes['private_komm']==2 && $user['id']!=$avtor['id'] && !user_access('notes_delete')) {
    echo '<div class="mess">'."\n".
    '   Пользователь запретил комментирование дневника'."\n".
    '</div>'."\n";
    echo " <div class='foot'>\n";
    echo "   <a href='index.php'>Назад</a>\n";
    echo "</div>\n";
    include_once '../../sys/inc/tfoot.php';
    exit;
}
if (isset($user)) {
    echo "<form method=\"post\" name='message' action=\"?id=".$input_get['id']."&amp;page=$page".$go_link."\">\n";
    if ($set['web'] && is_file(H.'style/themes/'.$set['set_them'].'/altername_post_form.php')) {
        include_once H.'style/themes/'.$set['set_them'].'/altername_post_form.php';
    } else {
        echo "$tPanel<textarea name=\"msg\">$insert</textarea><br />\n";
    }
    echo "<input value=\"Отправить\" type=\"submit\" />\n";
    echo "</form>\n";
}
echo "<div class=\"foot\">\n";
echo "<img src='/style/icons/str2.gif' alt='*'> <a href='index.php'>Дневники</a> | <a href='/info.php?id=$avtor[id]'>$avtor[nick]</a>\n";
echo ' | <b>' . output_text($notes['name']) . '</b>';
echo "</div>\n";

include_once '../../sys/inc/tfoot.php';
