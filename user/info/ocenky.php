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

only_reg();

$set['title']='Оценки';
include_once '../../sys/inc/thead.php';
title();
err();
aut(); // форма авторизации

echo '<div class="foot">' . "\n";
echo "\t" . '<img src="/style/icons/str2.gif" alt=""> <a href="/info.php">' . $user['nick'] . '</a> | Оценки<br />' . "\n";
echo '</div>' . "\n";

$k_post = $db->query(
    "SELECT COUNT( * ) FROM `gallery_rating` WHERE `avtor`=?i",
                     [$user['id']])->el();

if (!$k_post) {
    echo '<div class="mess">' . "\n\t";
    echo 'Нет оценок' . "\n";
    echo '</div>' . "\n";
} else {
    $k_page=k_page($k_post, $set['p_str']);
    $page=page($k_page);
    $start=$set['p_str']*$page-$set['p_str'];
    echo '<!-- ./ listing -->'."\n";
    $q=$db->query("SELECT r.id_user, r.id_foto, r.avtor, r.`like`, r.`read`, r.`time`, f.id, f.name, f.ras, f.id_gallery, u.nick FROM `gallery_rating` r
JOIN gallery_foto f ON f.id=r.id_foto
JOIN `user` u ON u.id=r.id_user
WHERE r.`avtor`=?i ORDER BY r.`time` DESC LIMIT ?i, ?i",
                [$user['id'], $start, $set['p_str']]);
    $num=0;
    while ($post = $q->row()) {
        if ($post['id'] && $post['id_user']) {
            //-----------зебра-----------//
            if ($num==0) {
                echo '<div class="nav1">'."\n";
                $num=1;
            } elseif ($num==1) {
                echo '<div class="nav2">'."\n";
                $num=0;
            }
            //---------------------------//
            if ($post['read']==1) {
                $color = '<span style="color:red;">';
                $color2 = '</span>';
            } else {
                $color = null;
                $color2 = null;
            }
            echo "<table>\n";
            echo "\t".'<tr>'."\n";
            echo "\t\t".'<td style="vertical-align:top;">'."\n\t\t\t";
            echo '<p>';
            status($post['id_user']) . group($post['id_user']);
            echo '<a href="/info.php?id=' . $post['id_user'] . '">' . $post['nick'] . '</a> ' . medal($post['id_user']) . ' ' . online($post['id_user']) . '</p>'."\n";
            echo "\t\t\t".'<img src="/style/icons/' . $post['like'] . '.png" alt="" /> ' . $color . vremja($post['time']) . $color2 . "\n";
            echo "\t\t".'</td>'."\n";
            echo "\t\t".'<td style="vertical-align:top;">'."\n";
            echo "\t\t\t".'<a href="/foto/' . $user['id'] . '/' . $post['id_gallery'] . '/' . $post['id'] . '/"><img class="show_foto" src="/foto/foto' .
            ($set['web'] ? "128" : "50") . '/' . $post['id'] . '.' . $post['ras'] . '" alt="' . $post['name'] . '" align="right" /></a>'."\n";
            echo "\t\t".'</td>'."\n";
            echo "\t".'</tr>'."\n";
            echo '</table>'."\n";
            echo '</div>'."\n";
        } else {
            $db->query("DELETE FROM `gallery_rating` WHERE `avtor`=?i AND `id_foto`=?i",
                       [$post['avtor'], $post['id_foto']]);
        }
    }
    $db->query("UPDATE `gallery_rating` SET `read`=? WHERE `avtor`=?i AND `read`=?",
               ['0', $user['id'], '1']);
    if ($k_page>1) {
        str("?", $k_page, $page);
    } // Вывод страниц
}
echo '<div class="foot">'."\n";
echo '<img src="/style/icons/str2.gif" alt="" /> <a href="/info.php">' . $user['nick'] . '</a> | Оценки'."\n";
echo '</div>'."\n";

include_once '../../sys/inc/tfoot.php';
