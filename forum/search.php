<?php
include_once '../sys/inc/start.php';
include_once '../sys/inc/compress.php';
include_once '../sys/inc/sess.php';
include_once '../sys/inc/home.php';
include_once '../sys/inc/settings.php';
include_once '../sys/inc/db_connect.php';
include_once '../sys/inc/ipua.php';
include_once '../sys/inc/fnc.php';
include_once '../sys/inc/user.php';
/* Бан пользователя */
if (isset($user['id']) && $db->query("SELECT COUNT(*) FROM `ban` WHERE `razdel` = 'forum' AND `id_user` = '$user[id]' AND (`time` > '$time' OR `view` = '0' OR `navsegda` = '1')")->el()) {
    header('Location: /ban.php?'.SID);
    exit;
}
$searched = &$_SESSION['searched'];
if (!isset($searched) || isset($_GET['newsearch']) || isset($_GET['null'])) {
    // зануляем весь запрос
    $searched['in'] = array('m'=>null);
    $searched['text'] = null;
    $searched['query'] = null;
    $searched['sql_query'] = null;
    $searched['result'] = array();
    $searched['mark'] = array();
}
if (isset($_GET['newsearch'])) {
    include 'inc/search_act.php';
}
// Заголовок страницы
$set['title']='Форум - Поиск';
include_once '../sys/inc/thead.php';
title();
aut(); // форма авторизации
err();
if (isset($_GET['newsearch'])) {
    if (count($searched['result'])!=0) {
        msg('По запросу "' . htmlentities($searched['text'], ENT_QUOTES, 'UTF-8') . '" найдено совпадений:' . count($searched['result']));
    } elseif (!isset($err)) {
        msg('По запросу "' . htmlentities($searched['text'], ENT_QUOTES, 'UTF-8') . '" ничего не найдено');
    }
}
$res = $searched['result'];
if (count($res)) {
    $k_post = count($res);
    $k_page = k_page($k_post, $set['p_str']);
    $page = page($k_page);
    $start = $set['p_str'] * $page-$set['p_str'];
    $end = min($set['p_str'] * $page, $k_post);
     
    echo '<table class="post">';
         
    for ($i = $start; $i < $end; $i++) {
        $them = $res[$i];
     
        if ($db->query("SELECT COUNT(*) FROM `forum_p` WHERE `id_them` = '$them[id]'")->el() == $them['k_post']) {
            // Определение подфорума
            $forum = $db->query("SELECT * FROM `forum_f` WHERE `id` = '$them[id_forum]' LIMIT 1")->row();
             
            // Определение раздела
            $razdel = $db->query("SELECT * FROM `forum_r` WHERE `id` = '$them[id_razdel]' LIMIT 1")->row();
             
            // Лесенка дивов
            if ($num == 0) {
                echo '<div class="nav1">';
                $num = 1;
            } elseif ($num == 1) {
                echo '<div class="nav2">';
                $num = 0;
            }
             
            // Иконка темы
            echo '<img src="/style/themes/' . $set['set_them'] . '/forum/14/them_' . $them['up'] . $them['close'] . '.png" alt="" /> ';
             
            // Ссылка на тему
            echo '<a href="/forum/' . $forum['id'] . '/' . $razdel['id'] . '/' . $them['id'] . '/">' . text($them['name']) . '</a> 
            <a href="/forum/' . $forum['id'] . '/' . $razdel['id'] . '/' . $them['id'] . '/?page=' . $pageEnd . '"> 
            (' . $db->query("SELECT COUNT(*) FROM `forum_p` WHERE `id_forum` = '$forum[id]' AND `id_razdel` = '$razdel[id]' AND `id_them` = '$them[id]'")->el() . ')</a><br/>';
             
            // Подфорум и раздел
            echo '<a href="/forum/' . $forum['id'] . '/">' . text($forum['name']) . '</a> > <a href="/forum/' . $forum['id'] . '/' . $razdel['id'] . '/">' . text($razdel['name']) . '</a>';
             
            // Автор темы
            $ank = get_user($them['id_user']);
            echo 'Автор: <a href="/info.php?id=' . $ank['id'] . '">' . $ank['nick'] . '</a> (' . vremja($them['time_create']) . ') ';
            // Последний пост
            $post = $db->query("SELECT * FROM `forum_p` WHERE `id_them` = '$them[id]' AND `id_razdel` = '$razdel[id]' AND `id_forum` = '$forum[id]' ORDER BY `time` DESC LIMIT 1")->row();
             
            // Автор последнего поста
            if (isset($post['id_user'])) {
                $ank2 = get_user($post['id_user']);
                echo 'Посл.: <a href="/info.php?id=' . $ank2['id'] . '">' . $ank2['nick'] . '</a> (' . vremja($post['time']) . ') ';
            }
             
            echo '</div>';
        } else {
            echo esc(br(bbcode(preg_replace($searched['mark'], "<span class='search_cit'>1</span>", htmlentities($them['msg'], ENT_QUOTES, 'UTF-8')))))."\n";
            echo "Всего совпадений: ".$them['k_post']."\n";
        }
    }
     
    echo '</table>';
     
    if ($k_page > 1) {
        str('?', $k_page, $page);
    } // Вывод страниц
} else {
    include 'inc/search_form.php';
}
// Меню возврата
echo '<div class="foot">';
if (count($searched['result']) != 0) {
    echo '<img src="/style/icons/str2.gif" /> <a href="?null=' . $passgen . '">Новый поиск</a> 
';
}
echo '<img src="/style/icons/str2.gif" /> <a href="/forum/">Форум</a> | <b>Поиск по форуму</b>';
echo '</div>';
include_once '../sys/inc/tfoot.php';
