<?php
include_once '../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/adm_check.php';
include_once H . 'sys/inc/user.php';

user_access('adm_mysql', null, 'index.php?'.SID);
adm_check();

$set['title']='MySQL запрос';
include_once H . 'sys/inc/thead.php';
title();

if (isset($_GET['set']) && $_GET['set']=='set' && isset($_POST['query'])) {
    $sql=trim($_POST['query']);
    if ($conf['phpversion']>=5) {
        include_once H.'sys/inc/sql_parser.php';
        // при помощи парсера запросы разбиваются точнее, но работает это только в php5
        $sql=SQLParser::getQueries($sql);
    } else {
        $sql = preg_split('/;(\n|\r)*/', $sql);
    }
    $k_z=0;
    $k_z_ok=0;

    for ($i=0;$i<count($sql);$i++) {
        if ($sql[$i]!='') {
            $k_z++;
            try {
                $db->query($sql[$i]);
                $k_z_ok++;
            } catch (go\DB\Exceptions\Query $e) {
                echo '<div class="foot">';
                echo '<ol style="overflow-x: auto;font-family: monospace;font-size: small;">';
                echo '<li><span style="color: #8F3504;">SQL-query: '.$e->getQuery().'</span></li>'."\n";
                echo '<li><span style="color: red;">Error description: '.$e->getError()."</span></li>\n";
                echo '<li>Error code: '.$e->getErrorCode().'</li>';
                echo '</ol>';
                echo '</div>'."\n";
            }
        }
    }
    if ($k_z_ok>0) {
        msg("Выполнено $k_z_ok запросов из $k_z");
    } else {
        $err = "Пустые параметры";
    }
    admin_log('Админка', 'MySQL', "Выполнено $k_z_ok запрос(ов)");
}
err();
aut();
echo "<form method=\"post\" action=\"mysql.php?set=set\">\n";
echo "<textarea name=\"query\" ></textarea><br />\n";
echo "<input value=\"Выполнить\" type=\"submit\" />\n";
echo "</form>\n";
if (user_access('adm_panel_show')) {
    echo "<div class='foot'>\n";
    echo "&laquo;<a href='/adm_panel/'>В админку</a><br />\n";
    echo "&laquo;<a href='tables.php'>Залить файлом</a><br />\n";
    echo "</div>\n";
}
include_once H . 'sys/inc/tfoot.php';
