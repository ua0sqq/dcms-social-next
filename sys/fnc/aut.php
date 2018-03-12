<?php
function title()
{
	global $user;	
	
	if (isset($user))
	{
		global $set;		
		
		if ($set['web'] == false)
		{			
			?><table style="width:100%" cellspacing="0" cellpadding="0"><tr><?php
		
			$k_new = go\DB\query("SELECT COUNT(`mail`.`id`) FROM `mail`
			 LEFT JOIN `users_konts` ON `mail`.`id_user` = `users_konts`.`id_kont` AND `users_konts`.`id_user` = '$user[id]'
			 WHERE `mail`.`id_kont` = '$user[id]' AND (`users_konts`.`type` IS NULL OR `users_konts`.`type` = 'common' OR `users_konts`.`type` = 'favorite') AND `mail`.`read` = '0'")->el();
			 
			$k_new_fav = go\DB\query("SELECT COUNT(`mail`.`id`) FROM `mail`
			 LEFT JOIN `users_konts` ON `mail`.`id_user` = `users_konts`.`id_kont` AND `users_konts`.`id_user` = '$user[id]'
			 WHERE `mail`.`id_kont` = '$user[id]' AND (`users_konts`.`type` = 'favorite') AND `mail`.`read` = '0'")->el(); // Почта			
			 
			 $lenta = go\DB\query("SELECT COUNT(`read`) FROM `tape` WHERE `id_user` = '$user[id]' AND `read` = '0' ")->el(); // Лента			
			 
			 $discuss = go\DB\query("SELECT COUNT(`count`) FROM `discussions` WHERE `id_user` = '$user[id]' AND `count` > '0' ")->el(); // Обсуждения			
			 
			 $k_frend = go\DB\query("SELECT COUNT(id) FROM `frends_new` WHERE `to` = '$user[id]'")->el(); // Друзья			
			 
			 $k_notif = go\DB\query("SELECT COUNT(`read`) FROM `notification` WHERE `id_user` = '$user[id]' AND `read` = '0'")->el(); // Уведомления			 
			 
			/*
			=================================
			Страничка
			=================================
			*/			
			
			?>
			<td class="auts">
			<a href="/info.php?id=<?=$user['id']?>"><center><img src="/style/icons/nav_stranica.gif" alt="DS" /></center></a>
			</td>
			<?php
			
			/*
			=================================
			Почта
			=================================
			*/			
			
			?><td class="auts"><?php
			if ($k_new != 0 && $k_new_fav == 0)
			{				
				?><a href="/new_mess.php"><center><img src="/style/icons/icon_pochta22.gif" alt="DS" /><font color="#ff0000">(<?=$k_new?>)</font></center></a><?php
			}
			else
			{
				?><a href="/konts.php"><center><img src="/style/icons/nav_pochta.gif" alt="S" /></center></a><?php
			}
			?></td><?php
			
			/*
			=================================
			Лента
			=================================
			*/
			
			if ($lenta > 0)
			{
				$j2 = 'tape';
			}
			elseif ($discuss > 0)
			{
				$j2 = 'discussions';
			}
			elseif ($k_notif > 0)
			{
				$j2 = 'notification';
			}
			else
			{
				$j2 = 'tape';
			}
			
			?>
			<td class='auts'>
			<a href="/user/<?=$j2?>/index.php"><center><img src="/style/icons/nav_lenta.gif" alt="DS" />
			<?php
			// Cкладываем сумму счетчиков
			$k_l = $lenta + $k_notif + $discuss;
			
			if($k_l > 0)
			{
				?>
				<font color="#ff0000">(<?=$k_l?>)</font>
				<?php
			}
			?>
			</center></a>
			</td>
			<?php
			/*
			=================================
			Друзья
			=================================
			*/			 
			
			if ($k_frend > 0)
			{
				?>
				<td class='auts'>
				<a href="/user/frends/new.php"><center><img src="/style/icons/icon_druzya.gif" alt="DS" /><font color='red'>(<?=$k_frend?>)</font></center></a>
				</td>
				<?php
			}			
			
			/*
			=================================
			Обновить
			=================================
			*/
			
			?>
			<td class='auts'>
			<a href="<?=text($_SERVER['REQUEST_URI'])?>"><center><img src="/style/icons/nav_obnovit.gif" alt="DS" /></center></a>
			</td>		
			</tr></table>
			<?php
		}
	}
}
?>