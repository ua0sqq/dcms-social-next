<?php
    $cel = "(
	`ank_lov_1` = '1' OR 
	`ank_lov_2` = '1' OR 
	`ank_lov_3` = '1' OR 
	`ank_lov_4` = '1' OR 
	`ank_lov_5` = '1' OR 
	`ank_lov_6` = '1' OR 
	`ank_lov_7` = '1' OR 
	`ank_lov_8` = '1' OR 
	`ank_lov_9` = '1' OR 
	`ank_lov_10` = '1' OR 
	`ank_lov_11` = '1' OR 
	`ank_lov_12` = '1' OR 
	`ank_lov_13` = '1' OR 
	`ank_lov_14` = '1'
	)";
    $orien = "(
	`ank_orien` = '1' OR 
	`ank_orien` = '2' OR 
	`ank_orien` = '3'
	)";
    $opar = "(
	`ank_o_par` IS NOT NULL 
	)";
    $osebe = "(
	`ank_o_sebe` IS NOT NULL 
	)";
$k_p = $db->query("SELECT COUNT(*) FROM `user` WHERE $cel AND $orien AND $opar AND $osebe AND `date_last` > '".(time()-259200)."'")->el();
$k_n = $db->query("SELECT COUNT(*) FROM `user` WHERE $cel AND $orien AND $opar AND $osebe AND `date_reg` > '".(time()-8600)."'")->el();
if ($k_n == 0) {
    $k_n = null;
} else {
    $k_n = '+' . $k_n;
}
echo '(' . $k_p . ') <font color="red">' . $k_n . '</font>';
