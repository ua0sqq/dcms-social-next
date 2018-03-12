<?php
if (isset($_GET['act']) && $_GET['act']=='mesto')
{
    echo "<form method=\"post\" action=\"/forum/$forum[id]/$razdel[id]/?act=mesto&amp;ok\">\n";
    echo "Подфорум:<br />\n";
    echo "<select name=\"forum\">\n";
    $q2 = $db->query("SELECT * FROM `forum_f` ORDER BY `pos` ASC");
    
    while ($forums = $q2->row())
    {
        if ($forum['id']==$forums['id'])$check=' selected="selected"';
        else 
        $check=NULL;
        
        echo '<option' . $check . ' value="' . $forums['id'] . '">' . text($forums['name']) . '</option>';
    }
    echo "</select><br />\n";
    echo "<input value=\"Переместить\" type=\"submit\" /><br />\n";
    echo "<img src='/style/icons/str2.gif' alt='*'> <a href='/forum/$forum[id]/$razdel[id]/'>Отмена</a><br />\n";
    echo "</form>\n";
}
if (isset($_GET['act']) && $_GET['act']=='set')
{
    echo "<form method=\"post\" action=\"/forum/$forum[id]/$razdel[id]/?act=set&amp;ok\">\n";
    echo "Название раздела:<br />\n";
    echo '<input name="name" type="text" maxlength="32" value="' . text($razdel['name']) . '" /><br />';
    echo "Описание<br/>\n";
	echo "<textarea name='opis' placeholder='Описание раздела'>" . text($razdel['opis']) . "</textarea><br/>";
    echo "<input value=\"Изменить\" type=\"submit\" /><br />\n";
    echo "<img src='/style/icons/str2.gif' alt='*'> <a href='/forum/$forum[id]/$razdel[id]/'>Отмена</a><br />\n";
    echo "</form>\n";
}
if (isset($_GET['act']) && $_GET['act']=='del')
{
    echo "<div class=\"err\">\n";
    echo "Подтвердите удаление раздела<br />\n";
    echo "<a href=\"/forum/$forum[id]/$razdel[id]/?act=delete&amp;ok\">Да</a> / <a href=\"/forum/$forum[id]/$razdel[id]/\">Нет</a><br />";
    echo "</div>\n";
}
echo "<div class=\"foot\">\n";
echo "<img src='/style/icons/str.gif' alt='*'> <a href='/forum/$forum[id]/$razdel[id]/?act=mesto'>Переместить раздел</a><br />\n";
echo "<img src='/style/icons/str.gif' alt='*'> <a href='/forum/$forum[id]/$razdel[id]/?act=del'>Удалить раздел</a><br />\n";
echo "<img src='/style/icons/str.gif' alt='*'> <a href='/forum/$forum[id]/$razdel[id]/?act=set'>Параметры раздела</a><br />\n";
echo "</div>\n";
?>