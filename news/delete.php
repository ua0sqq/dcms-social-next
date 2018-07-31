<?php
include_once '../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

$del = filter_input_array(INPUT_GET, FILTER_VALIDATE_INT);

// Удаление комментариев
if (isset($del['id']) && $db->query(
    "SELECT COUNT(*) FROM `news_komm` WHERE `id`=?i",
                             [$del['id']]
)->el()) {
    // TODO: доделать удаление посл. поста юзером
    $post = $db->query(
        "SELECT nwk.id, u.id AS id_user, u.`level` FROM `news_komm` nwk
LEFT JOIN `user` u ON u.id=nwk.id_user
WHERE nwk.`id`=?i",
                       [$del['id']]
    )->row();
    
    if (isset($user) && ($user['level'] > $post['level'] || $user['level'] == 10)) {
        $db->query(
        "DELETE FROM `news_komm` WHERE `id`=?i",
               [$post['id']]
    );
    }
    
    $_SESSION['message'] = 'Комментарий успешно удален';

    if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] != null) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        header('Location: index.php?' . SID);
    }
    exit;
}
  
// Удаление новости
if (isset($del['news_id']) && $news_id = $db->query(
    "SELECT `id` FROM `news` WHERE `id`=?i",
                                         [$del['news_id']]
)->el()) {
    if (user_access('adm_news')) {
        $db->query(
            "DELETE FROM `news` WHERE `id`=?i",
                   [$news_id]
        );
        $db->query(
            "DELETE FROM `news_komm` WHERE `id_news`=?i",
                   [$news_id]
        );
        
        $_SESSION['message'] = 'Новость успешно удалена';
    }
    
    header('Location: index.php?' . SID);
    exit;
}
http_response_code(404);
header('Location: index.php?' . SID);
exit;
