<?php
include_once '../../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/shif.php';
include_once H . 'sys/inc/user.php';

$set['title']='Безопасность';
include_once H . 'sys/inc/thead.php';
title();

$args = [
         'save' => FILTER_DEFAULT,
         'pass' => FILTER_DEFAULT,
         'pass1' => FILTER_DEFAULT,
         'pass2' => FILTER_DEFAULT
         ];
$input_post = filter_input_array(INPUT_POST, $args);
unset($args);

if (isset($input_post['save'])) {
    if (isset($input_post['pass']) && $db->query("SELECT COUNT( * ) FROM `user` WHERE `id`=?i AND `pass`=?",
                                                 [$user['id'], shif($input_post['pass'])])->el()) {
        if (isset($input_post['pass1']) && isset($input_post['pass2'])) {
            if ($input_post['pass1']==$input_post['pass2']) {
                if (strlen2($input_post['pass1'])<6) {
                    $err='По соображениям безопасности новый пароль не может быть короче 6-ти символов';
                }
                if (strlen2($input_post['pass1'])>32) {
                    $err='Длина пароля превышает 32 символа';
                }
            } else {
                $err='Новый пароль не совпадает с подтверждением';
            }
        } else {
            $err='Введите новый пароль';
        }
    } else {
        $err='Старый пароль неверен';
    }
    if (!isset($err)) {
        $db->query("UPDATE `user` SET `pass`=? WHERE `id`=?i",
                   [shif($input_post['pass1']), $user['id']]);
        setcookie('pass', cookie_encrypt($input_post['pass1'], $user['id']), time()+60*60*24*365);
        $_SESSION['message'] = 'Пароль успешно изменен';
        header("Location: ?");
        exit;
    }
}
err();
aut();
echo "<div id='comments' class='menus'>";
echo "<div class='webmenu'>";
echo "<a href='/user/info/settings.php'>Общие</a>";
echo "</div>";
echo "<div class='webmenu last'>";
echo "<a href='/user/tape/settings.php'>Лента</a>";
echo "</div>";
echo "<div class='webmenu last'>";
echo "<a href='/user/discussions/settings.php'>Обсуждения</a>";
echo "</div>";
echo "<div class='webmenu last'>";
echo "<a href='/user/notification/settings.php'>Уведомления</a>";
echo "</div>";
echo "<div class='webmenu last'>";
echo "<a href='/user/info/settings.privacy.php' >Приватность</a>";
echo "</div>";
echo "<div class='webmenu last'>";
echo "<a href='/user/info/secure.php'  class='activ'>Пароль</a>";
echo "</div>";
echo "</div>";
echo "<form method='post' action='?$passgen'>\n";
echo "Старый пароль:<br />\n<input type='text' name='pass' value='' /><br />\n";
echo "Новый пароль:<br />\n<input type='password' name='pass1' value='' /><br />\n";
echo "Подтверждение:<br />\n<input type='password' name='pass2' value='' /><br />\n";
echo "<input type='submit' name='save' value='Изменить' />\n";
echo "</form>\n";

include_once H . 'sys/inc/tfoot.php';
