<?php
/*
Dcms-Social
Искатель
http://mydcms.ru
*/
if (isset($_GET['act']) && $_GET['act'] == 'upload' && isset($_GET['ok']) && $l != '/' && $user['id'] == $trans['id_user']) {
    $dir_my=$db->query(
        "SELECT * FROM `obmennik_dir` WHERE `id`=?i",
                       [$trans['id_dir']])->row();
    if ($dir_id['upload']==1) {
        $ras=$trans['ras'];
        $rasss=explode(';', $dir_id['ras']);
        $ras_ok=false;
        for ($i=0;$i<count($rasss);$i++) {
            if ($rasss[$i]!=null && $ras==$rasss[$i]) {
                $ras_ok=true;
            }
        }
        if (!$ras_ok) {
            $err='Неверное расширение файла';
        }
        if (!isset($err)) {
            $db->query(
                'UPDATE `obmennik_files` SET `id_dir`=?i WHERE `id`=?i',
                       [$dir_id['id'], $trans['id']]);
            $_SESSION['message'] = 'Файл успешно добавлен в папку '.$dir_id['name'].' зоны обмена';
            header('Location: /user/personalfiles/' . $trans['id_user'] . '/' . $trans['my_dir'] . '/?id_file=' . $trans['id']);
            exit;
        } else {
            $_SESSION['message'] = $err;
            header('Location: /user/personalfiles/' . $trans['id_user'] . '/' . $trans['my_dir'] . '/?id_file=' . $trans['id']);
            exit;
        }
    } else {
        echo "Ошибка! Эта папка не доступна!";
        exit;
    }
}
