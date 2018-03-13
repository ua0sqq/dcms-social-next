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

$set['title']='Редактирование';
include_once '../../sys/inc/thead.php';
$args = [
		 'msg' => FILTER_DEFAULT,
		 'url' =>[
				  'filter'  => FILTER_VALIDATE_URL,
				  'options' => [
								'default'   => '',
        ],
    ],
		 'name_url' => FILTER_DEFAULT,
		 'title' => FILTER_DEFAULT,
		 ];
$input = filter_input_array(INPUT_POST, $args);
unset($args);
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (isset($user) && $user['level'] < 3) {
    header("Location: /");
}

title();
aut();

// Редактирование поста
if (isset($_GET['act']) && $_GET['act'] == 'edit') {
    if (isset($_GET['id']) && $db->query("SELECT COUNT(*) FROM `rules_p` WHERE `id` = ?i", [$id])->el()) {
        
		$post=$db->query("SELECT * FROM `rules_p` WHERE `id` = ?i", [$id])->row();
        $ank=$db->query("SELECT * FROM `user` WHERE `id` = $post[id_user] LIMIT 1")->row();
        if (isset($_POST['change']) && isset($_GET['id']) && isset($_POST['name']) && $_POST['name']!=null) {
                    
            $db->query("UPDATE `rules_p` SET `msg` = ? WHERE `id` = ?i",
					   [$input['msg'], $id]);
            $_SESSION['message'] = 'Пункт меню успешно изменен';
            header("Location: post.php?id=$post[id_news]");
            exit;
        }
    }
    if (isset($_GET['id']) && isset($_GET['act']) && $_GET['act'] == 'edit') {
        echo '<form action="?id=' . $post['id'] . '&amp;act=edit" method="post">';
        echo 'Редактирование поста:<br />';
        echo '<textarea name="name">' . text($post['msg']) . '</textarea><br />';
        echo '<input class="submit" name="change" type="submit" value="Изменить" /><br />';
        echo '</form>';
    }
}
// Редактирование пункта
if (isset($_GET['act']) && $_GET['act'] == 'edits') {
    if (isset($_GET['id']) && $db->query("SELECT COUNT(*) FROM `rules` WHERE `id` = ?i", [$id])->el()) {
        
		$post = $db->query("SELECT * FROM `rules` WHERE `id` = ?i", [$id])->row();
        $ank = $db->query("SELECT * FROM `user` WHERE `id` = $post[id_user] LIMIT 1")->row();
        
        if (isset($_POST['change']) && $id) {

            $db->query("UPDATE `rules` SET `msg` = ?, `title` = ?, `url` = ?, `name_url` = ? WHERE `id` = ?i",
					   [$input['msg'], $input['title'], $input['url'], $input['name_url'], $id]);
			
            $_SESSION['message'] = 'Пункт меню успешно изменен';
            header("Location: index.php");
            exit;
        }
    }
    if (isset($_GET['id']) && $_GET['id'] == $post['id'] && isset($_GET['act']) && $_GET['act']=='edits') {
        echo '<form action="?id=' . $post['id'] . '&amp;act=edits" method="post">';
        echo 'Название ссылки:<br /><input name="name_url" size="16" value="' . text($post['name_url']) . '" type="text" /><br />';
        echo 'Адрес ссылки:<br /><input name="url" size="16" value="' . text($post['url']) . '" type="text" /><br />';
        echo 'Название пункта:<br /><input name="title" size="16" value="' . text($post['title']) . '" type="text" /><br />';
        echo 'Редактирование текста:<br />';
        echo '<textarea name="msg">' . text($post['msg']) . '</textarea><br />';
        echo '<input class="submit" name="change" type="submit" value="Изменить" /><br />';
        echo '</form>';
    }
}

echo '<div class="foot"><img src="/style/icons/str2.gif" alt="*"/> <a href="index.php">Информация</a></div>';

include_once '../../sys/inc/tfoot.php';
