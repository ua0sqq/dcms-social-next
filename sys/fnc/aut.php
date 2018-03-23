<?php
function title()
{
    global $user;

    if (isset($user)) {
        global $set;
        
        if ($set['web'] == false) {
            ?><table style="width:100%" cellspacing="0" cellpadding="0"><tr><?php
            $cnt = go\DB\query(
			
                'SELECT (SELECT COUNT(`mail`.`id`) FROM `mail`
LEFT JOIN `users_konts` ON `mail`.`id_user` = `users_konts`.`id_kont` AND `users_konts`.`id_user` = ?i
WHERE `mail`.`id_kont` = ?i
AND (`users_konts`.`type` IS NULL OR `users_konts`.`type` = "common" OR `users_konts`.`type` = "favorite") AND `mail`.`read` = "0") k_new, (
SELECT COUNT(`mail`.`id`) FROM `mail`
LEFT JOIN `users_konts` ON `mail`.`id_user` = `users_konts`.`id_kont` AND `users_konts`.`id_user` = ?i
WHERE `mail`.`id_kont` = ?i AND (`users_konts`.`type` = "favorite") AND `mail`.`read` = "0") k_new_fav, (
SELECT COUNT(`read`) FROM `tape` WHERE `id_user` = ?i AND `read` = "0") lenta, (
SELECT COUNT(`count`) FROM `discussions` WHERE `id_user` = ?i AND `count` <> "0") discuss, (
SELECT COUNT(id) FROM `frends_new` WHERE `to` = ?i) k_frend, (
SELECT COUNT(`read`) FROM `notification` WHERE `id_user` = ?i AND `read` = "0") k_notif',
             [$user['id'], $user['id'], $user['id'], $user['id'], $user['id'], $user['id'], $user['id'], $user['id']]
			
            )->row();
            
            // Страничка
            ?>
			<td class="auts">
			<a href="/info.php?id=<?=$user['id']?>"><center><img src="/style/icons/nav_stranica.gif" alt="DS" /></center></a>
			</td>
			<?php
            // Почта
            ?><td class="auts"><?php
            if ($cnt['k_new'] != 0 && $cnt['k_new_fav'] == 0) {
                ?><a href="/new_mess.php"><center><img src="/style/icons/icon_pochta22.gif" alt="DS" /><font color="#ff0000">(<?=$cnt['k_new']?>)</font></center></a><?php
            } else {
                ?><a href="/konts.php"><center><img src="/style/icons/nav_pochta.gif" alt="S" /></center></a><?php
            } ?></td><?php
            // Лента
            if ($cnt['lenta'] > 0) {
                $j2 = 'tape';
            } elseif ($cnt['discuss'] > 0) {
                $j2 = 'discussions';
            } elseif ($cnt['k_notif'] > 0) {
                $j2 = 'notification';
            } else {
                $j2 = 'tape';
            } ?>
			<td class='auts'>
			<a href="/user/<?=$j2?>/index.php"><center><img src="/style/icons/nav_lenta.gif" alt="DS" />
			<?php
            // Cкладываем сумму счетчиков
            $k_l = $cnt['lenta'] + $cnt['k_notif'] + $cnt['discuss'];
            
            if ($k_l > 0) {
                ?>
				<font color="#ff0000">(<?=$k_l?>)</font>
				<?php
            } ?>
			</center></a>
			</td>
			<?php
            // Друзья
            if ($cnt['k_frend'] > 0) {
                ?>
				<td class='auts'>
				<a href="/user/frends/new.php"><center><img src="/style/icons/icon_druzya.gif" alt="DS" /><font color='red'>(<?=$cnt['k_frend']?>)</font></center></a>
				</td>
				<?php
            }
            
            // Обновить
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