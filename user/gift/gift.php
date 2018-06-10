<?php
/*
=======================================
Подарки для Dcms-Social
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
$db->setDebug('mydebug');
only_reg();
    // Размер подарков при выводе в браузер
    $width = ($webbrowser == 'web' ? '100' : '70');
    // Подарок
    $post = $db->query(
        "SELECT `id`, `status`, `coment`, `id_gift`, `id_ank`, `id_user`, `time` FROM `gifts_user` WHERE `id`=?i",
                       [$_GET['id']])->row();
    
    // Если записи нет кидаем на главную
    if (!$post['id']) {
        header("Location: /index.php?");
    }
    
     // Сам Подарок
    $gift = $db->query( 
        "SELECT id,name FROM `gift_list` WHERE `id`=?i",
                       [$post['id_gift']])->row();
    
     // Кому подарили
    $ank = get_user($post['id_user']);
    
     // Кто подарил
    $anketa = get_user($post['id_ank']);
    
    // Принятие подарка
    if ($post['status'] == 0 && isset($_GET['ok']) && $user['id'] == $ank['id']) {
        $db->query(
            "UPDATE `gifts_user` SET `status`=?i WHERE `id`=?i",
                   [1, $post['id']]);
        /*
        ==========================
        Уведомления
        ==========================
        */
        $db->query(
            "INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES (?i, ?i, ?i, ?, ?i)",
                   [$user['id'], $anketa['id'], $gift['id'], 'ok_gift', $time]);
            
        // Сообщение
        $_SESSION['message'] = 'Подарок от ' . $anketa['nick'] . ' принят';        
        header("Location: gift.php?id=$post[id]");
        exit;
    }
    
    // Отказ от подарка
    if ($post['status'] == 0 && isset($_GET['no']) && $user['id'] == $ank['id']) {
        $db->query("DELETE FROM `gifts_user` WHERE `id`=?i", [$post['id']]);
        /*
        ==========================
        Уведомления
        ==========================
        */
        $db->query(
            "INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES (?i, ?i, ?i, ?, ?i)",
                   [$user['id'], $anketa['id'], $gift['id'], 'no_gift', $time]);
        
        $_SESSION['message'] = 'Подарок от ' . $anketa['nick'] . ' отклонен';
        header("Location: ?new");
        exit;
    }
    
    
     // Удаление подарка
    if (isset($_GET['delete']) && ($ank['id'] == $user['id']  || $user['level'] > 2)) {
        // Запрос удаления
        $db->query("DELETE FROM `gifts_user` WHERE `id`=?i", [$post['id']]);
        
        // Сообщение
        $_SESSION['message'] = 'Подарок от ' . $anketa['nick'] . ' удален';       
        header("Location: index.php");
        exit;
    }
    
     // Заголовок страницы
    $set['title'] = 'Подарок ' . $ank['nick'] . ' ' . htmlspecialchars($gift['name']);
    include_once '../../sys/inc/thead.php';
    title();
    aut();
/*
==================================
Вывод подарка пользователя
==================================
*/
    echo '<div class="foot">';
    echo '<img src="/style/icons/str2.gif" alt="*" /> <a href="/info.php?id=' . $ank['id'] . '">' . $ank['nick'] . '</a> | <a href="/user/gift/index.php?id=' . $ank['id'] . '">Подарки</a> | <b>' . htmlspecialchars($gift['name']) . '</b>';
    echo '</div>';
    // Подарок
    echo '<div class="nav2">';
    echo '<img src="/sys/gift/' . $gift['id'] . '.png" style="max-width:' . $width . 'px;" alt="*" /><br />';
    echo htmlspecialchars($gift['name']) . ' :: ' . vremja($post['time']) . '<br />';
    echo '</div>';
    // Автор подарка
    echo '<div class="nav1">';
    echo status($anketa['id']) , group($anketa['id']) , '<a href="/info.php?id=' . $anketa['id'] . '">' . $anketa['nick'] . '</a>' , medal($anketa['id']) , online($anketa['id']) . '<br />';
    if ($post['coment']) {
        echo 'Комментарий: <br />' . output_text($post['coment']);
    }
    
    echo '</div>';
    
if ($ank['id'] == $user['id']) {
    echo '<div class="nav2">';
    
    if ($post['status'] == 0) {
        // Новый подарок - Действие
        echo '<center><img src="/style/icons/ok.gif" alt="*" /> <a href="?id=' . $post['id'] . '&amp;ok">Принять</a> ';
        echo '<img src="/style/icons/delete.gif" alt="*" /> <a href="?id=' . $post['id'] . '&amp;no">Отказаться</a></center>';
    } else {
        // Удаление
        echo '<img src="/style/icons/delete.gif" alt="*" /> <a href="/user/gift/gift.php?id=' . $post['id'] . '&amp;delete">Удалить</a>';
    }
    echo '</div>';
}
    echo '<div class="foot">';
    echo '<img src="/style/icons/str2.gif" alt="*" /> <a href="/info.php?id=' . $ank['id'] . '">' . $ank['nick'] . '</a> | <a href="/user/gift/index.php?id=' . $ank['id'] . '">Подарки</a> | <b>' . htmlspecialchars($gift['name']) . '</b>';
    echo '</div>';
include_once '../../sys/inc/tfoot.php';
