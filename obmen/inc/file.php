<?php
if (is_file(H."sys/obmen/screens/128/$file_id[id].gif"))
{
echo "<img src='/sys/obmen/screens/128/$file_id[id].gif' alt='Скрин...' /><br />\n";
}
	if ($file_id['opis']!=NULL)
	{
		echo "Описание: ";
		echo output_text($file_id['opis']);
		echo "<br />\n";
	}
	else 
		echo 'Без описания <br />';
	$ank=$db->query("SELECT * FROM `user` WHERE `id` = '$file_id[id_user]' LIMIT 1")->row();
	
	echo "
		  Размер: ".size_file($file_id['size'])."<br />
		  Выгрузил: <a href='/info.php?id=$ank[id]'>$ank[nick]</a>
		  ".vremja($file_id['time'])."\n";
?>