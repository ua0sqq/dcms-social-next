<?php
echo "<form method='post' action='?newsearch=$passgen'>\n";
echo "Текст:<br />\n<input type='text' name='text' value='".htmlentities($searched['text'], ENT_QUOTES, 'UTF-8')."' /><br />\n";
echo "Место поиска:<br />\n<select name='in'>\n";
echo "<option value=''>Везде</option>\n";
$q = $db->query("SELECT `id`,`name` FROM `forum_f`".((!isset($user) || $user['level']==0)?" WHERE `adm` = '0'":null)." ORDER BY `pos` ASC");
while ($forums = $q->row())
{
	echo "<option value='f$forums[id]'".(($searched['in']['m']=='f' && $searched['in']['id']==$forums['id'])?" selected='selected'":null).">&gt;&gt; " . htmlspecialchars($forums['name']) . "</option>\n";
	
	$q2 = $db->query("SELECT `id`,`name` FROM `forum_r` WHERE `id_forum` = '$forums[id]' ORDER BY `time` DESC");
	
	while ($razdels = $q2->row())
	{
		echo "<option value='r$razdels[id]'".(($searched['in']['m']=='r' && $searched['in']['id']==$razdels['id'])?" selected='selected'":null).">&gt; " . htmlspecialchars($razdels['name']) . "</option>\n";
	}
}
echo "</select><br />\n";
echo "<input type='submit' value='Начать поиск' /><br />\n";
echo "</form>\n";
?>