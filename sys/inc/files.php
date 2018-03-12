<?php
function user_files($path)
{
    $path=preg_replace("#(/){1,}#", "/", $path);
    $path=preg_replace("#(^(/){1,})|((/){1,}$)#", "", $path);
    $path_arr=explode('/', $path);
    $rudir=null;
    for ($i=0;$i<count($path_arr);$i++) {
        $of='/';
        for ($z=0;$z<=$i;$z++) {
            $of.=$path_arr[$z].'/';
        }
        $dir_id=go\DB\query("SELECT * FROM `user_files` WHERE `id` = '$path_arr[$i]' LIMIT 1")->row();
        $dirname=$dir_id['name'];
        $rudir.=' <a href="/user/personalfiles/'.$dir_id['id_user'].'/'.$dir_id['id'].'/">'.htmlspecialchars($dirname).'</a> &gt; ';
    }
    return preg_replace("# &gt; $#", "", $rudir);
}
