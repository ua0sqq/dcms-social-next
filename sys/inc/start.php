<?php
// Exept
register_shutdown_function(function () {// TODO: remove
    $error = error_get_last();
    if ($error && ($error['type'] == E_NOTICE || $error['type'] == E_WARNING || $error['type'] == E_ERROR || $error['type'] == E_PARSE || $error['type'] == E_COMPILE_ERROR)) {
        if (strpos($error['message'], 'Allowed memory size') === 0) {
            ini_set('memory_limit', (intval(ini_get('memory_limit'))+64) . 'M');
            file_put_contents(
                H . 'sys/tmp/user.log',
                            PHP_EOL . 'PHP Fatal: not enough memory in '  .
                             PHP_EOL . $error['file'] . ':' . $error['line'],
                FILE_APPEND
            );
        } else {
            //  echo '<pre>'.(/*H . 'sys/tmp/user.log',*/
        //                     PHP_EOL . 'Exept: '.$error['message'].' in ' .
         //                    PHP_EOL . $error['file'] . ':' . $error['line']/*, FILE_APPEND*/);
        }
        if (!headers_sent()) {
            header($_SERVER['SERVER_PROTOCOL'] . ' ' . '503');
            exit();
            header('Location: /503.html');
        }
    }
});
// end
if (function_exists('error_reporting')) {
    @error_reporting(0);
} // отключаем показ ошибок
// Ставим ограничение для выполнения скрипта на 60 сек
if (function_exists('set_time_limit')) {
    @set_time_limit(60);
}
if (function_exists('ini_set')) {
    ini_set('display_errors', false); // отключаем показ ошибок
    ini_set('register_globals', false); // вырубаем глобальные переменные // TODO: remove
    ini_set('session.use_cookies', true); // используем куки для сессий
    ini_set('session.use_trans_sid', true); // используем url для передачи сессий
    ini_set('arg_separator.output', "&amp;"); // разделитель переменных в url (для соответствия с xml)
}
// принудительно вырубаем глобальные переменные
if (ini_get('register_globals')) {// TODO: remove
  $allowed = array('_ENV' => 1, '_GET' => 1, '_POST' => 1, '_COOKIE' => 1, '_FILES' => 1, '_SERVER' => 1, '_REQUEST' => 1, 'GLOBALS' => 1);
    foreach ($GLOBALS as $key => $value) {
        if (!isset($allowed[$key])) {
            unset($GLOBALS[$key]);
        }
    }
}
list($msec, $sec) = explode(chr(32), microtime()); // время запуска скрипта
$conf['headtime'] = $sec + $msec;
$time = time();
$phpvervion=explode('.', phpversion());
$conf['phpversion']=$phpvervion[0];
$upload_max_filesize=ini_get('upload_max_filesize');
if (preg_match('#([0-9]*)([a-z]*)#i', $upload_max_filesize, $varrs)) {
    if ($varrs[2]=='M') {
        $upload_max_filesize=$varrs[1]*1048576;
    } elseif ($varrs[2]=='K') {
        $upload_max_filesize=$varrs[1]*1024;
    } elseif ($varrs[2]=='G') {
        $upload_max_filesize=$varrs[1]*1024*1048576;
    }
}
function fiera($msg)
{// TODO: remove
    $msg=str_replace("script", "sсript", $msg);
    $msg=str_replace("javаscript:", "javаscript:", $msg);
    if ($_SERVER['PHP_SELF']!='/adm_panel/mysql.php') {
        $msg=addslashes(stripslashes(trim($msg)));
    }
    return $msg;
}
 // Полночь
$ftime = mktime(0, 0, 0);
