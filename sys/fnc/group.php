<?php
function group($user = null)
{
    if (!$ank = go\DB\query(
        "SELECT `group_access`, `pol`  FROM `user` WHERE `id`=?i AND NOT EXISTS(
SELECT `id_user` FROM `ban` WHERE  `id_user`=?i  AND (`time`>?i OR `navsegda`=1))",
                            [$user, $user, time()]
    )->row()) {
        return ' <img src="/style/user/ban.png" alt="*" class="icon" id="icon_group" /> ';
    }
    if ($ank['group_access'] > 7 && ($ank['group_access'] < 10 || $ank['group_access'] > 14)) {
        if ($ank['pol'] == 1) {
            return '<img src="/style/user/1.png" alt="*" class="icon" id="icon_group" /> ';
        }
        return '<img src="/style/user/2.png" alt="" class="icon"/> ';
    } elseif (($ank['group_access'] > 1 && $ank['group_access'] <= 7) || ($ank['group_access'] > 10 && $ank['group_access'] <= 14)) {
        if ($ank['pol'] == 1) {
            return '<img src="/style/user/3.png" alt="*" class="icon" id="icon_group" /> ';
        }
        return '<img src="/style/user/4.png" alt="*" class="icon" id="icon_group" /> ';
    } else {
        if ($ank['pol'] == 1) {
            return '<img src="/style/user/5.png" alt="" class="icon" id="icon_group" /> ';
        }
        return '<img src="/style/user/6.png" alt="" class="icon" id="icon_group" /> ';
    }
}
