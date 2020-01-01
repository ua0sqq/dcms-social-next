<?php
/**
* / Основные пользовательские функции
* / nick() - выводит ник и значок онлайна
* / avatar - выводит аватар и иконку пользователя
* / у всех функций есть параметры что выводить а что нет
*/
class user
{

    /**
    * / Ссылка и Ник юзера
    */
    public static function nick($user = 0, $url = 1, $on = 0, $medal = 0)
    {
        /*
        * $url == 0		Выводит только ник
        * $url == 1		Выводит ник с ссылкой на страницу юзера
        * $on  == 1		Выводит рядом с ником значок онлайн
        * $medal == 1	Выводит медальку рядом со значком онлайн
        */

        $ank =go\DB\query('SELECT `nick`, `date_last`, `rating`, `browser` FROM `user` WHERE `id`=?i',
                          [$user])->row();

        $nick = null;
        $online = null;
        $icon_medal = null;

        // Вывод ника
        if ($user == 0) {
            $ank = array('id' => '0', 'nick' => 'Cистема', 'pol' => '1', 'rating' => '0', 'browser' => 'wap', 'date_last' => time());
        } elseif (!$ank) {$url = 0;
            $ank = array('id' => '0', 'nick' => '[Удален]', 'pol' => '1', 'rating' => '0', 'browser' => 'wap', 'date_last' => time());
        }

        if ($url == true) {
            $nick = ' <a href="/id' . $user . '">' . text($ank['nick']) . '</a> ';
        } else {
            $nick = text($ank['nick']);
        }

        // Вывод значка онлайн
        if ($user != 0 && $ank['date_last'] > TIME_600 && $on == true) {
            if ($ank['browser'] == 'wap') {
                $online = ' <img src="/style/icons/online.gif" alt="WAP" /> ';
            } else {
                $online = ' <img src="/style/icons/online_web.gif" alt="WEB" /> ';
            }
        }

        // Вывод медали
        $R = $ank['rating'];

        if ($medal == 1 && $R >= 6) {
            if ($R >= 6 && $R <= 11) {
                $img = 1;
            } elseif ($R >= 12 && $R <= 19) {
                $img = 2;
            } elseif ($R >= 20 && $R <= 27) {
                $img = 3;
            } elseif ($R >= 28 && $R <= 37) {
                $img = 4;
            } elseif ($R >= 38 && $R <= 47) {
                $img = 5;
            } elseif ($R >= 48 && $R <= 59) {
                $img = 6;
            } elseif ($R >= 60) {
                $img = 7;
            }
            $icon_medal = ' <img src="/style/medal/' . $img . '.png" alt="ico" /> ';
        }

        return $nick . $icon_medal . $online;
    }
    /**
    * / Аватар, иконка группы пользователя
    */
    public static function avatar($user = 0, $type = 0)
    {
        /*
        * $type == 0 - Выводит аватар и иконку вместе
        * $type == 1 - Выводит только аватар
        * $type == 2 - Выводит только иконку
        */
        global $time, $set;

        $avatar = null;
        $icon = null;
        if ($user != 0) {
            $ank =go\DB\query(
                        'SELECT u.`pol`, u.`id`, u.`group_access`, gf.`id` AS id_foto, gf.`ras`, (
SELECT COUNT( * ) FROM `ban` WHERE `id_user`=u.`id` AND (`time`>?i OR `navsegda`=1)) AS icon
FROM `user` u
LEFT JOIN `gallery_foto` gf ON gf.`id_user`=u.`id` AND gf.`avatar`="1"
WHERE u.`id`=?i',
[time(), $user])->row();
        }

        if ($user == 0) {
            $ank = array('id' => '0', 'pol' => '1', 'group_access' => '0');
        } elseif (!$ank) {
            $ank = array('id' => '0', 'pol' => '1', 'group_access' => '0');
        }

        // Аватар
        if ($type == 0 || $type == 1) {
            if (is_file(H . 'sys/gallery/50/' . $ank['id_foto'] . '.' . $ank['ras'])) {
                $avatar = ' <img class="avatar" src="/foto/foto50/' . $ank['id_foto'] . '.' . $ank['ras'] . '" alt="Avatar" /> ';
            } else {
                $avatar = '<img class="avatar" src="/style/user/avatar.gif" width="50" alt="No Avatar" /> ';
            }
        }

        // Иконка пользователя
        if ($type == 0 || $type == 2) {
            if ($ank['icon']) {
                $icon = ' <img src="/style/user/ban.png" alt="ico" class="icon" id="icon_group" /> ';
            } else {
                if ($ank['group_access'] > 7 && ($ank['group_access'] < 10 || $ank['group_access'] > 14)) {
                    if ($ank['pol'] == 1) {
                        $icon = '<img src="/style/user/1.png" alt="ico" class="icon" id="icon_group" /> ';
                    } else {
                        $icon = '<img src="/style/user/2.png" alt="" class="icon"/> ';
                    }
                } elseif (($ank['group_access'] > 1 && $ank['group_access'] <= 7) || ($ank['group_access'] > 10 && $ank['group_access'] <= 14)) {
                    if ($ank['pol'] == 1) {
                        $icon = '<img src="/style/user/3.png" alt="ico" class="icon" id="icon_group" /> ';
                    } else {
                        $icon = '<img src="/style/user/4.png" alt="ico" class="icon" id="icon_group" /> ';
                    }
                } elseif (isset($ank['status']) == 0) {
                    if ($ank['pol'] == 1) {
                        $icon = '<img src="/style/user/5.png" alt="" class="icon" id="icon_group" /> ';
                    } else {
                        $icon = '<img src="/style/user/6.png" alt="" class="icon" id="icon_group" /> ';
                    }
                }
            }
        }

        return $avatar . $icon;
    }

    /**
    * / Функция выборки пользовательских данных
    * / Выводин данные из таблицы user
    * / и генериует аватар, иконки медалей и онлайна в массив
    * $ank['link'], $ank['avatar'], $ank['online'],
    * $ank['medal'], $ank['icon']
    */
    public static function get_user($ID = 0, $photo = 1)
    {
        /*
        * $ID	- ID юзера
        * $photo - Параметр на выборку аватара
        */

        global $user;
        $ank=array();
        $ID = (int)$ID; //Определяем ID и $ank
        $ank['group_name'] = null;

        // Если вы авторизованы, и функция вызывает
        // ваш ID, то просто берем данные из $user
        if ($user['id'] == $ID) {
            $ank = $user;
        } else {
            // Иначе выбираем из базы
            $ank = go\DB\query('SELECT * FROM `user` WHERE `id`=?i', [$ID])->row();
        }

        // Если система или неопределенный юзер
        if ($ID == 0) {
            $ank = array('id' => '0', 'pol' => '1', 'wmid' => '0', 'group_access' => '0', 'level' => '999');
        } elseif (!$ank) {
            $ank = array('id' => '0', 'pol' => '1', 'wmid' => '0', 'group_access' => '0', 'level' => '0');
        } else {
            $tmp_us = go\DB\query("SELECT `level`, `name` AS `group_name` FROM `user_group` WHERE `id`=?i",
                                  [$ank['group_access']])->row();
            $ank['group_name'] = $tmp_us['group_name'];
            $ank['level'] = $tmp_us['level'];
        }

        // Если поставлен параметр выводить фото
        if ($photo) {
            // Определяем аватар
            $avatar = go\DB\query("SELECT `id`, `ras` FROM `gallery_foto` WHERE `id_user`=?i AND `avatar`=? LIMIT ?i",
                                  [$ID, '1', 1])->row();

            if (is_file(H.'sys/gallery/50/' . $avatar['id'] . '.' . $avatar['ras'])) {
                $ank['avatar'] = ' <img class="avatar" src="/sys/gallery/50/' . $avatar['id'] . '.' . $avatar['ras'] . '" alt="Avatar" /> ';
            } else {
                $ank['avatar'] = ' <img class="avatar" src="/style/user/avatar.gif" width="50" alt="No Avatar" /> ';
            }
        }

        // Вывод значка онлайн
        if ($ID != 0 && $ank['date_last'] > TIME_600) {
            if ($ank['browser'] == 'wap') {
                $ank['online'] = ' <img src="/style/icons/online.gif" alt="WAP" /> ';
            } else {
                $ank['online'] = ' <img src="/style/icons/online_web.gif" alt="WEB" /> ';
            }
        } else {
            $ank['online'] = null;
        }

        // Вывод медали
        $R = $ank['rating'];

        if ($R >= 6) {
            if ($R >= 6 && $R <= 11) {
                $img = 1;
            } elseif ($R >= 12 && $R <= 19) {
                $img = 2;
            } elseif ($R >= 20 && $R <= 27) {
                $img = 3;
            } elseif ($R >= 28 && $R <= 37) {
                $img = 4;
            } elseif ($R >= 38 && $R <= 47) {
                $img = 5;
            } elseif ($R >= 48 && $R <= 59) {
                $img = 6;
            } elseif ($R >= 60) {
                $img = 7;
            }
            $ank['medal'] = ' <img src="/style/medal/' . $img . '.png" alt="ico" /> ';
        } else {
            $ank['medal'] = null;
        }

        // Иконка пользователя
        if (go\DB\query("SELECT COUNT( * ) FROM `ban` WHERE `id_user`=?i AND (`time`>?i OR `navsegda`=?i)",
                        [$ID, time(), 1])->el()) {
            $ank['icon'] = ' <img src="/style/user/ban.png" alt="ico" class="icon" id="icon_group" /> ';
        } else {
            if ($ank['group_access'] > 7 && ($ank['group_access'] < 10 || $ank['group_access'] > 14)) {
                if ($ank['pol'] == 1) {
                    $ank['icon'] = '<img src="/style/user/2.png" alt="ico" class="icon" id="icon_group" /> ';
                } else {
                    $ank['icon'] = '<img src="/style/user/120.png" alt="" class="icon"/> ';
                }
            } elseif (($ank['group_access'] > 1 && $ank['group_access'] <= 7) || ($ank['group_access'] > 10 && $ank['group_access'] <= 14)) {
                if ($ank['pol'] == 1) {
                    $ank['icon'] = '<img src="/style/user/77.png" alt="ico" class="icon" id="icon_group" /> ';
                } else {
                    $ank['icon'] = '<img src="/style/user/118.png" alt="ico" class="icon" id="icon_group" /> ';
                }
            } else {
                if ($ank['pol'] == 1) {
                    $ank['icon'] = '<img src="/style/user/23.png" alt="" class="icon" id="icon_group" /> ';
                } else {
                    $ank['icon'] = '<img src="/style/user/117.png" alt="" class="icon" id="icon_group" /> ';
                }
            }
        }

        $ank['link'] = ' <a href="/id' . $ID . '">' . text($ank['nick']) . '</a> ';
        $ank['nick'] = text($ank['nick']);

        return $ank;
    }
}
