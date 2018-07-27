<?php
// псевдонимы функций temporary
function my_esc($value) {
    $search = array("\\", "\x00", "\n", "\r", "'", '"', "\x1a", "?");
    $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z", "??");
    return str_replace($search, $replace, $value);
}

function __($str)
{
    return $str;
}
// для php 4 (альтернатива file_put_contents)
if (!function_exists('file_put_contents')) {
    function file_put_contents($file, $data)
    {
        $f=@fopen($file, 'w');
        return @fwrite($f, $data);
        @fclose($f);
    }
}
// Защита от частых запросов с одного IP
if ($set['antidos']) {
    $antidos[]=array('time'=>$time);
    $k_loads=0;
    if (is_file(H.'sys/tmp/antidos_'.$iplong.'.dat')) {
        $antidos_dat=unserialize(file_get_contents(H.'sys/tmp/antidos_'.$iplong.'.dat'));
        for ($i=0;$i<150 && $i<sizeof($antidos_dat);$i++) {
            if ($antidos_dat[$i]['time']>$time-5) {
                $k_loads++;
                $antidos[]=$antidos_dat[$i];
            }
        }
    }
    if ($k_loads>100) {
        if (!$db->query("SELECT COUNT( * ) FROM `ban_ip` WHERE `min` <= ?i AND `max` >= ?i",
                        [$iplong, $iplong])->el()) {
            $db->query("INSERT INTO `ban_ip` (`min`, `max`) values(?i , ?i)",
                       [$iplong, $iplong]);
        }
    }
    @file_put_contents(H.'sys/tmp/antidos_'.$iplong.'.dat', serialize($antidos));
    @chmod(H.'sys/tmp/antidos_'.$iplong.'.dat', 0644);
}
// антимат сделает автоматическое предупреждение, а затем бан
function antimat($str)
{
    global $user,$time,$set;
    if ($set['antimat']) {
        $antimat=&$_SESSION['antimat'];
        include_once H.'sys/inc/censure.php';
        $censure=censure($str);
        if ($censure) {
            $antimat[$censure]=$time;
            // если сделано больше 3-х предупреждений
            if (count($antimat)>3 && isset($user) && $user['level']) { 
                $prich="Обнаружен мат: $censure";
                $timeban=$time+60*60; // бан на час
                $db->query("INSERT INTO `ban` (`id_user`, `id_ban`, `prich`, `time`) VALUES (?i, ?i, ?, ?i)",
                           [$user['id'], 0, $prich, $timeban]);
                admin_log('Пользователи', 'Бан', "Бан пользователя '[url=/amd_panel/ban.php?id=$user[id]]$user[nick][/url]' (id#$user[id]) до ".vremja($timeban)." по причине '$prich'");
                header('Location: /ban.php?'.SID);
                exit;
            }
            return $censure;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
// рекурсивное удаление папки
function delete_dir($dir)
{
    if (is_dir($dir)) {
        $od=opendir($dir);
        while ($rd=readdir($od)) {
            if ($rd == '.' || $rd == '..') {
                continue;
            }
            if (is_dir($dir . '/' . $rd)) {
                delete_dir($dir . '/' . $rd);
            } else {
                if (is_file($dir . '/' . $rd)) {
                    unlink($dir . '/' . $rd);
                }
            }
        }
        closedir($od);
        return rmdir($dir);
    } else {
        if (is_file($dir)) {
            unlink($dir);
        }
    }
}
// очистка временной папки
if (!isset($hard_process)) {
    $cron = $db->query('SELECT * FROM cron')->vars();
    if (!isset($cron['clear_tmp_dir'])) {
        $db->query('INSERT INTO `cron` (`id`, `time`) VALUES (?, ?i)',
                    ['clear_tmp_dir', time()]);
    } else
    if (/*isset($cron['clear_tmp_dir']) && */$cron['clear_tmp_dir'] < (time() - 60 * 60 * 24)) {
        $hard_process = true;
        $db->query('UPDATE `cron` SET `time`=?i WHERE `id`=?',
                    [time(), 'clear_tmp_dir']);
        $od = opendir(H . 'sys/tmp/');
        while ($rd = readdir($od)) {
            if (!preg_match('#^\.#', $rd) && filectime(H . 'sys/tmp/' . $rd) < time() - 60 * 60 * 24) {
                delete_dir(H . 'sys/tmp/' . $rd);
            }
        }
        closedir($od);
    }
    if (!isset($cron['visit'])) {
        $db->query('INSERT INTO `cron` (`id`, `time`) VALUES (?, ?i)',
                   ['visit', time()]);
    } else
    if (/*isset($cron['visit']) && */$cron['visit'] < (time() - 60 * 60 * 24)) {
        // Ставим ограничение на 10 минут
        if (function_exists('set_time_limit')) {
            set_time_limit(600);
        }
        $last_day = mktime(0, 0, 0, date('m'), date('d') - 1); // начало вчерашних суток
        $today_time = mktime(0, 0, 0); // начало сегодняшних суток
        if (!$db->query('SELECT COUNT( * ) FROM `visit_everyday` WHERE `time`=?i',
                        [$last_day])->el()) {
            $hard_process = true;
            // записываем общие данные за вчерашние сутки в отдельную таблицу
            $data = [$today_time, $today_time, $today_time, $last_day];try{
            $db->query('INSERT INTO `visit_everyday` (`host` , `host_ip_ua`, `hit`, `time`) VALUES ((
SELECT COUNT(DISTINCT `ip`) FROM `visit_today` WHERE `time` < ?i),(
SELECT COUNT(DISTINCT (`ip` || `ua`)) FROM `visit_today` WHERE `time` < ?i),(
SELECT COUNT( * ) FROM `visit_today` WHERE `time` < ?i), ?i)', $data);
                } catch (go\DB\Exceptions\Query $e) {
                echo '<div class="foot">';
                echo '<ol style="overflow-x: auto;font-family: monospace;font-size: small;">';
                echo '<li><span style="color: #8F3504;">SQL-query: '.$e->getQuery().'</span></li>'."\n";
                echo '<li><span style="color: red;">Error description: '.$e->getError()."</span></li>\n";
                echo '<li>Error code: '.$e->getErrorCode().'</li>';
                echo '</ol>';
                echo '</div>'."\n";
            }
            $db->query('DELETE FROM `visit_today` WHERE `time` < ?i',
                       [$today_time]);
            unset($data);
        }
    }
    if (!isset($cron['everyday'])) {
        $db->query('INSERT INTO `cron` (`id`, `time`) VALUES (?, ?i)',
                 ['everyday', time()]);
    } else
    if (/*isset($cron['everyday']) && */$cron['everyday'] < (time() - 60 * 60 * 24)) {
        $hard_process = true;
        // Ставим ограничение на 10 минут
        if (function_exists('set_time_limit')) {
            set_time_limit(600);
        }
        $db->query('UPDATE `cron` SET `time`=?i WHERE `id`=?',
                   [time(), 'everyday']);
        // удаление гостей старше 10 минут
        $db->query('DELETE FROM `guests` WHERE `date_last` < ?i',
                   [(time() - 600)]);
        // удаление старых постов в чате
        $db->query('DELETE FROM `chat_post` WHERE `time` < ?i',
                   [(time() - 60 * 60 * 24)]);
        // удаление неактивированных аккаунтов
        $db->query('DELETE FROM `user` WHERE `activation` != null AND `date_reg` < ?i',
                   [(time() - 60 * 60 * 24)]);
        // удаляем все контакты, помеченные на удаление более месяца назад
        $qd = $db->query('SELECT * FROM `users_konts` WHERE `type`=?string AND `time`<?i',
                         ['deleted',  (time() - 60 * 60 * 24 * 30)]);
        while ($deleted = $qd->row()) {
            $db->query('DELETE FROM `users_konts` WHERE `id_user`=?i AND `id_kont`=?i',
                       [$deleted['id_user'], $deleted['id_kont']]);
            if (!$db->query('SELECT COUNT( * ) FROM `users_konts` WHERE `id_kont`=?i AND `id_user`=?i',
                            [$deleted['id_user'], $deleted['id_kont']])->el()) {
                // если юзер не находится в контакте у другого, то удаляем и все сообщения
                $db->query('DELETE FROM `mail` WHERE `id_user`=?i AND `id_kont`=?i OR `id_kont`=?i AND `id_user`=?i',
                           [$deleted['id_user'], $deleted['id_kont'], $deleted['id_user'], $deleted['id_kont']]);
            }
        }
        // оптимизация таблиц
        $db->query('OPTIMIZE TABLE `guests`, `chat_post`, `user`, `users_konts`, `mail`');
    }
}
// запись о переходах на сайт
if ($reff = filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_SANITIZE_URL)) {
    $ref = parse_url($reff);
    if (isset($ref['host']) && $ref['host'] != filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_DEFAULT)) {
        $_SESSION['http_referer'] = $ref['host'];
    }
}
// переносы строк
function br($msg, $br='<br />')
{
    return preg_replace("#((<br( ?/?)>)|\n|\r)+#i", $br, $msg);
}
// Вырезает все нечитаемые символы
function esc($text, $br=null)
{ 
    if ($br!=null) {
        for ($i=0;$i<=31;$i++) {
            $text=str_replace(chr($i), null, $text);
        }
    } else {
        for ($i=0;$i<10;$i++) {
            $text=str_replace(chr($i), null, $text);
        }
        for ($i=11;$i<20;$i++) {
            $text=str_replace(chr($i), null, $text);
        }
        for ($i=21;$i<=31;$i++) {
            $text=str_replace(chr($i), null, $text);
        }
    }
    return $text;
}
// получаем данные пользователя и уровень прав (+ кеширование)
function get_user($user_id=0)
{
    if ($user_id == 0) {
        // бот
        $ank2['id']=0;
        $ank2['nick']='Система';
        $ank2['level']=999;
        $ank2['pol']=1;
        $ank2['date_last']=time();
        $ank2['group_name']='Системный робот';
        $ank2['ank_o_sebe']='Создан для уведомлений';
        return $ank2;
    } else {
        // переменная не удаляется после вызова функции
        static $users; 
        //$user_id=intval($user_id);
        $users[0]=false;
        if (!isset($users[$user_id])) {
            if (go\DB\query('SELECT COUNT( * ) FROM `user` WHERE `id`=?i', [$user_id])->el()) {
                $users[$user_id] = go\DB\query('SELECT `u` . *, `gr`.`name` AS `group_name`, `gr`.`level` AS `level` FROM `user` u
LEFT JOIN `user_group` gr ON `gr`.`id`=`u`.`group_access` WHERE `u`.`id`=?i', [$user_id])->row();

                if ($users[$user_id]['group_name'] == null) {
                    $users[$user_id]['level'] = '0';
                    $users[$user_id]['group_name']='Пользователь';
                }
                
            } else {
                $users[$user_id] = false;
                header('Location: /err.php?err=404');
            }
        }
        return $users[$user_id];
    }
}
// определение оператора
function opsos($ips = null)
{
    global $ip;
    if ($ips == null) {
        $ips = $ip;
    }
    if ($opsos = go\DB\query('SELECT `opsos` FROM `opsos` WHERE  ?i BETWEEN `min` AND `max`',
                            [$ips])->el()) {
        return stripcslashes(htmlspecialchars($opsos));
    }
    return false;
}
// вывод времени
function vremja($time=null)
{
    global $user;
    if ($time==null) {
        $time=time();
    }
    if (isset($user)) {
        $time=$time+$user['set_timesdvig']*60*60;
    }
    $timep="".date("j M Y в H:i", $time)."";
    $time_p[0]=date("j n Y", $time);
    $time_p[1]=date("H:i", $time);
    if ($time_p[0]==date("j n Y")) {
        $timep=date("H:i:s", $time);
    }
    if (isset($user)) {
        if ($time_p[0]==date("j n Y", time()+$user['set_timesdvig']*60*60)) {
            $timep=date("H:i:s", $time);
        }
        if ($time_p[0]==date("j n Y", time()-60*60*(24-$user['set_timesdvig']))) {
            $timep="Вчера в $time_p[1]";
        }
    } else {
        if ($time_p[0]==date("j n Y")) {
            $timep=date("H:i:s", $time);
        }
        if ($time_p[0]==date("j n Y", time()-60*60*24)) {
            $timep="Вчера в $time_p[1]";
        }
    }
    $timep=str_replace("Jan", "Янв", $timep);
    $timep=str_replace("Feb", "Фев", $timep);
    $timep=str_replace("Mar", "Марта", $timep);
    $timep=str_replace("May", "Мая", $timep);
    $timep=str_replace("Apr", "Апр", $timep);
    $timep=str_replace("Jun", "Июня", $timep);
    $timep=str_replace("Jul", "Июля", $timep);
    $timep=str_replace("Aug", "Авг", $timep);
    $timep=str_replace("Sep", "Сент", $timep);
    $timep=str_replace("Oct", "Окт", $timep);
    $timep=str_replace("Nov", "Ноября", $timep);
    $timep=str_replace("Dec", "Дек", $timep);
    return $timep;
}
// только для зарегистрированых
function only_reg($link = null)
{
    global $user;
    if (!isset($user)) {
        if ($link==null) {
            $link='/index.php?'.SID;
        }
        header("Location: $link");
        exit;
    }
}
// только для незарегистрированых
function only_unreg($link = null)
{
    global $user;
    if (isset($user)) {
        if ($link==null) {
            $link='/index.php?'.SID;
        }
        header("Location: $link");
        exit;
    }
}
// только для тех, у кого уровень доступа больше или равен $level
function only_level($level=0, $link = null)
{
    global $user;
    if (!isset($user) || $user['level']<$level) {
        if ($link==null) {
            $link='/index.php?'.SID;
        }
        header("Location: $link");
        exit;
    }
}
// Вывод предупреждений
function err()
{
    global $err;
    if (isset($err)) {
        if (is_array($err)) {
            foreach ($err as $key=>$value) {
                echo "<div class='err'>$value</div>\n";
            }
        } else {
            echo "<div class='err'>$err</div>\n";
        }
    }
}
// вывод сообщений
function msg($msg)
{
    echo "<div class='msg'>$msg</div>\n";
}
// отправка запланированных писем
$q=$db->query("SELECT * FROM `mail_to_send` LIMIT 1")->assoc();
if (count($q)) {
    foreach ($q as $mail);
    $adds="From: \"admin@$_SERVER[HTTP_HOST]\" <admin@$_SERVER[HTTP_HOST]>\n";
    $adds .= "Content-Type: text/html; charset=utf-8\n";
    mail($mail['mail'], '=?utf-8?B?' . base64_encode($mail['them']) . '?=', $mail['msg'], $adds);
    $db->query("DELETE FROM `mail_to_send` WHERE `id`=?i",
               [$mail['id']]);
    $db->query('OPTIMIZE TABLE  `mail_to_send`;');
}
// сохранение настроек системы
function save_settings($set)
{
    unset($set['web']);
    if ($fopen=@fopen(H.'sys/dat/settings_6.2.dat', 'w')) {
        @fputs($fopen, serialize($set));
        @fclose($fopen);
        return true;
    } else {
        return false;
    }
}
// запись действий администрации
function admin_log($mod, $act, $opis)
{
    global $user;
    $sql = go\DB\query('SELECT * FROM `admin_log_mod` WHERE `name`=? LIMIT ?i', [$mod, 1])->row();
    if (!$sql['id']) {
        $id_mod = go\DB\query('INSERT INTO `admin_log_mod` (`name`) VALUES (?)', [$mod])->id();
    } else {
        $id_mod = $sql['id'];
    }

    $sqls = go\DB\query('SELECT * FROM `admin_log_act` WHERE `name`=? AND `id_mod`=?i LIMIT ?i', [$act, $id_mod, 1])->row();
    if (!$sqls['id']) {
        $id_act = go\DB\query('INSERT INTO `admin_log_act` (`name`, `id_mod`) VALUES (?, ?i)', [$act, $id_mod])->id();
    } else {
        $id_act =  $sqls['id'];
    }
    go\DB\query('INSERT INTO `admin_log` (`time`, `id_user`, `mod`, `act`, `opis`) VALUES (?i, ?i, ?i, ?i, ?)',
                    [time(), $user['id'], $id_mod, $id_act, $opis]);
}
// LoginAPI
if (isset($_POST['token'])) {
    $s = file_get_contents('http://ulogin.ru/token.php?token=' . $_POST['token'] . '&host=' . $_SERVER['HTTP_HOST']);
    $_POST['loginAPI'] = true;
}
// Загрузка остальных функций из папки "sys/fnc"
$opdirbase = opendir(H.'sys/fnc');
while ($filebase = readdir($opdirbase)) {
    if (preg_match('#\.php$#i', $filebase)) {
        include_once(H.'sys/fnc/'.$filebase);
    }
}
// запись о посещении
if (isset($_SERVER['HTTP_USER_AGENT'])) {
    $bot = $_SERVER['HTTP_USER_AGENT'];
} else {
    $bot = 'Нет данных';
}
$db->query("INSERT INTO `visit_today` (`ip` , `ua`, `time`) VALUES (?i, ?, ?i)",
           [$iplong, $bot, $time]);
