<?php
function cmp2($a, $b)
{
    if ($a['2'] == $b['2']) {
        return 0;
    }
    return ($a['2'] > $b['2']) ? -1 : 1;
}
