<?php
require '../sys/inc/start.php';
require H . 'sys/inc/compress.php';
require H . 'sys/inc/sess.php';
require H . 'sys/inc/settings.php';
require H . 'sys/inc/db_connect.php';
require H . 'sys/inc/ipua.php';
require H . 'sys/inc/fnc.php';
require H . 'sys/inc/user.php';

$args = [
        'act' => FILTER_DEFAULT,
        'ok' => FILTER_DEFAULT,
        'acth' => FILTER_DEFAULT,
        'id_forum' =>  [
                     'filter' => FILTER_VALIDATE_INT,
                     'options' => [
                                   'default' => 0,
                                   'min_range' => 1
                                   ]
                     ],
        'id_razdel' =>  [
                     'filter' => FILTER_VALIDATE_INT,
                     'options' => [
                                   'default' => 0,
                                   'min_range' => 1
                                   ]
                     ],
        'id_them' => [
                     'filter' => FILTER_VALIDATE_INT,
                     'options' => [
                                   'default' => 0,
                                   'min_range' => 1
                                   ]
                     ],
        'id_post' => [
                     'filter' => FILTER_VALIDATE_INT,
                     'options' => [
                                   'default' => 0,
                                   'min_range' => 1
                                   ]
                     ],
        'ok' =>  FILTER_DEFAULT,
        'f_del' =>  [
                     'filter' => FILTER_VALIDATE_INT,
                     'options' => [
                                   'default' => 0,
                                   'min_range' => 1
                                   ]
                     ],
        'rating' => FILTER_DEFAULT,
        'id_file' => FILTER_VALIDATE_INT,
        'zakl' => FILTER_VALIDATE_INT,
        'spam' => FILTER_VALIDATE_INT,
        'vote_user' => FILTER_VALIDATE_INT,
        'page' => FILTER_VALIDATE_INT,
        'del' => FILTER_VALIDATE_INT,
    ];

    $input_get = filter_input_array(INPUT_GET, $args);
    unset($args);
    
require H . 'sys/inc/thead.php';

if ($input_get['id_forum'] && $input_get['id_razdel'] && $input_get['id_them'] && $input_get['id_post']) {
    $data = [
             $input_get['id_forum'],
             $input_get['id_razdel'],
             $input_get['id_forum'],
             $input_get['id_them'],
             $input_get['id_razdel'],
             $input_get['id_forum'],
             $input_get['id_post'],
             $input_get['id_them'],
             $input_get['id_razdel'],
             $input_get['id_forum']
             ];
    $cnt = $db->query(
        'SELECT * FROM (
SELECT COUNT(*) cnt_frm FROM `forum_f` WHERE'.((!isset($user) || $user['level']==0)?' `adm`="0" AND':null).' `id`=?i)q, (
SELECT COUNT(*) cnt_rzd FROM `forum_r` WHERE `id`=?i AND `id_forum`=?i)q2, (
SELECT COUNT(*) cnt_thm FROM `forum_t` WHERE `id`=?i AND `id_razdel`=?i AND `id_forum`=?i)q3, (
SELECT COUNT(*) cnt_pst FROM `forum_p` WHERE `id`=?i AND `id_them`=?i AND `id_razdel`=?i AND `id_forum`=?i)q4',
$data)->row();
    
    if ($cnt['cnt_frm'] && $cnt['cnt_rzd'] && $cnt['cnt_thm'] && $cnt['cnt_pst']) {
        $forum = $db->query(    
            'SELECT `frm`.id, `frm`.name, `rzd`.id AS r_id, `rzd`.name AS r_name, `thm`.id AS t_id, `thm`.name AS t_name, `thm`.close  
FROM `forum_f` `frm`
LEFT JOIN `forum_r` `rzd` ON `frm`.id=`rzd`.id_forum
LEFT JOIN `forum_t` `thm` ON `rzd`.id=`thm`.id_razdel 
WHERE `frm`.`id`=?i AND `rzd`.id=?i AND `thm`.id=?i LIMIT ?i',
            [$input_get['id_forum'], $input_get['id_razdel'] , $input_get['id_them'],  1])->row();
    
        $razdel = ['id' => $forum['r_id'], 'name' => $forum['r_name']];
        $them = ['id' => $forum['t_id'], 'name' => $forum['t_name'], 'close' => $forum['close']];
    
        $post = $db->query(    
            'SELECT `pst`. * , u.id a_id, u.nick, u.group_access, `pst2`.id AS id2, `pst2`.id_user AS id_user2
FROM `forum_p` `pst`
LEFT JOIN `user` u ON `pst`.id_user = u.id
LEFT JOIN (
        SELECT id, id_user, id_them FROM `forum_p`) `pst2` ON `pst`.id_them = `pst2`.id_them
WHERE `pst`.`id`=?i AND `pst`.`id_them`=?i AND `pst`.`id_razdel`=?i AND `pst`.`id_forum`=?i ORDER BY `pst2`.id DESC LIMIT ?i',
            [$input_get['id_post'], $input_get['id_them'], $input_get['id_razdel'], $input_get['id_forum'],  1])->row();

        $post2 = ['id' => $post['id2'], 'id_user' => $post['id_user2']];
        $ank = ['id' => $post['a_id'], 'nick' => $post['nick'], 'group_access' => $post['group_access']];
    
        if (isset($user)) {
            if (isset($input_get['act']) && $input_get['act']=='edit' && isset($_POST['msg']) && isset($_POST['post']) && (user_access('forum_post_ed')
        || (isset($user) && $user['id']==$post['id_user'] && $post['time']>TIME_600 && $post['id_user']==$post2['id_user']))) {
                $msg=$_POST['msg'];
                if (isset($_POST['translit']) && $_POST['translit']==1) {
                    $msg=translit($msg);
                }
                if (strlen2($msg)<2) {
                    $err[]='Короткое сообщение';
                }
                if (strlen2($msg)>1024) {
                    $err[]='Длина сообщения превышает предел в 1024 символа';
                }
                $mat=antimat($msg);
                if ($mat) {
                    $err[]='В тексте сообщения обнаружен мат: '.$mat;
                }
                if (!isset($err)) {
                    $db->query(
                    'UPDATE `forum_p` SET `msg`=? WHERE `id`=?i',
                           [$msg, $post['id']]
                );
                }
            } elseif (isset($input_get['act']) && $input_get['act']=='edit' && (user_access('forum_post_ed')
                && ($ank['group_access']<$user['group_access'] || $ank['group_access']==$user['group_access'] && $ank['id']==$user['id'])
                || isset($user) && $post['id']==$post2['id'] && $post['id_user']==$user['id'] && $post['time']>TIME_600)) {
                // заголовок страницы
                $set['title']='Форум - редактирование поста';
                //require H . 'sys/inc/thead.php';
                title();
                echo "<div class='nav2'><form method='post' name='message' action='/forum/$forum[id]/$razdel[id]/$them[id]/$post[id]/edit'>\n";
                $msg2=output_text($post['msg'], false, true, false, false, false);
                if ($set['web'] && is_file(H.'style/themes/'.$set['set_them'].'/altername_post_form.php')) {
                    require H.'style/themes/'.$set['set_them'].'/altername_post_form.php';
                } else {
                    echo "Сообщение:<br />\n<textarea name=\"msg\">".$msg2."</textarea><br />\n";
                }
                echo "<input name='post' value='Изменить' type='submit' /><br />\n";
                echo "</form></div>\n";
                echo "<div class=\"foot\">\n";
                echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/forum/$forum[id]/$razdel[id]/$them[id]/?page=end\" title='Вернуться в тему'>В тему</a><br />\n";
                echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/forum/$forum[id]/$razdel[id]/\" title='В раздел'>" . text($razdel['name']) . "</a><br />\n";
                echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/forum/$forum[id]/\" title='В подфорум'>" . text($forum['name']) . "</a><br />\n";
                echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/forum/\">Форум</a><br />\n";
                echo "</div>\n";
                require H . 'sys/inc/tfoot.php';
            } elseif (isset($input_get['act']) && $input_get['act']=='delete' && isset($user) && $them['close']==0 && ((user_access('forum_post_ed')
            && ($ank['group_access']<=$user['group_access'] || $ank['group_access']==$user['group_access'] && $ank['id']==$user['id']))
            || $post['id']==$post2['id'] && $post['id_user']==$user['id'] && $post['time']>TIME_600)) {
                $db->query(
                    'DELETE FROM `forum_p` WHERE `id`=?i AND `id_them`=?i AND `id_razdel`=?i AND `id_forum`=?i LIMIT ?i',
                            [$input_get['id_post'], $input_get['id_them'], $input_get['id_razdel'], $input_get['id_forum'],  1]);
            } elseif (isset($input_get['act']) && $input_get['act']=='msg' && $them['close']==0 && isset($user)) {
                // заголовок страницы
                $set['title']='Форум - '.text($them['name']);
                require H . 'sys/inc/thead.php';
                title();
                aut();
                echo "<div class='nav2'><form method='post' name='message' action='/forum/$forum[id]/$razdel[id]/$them[id]/new'>\n";
                echo "<a href='/info.php?id=$ank[id]'>Посмотреть анкету</a><br />\n";
                $msg2=$ank['nick'].', ';
                if ($set['web'] && is_file(H.'style/themes/'.$set['set_them'].'/altername_post_form.php')) {
                    require H.'style/themes/'.$set['set_them'].'/altername_post_form.php';
                } else {
                    echo "Сообщение:<br />\n<textarea name=\"msg\">$ank[nick], </textarea><br />\n";
                }
                echo "<input name='post' value='Отправить сообщение' type='submit' /><br />\n";
                echo "</form></div>\n";
                echo "<div class=\"foot\">\n";
                echo "<img src='/style/icons/str.gif' alt='*'> <a href=\"/smiles.php\">Смайлы</a><br />\n";
                echo "<img src='/style/icons/str.gif' alt='*'> <a href=\"/rules.php\">Правила</a><br />\n";
                echo "</div>\n";
                echo "<div class=\"foot\">\n";
                echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/forum/$forum[id]/$razdel[id]/$them[id]/?page=end\" title='Вернуться в тему'>В тему</a><br />\n";
                echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/forum/$forum[id]/$razdel[id]/\" title='В раздел'>" . text($razdel['name']) . "</a><br />\n";
                echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/forum/$forum[id]/\" title='В подфорум'>" . text($forum['name']) . "</a><br />\n";
                echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/forum/\">Форум</a><br />\n";
                echo "</div>\n";
                require H . 'sys/inc/tfoot.php';
            } elseif (isset($input_get['act']) && $input_get['act']=='cit' && $them['close']==0 && isset($user)) {

                // заголовок страницы
                $set['title']='Форум - '.text($them['name']);
                title();
                aut();
                echo "<div class='nav2'>Будет процитировано сообщение:<br/>\n";
                echo "<div class='cit'>\n";
                echo output_text($post['msg'])."<br />\n";
                echo "</div>\n";
                echo "<form method='post' name='message' action='/forum/$forum[id]/$razdel[id]/$them[id]/new'>\n";
                echo "<input name='cit' value='$post[id]' type='hidden' />";
                $msg2=$ank['nick'].', ';
                if ($set['web'] && is_file(H.'style/themes/'.$set['set_them'].'/altername_post_form.php')) {
                    require H.'style/themes/'.$set['set_them'].'/altername_post_form.php';
                } else {
                    echo "Сообщение:<br />\n<textarea name=\"msg\">$ank[nick], </textarea><br />\n";
                }
                echo "<input name='post' value='Отправить сообщение' type='submit' /><br />\n";
                echo "</form></div>\n";
                echo "<div class=\"foot\">\n";
                echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/forum/$forum[id]/$razdel[id]/$them[id]/?page=end\" title='Вернуться в тему'>В тему</a><br />\n";
                echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/forum/$forum[id]/$razdel[id]/\" title='В раздел'>" . text($razdel['name']) . "</a><br />\n";
                echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/forum/$forum[id]/\" title='В подфорум'>" . text($forum['name']) . "</a><br />\n";
                echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/forum/\">Форум</a><br />\n";
                echo "</div>\n";
                require H . 'sys/inc/tfoot.php';
            }
        }
    } else {
        http_response_code(404);
        $_SESSION['message'] = 'This item is missing!';
        header('Location: /forum/');
    }
}

if ($input_get['id_forum'] && $input_get['id_razdel'] && $input_get['id_them']) {
    
    $data = [
             $input_get['id_forum'],
             $input_get['id_razdel'],
             $input_get['id_forum'],
             $input_get['id_them'],
             $input_get['id_razdel'],
             $input_get['id_forum']
             ];
    $cnt = $db->query(
        'SELECT * FROM (
SELECT COUNT(*) cnt_frm FROM `forum_f` WHERE'.((!isset($user) || $user['level']==0)?' `adm`="0" AND':null).' `id`=?i)q, (
SELECT COUNT(*) cnt_rzd FROM `forum_r` WHERE `id`=?i AND `id_forum`=?i)q2, (
SELECT COUNT(*) cnt_thm FROM `forum_t` WHERE `id`=?i AND `id_razdel`=?i AND `id_forum`=?i)q3',
$data)->row();
    
    if ($cnt['cnt_frm'] && $cnt['cnt_rzd'] && $cnt['cnt_thm']) {
        $them = $db->query("SELECT `thm`.*, `rzd`.id AS r_id, `rzd`.name AS r_name, `frm`.id AS f_id, `frm`.name AS f_name, u.id AS u_id, u.nick, u.`level`, u.group_access
FROM forum_t `thm`
LEFT JOIN forum_r `rzd` ON `thm`.id_razdel=`rzd`.id
LEFT JOIN forum_f `frm` ON `thm`.id_forum=`frm`.id
LEFT JOIN user u ON `thm`.id_user=u.id
WHERE `thm`.id=?i", [$input_get['id_them']])->row();
        
        $forum = ['id' => $them['f_id'], 'name' => $them['f_name']];
        $razdel = ['id' => $them['r_id'], 'name' => $them['r_name']];
        $ank2 = ['id' => $them['u_id'], 'nick' => $them['nick'], 'level' => $them['level'], 'group_access' => $them['group_access']];

        if (isset($user)) {
            // Помечаем уведомление прочитанным
            $db->query(
            'UPDATE `notification` SET `read`=? WHERE `id_object`=?i AND `type`=? AND `id_user`=?i',
                   ['1', $them['id'], 'them_komm', $user['id']]);
            // очищаем счетчик этого обсуждения
            $db->query(
            'UPDATE `discussions` SET `count`=?i WHERE `id_user`=?i AND `type`=? AND `id_sim`=?i LIMIT ?i',
                   [0, $user['id'], 'them', $them['id'],  1]);
        }
    
        // заголовок страницы
        $set['title']='Форум - '.text($them['name']);
        title();

        include 'inc/set_them_act.php';
        include 'inc/them.php';
        include 'inc/set_them_form.php';
        echo "<div class=\"foot\">\n";
        echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/forum/\">Форум</a> | <a href=\"/forum/$forum[id]/\" title='В подфорум'>" . text($forum['name']) . "</a> | <a href=\"/forum/$forum[id]/$razdel[id]/\" title='В раздел'>" . text($razdel['name']) . "</a><br />\n";
        echo "</div>\n";
        require H . 'sys/inc/tfoot.php';
    }
}

if (isset($input_get['id_forum']) && isset($input_get['id_razdel'])) {
    if (empty($cnt['cnt_frm']) && empty($cnt['cnt_rzd'])) {
        $data = [
             $input_get['id_forum'],
             $input_get['id_razdel'],
             $input_get['id_forum']
             ];
        $cnt = $db->query(
        'SELECT * FROM (
SELECT COUNT(*) cnt_frm FROM `forum_f` WHERE'.((!isset($user) || $user['level']==0)?' `adm`="0" AND':null).' `id`=?i)q, (
SELECT COUNT(*) cnt_rzd FROM `forum_r` WHERE `id`=?i AND `id_forum`=?i)q2',
$data
    )->row();
    }
    if ($cnt['cnt_frm'] && $cnt['cnt_rzd']) {
        $razdel = $db->query(
                "SELECT `rzd`.id, `rzd`.name, `rzd`.opis, `frm`.id AS f_id, `frm`.name AS f_name, `frm`.adm
FROM forum_r `rzd` LEFT JOIN forum_f `frm` ON `rzd`.id_forum=`frm`.id WHERE `rzd`.id=?i",
                         [$input_get['id_razdel']]
            )->row();
        $forum = ['id' => $razdel['f_id'], 'name' => $razdel['f_name'], 'adm' => $razdel['adm']];
        // создание новой темы
        if (isset($user) && isset($input_get['act']) && $input_get['act']=='new'
        && (!isset($_SESSION['time_c_t_forum']) || $_SESSION['time_c_t_forum']<$time-600 || $user['level']>0)) {
            include 'inc/new_t.php';
        } else {
            // заголовок страницы
            $set['title']='Форум - '.text($razdel['name']);
            //require H . 'sys/inc/thead.php';
            title();
            if (user_access('forum_razd_edit')) {
                include 'inc/set_razdel_act.php';
            }
            include 'inc/razdel.php';
            if (user_access('forum_razd_edit')) {
                include 'inc/set_razdel_form.php';
            }
            echo "<div class=\"foot\">\n";
            echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/forum/$forum[id]/\">" . text($forum['name']) . "</a><br />\n";
            echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/forum/\">Форум</a><br />\n";
            echo "</div>\n";
        }
        require H . 'sys/inc/tfoot.php';
    }
}

if ($input_get['id_forum']) {
    if (empty($cnt['cnt_frm'])) {
        $data = [$input_get['id_forum']];
        $cnt = $db->query(
                'SELECT COUNT(*) cnt_frm FROM `forum_f` WHERE'.((!isset($user) || $user['level']==0)?' `adm`="0" AND':null).' `id`=?i',
                              $data)->row();
    }
    
    if ($cnt['cnt_frm']) {
        $forum = $db->query(
            "SELECT * FROM `forum_f` WHERE `id`=?i",
                          [$input_get['id_forum']])->row();
        
        $set['title']='Форум - '.text($forum['name']); // заголовок страницы
        title();
        // действия над подфорумом
        if (isset($user) && $user['group_access'] > 1) {
            include 'inc/set_forum_act.php';
        }
        // содержимое
        include 'inc/forum.php';
        // формы действий над подфорумом
        if (isset($user) && $user['group_access'] > 1) {
            include 'inc/set_forum_form.php';
        }
        
        echo "<div class=\"foot\">\n";
        echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/forum/\">Форум</a><br />\n";
        echo "</div>\n";
        
        require H . 'sys/inc/tfoot.php';
    }
}

// заголовок страницы
$set['title']='Форум';
title();

if (user_access('forum_for_create') && isset($input_get['act']) && isset($input_get['ok']) && $input_get['act']=='new' && isset($_POST['name']) && isset($_POST['opis']) && isset($_POST['pos'])) {
    $name=trim($_POST['name']);
    if (strlen2($name)<3) {
        $err='Слишком короткое название';
    }
    if (strlen2($name)>32) {
        $err='Слишком днинное название';
    }
    $opis=$_POST['opis'];
    if (strlen2($opis)>512) {
        $err='Слишком длинное описание';
    }
    $opis=trim($opis);
    if (!isset($_POST['icon']) || $_POST['icon']==null) {
        $icons='default';
    } else {
        $icons=preg_replace('#[^a-z0-9 _\-\.]#i', null, $_POST['icon']);
    }
    $pos=intval($_POST['pos']);
    if (!isset($err)) {
        admin_log('Форум', 'Подфорумы', 'Создание подфорума "' . $name . '"');
        $db->query(
            "INSERT INTO `forum_f` (`opis`, `name`, `pos`, `icon`) VALUES(?, ?, ?i, ?)",
                   [$opis, $name, $pos, $icons]
        );
        msg('Подфорум успешно создан');
    }
}

// форма авторизации
err();
aut();

echo "<div class=\"err\">\n";
echo "<a href='/rules.php'>Правила</a><br />\n";
echo "</div>\n";
echo "<div class=\"main\">\n";
echo "<img src='/style/icons/New.gif'> Новые: <a href='/forum/new_t.php'>&bull; темы</a> | \n";
echo "<a href='/forum/new_p.php'>&bull; коммы</a><br />\n";
if (isset($user)) {
    echo "<img src='/style/icons/top.gif'> Мои: <a href='/user/info/them_p.php?id=".$user['id']."'>&bull; темы</a> | \n";
    echo "<a href='/user/bookmark/forum.php?id=".$user['id']."'> &bull; закладки</a> | <a href='/user/info/them_p.php?id=".$user['id']."&komm'> &bull; посты</a><br/>";
}
echo "<img src='/style/icons/searcher.png'> <a href='/forum/search.php'>Поиск по форуму<br /></a>\n";
echo "</div>\n";

echo "<table class='post'>\n";

$q=$db->query('SELECT frm.*, (
SELECT COUNT(*) FROM `forum_p` WHERE `id_forum`=frm.id) cnt_pst, (
SELECT COUNT(*) FROM `forum_t` WHERE `id_forum`=frm.id) cnt_thm
FROM `forum_f` frm ' . ((!isset($user) || $user['level']==0) ? ' WHERE frm.`adm`="0"' : null) . ' ORDER BY frm.`pos` ASC')->assoc();

if (!count($q)) {
    echo "  <div class='mess'>\n";
    echo "Нет подфорумов\n";
    echo "  </div>\n";
} else {
    foreach ($q as $forum) {
        if ($num==0) {
            echo "  <div class='nav1'>\n";
            $num=1;
        } elseif ($num==1) {
            echo "  <div class='nav2'>\n";
            $num=0;
        }
        echo "<img src='/style/forum/$forum[icon]' alt='*'/> ";
        echo "<a href='/forum/$forum[id]/'><b>" . text($forum['name']) . "</b></a> <span style='color:#666;'>(" .
        $forum['cnt_pst'] . '/' . $forum['cnt_thm'] . ")\n";
        if ($forum['opis']!=null) {
            echo '<br />'.output_text($forum['opis']);
        }
        echo "  </span> </div>\n";
    }
}

echo "</table>\n";

echo "<div class='foot'>";
echo "<img src='/style/icons/soob114.gif'> <a href='/forum/on-forum.php'>Кто в форуме?</a> | <a href='/user/admin.user.php?forum'>Модерация</a>";
echo "</div>";

if (user_access('forum_for_create') && (isset($input_get['act']) && $input_get['act']=='new')) {
    echo "<form method=\"post\" action=\"/forum/index.php?act=new&amp;ok\">\n";
    echo "Название подфорума:<br />\n";
    echo "<input name=\"name\" type=\"text\" maxlength='32' value='' /><br />\n";
    echo "Описание:<br />\n";
    echo "<textarea name=\"opis\"></textarea><br />\n";
    echo "Позиция:<br />\n";
    $pos=$db->query("SELECT MAX(`pos`) FROM `forum_f`")->el()+1;
    echo "<input name=\"pos\" type=\"text\" maxlength='3' value='$pos' /><br />\n";
    $icon=array();
    $opendiricon=opendir(H.'style/forum');
    while ($icons=readdir($opendiricon)) {
        if (preg_match('#^\.|default.png#', $icons)) {
            continue;
        }
        $icon[]=$icons;
    }
    closedir($opendiricon);
    echo "Иконка:<br />\n";
    echo "<select name='icon'>\n";
    echo "<option value='default.png'>По умолчанию</option>\n";
    for ($i=0;$i<sizeof($icon);$i++) {
        echo "<option value='$icon[$i]'>$icon[$i]</option>\n";
    }
    echo "</select><br />\n";
    echo "<input value=\"Создать\" type=\"submit\" /><br />\n";
    echo "<img src='/style/icons/str2.gif' alt='*'> <a href=\"/forum/\">Отмена</a><br />\n";
    echo "</form>\n";
}

if (user_access('forum_for_create')) {
    echo "<div class=\"foot\">\n";
    echo "<img src='/style/icons/str.gif' alt='*'> <a href=\"/forum/?act=new\">Новый подфорум</a><br />\n";
    echo "</div>\n";
}

require H . 'sys/inc/tfoot.php';
