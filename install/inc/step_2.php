<?php
$set['title']='Проверка CHMOD';
include_once 'inc/head.php'; // верхняя часть темы оформления
if (isset($_GET['chmod_ok'])) {
    @chmod(H.'install/', 0755);
    @chmod(H.'sys/avatar/', 0755);
    @chmod(H.'sys/dat/', 0755);
    @chmod(H.'sys/forum/files', 0755);
    @chmod(H.'sys/gallery/48/', 0755);
    @chmod(H.'sys/gallery/50/', 0755);
    @chmod(H.'sys/gallery/128/', 0755);
    @chmod(H.'sys/gallery/640/', 0755);
    @chmod(H.'sys/gallery/foto/', 0755);
    @chmod(H.'sys/inc/', 0755);
    @chmod(H.'sys/fnc/', 0755);
    @chmod(H.'sys/obmen/files/', 0755);
    @chmod(H.'sys/obmen/screens/14/', 0755);
    @chmod(H.'sys/obmen/screens/48/', 0755);
    @chmod(H.'sys/obmen/screens/128/', 0755);
    @chmod(H.'sys/update/', 0755);
    @chmod(H.'sys/tmp/', 0755);
    @chmod(H.'style/themes/', 0755);
    @chmod(H.'style/smiles/', 0755);
    @chmod(H.'sys/gift/', 0755);
    msg('Права успешно получены!');
}
echo "<form method='post' action='?chmod_ok'>";
echo "<input type='submit' name='refresh' value='Получить права!' />";
echo "</form>";

include_once H.'sys/inc/chmod_test.php';

if (isset($err)) {
    if (is_array($err)) {
        foreach ($err as $key=>$value) {
            echo "<div class='err'>$value</div>\n";
        }
    } else {
        echo "<div class='err'>$err</div>\n";
    }
} elseif (isset($_GET['step']) && $_GET['step']=='3') {
    $_SESSION['install_step']++;
    header("Location: index.php?$passgen&".SID);
    exit;
}

echo "<hr />\n";

echo "<form method=\"get\" action=\"index.php\">\n";
echo "<input name='gen' value='$passgen' type='hidden' />\n";
echo "<input name=\"step\" value=\"".($_SESSION['install_step']+1)."\" type=\"hidden\" />\n";
echo "<input value=\"".(isset($err)?'Cкрипт не готов к установке':'Продолжить')."\" type=\"submit\"".(isset($err)?' disabled="disabled"':null)." />\n";
echo "</form>\n";

echo "<hr />\n";
echo "<b>Шаг: $_SESSION[install_step]</b>\n";

include_once 'inc/foot.php'; // нижняя часть темы оформления
