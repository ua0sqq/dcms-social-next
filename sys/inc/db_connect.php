<?php
// TODO: ???
require_once $_SERVER['DOCUMENT_ROOT'] . '/sys/library/autoload.php';
use go\DB\DB;

//require_once $_SERVER['DOCUMENT_ROOT'] . '/sys/library/goDB/autoload.php';
    $set['charset_names'] = 'utf8';
    $params = array(
                '_adapter' => 'mysql',
                'host'     => $set['mysql_host'],
                'username' => $set['mysql_user'],
                'password' => $set['mysql_pass'],
                'dbname'   => $set['mysql_db_name'],
                'charset'  => $set['charset_names'],
                '_debug'   => false,
                '_prefix'  => '',
            );
    // Mysql connecting, for depricate php modules
    // Настоятельно рекомендуется переделать свои модули,
    // и не пользоваться этим соединением!
    if (extension_loaded('mysql')) {
        @mysql_connect($set['mysql_host'], $set['mysql_user'], $set['mysql_pass'])
        or die('Невозможно подключиться к базе данных');
        mysql_select_db($set['mysql_db_name'])
        or die('Не найдена база : ' . $set['mysql_db_name']);
        if ($set['charset_names'] != null) {
            mysql_query('SET NAMES ' . $set['charset_names']);
        }
    }
    // e. g. for sqlite data base connect
/*
 *$params = [
           '_adapter' => 'sqlite',
           'filename' => $_SERVER['DOCUMENT_ROOT'] . '/sys/dat/orm.db',
           'mysql_quot' => false,
           '_debug' => false,
           ];
*/
    //\go\DB\autoloadRegister();

    $db = DB::create($params);

    go\DB\Storage::getInstance()->create($params);

// e. g. run debug $db->setDebug('mydebug');
function mydebug($query, $duration, $info)
{
    $trace = debug_backtrace();
    echo '<pre style="font-style: oblique; font-size: 11px;"><p style="color:#B3460B;">' .
    $trace[4]['object']->query_number . '-'.$query.' ' . round($duration, 5) .
    '<p> ' . $trace[4]['file'] . ' ' . $trace[4]['line'] . '</p></pre>';
}
//$db->setDebug('mydebug');
// оптимизация всех таблиц
function db_optimize()
{
    time_limit(20);// Ставим ограничение на 20 секунд
    $tab = $db->query('SHOW TABLE STATUS') ;
    while ($tables = $tab->row()) {
        if ($tables['Engine'] == 'MyISAM' && $tables['Data_free'] > '0') {
            $db->query('OPTIMIZE TABLE `' . $tables['Name'] . '`');
        }
    }
}
