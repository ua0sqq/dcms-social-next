<?php
require '../sys/inc/start.php';
if (isset($_GET['showinfo']) || !isset($_GET['f']) || isset($_GET['komm'])) {
    require H . 'sys/inc/compress.php';
}
require H . 'sys/inc/sess.php';
require H . 'sys/inc/settings.php';
require H . 'sys/inc/db_connect.php';
require H . 'sys/inc/ipua.php';
require H . 'sys/inc/fnc.php';
require H . 'sys/inc/obmen.php';
require H . 'sys/inc/user.php';

// Сортировка файлов
if (!isset($_SESSION['sort'])) {
    $_SESSION['sort'] = 0;
}
if (isset($_GET['sort_files']) && $_GET['sort_files'] == 1) {
    $_SESSION['sort'] = 1;
} elseif (isset($_GET['sort_files'])) {
    $_SESSION['sort'] = 0;
}
if ($_SESSION['sort'] == 1) {
    $sort_files = ['k_loads' => false];
} else {
    $sort_files = ['time' => false];
}

if (isset($_GET['d']) && esc($_GET['d']) != null) {
    $l = preg_replace("#\.{2,}#", null, esc($_GET['d']));
    $l = preg_replace("#\./|/\.#", null, $l);
    $l = preg_replace("#(/){1,}#", "/", $l);
    $l = '/' . preg_replace("#(^(/){1,})|((/){1,}$)#", "", $l);
} else {
    $l = '/';
}
if ($l == '/') {
    $dir_id['upload'] = 0;
    $id_dir = 0;
    $l = '/';
} elseif ($db->query(
    "SELECT COUNT( * ) FROM `obmennik_dir` WHERE `dir`=? OR `dir`=? OR `dir`=?",
                     ['/' . $l, $l . '/', $l])->el()) {
    $dir_id = $db->query(
        "SELECT * FROM `obmennik_dir` WHERE `dir`=? OR `dir`=? OR `dir`=? LIMIT ?i",
                         ['/' . $l, $l . '/', $l, 1])->row();
    $id_dir = $dir_id['id'];
} else {
    $dir_id['upload'] = 0;
    $id_dir = 0;
    $l = '/';
}
if (isset($_GET['f'])) {
    $f=esc(urldecode($_GET['f']));
    $name=preg_replace('#.[^.]*$#', null, $f); // имя файла без расширения
    $ras=strtolower(preg_replace('#^.*.#', null, $f));
    $ras=str_replace('jad', 'jar', $ras);
    if ($db->query(
        "SELECT COUNT( * ) FROM `obmennik_files` WHERE `id_dir`=?i AND `id`=?i",
                   [$id_dir, $_GET['f']])->el()) {
        $sql_music = null;
        if (isset($user)) {
            $sql_music = ', (
            SELECT COUNT( * ) FROM `user_music` WHERE `id_user`=' . $user['id'] . ' AND `dir`="obmen" AND `id_file`= fls.id) music, (
            SELECT COUNT( * ) FROM `like_object` WHERE `id_object`=fls.id AND `type`="obmen" AND `id_user`=' . $user['id'] . ') like_user';
        }
        
        $file_id = $db->query(
            'SELECT fls.*, u.id AS user_id, u.balls, u.rating_tmp, (
SELECT COUNT( * ) FROM `user_music` WHERE `dir`="obmen" AND `id_file`=fls.id) music_people ?q;, (
SELECT COUNT( * ) FROM `like_object` WHERE `id_object`=fls.id AND `type`="obmen" AND `like`=1) like_plus, (
SELECT COUNT( * ) FROM `like_object` WHERE `id_object`=fls.id AND `type`="obmen" AND `like`=0) like_minus
FROM `obmennik_files` fls
JOIN `user` u ON u.id=fls.id_user
WHERE fls.`id_dir`=?i AND fls.`id`=?i',
                            [$sql_music, $id_dir, $_GET['f']])->row();
        
        $ras=$file_id['ras'];
        $file = H . 'sys/obmen/files/' . $file_id['id'] . '.dat';
        $session_file_name = md5($file);
        $name = $file_id['name'];
        $size = $file_id['size'];
        $file_id['name'] = str_replace('_', ' _', $file_id['name']);
        
        if (!isset($_GET['showinfo']) && !isset($_GET['komm']) && is_file(H.'sys/obmen/files/'.$file_id['id'].'.dat')) {
            // TODO: ??? вычисляем доступность папки
            $is_pass = $db->query('SELECT `pass` FROM `user_files` WHERE `id`=?i AND (`pass` IS NOT NULL AND `pass`<>"")',
                                  [$file_id['my_dir']])->el();
            if (user_access('obmen_dir_edit') && $user['id'] == $file_id['id_user']) {
                $is_pass = false;
            }
            if ($is_pass && !isset($_SESSION['pass']) || (isset($_SESSION['pass']) && $_SESSION['pass'] != $is_pass)) {
                $_SESSION['err'] = 'Введите пароль';
                header('Location: /user/personalfiles/'.$file_id['id_dir'].'/'.$file_id['my_dir'].'/');
                exit;
            }
            
            if ($ras == 'jar' && strtolower(preg_replace('#^.*.#', null, $f)) == 'jad') {
                require H.'sys/inc/zip.php';
                $zip=new PclZip(H.'sys/obmen/files/'.$file_id['id'].'.dat');
                $content = $zip->extract(PCLZIP_OPT_BY_NAME, "META-INF/MANIFEST.MF", PCLZIP_OPT_EXTRACT_AS_STRING);
                $jad=preg_replace("#(MIDlet-Jar-URL:( )*[^(n|r)]*)#i", null, $content[0]['content']);
                $jad=preg_replace("#(MIDlet-Jar-Size:( )*[^(n|r)]*)(n|r)#i", null, $jad);
                $jad=trim($jad);
                $jad.="rnMIDlet-Jar-Size: ".filesize(H.'sys/obmen/files/'.$file_id['id'].'.dat')."";
                $jad.="rnMIDlet-Jar-URL: /obmen$dir_id[dir]$file_id[id].$file_id[ras]";
                $jad=br($jad, "rn");
                header('Content-Type: text/vnd.sun.j2me.app-descriptor');
                header('Content-Disposition: attachment; filename="'.$file_id['name'].'.jad";');
                echo $jad;
                exit;
            }

            if (isset($user) && isset($_SESSION[$session_file_name]) && $_SESSION[$session_file_name] == 0) {
                $db->query(
                    "UPDATE `user` SET `rating_tmp`=`rating_tmp`+1 WHERE `id`=?i",
                                [$file_id['id_user']]);
            }
            
            $_SESSION[$session_file_name] = 1;
            $db->query(
                "UPDATE `obmennik_files` SET `k_loads`=`k_loads`+1 WHERE `id`=?i",
                            [$file_id['id']]);
            
            require H . 'sys/inc/downloadfile.php';
            downloadfile(H.'sys/obmen/files/'.$file_id['id'].'.dat', retranslit($file_id['name']).'_'.$_SERVER['HTTP_HOST'].'.'.$ras, ras_to_mime($ras));
            exit;
        }
        
        if (isset($user) && isset($_GET['play']) && ($_GET['play'] == 1 || $_GET['play'] == 0) && ($file_id['ras'] == 'mp3' || $file_id['ras'] == 'wav' || $file_id['ras'] == 'ogg')) {
            // Добавляем в плейлист
            if ($_GET['play'] == 1 && $file_id['music'] == 0) {
                $db->query(
                    "INSERT INTO `user_music` (`id_user`, `id_file`, `dir`) VALUES (?i, ?i, ?)",
                           [$user['id'], $file_id['id'], 'obmen']);
                $db->query(
                    "UPDATE `user` SET `balls`=`balls`+1, `rating_tmp`=`rating_tmp`+1 WHERE `id`=?i",
                           [$file_id['id_user']]);
                $_SESSION['message']='Трек добавлен в плейлист';
            }
            // Удаляем из плейлиста
            if ($_GET['play'] == 0 && $file_id['music'] == 1) {
                $db->query(
                    "DELETE FROM `user_music` WHERE `id_user`=?i AND `id_file`=?i AND `dir`=? LIMIT ?i",
                           [$user['id'], $file_id['id'], 'obmen', 1]);
                $db->query(
                    "UPDATE `user` SET `rating_tmp`=`rating_tmp`-1 WHERE `id`=?i",
                           [$file_id['id_user']]);
     
                $_SESSION['message']='Трек удален из плейлиста';
            }
            header('Location: ?showinfo');
            exit;
        }
        /*------------------------------------------------------------*/
        if (isset($_GET['fav']) && isset($user)) {
            if ($_GET['fav']  ==  1 && !$db->query(
                "SELECT COUNT( * ) FROM `bookmarks` WHERE `id_user`=?i AND `id_object`=?i AND `type`=?",
                            [$user['id'], $file_id['id'], 'file'])->el()) {
                $db->query(
                    "INSERT INTO `bookmarks` (`type`,`id_object`, `id_user`, `time`) VALUES (?, ?i, ?i, ?i)",
                           ['file', $file_id['id'], $user['id'], time()]);
                $_SESSION['message'] = text($file_id['name']) . ' добавлен в закладки';
            } elseif ($_GET['fav']  ==  0 && $db->query(
                "SELECT COUNT( * ) FROM `bookmarks` WHERE `id_user`=?i AND `id_object`=?i AND `type`=?",
                            [$user['id'], $file_id['id'], 'file'])->el()) {
                $db->query(
                    "DELETE FROM `bookmarks` WHERE `id_user`=?i AND  `id_object`=?i AND `type`=?",
                           [$user['id'], $file_id['id'], 'file']);
                $_SESSION['message'] = text($file_id['name']) . ' удален из закладок';
            }
     
            header('Location: ?showinfo');
            exit;
        }
        // Мне нравится
        if (isset($user) && $file_id['id_user']!=$user['id'] && isset($_GET['like']) && ($_GET['like'] == 1 || $_GET['like'] == 0)
            && !$db->query(
                "SELECT COUNT( * ) FROM `like_object` WHERE `id_object`=?i AND `type`=? AND `id_user`=?i",
                           [$file_id['id'], 'obmen', $user['id']])->el()) {
            $db->query(
                "INSERT INTO `like_object` (`id_user`, `id_object`, `type`, `like`) VALUES (?i, ?i, ?, ?i)",
                       [$user['id'], $file_id['id'], 'obmen', $_GET['like']]);
            $db->query(
                "UPDATE `user` SET `balls`=`balls`+1, `rating_tmp`=`rating_tmp`+1 WHERE `id`=?i",
                       [$file_id['id_user']]);
            header('Location: ?showinfo');
            exit;
        }

        $set['title']='Обменник - ' . text(str_replace('_', ' _', $file_id['name'])); // заголовок страницы
        require H . 'sys/inc/thead.php';
        title();
        if (isset($_GET['spam'])  && isset($user)) {
            $mess = $db->query("SELECT obk.*, u.id AS id_user, u.nick FROM `obmennik_komm` obk
JOIN `user` u ON u.id=obk.id_user
WHERE obk.`id`=?i", [$_GET['spam']])->row();
            
            if (!$db->query(
                "SELECT COUNT( * ) FROM `spamus` WHERE `id_user`=?i AND `id_spam`=?i AND `razdel`=? AND `spam`=?",
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
                                        [$file_id['id'], $user['id'], $msg, $mess['id_user'], time(), $types, 'obmen_komm', $mess['msg']]);
                            
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
                "SELECT COUNT( * ) FROM `spamus` WHERE `id_user`=?i AND `id_spam`=?i AND `razdel`=?",
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
            echo "<img src='/style/icons/str2.gif' alt='*'> <a href='/obmen$dir_id[dir]$file_id[id].$file_id[ras]?showinfo&amp;page=".intval($_GET['page'])."'>Назад</a>\n";
            echo "</div>\n";
            require H . 'sys/inc/tfoot.php';
            exit;
        }
        if (isset($user)) {
            $db->query(
                "UPDATE `notification` SET `read`=? WHERE `type`=? AND `id_user`=?i AND `id_object`=?i",
                       ['1', 'obmen_komm', $user['id'], $file_id['id']]);
        }
        if (isset($_POST['msg']) && isset($user)) {
            $msg=$_POST['msg'];
            if (isset($_POST['translit']) && $_POST['translit'] == 1) {
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
                "SELECT COUNT( * ) FROM `obmennik_komm` WHERE `id_file`=?i AND `id_user`=?i AND `msg`=?",
                                 [$file_id['id'], $user['id'], $msg])->el()) {
                $err='Ваше сообщение повторяет предыдущее';
            } elseif (!isset($err)) {
                if (isset($user) && $respons == true) {
                    $notifiacation=$db->query(
                        "SELECT * FROM `notification_set` WHERE `id_user`=?i LIMIT ?i",
                                              [$ank_reply['id'], 1])->row();
                 
                    if ($notifiacation['komm']  ==  1 && $ank_reply['id'] != $user['id']) {
                        $db->query(
                            "INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES (?i, ?i, ?i, ?, ?i)",
                                   [$user['id'], $ank_reply['id'], $file_id['id'], 'obmen_komm', time()]);
                    }
                }
                $res = $db->query(
                    "SELECT fr.user, fr.disc_obmen, dsc.disc_files, (
SELECT COUNT( * ) FROM `discussions` WHERE `id_user`=fr.`user` AND `type`='obmen' AND `id_sim`=?i) is_discus
FROM `frends` fr 
JOIN discussions_set dsc ON dsc.id_user=fr.user
WHERE fr.`frend`=?i AND fr.`disc_foto`=1 AND fr.`user`<>?i AND `i`=1 AND dsc.disc_files=1",
                        [$file_id['id'], $file_id['id_user'], $user['id']])->assoc();
             
                foreach ($res as $frend) {
                    if ($frend['disc_obmen'] == 1 && $frend['disc_files'] == 1) {
                        if (!$frend['is_discus']) {
                            $insert_discus[] = [(int)$frend['user'], (int)$file_id['id_user'], 'obmen', time(), (int)$file_id['id'], 1];
                        } else {
                            $update_list = [$frend['user']];
                        }
                    }
                }
                if (!empty($insert_discus)) {
                    $db->query(
                        'INSERT INTO `discussions` (`id_user`, `avtor`, `type`, `time`, `id_sim`, `count`) VALUES ?v',
                               [$insert_discus]);
                }
                if (!empty($update_list)) {
                    $db->query(
                        'UPDATE `discussions` SET `count`=`count`+1, `time`=?i WHERE `id_user` IN(?li) AND `type`=? AND `id_sim`=?i',
                               [time(), $update_list, 'obmen', $file_id['id']]);
                }
                
                // отправляем автору
                if ($file_id['id_user'] != $user['id']) {
                    if (!$db->query(
                    "SELECT COUNT( * ) FROM `discussions` WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i",
                                [$file_id['id_user'], 'obmen', $file_id['id']])->el()) {
                        $db->query(
                            "INSERT INTO `discussions` (`id_user`, `avtor`, `type`, `time`, `id_sim`, `count`) VALUES(?i, ?i, ?, ?i, ?i, ?i)",
                                   [$file_id['id_user'], $file_id['id_user'], 'obmen', time(), $file_id['id'], 1]);
                    } else {
                        $db->query(
                            "UPDATE `discussions` SET `count`=`count`+1, `time`=?i WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i AND `id_user`<>?i LIMIT ?i",
                                   [time(), $file_id['id_user'], 'obmen', $file_id['id'], $user['id'], 1]);
                    }
                }
                $db->query(
                    "INSERT INTO `obmennik_komm` (`id_file`, `id_user`, `time`, `msg`) VALUES(?i, ?i, ?i, ?)",
                           [$file_id['id'], $user['id'], time(), $msg]);
                $db->query(
                    "UPDATE `user` SET `balls`=`balls`+1, `rating_tmp`=`rating_tmp`+1 WHERE `id`=?i",
                           [$user['id']]);
                $_SESSION['message'] = 'Сообщение успешно добавлено';
                header("Location: /obmen$dir_id[dir]$file_id[id].$file_id[ras]?showinfo");
                exit;
            }
        }
        include 'inc/file_act.php';
        err();
        aut(); // форма авторизации
        $my_dir = $db->query(
            "SELECT id, name, pass FROM `user_files` WHERE `id`=?i",
                             [$file_id['my_dir']])->row();
        // Папка под паролем
        if ($my_dir['pass']!=null) {
            if (isset($_POST['password'])) {
                $_SESSION['pass']=$_POST['password'];
         
                if ($_SESSION['pass']!=$my_dir['pass']) {
                    $_SESSION['message'] = 'Неверный пароль';
                    $_SESSION['pass']=null;
                }
         
                header("Location: ?showinfo");
                exit;
            }
            if (!user_access('obmen_dir_edit') && ($user['id']!=$file_id['id_user'] && $_SESSION['pass']!=$my_dir['pass'])) {
                echo '<form action="?showinfo" method="POST">Пароль:  
        <input type="pass" name="password" value="" /> 
        <input type="submit" value="Войти"/></form>';
                require H . 'sys/inc/tfoot.php';
                exit;
            }
        }
        // действия с комментариями
        require 'inc/komm_act.php';
        include 'inc/file_form.php';
        echo '<div class="main">';
        require 'inc/icon14.php';
        echo output_text($file_id['name']).'.'.$ras.' ';
        if ($file_id['metka']  ==  1) {
            echo ' <font color=red><b>(18+)</b></font>';
        }
        echo '</div>';
        // Метка 18+
        if (($user['abuld']  ==  1 || $file_id['metka']  ==  0 || $file_id['id_user']  ==  $user['id'])) {
            echo '<div class="main">';
            if (is_file("inc/file/$ras.php")) {
                include "inc/file/$ras.php";
            } else {
                include 'inc/file.php';
            }
            echo '</div>';
        } elseif (!isset($user)) {
            echo '<div class="mess">';
            echo '<img src="/style/icons/small_adult.gif" alt="*"> 
 Данный файл содержит изображения эротического характера. Только зарегистрированные пользователи старше 18 лет могут просматривать такие файлы.';
            echo '<a href="/aut.php">Вход</a> | <a href="/reg.php">Регистрация</a>';
            echo '</div>';
        } else {
            echo '<div class="mess">';
            echo '<img src="/style/icons/small_adult.gif" alt="*"> 
  
    Данный файл содержит изображения эротического характера.  
    Если Вас это не смущает и Вам 18 или более лет, то можете <a href="/obmen'.$dir_id['dir'].$file_id['id'].'.'.$file_id['ras'].'?showinfo&sess_abuld=1">продолжить просмотр</a>.  
    Или Вы можете отключить предупреждения в <a href="/user/info/settings.php">настройках</a>.';
            echo '</div>';
        }
        // листинг
        $listing = $db->query('SELECT tbl2.id as start_id, tbl2.ras, tbl3.id as end_id, tbl3.ras as end_ras, (
SELECT COUNT( * )+1 FROM obmennik_files WHERE id>tbl1.id AND id_dir=tbl1.id_dir) AS cnt, (
SELECT COUNT( * ) FROM obmennik_files WHERE id_dir=tbl1.id_dir) AS all_cnt
FROM `obmennik_files` tbl1
LEFT JOIN `obmennik_files` tbl2 ON (tbl1.id > tbl2.id AND tbl2.id_dir=tbl1.id_dir)
LEFT JOIN `obmennik_files` tbl3 ON (tbl1.id < tbl3.id AND tbl3.id_dir=tbl1.id_dir)
WHERE tbl1.`id_dir`=?i AND tbl1.`id`=?i ORDER BY tbl2.`id` DESC, tbl3.id LIMIT ?i',
                    [$dir_id['id'], $file_id['id'], 1])->row();

        echo '<div class="c2" style="text-align: center;">';
        echo '<span class="page">'.($listing['end_id']?'<a href="/obmen'.$dir_id['dir'] . $listing['end_id'].'.'.$listing['end_ras'].'?showinfo">&laquo; Пред.</a> ':'&laquo; Пред. ').'</span>';
        echo ' ('.$listing['cnt'].' из '.$listing['all_cnt'].') ';
        echo '<span class="page">'.($listing['start_id']?'<a href="/obmen'.$dir_id['dir'] . $listing['start_id'].'.'.$listing['ras'].'?showinfo">След. &raquo;</a>':' След. &raquo;').'</span>';
        echo '</div>';
        
        // Метка 18+
        if (($user['abuld']  ==  1 || $file_id['metka']  ==  0 || $file_id['id_user']  ==  $user['id'])) {
            // Действия над файлом
            if (user_access('obmen_file_edit') || $user['id'] == $file_id['id_user']) {
                echo '<div class="main">';
                echo '<img src="/style/icons/edit.gif" alt="*"> <a href="?showinfo&act=edit">Редактировать</a>';
                echo '<br /><img src="/style/icons/delete.gif" alt="*"> <a href="?showinfo&act=delete">Удалить</a>';
                echo '</div>';
            }

            // Мне нравится
            echo '<div class="main">';
            if (isset($user) && $file_id['id_user'] != $user['id'] && !$file_id['like_user']) {
                echo '<img src="/style/icons/thumbu.png" alt="*"> <a href="/obmen'.$dir_id['dir'].$file_id['id'].'.'.$file_id['ras'].'?showinfo&like=1">Мне нравится</a> ('.($file_id['like_plus']-$file_id['like_minus']).') ';
                echo '<a href="/obmen'.$dir_id['dir'].$file_id['id'].'.'.$file_id['ras'].'?showinfo&like=0"><img src="/style/icons/thumbd.png" alt="*"></a>';
            } else {
                echo '<img src="/style/icons/thumbu.png" alt="*"> ('.($file_id['like_plus']-$file_id['like_minus']).') ';
                echo ' <img src="/style/icons/thumbd.png" alt="*"> ';
            }
            echo '</div>';
            if (isset($user)) {
                $markinfo=$db->query(
                "SELECT COUNT( * ) FROM `bookmarks` WHERE `id_object`=?i AND `type`=?",
                                 [$file_id['id'], 'file'])->el();
                echo "<div class='main'>";
                echo "<img src='/style/icons/add_fav.gif' alt='*' /> ";
                if (!$db->query(
                "SELECT COUNT( * ) FROM `bookmarks` WHERE `id_user`=?i AND `id_object`=?i AND `type`=?",
                            [$user['id'], $file_id['id'], 'file'])->el()) {
                    echo "<a href='?showinfo&fav=1'>Добавить в закладки</a>\n";
                } else {
                    echo "<a href='?showinfo&fav=0'>Удалить из закладок</a>\n";
                }
                echo "<br /><img src='/style/icons/add_fav.gif' alt='*' /'> В закладках у <a href='?showinfo&markinfo'>$markinfo</a> чел.";
                echo "</div>";
            }
            echo '<div class="main">';
            if ($file_id['ras'] == 'jar') {
                echo '<img src="/style/icons/d.gif" alt="*"> <a href="/obmen'.$dir_id['dir'].$file_id['id'].'.'.$file_id['ras'].'">Скачать JAR ('.size_file($size).')</a> <a href="/obmen'.$dir_id['dir'].$file_id['id'].'.jad">JAD</a>';
            } else {
                echo '<img src="/style/icons/d.gif" alt="*"> <a href="/obmen'.$dir_id['dir'].$file_id['id'].'.'.$file_id['ras'].'">Скачать ('.size_file($size).')</a>';
            }
            echo '<br />Скачан ('.$file_id['k_loads'].')';
            echo '</div>';
        }
        echo '<div class="main">';
        echo 'Добавил: ';
        echo group($file_id['id_user']).' ';
        echo user::nick($file_id['id_user'], 1, 1, 1);
        echo ' <span style="color:#666;">'.vremja($file_id['time']).'</span><br />';
        echo 'В папку: <a href="/user/personalfiles/'.$file_id['id_user'].'/'.$my_dir['id'].'/">'.text($my_dir['name']).'</a>';
        echo '</div>';
        
        // Моя музыка
        if (isset($user) && ($file_id['ras'] == 'mp3' || $file_id['ras'] == 'wav' || $file_id['ras'] == 'ogg')) {
            echo '<div class="main">';
            if ($file_id['music'] == 0) {
                echo '<a href="?showinfo&play=1"><img src="/style/icons/play.png" alt="*"></a> ('.$file_id['music_people'].')';
            } else {
                echo '<a href="?showinfo&play=0"><img src="/style/icons/play.png" alt="*"></a> ('.$file_id['music_people'].') <img src="/style/icons/ok.gif" alt="*">';
            }
            echo '</div>';
        }

        $_SESSION['page']=1;
        
        require 'inc/komm.php';
        echo '<div class="foot">';
        echo '<img src="/style/icons/str2.gif" alt="*"> <a href="/obmen'.$dir_id['dir'].'">В папку</a>';
        echo '</div>';
        require H . 'sys/inc/tfoot.php';
    }
}

require 'inc/dir.php';
require H . 'sys/inc/tfoot.php';
