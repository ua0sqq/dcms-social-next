<?php
if (isset($_GET['id'])) {
include_once '../../sys/inc/start.php';
    //include_once '../../sys/inc/compress.php';
include_once '../../sys/inc/sess.php';
include_once '../../sys/inc/home.php';
include_once '../../sys/inc/settings.php';
include_once '../../sys/inc/db_connect.php';
include_once '../../sys/inc/ipua.php';
include_once '../../sys/inc/fnc.php';
include_once '../../sys/inc/user.php';
    $ank = go\DB\query('SELECT * FROM `user` WHERE `id`=?i', [$_GET['id']])->row();
    echo ' <a onclick="anketaClose.submit()" name="myForm"><div class="form_info">Скрыть подробную информацию</div></a>';
// Анкета пользователя,
// если автор то выводим ссылки на редактирование полей, если нет то нет

if (isset($user) && $ank['id']==$user['id']) {
        $orien = "<a href='/user/info/edit.php?act=ank_web&amp;set=orien'>";
        $loves = "<a href='/user/info/edit.php?act=ank_web&amp;set=loves'>";
        $opar = "<a href='/user/info/edit.php?act=ank_web&amp;set=opar'>";
        $volos = "<a href='/user/info/edit.php?act=ank_web&amp;set=volos'>";
        $ves = "<a href='/user/info/edit.php?act=ank_web&amp;set=ves'>";
        $glaza = "<a href='/user/info/edit.php?act=ank_web&amp;set=glaza'>";
        $rost = "<a href='/user/info/edit.php?act=ank_web&amp;set=rost'>";
        $osebe = "<a href='/user/info/edit.php?act=ank_web&amp;set=osebe'>";
        $telo = "<a href='/user/info/edit.php?act=ank_web&amp;set=telo'>";
        $avto = "<a href='/user/info/edit.php?act=ank_web&amp;set=avto'>";
        $baby = "<a href='/user/info/edit.php?act=ank_web&amp;set=baby'>";
        $proj = "<a href='/user/info/edit.php?act=ank_web&amp;set=proj'>";
        $zan = "<a href='/user/info/edit.php?act=ank_web&amp;set=zan'>";
        $smok = "<a href='/user/info/edit.php?act=ank_web&amp;set=smok'>";
        $mat_pol = "<a href='/user/info/edit.php?act=ank_web&amp;set=mat_pol'>";
        $mail = "<a href='/user/info/edit.php?act=ank_web&amp;set=mail'>";
        $icq = "<a href='/user/info/edit.php?act=ank_web&amp;set=icq'>";
        $skype = "<a href='/user/info/edit.php?act=ank_web&amp;set=skype'>";
        $mobile = "<a href='/user/info/edit.php?act=ank_web&amp;set=mobile'>";
        $a = "</a>";
    } else {
        $orien = "<font style='padding:1px; color : #005ba8; padding:1px;'>";
        $loves = "<font style='padding:1px; color : #005ba8; padding:1px;'>";
        $opar = "<font style='padding:1px; color : #005ba8; padding:1px;'>";
        $avto = "<font style='padding:1px; color : #005ba8; padding:1px;'>";
        $baby =  "<font style='padding:1px; color : #005ba8; padding:1px;'>";
        $zan = "<font style='padding:1px; color : #005ba8; padding:1px;'>";
        $smok = "<font style='padding:1px; color : #005ba8; padding:1px;'>";
        $mat_pol =  "<font style='padding:1px; color : #005ba8; padding:1px;'>";
        $proj =  "<font style='padding:1px; color : #005ba8; padding:1px;'>";
        $telo =  "<font style='padding:1px; color : #005ba8; padding:1px;'>";
        $volos = "<font style='padding:1px; color : #005ba8; padding:1px;'>";
        $ves =  "<font style='padding:1px; color : #005ba8; padding:1px;'>";
        $glaza =  "<font style='padding:1px; color : #005ba8; padding:1px;'>";
        $rost =  "<font style='padding:1px; color : #005ba8; padding:1px;'>";
        $osebe =   "<font style='padding:1px; color : #005ba8; padding:1px;'>";
        $mail =   "<font style='padding:1px; color : #005ba8; padding:1px;'>";
        $icq =   "<font style='padding:1px; color : #005ba8; padding:1px;'>";
        $skype =   "<font style='padding:1px; color : #005ba8; padding:1px;'>";
        $mobile =   "<font style='padding:1px; color : #005ba8; padding:1px;'>";
        $alko =   "<font style='padding:1px; color : #005ba8; padding:1px;'>";
        $nark =   "<font style='padding:1px; color : #005ba8; padding:1px;'>";
        $a = "</font>";
    }
    echo '<div class="nav2">';
    if ($ank['ank_rost'] != null) {
        echo $rost . '<span class="ank_n">Рост:</span>' . $a . ' <span class="ank_d">' . $ank['ank_rost'] . '</span><br />';
    } else {
        echo $rost . '<span class="ank_n">Рост:</span>' . $a . '<br />';
    }
    if ($ank['ank_ves']!=null) {
        echo $ves . '<span class="ank_n">Вес:</span>' . $a . ' <span class="ank_d">' . $ank['ank_ves'] . '</span><br />';
    } else {
        echo $ves . '<span class="ank_n">Вес:</span>' . $a . '<br />';
    }
    if ($ank['ank_cvet_glas']!=null) {
        echo $glaza . '<span class="ank_n">Цвет глаз:</span>' . $a . ' <span class="ank_d">' . $ank['ank_cvet_glas'] . '</span><br />';
    } else {
        echo $glaza . '<span class="ank_n">Цвет глаз:</span>' . $a . '<br />';
    }
    if ($ank['ank_volos']!=null) {
        echo $volos . '<span class="ank_n">Волосы:</span>' . $a . ' <span class="ank_d">' . $ank['ank_volos'] . '</span><br />';
    } else {
        echo $volos . '<span class="ank_n">Волосы:</span>' . $a . '<br />';
    }
    echo $telo . '<span class="ank_n">Телосложение:</span>' . $a . '';
    if ($ank['ank_telosl']==1) {
        echo ' <span class="ank_d">Нет ответа</span><br />';
    }
    if ($ank['ank_telosl']==2) {
        echo ' <span class="ank_d">Худощавое</span><br />';
    }
    if ($ank['ank_telosl']==3) {
        echo ' <span class="ank_d">Обычное</span><br />';
    }
    if ($ank['ank_telosl']==4) {
        echo ' <span class="ank_d">Спортивное</span><br />';
    }
    if ($ank['ank_telosl']==5) {
        echo ' <span class="ank_d">Мускулистое</span><br />';
    }
    if ($ank['ank_telosl']==6) {
        echo ' <span class="ank_d">Плотное</span><br />';
    }
    if ($ank['ank_telosl']==7) {
        echo ' <span class="ank_d">Полное</span><br />';
    }
    if ($ank['ank_telosl']==0) {
        echo '<br />';
    }
    echo '</div>';
//Для знакомств
echo "<div class='nav1'>";
    echo "$orien<span class=\"ank_n\">Ориентация:</span>$a";
    if ($ank['ank_orien']==0) {
        echo "<br />\n";
    }
    if ($ank['ank_orien']==1) {
        echo " <span class=\"ank_d\">Гетеро</span><br />\n";
    }
    if ($ank['ank_orien']==2) {
        echo " <span class=\"ank_d\">Би</span><br />\n";
    }
    
    if ($ank['ank_orien']==3) {
        echo " <span class=\"ank_d\">Гей/Лесби</span><br />\n";
    }
    echo "$loves<span class=\"ank_n\">Цели знакомства:</span>$a<br />";
    if ($ank['ank_lov_1']==1) {
        echo "&raquo; Дружба и общение<br />";
    }
    if ($ank['ank_lov_2']==1) {
        echo "&raquo; Переписка<br />";
    }
    if ($ank['ank_lov_3']==1) {
        echo "&raquo; Любовь, отношения<br />";
    }
    if ($ank['ank_lov_4']==1) {
        echo "&raquo; Регулярный секс вдвоем<br />";
    }
    if ($ank['ank_lov_5']==1) {
        echo "&raquo; Секс на один-два раза<br />";
    }
    if ($ank['ank_lov_6']==1) {
        echo "&raquo; Групповой секс<br />";
    }
    if ($ank['ank_lov_7']==1) {
        echo "&raquo; Виртуальный секс<br />";
    }
    if ($ank['ank_lov_8']==1) {
        echo "&raquo; Предлагаю интим за деньги<br />";
    }
    if ($ank['ank_lov_9']==1) {
        echo "&raquo; Ищу интим за деньги<br />";
    }
    if ($ank['ank_lov_10']==1) {
        echo "&raquo; Брак, создание семьи<br />";
    }
    if ($ank['ank_lov_11']==1) {
        echo "&raquo; Рождение, воспитание ребенка<br />";
    }
    if ($ank['ank_lov_12']==1) {
        echo "&raquo; Брак для вида<br />";
    }
    if ($ank['ank_lov_13']==1) {
        echo "&raquo; Совместная аренда жилья<br />";
    }
    if ($ank['ank_lov_14']==1) {
        echo "&raquo; Занятия спортом<br />";
    }
    if ($ank['ank_o_par']!=null) {
        echo "$opar<span class=\"ank_n\">О партнере:</span>$a <span class=\"ank_d\">".output_text($ank['ank_o_par'])."</span><br />\n";
    } else {
        echo "$opar<span class=\"ank_n\">О партнере:</span>$a<br />\n";
    }
    if ($ank['ank_o_sebe']!=null) {
        echo "$osebe<span class=\"ank_n\">О себе:</span>$a <span class=\"ank_d\">".output_text($ank['ank_o_sebe'])."</span><br />\n";
    } else {
        echo "$osebe<span class=\"ank_n\">О себе:</span>$a<br />\n";
    }
    echo "</div>\n";
    // О себе
    echo "<div class='nav2'>";
    if ($ank['ank_zan']!=null) {
        echo "$zan<span class=\"ank_n\">Чем занимаюсь:</span>$a <span class=\"ank_d\">".output_text($ank['ank_zan'])."</span><br />\n";
    } else {
        echo "$zan<span class=\"ank_n\">Чем занимаюсь:</span>$a<br />\n";
    }
    echo "$smok<span class=\"ank_n\">Курение:</span>$a";
    if ($ank['ank_smok']==1) {
        echo " <span class=\"ank_d\">Не курю</span><br />\n";
    }
    if ($ank['ank_smok']==2) {
        echo " <span class=\"ank_d\">Курю</span><br />\n";
    }
    if ($ank['ank_smok']==3) {
        echo " <span class=\"ank_d\">Редко</span><br />\n";
    }
    if ($ank['ank_smok']==4) {
        echo " <span class=\"ank_d\">Бросаю</span><br />\n";
    }
    if ($ank['ank_smok']==5) {
        echo " <span class=\"ank_d\">Успешно бросил</span><br />\n";
    }
    if ($ank['ank_smok']==0) {
        echo "<br />\n";
    }
    echo "$mat_pol<span class=\"ank_n\">Материальное положение:</span>$a";
    if ($ank['ank_mat_pol']==1) {
        echo " <span class=\"ank_d\">Непостоянные заработки</span><br />\n";
    }
    if ($ank['ank_mat_pol']==2) {
        echo " <span class=\"ank_d\">Постоянный небольшой доход</span><br />\n";
    }
    if ($ank['ank_mat_pol']==3) {
        echo " <span class=\"ank_d\">Стабильный средний доход</span><br />\n";
    }
    if ($ank['ank_mat_pol']==4) {
        echo " <span class=\"ank_d\">Хорошо зарабатываю / обеспечен</span><br />\n";
    }
    if ($ank['ank_mat_pol']==5) {
        echo " <span class=\"ank_d\">Не зарабатываю</span><br />\n";
    }
    if ($ank['ank_mat_pol']==0) {
        echo "<br />\n";
    }
    echo "$avto<span class=\"ank_n\">Наличие автомобиля:</span>$a";
    if ($ank['ank_avto_n']==1) {
        echo " <span class=\"ank_d\">Есть</span><br />\n";
    }
    if ($ank['ank_avto_n']==2) {
        echo " <span class=\"ank_d\">Нет</span><br />\n";
    }
    if ($ank['ank_avto_n']==3) {
        echo " <span class=\"ank_d\">Хочу купить</span><br />\n";
    }
    if ($ank['ank_avto_n']==0) {
        echo "<br />\n";
    }
    if ($ank['ank_avto'] && $ank['ank_avto_n']!=2 && $ank['ank_avto_n']!=0) {
        echo "&raquo; <span class=\"ank_d\">".output_text($ank['ank_avto'])."</span><br />";
    }
    echo "$proj<span class=\"ank_n\">Проживание:</span>$a";
    if ($ank['ank_proj']==1) {
        echo " <span class=\"ank_d\">Отдельная квартира (снимаю или своя)</span><br />\n";
    }
    if ($ank['ank_proj']==2) {
        echo " <span class=\"ank_d\">Комната в общежитии, коммуналка</span><br />\n";
    }
    if ($ank['ank_proj']==3) {
        echo " <span class=\"ank_d\">Живу с родителями</span><br />\n";
    }
    if ($ank['ank_proj']==4) {
        echo " <span class=\"ank_d\">Живу с приятелем / с подругой</span><br />\n";
    }
    if ($ank['ank_proj']==5) {
        echo " <span class=\"ank_d\">Живу с партнером или супругом (-ой)</span><br />\n";
    }
    if ($ank['ank_proj']==6) {
        echo " <span class=\"ank_d\">Нет постоянного жилья</span><br />\n";
    }
    if ($ank['ank_proj']==0) {
        echo "<br />\n";
    }
    echo "$baby<span class=\"ank_n\">Есть ли дети:</span>$a";
    if ($ank['ank_baby']==1) {
        echo " <span class=\"ank_d\">Нет</span><br />\n";
    }
    if ($ank['ank_baby']==2) {
        echo " <span class=\"ank_d\">Нет, но хотелось бы</span><br />\n";
    }
    if ($ank['ank_baby']==3) {
        echo " <span class=\"ank_d\">Есть, живем вместе</span><br />\n";
    }
    if ($ank['ank_baby']==4) {
        echo " <span class=\"ank_d\">Есть, живем порознь</span><br />\n";
    }
    if ($ank['ank_proj']==0) {
        echo "<br />\n";
    }
    echo "</div>\n";
    if (isset($user) && $ank['id']==$user['id']) {
        $alko = "<a href='/user/info/edit.php?act=ank_web&amp;set=alko'>";
        $nark = "<a href='/user/info/edit.php?act=ank_web&amp;set=nark'>";
    }
    // Дополнительно
    echo "<div class='nav1'>";
    echo "$alko<span class=\"ank_n\">Алкоголь:</span>$a";
    if ($ank['ank_alko_n']==1) {
        echo " <span class=\"ank_d\">Да, выпиваю</span><br />\n";
    }
    if ($ank['ank_alko_n']==2) {
        echo " <span class=\"ank_d\">Редко, по праздникам</span><br />\n";
    }
    if ($ank['ank_alko_n']==3) {
        echo " <span class=\"ank_d\">Нет, категорически не приемлю</span><br />\n";
    }
    if ($ank['ank_alko_n']==0) {
        echo "<br />\n";
    }
    if ($ank['ank_alko'] && $ank['ank_alko_n']!=3 && $ank['ank_alko_n']!=0) {
        echo "&raquo; <span class=\"ank_d\">".output_text($ank['ank_alko'])."</span><br />";
    }
    echo "$nark<span class=\"ank_n\">Наркотики:</span>$a";
    if ($ank['ank_nark']==1) {
        echo " <span class=\"ank_d\">Да, курю травку</span><br />\n";
    }
    if ($ank['ank_nark']==2) {
        echo " <span class=\"ank_d\">Да, люблю любой вид наркотических средств</span><br />\n";
    }
    if ($ank['ank_nark']==3) {
        echo " <span class=\"ank_d\">Бросаю, прохожу реабилитацию</span><br />\n";
    }
    if ($ank['ank_nark']==4) {
        echo " <span class=\"ank_d\">Нет, категорически не приемлю</span><br />\n";
    }
    if ($ank['ank_nark']==0) {
        echo "<br />\n";
    }
    echo "</div>\n";
    
    // Контакты
    echo "<div class='nav2'>";
    if ($ank['ank_icq']!=null && $ank['ank_icq']!=0) {
        echo "$icq<span class=\"ank_n\">ICQ:</span>$a <span class=\"ank_d\">$ank[ank_icq]</span><br />\n";
    } else {
        echo "$icq<span class=\"ank_n\">ICQ:</span>$a<br />\n";
    }
    echo "$mail E-Mail:$a";
    if ($ank['ank_mail']!=null && ($ank['set_show_mail']==1 || isset($user) && ($user['level']>$ank['level'] || $user['level']==4))) {
        if ($ank['set_show_mail']==0) {
            $hide_mail=' (скрыт)';
        } else {
            $hide_mail=null;
        }
        if (preg_match("#(@mail\.ru$)|(@bk\.ru$)|(@inbox\.ru$)|(@list\.ru$)#", $ank['ank_mail'])) {
            echo " <a href=\"mailto:$ank[ank_mail]\" title=\"Написать письмо\" class=\"ank_d\">$ank[ank_mail]</a>$hide_mail<br />\n";
        } else {
            echo " <a href=\"mailto:$ank[ank_mail]\" title=\"Написать письмо\" class=\"ank_d\">$ank[ank_mail]</a>$hide_mail<br />\n";
        }
    } else {
        echo "<br />";
    }
                
    if ($ank['ank_n_tel']!=null) {
        echo "$mobile<span class=\"ank_n\">Телефон:</span>$a <span class=\"ank_d\">$ank[ank_n_tel]</span><br />\n";
    } else {
        echo "$mobile<span class=\"ank_n\">Телефон:</span>$a<br />\n";
    }
    if ($ank['ank_skype']!=null) {
        echo "$skype<span class=\"ank_n\">Skype:</span>$a <span class=\"ank_d\">$ank[ank_skype]</span><br />\n";
    } else {
        echo "$skype<span class=\"ank_n\">Skype:</span>$a<br />\n";
    }
    echo "</div>\n";
    echo "</div>";
} else {
    echo ' <a onclick="anketa.submit()" name="myForm"><div class="form_info">Показать подробную информацию</div></a>';
    }
