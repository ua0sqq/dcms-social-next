<?php
if ($set['web']) {
    ?>
<table width='100%' border='1' style='margin:5px;'>
<tr class='forum_file_table_title'>
<td width='14'>
</td>
<td>
Файл
</td>
<td>
Тип
</td>
<td width='50'>
Размер
</td>
<td width='50'>
Скачано
</td>
<td width='50'>
Рейтинг
</td>
<td width='50'>
</td>
<?php
if (isset($user) && $user['level']>1) {
        echo "<td width='14'></td>\n";
    } ?>
</tr>
<?php
    $q_f = $db->query("SELECT `frf`.*, (
SELECT COUNT( * ) FROM `forum_files_rating` WHERE `id_file`=`frf`.`id`) rating, (
SELECT SUM(`rating`) FROM `forum_files_rating` WHERE `id_file`=`frf`.`id`) sum_rating, (
SELECT COUNT( * ) FROM `forum_files_rating` WHERE `id_user` = '$user[id]' AND `id_file`=`frf`.`id`) user_rating
FROM `forum_files` `frf` WHERE `frf`.`id_post`=?i", [$post['id']]);
    while ($file = $q_f->row()) {
        echo "<tr class='forum_file_table_file'>\n";
        echo "<td>\n";
        if (is_file(H.'style/themes/'.$set['set_them'].'/loads/14/'.$file['ras'].'.png')) {
            echo "<img src='/style/themes/$set[set_them]/loads/14/$file[ras].png' alt='$file[ras]' />\n";
            if ($set['echo_rassh_forum']==1) {
                $ras=".$file[ras]";
            } else {
                $ras=null;
            }
        } else {
            echo "<img src='/style/themes/$set[set_them]/forum/14/file.png' alt='' />\n";
            $ras=".$file[ras]";
        }
        echo "</td>\n";
        echo "<td>$file[name]$ras</td>\n";
        echo "<td>$file[type]</td>\n";
        echo "<td>".size_file($file['size'])."</td>\n";
        if (!isset($file['count'])) {
            $db->query("ALTER TABLE `forum_files` ADD `count` INT DEFAULT '0' NOT NULL");
        }
        echo "<td style='text-align:center;'>$file[count]</td>\n";
        echo "<td style='text-align:center;'> ";
        $k_vote = $file['rating'];
        $sum_vote = $file['sum_rating'];
        if ($sum_vote==null) {
            $sum_vote=0;
        }
        if (isset($user) && $user['balls']>=50 && $user['rating']>=0 && !$file['user_rating']) {
            echo "<a href=\"/forum/$forum[id]/$razdel[id]/$them[id]/?page=$page&amp;id_file=$file[id]&amp;rating=down\" title=\"Отдать отрицательный голос\">[-]</a>";
        }
        echo "&nbsp;$sum_vote/$k_vote&nbsp;";
        if (isset($user) && $user['balls']>=50 && $user['rating']>=0 && !$file['user_rating']) {
            echo "<a href=\"/forum/$forum[id]/$razdel[id]/$them[id]/?page=$page&amp;id_file=$file[id]&amp;rating=up\" title=\"Отдать положительный голос\">[+]</a>";
        }
        echo "</td>\n";
        echo "<td><a href='/forum/files/$file[id]/$file[name].$file[ras]'>Скачать</a></td>\n";
        if (isset($user) && $user['level']>1) {
            echo "<td><a href='/forum/files/delete/$file[id]/' title='Удалить из списка'><img src='/style/themes/$set[set_them]/forum/14/del_file.png' alt='' /></a></td>\n";
        }
        echo "</tr>\n";
    }
} else {
    $q_f = $db->query("SELECT `frf`.*, (
SELECT COUNT( * ) FROM `forum_files_rating` WHERE `id_file`=`frf`.`id`) rating, (
SELECT SUM(`rating`) FROM `forum_files_rating` WHERE `id_file`=`frf`.`id`) sum_rating, (
SELECT COUNT( * ) FROM `forum_files_rating` WHERE `id_user` = '$user[id]' AND `id_file`=`frf`.`id`) user_rating
FROM `forum_files` `frf` WHERE `frf`.`id_post`=?i", [$post['id']]);
    while ($file = $q_f->row()) {
        if (is_file(H.'style/themes/'.$set['set_them'].'/loads/14/'.$file['ras'].'.png')) {
            echo "<img src='/style/themes/$set[set_them]/loads/14/$file[ras].png' alt='$file[ras]' />\n";
            if ($set['echo_rassh_forum']==1) {
                $ras=".$file[ras]";
            } else {
                $ras=null;
            }
        } else {
            echo "<img src='/style/themes/$set[set_them]/forum/14/file.png' alt='' />\n";
            $ras=".$file[ras]";
        }
        echo "<a href='/forum/files/$file[id]/$file[name].$file[ras]'>$file[name]$ras</a> (".size_file($file['size']).") \n";
        $k_vote = $file['rating'];
        $sum_vote = $file['sum_rating'];
        if ($sum_vote == null) {
            $sum_vote = 0;
        }
        if (isset($user) && $user['level'] > 1) {
            echo "<a href='/forum/files/delete/$file[id]/' title='Удалить из списка'><img src='/style/themes/$set[set_them]/forum/14/del_file.png' alt='' /></a>\n";
        }
        echo "<br />\n";
        echo "Рейтинг: ";
        if (isset($user) && $user['balls'] >= 50 && $user['rating']>=0 && !$file['user_rating']) {
            echo "<a href=\"/forum/$forum[id]/$razdel[id]/$them[id]/?page=$page&amp;id_file=$file[id]&amp;rating=down\" title=\"Отдать отрицательный голос\">[-]</a>";
        }
        echo "&nbsp;$sum_vote/$k_vote&nbsp;";
        if (isset($user) && $user['balls']>=50 && $user['rating']>=0 && !$file['user_rating']) {
            echo "<a href=\"/forum/$forum[id]/$razdel[id]/$them[id]/?page=$page&amp;id_file=$file[id]&amp;rating=up\" title=\"Отдать положительный голос\">[+]</a>";
        }
        echo " | ";
        echo "Скачано: $file[count] раз(а) ";
        echo "<br />\n";
    }
}
?>