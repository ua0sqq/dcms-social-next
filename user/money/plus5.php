<?php

include_once '../../sys/inc/start.php';
include_once '../../sys/inc/compress.php';
include_once '../../sys/inc/sess.php';
include_once '../../sys/inc/home.php';
include_once '../../sys/inc/settings.php';
include_once '../../sys/inc/db_connect.php';
include_once '../../sys/inc/ipua.php';
include_once '../../sys/inc/fnc.php';
include_once '../../sys/inc/adm_check.php';
include_once '../../sys/inc/user.php';

only_reg('/aut.php');

$set['title']='Оценки 5+';
include_once '../../sys/inc/thead.php';
title();
err();
aut();
$db->setDebug('mydebug');
$stav = filter_input(INPUT_POST, 'stav', FILTER_VALIDATE_INT);
    if ($stav) {
        if (in_array($stav, range(1, 7))) {
            $plus_time = 86400*$stav;
            $tm = time() + $plus_time;
        } else {
            $err = 'Неверное значение';
        }

        if ($user['money'] >= $stav) {
            if (!isset($err)) {
                $db->query(
                "UPDATE `user_set` SET `ocenka`=?i WHERE `id_user`=?i",
                       [$tm, $user['id']]);
				$db->query("UPDATE `user` SET `money`=`money`-?i WHERE `id`=?i",
						   [$stav, $user['id']]);

                $_SESSION['message'] = 'Поздравляем, вы успешно подключили услугу';
                header('Location: /user/money/index.php?');
                exit;
            }
        } else {
            $err='У вас не достаточно средств';
        }
    }
    err();
?>
<div class="foot">
	<img src="/style/icons/str2.gif" alt="*"> <a href="/info.php"><?php echo $user['nick'];?></a> | Услуга "Оценка 5+"
</div>
<div class="nav1">
	<p>Услуга <img src="/style/icons/6.png" alt="*">
	<p>1 монета = 1 день пользования привилегией.</p>
</div><?php

    if (!$db->query(
        "SELECT COUNT( * ) FROM `user_set` WHERE `id_user`=?i AND `ocenka`>?i",
                    [$user['id'], time()])->el()) {
?>
<form method="post" action="?">
	<p>Ставка: <select name="stav">
	<option value="1">1</option>
	<option value="2">2</option>
	<option value="3">3</option>
	<option value="4">4</option>
	<option value="5">5</option>
	<option value="6">6</option>
	<option value="7">7</option>
	</select> монета</p>
     <p><input value="Купить услугу" type="submit" /></p>
</form><?php
    } else {
?>
<div class="mess">
	Услуга подключена
</div><?php
    }
?>
<div class="foot">
	<img src="/style/icons/str2.gif" alt=""> <a href="/info.php"><?php echo $user['nick'];?></a> | Услуга "Оценка 5+"
</div><?php

include_once '../../sys/inc/tfoot.php';
