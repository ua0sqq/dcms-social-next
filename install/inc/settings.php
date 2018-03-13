<?php

if (isset($_SESSION['install_step']) && $_SESSION['install_step'] < 4 && file_exists(H.'sys/dat/settings_6.2.dat')) {
    echo 'Для продолжения установки необходимо удалить файл <b>sys/dat/settings_6.2.dat</b>';
    exit;
}
if (!($set=@parse_ini_file(H.'sys/dat/default.ini', false))) {
    echo 'Не найден файл конфигурации';
    exit;
}
$tmp_set=$set;
