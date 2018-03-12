<?php
/*
-----------------------------------------------------------------
Загрузка Классов
-----------------------------------------------------------------
*/
require 'classes/class.user.php';
// Определение юзера
if (isset($_SESSION['id_user']) && $db->query("SELECT COUNT(*) FROM `user` WHERE `id` = '$_SESSION[id_user]' LIMIT 1")->el())
{
	$user = $db->query("SELECT * FROM `user` WHERE `id` = $_SESSION[id_user] LIMIT 1")->row();
	$db->query("UPDATE `user` SET `date_last` = '$time' WHERE `id` = '$user[id]' LIMIT 1");
	$user['type_input'] = 'session';
}
elseif (!isset($input_page) && isset($_COOKIE['id_user']) && isset($_COOKIE['pass']) && $_COOKIE['id_user'] && $_COOKIE['pass'])
{
	if (!isset($_POST['token']))
	{
		header("Location: /login.php?return=" . urlencode($_SERVER['REQUEST_URI']) . "&$passgen");
		exit;
	} 
}
if (!isset($_SERVER['HTTP_REFERER']))
$_SERVER['HTTP_REFERER'] = '/index.php';
// если аккаунт не активирован
if (isset($user['activation']) && $user['activation'] != NULL) 
{
	$err[] = 'Вам необходимо активировать Ваш аккаунт по ссылке, высланной на Email, указанный при регистрации';
	unset($user);
}
if (isset($user))
{
	$tmp_us = $db->query("SELECT `level` FROM `user_group` WHERE `id` = '$user[group_access]' LIMIT 1")->row();
	$user['level'] = $tmp_us['level'];
	$timeactiv  =  time() - $user['date_last'];
	
	if($timeactiv < 120)
	{
		$newtimeactiv = $user['time'] + $timeactiv;
		$db->query("UPDATE `user` SET `time` ='$newtimeactiv' WHERE `id` = '$user[id]' LIMIT 1");
	}
	if (isset($user['type_input']) && isset($_SERVER['HTTP_REFERER']) && !preg_match('#'.preg_quote($_SERVER['HTTP_HOST']).'#', $_SERVER['HTTP_REFERER']) && preg_match('#^https?://#i', $_SERVER['HTTP_REFERER']) && $ref=@parse_url($_SERVER['HTTP_REFERER']))
	{
		if (isset($ref['host']))
		{
			if (!$db->query("SELECT COUNT(*) FROM `user_ref` WHERE `id_user` = '$user[id]' AND `url` = '".my_esc($ref['host'])."'")->el())
			$db->query("INSERT INTO `user_ref` (`time`, `id_user`, `type_input`, `url`) VALUES ('$time', '$user[id]', '$user[type_input]', '".my_esc($ref['host'])."')");
			else
			$db->query("UPDATE `user_ref` SET `time` = '$time' WHERE `id_user` = '$user[id]' AND `url` = '".my_esc($ref['host'])."'");
		}
	}
	// Время обновления чата
	if ($user['set_time_chat']!=NULL)
	$set['time_chat'] = $user['set_time_chat'];
	// Постраничная навигация
	if ($user['set_p_str']!=NULL)
	$set['p_str'] = $user['set_p_str'];
	// Режим иконок
	$set['set_show_icon'] = $user['set_show_icon'];
	if (!isset($banpage)) // бан пользователя
	{
		if ($db->query("SELECT COUNT(*) FROM `ban` WHERE `razdel` = 'all' AND `id_user` = '$user[id]' AND (`time` > '$time' OR `view` = '0' OR `navsegda` = '1')")->el())
		{
			header('Location: /ban.php?'.SID);
			exit;
		}
	}
	/*
	========================================
	Создание настроек юзера 
	========================================
	*/
	if (!$db->query("SELECT COUNT(*) FROM `user_set` WHERE `id_user` = '$user[id]'")->el())
	$db->query("INSERT INTO `user_set` (`id_user`) VALUES ('$user[id]')");
	if (!$db->query("SELECT COUNT(*) FROM `discussions_set` WHERE `id_user` = '$user[id]'")->el())
	$db->query("INSERT INTO `discussions_set` (`id_user`) VALUES ('$user[id]')");
	if (!$db->query("SELECT COUNT(*) FROM `tape_set` WHERE `id_user` = '$user[id]'")->el())
	$db->query("INSERT INTO `tape_set` (`id_user`) VALUES ('$user[id]')");
	if (!$db->query("SELECT COUNT(*) FROM `notification_set` WHERE `id_user` = '$user[id]'")->el())
	$db->query("INSERT INTO `notification_set` (`id_user`) VALUES ('$user[id]')");
	// Записываем url 
	$db->query("UPDATE `user` SET `url` = '".my_esc($_SERVER['SCRIPT_NAME'])."' WHERE `id` = '$user[id]' LIMIT 1");
	
	// для web темы
	if ($webbrowser) 
	{
		if (is_dir(H.'style/themes/' . $user['set_them2']))
		$set['set_them'] = $user['set_them2'];
		else 
		$db->query("UPDATE `user` SET `set_them2` = '$set[set_them]' WHERE `id` = '$user[id]' LIMIT 1");
	}
	else
	{
		if (is_dir(H.'style/themes/'.$user['set_them']))$set['set_them'] = $user['set_them'];
		else $db->query("UPDATE `user` SET `set_them` = '$set[set_them]' WHERE `id` = '$user[id]' LIMIT 1");
	}
	// Пишем ip пользователя
	if (isset($ip2['add']))$db->query("UPDATE `user` SET `ip` = " . ip2long($ip2['add']) . " WHERE `id` = '$user[id]' LIMIT 1");
	else $db->query("UPDATE `user` SET `ip` = null WHERE `id` = '$user[id]' LIMIT 1");
	if (isset($ip2['cl']))$db->query("UPDATE `user` SET `ip_cl` = " . ip2long($ip2['cl']) . " WHERE `id` = '$user[id]' LIMIT 1");
	else $db->query("UPDATE `user` SET `ip_cl` = null WHERE `id` = '$user[id]' LIMIT 1");
	if (isset($ip2['xff']))$db->query("UPDATE `user` SET `ip_xff` = " . ip2long($ip2['xff']) . " WHERE `id` = '$user[id]' LIMIT 1");
	else $db->query("UPDATE `user` SET `ip_xff` = null WHERE `id` = '$user[id]' LIMIT 1");
	if ($ua)$db->query("UPDATE `user` SET `ua` = '" . my_esc($ua) . "' WHERE `id` = '$user[id]' LIMIT 1");
	// Непонятная сессия
	$db->query("UPDATE `user` SET `sess` = '$sess' WHERE `id` = '$user[id]' LIMIT 1");
	// Тип браузера
	$db->query("UPDATE `user` SET `browser` = '" . ($webbrowser == true ? "web" : "wap") . "' WHERE `id` = '$user[id]' LIMIT 1");
	// Проверяем на схожие ники
	$collision_q = $db->query("SELECT * FROM `user` WHERE `ip` = '$iplong' AND `ua` = '".my_esc($ua)."' AND `date_last` > '".(time()-600)."' AND `id` <> '$user[id]'");
	while ($collision = $collision_q->row())
	{
		if (!$db->query("SELECT COUNT(*) FROM `user_collision` WHERE `id_user` = '$user[id]' AND `id_user2` = '$collision[id]' OR `id_user2` = '$user[id]' AND `id_user` = '$collision[id]'")->el())
		$db->query("INSERT INTO `user_collision` (`id_user`, `id_user2`, `type`) values('$user[id]', '$collision[id]', 'ip_ua_time')");
	}
	
	
	/*
	========================================
	Ответы в комм > v.1.7.4
	========================================
	*/
	
	if (!isset($insert))
	$insert = null;
	if (isset($_GET['response']) && $db->query("SELECT COUNT(*) FROM `user` WHERE `id` = '" . intval($_GET['response']) . "'")->el())
	{
		$ank_reply = $db->query("SELECT nick,id FROM `user` WHERE `id` = '" . intval($_GET['response']) . "' LIMIT 1")->row();
		$insert = user::nick($ank_reply['id'], 0) . ', ';
		$go_link = '?' . $passgen . '&amp;response=' . $ank_reply['id'];	
	}
	else
	{
		$go_link = NULL;
	}
	// Ссылка при ответе
	define("REPLY", $go_link);
}
else
{
	// Тема для гостя
	if ($webbrowser)
	$set['set_them'] = $set['set_them2'];
	
	// Гость
	if ($ip && $ua)
	{
		if ($db->query("SELECT COUNT(*) FROM `guests` WHERE `ip` = '$iplong' AND `ua` = '".my_esc($ua)."' LIMIT 1")->el())
		{
			$guests = $db->query("SELECT * FROM `guests` WHERE `ip` = '$iplong' AND `ua` = '".my_esc($ua)."' LIMIT 1")->row();
			$db->query("UPDATE `guests` SET `date_last` = ".time().", `url` = '".my_esc($_SERVER['SCRIPT_NAME'])."', `pereh` = '".($guests['pereh']+1)."' WHERE `ip` = '$iplong' AND `ua` = '".my_esc($ua)."' LIMIT 1");
		}
		else
		{
			$db->query("INSERT INTO `guests` (`ip`, `ua`, `date_aut`, `date_last`, `url`) VALUES ('$iplong', '".my_esc($ua)."', '".time()."', '".time()."', '".my_esc($_SERVER['SCRIPT_NAME'])."')");
		}
	}
	unset($access);
}
if (!isset($user) || $user['level']  ==  0)
{
	@error_reporting(0);
	@ini_set('display_errors',false); // показ ошибок
	if (function_exists('set_time_limit'))@set_time_limit(20); // Ставим ограничение на 20 сек
}
if (!isset($user) && $set['guest_select']  ==  '1' && !isset($show_all) && $_SERVER['PHP_SELF'] != '/index.php' && $_SERVER['PHP_SELF'] != '/user/connect/loginAPI.php')
{
	header("Location: /aut.php");
	exit;
} 
if (isset($user))
{
	$user_gr = $db->query("SELECT * FROM `user_group` WHERE `id` = $user[group_access] LIMIT 1")->row();
	$user['group_name'] = $user_gr['name'];
	if (isset($_GET['sess_abuld']) && $_GET['sess_abuld']  ==  1) // Продолжаем просмотр файла с меткой 18+
	{
		$_SESSION['abuld'] = 1;
	}
	
	if (isset($_SESSION['abuld']) && $_SESSION['abuld']  ==  1)
	$user['abuld'] = 1;
}
/*
========================================
Смена тем для гостей
========================================
*/
if (isset($_GET['t']) && $_GET['t'] == 'wap' && !isset($user))
{
	$_SESSION['guest_theme']='wap';
	header('Location: ' . htmlspecialchars($_SERVER['HTTP_REFERER']));
	exit;
}
elseif (isset($_GET['t']) && $_GET['t'] == 'web' && !isset($user))
{
	$_SESSION['guest_theme']='web';
	header('Location: ' . htmlspecialchars($_SERVER['HTTP_REFERER']));
	exit;
}
if (isset($_SESSION['guest_theme']) && $_SESSION['guest_theme'] == 'web' && !isset($user))
{
	$set['set_them'] = 'web';
	$set['set_them2'] = 'web';	
}
elseif (isset($_SESSION['guest_theme']) && $_SESSION['guest_theme'] == 'wap' && !isset($user))
{
	$set['set_them'] = 'default';
	$set['set_them2'] = 'default';
}
/*
========================================
Смена тем для юзеров папки wap и web
========================================
*/
if (isset($user) && isset($_GET['t']))
{
	if ($webbrowser == 'WEB')
	{$set_t='set_them2';}
	else
	{$set_t='set_them';}
	
	$wap = 'default';
	$web = 'web';
	
	if ($_GET['t'] == 'wap')
	$db->query("update `user` set `$set_t` = '$wap' where `id`='$user[id]' limit 1");
	elseif ($_GET['t'] == 'web')
	$db->query("update `user` set `$set_t` = '$web' where `id`='$user[id]' limit 1");
	header('Location: ' . htmlspecialchars($_SERVER['HTTP_REFERER']));
	exit;
}
/*
========================================
Сортировка списка по времени
========================================
*/
if (isset($user) && isset($_GET['sort']) && ($_GET['sort'] == '0' || $_GET['sort'] == '1'))
{
	$db->query("update `user` set `sort` = '$_GET[sort]' where `id` = '$user[id]' limit 1");
	header('Location: ' . htmlspecialchars($_SERVER['HTTP_REFERER']));
}
if (isset($user))$sort = ($user['sort'] == 1?' ASC ':' DESC ');
else $sort = 'DESC';
// Страницы 
if (isset($user) && $user['sort']  ==  1)
$pageEnd = 'end'; else $pageEnd = '1';
/*
========================================
Ответы в комм [DELETE]
========================================
*/
if (isset($user) && isset($_GET['response']))
{
	if ($db->query("SELECT COUNT(*) FROM `user` WHERE `id` = '".intval($_GET['response'])."'")->el())
	{
		$id_response = intval($_GET['response']);
		$ank_response = get_user($id_response);
	}
	else
	{
		$id_response=NULL;
		$ank_response=NULL;
	}
}
if (isset($_GET['go_otv']) && $db->query("SELECT COUNT(*) FROM `user` WHERE `id` = '".intval($_GET['go_otv'])."'")->el())
{
	$otv_ank = intval($_GET['go_otv']);
	$ank_otv = get_user($otv_ank);
	$respons = TRUE;
}
else
{
	$otv_ank = NULL;
	$ank_otv = NULL;
	$respons = false;
}
if (isset($_GET['response']))
{
	$otvet = "$ank_response[nick], ";
	$go_otv = '?' . $passgen . '&amp;go_otv=' . $ank_response['id'] . '';
}else{
	$otvet = NULL;
	$go_otv = NULL;
}
if (isset($_GET['response']))
{
	$id_response = intval($_GET['response']);
	$ank_response = get_user($id_response);
	$respons_msg = ''.$ank_response['nick'].', ';
}
else
{
	$id_response = NULL;
	$ank_response = NULL;
	$respons_msg = NULL;
}
/*
========================================
Скрытие новости
========================================
*/
if (isset($user) && isset($_GET['news_read']))
{
	$db->query("update `user` set `news_read` = '1' where `id` = '$user[id]' limit 1");
	$_SESSION['message'] = "Новость успешно скрыта"; // Оповещаем
	header("Location: /?");
	exit;
}
/*
========================================
Панель навигации над полем ввода
========================================
*/
$tPanel = "<div id='comments' class='tpanel'>
<div class='tmenu'><a href='/plugins/smiles/'>Смайлы</a></div>
<div class='tmenu'><a href='/plugins/rules/bb-code.php'>Теги</a></div>
</div>";
/*
========================================
Причины бана
========================================
*/
$pBan[0] = "Другое";
$pBan[1] = "Спам/Реклама";
$pBan[2] = "Мошенничество";
$pBan[3] = "Нецензурная брань";
$pBan[4] = "Клонирование ников";
$pBan[5] = "Подстрекательство, провокация и побуждение к агрессии";
$pBan[6] = "Флуд";
$pBan[7] = "Флейм";
/*
========================================
Раздел бана
========================================
*/
$rBan['all'] = "Весь сайт";
$rBan['notes'] = "Дневники";
$rBan['forum'] = "Форум";
$rBan['files'] = "Файлы";
$rBan['guest'] = "Гостевая";
$rBan['chat'] = "Чат";
$rBan['lib'] = "Библиотека";
$rBan['foto'] = "Фотографии";
/*
========================================
Сообщение в комментариях
========================================
*/
$banMess = '[red]Это сообщение ушло париться вместе с автором в баню![/red]';
if (isset($_POST['msg']) && !isset($user)){echo "Вы не авторизованы!"; exit;}
/*
========================================
Валюта
========================================
*/
$sMonet[0] = 'монет';
$sMonet[1] = 'монета';
$sMonet[2] = 'монеты';
// Загрузка остальных плагинов из папки "sys/inc/plugins"
$opdirbase = opendir(H.'sys/inc/plugins');
while ($filebase = readdir($opdirbase))
{
	if (preg_match('#\.php$#i', $filebase))
	{
		require_once(H.'sys/inc/plugins/' . $filebase);
	}
}
?>