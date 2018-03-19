<?php
include_once 'sys/inc/start.php';
include_once 'sys/inc/compress.php';
include_once 'sys/inc/sess.php';
include_once 'sys/inc/home.php';
include_once 'sys/inc/settings.php';
include_once 'sys/inc/db_connect.php';
include_once 'sys/inc/ipua.php';
include_once 'sys/inc/fnc.php';
include_once 'sys/inc/shif.php';
$show_all=true; // показ для всех
$input_page=true;
include_once 'sys/inc/user.php';
only_unreg();
$args = [
		 'id' => [
				  'filter'  => FILTER_VALIDATE_INT,
				  'options' => [
								'default'   => null,
								'min_range' => 1,
								],
				  ],
		 'pass' => FILTER_DEFAULT,
		 'nick' => FILTER_SANITIZE_STRING,
		 'aut_save' => [
				  'filter'  => FILTER_VALIDATE_INT,
				  'options' => [
								'default'   => null,
								'max_range' => 1,
								],
				  ],
		 'return' => FILTER_SANITIZE_URL,
		 'id_user' => [
				  'filter'  => FILTER_VALIDATE_INT,
				  'options' => [
								'default'   => null,
								'min_range' => 1,
								],
				  ],
		 ];
$in_get = filter_input_array(INPUT_GET, $args);
$in_post = filter_input_array(INPUT_POST, $args);
$in_cookies = filter_input_array(INPUT_COOKIE, $args);
unset($args);
$user_data = [];
$user_log_data = [];

if ($in_get['id'] && !empty($in_get['pass'])) {
    if ($user = $db->query("SELECT `id` FROM `user` WHERE `id` = ?i AND `pass` = ?",
				   [$in_get['id'], shif($in_get['pass'])])->el()) {
        
		$user = get_user($user);
        $_SESSION['id_user'] = $user['id'];

		$user_data = ['date_aut' =>time(), 'date_last' => time()];		
		$user_log_data = [$user['id'], time(), $user['ua'], $user['ip'], '0'];		
    
	} else {
        $_SESSION['err'] = 'Неправильный логин или пароль';
    }
} elseif (!empty($in_post['nick']) && !empty($in_post['pass'])) {
	$data =  [$in_post['nick'], shif($in_post['pass'])];
    if ($user = $db->query("SELECT `id` FROM `user` WHERE `nick` = ? AND `pass` = ?", $data)->el()) {

		$user = get_user($user);
        $_SESSION['id_user'] = $user['id'];
       
        // сохранение данных в COOKIE
        if (isset($in_post['aut_save']) && $in_post['aut_save']) {
            setcookie('id_user', $user['id'], time()+60*60*24*30);
            setcookie('pass', cookie_encrypt($in_post['pass'], $user['id']), time()+60*60*24*30);
        }

		$user_data = ['date_aut' =>time(), 'date_last' => time()];		
		$user_log_data = [$user['id'], time(), $user['ua'], $user['ip'], '1'];	
    
	} else {
        $_SESSION['err'] = 'Неправильный логин или пароль';
    }
} elseif (!empty($in_cookies['id_user']) && !empty($in_cookies['pass'])) {
	$data = [$in_cookies['id_user'], shif(cookie_decrypt($in_cookies['pass'], $in_cookies['id_user']))];
    if ($user = $db->query("SELECT `id` FROM `user` WHERE `id` = ?i AND `pass` = ?",
						   $data)->el()) {
   
		$user = get_user($user);
        $_SESSION['id_user'] = $user['id'];
		$user_data = ['date_aut' =>time(), 'date_last' => time()];		
		$user_log_data = [$user['id'], time(), $user['ua'], $user['ip'], '2'];

    } else {
        $_SESSION['err'] = 'Ошибка авторизации по COOKIE';
        setcookie('id_user');
        setcookie('pass');
    }
} else {
    $_SESSION['err'] = 'Ошибка авторизации';
}
if (!isset($user)) {
    header('Location: /aut.php');
    exit;
}

// Пишем ip пользователя
if (isset($ip2['add'])) {
	$user_data += ['ip' => ip2long($ip2['add'])];
}
if (isset($ip2['cl'])) {
	$user_data += ['ip_cl' => ip2long($ip2['cl'])];
}
if (isset($ip2['xff'])) {
	$user_data += ['ip_xff' => ip2long($ip2['xff'])];
}
if ($ua) {
	$user_data += ['ua' => $ua];
}

// Проверяем на схожие ники
$collision_q = $db->query("SELECT `u`.`id`, (
SELECT COUNT(*) FROM `user_collision` WHERE `id_user`=?i AND `id_user2`=`u`.`id` OR `id_user2`=?i AND `id_user`=`u`.`id`) cnt
FROM `user` u WHERE `ip`=?i AND `ua`=? AND `date_last`>?i AND `u`.`id`<>?i",
						  [$user['id'], $user['id'], $iplong, $ua, time()-600, $user['id']]);
while ($collision = $collision_q->row()) {
    if (!$collision['cnt']) {
        $db->query("INSERT INTO `user_collision` (`id_user`, `id_user2`, `type`) VALUES(?i, ?i, ?)",
				   [$user['id'], $collision['id'], 'ip_ua_time']);
    }
}

// Рейтинг
if ($user['rating_tmp'] > 1000) {
    $col = $user['rating_tmp'];
    $col = $user['rating_tmp'] / 1000;
    // Округляем
    $col = intval($col);
    // Оповещаем
    $_SESSION['message'] = "Поздравляем! Вам за вашу активность начислено $col% рейтинга!";
    // Вычисляем остаток счетчика активности
    $col_reset = $user['rating_tmp'] - ($col * 1000);
    // Сбрасываем
	$user_data += ['rating' => $user['rating'] + $col, 'rating_tmp' => $col_reset];
}
if (!empty($user_data)) {
	$user_data += ['sess' => $sess, 'browser' => ($webbrowser == true ? "wap" : "web")];
	$db->query("UPDATE `user` SET ?set WHERE `id` = ?i",
			   [$user_data, $user['id']]);
}
if (!empty($user_log_data)) {
	$db->query("INSERT INTO `user_log` (`id_user`, `time`, `ua`, `ip`, `method`) VALUES(?i, ?i, ?, ?i, ?)",
			   $user_log_data);
}
if (isset($in_get['return'])) {
    header('Location: '.urldecode($in_get['return']));
} else {
    header("Location: /my_aut.php?".SID);
}
exit;
