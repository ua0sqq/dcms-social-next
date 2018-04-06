<?php
include_once '../../sys/inc/start.php';
include_once '../../sys/inc/compress.php';
include_once '../../sys/inc/sess.php';
include_once '../../sys/inc/home.php';
include_once '../../sys/inc/settings.php';
include_once '../../sys/inc/db_connect.php';
include_once '../../sys/inc/ipua.php';
include_once '../../sys/inc/fnc.php';
include_once '../../sys/inc/adm_check.php';
include_once '../../sys/inc/user.php';

$set['title']='Новый дневник';
include_once '../../sys/inc/thead.php';
title();

if (!isset($user)) {
    header("location: index.php?");
}
if (isset($_POST['title']) && isset($_POST['msg'])) {
    if ($user['rating'] < 1 && $user['group_access'] < 6) {
        if (!isset($_SESSION['captcha'])) {
            $err[]='Ошибка проверочного числа';
        }
        if (!isset($_POST['chislo'])) {
            $err[]='Введите проверочное число';
        } elseif ($_POST['chislo']==null) {
            $err[]='Введите проверочное число';
        } elseif ($_POST['chislo']!=$_SESSION['captcha']) {
            $err[]='Проверьте правильность ввода проверочного числа';
        }
    }
    if (!isset($err)) {
        $msg = trim($_POST['msg']);
        if (empty($_POST['title'])) {
            $title=esc(mb_substr($_POST['msg'], 0, 24)).' ...';
        } else {
            $title=trim($_POST['title']);
        }
        
        $id_dir = !empty($_POST['id_dir']) ? intval($_POST['id_dir']) : null;
        
        if (isset($_POST['private'])) {
            $privat=intval($_POST['private']);
        } else {
            $privat=0;
        }
        if (isset($_POST['private_komm'])) {
            $privat_komm=intval($_POST['private_komm']);
        } else {
            $privat_komm=0;
        }
        $type=0;
        if (strlen2($title)>32) {
            $err='Название не может превышать больше 32 символов';
        }
        if (strlen2($msg)>30000) {
            $err='Содержание не может превышать больше 30000 символов';
        }
        if (strlen2($msg)<2 && $type == 0) {
            $err='Содержание слишком короткое';
        }
        if (!isset($err)) {
            $st = $db->query(
                "INSERT INTO `notes` (`time`, `msg`, `name`, `id_user`, `private`, `private_komm`, `id_dir`, `type`) VALUES(?i, ?, ?, ?i, ?i, ?i, ?in, ?i)",
                             [$time, $msg, $title, $user['id'], $privat, $privat_komm, $id_dir, $type])->id();

            if ($privat!=2) {
                $db->query(
                    "INSERT INTO `stena`(`id_stena`,`id_user`,`time`,`info`,`info_1`,`type`) VALUES( ?i, ?i, ?i, ?, ?, ?)",
                           [$user['id'], $user['id'], $time, 'новый дневник', $st ,'note']);
            }
            // Лента
            $q = $db->query(
                "SELECT fr.user FROM `frends` fr 
JOIN tape_set ts ON ts.id_user=fr.user
WHERE fr.`frend`=?i AND fr.`lenta_notes`=?i AND ts.`lenta_notes`=?i AND `i`=?i",
                            [$user['id'], 1, 1, 1]);
            while ($frend = $q->row()) {
                $db->query(
                    "INSERT INTO `tape` (`id_user`,`avtor`, `type`, `time`, `id_file`) VALUES(?i, ?i, ?, ?i, ?i)",
                               [$frend['user'], $user['id'], 'notes', $time, $st]);
            }
           
            $_SESSION['message'] = 'Дневник успешно создан';
            header("Location: list.php?id=$st");
            $_SESSION['captcha']=null;
            exit;
        }
    }
}
if (isset($_GET['id_dir'])) {
    $id_dir=intval($_GET['id_dir']);
} else {
    $id_dir=0;
}
err();
aut();
if (isset($_POST["msg"])) {
    $msg = output_text($_POST["msg"]);
}
echo "<form method=\"post\" name=\"message\" action=\"add.php\">\n";
echo "Название:<br />\n<input name=\"title\" size=\"16\" maxlength=\"32\" value=\"\" type=\"text\" /><br />\n";
if ($set['web'] && is_file(H.'style/themes/'.$set['set_them'].'/altername_post_form.php')) {
    include_once H.'style/themes/'.$set['set_them'].'/altername_post_form.php';
} else {
    echo "Сообщение:$tPanel<textarea name=\"msg\"></textarea><br />\n";
}
echo "Категория:<br />\n<select name='id_dir'>\n";
$q=$db->query("SELECT * FROM `notes_dir` ORDER BY `id` DESC");
echo "<option value='0'".($id_dir==0?" selected='selected'":null)."><b>Без категории</b></option>\n";
while ($post = $q->row()) {
    echo "<option value='$post[id]'".($id_dir == $post['id']?" selected='selected'" : null).">" . text($post['name']) . "</option>\n";
}
echo "</select><br />\n";
echo "<div class='main'>Могут смотреть:<br /><input name='private' type='radio' value='0'  selected='selected'/>Все ";
echo "<input name='private' type='radio'  value='1' />Друзья ";
echo "<input name='private' type='radio'  value='2' />Только я</div>";
 
echo "<div class='main'>Могут комментировать:<br /><input name='private_komm' type='radio' value='0'  selected='selected'/>Все ";
echo "<input name='private_komm' type='radio'  value='1' />Друзья ";
echo "<input name='private_komm' type='radio'  value='2' />Только я</div>";
if ($user['rating'] < 1 && $user['group_access'] < 6) {print $user['rating'];
    echo "<img src='/captcha.php?SESS=$sess' width='100' height='30' alt='Проверочное число' /><br />\n<input name='chislo' size='5' maxlength='5' value='' type='text' /><br/>\n";
}
     
echo "<input value=\"Создать\" type=\"submit\" />\n";
echo "</form>\n";
echo "<div class='foot'>\n";
echo "<img src='/style/icons/str2.gif' alt='*'> <a href='index.php'>Дневники</a><br />\n";
echo "</div>\n";
include_once '../../sys/inc/tfoot.php';
