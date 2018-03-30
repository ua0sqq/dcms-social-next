<?php
if (is_file('inc/opis/' . $ras . '.php')) {
    include 'inc/opis/' . $ras . '.php';
} else {
    echo 'Размер: ' . size_file($size) . '<br />' . "\n";
    $ank=$db->query('SELECT * FROM `user` WHERE `id`=?i',
                    [$post['id_user']])->row();
    echo 'Выгрузил: <a href="/info.php?id=' . $ank['id'] . '">' . $ank[nick] . '</a> ' . vremja($post['time']) . ' <br />' . "\n";
}
