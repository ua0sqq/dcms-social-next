<?php
err();
aut();

$k_post=$db->query("SELECT COUNT(*) FROM `forum_r` WHERE `id_forum`=?i", [$forum['id']])->el();
$k_page=k_page($k_post, $set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];

echo "<div class='post'>\n";

$q=$db->query(
    "SELECT rzd.*, (
SELECT COUNT(*) FROM `forum_p` WHERE `id_forum` = rzd.id_forum AND `id_razdel` = rzd.id) posts, (
SELECT COUNT(*) FROM `forum_t` WHERE `id_forum` = rzd.id_forum AND `id_razdel` =  rzd.id) themes
FROM `forum_r` rzd WHERE rzd.`id_forum`=?i ORDER BY rzd.`time` DESC LIMIT ?i OFFSET ?i",
              [$forum['id'], $set['p_str'], $start]
)->assoc();
if (!count($q)) {
    echo "  <div class='mess'>\n";
    echo "Нет разделов\n";
    echo "  </div>\n";
}
foreach ($q as $razdel) {
    /*-----------зебра-----------*/
    if ($num==0) {
        echo "  <div class='nav1'>\n";
        $num=1;
    } elseif ($num==1) {
        echo "  <div class='nav2'>\n";
        $num=0;
    }
    /*---------------------------*/
    echo "<a href='/forum/$forum[id]/$razdel[id]/'>" . text($razdel['name']) . "</a> [" . $razdel['posts'] . '/' . $razdel['themes'] . "]\n";
    if (!empty($razdel['opis'])) {
        echo '<br/><span style="color:#666;">'.output_text($razdel['opis']).'</span>';
    }
    echo "   </div>\n";
}
echo "</div>\n";
if ($k_page>1) {
    str("/forum/$forum[id]/?", $k_page, $page);
} // Вывод страниц
