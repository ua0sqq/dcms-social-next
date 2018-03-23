<?php
// Вывод активности на стене
function stena($id_us = null, $id = null)
{
    global $webbrowser;
    if ($post = go\DB\query("SELECT `type`, `info_1` FROM `stena` WHERE `id`=?i AND `info_1`<>0", [$id])->row()) {
        // Определяем автора комментария
        $ank_stena = go\DB\query("SELECT `id`, `pol` FROM `user` WHERE `id`=?i", [$id_us])->row();
        // Если смена аватара
        if ($post['type'] == 'foto') {
            echo ' <span style="color:darkgreen;">установил' . ($ank_stena['pol'] == 0 ? 'а' : null) . ' новый аватар на своей страничке</span><br/>' . "\n";
            $foto = go\DB\query("SELECT `id`,`id_gallery`,`ras` FROM `gallery_foto` WHERE `id`=?i", [$post['info_1']])->row();
            echo '<a href="/foto/' . $ank_stena['id'] . '/' . $foto['id_gallery'] . '/' . $foto['id'] . '/"><img class="stenka" style="width:' .
             ($webbrowser ? '240px;' : '60px;') . '" src="/foto/foto0/' . $foto['id'] . '.' . $foto['ras'] . '"></a>';
        // Если новый дневник
        } elseif ($post['type'] == 'note') {
            if (!$notes = go\DB\query("SELECT `id`,`name`,`msg` FROM `notes` WHERE `id`=?i", [$post['info_1']])->row()) {
                echo ' <span style="color:#666;">написал' . ($ank_stena['pol']==0 ? 'a' : null) . ' новый дневник, который был удалён.</span>';
            } else {
                echo ' <span style="color:darkgreen;">создал' . ($ank_stena['pol']==0 ? 'a' : null) . ' новую запись у себя в дневнике</span><br/>' . "\n";
                echo '<a href="/plugins/notes/list.php?id=' . $notes['id'] . '"><b style="color:#999;">' . text($notes['name']) . '</b></a><br/>';
                echo '<span style="color:#666;">' . rez_text($notes['msg'], 82) . '</span>';
            }
            // Если это тема форума
        } elseif ($post['type'] == 'them') {
            if (!$them = go\DB\query("SELECT `id`,`id_forum`,`id_razdel`,`name`,`text` FROM `forum_t` WHERE `id`=?i", [$post['info_1']])->row()) {
                echo ' <span style="color:#666;">написал' . ($ank_stena['pol']==0 ? 'a' : null) . ' тему в форуме, которая была удалена.</span>';
            } else {
                echo ' <span style="color:darkgreen;">создал' . ($ank_stena['pol']==0 ? 'a' : null) . ' новую тему в форуме</span><br/>';
                echo ' <a href="/forum/' . $them['id_forum'] . '/' . $them['id_razdel'] . '/' . $them['id'] . '/"><b style="color:#999;">' . text($them['name']) . '</b></a><br/>';
                echo ' <span style="color:#666;">' . rez_text($them['text'], 82) . '</span>';
            }
        }
    }
}
