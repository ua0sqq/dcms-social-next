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

$set['title']='Редактирование анкеты';
include_once '../../sys/inc/thead.php';
title();
aut();
$get_set = filter_input(INPUT_GET, 'set', FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^([a-z]*)$/', 'default' => null]]);

if ($get_set) {
    $get = preg_replace('/&(.+?);/', '', $get_set);
    $get_act = filter_input(INPUT_GET, 'act', FILTER_DEFAULT);
    if ($get_act && $get_act == 'ank') {
        $get2 = 'act=ank&amp;';
    } elseif ($get_act && $get_act == 'ank_web') {
        $get2 = "act=ank_web&amp;";
    } else {
        $get2 = null;
    }
    
    $stream = urldecode(file_get_contents('php://input', true));
    !$stream ?: $stream;
    parse_str($stream, $user_set_data);
    
    if (isset($user_set_data['save'])) {
        // ник
        if ($get_set == 'nick' && $user['set_nick'] == 1 && isset($user_set_data['nick'])) {
            if (!$db->query("SELECT COUNT( * ) FROM `user` WHERE `nick`=?",
                            [$user_set_data['nick']])->el()) {
                $user_set_data['nick'] = trim($user_set_data['nick']);
                if (!preg_match("/^([A-zА-я0-9\-\_\ ])+$/ui", $user_set_data['nick'])) {
                    $err[]='В нике присутствуют запрещенные символы';
                }
                if (preg_match("/[a-z]+/ui", $user_set_data['nick']) && preg_match("/[а-я]+/ui", $user_set_data['nick'])) {
                    $err[]='Разрешается использовать символы только русского или только английского алфавита';
                }
                if (strlen2($user_set_data['nick']) < 3) {
                    $err[]='Короткий ник';
                }
                if (strlen2($user_set_data['nick']) > 32) {
                    $err[]='Длина ника превышает 32 символа';
                }
            } else {
                $err[]='Ник "' . $user_set_data['nick'] . '" уже зарегистрирован';
            }
            if (!isset($err)) {
                $user['nick'] = $user_set_data['nick'];
                $db->query("UPDATE `user` SET `nick`=?, `set_nick`=? WHERE `id`=?i",
                           [$user_set_data['nick'], '0', $user['id']]);
            } else {
                unset($user_set_data);
            }
        }
        // имя
        if ($get_set == 'name') {
            if (isset($user_set_data['ank_name']) && preg_match('/^([A-zА-я \-]*)$/ui', $user_set_data['ank_name'])) {
                $user['ank_name'] = $user_set_data['ank_name'];
                $db->query("UPDATE `user` SET `ank_name`=? WHERE `id`=?i",
                           [$user['ank_name'], $user['id']]);
            } else {
                $err[]='Неверный формат имени';
            }
        }
        // город
        if ($get_set == 'gorod') {
            if (isset($user_set_data['ank_city']) && preg_match('/^([A-zА-я \-]*)$/ui', $user_set_data['ank_city'])) {
                $user['ank_city'] = $user_set_data['ank_city'];
                $db->query("UPDATE `user` SET `ank_city`=? WHERE `id`=?i",
                           [$user['ank_city'], $user['id']]);
            } else {
                $err[]='Неверный формат названия города';
            }
        }
        // Dата рождения
        if ($get_set == 'date') {
            $max_year_of_birth = date('Y') - 14; // TODO: ??? в настройки?
            $min_year_of_birth = $max_year_of_birth - 70;
            $args = [
                     'ank_d_r' => ['filter'  => FILTER_VALIDATE_INT, 'options' => ['default'   => null, 'min_range' => 1, 'max_range' => 31],],
                     'ank_m_r' =>  ['filter'  => FILTER_VALIDATE_INT, 'options' => ['default'   => null, 'min_range' => 1, 'max_range' => 12],],
                     'ank_g_r' =>  ['filter'  => FILTER_VALIDATE_INT, 'options' => ['default'   => null, 'min_range' => $min_year_of_birth, 'max_range' => $max_year_of_birth],],
                     ];
            
            $set_data = filter_var_array($user_set_data, $args);
            
            if ($set_data['ank_d_r']) {
                $user['ank_d_r'] = $set_data['ank_d_r'];
            } else {
                $err[]='Неверный формат дня рождения';
            }
            
            if ($set_data['ank_m_r']) {
                $user['ank_m_r'] = $set_data['ank_m_r'];
            } else {
                $err[]='Неверный формат месяца рождения';
            }
            
            if ($set_data['ank_g_r']) {
                $user['ank_g_r'] = $set_data['ank_g_r'];
            } else {
                $err[]='Неверный формат года рождения';
            }
            // update data user
            if (count($set_data)) {
                $db->query(
                        'UPDATE `user` SET ?set WHERE `id`=?i',
                                [$set_data, $user['id']]);
                unset($set_data);
            }
        }
        // icq
        if ($get_set == 'icq' && isset($user_set_data['ank_icq'])) {
            $set_icq = filter_var($user_set_data['ank_icq'], FILTER_VALIDATE_INT,
                                  ['options' => ['default' => null, 'min_range' => 10000, 'max_range' => 999999999]]);
            if ($set_icq || $set_icq == null) {
                if ($set_icq == null) {
                    $err = 'Введите правильный номер ICQ';
                }
                $user['ank_icq'] = $set_icq;
                $db->query(
                        'UPDATE `user` SET `ank_icq`=?i WHERE `id`=?i',
                                [$user['ank_icq'], $user['id']]);
                unset($set_icq);
            } else {
                $err[]='Неверный формат ICQ';
            }
        }
        // skype
        if ($get_set == 'skype' && isset($user_set_data['ank_skype'])) {
            $set_skype = filter_var($user_set_data['ank_skype'],
                                    FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[a-z][a-z0-9_\-\.]{5,31}$/ui', 'default' => null]]);
            if ($set_skype || $set_skype == null) {
                if ($set_skype == null) {
                    $err = 'Введите правильный логин Skype';
                }
                $user['ank_skype'] = $set_skype;
                $db->query("UPDATE `user` SET `ank_skype`=? WHERE `id`=?i",
                           [$user['ank_skype'], $user['id']]);
            } else {
                $err[] = 'Неверный логин Skype';
            }
        }
        // email
        if ($get_set == 'mail') {
            if (isset($user_set_data['set_show_mail']) && $user_set_data['set_show_mail'] == 1) {
                $user['set_show_mail'] = 1;
            } else {
                $user['set_show_mail'] = 0;
            }
            $ank_mail = empty($user_set_data['ank_mail']) ? null : $user_set_data['ank_mail'];
            $ank_mail = filter_var($user_set_data['ank_mail'], FILTER_VALIDATE_EMAIL,
                                   ['options' => ['default' => null]]);
            if ($ank_mail || $ank_mail == null) {
                if ($ank_mail == null) {
                    $err = 'Введите правильный e-mail';
                }
                $user['ank_mail'] = $ank_mail;
            } else {
                $err[]='Неверный E-mail';
            }
            $db->query("UPDATE `user` SET `set_show_mail`=?string, `ank_mail`=? WHERE `id`=?i",
                       [$user['set_show_mail'], $user['ank_mail'], $user['id']]);
        }
        // телефон
        if ($get_set == 'mobile') {
            $set_tel = empty($user_set_data['ank_n_tel']) ? null : $user_set_data['ank_n_tel'];
            if (is_numeric($set_tel) && mb_strlen($set_tel) > 4 && mb_strlen($set_tel) < 12 || $set_tel == null) {
                if ($set_tel == null) {
                    $err = 'Введите правильный номер телефона';
                }
                $user['ank_n_tel'] = $set_tel;
                $db->query(
                        'UPDATE `user` SET `ank_n_tel`=? WHERE `id`=?i',
                                [$user['ank_n_tel'], $user['id']]);
                unset($set_tel);
            } else {
                $err[]='Неверный формат номера телефона';
            }
        }
        // пол
        if ($get_set == 'pol' && isset($user_set_data['pol'])) {
            $user['pol'] = $user_set_data['pol'] ? 1 : 0;
            $db->query("UPDATE `user` SET `pol`=?string WHERE `id`=?i",
                       [$user['pol'], $user['id']]);
        }
        // глаза
        if ($get_set == 'glaza') {
            if (isset($user_set_data['ank_cvet_glas']) && preg_match('#^([A-zА-я \-]*)$#ui', $user_set_data['ank_cvet_glas'])) {
                $user['ank_cvet_glas'] = $user_set_data['ank_cvet_glas'];
                $db->query("UPDATE `user` SET `ank_cvet_glas`=? WHERE `id`=?i",
                           [$user['ank_cvet_glas'], $user['id']]);
            } else {
                $err[] = 'Неверный формат цвет глаз';
            }
        }
        // волосы
        if ($get_set == 'volos') {
            if (isset($user_set_data['ank_volos']) && preg_match('#^([A-zА-я \-]*)$#ui', $user_set_data['ank_volos'])) {
                $user['ank_volos'] = $user_set_data['ank_volos'];
                $db->query("UPDATE `user` SET `ank_volos`=? WHERE `id`=?i",
                           [$user['ank_volos'], $user['id']]);
            } else {
                $err[] = 'Неверный формат цвет глаз';
            }
        }
        // вес
        if ($get_set == 'ves' && isset($user_set_data['ank_ves'])) {
            $ank_ves = filter_var_array($user_set_data,
                                        [
                                         'ank_ves' => [
                                                       'filter' => FILTER_VALIDATE_INT,
                                                       'options' => [
                                                                     'min_range' => 32,
                                                                     'max_range' => 132,
                                                                     'default' => null
                                                                     ]
                                                       ]
                                         ]);
            if ($ank_ves['ank_ves'] == null) {
                $err[] = 'Некорректный вес!';
            }
            $user['ank_ves'] = $ank_ves['ank_ves'];
            if (!empty($ank_ves)) {
                $db->query('UPDATE `user` SET ?set WHERE `id`=?i',
                           [$ank_ves, $user['id']]);
            }
        }
        // рост
        if ($get_set == 'rost' && isset($user_set_data['ank_rost'])) {
            $ank_rost = filter_var_array($user_set_data,
                                        [
                                         'ank_rost' => [
                                                       'filter' => FILTER_VALIDATE_INT,
                                                       'options' => [
                                                                     'min_range' => 140,
                                                                     'max_range' => 233,
                                                                     'default' => null
                                                                     ]
                                                       ]
                                         ]);
            if ($ank_rost['ank_rost'] == null) {
                $err[] = 'Некорректный рост!';
            }
            $user['ank_rost'] = $ank_rost['ank_rost'];
            if (!empty($ank_rost)) {
                $db->query('UPDATE `user` SET ?set WHERE `id`=?i',
                           [$ank_rost, $user['id']]);
            }
        }
        // Цели знакомства
        if ($get_set == 'loves') {
            $set_ank_lov = [];
            $val = range(1, 14);
            foreach ($val as $key) {
                if (isset($user_set_data['ank_lov_' . $key])) {
                    $set_ank_lov += ['ank_lov_' . $key => 1];
                    $user['ank_lov_' . $key] = 1;
                } else {
                    $set_ank_lov += ['ank_lov_' . $key => 0];
                    $user['ank_lov_' . $key] = 0;                    
                }
            }
            if (!empty($set_ank_lov)) {
                $db->query('UPDATE `user` SET ?set WHERE `id`=?i',
                           [$set_ank_lov, $user['id']]);
            }
        }
        // телосложение
        if ($get_set == 'telo') {
            $val = range(0, 7);
            if (isset($user_set_data['ank_telosl']) && in_array($user_set_data['ank_telosl'] , $val)) {
                $user['ank_telosl'] = $val[$user_set_data['ank_telosl']];
                $db->query("UPDATE `user` SET `ank_telosl`=?i WHERE `id`=?i",
                           [$user['ank_telosl'], $user['id']]);
            }
        }
        // Ориентация
        if ($get_set == 'orien') {
            $val = range(0, 3);
            if (isset($user_set_data['ank_orien']) && in_array($user_set_data['ank_orien'] , $val)) {
                $user['ank_orien'] = $val[$user_set_data['ank_orien']];
                $db->query("UPDATE `user` SET `ank_orien`=?i WHERE `id`=?i",
                           [$user['ank_orien'], $user['id']]);
            }
        }
        // есть ли дети
        if ($get_set == 'baby') {
            $val = range(0, 4);
            if (isset($user_set_data['ank_baby']) && in_array($user_set_data['ank_baby'] , $val)) {
                $user['ank_baby'] = $val[$user_set_data['ank_baby']];
                $db->query("UPDATE `user` SET `ank_baby`=?i WHERE `id`=?i",
                           [$user['ank_baby'], $user['id']]);
            }
        }
        // Курение
        if ($get_set == 'smok') {
            $val = range(0, 5);
            if (isset($user_set_data['ank_smok']) && in_array($user_set_data['ank_smok'] , $val)) {
                $user['ank_smok'] = $val[$user_set_data['ank_smok']];
                $db->query("UPDATE `user` SET `ank_smok`=?i WHERE `id`=?i",
                           [$user['ank_smok'], $user['id']]);
            }
        }
        // материальное положение
        if ($get_set == 'mat_pol') {
            $val = range(0, 5);
            if (isset($user_set_data['ank_mat_pol']) && in_array($user_set_data['ank_mat_pol'] , $val)) {
                $user['ank_mat_pol'] = $val[$user_set_data['ank_mat_pol']];
                $db->query("UPDATE `user` SET `ank_mat_pol`=?i WHERE `id`=?i",
                           [$user['ank_mat_pol'], $user['id']]);
            }
        }
        // проживание
        if ($get_set == 'proj') {
            $val = range(0, 6);
            if (isset($user_set_data['ank_proj']) && in_array($user_set_data['ank_proj'] , $val)) {
                $user['ank_proj'] = $val[$user_set_data['ank_proj']];
                $db->query("UPDATE `user` SET `ank_proj`=?i WHERE `id`=?i",
                           [$user['ank_proj'], $user['id']]);
            }
        }
        // автомобиль
        if ($get_set == 'avto') {
            $val = range(0, 3);
            $set_ank_auto = [];
            if (isset($user_set_data['ank_avto_n']) && in_array($user_set_data['ank_avto_n'] , $val)) {
                $set_ank_auto += ['ank_avto_n' => $val[$user_set_data['ank_avto_n']]];
                $user['ank_avto_n'] = $set_ank_auto['ank_avto_n'];
            }
            if (isset($user_set_data['ank_avto']) && strlen2($user_set_data['ank_avto']) < 216) {
                if (preg_match('/[^a-zа-яё0-9 _\-\=\+\(\)\*\!\?\.,]/ui', $user_set_data['ank_avto'])) {
                    $err[] = 'В поле "Название\Марка авто" используются запрещенные символы';
                } else {
                    $user_set_data['ank_avto'] = trim($user_set_data['ank_avto']);
                    $user_set_data['ank_avto'] = (!empty($user_set_data['ank_avto']) && $user['ank_avto_n'] && $user['ank_avto_n'] <> 2 ? $user_set_data['ank_avto'] : null);
                    $set_ank_auto += ['ank_avto' => (!empty($user_set_data['ank_avto']) && $user['ank_avto_n'] <> 2 ? $user_set_data['ank_avto'] : null)];
                    $user['ank_avto'] = $set_ank_auto['ank_avto'];
                }
            } else {
                $err[] = 'О вашем авто нужно писать меньше :)';
            }
            if (!empty($set_ank_auto)) {
                $db->query('UPDATE `user` SET ?set WHERE `id`=?i',
                           [$set_ank_auto, $user['id']]);
            }
        }
        // напиток
        if ($get_set == 'alko') {
            $val = range(0, 3);
            $set_ank_alko = [];
            if (isset($user_set_data['ank_alko_n']) && in_array($user_set_data['ank_alko_n'] , $val)) {
                $set_ank_alko += ['ank_alko_n' => $val[$user_set_data['ank_alko_n']]];
                $user['ank_alko_n'] = $set_ank_alko['ank_alko_n'];
            }
            if (isset($user_set_data['ank_alko']) && strlen2($user_set_data['ank_alko']) < 216) {
                if (preg_match('/[^A-zА-я0-9 _\-\=\+\(\)\*\!\?\.,]/ui', $user_set_data['ank_alko'])) {
                    $err[] = 'В поле "Напиток" используются запрещенные символы';
                } else {
                    $user_set_data['ank_alko'] = trim($user_set_data['ank_alko']);
                    $user_set_data['ank_alko'] = (!empty($user_set_data['ank_alko']) && $user['ank_alko_n'] && $user['ank_alko_n'] <> 3 ? $user_set_data['ank_alko'] : null);
                    $set_ank_alko += ['ank_alko' => !empty($user_set_data['ank_alko']) ? $user_set_data['ank_alko'] : null];
                    $user['ank_alko'] = $set_ank_alko['ank_alko'];
                }
            } else {
                $err[] = 'О любимом напитке нужно писать меньше :)';
            }
            if (!empty($set_ank_alko)) {
                $db->query('UPDATE `user` SET ?set WHERE `id`=?i',
                           [$set_ank_alko, $user['id']]);
            }
        }
        // о себе
        if ($get_set == 'osebe') {
            if (isset($user_set_data['ank_o_sebe']) && strlen2($user_set_data['ank_o_sebe']) < 513) {
                if (preg_match('#[^A-zА-я0-9 _\-\=\+\(\)\*\!\?\.,]#ui', $user_set_data['ank_o_sebe'])) {
                    $err[] = 'В поле "О себе" используются запрещенные символы';
                } else {
                    $user['ank_o_sebe'] = $user_set_data['ank_o_sebe'];
                    $db->query("UPDATE `user` SET `ank_o_sebe`=? WHERE `id`=?i",
                               [$user['ank_o_sebe'], $user['id']]);
                }
            } else {
                $err[] = 'О себе нужно писать меньше :)';
            }
        }
        // о партнере
        if ($get_set == 'opar') {
            if (isset($user_set_data['ank_o_par']) && strlen2($user_set_data['ank_o_par']) < 216) {
                if (preg_match('#[^A-zА-я0-9 _\-\=\+\(\)\*\!\?\.,]#ui', $user_set_data['ank_o_par'])) {
                    $err[]='В поле "О партнере" используются запрещенные символы';
                } else {
                    $user['ank_o_par'] = $user_set_data['ank_o_par'];
                    $db->query("UPDATE `user` SET `ank_o_par`=? WHERE `id`=?i",
                               [$user['ank_o_par'], $user['id']]);
                }
            } else {
                $err[] = 'О партнере нужно писать меньше :)';
            }
        }
        // наркотики
        if ($get_set == 'nark') {
            $val = range(0, 4);
            $set_ank_nark = [];
            if (isset($user_set_data['ank_nark']) && in_array($user_set_data['ank_nark'] , $val)) {
                $set_ank_nark += ['ank_nark' => $val[$user_set_data['ank_nark']]];
                $user['ank_nark'] = $set_ank_nark['ank_nark'];
            }
            if (!empty($set_ank_nark)) {
                $db->query('UPDATE `user` SET ?set WHERE `id`=?i',
                           [$set_ank_nark, $user['id']]);
            }
        }
        // чем занимаюсь
        if ($get_set == 'zan') {
            if (isset($user_set_data['ank_zan']) && strlen2($user_set_data['ank_zan'])<=215) {
                if (preg_match('#[^A-zА-я0-9 _\-\=\+\(\)\*\!\?\.,]#ui', $user_set_data['ank_zan'])) {
                    $err[] = 'В поле "Чем занимаюсь" используются запрещенные символы';
                } else {
                    $user['ank_zan'] = $user_set_data['ank_zan'];
                    $db->query("UPDATE `user` SET `ank_zan`=? WHERE `id`=?i",
                                [$user['ank_zan'], $user['id']]);
                }
            } else {
                $err[] = 'Слишком большой текст';
            }
        }
        if (!isset($err)) {
            $_SESSION['message'] = 'Изменения успешно приняты';
            $db->query("UPDATE `user` SET `rating_tmp`=`rating_tmp`+?i WHERE `id`=?i",
                       [1, $user['id']]);
        
            if ($get_act && $get_act['act']=='ank') {
                header("Location: /user/info/anketa.php?".SID);
            } elseif ($get_act && $get_act['act']=='ank_web') {
                header("Location: /info.php".SID);
            } else {
                header("Location: /user/info/edit.php?".SID);
            }
            exit;
        }
    }
    
    err();
    
    echo '<form method="post" action="?' . $get2 . 'set=' . $get . '">'."\n";
    if ($get_set == 'nick' && $user['set_nick'] == 1) {
        echo "<div class='mess'>Внимание! Изменить свой ник вы можете только один раз!</div> Nick Name:<br /><input type='text' name='nick' value='".htmlspecialchars($user['nick'], false)."' maxlength='32' /><br />";
    }
    if ($get_set == 'name') {
        echo "Имя в реале:<br /><input type='text' name='ank_name' value='".htmlspecialchars($user['ank_name'], false)."' maxlength='32' /><br />";
    }
    
    if ($get_set == 'glaza') {
        echo "Цвет глаз:<br /><input type='text' name='ank_cvet_glas' value='".htmlspecialchars($user['ank_cvet_glas'], false)."' maxlength='32' /><br />";
    }
    
    if ($get_set == 'volos') {
        echo "Волосы:<br /><input type='text' name='ank_volos' value='".htmlspecialchars($user['ank_volos'], false)."' maxlength='32' /><br />";
    }
    // Form Date
    if ($get_set == 'date') {
		$array_day = range(1, 31);
?>
	<p>Дата рождения:</p>
	<p><select name="ank_d_r">
        <option value=""></option>
	<?php
		foreach($array_day as $date_day) {
			echo '  <option value="' . $date_day . '"' . ($user['ank_d_r'] == $date_day ? ' selected="selected"' : null) . '>' . $date_day . '</option>'."\n";
		}
?>
	</select>
	<select name="ank_m_r">
        <option value=""></option>
    <?php
		$array_month = range(1, 12);
		foreach($array_month as $date_month) {
			echo '  <option value="' . $date_month . '"' . ($user['ank_m_r'] == $date_month ? ' selected="selected"' : null) . '>' . $date_month . '</option>'."\n";
		}		
        
        echo '</select>'."\n";
        $max_year_of_birth = date('Y') - 14; // TODO: ??? в настройки?
        $min_year_of_birth = $max_year_of_birth - 70;
		$array_year = range($min_year_of_birth, $max_year_of_birth);
        echo '<select name="ank_g_r">'."\n".
        '<option value=""></option>'."\n";
		foreach($array_year as $date_year) {
			echo '  <option value="' . $date_year . '"' . ($user['ank_g_r'] == $date_year ? ' selected="selected"' : null) . '>' . $date_year . '</option>'."\n";
		}    
        echo '</select><br/>';
    }
        
    if ($get_set == 'pol') {
        echo "Пол:<br /> <input name='pol' type='radio' ".($user['pol']==1?' checked="checked"':null)." value='1' />Муж.<br />
	<input name='pol' type='radio' ".($user['pol']==0?' checked="checked"':null)." value='0' />Жен.<br />";
    }
        
    if ($get_set == 'telo') {
        echo "Телосложение:<br /> 
	<input name='ank_telosl' type='radio' ".($user['ank_telosl']==1?' checked="checked"':null)." value='1' />Нет ответа<br />
	<input name='ank_telosl' type='radio' ".($user['ank_telosl']==2?' checked="checked"':null)." value='2' />Худощавое<br />
	<input name='ank_telosl' type='radio' ".($user['ank_telosl']==3?' checked="checked"':null)." value='3' />Обычное<br />
	<input name='ank_telosl' type='radio' ".($user['ank_telosl']==4?' checked="checked"':null)." value='4' />Спортивное<br />
	<input name='ank_telosl' type='radio' ".($user['ank_telosl']==5?' checked="checked"':null)." value='5' />Мускулистое<br />
	<input name='ank_telosl' type='radio' ".($user['ank_telosl']==6?' checked="checked"':null)." value='6' />Плотное<br />
	<input name='ank_telosl' type='radio' ".($user['ank_telosl']==7?' checked="checked"':null)." value='7' />Полное<br />
	<input name='ank_telosl' type='radio' ".($user['ank_telosl']==0?' checked="checked"':null)." value='0' />Не указано<br />";
    }
        
    if ($get_set == 'avto') {
        echo "Наличие автомобиля:<br /> 
	<input name='ank_avto_n' type='radio' ".($user['ank_avto_n']==0?' checked="checked"':null)." value='0' />Не указано<br />
	<input name='ank_avto_n' type='radio' ".($user['ank_avto_n']==1?' checked="checked"':null)." value='1' />Есть<br />
	<input name='ank_avto_n' type='radio' ".($user['ank_avto_n']==2?' checked="checked"':null)." value='2' />Нет<br />
	<input name='ank_avto_n' type='radio' ".($user['ank_avto_n']==3?' checked="checked"':null)." value='3' />Хочу купить<br />";
        echo "Название\Марка авто:<br /><input type='text' name='ank_avto' value='".htmlspecialchars($user['ank_avto'], false)."' maxlength='215' /><br />";
    }
    if ($get_set == 'nark') {
        echo "Наркотики:<br /> 
	<input name='ank_nark' type='radio' ".($user['ank_nark']==0?' checked="checked"':null)." value='0' />Не указано<br />
	<input name='ank_nark' type='radio' ".($user['ank_nark']==1?' checked="checked"':null)." value='1' />Да, курю травку<br />
	<input name='ank_nark' type='radio' ".($user['ank_nark']==2?' checked="checked"':null)." value='2' />Да, люблю любой вид наркотических средств<br />
	<input name='ank_nark' type='radio' ".($user['ank_nark']==3?' checked="checked"':null)." value='3' />Бросаю, прохожу реабилитацию<br />
	<input name='ank_nark' type='radio' ".($user['ank_nark']==4?' checked="checked"':null)." value='4' />Нет, категорически не приемлю<br />";
    }
    if ($get_set == 'alko') {
        echo "Алкоголь:<br /> 
	<input name='ank_alko_n' type='radio' ".($user['ank_alko_n']==0?' checked="checked"':null)." value='0' />Не указано<br />
	<input name='ank_alko_n' type='radio' ".($user['ank_alko_n']==1?' checked="checked"':null)." value='1' />Да, выпиваю<br />
	<input name='ank_alko_n' type='radio' ".($user['ank_alko_n']==2?' checked="checked"':null)." value='2' />Редко, по праздникам<br />
	<input name='ank_alko_n' type='radio' ".($user['ank_alko_n']==3?' checked="checked"':null)." value='3' />Нет, категорически не приемлю<br />";
        echo "Напиток:<br /><input type='text' name='ank_alko' value='".htmlspecialchars($user['ank_alko'], false)."' maxlength='215' /><br />";
    }
    if ($get_set == 'orien') {
        echo "Ориентация:<br /> 
	<input name='ank_orien' type='radio' ".($user['ank_orien']==0?' checked="checked"':null)." value='0' />Не указано<br />
	<input name='ank_orien' type='radio' ".($user['ank_orien']==1?' checked="checked"':null)." value='1' />Гетеро<br />
	<input name='ank_orien' type='radio' ".($user['ank_orien']==2?' checked="checked"':null)." value='2' />Би<br />
	<input name='ank_orien' type='radio' ".($user['ank_orien']==3?' checked="checked"':null)." value='3' />Гей/Лесби<br />";
    }
    if ($get_set == 'mat_pol') {
        echo "Материальное положение:<br /> 
	<input name='ank_mat_pol' type='radio' ".($user['ank_mat_pol']==0?' checked="checked"':null)." value='0' />Не указано<br />
	<input name='ank_mat_pol' type='radio' ".($user['ank_mat_pol']==1?' checked="checked"':null)." value='1' />Непостоянные заработки<br />
	<input name='ank_mat_pol' type='radio' ".($user['ank_mat_pol']==2?' checked="checked"':null)." value='2' />Постоянный небольшой доход<br />
	<input name='ank_mat_pol' type='radio' ".($user['ank_mat_pol']==3?' checked="checked"':null)." value='3' />Стабильный средний доход<br />
	<input name='ank_mat_pol' type='radio' ".($user['ank_mat_pol']==4?' checked="checked"':null)." value='4' />Хорошо зарабатываю / обеспечен<br />
	<input name='ank_mat_pol' type='radio' ".($user['ank_mat_pol']==5?' checked="checked"':null)." value='5' />Не зарабатываю<br />";
    }
    if ($get_set == 'smok') {
        echo "Курение:<br /> 
	<input name='ank_smok' type='radio' ".($user['ank_smok']==0?' checked="checked"':null)." value='0' />Не указано<br />
	<input name='ank_smok' type='radio' ".($user['ank_smok']==1?' checked="checked"':null)." value='1' />Не курю<br />
	<input name='ank_smok' type='radio' ".($user['ank_smok']==2?' checked="checked"':null)." value='2' />Курю<br />
	<input name='ank_smok' type='radio' ".($user['ank_smok']==3?' checked="checked"':null)." value='3' />Редко<br />
	<input name='ank_smok' type='radio' ".($user['ank_smok']==4?' checked="checked"':null)." value='4' />Бросаю<br />
	<input name='ank_smok' type='radio' ".($user['ank_smok']==5?' checked="checked"':null)." value='5' />Успешно бросил<br />";
    }
    
    if ($get_set == 'proj') {
        echo "Проживание:<br /> 
	<input name='ank_proj' type='radio' ".($user['ank_proj']==0?' checked="checked"':null)." value='0' />Не указано<br />
	<input name='ank_proj' type='radio' ".($user['ank_proj']==1?' checked="checked"':null)." value='1' />Отдельная квартира (снимаю или своя)<br />
	<input name='ank_proj' type='radio' ".($user['ank_proj']==2?' checked="checked"':null)." value='2' />Комната в общежитии, коммуналка<br />
	<input name='ank_proj' type='radio' ".($user['ank_proj']==3?' checked="checked"':null)." value='3' />Живу с родителями<br />
	<input name='ank_proj' type='radio' ".($user['ank_proj']==4?' checked="checked"':null)." value='4' />Живу с приятелем / с подругой<br />
	<input name='ank_proj' type='radio' ".($user['ank_proj']==5?' checked="checked"':null)." value='5' />Живу с партнером или супругом (-ой)<br />
	<input name='ank_proj' type='radio' ".($user['ank_proj']==6?' checked="checked"':null)." value='6' />Нет постоянного жилья<br />";
    }
    
    
    if ($get_set == 'baby') {
        echo "Есть ли дети:<br /> 
	<input name='ank_baby' type='radio' ".($user['ank_baby']==0?' checked="checked"':null)." value='0' />Не указано<br />
	<input name='ank_baby' type='radio' ".($user['ank_baby']==1?' checked="checked"':null)." value='1' />Нет<br />
	<input name='ank_baby' type='radio' ".($user['ank_baby']==2?' checked="checked"':null)." value='2' />Нет, но хотелось бы<br />
	<input name='ank_baby' type='radio' ".($user['ank_baby']==3?' checked="checked"':null)." value='3' />Есть, живем вместе<br />
	<input name='ank_baby' type='radio' ".($user['ank_baby']==4?' checked="checked"':null)." value='4' />Есть, живем порознь<br />";
    }
    
    if ($get_set == 'zan') {
        echo "Чем занимаюсь:<br /><input type='text' name='ank_zan' value='$user[ank_zan]' maxlength='215' /><br />";
    }
    
    if ($get_set == 'gorod') {
        echo "Город:<br /><input type='text' name='ank_city' value='$user[ank_city]' maxlength='32' /><br />";
    }
    
    if ($get_set == 'rost') {
        echo "Рост:<br /><input type='text' name='ank_rost' value='$user[ank_rost]' maxlength='3' /><br />";
    }
    
    if ($get_set == 'ves') {
        echo "Вес:<br /><input type='text' name='ank_ves' value='$user[ank_ves]' maxlength='3' /><br />";
    }
    
    if ($get_set == 'icq') {
        echo "ICQ:<br /><input type='text' name='ank_icq' value='$user[ank_icq]' maxlength='9' /><br />";
    }
    
    if ($get_set == 'skype') {
        echo "Skype логин<br /><input type='text' name='ank_skype' value='$user[ank_skype]' maxlength='16' /><br />";
    }
    
    
    if ($get_set == 'mail') {
        echo "E-mail:<br />
		<input type='text' name='ank_mail' value='$user[ank_mail]' maxlength='32' /><br />
		<label><input type='checkbox' name='set_show_mail'".($user['set_show_mail']==1?' checked="checked"':null)." value='1' /> Показывать E-mail в анкете</label><br />";
    }
    
    if ($get_set == 'loves') {
        echo "Цели знакомства:<br />
		<label><input type='checkbox' name='ank_lov_1'".($user['ank_lov_1']==1?' checked="checked"':null)." value='1' /> Дружба и общение</label><br />
		<label><input type='checkbox' name='ank_lov_2'".($user['ank_lov_2']==1?' checked="checked"':null)." value='1' /> Переписка</label><br />
		<label><input type='checkbox' name='ank_lov_3'".($user['ank_lov_3']==1?' checked="checked"':null)." value='1' /> Любовь, отношения</label><br />
		<label><input type='checkbox' name='ank_lov_4'".($user['ank_lov_4']==1?' checked="checked"':null)." value='1' /> Регулярный секс вдвоем</label><br />
		<label><input type='checkbox' name='ank_lov_5'".($user['ank_lov_5']==1?' checked="checked"':null)." value='1' /> Секс на один-два раза</label><br />
		<label><input type='checkbox' name='ank_lov_6'".($user['ank_lov_6']==1?' checked="checked"':null)." value='1' /> Групповой секс</label><br />
		<label><input type='checkbox' name='ank_lov_7'".($user['ank_lov_7']==1?' checked="checked"':null)." value='1' /> Виртуальный секс</label><br />
		<label><input type='checkbox' name='ank_lov_8'".($user['ank_lov_8']==1?' checked="checked"':null)." value='1' /> Предлагаю интим за деньги</label><br />
		<label><input type='checkbox' name='ank_lov_9'".($user['ank_lov_9']==1?' checked="checked"':null)." value='1' /> Ищу интим за деньги</label><br />
		<label><input type='checkbox' name='ank_lov_10'".($user['ank_lov_10']==1?' checked="checked"':null)." value='1' /> Брак, создание семьи</label><br />
		<label><input type='checkbox' name='ank_lov_11'".($user['ank_lov_11']==1?' checked="checked"':null)." value='1' /> Рождение, воспитание ребенка</label><br />
		<label><input type='checkbox' name='ank_lov_12'".($user['ank_lov_12']==1?' checked="checked"':null)." value='1' /> Брак для вида</label><br />
		<label><input type='checkbox' name='ank_lov_13'".($user['ank_lov_13']==1?' checked="checked"':null)." value='1' /> Совместная аренда жилья</label><br />
		<label><input type='checkbox' name='ank_lov_14'".($user['ank_lov_14']==1?' checked="checked"':null)." value='1' /> Занятия спортом</label><br />
    	<br />";
    }
    
    if ($get_set == 'mobile') {
        echo "Номер телефона:<br /><input type='text' name='ank_n_tel' value='$user[ank_n_tel]' maxlength='11' /><br />";
    }
    
    if ($get_set == 'osebe') {
        echo "О себе:<br /><input type='text' name='ank_o_sebe' value='$user[ank_o_sebe]' maxlength='512' /><br />";
    }
    
    if ($get_set == 'opar') {
        echo "О партнере:<br /><input type='text' name='ank_o_par' value='$user[ank_o_par]' maxlength='215' /><br />";
    }
   
    echo "<input type='submit' name='save' value='Сохранить' /></form>\n";
    
} else {
    echo "<div class='nav2'>";
    echo "Основное";
    echo "</div>";
    echo "<div class='nav1'>";
    if ($user['set_nick'] == 1) {
        echo "<a href='?set=nick'> <img src='/style/icons/str.gif' alt='*'>  <b>Nick Name</b></a>";
        if ($user['nick']!=null) {
            echo " &#62; $user[nick]<br />\n";
        } else {
            echo "<br />\n";
        }
    }
    echo "<a href='?set=name'> <img src='/style/icons/str.gif' alt='*'>  Имя</a>";
    if ($user['ank_name']!=null) {
        echo " &#62; $user[ank_name]<br />\n";
    } else {
        echo "<br />\n";
    }
    echo "<a href='?set=pol'> <img src='/style/icons/str.gif' alt='*'>  Пол</a> &#62; ".(($user['pol']==1)?'Мужской':'Женский')."<br />";
    echo "<a href='?set=gorod'> <img src='/style/icons/str.gif' alt='*'>  Город</a>";
    if ($user['ank_city']!=null) {
        echo " &#62; $user[ank_city]<br />\n";
    } else {
        echo "<br />\n";
    }
    echo "<a href='?set=date'> <img src='/style/icons/str.gif' alt='*'>  Дата рождения</a> ";
    if ($user['ank_d_r']!=null && $user['ank_m_r']!=null && $user['ank_g_r']!=null) {
        echo " &#62; $user[ank_d_r].$user[ank_m_r].$user[ank_g_r] г. <br />\n";
    } elseif ($user['ank_d_r']!=null && $user['ank_m_r']!=null) {
        echo " &#62; $user[ank_d_r].$user[ank_m_r]<br />\n";
    }
    echo "</div>";
    echo "<div class='nav2'>";
    echo "Типаж";
    echo "</div>";
    echo "<div class='nav1'>";
    echo "<a href='?set=rost'> <img src='/style/icons/str.gif' alt='*'>  Рост</a>";
    if ($user['ank_rost']!=null) {
        echo " &#62; $user[ank_rost]<br />\n";
    } else {
        echo "<br />\n";
    }
    echo "<a href='?set=ves'> <img src='/style/icons/str.gif' alt='*'>  Вес</a>";
    if ($user['ank_ves']!=null) {
        echo " &#62; $user[ank_ves]<br />\n";
    } else {
        echo "<br />\n";
    }
    echo "<a href='?set=glaza'> <img src='/style/icons/str.gif' alt='*'>  Глаза</a>";
    if ($user['ank_cvet_glas']!=null) {
        echo " &#62; $user[ank_cvet_glas]<br />\n";
    } else {
        echo "<br />\n";
    }
    echo "<a href='?set=volos'> <img src='/style/icons/str.gif' alt='*'>  Волосы</a>";
    if ($user['ank_volos']!=null) {
        echo " &#62; $user[ank_volos]<br />\n";
    } else {
        echo "<br />\n";
    }
    echo "<a href='?set=telo'> <img src='/style/icons/str.gif' alt='*'>  Телосложение</a> ";
    if ($user['ank_telosl']==1) {
        echo " &#62; Нет ответа<br />\n";
    }
    if ($user['ank_telosl']==2) {
        echo " &#62; Худощавое<br />\n";
    }
    if ($user['ank_telosl']==3) {
        echo " &#62; Обычное<br />\n";
    }
    if ($user['ank_telosl']==4) {
        echo " &#62; Спортивное<br />\n";
    }
    if ($user['ank_telosl']==5) {
        echo " &#62; Мускулистое<br />\n";
    }
    if ($user['ank_telosl']==6) {
        echo " &#62; Плотное<br />\n";
    }
    if ($user['ank_telosl']==7) {
        echo " &#62; Полное<br />\n";
    }
    if ($user['ank_telosl']==0) {
        echo "<br />\n";
    }
    echo "</div>";
    echo "<div class='nav2'>";
    echo "Для знакомства";
    echo "</div>";
    echo "<div class='nav1'>";
    echo "<a href='?set=orien'> <img src='/style/icons/str.gif' alt='*'>  Ориентация</a> ";
    if ($user['ank_orien']==0) {
        echo "<br />\n";
    }
    if ($user['ank_orien']==1) {
        echo " &#62;  Гетеро<br />\n";
    }
    if ($user['ank_orien']==2) {
        echo " &#62;  Би<br />\n";
    }
    if ($user['ank_orien']==3) {
        echo " &#62;  Гей/Лесби<br />\n";
    }
    echo "<a href='?set=loves'> <img src='/style/icons/str.gif' alt='*'>  Цели знакомства</a><br />";
    if ($user['ank_lov_1']==1) {
        echo " &#62; Дружба и общение<br />";
    }
    if ($user['ank_lov_2']==1) {
        echo " &#62; Переписка<br />";
    }
    if ($user['ank_lov_3']==1) {
        echo " &#62; Любовь, отношения<br />";
    }
    if ($user['ank_lov_4']==1) {
        echo " &#62; Регулярный секс вдвоем<br />";
    }
    if ($user['ank_lov_5']==1) {
        echo " &#62; Секс на один-два раза<br />";
    }
    if ($user['ank_lov_6']==1) {
        echo " &#62; Групповой секс<br />";
    }
    if ($user['ank_lov_7']==1) {
        echo " &#62; Виртуальный секс<br />";
    }
    if ($user['ank_lov_8']==1) {
        echo "&#62; Предлагаю интим за деньги<br />";
    }
    if ($user['ank_lov_9']==1) {
        echo " &#62; Ищу интим за деньги<br />";
    }
    if ($user['ank_lov_10']==1) {
        echo " &#62; Брак, создание семьи<br />";
    }
    if ($user['ank_lov_11']==1) {
        echo " &#62; Рождение, воспитание ребенка<br />";
    }
    if ($user['ank_lov_12']==1) {
        echo " &#62; Брак для вида<br />";
    }
    if ($user['ank_lov_13']==1) {
        echo " &#62; Совместная аренда жилья<br />";
    }
    if ($user['ank_lov_14']==1) {
        echo " &#62; Занятия спортом<br />";
    }
    echo "<a href='?set=opar'> <img src='/style/icons/str.gif' alt='*'>  О партнере</a>";
    if ($user['ank_o_par']!=null) {
        echo " &#62; ".htmlspecialchars($user['ank_o_par'])."<br />\n";
    } else {
        echo "<br />";
    }
    echo "<a href='?set=osebe'> <img src='/style/icons/str.gif' alt='*'>  О себе</a>";
    if ($user['ank_o_sebe']!=null) {
        echo " &#62; ".htmlspecialchars($user['ank_o_sebe'])."<br />\n";
    } else {
        echo "<br />";
    }
    echo "</div>";
    echo "<div class='nav2'>";
    echo "Общее положение";
    echo "</div>";
    echo "<div class='nav1'>";
    echo "<a href='?set=zan'> <img src='/style/icons/str.gif' alt='*'>  Чем занимаюсь</a> ";
    if ($user['ank_zan']!=null) {
        echo " &#62; ".htmlspecialchars($user['ank_zan']);
    }
    echo '<br />';
    echo "<a href='?set=mat_pol'> <img src='/style/icons/str.gif' alt='*'>  Материальное положение</a>";
    if ($user['ank_mat_pol']==1) {
        echo " &#62; Непостоянные заработки<br />\n";
    }
    if ($user['ank_mat_pol']==2) {
        echo " &#62; Постоянный небольшой доход<br />\n";
    }
    if ($user['ank_mat_pol']==3) {
        echo " &#62; Стабильный средний доход<br />\n";
    }
    if ($user['ank_mat_pol']==4) {
        echo " &#62; Хорошо зарабатываю / обеспечен<br />\n";
    }
    if ($user['ank_mat_pol']==5) {
        echo " &#62; Не зарабатываю<br />\n";
    }
    if ($user['ank_mat_pol']==0) {
        echo "<br />\n";
    }
    echo "<a href='?set=avto'> <img src='/style/icons/str.gif' alt='*'>  Наличие автомобиля</a>";
    if ($user['ank_avto_n']==1) {
        echo " &#62; Есть<br />\n";
    }
    if ($user['ank_avto_n']==2) {
        echo " &#62; Нет<br />\n";
    }
    if ($user['ank_avto_n']==3) {
        echo " &#62; Хочу купить<br />\n";
    }
    if ($user['ank_avto_n']==0) {
        echo "<br />\n";
    }
    if ($user['ank_avto'] && $user['ank_avto_n']!=2 && $user['ank_avto_n']!=0) {
        echo "<img src='/style/icons/str.gif' alt='*'>  ".htmlspecialchars($user['ank_avto'])."<br />";
    }
    echo "<a href='?set=proj'> <img src='/style/icons/str.gif' alt='*'>  Проживание</a> ";
    if ($user['ank_proj']==1) {
        echo " &#62; Отдельная квартира (снимаю или своя)<br />\n";
    }
    if ($user['ank_proj']==2) {
        echo " &#62; Комната в общежитии, коммуналка<br />\n";
    }
    if ($user['ank_proj']==3) {
        echo " &#62; Живу с родителями<br />\n";
    }
    if ($user['ank_proj']==4) {
        echo " &#62; Живу с приятелем / с подругой<br />\n";
    }
    if ($user['ank_proj']==5) {
        echo " &#62; Живу с партнером или супругом (-ой)<br />\n";
    }
    if ($user['ank_proj']==6) {
        echo " &#62; Нет постоянного жилья<br />\n";
    }
    if ($user['ank_proj']==0) {
        echo "<br />\n";
    }
    echo "<a href='?set=baby'> <img src='/style/icons/str.gif' alt='*'>  Есть ли дети</a> ";
    if ($user['ank_baby']==1) {
        echo " &#62; Нет<br />\n";
    }
    if ($user['ank_baby']==2) {
        echo " &#62; Нет, но хотелось бы<br />\n";
    }
    if ($user['ank_baby']==3) {
        echo " &#62; Есть, живем вместе<br />\n";
    }
    if ($user['ank_baby']==4) {
        echo " &#62; Есть, живем порознь<br />\n";
    }
    if ($user['ank_baby']==0) {
        echo "<br />\n";
    }
    echo "</div>";
    echo "<div class='nav2'>";
    echo "Привычки";
    echo "</div>";
    echo "<div class='nav1'>";
    echo "<a href='?set=smok'> <img src='/style/icons/str.gif' alt='*'>  Курение</a>";
    if ($user['ank_smok']==1) {
        echo " &#62; Не курю<br />\n";
    }
    if ($user['ank_smok']==2) {
        echo " &#62; Курю<br />\n";
    }
    if ($user['ank_smok']==3) {
        echo " &#62; Редко<br />\n";
    }
    if ($user['ank_smok']==4) {
        echo " &#62; Бросаю<br />\n";
    }
    if ($user['ank_smok']==5) {
        echo " &#62; Успешно бросил<br />\n";
    }
    if ($user['ank_smok']==0) {
        echo "<br />\n";
    }
    echo "<a href='?set=alko'> <img src='/style/icons/str.gif' alt='*'>  Алкоголь</a> ";
    if ($user['ank_alko_n']==1) {
        echo "&#62; Да, выпиваю<br />\n";
    }
    if ($user['ank_alko_n']==2) {
        echo "&#62; Редко, по праздникам<br />\n";
    }
    if ($user['ank_alko_n']==3) {
        echo "&#62; Нет, категорически не приемлю<br />\n";
    }
    if ($user['ank_alko_n']==0) {
        echo "<br />\n";
    }
    if ($user['ank_alko'] && $user['ank_alko_n']!=3 && $user['ank_alko_n']!=0) {
        echo "<img src='/style/icons/str.gif' alt='*'>  ".htmlspecialchars($user['ank_alko'])."<br />";
    }
    echo "<a href='?set=nark'> <img src='/style/icons/str.gif' alt='*'>  Наркотики</a> ";
    if ($user['ank_nark']==1) {
        echo " Да, курю травку<br />\n";
    }
    if ($user['ank_nark']==2) {
        echo "&#62; Да, люблю любой вид наркотических средств<br />\n";
    }
    if ($user['ank_nark']==3) {
        echo "&#62; Бросаю, прохожу реабилитацию<br />\n";
    }
    if ($user['ank_nark']==4) {
        echo "&#62; Нет, категорически не приемлю<br />\n";
    }
    if ($user['ank_nark']==0) {
        echo "<br />\n";
    }
    echo "</div>";
    echo "<div class='nav2'>";
    echo "Контакты";
    echo "</div>";
    echo "<div class='nav1'>";
    echo "<a href='?set=mobile'> <img src='/style/icons/str.gif' alt='*'>  Мобильный</a> ";
    if ($user['ank_n_tel']) {
        echo "&#62; $user[ank_n_tel]<br />";
    } else {
        echo "<br />";
    }
    echo "<a href='?set=icq'> <img src='/style/icons/str.gif' alt='*'>  ICQ</a> ";
    if ($user['ank_icq']) {
        echo "&#62; $user[ank_icq]<br />";
    } else {
        echo "<br />";
    }
    echo "<a href='?set=mail'> <img src='/style/icons/str.gif' alt='*'>  E-Mail</a> ";
    if ($user['ank_mail']) {
        echo "&#62; $user[ank_mail]<br />";
    } else {
        echo "<br />";
    }
    echo "<a href='?set=skype'> <img src='/style/icons/str.gif' alt='*'>  Skype</a> ";
    if ($user['ank_skype']) {
        echo "&#62; $user[ank_skype]<br />";
    } else {
        echo "<br />";
    }
    echo "</div>";
}
echo "<div class='foot'><img src='/style/icons/str.gif' alt='*'> <a href='anketa.php'>Посмотреть анкету</a><br />";
if (isset($_SESSION['refer']) && $_SESSION['refer']!=null && otkuda($_SESSION['refer'])) {
    echo "<img src='/style/icons/str2.gif' alt='*'> <a href='$_SESSION[refer]'>".otkuda($_SESSION['refer'])."</a><br />\n";
}
echo '</div>';
    
include_once '../../sys/inc/tfoot.php';
