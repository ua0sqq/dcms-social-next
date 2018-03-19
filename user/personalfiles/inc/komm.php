<?php
/*
=======================================
Личные файлы юзеров для Dcms-Social
Автор: Искатель
---------------------------------------
Этот скрипт распостроняется по лицензии
движка Dcms-Social. 
При использовании указывать ссылку на
оф. сайт http://dcms-social.ru
---------------------------------------
Контакты
ICQ: 587863132
http://dcms-social.ru
=======================================
*/
$k_post=$db->query("SELECT COUNT(*) FROM `obmennik_komm` WHERE `id_file` = '$file_id[id]'")->el();
$k_page=k_page($k_post,$set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
echo "<table class='post'>\n";
echo '<div class="foot">';
echo "Комментарии:";
echo '</div>';
if ($k_post==0)
{
echo '<div class="mess">';
echo "Нет сообщений\n";
echo '</div>';
}
else if (isset($user))
{
	/*------------сортировка по времени--------------*/
	if (isset($user)){
	echo "<div id='comments' class='menus'>";
	echo "<div class='webmenu'>";
	echo "<a href='?id_file=$file_id[id]&amp;sort=1' class='".($user['sort']==1?'activ':'')."'>Внизу</a>";
	echo "</div>"; 
	echo "<div class='webmenu'>";
	echo "<a href='?id_file=$file_id[id]&amp;sort=0' class='".($user['sort']==0?'activ':'')."'>Вверху</a>";
	echo "</div>"; 
	echo "</div>";
	}
	/*---------------alex-borisi---------------------*/
}
$q=$db->query("SELECT * FROM `obmennik_komm` WHERE `id_file` = '$file_id[id]' ORDER BY `id` $sort LIMIT $start, $set[p_str]");
while ($post = $q->row())
{
	$anketa=$db->query("SELECT * FROM `user` WHERE `id` = '$post[id_user]' LIMIT 1")->row();
/*-----------зебра-----------*/ 
	if ($num==0){
		echo '<div class="nav1">';
		$num=1;
	}
	elseif ($num==1){
		echo '<div class="nav2">';
		$num=0;
	}
/*---------------------------*/
	echo " ".group($anketa['id'])." <a href='/info.php?id=$anketa[id]'>$anketa[nick]</a>";
	if (isset($user) && $anketa['id'] != $user['id'])echo ' <a href="?id_file='.$file_id['id'].'&amp;page='.$page.'&amp;response='.$anketa['id'].'">[*]</a> ';
	echo "".online($anketa['id'])." (".vremja($post['time']).")<br />\n";
	
$postBan = $db->query("SELECT COUNT(*) FROM `ban` WHERE (`razdel` = 'all' OR `razdel` = 'files') AND `post` = '1' AND `id_user` = '$anketa[id]' AND (`time` > '$time' OR `navsegda` = '1')")->el();
if ($postBan == 0) // Блок сообщения
{	
	echo esc(trim(br(bbcode(smiles(links(stripcslashes(htmlspecialchars($post['msg']))))))))."<br />\n";
}else{
	echo output_text($banMess).'<br />';
}
if (isset($user))
{
echo '<div style="text-align:right;">';
	if ($anketa['id']!=$user['id'])
		echo "<a href=\"?id_file=$file_id[id]&amp;page=$page&amp;spam=$post[id]\"><img src='/style/icons/blicon.gif' alt='*' title='Это спам'></a> "; 
	if (user_access('obmen_komm_del') || $anketa['id'] == $user['id'])
		echo '<a href="?id_file='.$file_id['id'].'&amp;page='.$page.'&amp;del_post='.$post['id'].'"><img src="/style/icons/delete.gif" alt="*"></a>';
	echo "   </div>\n";
}
	echo "   </div>\n";
}
echo "</table>\n";
if ($k_page>1)str('?id_file='.$file_id['id'].'&amp;',$k_page,$page); // Вывод страниц
if (isset($user))
{
echo "<form method=\"post\" name='message' action=\"?id_file=$file_id[id]".$go_link."\">\n";
if ($set['web'] && is_file(H.'style/themes/'.$set['set_them'].'/altername_post_form.php'))
include_once H.'style/themes/'.$set['set_them'].'/altername_post_form.php';
else
echo "$tPanel<textarea name=\"msg\">$insert</textarea><br />\n";
echo "<input value=\"Отправить\" type=\"submit\" />\n";
echo "</form>\n";
}
?>