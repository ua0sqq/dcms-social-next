<?php
$set['title']='Проверка платформы';
include_once 'inc/head.php'; // верхняя часть темы оформления

echo "<form method='post' action='?".passgen()."'>";
echo "<input type='submit' name='refresh' value='Обновить' />";
echo "</form>";

include_once H.'sys/inc/testing.php';

if (isset($err)) {
    if (is_array($err)) {
        foreach ($err as $key=>$value) {
            echo "<div class='err'>$value</div>\n";
        }
    } else {
        echo "<div class='err'>$err</div>\n";
    }
} elseif (isset($_GET['step']) && $_GET['step']=='2') {
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
