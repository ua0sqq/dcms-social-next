<?php
if (user_access('chat_room') && isset($_GET['set']) && is_numeric($_GET['set'])
    && $db->query("SELECT COUNT(*) FROM `chat_rooms` WHERE `id`=?i",
                  [$_GET['set']])->el()) {
    $room = $db->query("SELECT * FROM `chat_rooms` WHERE `id`=?i",
                     [$_GET['set']])->row();
    echo "<form action='?set=$room[id]&amp;ok' method='post'>";
    echo "Название комнаты:<br />\n<input type='text' name='name' value='$room[name]' /><br />\n";
    echo "Позиция:<br />\n<input type='text' name='pos' value='$room[pos]' /><br />\n";
    echo "Описание:<br />\n<input type='text' name='opis' value='$room[opis]' /><br />\n";
    echo "Боты:<br />\n<select name=\"bots\">\n";
    echo "<option value='0'".(($room['umnik']==0 && $room['shutnik']==0)?' selected="selected"':null).">Нет</option>\n";
    echo "<option value='1'".(($room['umnik']==1 && $room['shutnik']==0)?' selected="selected"':null).">$set[chat_umnik]</option>\n";
    echo "<option value='2'".(($room['umnik']==0 && $room['shutnik']==1)?' selected="selected"':null).">$set[chat_shutnik]</option>\n";
    echo "<option value='3'".(($room['umnik']==1 && $room['shutnik']==1)?' selected="selected"':null).">$set[chat_umnik] и $set[chat_shutnik]</option>\n";
    echo "</select><br />\n";
    echo "<input class='submit' type='submit' value='Применить' /><br />\n";
    echo "<a href='?delete=$room[id]'>Удалить</a><br />\n";
    echo "<a href='?cancel=$passgen'>Отмена</a><br />\n";
    echo "</form>";
}
if (user_access('chat_clear') && isset($_GET['act']) && $_GET['act']=='clear') {
    echo "<div class=\"err\">";
    echo "Очистить чат?<br />\n";
    echo "<a href=\"?act=clear2\">Да</a> \n";
    echo "<a href=\"?\">Нет</a><br />\n";
    echo "</div>";
}
if (isset($user) && $user['group_access'] > 0 && user_access('chat_room')) {
    $cnt_chat_rooms = $db->query('SELECT COUNT(*) FROM `chat_rooms`')->el();
}
if (user_access('chat_room') && (isset($_GET['act']) && $_GET['act']=='add_room'
                                 || !$cnt_chat_rooms)) {
    echo "<form class=\"foot\" action=\"?act=add_room&amp;ok\" method=\"post\">";
    echo "Название комнаты:<br />\n";
    echo "<input type='text' name='name' value='' /><br />\n";
    $pos=$db->query("SELECT MAX(`pos`) FROM `chat_rooms`")->el()+1;
    echo "Позиция:<br />\n";
    echo "<input type='text' name='pos' value='$pos' /><br />\n";
    echo "Описание:<br />\n";
    echo "<input type='text' name='opis' value='' /><br />\n";
    echo "Боты:<br />\n<select name=\"bots\">\n";
    echo "<option value='0'>Нет</option>\n";
    echo "<option value='1'>$set[chat_umnik]</option>\n";
    echo "<option value='2'>$set[chat_shutnik]</option>\n";
    echo "<option value='3'>$set[chat_umnik] и $set[chat_shutnik]</option>\n";
    echo "</select><br />\n";
    echo "<input class=\"submit\" type=\"submit\" value=\"Создать комнату\" /><br />\n";
    echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"?\">Отмена</a><br />\n";
    echo "</form>";
}
echo "<div class=\"foot\">\n";
if (user_access('chat_clear')) {
    echo "<img src='/style/icons/str.gif' alt='*'> <a href=\"?act=clear\">Очистить чат от сообщений</a><br />\n";
}
if (user_access('chat_room') && $cnt_chat_rooms) {
    echo "<img src='/style/icons/str.gif' alt='*'> <a href=\"?act=add_room\">Создать комнату</a><br />\n";
}
echo "</div>\n";
