<?php
function aut($title=NULL)
{
	global $set;
	if ($set['web'] == false)
	{
		if ($title == NULL)
			$title = $set['title'];
		echo "\n".'<table cellspacing="0" cellpadding="0">'."\n".
		'<tr>'."\n";
		if ($_SERVER['PHP_SELF'] != '/index.php')
		{
			echo '<td class="titles">'."\n";
			echo '<a href="/index.php"><img src="/style/icons/icon_glavnaya.gif" alt="DS" /></a>'."\n";
			echo '</td>'."\n"; 
		}
		
		echo '<td class="title">'."\n" . $title . "\n".'</td>'."\n";
		echo '</table>'."\n";
	}
}
?>