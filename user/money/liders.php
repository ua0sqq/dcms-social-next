<?php

include_once '../../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/adm_check.php';
include_once H . 'sys/inc/user.php';

only_reg('/aut.php');

$set['title'] = 'Лидеры';
include_once H . 'sys/inc/thead.php';
title();
err();
aut();

$args = [
         'stav' => FILTER_VALIDATE_INT,
         'msg' => FILTER_DEFAULT,];
$in_post = filter_input_array(INPUT_POST, $args);
unset($args);

if ($in_post['stav'] && !empty($in_post['msg'])) {
    if (in_array($in_post['stav'], range(1, 7))) {
        $plus_time = 86400*$in_post['stav'];
        $tm = time() + $plus_time;
    } else {
        $err = 'Неверное значение';
    }
    if ($user['money'] >= $in_post['stav']) {
        if (!isset($err)) {
            if (!$db->query(
                "SELECT COUNT( * ) FROM `liders` WHERE `id_user`=?i",
                            [$user['id']])->el()) {
                $db->query(
                    "INSERT INTO `liders` (`id_user`, `stav`, `msg`, `time`, `time_p`) VALUES(?i, ?i, ?, ?i, ?i)",
                           [$user['id'], $in_post['stav'], $in_post['msg'], $tm, time()]);
				$_SESSION['message'] = 'Вы успешно стали лидером';
            } else {
                $db->query(
                    "UPDATE `liders` SET `time`=?i, `time_p`=?i, `msg`=?, `stav`=?i WHERE `id_user`=?i",
                           [$tm, time(), $in_post['msg'], $in_post['stav'], $user['id']]);
				$_SESSION['message'] = 'Вы добавили время лидера';
            }
            $db->query(
                "UPDATE `user` SET `money`=`money`-?i WHERE `id`=?i",
                       [$in_post['stav'], $user['id']]);
            header('Location: /user/liders/index.php?ok');
            exit;
        }
    } else {
        $err='У вас не достаточно средств';
    }
} else {
    $err='Поле сообщения не может быть пустым';
}
    err();
?>
<div class="foot">
	<img src="/style/icons/str2.gif" alt="S"/> <a href="/user/money/">Дополнительные услуги</a> | <strong>Стать лидером</strong>
</div>
<div class="mess">
	<p>&nbsp;&nbsp;&nbsp;Для того, чтобы попасть в Лидеры, необходимо минимум <strong class="off">1 <span class="on">монету</span></strong>.
	Эта услуга в течение 1 дня обеспечит Ваше пребывание в данном ТОП\'е.
	<p>&nbsp;&nbsp;&nbsp;Ваше положение в ТОП\'е зависит от кол-ва монет (общем времени пребывания)! 
	Помимо этого, Ваша анкета будет котироваться на страницах Знакомств и Поиска!</p>
</div>
<form class="main" method="post" action="?">
	<p>Ставка:
	<p><select name="stav">
	<option value="1">1</option>
	<option value="2">2</option>
	<option value="3">3</option>
	<option value="4">4</option>
	<option value="5">5</option>
	<option value="6">6</option>
	<option value="7">7</option>
	</select>&nbsp;&nbsp;монета</p>
    <p>Подпись (215 символов)
	<p><textarea name="msg"></textarea>
	<p><input value="Стать лидером" type="submit" /></p>
</form>
<div class="foot">
	<img src="/style/icons/str2.gif" alt="S"/> <a href="/user/money/">Дополнительные услуги</a> | <strong>Стать лидером</strong>
</div><?php

include_once H . 'sys/inc/tfoot.php';
