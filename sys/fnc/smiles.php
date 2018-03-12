<?php
function smiles($msg)
{
	global $user;
	$q = go\DB\query("SELECT `id`, `smile` FROM `smile`");
	while($post = $q->row())
	{
		$sm = explode("|", $post['smile']);
		for ($i = 0; $i < count($sm); $i++)
		{
			$msg = str_replace($sm[$i], '<img src="/style/smiles/' . $post['id'] . '.gif" alt="smile" />', $msg);
		}
	}
	return $msg;
}
?>