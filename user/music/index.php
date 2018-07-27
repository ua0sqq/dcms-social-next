<?php
/*
=======================================
Музыка юзеров для Dcms-Social
Автор: Искатель
---------------------------------------
Этот скрипт распостроняется по лицензии
движка Dcms-Social.
При использовании указывать ссылку на
оф. сайт http://dcms-social.ru
---------------------------------------
Контакты
ICQ: 587863132
http://dcms-social.ru
=======================================
*/
include_once '../../sys/inc/start.php';
include_once '../../sys/inc/compress.php';
include_once '../../sys/inc/sess.php';
include_once '../../sys/inc/home.php';
include_once '../../sys/inc/settings.php';
include_once '../../sys/inc/db_connect.php';
include_once '../../sys/inc/ipua.php';
include_once '../../sys/inc/fnc.php';
include_once '../../sys/inc/user.php';
include_once '../../sys/inc/files.php';

include_once '../../sys/inc/thead.php';

if (isset($user)) {
    $ank['id'] = $user['id'];
    $ank['nick'] = $user['nick'];
} else {
    $ank = null;
}
if ($id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)) {
    // Определяем id автора плейлиста
    $ank = $db->query(
        'SELECT `id`, `nick` FROM `user` WHERE `id`=?i',
                [$id]
    )->row();
}
if (!$ank) {
    $set['title'] = 'Error!';
    title();
    echo '<div class="mess">Ошибка! Пользователь не найден</div>';
    include_once '../../sys/inc/tfoot.php';
    exit;
}

$set['title'] = 'Музыка '.$ank['nick'];
title();
aut();
?>
<style>
#ajaxsPlayer{
margin:auto;
}
.button{
float:left;
}
.play{
width:20px;
height:20px;
background-image:url(/style/icons/play.png);
display:block;
cursor:pointer;
margin:2px;
}
.pause{
width:20px;
height:20px;
background-image:url(/style/icons/pause.png);
display:block;
cursor:pointer;
display:none;
margin:2px;
}
.nameTrack{
font: 14px/90% Helvetica, 'Lucida Grande', 'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
color: #666666;
padding:5px 30px;
vertical-align:middle;
width:90%;
}
.clear{
	clear:both;	
}
</style>
<div id="ajaxsPlayer">
<div class="foot">
	<img src="/style/icons/str2.gif" alt="" /> <a href="/info.php?id=<?php echo $ank['id'];?>"><?php echo $ank['nick'];?></a> |
	<strong>Музыка</strong>
</div><?php
if ($set['web']) {
    $set['p_str'] = 100;
}
$k_post=$db->query(
    "SELECT COUNT( * ) FROM `user_music` WHERE `id_user`=?i",
                   [$ank['id']])->el();

if (!$k_post) {
    echo "<div class='mess'>";
    echo "Нет треков в плейлисте\n";
    echo '</div>';
} else {
    $k_page=k_page($k_post, $set['p_str']);
    $page=page($k_page);
    $start=$set['p_str']*$page-$set['p_str'];
    $track = 0;

    $q=$db->query(
    "SELECT usm.*, of.name, of.ras, of.size, od.dir
FROM `user_music` usm
JOIN `obmennik_files` of ON of.id=usm.id_file
JOIN `obmennik_dir` od ON od.id=of.id_dir
WHERE usm.`id_user`=?i ORDER BY usm.`id` DESC LIMIT ?i, ?i",
              [$ank['id'], $start, $set['p_str']]);

    while ($post = $q->row()) {
        if ($num==0) {
            echo "  <div class='nav1'>\n";
            $num=1;
        } elseif ($num==1) {
            echo "  <div class='nav2'>\n";
            $num=0;
        }
        if ($webbrowser=='web') {
            echo '<div class="track">';
            echo '<div class="button">';
            echo '<div class="play" id="'.$track.'" file="/obmen'.$post['dir'].'/'.$post['id_file'].'.'.$post['ras'].'"></div>';
            echo '<div class="pause"></div>';
            echo '</div>';
            echo '<div class="nameTrack"><a href="/obmen'.$post['dir'] . $post['id_file'].'.'.$post['ras'].'">
	<img src="/style/icons/d.gif" alt="*" title="Скачать трек"></a> ' . htmlspecialchars($post['name']) . ' (' . size_file($post['size']) . ')</div>
	<div class="clear"></div>';
            echo '</div>';
        } else {
            echo '<a href="/obmen'.$post['dir'] . $post['id_file'].'.'.$post['ras'].'">
	<img src="/style/icons/d.gif" alt="*" title="Скачать трек"></a> ' . htmlspecialchars($post['name']) . ' (' . size_file($post['size']) . ')';
        }
        echo '</div>';
        $track++;
    } ?>
</div>
<?php
if ($k_page>1) {
        str('index.php?id='.$ank['id'].'&amp;', $k_page, $page);
    } // Вывод страниц
}
echo '<div class="foot">'."\n\t";
echo '<img src="/style/icons/str2.gif" alt="" /> <a href="/info.php?id=' . $ank['id'] . '">' . $ank['nick'] . '</a> | '."\n";
echo '<strong>Музыка</strong>';
echo '</div>'."\n";
?><script type="text/javascript" src="/ajax/js/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="/ajax/js/user-music.js"></script><?php
include_once '../../sys/inc/tfoot.php';

?>