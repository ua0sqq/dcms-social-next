<?php
/***** Определяем тип репостнутой записи *****/
function notes_sh($id=null)
{
    $sql=go\DB\query("SELECT * FROM `notes` WHERE `id`='".(int)$id."' LIMIT 1")->assoc();
    if (count($sql)) {
        foreach ($sql as $post);
        if ($post['share']==1) {
            if ($post['share_type']=='notes') {
                ?><div style="padding-left:5px;padding-top:5px;margin-left:17px;border-top:1px solid #b3b3b3;border-left:1px solid #b3b3b3;">
<img src='/style/icons/repostik.png' style='width:16px;'><i> Репост записи <?php echo "<a href='/plugins/notes/list.php?id=".$post['share_id']."'><b style='color:#758b9b;'>".text($post['share_name'])."</b></a></i><br/>";
                echo " ".rez_text(smiles(htmlspecialchars($post['share_text'])))." "; ?></div><?php
            } elseif ($post['share_type']=='forum') {
                $them=go\DB\query("SELECT `id_forum`,`id`,`id_razdel` FROM `forum_t` WHERE `id`='".$post['share_id']."' LIMIT 1")->row(); ?><div style="padding-left:5px;padding-top:5px;margin-left:17px;border-top:1px solid #b3b3b3;border-left:1px solid #b3b3b3;">
<img src='/style/icons/repostik.png' style='width:16px;'><i> Репост темы форума <?php echo "<a href='/forum/".$them['id_forum']."/".$them['id_razdel']."/".$post['share_id']."/'><b style='color:#758b9b;'>".text($post['share_name'])."</b></span></i></a><br/>";
                echo " ".rez_text(smiles(htmlspecialchars($post['share_text'])))." "; ?></div><?php
            }
        }
    }
}
 function notes_share($id=null)
 {
     $sql=go\DB\query("SELECT * FROM `notes` WHERE `id`='".(int)$id."' LIMIT 1")->assoc();
     if (count($sql)) {
         foreach ($sql as $post);
         if ($post['share']==1) {
             if ($post['share_type']=='notes') {
                 ?><div style="padding-left:5px;padding-top:5px;margin-left:17px;border-top:1px solid #b3b3b3;border-left:1px solid #b3b3b3;">
<img src='/style/icons/repostik.png' style='width:16px;'><i> Репост записи <?php echo "<a href='/plugins/notes/list.php?id=".$post['share_id']."'><b style='color:#758b9b;'>".text($post['share_name'])."</b></a></i><br/>";
                 echo " ".output_text($post['share_text'])." "; ?></div><?php
             } elseif ($post['share_type']=='forum') {
                 $them=go\DB\query("SELECT `id_forum`,`id`,`id_razdel` FROM `forum_t` WHERE `id`='".$post['share_id']."' LIMIT 1")->row(); ?><div style="padding-left:5px;padding-top:5px;margin-left:17px;border-top:1px solid #b3b3b3;border-left:1px solid #b3b3b3;">
<img src='/style/icons/repostik.png' style='width:16px;'><i> Репост темы форума <?php echo "<a href='/forum/".$them['id_forum']."/".$them['id_razdel']."/".$post['share_id']."/'><b style='color:#758b9b;'>".text($post['share_name'])."</b></span></i></a><br/>";
                 echo " ".output_text($post['share_text'])." "; ?></div><?php
             }
         }
     }
 }
?>