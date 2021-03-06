<?php
function br($msg, $br='<br />')
{
    return preg_replace("~((<br( ?/?)>)|\n|\r)+~i", $br, $msg);
}
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
function output_text($str, $br=true, $html=true, $smiles=true, $links=true, $bbcode=true)
{
    if ($html==true) {
        $str=htmlentities($str, ENT_QUOTES, 'UTF-8');
    }
    if ($br==true) {
        $str=br($str);
        $str=esc($str);
    } else {
        $str=esc($str);
    }
    return $str;
}
function msg($msg)
{
    echo "<div class='msg'>$msg</div>\n";
}
function passgen($k_simb=8, $types=3)
{
    $password="";
    $small="abcdefghijklmnopqrstuvwxyz";
    $large="ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $numbers="1234567890";
    mt_srand((double)microtime()*1000000);
    for ($i=0; $i<$k_simb; $i++) {
        $type=mt_rand(1, min($types, 3));
        switch ($type) {
            case 3:
                $password.=$large[mt_rand(0, 25)];
            break;
            case 2:
                $password.=$small[mt_rand(0, 25)];
            break;
            case 1:
                $password.=$numbers[mt_rand(0, 9)];
            break;
        }
    }
    return $password;
}
$passgen = passgen(8);
// сохранение настроек системы
function save_settings($set)
{
    unset($set['web']);
    if ($fopen=@fopen(H.'sys/dat/settings_6.2.dat', 'w')) {
        @fputs($fopen, serialize($set));
        @fclose($fopen);
        @chmod(H.'sys/dat/settings_6.2.dat', 0777);
        return true;
    } else {
        return false;
    }
}
// рекурсивное удаление папки
function delete_dir($dir)
{
    return; // TODO: ???
    if (is_dir($dir)) {
        $od=opendir($dir);
        while ($rd=readdir($od)) {
            if ($rd == '.' || $rd == '..') {
                continue;
            }
            if (is_dir("$dir/$rd")) {
                @chmod("$dir/$rd", 0777);
                delete_dir("$dir/$rd");
            } else {
                @chmod("$dir/$rd", 0777);
                @unlink("$dir/$rd");
            }
        }
        closedir($od);
        @chmod("$dir", 0777);
        return @rmdir("$dir");
    } else {
        @chmod("$dir", 0777);
        @unlink("$dir");
    }
}
