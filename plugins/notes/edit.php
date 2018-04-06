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

$set['title']='Дневники';
include_once '../../sys/inc/thead.php';
title();
aut();
$input_get = filter_input_array(INPUT_GET, FILTER_VALIDATE_INT);
if (!$db->query("SELECT COUNT( * ) FROM `notes` WHERE `id`=?i",
                [$input_get['id']])->el()) {
    header("Location: index.php?".SID);
    exit;
}

$notes=$db->query("SELECT n.*, u.id AS id_user, u.nick FROM `notes` n JOIN `user` u ON u.id=n.id_user WHERE n.`id`=?i",
                  [$input_get['id']])->row();
if (user_access('notes_edit') || $user['id'] == $notes['id_user']) {
    $avtor = [
              'id' => $notes['id_user'],
              'nick' => $notes['nick']
              ];
    if (isset($input_get['edit']) && isset($_POST['name']) && $_POST['name'] != null && isset($_POST['msg'])) {
        $msg = trim($_POST['msg']);
        $id_dir = $db->query('SELECT `id` FROM `notes_dir` WHERE `id`=?i', [$_POST['id_dir']])->el();
        $privat = isset($_POST['private']) ?  intval($_POST['private']) : 0;
        $privat_komm= isset($_POST['private_komm']) ? intval($_POST['private_komm']) : 0;
        $type=0;
        
        if ($_POST['name'] == null) {
            $name = mb_substr(esc($_POST['msg']), 0, 24);
        } else {
            $name = trim($_POST['name']);
        }
        if (strlen2($name)>50) {
            $err='Длина названия превышает предел в 50 символов';
        }
        if (strlen2($msg)<3) {
            $err='Короткий Текст';
        }
        if (strlen2($msg)>10000) {
            $err='Длина текста превышает предел в 10000 символа';
        }var_dump([$name, $type, $id_dir, $msg, $privat, $privat_komm, $notes['id']]);
        if (!isset($err)) {
            $db->query("UPDATE `notes` SET `name`=?, `type`=?i, `id_dir`=?in, `msg`=?, `private`=?i, `private_komm`=?i WHERE `id`=?i",
                       [$name, $type, $id_dir, $msg, $privat, $privat_komm, $notes['id']]);
            
            $_SESSION['message'] = 'Изменения успешно приняты';
            header("Location: list.php?id=".$notes['id']."".SID);
            exit;
        }
    }
    err();
    echo "<div class=\"foot\">\n";
    echo "<img src='/style/icons/str2.gif' alt='*'> <a href='index.php'>Дневники</a> | <a href='/info.php?id=$avtor[id]'>$avtor[nick]</a>\n";
    echo " | <a href='list.php?id=$notes[id]'>" . text($notes['name']) . "</a> | <b>Редактирование</b>";
    echo "</div>\n";
    echo "<form method='post' name='message' action='?id=".$notes['id']."&amp;edit=1'>\n";
    echo "Название:<br />\n<input type=\"text\" name=\"name\" value=\""  . text($notes['name']) . "\" /><br />\n";
    $msg2 = text($notes['msg']);
    if ($set['web'] && is_file(H.'style/themes/'.$set['set_them'].'/altername_post_form.php')) {
        include_once H.'style/themes/'.$set['set_them'].'/altername_post_form.php';
    } else {
        echo "Сообщение:$tPanel<textarea name=\"msg\">"  . text($notes['msg']) . "</textarea><br />\n";
    }
    echo "Категория:<br />\n<select name='id_dir'>\n";
    $q=$db->query("SELECT * FROM `notes_dir` ORDER BY `id` DESC");
    echo "<option value='0'".(!$notes['id_dir'] ? " selected='selected'":null)."><b>Без категории</b></option>\n";
    while ($post = $q->row()) {
        echo "<option value='$post[id]'".($notes['id_dir'] == $post['id'] ?" selected='selected'":null).">" . text($post['name']) . "</option>\n";
    }
    echo "</select><br />\n";
    echo "<div class='main'>Могут смотреть:<br /><input name='private' type='radio' ".($notes['private']==0?' checked="checked"':null)." value='0' />Все ";
    echo "<input name='private' type='radio' ".($notes['private']==1?' checked="checked"':null)." value='1' />Друзья ";
    echo "<input name='private' type='radio' ".($notes['private']==2?' checked="checked"':null)." value='2' />Только я</div>";
    echo "<div class='main'>Могут комментировать:<br /><input name='private_komm' type='radio' ".($notes['private_komm']==0?' checked="checked"':null)." value='0' />Все ";
    echo "<input name='private_komm' type='radio' ".($notes['private_komm']==1?' checked="checked"':null)." value='1' />Друзья ";
    echo "<input name='private_komm' type='radio' ".($notes['private_komm']==2?' checked="checked"':null)." value='2' />Только я</div>";
    echo "<input value=\"Применить\" type=\"submit\" />\n";
    echo "</form>\n";
    echo "<div class=\"foot\">\n";
    echo "<img src='/style/icons/str2.gif' alt='*'> <a href='index.php'>Дневники</a> | <a href='/info.php?id=$avtor[id]'>$avtor[nick]</a>\n";
    echo " | <a href='list.php?id=$notes[id]'>" . text($notes['name']) . "</a> | <b>Редактирование</b>";
    echo "</div>\n";
}
include_once '../../sys/inc/tfoot.php';
