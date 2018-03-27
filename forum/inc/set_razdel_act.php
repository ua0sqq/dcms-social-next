<?php
if (isset($input_get['act']) && isset($input_get['ok']) && $input_get['act']=='set' && isset($_POST['name'])) {
    $name=$_POST['name'];
    $opis=$_POST['opis'];
    if (strlen2($name)<3) {
        $err='Слишком короткое название';
    }
    if (strlen2($name)>32) {
        $err='Слишком днинное название';
    }
    $name=my_esc($name);
    $opis=my_esc($opis);
    if (!isset($err)) {
        $razd=$db->query("SELECT * FROM `forum_r` WHERE `id` = '".intval($input_get['id_razdel'])."' AND `id_forum` = '".intval($input_get['id_forum'])."' LIMIT 1")->row();
        admin_log('Форум', 'Разделы', "Переименование раздела '$razd[name]' в '$name'");
        $db->query("UPDATE `forum_r` SET `name` = '$name', `opis` = '$opis' WHERE `id` = '$razdel[id]' LIMIT 1");
        $razdel=$db->query("SELECT * FROM `forum_r` WHERE `id` = '$razdel[id]' LIMIT 1")->row();
        msg('Изменения успешно приняты');
    }
}
$razd=$db->query("SELECT * FROM `forum_r` WHERE `id` = '".intval($input_get['id_razdel'])."' AND `id_forum` = '".intval($input_get['id_forum'])."' LIMIT 1")->row();

if (isset($input_get['act']) && isset($input_get['ok']) && $input_get['act'] == 'mesto' && isset($_POST['forum']) && is_numeric($_POST['forum'])
&& $db->query("SELECT COUNT(*) FROM `forum_f` WHERE `id` = '".intval($_POST['forum'])."'")->el()) {
    $forum_new['id']=intval($_POST['forum']);
    $forum_old=$forum;
    $db->query("UPDATE `forum_p` SET `id_forum` = '$forum_new[id]' WHERE `id_forum` = '$forum[id]' AND `id_razdel` = '$razdel[id]'");
    $db->query("UPDATE `forum_t` SET `id_forum` = '$forum_new[id]' WHERE `id_forum` = '$forum[id]' AND `id_razdel` = '$razdel[id]'");
    $db->query("UPDATE `forum_r` SET `id_forum` = '$forum_new[id]' WHERE `id_forum` = '$forum[id]' AND `id` = '$razdel[id]'");
    $forum=$db->query("SELECT * FROM `forum_f` WHERE `id` = '$forum_new[id]' LIMIT 1")->row();
    admin_log('Форум', 'Разделы', "Перенос раздела '$razd[name]' из подфорума '$forum_old[name]' в '$forum[name]'");
    msg('Раздел успешно перенесен');
}
// removed razdel's
if (isset($input_get['act']) && isset($input_get['ok']) && $input_get['act'] == 'razdel_delete') {
    $db->query("DELETE FROM `forum_r` WHERE `id`=?i", [$razdel['id']]);
    $db->query("DELETE FROM `forum_t` WHERE `id_razdel`=?i", [$razdel['id']]);
    $db->query("DELETE FROM `forum_p` WHERE `id_razdel`=?i", [$razdel['id']]);
    $res = $db->query('SELECT `id` FROM `forum_files` WHERE `id_post` NOT IN(SELECT `id` FROM `forum_p`)')->col();
    if (count($res)) {
        foreach ($res as $id) {
            if (is_file(H . 'sys/forum/files/' . $id . '.frf')) {
                unlink(H . 'sys/forum/files/' . $id . '.frf');
            }
        }
        $db->query('DELETE FROM `forum_files` WHERE `id` IN(?li)', [$res]);
        $db->query('DELETE FROM `forum_files_rating` WHERE `id_file` NOT IN(SELECT `id` FROM `forum_files`)');
    }
    $db->query('OPTIMIZE TABLE `forum_r`, `forum_t`, `forum_p`, `forum_files`, `forum_files_rating`');
    admin_log('Форум', 'Разделы', 'Удаление раздела "' . $razd['name'] . '" из подфорума "' . $forum['name'] . '"');
    msg('Раздел успешно удален');
    err();
    aut();
    echo "<a href=\"/forum/$forum[id]/\">В Подфорум</a><br />\n";
    echo "<a href=\"/forum/\">В форум</a><br />\n";

    include_once '../sys/inc/tfoot.php';
}
