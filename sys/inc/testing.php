<?php
echo "Версия DCMS-Social v.$set[dcms_version] $set[dcms_state] ".((!isset($license) || $license==false)?'':'(расширенная)')."<br />\n";
list($php_ver1, $php_ver2, $php_ver3)=explode('.', strtok(strtok(phpversion(), '-'), ' '), 3);
if (version_compare(PHP_VERSION, '5.6', '>')) {
    echo "<span class='on'>Версия PHP: $php_ver1.$php_ver2.$php_ver3 (OK)</span><br />\n";
} else {
    echo "<span class='off'>Версия PHP: $php_ver1.$php_ver2.$php_ver3</span><br />\n";
    $err[]="Тестирование на версии php $php_ver1.$php_ver2.$php_ver3 не осуществялось";
}
/*
if (function_exists('disk_free_space') && function_exists('disk_total_space'))
{
$free_space=disk_free_space(H);
$total_space=disk_total_space(H);
if ($free_space>1024*1024*5)
echo "<span class='on'>Свободно:</span> ".size_file($free_space).' / '.size_file($total_space)."<br />\n";
else
{
echo "<span class='off'>Свободно:</span> ".size_file($free_space).' / '.size_file($total_space)."<br />\n";
$err[]='Мало свободного места на диске';
}
}
*/
if (function_exists('set_time_limit')) {
    echo "<span class='on'>set_time_limit: OK</span><br />\n";
} else {
    echo "<span class='on'>set_time_limit: Запрещено</span><br />\n";
}
if (ini_get('session.use_trans_sid')==true) {
    echo "<span class='on'>session.use_trans_sid: OK</span><br />\n";
} else {
    echo "<span class='off'>session.use_trans_sid: Нет</span><br />\n";
    $err[]='Будет теряться сессия на браузерах без поддержки COOKIE';
    $err[]='Добавьте в корневой .htaccess строку <b>php_value session.use_trans_sid 1</b>';
}
if (ini_get('magic_quotes_gpc')==0) {
    echo "<span class='on'>magic_quotes_gpc: 0 (OK)</span><br />\n";
} else {
    echo "<span class='off'>magic_quotes_gpc: Включено</span><br />\n";
    $err[]='Включено экранирование кавычек';
    $err[]='Добавьте в корневой .htaccess строку <b>php_value magic_quotes_gpc 0</b>';
}
if (ini_get('arg_separator.output')=='&amp;') {
    echo "<span class='on'>arg_separator.output: &amp;amp; (OK)</span><br />\n";
} else {
    echo "<span class='off'>arg_separator.output: ".output_text(ini_get('arg_separator.output'))."</span><br />\n";
    $err[]='Возможно появление ошибки xml';
    $err[]='Добавьте в корневой .htaccess строку <b>php_value arg_separator.output &amp;amp;</b>';
}
if (file_exists(H.'install/mod_rewrite_test.php')) {
    if (@trim(file_get_contents("http://$_SERVER[HTTP_HOST]/install/mod_rewrite.test"))=='mod_rewrite-ok') {
        echo "<span class='on'>mod_rewrite: OK</span><br />\n";
    } elseif (function_exists('apache_get_modules')) {
        $apache_mod=@apache_get_modules();
        if (array_search('mod_rewrite', $apache_mod)) {
            echo "<span class='on'>mod_rewrite: OK</span><br />\n";
        } else {
            echo "<span class='off'>mod_rewrite: Нет</span><br />\n";
            $err[]='Необходима поддержка mod_rewrite';
        }
    } else {
        echo "<span class='off'>mod_rewrite: Нет</span><br />\n";
        $err[]='Необходима поддержка mod_rewrite';
    }
} elseif (function_exists('apache_get_modules')) {
    $apache_mod=@apache_get_modules();
    if (array_search('mod_rewrite', $apache_mod)) {
        echo "<span class='on'>mod_rewrite: OK</span><br />\n";
    } else {
        echo "<span class='off'>mod_rewrite: Нет</span><br />\n";
        $err[]='Необходима поддержка mod_rewrite';
    }
} else {
    echo "<span class='off'>mod_rewrite: Нет данных</span><br />\n";
}
if (function_exists('imagecreatefromstring') && function_exists('gd_info')) {
    $gdinfo=gd_info();
    echo "<span class='on'>GD: ".$gdinfo['GD Version']." OK</span><br />\n";
} else {
    echo "<span class='off'>GD: Нет</span><br />\n";
    $err[]='GD необходима для корректной работы движка';
}
if (function_exists('mysqli_info')) {
    echo "<span class='on'>MySQL: OK</span><br />\n";
} else {
    echo "<span class='off'>MySQL: Нет</span><br />\n";
    $err[]='Без MySQL работа не возможна';
}
if (function_exists('iconv')) {
    echo "<span class='on'>Iconv: OK</span><br />\n";
} else {
    echo "<span class='off'>Iconv: Нет</span><br />\n";
    $err[]='Без Iconv работа не возможна';
}
if (class_exists('ffmpeg_movie')) {
    echo "<span class='on'>FFmpeg: OK</span><br />\n";
} else {
    echo "<span class='on'>FFmpeg: Нет</span><br />\n";
    echo "* Без FFmpeg автоматическое создание скриношотов к видео недоступно<br />\n";
}
if (ini_get('register_globals')==false) {
    echo "<span class='on'>register_globals off: OK</span><br />\n";
} else {
    echo "<span class='off'>register_globals on: !!!</span><br />\n";
    $err[]='register_globals включен. Грубое нарушение безопасности';
}
if (function_exists('mcrypt_cbc')) {
    echo "<span class='on'>Шифрование COOKIE: OK</span><br />\n";
} else {
    echo "<span class='on'>Шифрование COOKIE: нет</span><br />\n";
    echo "* mcrypt не доступен<br />\n";
}
