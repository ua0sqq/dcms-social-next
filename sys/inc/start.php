<?php
// path document root
define('H', $_SERVER['DOCUMENT_ROOT'].'/');
// timestamp 10 minutes ago
define('TIME_600', time() - 600);
// start of the day
define('START_DAY', mktime(0, 0, 0));

// отключаем показ ошибок
if (function_exists('error_reporting')) {
    error_reporting(0);
} 

if (function_exists('ini_set')) {
    ini_set('display_errors', false); // отключаем показ ошибок
    ini_set('session.use_cookies', true); // используем куки для сессий
    ini_set('session.use_trans_sid', true); // используем url для передачи сессий
    ini_set('arg_separator.output', "&amp;"); // разделитель переменных в url (для соответствия с xml)
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

$num = 0;
