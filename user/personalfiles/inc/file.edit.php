<?php
/*
=======================================
Личные файлы юзеров для Dcms-Social
Автор: Искатель
---------------------------------------
Этот скрипт распостроняется по лицензии
движка Dcms-Social.
При использовании указывать ссылку на
оф. сайт http://dcms-social.ru
---------------------------------------
Контакты
ICQ: 587863132
http://dcms-social.ru
=======================================
*/
if (isset($_GET['edit'])) {
    if (isset($_GET['ok'])) {
        $name = trim($_POST['name']);
        $opis = trim($_POST['opis']);
        if (strlen2($name) < 2) {
            $err[] = 'Короткое Название';
        }
        if (strlen2($name) > 128) {
            $err[] = 'Длинное Название';
        }
        $metka = isset($_POST['metka']) ? 1 : 0;
        if (!isset($err)) {
			$data = [$metka, $name, $opis, $file_id['id']];
            $db->query("UPDATE `obmennik_files` SET `metka` = ?i, `name` = ?,`opis` = ? WHERE `id` = ?i", $data);
            $_SESSION['message']='Файл успешно отредактирован';
            header('Location: ?id_file=' . $file_id['id']);
            exit;
        }
    }
    echo '<div class="foot">';
    echo '<img src="/style/icons/str.gif" alt="*">  <a href="?go=' . $file_id['id'] . '">Переместить файл</a>';
    echo '</div>';
    
    echo '<form method="post"  action="?id_file=' . $file_id['id'] . '&amp;edit&amp;ok">
	Название файла:<br />
	<input name="name" type="text" maxlength="32" value="'.text($file_id['name']).'" /><br />
	Описание:<br />
	<textarea name="opis">' . text($file_id['opis']) . '</textarea><br />';
    echo "<label><input type='checkbox' name='metka' value='1' ".($file_id['metka'] == 1?"checked='checked'":"")."/> Метка <font color=red>18+</font></label><br />";
    echo '<img src="/style/icons/ok.gif" alt="*"> <input value="Изменить" type="submit" /> <a href="?id_file='.$file_id['id'].'"><img src="/style/icons/delete.gif" alt="*"> Отмена</a><br />';
    
    echo "<div class='foot'>";
    echo "<img src='/style/icons/up_dir.gif' alt='*'> " . ($dir['osn'] == 1 ? '<a href="/user/personalfiles/' . $ank['id'] . '/' . $dir['id'] . '/">Файлы</a>' : '') . " " . user_files($dir['id_dires'])." ".($dir['osn']==1?'':'&gt; <a href="/user/personalfiles/' . $ank['id'] . '/' . $dir['id'] . '/">' . text($dir['name']) . '</a>')."\n";
    echo "</div>";
    
    include_once H . 'sys/inc/tfoot.php';
    exit;
}
