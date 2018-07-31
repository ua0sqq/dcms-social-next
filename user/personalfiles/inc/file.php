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
$sql_music = null;
if (isset($user)) {
    $sql_music = ', (
    SELECT COUNT( * ) FROM `user_music` WHERE `id_user`=' . $user['id'] . ' AND `dir`="obmen" AND `id_file`=`a`.`id`) music, (
    SELECT COUNT( * ) FROM `like_object` WHERE `id_object`=`a`.`id` AND `type`="obmen" AND `id_user`=' . $user['id'] . ') like_user';
}

$file_id = $db->query(
    'SELECT a.*, b.name AS dir_name, b.dir, b.my, (
SELECT COUNT( * ) FROM `user_music` WHERE `dir`="obmen" AND `id_file`=a.id) music_people ?q;, (
SELECT COUNT( * ) FROM `like_object` WHERE `id_object`=a.id AND `type`="obmen" AND `like`=1) like_plus, (
SELECT COUNT( * ) FROM `like_object` WHERE `id_object`=a.id AND `type`="obmen" AND `like`=0) like_minus
FROM `obmennik_files` a
JOIN `obmennik_dir` b ON b.id=a.id_dir WHERE a.`id`=?i',
                    [$sql_music, $_GET['id_file']]
)->row();

if ($file_id['id_user'] != $ank['id']) {
    include_once H . 'sys/inc/thead.php';
    title();
    echo '<div class="err">Ошибка!</div>';
    include_once H . 'sys/inc/tfoot.php';
    exit;
}
$dir_id = [
           'name' => $file_id['dir_name'],
            'dir' => $file_id['dir'],
            'my' => $file_id['my']
          ];
$ras=$file_id['ras'];
$file=H."sys/obmen/files/$file_id[id].dat";
$name=$file_id['name'];
$size=$file_id['size'];
/*
================================
Модуль жалобы на пользователя
и его сообщение либо контент
в зависимости от раздела
================================
*/
if (isset($_GET['spam'])  && isset($user)) {
            $mess = $db->query("SELECT obk.*, u.id AS id_user, u.nick FROM `obmennik_komm` obk
JOIN `user` u ON u.id=obk.id_user
WHERE obk.`id`=?i", [$_GET['spam']])->row();
            
            if (!$db->query(
                "SELECT COUNT(*) FROM `spamus` WHERE `id_user`=?i AND `id_spam`=?i AND `razdel`=? AND `spam`=?",
                            [$user['id'], $mess['id_user'], 'obmen_komm', $mess['msg']])->el()) {
                if (isset($_POST['msg'])) {
                    if ($mess['id_user'] != $user['id']) {
                        $msg = trim($_POST['msg']);
                        if (strlen2($msg) < 3) {
                            $err = 'Укажите подробнее причину жалобы';
                        }
                        if (strlen2($msg) > 1512) {
                            $err = 'Длина текста превышает предел в 512 символов';
                        }
                        if (isset($_POST['types'])) {
                            $types = intval($_POST['types']);
                        } else {
                            $types = '0';
                        }
                        if (!isset($err)) {
                            $db->query(
                                "INSERT INTO `spamus` (`id_object`, `id_user`, `msg`, `id_spam`, `time`, `types`, `razdel`, `spam`)
VALUES(?i, ?i, ?, ?i, ?i, ?i, ?, ?)",
                                       [$file_id['id'], $user['id'], $msg, $mess['id_user'], $time, $types, 'obmen_komm', $mess['msg']]);
                            $_SESSION['message'] = 'Заявка на рассмотрение отправлена';
                            header("Location: /obmen$dir_id[dir]$file_id[id].$file_id[ras]?showinfo&spam=$mess[id]&page=".intval($_GET['page'])."");
                            exit;
                        }
                    }
                }
            }
            aut();
            err();
            if (!$db->query(
                "SELECT COUNT(*) FROM `spamus` WHERE `id_user`=?i AND `id_spam`=?i AND `razdel`=?",
                            [$user['id'], $mess['id_user'], 'obmen_komm'])->el()) {
                echo "<div class='mess'>Ложная информация может привести к блокировке ника.  
    Если вас постоянно достает один человек - пишет всякие гадости, вы можете добавить его в черный список.</div>";
                echo "<form class='nav1' method='post' action='/obmen$dir_id[dir]$file_id[id].$file_id[ras]?showinfo&spam=$mess[id]&page=".intval($_GET['page'])."'>\n";
                echo "<b>Пользователь:</b> ";
                echo " ".status($mess['id_user'])."  ".group($mess['id_user'])." <a href='/info.php?id=$mess[id_user]'>$mess[nick]</a>\n";
                echo "".medal($mess['id_user'])." ".online($mess['id_user'])." (".vremja($mess['time']).")<br />";
                echo "<b>Нарушение:</b> <font color='green'>".output_text($mess['msg'])."</font><br />";
                echo "Причина:<br />\n<select name='types'>\n";
                echo "<option value='1' selected='selected'>Спам/Реклама</option>\n";
                echo "<option value='2' selected='selected'>Мошенничество</option>\n";
                echo "<option value='3' selected='selected'>Оскорбление</option>\n";
                echo "<option value='0' selected='selected'>Другое</option>\n";
                echo "</select>\n";
                echo "Комментарий:<br />";
                echo "<textarea name='msg'></textarea><br />";
                echo "<input value='Отправить' type='submit'/>\n";
                echo "</form>\n";
            } else {
                echo "<div class='mess'>Жалоба на <font color='green'>$mess[nick]</font> будет рассмотрена в ближайшее время.</div>";
            }
            echo "<div class='foot'>\n";
            echo "<img src='/style/icons/str2.gif' alt='*'> <a href='/obmen$dir_id[dir]$file_id[id].$file_id[ras]?showinfo&page=".intval($_GET['page'])."'>Назад</a>\n";
            echo "</div>\n";
            require H . 'sys/inc/tfoot.php';
            exit;
        }

// очищаем счетчик этого обсуждения
if (isset($user)) {
    $db->query(
    'UPDATE `discussions` SET `count`=?i WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i LIMIT ?i',
           [0, $user['id'], 'obmen', $file_id['id'],  1]);
}

// Мне нравится
if (isset($user) && $ank['id'] != $user['id'] && isset($_GET['like']) && ($_GET['like'] == 1 || $_GET['like'] == 0)
&& !$db->query(
            'SELECT COUNT(*) FROM `like_object` WHERE `id_object`=?i AND `type`=? AND `id_user`=?i',
                    [$file_id['id'], 'obmen', $user['id']])->el()) {
    $db->query(
    'INSERT INTO `like_object` (`id_user`, `id_object`, `type`, `like`) VALUES (?i, ?i, ?, ?i)',
           [$user['id'], $file_id['id'], 'obmen', $_GET['like']]);
    $db->query(
    'UPDATE `user` SET `balls`=`balls`+1, `rating_tmp`=`rating_tmp`+1 WHERE `id`=?i',
           [$ank['id']]);
}


if (isset($user) && isset($_GET['play']) && ($_GET['play']==1 || $_GET['play']==0)
    && ($file_id['ras']=='mp3' || $file_id['ras']=='wav' || $file_id['ras']=='ogg')) {
    if ($_GET['play']==1 && $file_id['music']==0) { // Добавляем в плейлист
        $db->query(
        'INSERT INTO `user_music` (`id_user`, `id_file`, `dir`) VALUES (?i, ?i, ?)',
               [$user['id'], $file_id['id'], 'obmen']);
        $db->query(
        'UPDATE `user` SET `balls`=`balls`+1, `rating_tmp`=`rating_tmp`+1 WHERE `id`=?i',
               [$ank['id']]);
        $_SESSION['message']='Трек добавлен в плейлист';
    }
    
    if ($_GET['play']==0 && $file_id['music']==1) { // Удаляем из плейлиста
        $db->query(
        'DELETE FROM `user_music` WHERE `id_user`=?i AND `id_file`=?i AND `dir`=? LIMIT ?i',
               [$user['id'], $file_id['id'], 'obmen', 1]);
        $db->query(
        'UPDATE `user` SET `rating_tmp`=`rating_tmp`-1 WHERE `id`=?i',
               [$ank['id']]);
        $_SESSION['message']='Трек удален из плейлиста';
    }
    header('Location: ?id_file=' . $file_id['id']);
    exit;
}
 
// заголовок страницы
$set['title']= htmlspecialchars($file_id['name']); 
include_once H . 'sys/inc/thead.php';
title();
if ((user_access('obmen_komm_del') || $ank['id'] == $user['id']) && isset($_GET['del_post'])
    && $db->query(
        'SELECT COUNT(*) FROM `obmennik_komm` WHERE `id`=?i AND `id_file`=?i',
                [$_GET['del_post'], $file_id['id']])->el()) {
    $db->query(
    'DELETE FROM `obmennik_komm` WHERE `id`=?i',
            [$_GET['del_post']]);

    $_SESSION['message']='Комментарий успешно удален';
    header("Location: ?id_file=$file_id[id]");
}
if (isset($user)) {
    $db->query(
    'UPDATE `notification` SET `read`=? WHERE `type`=? AND `id_user`=?i AND `id_object`=?i',
            ['1', 'files_komm', $user['id'], $file_id['id']]);
}
if (isset($_POST['msg']) && isset($user)) {
    $msg=trim($_POST['msg']);
    if (isset($_POST['translit']) && $_POST['translit']==1) {
        $msg=translit($msg);
    }
    $mat=antimat($msg);
    if ($mat) {
        $err[]='В тексте сообщения обнаружен мат: '.$mat;
    }
    if (strlen2($msg)>1024) {
        $err[]='Сообщение слишком длинное';
    } elseif (strlen2($msg)<2) {
        $err[]='Короткое сообщение';
    } elseif ($db->query(
                    'SELECT COUNT(*) FROM `obmennik_komm` WHERE `id_file`=?i AND `id_user`=?i AND `msg`=?',
                            [$file_id['id'], $user['id'], $msg])->el()) {
        $err='Ваше сообщение повторяет предыдущее';
    } elseif (!isset($err)) {
        // Обсуждения
        $q = $db->query(
            'SELECT fr.user, fr.disc_obmen, dsc.disc_files FROM `frends` fr 
JOIN discussions_set dsc ON dsc.id_user=fr.user
WHERE fr.`frend`=?i AND fr.`disc_foto`=?i AND `i`=?i',
                [$file_id['id_user'], 1, 1]);
        while ($frend = $q->row()) {
            // Фильтр рассылки
            if ($frend['disc_obmen'] == 1 && $frend['disc_files'] == 1) {
                // друзьям автора
                if (!$db->query(
                    'SELECT COUNT(*) FROM `discussions` WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i',
                            [$frend['user'], 'obmen', $file_id['id']])->el()) {
                    if ($file_id['id_user'] != $frend['user'] || $frend['user'] != $user['id']) {
                        $db->query(
                'INSERT INTO `discussions` (`id_user`, `avtor`, `type`, `time`, `id_sim`, `count`) VALUES(?i, ?i, ?, ?i, ?i, ?i)',
                        [$frend['user'], $file_id['id_user'], 'obmen', $time, $file_id['id'], 1]);
                    }
                } else {
                    if ($file_id['id_user'] != $frend['user'] || $frend['user'] != $user['id']) {
                        $db->query(
                'UPDATE `discussions` SET `count`=`count`+1, `time`=?i WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i LIMIT ?i',
                        [$time, $frend['user'], 'obmen', $file_id['id'], 1]);
                    }
                }
            }
        }
        // отправляем автору
        if (!$db->query(
                'SELECT COUNT(*) FROM `discussions` WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i',
                        [$file_id['id_user'], 'obmen', $file_id['id']])->el()) {
            if ($file_id['id_user'] != $user['id']) {
                $db->query(
            "INSERT INTO `discussions` (`id_user`, `avtor`, `type`, `time`, `id_sim`, `count`) VALUES(?i, ?i, ?, ?i, ?i, ?i)",
                   [$file_id['id_user'], $file_id['id_user'], 'obmen', $time, $file_id['id'], 1]);
            }
        } else {
            if ($file_id['id_user'] != $user['id']) {
                $db->query(
            'UPDATE `discussions` SET `count`=`count`+1, `time`=?i WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i LIMIT ?i',
                    [$time, $file_id['id_user'], 'obmen', $file_id['id'], 1]);
            }
        }
        
        // Уведомления об ответах
        if (isset($user) && $respons == true) {
            if ($db->query(
                'SELECT COUNT( * ) FROM `notification_set` WHERE `id_user`=?i',
                           [$ank_reply['id']])->el() && $ank_reply['id'] != $user['id']) {
                $db->query(
                'INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES (?i, ?i, ?i, ?, ?i)',
                        [$user['id'], $ank_reply['id'], $file_id['id'], 'files_komm', $time]);
            }
        }
        $db->query(
            'INSERT INTO `obmennik_komm` (`id_file`, `id_user`, `time`, `msg`) VALUES(?i, ?i, ?i, ?)',
                    [$file_id['id'], $user['id'], $time, $msg]);
        $db->query(
            'UPDATE `user` SET `balls`=`balls`+1, `rating_tmp`=`rating_tmp`+1 WHERE `id`=?i',
                    [$user['id']]);
        
        $_SESSION['message'] = 'Сообщение успешно добавлено';
        header("Location: ?id_file=$file_id[id]");
        exit;
    }
}
err();
aut(); // форма авторизации
echo "<div class='foot'>";
echo "<img src='/style/icons/up_dir.gif' alt='*'> ".($dir['osn']==1?'<a href="/user/personalfiles/'.$ank['id'].'/'.$dir['id'].'/">Файлы</a>':'')." ".user_files($dir['id_dires'])." ".($dir['osn']==1?'':'&gt; <a href="/user/personalfiles/'.$ank['id'].'/'.$dir['id'].'/">'.htmlspecialchars($dir['name']).'</a>')."\n";
echo "</div>";
// Папка под паролем
if ($dir['pass']!=null) {
    if (isset($_POST['password'])) {
        $_SESSION['pass'] = trim($_POST['password']);
        if ($_SESSION['pass'] != $dir['pass']) {
            $_SESSION['message'] = 'Неверный пароль';
            $_SESSION['pass'] = null;
        }
        header("Location: ?");
    }
    if (!user_access('obmen_dir_edit') && ($user['id']!=$ank['id'] && $_SESSION['pass']!=$dir['pass'])) {
        echo '<form action="?id_file='.$file_id['id'].'" method="POST">Пароль: <br />		<input type="pass" name="password" value="" /><br />		
<input type="submit" value="Войти"/></form>';
        echo "<div class='foot'>";
        echo "<img src='/style/icons/up_dir.gif' alt='*'> ".($dir['osn']==1?'Файлы':'')." ".user_files($dir['id_dires'])." ".($dir['osn']==1?'':'&gt; '.htmlspecialchars($dir['name']))."\n";
        echo "</div>";
        include_once H . 'sys/inc/tfoot.php';
        exit;
    }
}
/*---------------------------------------------------------*/
 // Инклудим редактор
if (isset($user) && user_access('obmen_file_edit') || $ank['id']==$user['id']) {
    include "inc/file.edit.php";
}
 // Инклудим удаление
if (isset($user) && user_access('obmen_file_delete') || $ank['id']==$user['id']) {
    include "inc/file.delete.php";
}
echo '<div class="main">';
if ($dir_id['my']!=1) {
    if ($user['id']==$file_id['id_user']) {
        echo '<img src="/style/icons/z.gif" alt="*"> Зона обмена: <a href="/obmen'.$dir_id['dir'].'">'.$dir_id['name'].'</a> <a href="/obmen/?trans='.$file_id['id'].'"><img src="/style/icons/edit.gif" alt="*"></a><br />';
    } else {
        echo '<img src="/style/icons/z.gif" alt="*"> Зона обмена: <a href="/obmen'.$dir_id['dir'].'">'.$dir_id['name'].'</a><br /> ';
    }
}
include_once H.'obmen/inc/icon14.php';
echo htmlspecialchars($file_id['name']).'.'.$ras.' ';
if ($file_id['metka'] == 1) {
    echo '<font color=red><b>(18+)</b></font> ';
}
echo vremja($file_id['time']).'<br />';
echo '</div>';
if (($user['abuld'] == 1 || $file_id['metka'] == 0 || $file_id['id_user'] == $user['id'])) { // Метка 18+
    echo '<div class="main">';
    if (is_file(H."obmen/inc/file/$ras.php")) {
        include H."obmen/inc/file/$ras.php";
    } else {
        include_once H.'obmen/inc/file.php';
    }
    echo '</div>';
} elseif (!isset($user)) {
    echo '<div class="mess">';
    echo '<img src="/style/icons/small_adult.gif" alt="*"><br /> Данный файл содержит изображения эротического характера. Только зарегистрированные пользователи старше 18 лет могут просматривать такие файлы. <br />';
    echo '<a href="/aut.php">Вход</a> | <a href="/reg.php">Регистрация</a>';
    echo '</div>';
} else {
    echo '<div class="mess">';
    echo '<img src="/style/icons/small_adult.gif" alt="*"><br /> 
	Данный файл содержит изображения эротического характера. 
	Если Вас это не смущает и Вам 18 или более лет, то можете <a href="?id_file='.$file_id['id'].'&amp;sess_abuld=1">продолжить просмотр</a>. 
	Или Вы можете отключить предупреждения в <a href="/user/info/settings.php">настройках</a>.';
    echo '</div>';
}

// листинг
        $listing = $db->query('SELECT tbl2.id as start_id, tbl2.ras, tbl3.id as end_id, tbl3.ras as end_ras, (
SELECT COUNT(*)+1 FROM obmennik_files WHERE id>tbl1.id AND my_dir=tbl1.my_dir) AS cnt, (
SELECT COUNT(*) FROM obmennik_files WHERE my_dir=tbl1.my_dir) AS all_cnt
FROM `obmennik_files` tbl1
LEFT JOIN `obmennik_files` tbl2 ON (tbl1.id > tbl2.id AND tbl2.my_dir=tbl1.my_dir)
LEFT JOIN `obmennik_files` tbl3 ON (tbl1.id < tbl3.id AND tbl3.my_dir=tbl1.my_dir)
WHERE tbl1.`my_dir`=?i AND tbl1.`id`=?i ORDER BY tbl2.`id` DESC, tbl3.id LIMIT ?i',
[$dir['id'], $file_id['id'], 1])->row();
        
echo '<div class="c2" style="text-align: center;">';
echo '<span class="page">'.($listing['end_id']?'<a href="?id_file='.$listing['end_id'].'">&laquo; Пред.</a> ':'&laquo; Пред. ').'</span>';

echo ' ('.$listing['cnt'].' из '.$listing['all_cnt'].') ';

echo '<span class="page">'.($listing['start_id']?'<a href="?id_file='.$listing['start_id'].'">След. &raquo;</a>':' След. &raquo;').'</span>';
echo '</div>';

if (($user['abuld'] == 1 || $file_id['metka'] == 0 || $file_id['id_user'] == $user['id'])) { // Метка 18+
    // Действия над файлом
    if (user_access('obmen_file_edit') || $user['id']==$file_id['id_user']) {
        echo '<div class="main">';
        if ($user['id']==$file_id['id_user'] && $dir_id['my']==1) {
            echo '[<a href="/obmen/?trans='.$file_id['id'].'"><img src="/style/icons/z.gif" alt="*"> в зону</a>]';
        }
        echo ' [<img src="/style/icons/edit.gif" alt="*"> <a href="?id_file='.$file_id['id'].'&amp;edit">ред.</a>]';
        echo ' [<img src="/style/icons/delete.gif" alt="*"> <a href="?id_file='.$file_id['id'].'&amp;delete">удл.</a>]';
        echo '</div>';
    }

    echo '<div class="main">';
    if (isset($user) && $ank['id'] != $user['id'] && !$file_id['like_user']) {
        echo '[<img src="/style/icons/like.gif" alt="*"> <a href="?id_file='.$file_id['id'].'&amp;like=1">Мне нравится</a>] ';
        echo '[<a href="?id_file='.$file_id['id'].'&amp;like=0"><img src="/style/icons/dlike.gif" alt="*"></a>]';
    } else {
        echo '[<img src="/style/icons/like.gif" alt="*"> '.$file_id['like_plus'].'] ';
        echo '[<img src="/style/icons/dlike.gif" alt="*"> '.$file_id['like_minus'].']';
    }
    echo '</div>';
    echo '<div class="main">';
    if ($file_id['ras']=='jar') {
        echo '<img src="/style/icons/d.gif" alt="*"> <a href="/obmen'.$dir_id['dir'].$file_id['id'].'.'.$file_id['ras'].'">Скачать JAR ('.size_file($size).')</a> <a href="/obmen'.$dir_id['dir'].$file_id['id'].'.jad">JAD</a> <br />';
    } else {
        echo '<img src="/style/icons/d.gif" alt="*"> <a href="/obmen'.$dir_id['dir'].$file_id['id'].'.'.$file_id['ras'].'">Скачать ('.size_file($size).')</a><br />';
    }
    echo 'Скачан ('.$file_id['k_loads'].')';
    echo '</div>';
    /*-------------------Моя музыка---------------------*/
    if (isset($user) && ($file_id['ras']=='mp3' || $file_id['ras']=='wav' || $file_id['ras']=='ogg')) {
        echo '<div class="main">';
        if ($file_id['music']==0) {
            echo '<a href="?id_file='.$file_id['id'].'&amp;play=1"><img src="/style/icons/play.png" alt="*"></a> ('.$file_id['music_people'].')';
        } else {
            echo '<a href="?id_file='.$file_id['id'].'&amp;play=0"><img src="/style/icons/play.png" alt="*"></a> ('.$file_id['music_people'].') <img src="/style/icons/ok.gif" alt="*">';
        }
        echo '</div>';
    }
}
// комментарии
include_once 'inc/komm.php'; 
echo "<div class='foot'>";
echo "<img src='/style/icons/up_dir.gif' alt='*'> ".($dir['osn']==1?'<a href="/user/personalfiles/'.$ank['id'].'/'.$dir['id'].'/">Файлы</a>':'')." ".user_files($dir['id_dires'])." ".($dir['osn']==1?'':'&gt; <a href="/user/personalfiles/'.$ank['id'].'/'.$dir['id'].'/">'.htmlspecialchars($dir['name']).'</a>')."\n";
echo "</div>";
