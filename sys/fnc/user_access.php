<?php
function user_access($access, $u_id = null, $exit = false)
{
    if ($u_id == null) {
        global $user;
    } else {
        $user['group_access'] = go\DB\query("SELECT `group_access` FROM `user` WHERE `id`=?i", [$u_id])->el();
    }
    if (!isset($user['group_access']) || $user['group_access'] == null) {
        if ($exit !== false) {
            header('Location: ' . $exit);
            exit;
        } else {
            return false;
        }
    }
    if ($exit !== false) {
        if (!go\DB\query(
            "SELECT COUNT(*) FROM `user_group_access` WHERE `id_group`=?i AND `id_access`=?",
                         [$user['group_access'], $access]
        )->el()) {
            header("Location: $exit");
            exit;
        }
    } else {
        return (go\DB\query(
            "SELECT COUNT(*) FROM `user_group_access` WHERE `id_group`=?i AND `id_access`=?",
                            [$user['group_access'], $access]
        )->el() ? true : false);
    }
}
