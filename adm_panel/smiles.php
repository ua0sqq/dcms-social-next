<?php
/**
 * & CMS Name :: DCMS-Social
 * & Author   :: Alexandr Andrushkin
 * & Contacts :: ICQ 587863132
 * & Site     :: http://dcms-social.ru
 */
include_once '../sys/inc/home.php';
include_once H.'sys/inc/start.php';
include_once H.'sys/inc/compress.php';
include_once H.'sys/inc/sess.php';
include_once H.'sys/inc/settings.php';
include_once H.'sys/inc/db_connect.php';
include_once H.'sys/inc/ipua.php';
include_once H.'sys/inc/fnc.php';
include_once H.'sys/inc/user.php';

only_level(3);

if (isset($_GET['id'])) {
    if (!$dir_id = $db->query("SELECT `id` FROM `smile_dir` WHERE `id`=?i", [$_GET['id']])->el()) {
        $_SESSION['err'] = '404 Not Found';
        header("Location: ?");
        exit;
    }
    // Удаление смайлов
    if (isset($_GET['del'])) {
        if ($del_id = $db->query("SELECT `id` FROM `smile` WHERE `id`=?i", [$_GET['del']])->el()) {
            if (is_file(H . 'style/smiles/' . $del_id . '.gif')) {
                unlink(H . 'style/smiles/' . $del_id . '.gif');
            }
            $db->query("DELETE FROM `smile` WHERE `id`=?i", [$del_id]);
       
            $_SESSION['message'] = 'Смайл успешно удален';
            header('Location: ?id=' . $dir_id . '&page=' . intval($_GET['page']));
            exit;
        } else {
            $_SESSION['err'] = '404 Not Found';
            header("Location: ?");
            exit;
        }
    }
    // Загрузка смайлов
    if (isset($_GET['act']) && $_GET['act'] == 'add_smile' && isset($_GET['ok']) && isset($_POST['forms'])) {
        $forms = intval($_POST['forms']);
        if (isset($_FILES)) {
            for ($i = 0; $i < $forms; $i++) {
                if (isset($_FILES['file_' . $i]) && preg_match('#^\.|\.jpg|\.png$|\.gif$|\.jpeg$#i', $_FILES['file_' . $i]['name'])
                && filesize($_FILES['file_' . $i]['tmp_name']) > 0 && isset($_POST['smile_' . $i])) {
                    $file = text($_FILES['file_' . $i]['name']);
                    $smile = trim($_POST['smile_' . $i]);
                    $smile_id = $db->query(
                    "INSERT INTO `smile` (`smile`,`dir`) VALUES(?, ?i)",
                                 [$smile, $_GET['id']])->id();
                
                    if (move_uploaded_file($_FILES['file_' . $i]['tmp_name'], H . 'style/smiles/' . $smile_id . '.gif')) {// TODO: ???
                        $_SESSION['message'] = 'Выгрузка прошла успешно';
                    }
                } else {
                    $err = 'Файл (' . htmlspecialchars($_POST['smile_' . $i]) . ') не выгружен';
                }
            }
        }
    }
}
/*
========================
Удаление категорий
========================
*/
if (isset($_GET['delete'])) {
    $q = $db->query(
        "SELECT `id` FROM `smile` WHERE `dir`=?i",
                    [$_GET['delete']])->col();
    
    foreach ($q as $post_id) {
        if (is_file(H . 'style/smiles/' . $post_id . '.gif')) {
            unlink(H . 'style/smiles/' . $post_id . '.gif');
        }
        $list[] = $post_id;
    }
    $db->query("DELETE FROM `smile` WHERE `id` IN(?li)", [$list]);
    $db->query("DELETE FROM `smile_dir` WHERE `id`=?i", [$_GET['delete']]);
    
    $_SESSION['message'] = 'Категория успешно удалена';
    header("Location: ?");
    exit;
}

$set['title'] = 'Управление смайлами';
include_once H.'sys/inc/thead.php';
err();
title();
aut();

if (isset($_GET['id'])) {
    // Форма загрузки смайлов
    if (isset($_GET['act']) && $_GET['act'] == 'add_smile') {
        if (isset($_POST['forms'])) {
            $forms = intval($_POST['forms']);
        } elseif (isset($_SESSION['forms'])) {
            $forms = intval($_SESSION['forms']);
        } else {
            $forms = 1;
        }
        
        $_SESSION['forms'] = $forms; ?>
		<form action="?id=<?=intval($_GET['id'])?>&amp;act=add_smile" method="post">
		<p>Количество файлов:
		<p><input type="text" name="forms" value="<?=$forms?>"/>
		<p><input class="submit" type="submit" value="Показать формы" /></p>
		</form>		
		<form enctype="multipart/form-data" action="?id=<?=intval($_GET['id'])?>&amp;act=add_smile&amp;ok" method="post">
<!--		Количество файлов:<br / -->
		<input type="hidden" name="forms" value="<?=$forms?>"/><br />
<!--		<input class="submit" type="submit" value="Показать формы" /><br />-->
		<?php
        for ($i=0; $i < $forms; $i++) {
            echo '<p>' . ($i + 1) . ') Файл: <p><input name="file_' . $i . '" type="file" />';
            echo '<p>' . ($i + 1) . ') Смайл(например :-) или :-D .....)<p><input type="text" name="smile_' . $i . '" maxlength="32" />';
        } ?>
		<p><input type="submit" value="Добавить" />
		<p><a href="?id=<?=intval($_GET['id'])?>">Назад</a></p>
		</form>
		<?php
    }
    /*
    ========================
    Вывод смайлов
    ========================
    */
    $k_post = $db->query("SELECT COUNT(*) FROM `smile` WHERE `dir`=?i", [$_GET['id']])->el();
    $k_page = k_page($k_post, $set['p_str']);
    $page = page($k_page);
    $start = $set['p_str']*$page-$set['p_str']; ?><table class="post"><?php
    if ($k_post == 0) {
        ?><div class="mess">Список смайлов пуст</div><?php
    }
    $q = $db->query(
        "SELECT * FROM `smile` WHERE `dir`=?i ORDER BY id DESC LIMIT ?i, ?i",
                    [$_GET['id'], $start, $set['p_str']]);
    while ($post = $q->row()) {
        // Лесенка
        echo '<div class="' . ($num % 2 ? "nav1" : "nav2") . '">';
        $num++; ?>
		<img src="/style/smiles/<?=$post['id']?>.gif" alt="smile"/> <?=text($post['smile'])?> 
		
		<a href="?id=<?=intval($_GET['id'])?>&amp;edit=<?=$post['id']?>&amp;page=<?=$page?>"><img src="/style/icons/edit.gif" alt="*"></a> 
		<a href="?id=<?=intval($_GET['id'])?>&amp;del=<?=$post['id']?>&amp;page=<?=$page?>"><img src="/style/icons/delete.gif" alt="*"></a>
		<?php
        /*
        ========================
        Редактирование смайлов
        ========================
        */
        if (isset($_GET['edit']) && $_GET['edit'] == $post['id']) {
            // Редактирование смайлов
            
            if (isset($_POST['sav'])) {
                $smile = trim($_POST['smile']);
                if (strlen2($smile) < 1) {
                    $err = 'Названее не менее 1 символа';
                }
                if (!isset($err)) {
                    $db->query(
                        "UPDATE `smile` SET `smile`=? WHERE `id`=?i",
                               [$smile, $post['id']]
                    );
                    $_SESSION['message'] = 'Изменения приняты';
                    header("Location: ?id=$post[dir]&page=$page");
                    exit;
                }
            } ?>
			<form method="post" action="?id=<?=$post['dir']?>&amp;edit=<?=$post['id']?>&amp;page=<?=$page?>">
			<?=(isset($err) ? '<font color="red">' . $err . '</font><br />' : null)?>
			Смайл (например :-) ..)<br />
			<input type="text" name="smile" maxlength="32" value="<?=text($post['smile'])?>"/><br />
			<input type="submit" name="sav" value="Изменить" />
			</form>
			<?php
        } ?></div><?php
    } ?></table><?php
    if ($k_page>1) {
        str('?id=' . intval($_GET['id']) . '&amp;', $k_page, $page);
    } ?>
	<div class="foot">
	<img src="/style/icons/str.gif" alt="*" /> <a href="?id=<?=intval($_GET['id'])?>&amp;act=add_smile">Добавить смайл</a>
	</div>
	<div class="foot">
	<img src="/style/icons/str.gif" alt="*" /> <a href="smiles.php">Категории смайлов</a>
	</div>
	<?php
    include_once H.'sys/inc/tfoot.php';
    exit;
}
/*
========================
Создание категории
========================
*/
if (isset($_GET['act']) && $_GET['act'] == 'add_kat') {
    if (isset($_POST['save'])) {
        $name = trim($_POST['name']);
        if (strlen2($name) < 1) {
            $err = 'Слишком короткое название';
        }
        
        if (!isset($err)) {
            $db->query("INSERT INTO `smile_dir` (`name` ) VALUES (?)", [$name]);
            
            $_SESSION['message'] = 'Категория успешно создана';
            header("Location: ?act=add_kat");
            exit;
        }
    }
    
    err(); ?>
	<form method="post" action="?act=add_kat">
	Название<br />
	<input type="text" name="name" maxlength="32" /><br />
	<input type="submit" name="save" value="Добавить" />
	</form>
	<?php
}
/*
========================
Вывод категорий
========================
*/
$k_post = $db->query("SELECT COUNT( * ) FROM `smile_dir`")->el();
?><table class="post"><?php
if ($k_post == 0) {
    ?><div class="mess">Нет категорий</div><?php
}
$q = $db->query("SELECT sm.*, (SELECT COUNT( * ) FROM `smile` WHERE `dir`=sm.id) as cnt FROM `smile_dir` sm");
while ($post = $q->row()) {
    // Лесенка
    echo '<div class="' . ($num % 2 ? "nav1" : "nav2") . '">';
    $num++; ?>
	<img src="/style/themes/<?=$set['set_them']?>/loads/14/dir.png" alt="*"> 
	<a href="?id=<?=$post['id']?>"><?=text($post['name'])?></a> (<?=$post['cnt']?>)
	
	<a href="?edit=<?=$post['id']?>"><img src="/style/icons/edit.gif" alt="*"></a> 
	<a href="?delete=<?=$post['id']?>"><img src="/style/icons/delete.gif" alt="*"></a>
	</div>
	<?php
    /*
    ========================
    Редактирование категорий
    ========================
    */
    if (isset($_GET['edit']) && $_GET['edit'] == $post['id']) {
        if (isset($_POST['sav'])) {
            $name = trim($_POST['name']);
        
            if (strlen2($name) < 1) {
                $err = 'Название не менее 1 символа';
            }
            
            if (!isset($err)) {
                $db->query(
                    "UPDATE `smile_dir` SET `name`=? WHERE `id`=?i",
                           [$name, $_GET['edit']]
                );
                
                $_SESSION['message'] = 'Категория успешно переименована';
                header("Location: ?");
                exit;
            }
        } ?>
		<form method="post" action="?edit=<?=$post['id']?>">
		<?=(isset($err) ? '<font color="red">' . $err . '</font><br />' : null)?>
		Название:<br />
		<input type="text" name="name" maxlength="32" value="<?=text($post['name'])?>"/><br />
		<input type="submit" name="sav" value="Изменить" />
		</form>
		<?php
    } ?></div><?php
}
?></table><?php
?>
<div class="foot">
<img src="/style/icons/str.gif" alt="*"> <a href="?act=add_kat">Добавить категорию</a><br />
</div>
<?php

include_once H.'sys/inc/tfoot.php';

?>