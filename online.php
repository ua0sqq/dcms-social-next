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

// Cмена режима отображения
if (isset($_GET['admin']) && user_access('user_collisions')) {
    if ($_GET['admin'] == 'close') {
        $_SESSION['admin'] = null;
    } else {
        $_SESSION['admin'] = true;
    }
}
$set['title'] = 'Сейчас на сайте'; // заголовок страницы
include_once 'sys/inc/thead.php';
title();
aut();

$k_post = $db->query(
                    'SELECT (
SELECT COUNT( * ) FROM `user` WHERE `date_last` > ?i) k_post, (
SELECT COUNT( * ) FROM `liders` WHERE `time` > ?i) k_lider',
                            [(time()-600), $time])->row();
/*
==============================================
Этот скрипт выводит 1 случайного "Лидера" и
ссылку на весь их список.(с) DCMS-Social
==============================================
*/
if ($k_post['k_lider']) {
    $liders = $db->query("SELECT * FROM `liders` WHERE `time` > ?i ORDER BY RAND() LIMIT ?i",
                         [$time, 1])->row();
    echo '<div class="main">';
    echo user::avatar($liders['id_user'], 0) . user::nick($liders['id_user'], 1, 1, 1) . '<br />';
    if ($liders['msg']) {
        echo output_text($liders['msg']) . '<br />';
    }
    echo '<img src="/style/icons/lider.gif" alt="S"/> <a href="/user/liders/">Все лидеры</a> (' . $k_post['k_lider'] . ')';
    echo '</div>';
}

if (!$k_post['k_post']) {
    echo '<div class="mess">';
    echo 'Сейчас на сайте никого нет';
    echo '</div>';
} else {

$k_page = k_page($k_post['k_post'], $set['p_str']);
$page = page($k_page);
$start = $set['p_str']*$page-$set['p_str'];
$q = $db->query(
            'SELECT id, ank_city, pol, ank_d_r, ank_m_r, ank_g_r, ank_o_sebe, url, level, ip, ip_xff, ip_cl, ua, date_last FROM `user` WHERE `date_last` > ?i ORDER BY `date_last` DESC LIMIT ?i OFFSET ?i',
                    [(time()-600), $set['p_str'], $start]);
while ($ank = $q->row()) {
    $ank['ank_age'] = null;
        
    if ($ank['ank_d_r'] != null && $ank['ank_m_r'] != null && $ank['ank_g_r'] != null) {
        $ank['ank_age'] = date("Y")-$ank['ank_g_r'];
        if (date("n") < $ank['ank_m_r']) {
            $ank['ank_age'] = $ank['ank_age'] - 1;
        } elseif (date("n") == $ank['ank_m_r']&& date("j") < $ank['ank_d_r']) {
            $ank['ank_age'] = $ank['ank_age'] - 1;
        }
    }
    
    // Лесенка
    echo '<div class="' . ($num % 2 ? "nav1" : "nav2") . '">';
    $num++;
    echo user::avatar($ank['id'], 0) . user::nick($ank['id'], 1, 1, 1) . otkuda($ank['url']) . ' <br />';
// TODO: слишком много запросов!    
    // Расширенный режим
    if (isset($user) && isset($_SESSION['admin'])) {
        // Возможные ники
        $mass[0] = $ank['id'];
        $collisions = user_collision($mass);
        if (count($collisions)>1) {
            echo '<span class="ank_n">Возможные ники</span> ';
            echo '<span class="ank_d">';
            for ($i = 1; $i < count($collisions); $i++) {
                echo ' :: ' . user::nick($collisions[$i]);
            }
            echo '</span><br />';
        }
        
        // IP пользователя
        if ($ank['ip']!=null) {
            if (user_access('user_show_ip') && $ank['ip'] != 0) {
                echo '<span class="ank_n">IP:</span> <span class="ank_d">' . long2ip($ank['ip']) . '</span>';
                if (user_access('adm_ban_ip')) {
                    echo ' [<a href="/adm_panel/ban_ip.php?min=' . $ank['ip'] . '">Бан</a>]';
                }
                echo '<br />';
            }
        }
        
        // IP клиента
        if ($ank['ip_cl'] != null) {
            if (user_access('user_show_ip') && $ank['ip_cl']!=0) {
                echo '<span class="ank_n">IP (CLIENT):</span> <span class="ank_d">' . long2ip($ank['ip_cl']) . '</span>';
                if (user_access('adm_ban_ip')) {
                    echo ' [<a href="/adm_panel/ban_ip.php?min=' . $ank['ip_cl'] . '">Бан</a>]';
                }
                echo '<br />';
            }
        }
        
        // IP (XFF)
        if ($ank['ip_xff'] != null) {
            if (user_access('user_show_ip') && $ank['ip_xff'] != 0) {
                echo '<span class="ank_n">IP (XFF):</span> <span class="ank_d">' . long2ip($ank['ip_xff']) . '</span>';
                if (user_access('adm_ban_ip')) {
                    echo ' [<a href="/adm_panel/ban_ip.php?min=' . $ank['ip_xff'] . '">Бан</a>]';
                }
                echo '<br />';
            }
        }
        
        // Браузер
        if (user_access('user_show_ua') && $ank['ua']!=null) {
            echo '<span class="ank_n">Браузер:</span> <span class="ank_d">' . $ank['ua'] . '</span><br />';
        }
        
        if (user_access('user_show_ip') && $opsos = opsos($ank['ip'])) {
            echo '<span class="ank_n">Пров:</span> <span class="ank_d">' . $opsos . '</span><br />';
        }
        
        if (user_access('user_show_ip') && $opsos = opsos($ank['ip_cl'])) {
            echo '<span class="ank_n">Пров (CL):</span> <span class="ank_d">' . $opsos . '</span><br />';
        }
        
        if (user_access('user_show_ip') && $opsos = opsos($ank['ip_xff'])) {
            echo '<span class="ank_n">Пров (XFF):</span> <span class="ank_d">' . $opsos . '</span><br />';
        }
        
        if ($user['level'] > $ank['level'] && $user['id'] != $ank['id']) {
            if (user_access('user_prof_edit')) {
                echo '[<a href="/adm_panel/user.php?id=' . $ank['id'] . '"><img src="/style/icons/edit.gif" alt="*" /> ред.</a>] ';
            }
            
            if ($user['id'] != $ank['id']) {
                if (user_access('user_ban_set') || user_access('user_ban_set_h') || user_access('user_ban_unset')) {
                    echo '[<a href="/adm_panel/ban.php?id=' . $ank['id'] . '"><img src="/style/icons/blicon.gif" alt="*" /> бан</a>] ';
                }
                if (user_access('user_delete')) {
                    echo '[<a href="/adm_panel/delete_user.php?id=' . $ank['id'] . '"><img src="/style/icons/delete.gif" alt="*" /> удл.</a>] ';
                    echo '<br />';
                }
            }
        }
    } else {
        echo '<b>('.(($ank['pol'] == 1) ? 'М' : 'Ж') . (($ank['ank_age'] == null) ? '/Не указан' : '/' . $ank['ank_age']) . ')</b>';
        
        if ($ank['ank_city'] != null) {
            echo ', ' . text($ank['ank_city']);
        }
        if ($ank['ank_o_sebe'] != null) {
            echo ', ' . text($ank['ank_o_sebe']);
        }
        echo ', <img src="/style/icons/time.png" alt="away" /> [' . vremja($ank['date_last']) . ']';
    }
    
    echo '</div>';
}

if ($k_page>1) {
    str("?", $k_page, $page);
}
}
if (user_access('user_collisions')) {
    ?>
	<div class="foot">
	<?=(!isset($_SESSION['admin']) ? '<a href="?admin">Расширенный режим</a> | <b>Обычный режим</b>' : '<b>Расширенный режим</b> | <a href="?admin=close">Обычный режим</a>')?>
	</div>
	<?php
}

include_once 'sys/inc/tfoot.php';

?>