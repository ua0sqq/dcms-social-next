<?php
$set['title']='Регистрация Администратора';
include_once 'inc/head.php'; // верхняя часть темы оформления

if (!isset($_SESSION['shif'])) {
    $_SESSION['shif']=$passgen;
}
$set['shif'] = $_SESSION['shif'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/sys/library/goDB/autoload.php';

    $set['charset_names'] = 'utf8';
    $params = array(
                '_adapter' => 'mysql',
                'host'     => $_SESSION['host'],
                'username' => $_SESSION['user'],
                'password' => $_SESSION['pass'],
                'dbname'   => $_SESSION['db'],
                'charset'  => $set['charset_names'],
                '_debug'   => false,
                '_prefix'  => '',
            );
    
    \go\DB\autoloadRegister();
    $db = go\DB\DB::create($params);

if (isset($_SESSION['adm_reg_ok']) && $_SESSION['adm_reg_ok']==true) {
    if (isset($_GET['step']) && $_GET['step']=='5') {
        $tmp_set['title']=strtoupper($_SERVER['HTTP_HOST']).' - Главная';
        $tmp_set['mysql_host']=$_SESSION['host'];
        $tmp_set['mysql_user']=$_SESSION['user'];
        $tmp_set['mysql_pass']=$_SESSION['pass'];
        $tmp_set['mysql_db_name']=$_SESSION['db'];
        $tmp_set['charset_names']='utf8';
        $tmp_set['shif'] = $_SESSION['shif'];

        if (save_settings($tmp_set)) {
            unset($_SESSION['install_step'],$_SESSION['host'],$_SESSION['user'],$_SESSION['pass'],$_SESSION['db'],$_SESSION['adm_reg_ok'],$_SESSION['mysql_ok']);
            if ($_SERVER["SERVER_ADDR"]!='127.0.0.1') {
                // delete_dir(H.'install/'); // TODO: ???
            }
            header("Location: /index.php?".SID);
            exit;
        } else {
            $msg['Невозможно сохранить настройки системы'];
        }
    }
} elseif (isset($_POST['reg'])) {

// проверка ника
    if (!preg_match("#^([A-zА-я0-9\-\_\ ])+$#ui", $_POST['nick'])) {
        $err[]='В нике присутствуют запрещенные символы';
    }
    if (preg_match("#[a-z]+#ui", $_POST['nick']) && preg_match("#[а-я]+#ui", $_POST['nick'])) {
        $err[]='Разрешается использовать символы только русского или только английского алфавита';
    }
    if (preg_match("#(^\ )|(\ $)#ui", $_POST['nick'])) {
        $err[]='Запрещено использовать пробел в начале и конце ника';
    } else {
        if (strlen2($_POST['nick'])<3) {
            $err[]='Ник короче 3-х символов';
        } elseif (strlen2($_POST['nick'])>16) {
            $err[]='Ник длиннее 16-ти символов';
        } elseif ($db->query("SELECT COUNT(*) FROM `user` WHERE `nick` = ?",
                             [$_POST['nick']])->el()) {
            $err[]='Выбранный ник уже занят другим пользователем';
        } else {
            $nick=$_POST['nick'];
        }
    }
    // проверка пароля
    if (!isset($_POST['password']) || $_POST['password']==null) {
        $err[]='Введите пароль';
    } else {
        if (strlen2($_POST['password'])<6) {
            $err[]='Пароль короче 6-ти символов';
        } elseif (strlen2($_POST['password'])>16) {
            $err[]='Пароль длиннее 16-ти символов';
        } elseif (!isset($_POST['password_retry'])) {
            $err[]='Введите подтверждение пароля';
        } elseif ($_POST['password']!==$_POST['password_retry']) {
            $err[]='Пароли не совпадают';
        } else {
            $password=$_POST['password'];
        }
    }

    if (!isset($_POST['pol']) || !is_numeric($_POST['pol']) || ($_POST['pol']!=='0' && $_POST['pol']!=='1')) {
        $err[]='Ошибка при выборе пола';
    } else {
        $pol=intval($_POST['pol']);
    }

    if (!isset($err)) {
        // если нет ошибок
        $db->query("INSERT INTO `user` (`nick`, `pass`, `date_reg`, `date_aut`, `date_last`, `pol`, `level`, `group_access`, `balls`, `money`)
VALUES(?, ?, ?i, ?i, ?i, ?, ?, ?i, ?i, ?i)",
[$nick, shif($password), $time, $time, $time, $pol, '4', 15, 5000, 500]);

        $user = $db->query("SELECT * FROM `user` WHERE `nick` = ? AND `pass` = ?",
                           [$nick, shif($password)])->row();
        /*
        ========================================
        Создание настроек юзера
        ========================================
        */
        $db->query("INSERT INTO `user_set` (`id_user`) VALUES (?i)",
                   [$user['id']]);
        $db->query("INSERT INTO `discussions_set` (`id_user`) VALUES (?i)",
                   [$user['id']]);
        $db->query("INSERT INTO `tape_set` (`id_user`) VALUES (?i)",
                   [$user['id']]);
        $db->query("INSERT INTO `notification_set` (`id_user`) VALUES (?i)",
                   [$user['id']]);

        $_SESSION['id_user']=$user['id'];
        setcookie('id_user', $user['id'], time()+60*60*24*365, '/', $_SERVER['HTTP_HOST']);
        setcookie('pass', cookie_encrypt($password, $user['id']), time()+60*60*24*365);

        $_SESSION['adm_reg_ok']=true;
    }
}

if (isset($_SESSION['adm_reg_ok']) && $_SESSION['adm_reg_ok']==true) {
    echo "<div class='msg'>Регистрация администратора прошла успешно</div>\n";
    if (isset($msg)) {
        foreach ($msg as $key=>$value) {
            echo "<div class='msg'>$value</div>\n";
        }
    }
    echo "<hr />\n";
    echo "<form method=\"get\" action=\"index.php\">\n";
    echo "<input name='gen' value='$passgen' type='hidden' />\n";
    echo "<input name=\"step\" value=\"".($_SESSION['install_step']+1)."\" type=\"hidden\" />\n";
    echo "<input value='Завершить установку' type=\"submit\" />\n";
    echo "</form>\n";
    echo "* после установки обязательно удалите папку /install/<br />\n";
} else {
    if (isset($err)) {
        foreach ($err as $key=>$value) {
            echo "<div class='err'>$value</div>\n";
        }
        echo "<hr />\n";
    }

    echo "<form action='index.php?$passgen' method='post'>\n";
    echo "Логин (3-16 символов):<br />\n<input type='text' name='nick'".((isset($nick))?" value='".$nick."'":" value='Admin'")." maxlength='16' /><br />\n";
    echo "Пароль (6-16 символов):<br />\n<input type='password'".((isset($password))?" value='".$password."'":null)." name='password' maxlength='16' /><br />\n";
    echo "* использование простого пароля облегчает жизнь хакерам<br />\n";
    echo "Подтверждение пароля:<br />\n<input type='password'".((isset($password))?" value='".$password."'":null)." name='password_retry' maxlength='16' /><br />\n";
    echo "Ваш пол:<br />\n";
    echo "<select name='pol'>\n";
    echo "<option value='1'".((isset($pol) && $pol===1)?" selected='selected'":null).">Мужской</option>\n";
    echo "<option value='0'".((isset($pol) && $pol===0)?" selected='selected'":null).">Женский</option>\n";
    echo "</select><br />\n";
    echo "* Все поля обязательны к заполнению<br />\n";
    echo "<input type='submit' name='reg' value='Регистрация' /><br />\n";
    echo "</form>\n";
}
echo "<hr />\n";
echo "<b>Шаг: $_SESSION[install_step]</b>\n";

include_once 'inc/foot.php'; // нижняя часть темы оформления
