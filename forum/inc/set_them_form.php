<?php
if (isset($_GET['act']) && $_GET['act']=='post_delete' && (user_access('forum_post_ed') || (isset($user) && $ank2['id']==$user['id'])))
{
echo "<input value=\"Удалить выбранные посты\" type=\"submit\" /> \n";
echo "<a href='/forum/$forum[id]/$razdel[id]/$them[id]/'><img src='/style/icons/delete.gif' alt='*'> Отмена</a> \n";
echo "</form>\n";
}
echo "<div class=\"foot\">\n";
if ((!isset($_GET['act']) || $_GET['act']!='post_delete') && (user_access('forum_post_ed') || (isset($user) && $ank2['id']==$user['id']))){
echo "<a href='/forum/$forum[id]/$razdel[id]/$them[id]/?act=post_delete'><img src='/style/forum/inc/trun.png' alt='*'></a> | \n";
}
echo '<a href="txt"><img src="/style/forum/inc/txt.png" alt="*"></a> ';
echo "</div>\n";
?>