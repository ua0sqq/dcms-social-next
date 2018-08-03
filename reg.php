<?php

include_once 'sys/inc/start.php';
include_once 'sys/inc/compress.php';
include_once 'sys/inc/sess.php';
include_once 'sys/inc/settings.php';
include_once 'sys/inc/db_connect.php';
include_once 'sys/inc/ipua.php';
include_once 'sys/inc/fnc.php';
include_once 'sys/inc/shif.php';
$show_all=true; // показ для всех
include_once 'sys/inc/user.php';

only_unreg();
$set['title']='Регистрация';
include_once 'sys/inc/thead.php';
title();

if ($set['guest_select']=='1') {
    msg("Доступ к сайту разрешен только авторизованым пользователям");
}
if ((!isset($_SESSION['refer']) || $_SESSION['refer']==null) && isset($_SERVER['HTTP_REFERER'])
    && $_SERVER['HTTP_REFERER'] != null	&& !preg_match('#mail\.php#', $_SERVER['HTTP_REFERER'])) {
    $_SESSION['refer']=str_replace('&', '&amp;', preg_replace('#^http://[^/]*/#', '/', $_SERVER['HTTP_REFERER']));
}
if ($set['reg_select']=='close') {
    $err='Регистрация временно приостановлена';
    err();
    echo "<a href='/aut.php'>Авторизация</a><br />\n";
    include_once 'sys/inc/tfoot.php';
} elseif ($set['reg_select']=='open_mail' && isset($_GET['id']) && isset($_GET['activation']) && $_GET['activation']!=null) {
    if ($db->query("SELECT COUNT( * ) FROM `user` WHERE `id`=?i AND `activation`=?",
                   [$_GET['id'], $_GET['activation']])->el()) {
        $db->query("UPDATE `user` SET `activation`=?n WHERE `id`=?i",
                   [null, $_GET['id']]);
        $user = $db->query("SELECT * FROM `user` WHERE `id`=?i",
                           [$_GET['id']])->row();
        $db->query("INSERT INTO `reg_mail` (`id_user`,`mail`) VALUES (?i, ?)",
                   [$user['id'], $user['ank_mail']]);
        
        msg('Ваш аккаунт успешно активирован');
        $_SESSION['id_user'] = $user['id'];
        include_once 'sys/inc/tfoot.php';
    }
}
if (isset($_SESSION['step']) && $_SESSION['step']==1 && !$db->query("SELECT COUNT( * ) FROM `user` WHERE `nick`=?",
                                                                    [$_SESSION['reg_nick']])->el()
    && isset($_POST['pass1']) && $_POST['pass1']!=null && $_POST['pass2'] && $_POST['pass2']!=null) {
    if ($set['reg_select']=='open_mail') {
        if (!isset($_POST['ank_mail']) || $_POST['ank_mail']==null) {
            $err[]='Неоходимо ввести Email';
        } elseif (!preg_match('#^[A-z0-9-\._]+@[A-z0-9]{2,}\.[A-z]{2,4}$#ui', $_POST['ank_mail'])) {
            $err[]='Неверный формат Email';
        } elseif ($db->query("SELECT COUNT( * ) FROM `reg_mail` WHERE `mail`=?",
                             [$_POST['ank_mail']])->el()) {
            $err[]="Пользователь с этим E-mail уже зарегистрирован";
        }
    }
    if (strlen2($_POST['pass1'])<6) {
        $err[]='По соображениям безопасности пароль не может быть короче 6-ти символов';
    }
    if (strlen2($_POST['pass1'])>32) {
        $err[]='Длина пароля превышает 32 символа';
    }
    if ($_POST['pass1']!=$_POST['pass2']) {
        $err[]='Пароли не совпадают';
    }

    if (!isset($_SESSION['captcha']) || !isset($_POST['chislo']) || $_SESSION['captcha']!=$_POST['chislo']) {
        $err[]='Неверное проверочное число';
    }
    if (!isset($err)) {
        $pol = !empty($_POST['pol']) ? '1' : '0';
        if ($set['reg_select']=='open_mail') {
            
            $activation=md5(passgen());
            $id_reg=$db->query("INSERT INTO `user` (`nick`, `pass`, `date_reg`, `date_last`, `pol`, `activation`, `ank_mail`) VALUES(?, ?, ?i, ?i, ?, ?, ?)",
                               [$_SESSION['reg_nick'], shif($_POST['pass1']), $time, $time, $pol,$activation, $_POST['ank_mail']])->id();
            $subject = "Активация аккаунта";
            $regmail = "Здравствуйте $_SESSION[reg_nick]<br />
Для активации Вашего аккаунта перейдите по ссылке:<br />
<a href='http://$_SERVER[HTTP_HOST]/reg.php?id=$id_reg&amp;activation=$activation'>http://$_SERVER[HTTP_HOST]/reg.php?id=".$id_reg."&amp;activation=$activation</a><br />
Если аккаунт не будет активирован в течении 24 часов, он будет удален<br />
С уважением, администрация сайта<br />
";
            $adds="From: \"password@$_SERVER[HTTP_HOST]\" <password@$_SERVER[HTTP_HOST]>\n";
            //$adds = "From: <$set[reg_mail]>\n";
            //$adds .= "X-sender: <$set[reg_mail]>\n";
            $adds .= "Content-Type: text/html; charset=utf-8\n";
            mail($_POST['ank_mail'], '=?utf-8?B?'.base64_encode($subject).'?=', $regmail, $adds);
        } else {
            $db->query("INSERT INTO `user` (`nick`, `pass`, `date_reg`, `date_last`, `pol`) VALUES(?, ?, ?i, ?i, ?)",
                       [$_SESSION['reg_nick'], shif($_POST['pass1']), $time, $time, $pol]);
        }
        $user = $db->query("SELECT * FROM `user` WHERE `nick`=? AND `pass`=? LIMIT ?i",
                           [$_SESSION['reg_nick'], shif($_POST['pass1']), 1])->row();
        /*
        ========================================
        Создание настроек юзера
        ========================================
        */
        $db->query("INSERT INTO `user_set` (`id_user`) VALUES (?i)", [$user['id']]);
        $db->query("INSERT INTO `discussions_set` (`id_user`) VALUES (?i)", [$user['id']]);
        $db->query("INSERT INTO `tape_set` (`id_user`) VALUES (?i)", [$user['id']]);
        $db->query("INSERT INTO `notification_set` (`id_user`) VALUES (?i)", [$user['id']]);
        if (isset($_SESSION['http_referer'])) {
            $db->query("INSERT INTO `user_ref` (`time`, `id_user`, `type_input`, `url`) VALUES (?i, ?i, ?, ?)",
                       [$time, $user['id'], 'reg', $_SESSION['http_referer']]);
        }
        $_SESSION['id_user']=$user['id'];
        setcookie('id_user', $user['id'], time()+60*60*24*365);
        setcookie('pass', cookie_encrypt($_POST['pass1'], $user['id']), time()+60*60*24*365);
        if ($set['reg_select']=='open_mail') {
            msg('Вам необходимо активировать Ваш аккаунт по ссылке, высланной на Email');
        } else {
            $db->query("UPDATE `user` SET `wall`=?i WHERE `id`=?i",
                       [0, $user['id']]);
            header('Location: /umenu.php?login=' . htmlspecialchars($_POST['nick']) . '&pass=' . htmlspecialchars($_POST['pass1']));
        }
        echo "Если Ваш браузер не поддерживает Cookie, Вы можете создать закладку для автовхода<br />\n";
        echo "<input type='text' value='http://$_SERVER[SERVER_NAME]/login.php?id=$user[id]&amp;pass=".htmlspecialchars($_POST['pass1'])."' /><br />\n";
        if ($set['reg_select']=='open_mail') {
            unset($user);
        }
        echo "<div class='foot'>\n";
        echo "&raquo;<a href='settings.php'>Мои настройки</a><br />\n";
        echo "&raquo;<a href='umenu.php'>Мое меню</a><br />\n";
        echo "</div>\n";
        include_once 'sys/inc/tfoot.php';
    }
} elseif (isset($_POST['nick']) && !empty($_POST['nick'])) {
    $nick = trim($_POST['nick']);
    if (!$db->query("SELECT COUNT( * ) FROM `user` WHERE `nick`=?",
                    [$nick])->el()) {
        
        if (!preg_match("#^([A-zА-я0-9\-\_\ ])+$#ui", $nick)) {
            $err[] = 'В нике присутствуют запрещенные символы';
        }
        if (preg_match("#[a-z]+#ui", $nick) && preg_match("#[а-я]+#ui", $nick)) {
            $err[] = 'Разрешается использовать символы только русского или только английского алфавита';
        }
        if (preg_match("#(^\ )|(\ $)#ui", $nick)) {
            $err[] = 'Запрещено использовать пробел в начале и конце ника';
        }
        if (strlen2($nick) < 3) {
            $err[] = 'Короткий ник';
        }
        if (strlen2($nick) > 32) {
            $err[] = 'Длина ника превышает 32 символа';
        }
    } else { 
        $err[] = 'Ник "' . htmlspecialchars($nick) . '" уже зарегистрирован';
    }
    if (!isset($err)) { 
        $_SESSION['reg_nick'] = $nick;
        $_SESSION['step'] = 1;
        msg('Ник "' . $nick . '" может быть успешно зарегистрирован');
    }
}
err();
if (isset($_SESSION['step']) && $_SESSION['step']==1) {
    echo "<form method='post' action='/reg.php?$passgen'>\n";
    echo "Ваш ник [A-zА-я0-9 -_]:<br /><input type='text' name='nick' maxlength='32' value='$_SESSION[reg_nick]' /><br />\n";
    echo "<input type='submit' value='Другой' />\n";
    echo "</form><br />\n";
    echo "<form method='post' action='/reg.php?$passgen'>\n";
    echo "Ваш пол:<br /><select name='pol'><option value='1'>Мужской</option><option value='0'>Женский</option></select><br />\n";
    if ($set['reg_select']=='open_mail') {
        echo "E-mail:<br /><input type='mail' name='ank_mail' /><br />\n";
        echo "* Указывайте ваш реальный адрес E-mail. На него придет код для активации аккаунта.<br />\n";
    }
    echo "Введите пароль (6-32 символов):<br /><input type='password' name='pass1' maxlength='32' /><br />\n";
    echo "Повторите пароль:<br /><input type='password' name='pass2' maxlength='32' /><br />\n";
    echo "<img src='/captcha.php?$passgen&amp;SESS=$sess' width='100' height='30' alt='Проверочное число' /><br />\n<input name='chislo' size='5' maxlength='5' value='' type='text' /><br/>\n";
    echo "Регистрируясь, Вы автоматически соглашаетесь с <a href='/rules.php'>правилами</a> сайта<br />\n";
    echo "<input type='submit' value='Продолжить' />\n";
    echo "</form><br />\n";
} else {
    echo "<form class='mess' method='post' action='/reg.php?$passgen'>\n";
    echo "Выберите ник [A-zА-я0-9 -_]:<br /><input type='text' name='nick' maxlength='32' /><br />\n";
    echo "Регистрируясь, Вы автоматически соглашаетесь с <a href='/rules.php'>правилами</a> сайта<br />\n";
    echo "<input type='submit' value='Продолжить' />\n";
    echo "</form><br />\n";
}
echo "<div class = 'foot'>Уже зарегистрированы?<br />&raquo;<a href='/aut.php'>Авторизация</a></div>
<div class = 'foot'>Не можете вспомнить пароль?<br />&raquo;<a href='/pass.php'>Восстановить пароль</a></div>\n";

include_once 'sys/inc/tfoot.php';
