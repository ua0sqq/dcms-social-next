<?php
function online($user = null)
{
    static $users;
    if (!isset($users[$user])) {
        if ($ank = go\DB\query(
            "SELECT `browser` FROM `user` WHERE `id`=?i AND `date_last`>?i",
                        [$user, (time()-600)]
        )->el()) {
            if ($ank == 'wap') {
                $users[$user] = ' <img src="/style/icons/online.gif" alt="*" /> ';
            } else {
                $users[$user] = ' <img src="/style/icons/online_web.gif" alt="*" /> ';
            }
        } else {
            $users[$user]=null;
        }
    }
    return $users[$user];
}
