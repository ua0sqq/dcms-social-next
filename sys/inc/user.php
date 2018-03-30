<?php
/*
-----------------------------------------------------------------
Загрузка Классов
-----------------------------------------------------------------
*/
require 'classes/class.user.php';
function search_ban($a, $b)
{
    $result = false;
    foreach ($a as $aval) {
        foreach ($b as $bval) {
            if ($aval === $bval) {
                return $aval;
            }
        }
    }
    return $result;
}
$sort = 'DESC';
$insert = null;
$otvet = null;
$go_link = null;
$respons = false;
// Определение юзера
if (isset($_SESSION['id_user']) && $db->query("SELECT COUNT(*) FROM `user` WHERE `id`=?i",
                                              [$_SESSION['id_user']])->el()) {
// TODO: что то мне эта хрень с хитробаном не нравится
    $ban_module = ['guest', 'notes', 'forum', 'obmen', 'chat', 'lib', 'foto','personalfiles'];
    $module = search_ban(explode('/', $_SERVER['PHP_SELF']), $ban_module);
    if ($module) {
        if ($module == 'obmen') $module = 'files';
        if ($module == 'personalfiles') $module = 'files';
        $whr = '(`razdel`="all" OR `razdel`="' . $module . '")';
    } else {
        $whr = '`razdel`="all"';
    }

    $user = $db->query('SELECT `u`.*, `gr`.`name` AS `group_name`, `gr`.`level` AS `group_level`, (
SELECT COUNT(*) FROM `ban` WHERE ?q AND `id_user` = `u`.`id` AND (`time` > ?i OR `view` = ? OR `navsegda` = "1")) as ban FROM user `u` 
LEFT JOIN `user_group` `gr` ON `u`.`group_access`=`gr`.`id`
WHERE `u`.`id`=?i', [$whr, time(), "0", $_SESSION['id_user']])->row();

    $user['type_input'] = 'session';
} elseif (!isset($input_page) && isset($_COOKIE['id_user']) && isset($_COOKIE['pass']) && $_COOKIE['id_user'] && $_COOKIE['pass']) {
    if (!isset($_POST['token'])) {
        header("Location: /login.php?return=" . urlencode($_SERVER['REQUEST_URI']) . "&$passgen");
        exit;
    }
}
if (!isset($_SERVER['HTTP_REFERER'])) {
    $_SERVER['HTTP_REFERER'] = '/index.php';
}
// если аккаунт не активирован
if (isset($user['activation']) && $user['activation'] != null) {
    $err[] = 'Вам необходимо активировать Ваш аккаунт по ссылке, высланной на Email, указанный при регистрации';
    unset($user);
}
if (isset($user)) {
    $set_user = [];
    $user['level'] = $user['group_level'];
    $timeactiv  =  time() - $user['date_last'];
    if ($timeactiv < 120) {
        $newtimeactiv = $user['time'] + $timeactiv;
        $set_user += ['time' => (int)$newtimeactiv];
    } else {
        $set_user += ['time' => $user['time']];
    }

    if (isset($user['type_input']) && isset($_SESSION['http_referer']) && isset($ref['host']) && $ref['host'] == $_SESSION['http_referer']) {
        $db->query('INSERT INTO `user_ref` (`time`, `id_user`, `type_input`, `url`) VALUES (?i, ?i, ?, ?) ON
DUPLICATE KEY UPDATE `time` = VALUES (`time`)', [time(), $user['id'], $user['type_input'], $ref['host']]);
    }

    // Время обновления чата
    if ($user['set_time_chat']!=null) {
        $set['time_chat'] = $user['set_time_chat'];
    }
    // Постраничная навигация
    if ($user['set_p_str']!=null) {
        $set['p_str'] = $user['set_p_str'];
    }
    // Режим иконок
    $set['set_show_icon'] = $user['set_show_icon'];
    // бан пользователя
    if (!isset($banpage)) {
        if ((int)$user['ban']) {
            header('Location: /ban.php?'.SID);
            exit;
        }
    }
    /*
    ========================================
    Создание настроек юзера
    ========================================
    */
    $conf_user = $db->query(
        "SELECT * FROM (
SELECT COUNT(*) AS userset FROM `user_set` WHERE `id_user` = ?i)q1, (
SELECT COUNT(*) AS discussionset FROM `discussions_set` WHERE `id_user` = ?i)q2, (
SELECT COUNT(*) AS tapeset FROM `tape_set` WHERE `id_user` = ?i)q3, (
SELECT COUNT(*) AS notificationset FROM `notification_set` WHERE `id_user` = ?i)q",
                            [$user['id'], $user['id'], $user['id'], $user['id']]
    )->row();
    if (!$conf_user['userset']) {
        $db->query("INSERT INTO `user_set` (`id_user`) VALUES (?i)", [$user['id']]);
    }
    if (!$conf_user['discussionset']) {
        $db->query("INSERT INTO `discussions_set` (`id_user`) VALUES (?i)", [$user['id']]);
    }
    if (!$conf_user['tapeset']) {
        $db->query("INSERT INTO `tape_set` (`id_user`) VALUES (?i)", [$user['id']]);
    }
    if (!$conf_user['notificationset']) {
        $db->query("INSERT INTO `notification_set` (`id_user`) VALUES (?i)", [$user['id']]);
    }
    
    // для web темы
    if ($webbrowser) {
        if (is_dir(H . 'style/themes/' . $user['set_them2'])) {
            $set['set_them'] = $user['set_them2'];
        } else {
            $set_user += ['set_them2' => $set['set_them']];
        }
    } else {
        if (is_dir(H . 'style/themes/' . $user['set_them'])) {
            $set['set_them'] = $user['set_them'];
        } else {
            $set_user += ['set_them' => $set['set_them']];
        }
    }

    // Пишем ip пользователя
    $ip_add  = isset($ip2['add']) ? ip2long($ip2['add']) : 0;
    $ip_cl   = isset($ip2['cl']) ? ip2long($ip2['cl']) : 0;
    $ip_xff  = isset($ip2['xff']) ? ip2long($ip2['xff']) : 0;
    $ua      = isset($ua) ? $ua : 'Нет данных';
    $url     = filter_input(INPUT_SERVER, 'SCRIPT_NAME', FILTER_SANITIZE_URL);
    $set_user += [
              'sess' => $sess,
              'level' => $user['level'],
              'browser' => ($webbrowser == true ? "web" : "wap"),
              'ip' => $ip_add,
              'ip_cl' => $ip_cl,
              'ip_xff' => $ip_xff,
              'ua' => $ua,
              'date_last' => time(),
              'url' => $url
              ];
    /*
    ========================================
    Скрытие новости
    ========================================
    */
    if (isset($_GET['news_read'])) {
        $set_user += ['news_read' => "1"];
        $news_read = true;
    }
    /*
    ========================================
    Смена тем для юзеров папки wap и web
    ========================================
    */
    if (isset($_GET['t'])) {
        if ($webbrowser == 'WEB') {
            $set_t='set_them2';
        } else {
            $set_t='set_them';
        }
    
        $wap = 'default';
        $web = 'web';
    
        if ($_GET['t'] == 'wap') {
            $set_user += [$set_t => $wap];
        } elseif ($_GET['t'] == 'web') {
            $set_user += [$set_t => $web];
        }
        $conf_them = true;
    }
    /*
    ========================================
    Сортировка списка по времени
    ========================================
    */
    if (filter_input(INPUT_GET, 'sort', FILTER_VALIDATE_INT)) {
        $set_user += ['sort' => 1];
        $user['sort'] = 1;
    } else {
        $set_user += ['sort' => 0];
        $user['sort'] = 0;
    }

    $sort = ($user['sort'] == 1?' ASC ':' DESC ');

    // Страницы
    if (isset($user) && $user['sort']  ==  1) {
        $pageEnd = 'end';
    } else {
        $pageEnd = '1';
    }
    // update data user
    if (!empty($set_user)) {
        $db->query(
        'UPDATE `user` SET ?set WHERE `id`=?i',
        [$set_user, $user['id']]
    );
    }
    // Проверяем на сх    ожие ники
//    $collision_q = $db->query(
//    "SELECT `id` FROM `user` WHERE `ip` = ?i AND `ua` = ? AND `date_last` > ?i AND `id` <> ?i",
//                            [$iplong, $ua, (time()-600), $user['id']]
//)->col();
//    if (!empty($collision_q)) {
//        foreach ($collision_q as $collision) {
//            if (!$db->query(
//            "SELECT COUNT(*) FROM `user_collision` WHERE `id_user` = ?i AND `id_user2` = ?i OR `id_user2` = ?i AND `id_user` = ?i",
//                        [$user['id'], $collision, $user['id'], $collision]
//        )->el()) {
//                $db->query(
//                'INSERT INTO `user_collision` (`id_user`, `id_user2`, `type`) VALUES(?i, ?i, ?)',
//                       [$user['id'], $collision, "ip_ua_time"]
//            );
//            }
//        }
//    }
    if (!empty($news_read)) {
        // Оповещаем
        $_SESSION['message'] = "Новость успешно скрыта";
        header("Location: /?");
        exit;
    }
    if (!empty($conf_them)) {
        header('Location: ' . htmlspecialchars_decode($_SERVER['HTTP_REFERER']));
        exit;
    }
/*
========================================
Ответы в комм > v.1.7.4
========================================
*/
    if (!isset($insert)) {
        $insert = null;
    }
    if (filter_input(INPUT_GET, 'response', FILTER_VALIDATE_INT) &&
        $ank_reply = $db->query("SELECT id, nick FROM `user` WHERE `id` = ?i", [$_GET['response']])->row()) {
        $insert = $ank_reply['nick'] . ', ';
        $go_link = '?' . $passgen . '&amp;response=' . $ank_reply['id'];
        $respons = true;
    }

} else {
    // Тема для гостя
    if ($webbrowser) {
        $set['set_them'] = $set['set_them2'];
    }
    
    // Гость
    if ($ip && $ua) {
        if ($db->query(
            "SELECT COUNT(*) FROM `guests` WHERE `ip` = ?i AND `ua` = ?",
                       [$iplong, $ua]
        )->el()) {
            $db->query(
                "UPDATE `guests` SET `date_last` = ?i, `url` = ?, `pereh`=`pereh`+1 WHERE `ip` = ?i AND `ua` = ? LIMIT ?i",
                       [time(), $_SERVER['SCRIPT_NAME'], $iplong, $ua, 1]
            );
        } else {
            $db->query(
                "INSERT INTO `guests` (`ip`, `ua`, `date_aut`, `date_last`, `url`) VALUES (?i, ?, ?i, ?i, ?)",
                       [$iplong, $ua, time(), time(), $_SERVER['SCRIPT_NAME']]
            );
        }
    }
    unset($access);
}
if (!isset($user) || $user['level']  ==  0) {
    // показ ошибок
    error_reporting(0);
    ini_set('display_errors', false);
    if (function_exists('set_time_limit')) {
        // Ставим ограничение на 20 сек
        set_time_limit(20);
    }
}
if (!isset($user) && $set['guest_select']  ==  '1' && !isset($show_all)
    && $_SERVER['PHP_SELF'] != '/index.php' && $_SERVER['PHP_SELF'] != '/user/connect/loginAPI.php') {
    header("Location: /aut.php");
    exit;
}
if (isset($user)) {
    // Продолжаем просмотр файла с меткой 18+
    if (isset($_GET['sess_abuld']) && $_GET['sess_abuld']  ==  1) {
        $_SESSION['abuld'] = 1;
    }
    if (isset($_SESSION['abuld']) && $_SESSION['abuld']  ==  1) {
        $user['abuld'] = 1;
    }
}
/*
========================================
Смена тем для гостей
========================================
*/
if (isset($_GET['t']) && $_GET['t'] == 'wap' && !isset($user)) {
    $_SESSION['guest_theme']='wap';
    header('Location: ' . htmlspecialchars($_SERVER['HTTP_REFERER']));
    exit;
} elseif (isset($_GET['t']) && $_GET['t'] == 'web' && !isset($user)) {
    $_SESSION['guest_theme']='web';
    header('Location: ' . htmlspecialchars($_SERVER['HTTP_REFERER']));
    exit;
}
if (isset($_SESSION['guest_theme']) && $_SESSION['guest_theme'] == 'web' && !isset($user)) {
    $set['set_them'] = 'web';
    $set['set_them2'] = 'web';
} elseif (isset($_SESSION['guest_theme']) && $_SESSION['guest_theme'] == 'wap' && !isset($user)) {
    $set['set_them'] = 'default';
    $set['set_them2'] = 'default';
}

/*
========================================
Панель навигации над полем ввода
========================================
*/
$tPanel = "<div id='comments' class='tpanel'>
<div class='tmenu'><a href='/plugins/smiles/'>Смайлы</a></div>
<div class='tmenu'><a href='/plugins/rules/bb-code.php'>Теги</a></div>
</div>";
/*
========================================
Причины бана
========================================
*/
$pBan[0] = "Другое";
$pBan[1] = "Спам/Реклама";
$pBan[2] = "Мошенничество";
$pBan[3] = "Нецензурная брань";
$pBan[4] = "Клонирование ников";
$pBan[5] = "Подстрекательство, провокация и побуждение к агрессии";
$pBan[6] = "Флуд";
$pBan[7] = "Флейм";
/*
========================================
Раздел бана
========================================
*/
$rBan['all'] = "Весь сайт";
$rBan['notes'] = "Дневники";
$rBan['forum'] = "Форум";
$rBan['files'] = "Файлы";
$rBan['guest'] = "Гостевая";
$rBan['chat'] = "Чат";
$rBan['lib'] = "Библиотека";
$rBan['foto'] = "Фотографии";
/*
========================================
Сообщение в комментариях
========================================
*/
$banMess = '[red]Это сообщение ушло париться вместе с автором в баню![/red]';
if (isset($_POST['msg']) && !isset($user)) {
    echo "Вы не авторизованы!";
    exit;
}
/*
========================================
Валюта
========================================
*/
$sMonet[0] = 'монет';
$sMonet[1] = 'монета';
$sMonet[2] = 'монеты';
// Загрузка остальных плагинов из папки "sys/inc/plugins"
$opdirbase = opendir(H.'sys/inc/plugins');
while ($filebase = readdir($opdirbase)) {
    if (preg_match('#\.php$#i', $filebase)) {
        require_once(H.'sys/inc/plugins/' . $filebase);
    }
}
