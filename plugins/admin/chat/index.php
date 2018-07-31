<?php
include_once '../../../sys/inc/start.php';
include_once '../../../sys/inc/compress.php';
include_once '../../../sys/inc/sess.php';
include_once '../../../sys/inc/settings.php';
include_once '../../../sys/inc/db_connect.php';
include_once '../../../sys/inc/ipua.php';
include_once '../../../sys/inc/fnc.php';
include_once '../../../sys/inc/user.php';
if (user_access('adm_panel_show')) {
    $set['title']='Админ Чат'; // заголовок страницы
    include_once '../../../sys/inc/thead.php';
    title();
    /*
    ===============================
    Помечаем уведомление прочитанным
    ===============================
    */
    $db->query(
    "UPDATE `notification` SET `read`=?i WHERE `type`=? AND `id_user`=?i",
           [1, 'adm_komm', $user['id']]);
    include 'inc/admin_act.php';
    if (isset($_POST['msg']) && isset($user)) {
        $msg=$_POST['msg'];
        if (isset($_POST['translit']) && $_POST['translit']==1) {
            $msg=translit($msg);
        }
        $mat=antimat($msg);
        if ($mat) {
            $err[]='В тексте сообщения обнаружен мат: '.$mat;
        }
        if (strlen2($msg)>1024) {
            $err[]='Сообщение слишком длинное';
        } elseif (strlen2($msg)<2) {
            $err[]='Короткое сообщение';
        } elseif ($db->query(
    "SELECT COUNT(*) FROM `adm_chat` WHERE `id_user`=?i AND `msg`=?",
                   [$user['id'], $msg])->el()) {
            $err='Ваше сообщение повторяет предыдущее';
        } elseif (!isset($err)) {
            /*
            ==========================
            Уведомления об ответах
            ==========================
            */
            if (isset($user) && $respons==true) {
                $notifiacation=$db->query(
            "SELECT * FROM `notification_set` WHERE `id_user`=?i LIMIT ?i",
                                  [$ank_reply['id'], 1])->row();
            
                if ($notifiacation['komm'] == 1 && $ank_reply['id'] != $user['id']) {
                    $db->query(
                "INSERT INTO `notification` (`avtor`, `id_user`, `type`, `time`) VALUES (?i, ?i, ?, ?i)",
                       [$user['id'], $ank_reply['id'], 'adm_komm', $time]
            );
                }
            }
            $db->query(
    "INSERT INTO `adm_chat` (`id_user`, `time`, `msg`) VALUES(?i, ?i, ?)",
           [$user['id'], $time, $msg]);
            $db->query(
    "UPDATE `user` SET `balls`=`balls`+?i WHERE `id`=?i",
           [1, $user['id']]);
            $_SESSION['message'] = 'Сообщение успешно добавлено';
            header("Location: ?");
            exit;
        }
    }
    err();
    aut(); // форма авторизации
    $k_post=$db->query("SELECT COUNT(*) FROM `adm_chat`")->el();
    $k_page=k_page($k_post, $set['p_str']);
    $page=page($k_page);
    $start=$set['p_str']*$page-$set['p_str'];
    echo "<table class='post'>\n";
    if ($k_post==0) {
        echo "  <div class='mess'>\n";
        echo "Нет сообщений\n";
        echo "  </div>\n";
    }
    $num=0;
    $q=$db->query(
    "SELECT * FROM `adm_chat` ORDER BY id DESC LIMIT ?i OFFSET ?i",
              [$set['p_str'], $start]);
    while ($post = $q->row()) {
        /*-----------зебра-----------*/
        if ($num==0) {
            echo "  <div class='nav1'>\n";
            $num=1;
        } elseif ($num==1) {
            echo "  <div class='nav2'>\n";
            $num=0;
        }
        /*---------------------------*/
        $ank=get_user($post['id_user']);
        if ($set['set_show_icon']==2) {
            avatar($ank['id']);
        }
        echo " ".group($ank['id'])." <a href='/info.php?id=$ank[id]'>$ank[nick]</a> <a href='?response=$ank[id]'>[*]</a>\n";
        echo " ".medal($ank['id'])." ".online($ank['id'])." (".vremja($post['time']).")<br />";
        echo output_text($post['msg'])."<br />\n";
        if (user_access('guest_delete')) {
            echo '<div style="float:right;"><a href="./delete.php?id='.$post['id'].'"><img src="/style/icons/delete.gif" alt="*"></a></div><br />';
        }
        echo "   </div>\n";
    }
    echo "</table>\n";
    if ($k_page>1) {
        str('?', $k_page, $page);
    } // Вывод страниц
    if (isset($user) || (isset($set['write_guest']) && $set['write_guest']==1 && (!isset($_SESSION['antiflood']) || $_SESSION['antiflood']<$time-300))) {
        echo "<form method=\"post\" name='message' action=\"?$go_link\">\n";
        if ($set['web'] && is_file(H.'style/themes/'.$set['set_them'].'/altername_post_form.php')) {
            include_once H.'style/themes/'.$set['set_them'].'/altername_post_form.php';
        } else {
            echo "$tPanel<textarea name=\"msg\">$insert</textarea><br />\n";
        }
        echo "<input value=\"Отправить\" type=\"submit\" />\n";
        echo "</form>\n";
    }
    echo "<div class='foot'>\n";
    echo "<img src='/style/icons/str.gif' alt='*' /> <a href='/chat/who.php'>Кто здесь?</a><br />\n";
    echo "</div>\n";
    include 'inc/admin_form.php';
    echo "<div class='foot'>\n";
    echo "<img src='/style/icons/str2.gif' alt='*' /> <a href='/plugins/admin/'>Админ раздел</a><br />\n";
    echo "</div>\n";
}
include_once '../../../sys/inc/tfoot.php';
