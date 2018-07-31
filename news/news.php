<?php
include_once '../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

$input_get = filter_input_array(INPUT_GET, FILTER_VALIDATE_INT);
$input_post = filter_input(INPUT_POST, 'msg', FILTER_DEFAULT);

if (!$input_get['id']) {
    $_SESSION['message'] = 'Неверный запрос!';
    header('Location: index.php?' . SID);
    exit;
}
if (!$db->query("SELECT COUNT(*) FROM `news` WHERE `id`=?i", [$input_get['id']])->el()) {
    $_SESSION['message'] = 'Новость не найдена!';
    header('Location: index.php?' . SID);
    exit;
}
// Определение записи новости
$like_query = '';
if (isset($user)) {
    $like_query = ', (SELECT COUNT(*) FROM `like_object` WHERE `id_object`=`nws`.`id` AND `type`="news" AND `id_user`=' . $user['id'] . ') cnt';
}
$news = $db->query(
    "SELECT `nws`.*, `u`.`id` AS `id_user`,  `u`.`pol`?q
FROM `news` `nws`
JOIN `user` `u` ON `u`.`id`=`nws`.`id_user` WHERE `nws`.`id`=?i",
                   [$like_query, $input_get['id']]
)->row();

// Отмечаем уведомления
if (isset($user)) {
    $db->query(
        "UPDATE `notification` SET `read`=? WHERE `type`=? AND `id_user`=?i AND `id_object`=?i",
               ['1', 'news_komm', $user['id'], $news['id']]
    );
}
// Мне нравится
if (isset($user) && isset($input_get['like'])
    && !$news['cnt']) {
    $input_get['like'] = $input_get['like'] ? 1 : 0;
    $db->query(
        "INSERT INTO `like_object` (`id_user`, `id_object`, `type`, `like`) VALUES (?i, ?i, ?, ?i)",
               [$user['id'], $news['id'], 'news', $input_get['like']]
    );
    
    // Начисление баллов за активность
    include_once H . 'sys/add/user.active.php';
}

// Комментарий
if (isset($input_post) && isset($user)) {
    $msg = trim($input_post);
    $mat = antimat($msg);
    if ($mat) {
        $err[] = 'В тексте сообщения обнаружен мат: '.$mat;
    }
    if (strlen2($msg)>1024) {
        $err = 'Сообщение слишком длинное';
    } elseif (strlen2($msg)<2) {
        $err = 'Короткое сообщение';
    } elseif ($db->query(
        "SELECT COUNT(*) FROM `news_komm` WHERE `id_news`=?i AND `id_user`=?i AND `msg`=?",
                         [$input_get['id'], $user['id'], $msg]
    )->el()) {
        $err = 'Ваше сообщение повторяет предыдущее';
    } elseif (!isset($err)) {
        $db->query(
            "INSERT INTO `news_komm` (`id_user`, `time`, `msg`, `id_news`) VALUES(?i, ?i, ?, ?i)",
                   [$user['id'], $time, $msg, $input_get['id']]
        );

        // Начисление баллов за активность
        include_once H.'sys/add/user.active.php';
        
        // Уведомления об ответах
        if (isset($ank_reply['id'])) {
            $notifiacation = $db->query(
                "SELECT * FROM `notification_set` WHERE `id_user`=?i LIMIT ?i",
                                        [$ank_reply['id'], 1]
            )->row();
            
            if ($notifiacation['komm'] == 1 && $ank_reply['id'] != $user['id']) {
                $db->query(
                    "INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES (?i, ?i, ?i, ?, ?i)",
                           [$user['id'], $ank_reply['id'], $news['id'], 'news_komm', $time]
                );
            }
        }
        
        $_SESSION['message'] = 'Ваш комментарий успешно принят';
        header('Location: ?id=' . $input_get['id'] . '&page=' . $input_get['page']);
        exit;
    }
}
$set['title'] = 'Новости - ' . text($news['title']);
include_once H . 'sys/inc/thead.php';
title();
aut();
err();

// Название
echo '<div class="nav1" id="news_title">';
echo '<img src="/style/icons/news.png" alt="*" /> ' . text($news['title']);
echo '</div>';
// Текст новости
echo '<div class="nav2" id="news_content">';
echo output_text($news['msg']);
echo "</div>";

// Мне нравится и автор
echo '<div class="nav2" id="like">';
if (isset($user) && !$news['cnt']) {
    echo '[<img src="/style/icons/like.gif" alt="*"> <a href="?id='.$news['id'].'&amp;like=1">Мне нравится</a>] ';
    echo '[<a href="?id=' . $news['id'] . '&amp;like=0"><img src="/style/icons/dlike.gif" alt="*"></a>]';
} else {
    $cnt = $db->query('SELECT * FROM (
SELECT COUNT(*) "like" FROM `like_object` WHERE `id_object`=?i AND `type`="news" AND `like`=1)q, (
SELECT COUNT(*) "dislike" FROM `like_object` WHERE `id_object`=?i AND `type`="news" AND `like`=0)q2', [$news['id'], $news['id']])->row();

    echo '[<img src="/style/icons/like.gif" alt="*"> ' . $cnt['like'] . '] ';
    echo '[<img src="/style/icons/dlike.gif" alt="*"> ' . $cnt['dislike'] . ']';
}
echo '<br />';
// Автор
echo 'Опубликовал' . ($news['pol'] == 0 ? 'а' : null) . ': ' .
group($news['id_user']) . user::nick($news['id_user']) . medal($news['id_user']) . online($news['id_user']);
echo '</div>';

// Кнопки соц сетей
echo '<div class="nav2" id="news_share">';
echo 'Поделиться:<script type="text/javascript" src="/style/share/share.js" charset="utf-8"></script>
<span class="yashare-auto-init" data-yashareL10n="ru" data-yashareType="none" data-yashareQuickServices="vkontakte,twitter,odnoklassniki,moimir"></span>';
echo '</div>';

// Панелька управления
if (user_access('adm_news')) {
    echo '<div class="nav1" id="news_edit">';
    echo '[<img src="/style/icons/edit.gif" alt="*"> <a href="edit.php?id=' . $news['id'] . '">ред</a>] ';
    echo '[<img src="/style/icons/delete.gif" alt="*"> <a href="./delete.php?news_id=' . $news['id'] . '">удл</a>] ';
    echo '</div>';
}

// листинг
$listing = $db->query('SELECT tbl2.id as start_id, tbl3.id as end_id, (
SELECT COUNT(*)+1 FROM news WHERE id>tbl1.id) AS cnt, (SELECT COUNT(*) FROM news) AS all_cnt
FROM `news` tbl1
LEFT JOIN `news` tbl2 ON tbl1.id > tbl2.id
LEFT JOIN `news` tbl3 ON tbl1.id < tbl3.id
WHERE tbl1.`id`=?i ORDER BY tbl2.`id` DESC, tbl3.id LIMIT ?i', [$news['id'], 1])->row();

echo '<div class="c2" style="text-align: center;">';
echo '<span class="page">' . ($listing['start_id'] ? '<a href="?id=' . $listing['start_id'].'">&laquo; Пред.</a> ':'&laquo; Пред. ') . '</span>';
echo ' (' . $listing['cnt'] . ' из ' . $listing['all_cnt'] . ') ';
echo '<span class="page">' . ($listing['end_id'] ? '<a href="?id=' . $listing['end_id'] . '">След. &raquo;</a>' : ' След. &raquo;') . '</span>';
echo '</div>';

echo '<div class="foot" id="news_komm">';
echo 'Комментарии:';
echo '</div>';
// Колличество комментариев
$k_post = $db->query(
    "SELECT COUNT(*) FROM `news_komm` WHERE `id_news`=?i",
                     [$input_get['id']]
)->el();
$k_page = k_page($k_post, $set['p_str']);
$page = page($k_page);
$start = $set['p_str'] * $page - $set['p_str'];
// Выборка постов
$q = $db->query(
    "SELECT nwk.*, u.id AS id_user, u.`level` FROM `news_komm` nwk
JOIN `user` u ON u.id=nwk.id_user
WHERE nwk.`id_news`=?i ORDER BY nwk.`id`?q LIMIT ?i OFFSET ?i",
                [$input_get['id'], $sort, $set['p_str'], $start]
);
echo '<table class="post">';
if ($k_post == 0) {
    echo '<div class="mess" id="no_object">';
    echo 'Нет сообщений';
    echo '</div>';
} else {
    // сортировка по времени
    if (isset($user)) {
        echo '<div id="comments" class="menus">';
        echo '<div class="webmenu">';
        echo '<a href="?id=' . $news['id'] . '&amp;page=' . $page . '&amp;sort=1" class="' . ($user['sort'] == 1 ? 'activ' : null) . '">Внизу</a>';
        echo '</div>';
        
        echo '<div class="webmenu">';
        echo '<a href="?id=' . $news['id'] . '&amp;page=' . $page . '&amp;sort=0" class="' . ($user['sort'] == 0 ? 'activ' : null) . '">Вверху</a>';
        echo '</div>';
        echo '</div>';
    }
    // alex-borisi
}
while ($post = $q->row()) {
    // Лесенка
    echo '<div class="' . ($num % 2 ? "nav1" : "nav2") . '">';
    $num++;
    echo group($post['id_user']) . user::nick($post['id_user']);
    if (isset($user) && $user['id'] != $post['id_user']) {
        echo ' <a href="?id=' . $news['id'] . '&amp;page=' . $page . '&amp;response=' . $post['id_user'] . '">[*]</a> ';
    }
    echo medal($post['id_user']) . online($post['id_user']) . ' (' . vremja($post['time']) . ')<br />';
    echo output_text($post['msg']) . '<br />';
    if (isset($user)) {
        echo '<div class="right">';
        if (isset($user) && ($user['level'] > $post['level'] || $user['level'] > 0 && $user['id'] == $post['id_user'])) {
            echo '<a href="./delete.php?id=' . $post['id'] . '"><img src="/style/icons/delete.gif" alt="*"></a>';
        }
        echo '</div>';
    }
    echo '</div>';
}
echo '</table>';
// Вывод страниц
if ($k_page>1) {
    str("/news/news.php?id=" . $input_get['id'] . '&amp;', $k_page, $page);
}
// Форма для комментариев
if (isset($user)) {
    echo '<form method="post" name="message" action="?id=' . $input_get['id'] . '&amp;page=' . $page . $go_link . '">';
    if (is_file(H.'style/themes/' . $set['set_them'] . '/altername_post_form.php')) {
        include_once H.'style/themes/' . $set['set_them'] . '/altername_post_form.php';
    } else {
        echo $tPanel . '<textarea name="msg">' . $insert . '</textarea><br />';
    }
    echo '<input value="Отправить" type="submit" />';
    echo '</form>';
}
echo '<div class="foot">';
echo '<img src="/style/icons/str2.gif" alt="*"> <a href="index.php">К новостям</a><br />';
echo '</div>';

include_once H . 'sys/inc/tfoot.php';
