<?php
/**
 * & CMS Name :: DCMS-Social
 * & Author   :: Alexandr Andrushkin
 * & Contacts :: ICQ 587863132
 * & Site     :: http://dcms-social.ru
 */
include_once 'sys/inc/start.php';
include_once 'sys/inc/compress.php';
include_once 'sys/inc/sess.php';
include_once 'sys/inc/settings.php';
include_once 'sys/inc/db_connect.php';
include_once 'sys/inc/ipua.php';
include_once 'sys/inc/fnc.php';
include_once 'sys/inc/user.php';

only_reg();

$set['title'] = 'Личный кабинет';
include_once 'sys/inc/thead.php';
title();
aut();

if (isset($_GET['login']) && isset($_GET['pass'])) {
    echo '<div class="mess">'."\n";
    echo 'Если Ваш браузер не поддерживает Cookie, Вы можете создать закладку для автовхода<br />'."\n";
    echo '<input type="text" value="http://' . text($_SERVER['SERVER_NAME']) . '/login.php?id=' . $user['id'] . '&amp;pass=' . text($_GET['pass']) . '" /><br />'."\n";
    echo '</div>'."\n";
}
?>
<div class="main_menu" id="umenu_razd">
	Мой профиль
</div>
<div class="main" id="umenu">
	<img src='/style/my_menu/ank.png' alt='' /> <a href='/info.php'>Моя страничка</a><br />
</div>
<div class="main" id="umenu">
	<img src='/style/my_menu/ank.png' alt='' /> <a href='/user/info/anketa.php'>Анкета</a> [<a href='user/info/edit.php'>ред.</a>]<br />
</div>
<div class="main" id="umenu">
	<img src='/style/my_menu/avatar.png' alt='' /> <a href='/avatar.php'>Мой аватар</a><br />
</div>
<?php
// Загрузка остальных плагинов из папки "sys/add/umenu"
$opdirbase = opendir(H.'sys/add/umenu');
while ($filebase = readdir($opdirbase)) {
    if (preg_match('#\.php$#i', $filebase)) {
        echo '<div class="main" id="umenu">'."\n";
        include_once(H.'sys/add/umenu/' . $filebase);
        echo '</div>'."\n";
    }
}
?>
<div class="main_menu" id="umenu_razd">
	Мои настройки
</div>
<div class="main" id="umenu">
	<img src="/style/my_menu/set.png" alt="" /> <a href="/user/info/settings.php">Общие настройки</a><br />
</div>
<div class="main" id="umenu">
	<img src="/style/my_menu/secure.png" alt="" /> <a href="/secure.php">Сменить пароль</a><br />
</div>
<div class="main" id="umenu">
	<img src="/style/my_menu/rules.png" alt="" /> <a href="/rules.php">Правила</a><br />
</div>
<?php
// Админ права
if (user_access('adm_panel_show')) {
    echo '<div class="main_menu" id="umenu_razd">'."\n".'Админ-Панель'."\n".'</div>'."\n";
    
    echo '<div class="main" id="umenu">'."\n";
    echo '<img src="/style/my_menu/adm_panel.png" alt="" /> <a href="/adm_panel/">Админка</a><br />'."\n";
    echo '</div>'."\n";
}
// Только для wap
if ($set['web'] == false) {
    echo '<div class="main" id="umenu">'."\n";
    echo '<a href="/exit.php"><img src="/style/icons/delete.gif" /> Выход из под ' . user::nick($user['id'], 0) . '</a><br />'."\n";
    echo '</div>'."\n";
}

include_once 'sys/inc/tfoot.php';
exit;

?>