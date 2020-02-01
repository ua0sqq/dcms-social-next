<?php
include_once '../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

user_access('adm_news', null, 'index.php?'.SID);

$edit_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$args = [
         'title' => FILTER_DEFAULT,
         'msg' => FILTER_DEFAULT,
         'link' => FILTER_DEFAULT,
         'view' => FILTER_DEFAULT,
         'ok' => FILTER_DEFAULT,
         'mail' => FILTER_VALIDATE_INT,
         'mn' => FILTER_VALIDATE_INT,
         'ch' => FILTER_VALIDATE_INT,
         ];
$input_post = filter_input_array(INPUT_POST, $args);
unset($args);

if (!$edit_id) {
    $_SESSION['message'] = 'Неверный запрос!';
    header('Location: index.php?' . SID);
    exit;
}
if (!$db->query("SELECT COUNT(*) FROM `news` WHERE `id`=?i", [$edit_id])->el()) {
    $_SESSION['message'] = 'Новость не найдена!';
    header('Location: index.php?' . SID);
    exit;
}

$news = $db->query(
    "SELECT * FROM `news` WHERE `id`=?i",
                   [$edit_id]
)->row();

if (isset($input_post['view'])) {
    $news['title'] = $input_post['title'];
    $news['msg'] = $input_post['msg'];
    $news['link'] = $input_post['link'];
    $news['id_user'] = $user['id'];
}
if (isset($input_post['title']) && isset($input_post['msg']) && isset($input_post['link']) && isset($input_post['ok'])) {
    $title = esc($input_post['title'], 1);
    $link = esc($input_post['link'], 1);
    $msg = esc($input_post['msg']);

    if ($link != null && !preg_match('#^https?://#', $link) && !preg_match('#^/#i', $link)) {
        $link='/'.$link;
    }
    if (strlen2($title) > 50) {
        $err='Слишком большой заголовок новости';
    }
    if (strlen2($title) < 3) {
        $err='Короткий заголовок';
    }
    $mat = antimat($title);
    if ($mat) {
        $err[] = 'В заголовке новости обнаружен мат: '.$mat;
    }
    if (strlen2($msg)>10024) {
        $err='Содержиние новости слишком большое';
    }
    if (strlen2($msg) < 2) {
        $err='Новость слишком короткая';
    }
    $mat = antimat($msg);
    if ($mat) {
        $err[] = 'В содержании обнаружен мат: '.$mat;
    }
    $title = trim($input_post['title']);
    $msg = trim($input_post['msg']);
    if (!isset($err)) {
        $ch = $input_post['ch'];
        $mn = $input_post['mn'];
        $main_time = time() + $ch * $mn * 60 * 60 * 24;
        if ($main_time <= time()) {
            $main_time = 0;
        }

        $updt = ['title' => $title, 'msg' => $msg, 'link' => $link, 'main_time' => $main_time, 'time' => $time];
        $tbl = $db->getTable('news');
        $tbl->update($updt, ['id' => (int)$news['id']]);
        $db->query("UPDATE `user` SET `news_read` = '0'");

        $_SESSION['message'] = 'Изменения успешно приняты';
        header("Location: /news/news.php?id=$news[id]");
        exit;
    }
}
$set['title'] = 'Новости - редактирование';
include_once H . 'sys/inc/thead.php';
title();
err();
aut(); // форма авторизации

if (isset($input_post['view']) && !isset($err)) {
    echo '<div class="main_menu">';

    echo text($news['title']);
    echo '</div>';
    echo '<div class="mess">';
    echo output_text($news['msg']) . '<br />';
    echo '</div>';

    if ($news['link'] != null) {
        echo '<div class="main">';
        echo '<a href="' . htmlentities($news['link'], ENT_QUOTES, 'UTF-8') . '">Подробности &rarr;</a><br />';
        echo '</div>';
    }
}
echo '<form class="mess" method="post" name="message" action="?id=' . $news['id'] . '">';
echo 'Заголовок новости:<br /><input name="title" size="16" maxlength="32" value="' . text($news['title']) . '" type="text" /><br />';
$insert = text($news['msg']);
if (is_file(H.'style/themes/'.$set['set_them'].'/altername_post_form.php')) {
    include_once H.'style/themes/'.$set['set_them'].'/altername_post_form.php';
} else {
    echo 'Сообщение:' . $tPanel . '<textarea name="msg">' . $insert . '</textarea><br />';
}
echo 'Ссылка:<br /><input name="link" size="16" maxlength="64" value="' . text($news['link']) . '" type="text" /><br />';
echo 'Показывать на главной:<br />';
echo '<input type="text" name="ch" size="3" value="' . (isset($input_post['ch']) ? $input_post['ch'] : "1") . '" />';
echo '<select name="mn">';
echo '  <option value="0" '.($input_post['mn'] && $input_post['mn'] == 0 ? "selected='selected'" : null).'>   </option>';
echo '  <option value="1" '.($input_post['mn'] && $input_post['mn'] == 1 ? "selected='selected'" : null).'>Дней</option>';
echo '  <option value="7" '.($input_post['mn'] && $input_post['mn'] == 7 ? "selected='selected'" : null).'>Недель</option>';
echo '  <option value="31" '.($input_post['mn'] && $input_post['mn'] == 31 ? "selected='selected'" : null).'>Месяцев</option>';
echo '</select><br />';
echo '<input value="Просмотр" type="submit" name="view"/> ';
echo '<input value="Готово" type="submit" name="ok"/>';
echo '</form>';
echo'<div class="foot">';
echo '<img src="/style/icons/str.gif" alt="*"> <a href="index.php">Новости</a> | <a href="/news/news.php?id=' . $news['id'] . '">' . text($news['title']) . '</a><br />';
echo '</div>';

include_once H . 'sys/inc/tfoot.php';
