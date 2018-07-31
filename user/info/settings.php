<?php
include_once '../../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

only_reg();
$set['title']='Мои настройки';
include_once H . 'sys/inc/thead.php';
title();

$post_int = filter_input_array(INPUT_POST, FILTER_VALIDATE_INT);
$post_string = filter_input_array(INPUT_POST, [
                                               'save' => FILTER_SANITIZE_STRING,
                                               'set_them' => FILTER_SANITIZE_STRING,
                                               'set_them2' => FILTER_SANITIZE_STRING
                                               ]);
if (isset($post_string['save'])) {
    $set_user_conf = [];
    if (isset($post_int['add_konts']) && in_array($post_int['add_konts'], [0, 1, 2], true)) {
        $set_user_conf += ['add_konts' => (string)$post_int['add_konts']];
        $user['add_konts'] = $set_user_conf['add_konts'];
    } else {
        $err='Ошибка режима добавления контактов';
    }
    if (isset($post_int['set_files']) && ($post_int['set_files'] == 1 || $post_int['set_files'] == 0)) {
        $set_user_conf += ['set_files' => (string)$post_int['set_files']];
        $user['set_files'] = (string)$set_user_conf['set_files'];
    } else {
        $err='Ошибка режима файлов';
    }
    // Метка 18+
    if (isset($post_int['abuld']) && ($post_int['abuld'] == 1 || $post_int['abuld'] == 0)) {
        $set_user_conf += ['abuld' => (string)$post_int['abuld']];
        $user['abuld'] = $set_user_conf['abuld'];
    } else {
        $err='Ошибка метки 18+';
    }
    if (isset($post_int['show_url']) && ($post_int['show_url'] == 1 || $post_int['show_url'] == 0)) {
        $set_user_conf += ['show_url' => (string)$post_int['show_url']];
        $user['show_url'] = $set_user_conf['show_url'];
    } else {
        $err='Ошибка режима местоположения';
    }
    if (isset($post_int['set_time_chat']) && ($post_int['set_time_chat'] >= 0 && $post_int['set_time_chat'] < 900)) {
        $set_user_conf += ['set_time_chat' => $post_int['set_time_chat']];
        $user['set_time_chat'] = $set_user_conf['set_time_chat'];
        $set['time_chat'] = $user['set_time_chat'];
    } else {
        $err='Ошибка во времени автообновления';
    }
    if ($user['ank_mail']) {
    if (isset($post_int['set_news_to_mail'])) {
        $set_user_conf += ['set_news_to_mail' => $post_int['set_news_to_mail'] ? 1 : 0];
        $user['set_news_to_mail'] = $set_user_conf['set_news_to_mail'];
    } else {
        $err='Ошибка оповещения о новостях по емаил';
    }
}
    if (isset($post_string['set_them']) && preg_match('/^([a-z0-9\-_\(\)]+)$/ui', $post_string['set_them']) && is_dir(H . 'style/themes/' . $post_string['set_them'])) {
        $set_user_conf += ['set_them' => $post_string['set_them']];
        $user['set_them'] = $set_user_conf['set_them'];
    } elseif (isset($post_string['set_them2']) && preg_match('/^([a-z0-9\-_\(\)]+)$/ui', $post_string['set_them2']) && is_dir(H . 'style/themes/' . $post_string['set_them'])) {
        $set_user_conf += ['set_them2' => $post_string['set_them2']];
        $user['set_them2'] = $set_user_conf['set_them2'];
    } else {
        $err='Ошибка применения темы';
    }
    if (isset($post_int['set_p_str']) && $post_int['set_p_str'] > 3 && $post_int['set_p_str'] < 101) {
        $set_user_conf += ['set_p_str' => $post_int['set_p_str']];
        $user['set_p_str'] = $set_user_conf['set_p_str'];
        $set['p_str'] = $user['set_p_str'];
    } else {
        $err='Неправильное количество пунктов на страницу';
    }
    if (isset($post_int['set_timesdvig']) && ($post_int['set_timesdvig'] > -13 && $post_int['set_timesdvig'] < 13)) {
        $set_user_conf += ['set_timesdvig' => $post_int['set_timesdvig']];
        $user['set_timesdvig'] = $set_user_conf['set_timesdvig'];
    } else {
        $err='Неправильный часовой пояс';
    }
    if (!empty($set_user_conf)) {
        $db->query('UPDATE `user` SET ?set WHERE `id`=?i',
                   [$set_user_conf, $user['id']]);
    }
    if (!isset($err)) {
        $_SESSION['message'] = 'Изменения успешно приняты';
        header('Location: ?');
        exit;
    }
}

err();
aut();

echo "<div id='comments' class='menus'>";
echo "<div class='webmenu'>";
echo "<a href='/user/info/settings.php' class='activ'>Общие</a>";
echo "</div>";
echo "<div class='webmenu last'>";
echo "<a href='/user/tape/settings.php'>Лента</a>";
echo "</div>";
echo "<div class='webmenu last'>";
echo "<a href='/user/discussions/settings.php'>Обсуждения</a>";
echo "</div>";
echo "<div class='webmenu last'>";
echo "<a href='/user/notification/settings.php'>Уведомления</a>";
echo "</div>";
echo "<div class='webmenu last'>";
echo "<a href='/user/info/settings.privacy.php' >Приватность</a>";
echo "</div>";
echo "<div class='webmenu last'>";
echo "<a href='/user/info/secure.php' >Пароль</a>";
echo "</div>";
echo "</div>";
echo "<form method='post' action='?$passgen'>\n";
echo "Автообновление в чате:<br />\n<input type='text' name='set_time_chat' value='$set[time_chat]' maxlength='3' /><br />\n";
echo "Пунктов на страницу:<br />\n<input type='text' name='set_p_str' value='$set[p_str]' maxlength='3' /><br />\n";
echo "Тема (" . ($webbrowser ? 'WEB' : 'WAP') . "):<br />\n<select name='set_them" . ($webbrowser ? '2' : null) . "'>\n";

$opendirthem=opendir(H.'style/themes');
while ($themes=readdir($opendirthem)) {
    // пропускаем корневые папки и файлы
    if ($themes=='.' || $themes=='..' || !is_dir(H."style/themes/$themes")) {
        continue;
    }
    // пропускаем темы для определенных     браузеров
    if (file_exists(H."style/themes/$themes/.only_for_" . ($webbrowser ? 'wap' : 'web'))) {
        continue;
    }
    echo "<option value='$themes'" . ($user['set_them'.($webbrowser ? '2' : null)] == $themes ? " selected='selected'" : null) . ">".trim(file_get_contents(H.'style/themes/'.$themes.'/them.name')) . "</option>\n";
}
closedir($opendirthem);

echo "</select><br />\n";echo "Выгрузка файлов:<br />\n<select name='set_files'>\n";
echo "<option value='1'" . ($user['set_files'] == 1 ? " selected='selected'" : null) . ">Показывать поле</option>\n";
echo "<option value='0'" . ($user['set_files'] == 0 ? " selected='selected'" : null) . ">Не использовать выгрузку</option>\n";
echo "</select><br />\n";
echo "Местоположение:<br />\n<select name='show_url'>\n";
echo "<option value='1'" . ($user['show_url'] == 1 ? " selected='selected'" : null) . ">Показывать</option>\n";
echo "<option value='0'" . ($user['show_url'] == 0 ? " selected='selected'" : null) . ">Скрывать</option>\n";
echo "</select><br />\n";
echo "Добавление контактов:<br />\n<select name='add_konts'>\n";
echo "<option value='2'" . ($user['add_konts'] == 2 ? " selected='selected'" : null) . ">При чтении сообщений</option>\n";
echo "<option value='1'" . ($user['add_konts'] == 1 ? " selected='selected'" : null) . ">При написании сообщения</option>\n";
echo "<option value='0'" . ($user['add_konts'] == 0 ? " selected='selected'" : null) . ">Только вручную</option>\n";
echo "</select><br />\n";
echo "Время<br />\n<select name=\"set_timesdvig\"><br />\n";
for ($i = -12; $i < 12; $i++) {
    echo "<option value='$i'" . ($user['set_timesdvig'] == $i ? " selected='selected'" : null) . ">".date("G:i", $time + $i*60*60) . "</option>\n";
}
echo "</select><br />\n";
if ($user['ank_mail']) {
    echo "<label><input type='checkbox' name='set_news_to_mail'" . ($user['set_news_to_mail'] ? " checked='checked'" : null) . " value='1' /> Получать новости на E-mail</label><br />\n";
}
echo "Показ эротического материала без предупреждений:<br />";
echo "<input name='abuld'" . ($user['abuld'] == 0 ? " checked='checked'" : null) . "  type='radio' value='0' />Вкл ";
echo "<input name='abuld'" . ($user['abuld'] == 1 ? " checked='checked'" : null) . "  type='radio' value='1' />Выкл<br />";
echo "<input type='submit' name='save' value='Сохранить' />\n";
echo "</form>\n";
echo "<div class=\"foot\">\n";
echo "<img src='/style/icons/str2.gif' alt='*'> <a href='/info.php?id=$user[id]'>$user[nick]</a> | \n";
echo '<b>Общие</b>';
echo "</div>\n";

include_once H . 'sys/inc/tfoot.php';
