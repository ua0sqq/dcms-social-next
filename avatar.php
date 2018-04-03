<?php
include_once 'sys/inc/start.php';
include_once 'sys/inc/compress.php';
include_once 'sys/inc/sess.php';
include_once 'sys/inc/home.php';
include_once 'sys/inc/settings.php';
include_once 'sys/inc/db_connect.php';
include_once 'sys/inc/ipua.php';
include_once 'sys/inc/fnc.php';
include_once 'sys/inc/user.php';

only_reg();

$set['title']='Мой аватар';
include_once 'sys/inc/thead.php';
title();
err();
aut();
    
echo "<div class='main'>";
echo avatar($user['id'], true, 128, false);
echo "</div>";
echo "<div class='main'>";
echo "Для того что бы установить аватар на соей страничке, загрузите фото в свой фотоальбом, и нажмите ссылку \"Сделать главной\"";
echo "</div>";
// фотоальбомы
echo "<div class='main'>";echo "<img src='/style/icons/foto.png' alt='*' /> ";
echo "<a href='/foto/$user[id]/'>Фотографии</a> ";
echo "(" . $db->query(
                    'SELECT COUNT(*) FROM `gallery_foto` WHERE `id_user`=?i',
                            [$user['id']])->el() . ")";
echo "</div>";

include_once 'sys/inc/tfoot.php';
