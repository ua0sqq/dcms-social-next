<?php
function online($user = NULL)
{
	global $set, $time;
	static $users;
	
	if (!isset($users[$user]))
	{
		if (go\DB\query("SELECT COUNT(id) FROM `user` WHERE `id` = '$user' AND `date_last` > '" . (time()-600) . "' LIMIT 1")->el())
		{
			if ($set['show_away'] == 0)$on = 'online';
			else
			{
				$ank = go\DB\query("SELECT `date_last` FROM `user` WHERE `id` = '$user' LIMIT 1")->row();
				if ((time() - $ank['date_last']) == 0)
				$on = 'online';
				else
				$on = 'away: ' . (time()-$ank['date_last']) . ' сек';
			}
			$ank = go\DB\query("SELECT * FROM `user` WHERE `id` = '$user' LIMIT 1")->row();
			if ($ank['browser'] == 'wap')
				$users[$user] = " <img src='/style/icons/online.gif' alt='*' /> ";
			else
				$users[$user] = " <img src='/style/icons/online_web.gif' alt='*' /> ";
		}
		else
		{
			$users[$user]=null;
		}
	}
	return $users[$user];
}
?>