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

user_access('adm_ip_edit', null, 'index.php?'.SID);
adm_check();
$opsos=null;

$set['title']='Добавление оператора';
include_once H . 'sys/inc/thead.php';
title();

if (isset($_POST['min']) && isset($_POST['max']) && isset($_POST['opsos'])) {
    if (!preg_match("#^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$#", $_POST['min'])) {
        $err='Неверный формат IP';
    }
    if (!preg_match("#^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$#", $_POST['max'])) {
        $err='Неверный формат IP';
    }
    if ($_POST['opsos']==null) {
        $err='Введите название оператора';
    }
    $min=ip2long($_POST['min']);
    $max=ip2long($_POST['max']);
    $opsos=trim($_POST['opsos']);
    $db->query(
    "INSERT INTO `opsos` (`min`, `max`, `opsos`) values(?i, ?i, ?)",
           [$min, $max, $opsos]
);
    msg('Диапазон успешно добавлен');
}
if (isset($_GET['delmin'])  && isset($_GET['delmax'])
    && $db->query(
        "SELECT COUNT(*) FROM `opsos` WHERE `min`=?i AND `max`=?i",
            [$_GET['delmin'], $_GET['delmax']]
    )->el()) {
    $db->query(
    "DELETE FROM `opsos` WHERE `min`=?i AND `max`=?i",
            [$_GET['delmin'], $_GET['delmax']]
);
    $db->query("OPTIMIZE TABLE `opsos`");
    msg('Диапазон успешно удален');
}

err();
aut();

$k_post=$db->query("SELECT COUNT(*) FROM `opsos`")->el();
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
echo "<table class='post'>\n";
if ($k_post==0) {
    echo "   <tr>\n";
    echo "  <td class='p_t'>\n";
    echo "Нет операторов\n";
    echo "  </td>\n";
    echo "   </tr>\n";
}
$q=$db->query("SELECT * FROM `opsos` ORDER BY `opsos` ASC LIMIT ?i, ?i", [$start, $set['p_str']]);
while ($post = $q->row()) {
    echo "   <tr>\n";
    echo "  <td class='p_t'>\n";
    echo long2ip($post['min']).' - '.long2ip($post['max']);
    echo "  </td>\n";
    echo "   </tr>\n";
    echo "   <tr>\n";
    echo "  <td class='p_m'>\n";
    echo "$post[opsos]<br />\n";
    echo "<a href=\"?page=$page&amp;delmin=$post[min]&amp;delmax=$post[max]\">Удалить</a><br />\n";
    echo "  </td>\n";
    echo "   </tr>\n";
}
echo "</table>\n";
if ($k_page>1) {
    str('?', $k_page, $page);
} // Вывод страниц
echo "<form method=\"post\" action=\"\">\n";
echo "Начальный IP адрес:<br />\n<input name=\"min\" size=\"16\"  value=\"\" type=\"text\" /><br />\n";
echo "Завершающий IP:<br />\n<input name=\"max\" size=\"16\" value=\"\" type=\"text\" /><br />\n";
echo "Оператор:<br />\n<input name=\"opsos\" size=\"16\" value=\"$opsos\" type=\"text\" /><br />\n";
echo "<input value=\"Добавить\" type=\"submit\" />\n";
echo "</form>\n";
if (user_access('adm_panel_show')) {
    echo "<div class='foot'>\n";
    echo "&laquo;<a href='/adm_panel/'>В админку</a><br />\n";
    echo "</div>\n";
}
include_once H . 'sys/inc/tfoot.php';
