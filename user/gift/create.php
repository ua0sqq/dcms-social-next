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
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

only_level(3);
$width = ($webbrowser == 'web' ? '100' : '70'); // Размер подарков при выводе в браузер
$inp_get = filter_input_array(INPUT_GET, FILTER_VALIDATE_INT);

/*
==================================
Редактирование подарков
==================================
*/
if (isset($inp_get['edit_gift']) && isset($inp_get['category'])) {
    $category = $db->query(
        "SELECT * FROM `gift_categories` WHERE `id`=?i",
                           [$inp_get['category']])->row();
    $gift = $db->query(
        "SELECT * FROM `gift_list` WHERE `id`=?i",
                       [$inp_get['edit_gift']])->row();
    if (!$category || !$gift) {
        $_SESSION['message'] = 'Нет такой категории или подарка';
        header("Location: ?");
        exit;
    }
    if (isset($_POST['name']) && isset($_POST['money'])) { // Редактирование записи
        $name = trim($_POST['name']);
        $money = intval($_POST['money']);
        
        if ($money < 1) {
            $err = 'Укажите стоимость подарка';
        }
        
        if (strlen2($name) < 2) {
            $err = 'Короткое название';
        }
        if (strlen2($name) > 128) {
            $err = 'Длина названия превышает предел в 128 символов';
        }
        
        if (!isset($err)) {
            $db->query(
                "UPDATE `gift_list` SET `name`=?i, `money`=?i, `id_category`=?i WHERE `id`=?i",
                       [$name, $money, $category['id'], $gift['id']]);
            
            $_SESSION['message'] = 'Подарок успешно отредактирован';
            header('Location: ?category=' . $category['id'] . '&page=' . intval($inp_get['page']));
            exit;
        }
    }
    // Удаление подарка
    if (isset($_GET['delete'])) { 
        unlink(H.'sys/gift/' . $gift['id'] . '.png');
        
        $db->query("DELETE FROM `gift_list` WHERE `id`=?i", [$gift['id']]);
        $db->query("DELETE FROM `gifts_user` WHERE `id_gift`=?i", [$gift['id']]);
        
        $_SESSION['message'] = 'Подарок успешно удален';
        
        header("Location: ?category=$category[id]&page=" . intval($inp_get['page']));
        exit;
    }
    $set['title'] = 'Редактирование подарка';
    include_once H . 'sys/inc/thead.php';
    title();
    aut();
    err();
    
    echo '<div class="foot">';
    echo '<img src="/style/icons/str2.gif" alt="*" />  <a href="?">Категории</a> |  <a href="?category=' . $category['id'] . '">' . htmlspecialchars($category['name']) . '</a> | <b>Добавление подарка</b><br />';
    echo '</div>';
    // Форма редактирования подарка
    echo '<form class="main" method="post" enctype="multipart/form-data"  action="?category=' . $category['id'] . '&amp;edit_gift=' . $gift['id'] . '&amp;page=' . $inp_get['page'] . '">';
    echo '<img src="/sys/gift/' . $gift['id'] . '.png" style="max-width:' . $width . 'px;" alt="*" /><br />';
    echo 'Название:<br /><input type="text" name="name" value="' . htmlspecialchars($gift['name']) . '" /><br />';
    echo 'Цена:<br /><input type="text" name="money" value="' . $gift['money'] . '" style="width:30px;"/><br />';
    echo '<input value="Сохранить" type="submit" />';
    echo '</form>';
    
    echo '<div class="foot">';
    echo '<img src="/style/icons/str2.gif" alt="*" />  <a href="?">Категории</a> |  <a href="?category=' . $category['id'] . '">' . htmlspecialchars($category['name']) . '</a> | <b>Добавление подарка</b><br />';
    echo '</div>';
} elseif /*
==================================
Добавление подарков
==================================
*/
(isset($_GET['add_gift']) && isset($inp_get['category'])) {
    $category = $db->query(
        "SELECT * FROM `gift_categories` WHERE `id`=?i",
                           [$inp_get['category']])->row();
    if (!$category) {
        $_SESSION['message'] = 'Нет такой категории';
        header("Location: ?");
        exit;
    }
    if (isset($_POST['name']) && isset($_POST['money']) && isset($_FILES['gift'])) { // Создание записи
        $name = trim($_POST['name']);
        $money = intval($_POST['money']);
        
        if ($money < 1) {
            $err = 'Укажите стоимость подарка';
        }
        
        if (strlen2($name) < 2) {
            $err = 'Короткое название';
        }
        if (strlen2($name) > 128) {
            $err = 'Длина названия превышает предел в 128 символов';
        }
        
        if (!isset($err)) {
            $file_id = $db->query(
                "INSERT INTO `gift_list` (`name`, `money`, `id_category`) VALUES(?, ?i, ?i)",
                                  [$name, $money, $category['id']])->id();
            move_uploaded_file($_FILES['gift']['tmp_name'], H.'sys/gift/' . $file_id . '.png');
            
            $_SESSION['message'] = 'Подарок успешно добавлен';
            header("Location: ?category=" . $category['id']);
            exit;
        }
    }
    $set['title'] = 'Добавление подарка';
    include_once H . 'sys/inc/thead.php';
    title();
    aut();
    err();
    echo '<div class="foot">';
    echo '<img src="/style/icons/str2.gif" alt="*" />  <a href="?">Категории</a> |  <a href="?category=' . $category['id'] . '">' . htmlspecialchars($category['name']) . '</a> | <b>Добавление подарка</b><br />';
    echo '</div>';
    // Форма создания категории
    echo '<form class="main" method="post" enctype="multipart/form-data"  action="?category=' . $category['id'] . '&amp;add_gift">';
    echo 'Название:<br /><input type="text" name="name" value="" /><br />';
    echo 'Цена:<br /><input type="text" name="money" value="" style="width:30px;"/><br />';
    echo 'Подарок:<br /><input name="gift" accept="image/*,image/png" type="file" /><br />';
    echo '<input value="Добавить" type="submit" />';
    echo '</form>';
    
    echo '<div class="foot">';
    echo '<img src="/style/icons/str2.gif" alt="*" />  <a href="?">Категории</a> |  <a href="?category=' . $category['id'] . '">' . htmlspecialchars($category['name']) . '</a> | <b>Добавление подарка</b><br />';
    echo '</div>';
} elseif (isset($inp_get['category'])) {
    /*
    ==================================
    Вывод подарков
    ==================================
    */

    $category = $db->query(
        "SELECT * FROM `gift_categories` WHERE `id`=?i",
                           [$inp_get['category']])->row();
    if (!$category) {
        $_SESSION['message'] = 'Нет такой категории';
        header("Location: ?");
        exit;
    }
    $set['title'] = 'Список подарков';
    include_once H . 'sys/inc/thead.php';
    title();
    aut();
    err();
    echo '<div class="foot">';
    echo '<img src="/style/icons/str2.gif" alt="*" />  <a href="?">Категории</a> | <b>' . htmlspecialchars($category['name']) . '</b><br />';
    echo '</div>';
    
    // Список подарков
    
    $k_post = $db->query(    
        "SELECT COUNT( * ) FROM `gift_list`  WHERE `id_category`=?i",
                         [$category['id']])->el();
    if ($k_post == 0) {
        echo '<div class="mess">';
        echo 'Нет подарков';
        echo '</div>';
    }
    $k_page=k_page($k_post, $set['p_str']);
    $page=page($k_page);
    $start=$set['p_str']*$page-$set['p_str'];
    $q = $db->query(
        "SELECT name,id,money FROM `gift_list` WHERE `id_category`=?i ORDER BY `id` LIMIT ?i OFFSET ?i",
                    [$category['id'], $set['p_str'], $start]);
    while ($post = $q->row()) {
        /*-----------зебра-----------*/
        if ($num==0) {
            echo '<div class="nav1">';
            $num=1;
        } elseif ($num==1) {
            echo '<div class="nav2">';
            $num=0;
        }
        /*---------------------------*/
        echo '<img src="/sys/gift/' . $post['id'] . '.png" style="max-width:' . $width . 'px;" alt="*" /><br />';
        echo 'Название: ' . htmlspecialchars($post['name']) . '<br /> ';
        echo 'Стоимость: ' . $post['money'] . ' ' . $sMonet[0];
        echo ' <a href="create.php?category=' . $category['id'] . '&amp;edit_gift=' . $post['id'] . '&amp;page=' . $page . '"><img src="/style/icons/edit.gif" alt="*" /></a> ';
        echo ' <a href="create.php?category=' . $category['id'] . '&amp;edit_gift=' . $post['id'] . '&amp;page=' . $page . '&amp;delete"><img src="/style/icons/delete.gif" alt="*" /></a> ';
        echo '</div>';
    }
    if ($k_page>1) {
        str('create.php?category=' . $inp_get['category'] . '&amp;', $k_page, $page);
    } // Вывод страниц
    echo '<div class="foot">';
    echo '<img src="/style/icons/ok.gif" alt="*" />  <a href="?category=' . $category['id'] . '&amp;add_gift">Добавить подарок</a><br />';
    echo '</div>';
    echo '<div class="foot">';
    echo '<img src="/style/icons/str2.gif" alt="*" />  <a href="?">Категории</a> | <b>' . htmlspecialchars($category['name']) . '</b><br />';
    echo '</div>';
} elseif (isset($_GET['add_category'])) {
    /*
    ==================================
    Создание категорий
    ==================================
    */

    if (isset($_POST['name']) && $_POST['name'] != null) { // Создание записи
        $name = trim($_POST['name']);
        
        if (strlen2($name) < 2) {
            $err='Короткое название';
        }
        if (strlen2($name) > 128) {
            $err='Длина названия превышает предел в 128 символов';
        }
        
        if (!isset($err)) {
            $db->query("INSERT INTO `gift_categories` (`name`) VALUES(?)", [$name]);
            
            $_SESSION['message'] = 'Категория успешно добавлена';
            header("Location: ?");
            exit;
        }
    }
    $set['title'] = 'Создание категорий';
    include_once H . 'sys/inc/thead.php';
    title();
    aut();
    err();
    echo '<div class="foot">';
    echo '<img src="/style/icons/str2.gif" alt="*" />  <a href="?">Категории</a><br />';
    echo '</div>';
    
    // Форма создания категории
    echo '<form class="main" method="post" action="?add_category">';
    echo 'Название:<br /><input type="text" name="name" value="" /><br />';
    echo '<input value="Добавить" type="submit" />';
    echo '</form>';
    
    echo '<div class="foot">';
    echo '<img src="/style/icons/str2.gif" alt="*" />  <a href="?">Категории</a><br />';
    echo '</div>';
} elseif (isset($inp_get['edit_category'])) {
    /*
    ==================================
    Редактирование категорий
    ==================================
    */

    $category = $db->query(
        "SELECT * FROM `gift_categories` WHERE `id`=?i",
                           [$inp_get['edit_category']])->row();
    if (!$category) {
        $_SESSION['message'] = 'Нет такой категории';
        header("Location: ?");
        exit;
    }
    if (isset($_POST['name']) && $_POST['name'] != null) { // Создание записи
        $name = trim($_POST['name']);
        
        if (strlen2($name) < 2) {
            $err='Короткое название';
        }
        if (strlen2($name) > 128) {
            $err='Длина названия превышает предел в 128 символов';
        }
        
        if (!isset($err)) {
            $db->query(
                "UPDATE `gift_categories` SET `name`=? WHERE `id`=?i",
                       [$name, $category['id']]);
            
            $_SESSION['message'] = 'Категория успешно переименована';
            header("Location: ?");
            exit;
        }
    }
    
    if (isset($_GET['delete'])) { // Удаление категории
        $q = $db->query(
            "SELECT `id` FROM `gift_list` WHERE `id_category`=?i",
                        [$category['id']]);
        while ($post = $q->row()) {
            unlink(H.'sys/gift/' . $post['id'] . '.png');
            $db->query("DELETE FROM `gifts_user` WHERE `id_gift`=?i", [$post['id']]);
        }
        
        $db->query(        
            "DELETE FROM `gift_list` WHERE `id_category`=?i",
                   [$category['id']]);
        $db->query(
            "DELETE FROM `gift_categories` WHERE `id`=?i",
                   [$category['id']]);
        
        $_SESSION['message'] = 'Категория успешно удалена';
        
        header("Location: ?");
        exit;
    }
    
    
    $set['title'] = 'Редактирование категории';
    include_once H . 'sys/inc/thead.php';
    title();
    aut();
    err();
    // Форма редактирования категории
    echo '<form class="main" method="post" action="?edit_category=' . $category['id'] . '">';
    echo 'Название:<br /><input type="text" name="name" value="' . htmlspecialchars($category['name']) . '" /><br />';
    echo '<input value="Добавить" type="submit" />';
    echo '</form>';
} else {
    /*
    ==================================
    Вывод категорий
    ==================================
    */

    $set['title'] = 'Список категорий';
    include_once H . 'sys/inc/thead.php';
    title();
    aut();
    err();
    
    // Список категорий
    
    $k_post = $db->query("SELECT COUNT( * ) FROM `gift_categories`")->el();
    if ($k_post == 0) {
        echo '<div class="mess">';
        echo 'Нет категорий';
        echo '</div>';
    }
    $q = $db->query("SELECT gft.`id`, gft.`name`, (
                    SELECT COUNT( * ) FROM `gift_list` WHERE `id_category`=gft.id) cnt
                    FROM `gift_categories` gft ORDER BY gft.`id`");
    while ($post = $q->row()) {
        /*-----------зебра-----------*/
        if ($num==0) {
            echo '<div class="nav1">';
            $num=1;
        } elseif ($num==1) {
            echo '<div class="nav2">';
            $num=0;
        }
        /*---------------------------*/
        echo '<img src="/style/themes/default/loads/14/dir.png" alt="*" /> <a href="create.php?category=' . $post['id'] . '">' . htmlspecialchars($post['name']) . '</a> ';
        echo '(' . $post['cnt'] . ')';
        echo ' <a href="create.php?edit_category=' . $post['id'] . '"><img src="/style/icons/edit.gif" alt="*" /></a> ';
        echo ' <a href="create.php?edit_category=' . $post['id'] . '&amp;delete"><img src="/style/icons/delete.gif" alt="*" /></a> ';
        echo '</div>';
    }
    echo '<div class="foot">';
    echo '<img src="/style/icons/ok.gif" alt="*" />  <a href="?add_category">Создать категорию</a><br />';
    echo '</div>';
}

include_once H . 'sys/inc/tfoot.php';
