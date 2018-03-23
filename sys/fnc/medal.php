<?php
function medal($user = 0)
{
    $ank = (int)go\DB\query("SELECT `rating` FROM `user` WHERE `id`=?i", [$user])->el();
    
    $img = 0;
    if ($ank >= 6 && $ank <= 11) {
        $img = 1;
    } elseif ($ank >= 12 && $ank <= 19) {
        $img = 2;
    } elseif ($ank >= 20 && $ank <= 27) {
        $img = 3;
    } elseif ($ank >= 28 && $ank <= 37) {
        $img = 4;
    } elseif ($ank >= 38 && $ank <= 47) {
        $img = 5;
    } elseif ($ank >= 48 && $ank <= 59) {
        $img = 6;
    } elseif ($ank >= 60) {
        $img = 7;
    }
    
    if ($img) {
        return ' <img src="/style/medal/' . $img . '.png" alt="DS" />';
    }
}
