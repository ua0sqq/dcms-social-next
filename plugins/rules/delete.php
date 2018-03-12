<?php
include_once '../../sys/inc/start.php';
include_once '../../sys/inc/compress.php';
include_once '../../sys/inc/sess.php';
include_once '../../sys/inc/home.php';
include_once '../../sys/inc/settings.php';
include_once '../../sys/inc/db_connect.php';
include_once '../../sys/inc/ipua.php';
include_once '../../sys/inc/fnc.php';
include_once '../../sys/inc/user.php';
if (isset ($user) && $user['level'] < 3)
header("Location: /");
if (isset($_GET['del']) && $db->query("SELECT COUNT(*) FROM `rules_p` WHERE `id` = '".intval($_GET['del'])."'")==1)
{
	$post=$db->query("SELECT * FROM `rules_p` WHERE `id` = '".intval($_GET['del'])."' LIMIT 1")->row();
	$ank=$db->query("SELECT * FROM `user` WHERE `id` = $post[id_user] LIMIT 1")->row();
	if (isset($user) && ($user['level']>$ank['level']))
	$db->query("DELETE FROM `rules_p` WHERE `id` = '$post[id]'");
}
if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']!=NULL)
header("Location: ".$_SERVER['HTTP_REFERER']);
else
header("Location: post.php?".SID);
?>