<?php
include_once '../../sys/inc/start.php';
include_once '../../sys/inc/compress.php';
include_once '../../sys/inc/sess.php';
include_once '../../sys/inc/home.php';
include_once '../../sys/inc/settings.php';
include_once '../../sys/inc/db_connect.php';
include_once '../../sys/inc/ipua.php';
include_once '../../sys/inc/fnc.php';
include_once '../../sys/inc/user.php';

only_reg();
$set['title']='Настройка ленты';
include_once '../../sys/inc/thead.php';
title();

if (filter_input(INPUT_POST, 'save', FILTER_DEFAULT)) {
    $input_post = filter_input_array(INPUT_POST, FILTER_VALIDATE_INT);
    unset($input_post['save']);
    foreach($input_post as $key => $val) {
        $set_tape[$key] = $val ? 1 : 0;
    }
    try {
        $db->query('UPDATE `tape_set` SET ?s WHERE `id_user`=?i',
                   [$set_tape, $user['id']]);
        $_SESSION['message'] = 'Изменения успешно приняты';
    } catch (go\DB\Exceptions\Query $e) {
        $_SESSION['err'] = 'Bad Request';
    }
    header('Location: settings.php');
    exit;
}

$lentaSet = $db->query(
    "SELECT * FROM `tape_set` WHERE `id_user`=?i LIMIT ?i",
                       [$user['id'], 1])->row();

err();
aut();
echo "<div id='comments' class='menus'>";
echo "<div class='webmenu'>";
echo "<a href='/user/info/settings.php'>Общие</a>";
echo "</div>";
echo "<div class='webmenu last'>";
echo "<a href='/user/tape/settings.php' class='activ'>Лента</a>";
echo "</div>";
echo "<div class='webmenu last'>";
echo "<a href='/user/discussions/settings.php'>Обсуждения</a>";
echo "</div>";
echo "<div class='webmenu last'>";
echo "<a href='/user/notification/settings.php'>Уведомления</a>";
echo "</div>";
echo "<div class='webmenu last'>";
echo "<a href='/user/info/settings.privacy.php' >Приватность</a>";
echo "</div>";
echo "<div class='webmenu last'>";
echo "<a href='/user/info/secure.php' >Пароль</a>";echo "</div>";
echo "</div>";
echo "<form action='?' method=\"post\">";
 // Лента друзей
echo "<div class='mess'>";
echo "Уведомления о новых друзьях";
echo "</div>";
echo "<div class='nav1'>";
echo "<input name='lenta_frends' type='radio' ".($lentaSet['lenta_frends']==1?' checked="checked"':null)." value='1' /> Да ";
echo "<input name='lenta_frends' type='radio' ".($lentaSet['lenta_frends']==0?' checked="checked"':null)." value='0' /> Нет ";
echo "</div>";
 // Лента Дневников
echo "<div class='mess'>";
echo "Уведомления о новых дневниках";
echo "</div>";
echo "<div class='nav1'>";
echo "<input name='lenta_notes' type='radio' ".($lentaSet['lenta_notes']==1?' checked="checked"':null)." value='1' /> Да ";
echo "<input name='lenta_notes' type='radio' ".($lentaSet['lenta_notes']==0?' checked="checked"':null)." value='0' /> Нет ";
echo "</div>";
 // Лента темах
echo "<div class='mess'>";
echo "Уведомления о новых темах в форуме";
echo "</div>";
echo "<div class='nav1'>";
echo "<input name='lenta_forum' type='radio' ".($lentaSet['lenta_forum']==1?' checked="checked"':null)." value='1' /> Да ";
echo "<input name='lenta_forum' type='radio' ".($lentaSet['lenta_forum']==0?' checked="checked"':null)." value='0' /> Нет ";
echo "</div>";
 // Лента фото
echo "<div class='mess'>";
echo "Уведомления о новых фото";
echo "</div>";
echo "<div class='nav1'>";
echo "<input name='lenta_foto' type='radio' ".($lentaSet['lenta_foto']==1?' checked="checked"':null)." value='1' /> Да ";
echo "<input name='lenta_foto' type='radio' ".($lentaSet['lenta_foto']==0?' checked="checked"':null)." value='0' /> Нет ";
echo "</div>";
 // Лента о смене аватара
echo "<div class='mess'>";
echo "Уведомления о смене аватаров";
echo "</div>";
echo "<div class='nav1'>";
echo "<input name='lenta_avatar' type='radio' ".($lentaSet['lenta_avatar']==1?' checked="checked"':null)." value='1' /> Да ";
echo "<input name='lenta_avatar' type='radio' ".($lentaSet['lenta_avatar']==0?' checked="checked"':null)." value='0' /> Нет ";
echo "</div>";
 // Лента файлов
echo "<div class='mess'>";
echo "Уведомления о новых файлах";
echo "</div>";
echo "<div class='nav1'>";
echo "<input name='lenta_files' type='radio' ".($lentaSet['lenta_files']==1?' checked="checked"':null)." value='1' /> Да ";
echo "<input name='lenta_files' type='radio' ".($lentaSet['lenta_files']==0?' checked="checked"':null)." value='0' /> Нет ";
echo "</div>";
 // Лента статусов
echo "<div class='mess'>";
echo "Уведомления о новых статусах";
echo "</div>";
echo "<div class='nav1'>";
echo "<input name='lenta_status' type='radio' ".($lentaSet['lenta_status']==1?' checked="checked"':null)." value='1' /> Да ";
echo "<input name='lenta_status' type='radio' ".($lentaSet['lenta_status']==0?' checked="checked"':null)." value='0' /> Нет ";
echo "</div>";
 // Лента оценок статуса
echo "<div class='mess'>";
echo "Уведомления о \"Like\" к статусам друзей";
echo "</div>";
echo "<div class='nav1'>";
echo "<input name='lenta_status_like' type='radio' ".($lentaSet['lenta_status_like']==1?' checked="checked"':null)." value='1' /> Да ";
echo "<input name='lenta_status_like' type='radio' ".($lentaSet['lenta_status_like']==0?' checked="checked"':null)." value='0' /> Нет ";
echo "</div>";
echo "<div class='main'>";
echo "<input type='submit' name='save' value='Сохранить' />";
echo "</div>";
echo "</form>";
echo "<div class=\"foot\">\n";
echo "<img src='/style/icons/str2.gif' alt='*'> <a href='/info.php?id=$user[id]'>$user[nick]</a> | \n";
echo '<b>Лента</b>';
echo "</div>\n";
    
include_once '../../sys/inc/tfoot.php';
