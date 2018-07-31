<?php

$set['title'] = 'Фотоальбомы'; // заголовок страницы
include_once H . 'sys/inc/thead.php';
title();
aut();

$k_post = $db->query("SELECT COUNT( * ) FROM `gallery`")->el();

if (!$k_post) {
    echo '<div class="mess">'."\n\t";
    echo 'Нет фотоальбомов'."\n";
    echo '</div>'."\n";
} else {
    $k_page = k_page($k_post, $set['p_str']);
    $page = page($k_page);
    $start = $set['p_str']*$page-$set['p_str'];

    $q = $db->query("SELECT glr.*, (
SELECT COUNT( * ) FROM `gallery_foto` WHERE `id_gallery` =`glr`.`id`) cnt
FROM `gallery` glr ORDER BY glr.`time` DESC LIMIT $start, $set[p_str]");
    while ($post = $q->row()) {
        // Лесенка
        echo '<div class="' . ($num % 2 ? "nav1" : "nav2") . '">'."\n\t";
        $num++;
        echo '<p><img src="/style/themes/' . $set['set_them'] . '/loads/14/' . ($post['pass'] != null || $post['privat'] != 0 ? 'lock.gif' : 'dir.png') . '" alt="*" /> ';
        echo '<a href="/foto/' . $post['id_user'] . '/' . $post['id'] . '/">' . text($post['name']) . '</a> (' . $post['cnt'] . ' фото)</p>';
        echo '<p>';
        if ($post['opis'] == null) {
            echo 'Без описания</p>';
        } else {
            echo output_text($post['opis']) . '</p>';
        }
        echo '<p>Создан: ' . vremja($post['time_create']) . '</p>';
        echo '<p>Автор: ';
        echo user::avatar($post['id_user'], 2) . user::nick($post['id_user'], 1, 1, 1) . '</p>';
        echo '</div>'."\n";
    }
    if ($k_page>1) {
        str('?', $k_page, $page);
    } // Вывод страниц
}
if (isset($user)) {
?>
<div class="foot">
    <img src="/style/icons/str.gif" alt="ico"> <a href="/foto/<?php echo $user['id'];?>/">Мои альбомы</a>
</div>
<?php
}
include_once H . 'sys/inc/tfoot.php';
exit;
