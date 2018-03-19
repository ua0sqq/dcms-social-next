<?php
include_once '../../sys/inc/start.php';
include_once '../../sys/inc/compress.php';
include_once '../../sys/inc/sess.php';
include_once '../../sys/inc/home.php';
include_once '../../sys/inc/settings.php';
include_once '../../sys/inc/db_connect.php';
//include_once '../../sys/inc/ipua.php';
//include_once '../../sys/inc/fnc.php';
//include_once '../../sys/inc/user.php';
if (isset($_SESSION['id_user'])) {
    if (!isset($_GET['dir'])) {
        $_SESSION['category'] = 21;
    } else {
        $_SESSION['category'] = $_GET['dir'];
    }
	// Находим id категории если ранее не смотрели
    if (!isset($_SESSION['category']) || $cat = $db->query(
    "SELECT `id` FROM `smile_dir` WHERE `id`=?i",
                                                [$_SESSION['category']]
)->el()) {
        $_SESSION['category'] = $cat;
    }
    $q = $db->query(
    "SELECT * FROM `smile` WHERE `dir`=?i ORDER BY id DESC ",
                [$_SESSION['category']]
);
    echo '<div class="layer">';
    while ($post = $q->row()) {
        echo '<a href="javascript:emoticon(\''.$post['smile'].'\')"><img src="/style/smiles/' . $post['id'] . '.gif" title="' . stripcslashes(htmlspecialchars($post['smile'])) . '" /></a>';
    }
    echo '</div>';
    $q = $db->query("SELECT smd.*, (
SELECT COUNT(*) FROM `smile` WHERE `dir` = smd.id) cnt
FROM `smile_dir` smd ORDER BY smd.id ASC");
    echo '<div class="title">Категории</div>';
    while ($dir = $q->row()) {
        if ($dir['cnt']) {
            echo '<p><a onclick="showContent2(\'/ajax/php/smiles.php?dir='.$dir['id'].'\')" class="onclick">' . stripcslashes(htmlspecialchars($dir['name'])) . '</a> ';
            echo '('.$dir['cnt'].')</p> ';
        }
    }
}
