<?php
include_once '../../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

only_reg('/aut.php');

if (!$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)) {
    header('Location: index.php?' . SID);
}

$ank = $db->query(
    'SELECT `id`, `nick` FROM `user` WHERE `id`=?i',
                [$id])->row();

if (!$ank || $ank['id'] == $user['id']) {
    header('Location: index.php?' . SID);
    exit;
}

$frend = $db->query(
    "SELECT * FROM `frends` WHERE `user`=?i AND `frend`=?i AND `i`=?i",
                    [$user['id'], $ank['id'], 1])->row();

if (filter_input(INPUT_POST, 'save', FILTER_DEFAULT)) {
    $input_post = filter_input_array(INPUT_POST, FILTER_VALIDATE_INT);
    unset($input_post['save']);
    foreach ($input_post as $key => $val) {
        $set_tape[$key] = $val ? 1 : 0;
    }
    try {
        $db->query(
            'UPDATE `frends` SET ?s WHERE `user`=?i AND `frend`=?i',
                   [$set_tape, $user['id'], $ank['id']]);
        $_SESSION['message'] = 'Изменения успешно приняты';
    } catch (go\DB\Exceptions\Query $e) {
        $_SESSION['err'] = 'Bad Request';
    }
    header('Location: index.php');
    exit;
}
$set['title']='Настройка ленты для '.$ank['nick'];
include_once H . 'sys/inc/thead.php';
title();
err();
aut();
echo "<div id='comments' class='menus'>";
echo "<div class='webmenu'>";
echo "<a href='index.php'>Лента</a>";
echo "</div>";
echo "<div class='webmenu'>";
echo "<a href='settings.php'>Настройки</a>";
echo "</div>";
echo "</div>";
echo "<form action='?id=$ank[id]' method=\"post\">";
 // Лента друзей
echo "<div class='mess'>";
echo "Уведомления о новых друзьях $ank[nick].";
echo "</div>";
echo "<div class='nav1'>";
echo "<input name='lenta_frends' type='radio' ".($frend['lenta_frends'] == 1?' checked="checked"':null)." value='1' /> Да ";
echo "<input name='lenta_frends' type='radio' ".($frend['lenta_frends'] == 0?' checked="checked"':null)." value='0' /> Нет ";
echo "</div>";
 // Лента Дневников
echo "<div class='mess'>";
echo "Уведомления о новых дневниках $ank[nick].";
echo "</div>";
echo "<div class='nav1'>";
echo "<input name='lenta_notes' type='radio' ".($frend['lenta_notes'] == 1?' checked="checked"':null)." value='1' /> Да ";
echo "<input name='lenta_notes' type='radio' ".($frend['lenta_notes'] == 0?' checked="checked"':null)." value='0' /> Нет ";
echo "</div>";
 // Лента Форума
echo "<div class='mess'>";
echo "Уведомления о новых темах $ank[nick] в форуме.";
echo "</div>";
echo "<div class='nav1'>";
echo "<input name='lenta_forum' type='radio' ".($frend['lenta_forum'] == 1?' checked="checked"':null)." value='1' /> Да ";
echo "<input name='lenta_forum' type='radio' ".($frend['lenta_forum'] == 0?' checked="checked"':null)." value='0' /> Нет ";
echo "</div>";
 // Лента фото
echo "<div class='mess'>";
echo "Уведомления о новых фото $ank[nick].";
echo "</div>";
echo "<div class='nav1'>";
echo "<input name='lenta_foto' type='radio' ".($frend['lenta_foto'] == 1?' checked="checked"':null)." value='1' /> Да ";
echo "<input name='lenta_foto' type='radio' ".($frend['lenta_foto'] == 0?' checked="checked"':null)." value='0' /> Нет ";
echo "</div>";
 // Лента о смене аватара
echo "<div class='mess'>";
echo "Уведомления о смене аватаров $ank[nick].";
echo "</div>";
echo "<div class='nav1'>";
echo "<input name='lenta_avatar' type='radio' ".($frend['lenta_avatar'] == 1?' checked="checked"':null)." value='1' /> Да ";
echo "<input name='lenta_avatar' type='radio' ".($frend['lenta_avatar'] == 0?' checked="checked"':null)." value='0' /> Нет ";
echo "</div>";
 // Лента файлов
echo "<div class='mess'>";
echo "Уведомления о новых файлах $ank[nick].";
echo "</div>";
echo "<div class='nav1'>";
echo "<input name='lenta_obmen' type='radio' ".($frend['lenta_obmen'] == 1?' checked="checked"':null)." value='1' /> Да ";
echo "<input name='lenta_obmen' type='radio' ".($frend['lenta_obmen'] == 0?' checked="checked"':null)." value='0' /> Нет ";
echo "</div>";
 // Лента статусов
echo "<div class='mess'>";
echo "Уведомления о новых статусах $ank[nick].";
echo "</div>";
echo "<div class='nav1'>";
echo "<input name='lenta_status' type='radio' ".($frend['lenta_status'] == 1?' checked="checked"':null)." value='1' /> Да ";
echo "<input name='lenta_status' type='radio' ".($frend['lenta_status'] == 0?' checked="checked"':null)." value='0' /> Нет ";
echo "</div>";
 // Лента оценок статуса
echo "<div class='mess'>";
echo "Уведомления о \"Like\" к статусам друзей $ank[nick].";
echo "</div>";
echo "<div class='nav1'>";
echo "<input name='lenta_status_like' type='radio' ".($frend['lenta_status_like'] == 1?' checked="checked"':null)." value='1' /> Да ";
echo "<input name='lenta_status_like' type='radio' ".($frend['lenta_status_like'] == 0?' checked="checked"':null)." value='0' /> Нет ";
echo "</div>";
echo "<div class='main'>";
echo "<input type='submit' name='save' value='Сохранить' />";
echo "</div>";
echo "</form>";
    
include_once H . 'sys/inc/tfoot.php';
