<?php

if (!isset($set['forum_counter']) || $set['forum_counter']==0) {
    $adm_add_mass=null;
    $adm_add=null;
    if (!isset($user) || $user['level']==0) {
        $q222=$db->query("SELECT * FROM `forum_f` WHERE `adm` = '1'")->assoc();
        if (count($q222)) {
            $adm_add=' WHERE ';
            foreach ($q222 as $adm_f) {
                $adm_add_mass[]=$adm_f['id'];
            }
            for ($zzz=0;$zzz<count($adm_add_mass);$zzz++) {
                $adm_add.="`id_forum` <> '$adm_add_mass[$zzz]'";
                if (count($adm_add_mass)!=$zzz+1) {
                    $adm_add.= ' AND ';
                }
            }
        }
    }
    echo '('.$db->query("SELECT COUNT(*) FROM `forum_p`$adm_add")->el().'/'.
$db->query("SELECT COUNT(*) FROM `forum_t`$adm_add")->el().')';
} else {
    echo $db->query("SELECT COUNT(*) FROM `user` WHERE `date_last` > '".(time()-600)."' AND `url` like '/forum/%'")->el().' человек';
}
