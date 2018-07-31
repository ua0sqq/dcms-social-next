<?php
include_once '../sys/inc/start.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';
include_once H . 'sys/inc/downloadfile.php';

$id_file = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id_file && $db->query("SELECT COUNT(*) FROM `forum_files` WHERE `id`=?i", [$id_file])->el()) {
    
    $file = $db->query("SELECT * FROM `forum_files` WHERE `id`=?i", [$id_file])->row();

    if (is_file(H . 'sys/forum/files/' . $file['id'] . '.frf')) {
        if (isset($_GET['del']) && user_access('forum_post_ed')) {
            unlink(H . 'sys/forum/files/' . $file['id'] . '.frf');
            $db->query('DELETE FROM `forum_files` WHERE `id`=?i', [$file['id']]);
            $db->query('DELETE FROM `forum_files_rating` WHERE `id_file` NOT IN(SELECT `id` FROM `forum_files`)');
            $_SESSION['message'] = 'this file removed';
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {
        $db->query('UPDATE `forum_files` SET `count`=`count`+1 WHERE `id`=?i', [$file['id']]);
        downloadfile(H . 'sys/forum/files/' . $file['id'] . '.frf', $file['name'] . '.' . $file['ras'], ras_to_mime($file['ras']));
        exit;
    }
    } else {
        $db->query('DELETE FROM `forum_files` WHERE `id`=?i', [$file['id']]);
        $db->query('DELETE FROM `forum_files_rating` WHERE `id_file` NOT IN(SELECT `id` FROM `forum_files`)');
        $_SESSION['err'] = 'Error: File not found!';
        if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != null) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {
            header('Location: /forum/index.php?' . SID);
        }
    }
} else {
    header("Refresh: 3; url=/index.php");
    header("Content-type: text/html", null, 404);
    echo "<html>
<head>
<title>Ошибка 404</title>\n";
    echo "<link rel=\"stylesheet\" href=\"/style/themes/default/style.css\" type=\"text/css\" />\n";
    echo "</head>\n<body>\n<div class=\"body\"><div class=\"err\">\n";
    echo "Нет такой страницы\n";
    echo "<br />";
    echo "<a href=\"/index.php\">На главную</a>";
    echo "</div>\n</div>\n</body>\n</html>";
    exit;
}
