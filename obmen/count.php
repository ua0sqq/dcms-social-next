<?php
$my_dir = $db->query("SELECT id FROM `obmennik_dir` WHERE `my` = '1' LIMIT 1")->row();
$k_p=$db->query("SELECT COUNT(*) FROM `obmennik_files` WHERE `id_dir` != '$my_dir[id]'")->el();
$k_n= $db->query("SELECT COUNT(*) FROM `obmennik_files` WHERE `id_dir` != '$my_dir[id]' AND `time_go` > '".$ftime."'")->el();
if ($k_n==0) {
    $k_n=null;
} else {
    $k_n='+'.$k_n;
}
echo "($k_p) <font color='red'>$k_n</font>";
?>
