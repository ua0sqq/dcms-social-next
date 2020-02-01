<?php
require_once '../../sys/inc/start.php';
require_once H . 'sys/inc/compress.php';
require_once H . 'sys/inc/sess.php';
require_once H . 'sys/inc/settings.php';
require_once H . 'sys/inc/db_connect.php';
require_once H . 'sys/inc/ipua.php';
require_once H . 'sys/inc/fnc.php';
require_once H . 'sys/inc/adm_check.php';
require_once H . 'sys/inc/user.php';
$my = null;
$frend = null;
$all = null;
only_reg();

$args = [
         'likestatus' => FILTER_VALIDATE_INT,
         'page' => [
                    'filter'  => FILTER_VALIDATE_INT,
                    'options' => [
                                  'default'   => 1,
                                  'min_range' => 1,
                                  ],
                    ],
         'read' =>  FILTER_DEFAULT,
         'delete' => FILTER_DEFAULT,
         ];
$input_get = filter_input_array(INPUT_GET, $args);
unset($args);

$page = isset($input_get['page']) ?: 0;

// Класс к статусу
if (isset($input_get['likestatus'])) {
    // Статус пользователя
    $status = $db->query(
        "SELECT `id`, `id_user` FROM `status` WHERE `id`=?i",
        [$input_get['likestatus']]
    )->row();

    if (
        $user['id'] != $status['id_user']
        && !$db->query(
            "SELECT COUNT( * ) FROM `status_like` WHERE `id_status`=?i AND `id_user`=?i",
            [$status['id'], $user['id']]
        )->el()
    ) {
        $db->query(
            "INSERT INTO `status_like` (`id_user`, `time`, `id_status`) VALUES(?i, ?i, ?i)",
            [$user['id'], time(), $status['id']]
        );

        // Лента
        $res = $db->query(
            "SELECT `fr`.`frend`
FROM `frends` fr
JOIN `tape_set` tps ON `tps`.`id_user`=`fr`.`frend`
WHERE `fr`.`user`=?i AND fr.`frend`<>?i AND fr.`i`=1 AND `fr`.`lenta_status_like`=1 AND `tps`.`lenta_status_like`=1",
            [$user['id'], $status['id_user']]
        )->assoc();
        if (!empty($res)) {
            foreach ($res as $lentaSet) {
                $val[] = [(int)$lentaSet['frend'], (int)$user['id'], (int)$status['id_user'], 'status_like', time(), (int)$status['id']];
            }
            $db->query(
                "INSERT INTO `tape` (`id_user`,`ot_kogo`,  `avtor`, `type`, `time`, `id_file`) VALUES ?v",
                [$val]
            );
        }
        header('Location: ?page=' . $page);
        exit;
    }
}
$set['title'] = 'Лента';
require_once H . 'sys/inc/thead.php';
// Очищение списка непрочитанных
if (isset($input_get['read']) && $input_get['read'] == 'all') {
    if (isset($user)) {
        $db->query(
            "UPDATE `tape` SET `read`=? WHERE `id_user`=?i",
            ['1', $user['id']]
        );
        $_SESSION['message'] = 'Список непрочитанных очищен';
        header('Location: ?page=' . $page);
        exit;
    }
}
// Полная очистка ленты
if (isset($input_get['delete']) && $input_get['delete'] == 'all') {
    if (isset($user)) {
        $db->query(
            "DELETE FROM `tape` WHERE `id_user`=?i",
            [$user['id']]
        );
        $db->query('OPTIMIZE TABLE `tape`;');
        $_SESSION['message'] = 'Лента успешно очищена';
        header("Location: ?");
        exit;
    }
}

title();
err();
aut();

$cnt = $db->query(
    'SELECT (
SELECT COUNT( * ) FROM `tape`  WHERE `id_user`=?i) k_post, (
SELECT COUNT( * ) FROM `notification` WHERE `id_user`=?i AND `read`="0") notify, (
SELECT COUNT( * ) FROM `discussions` WHERE `id_user`=?i AND `count`>"0") discus, (
SELECT COUNT( * ) FROM `tape` WHERE `id_user`=?i AND `read`="0") tape',
    [$user['id'], $user['id'], $user['id'], $user['id']]
)->row();

if ($cnt['notify']) {
    $cnt['notify'] = '<span class="off">(' . $cnt['notify'] . ')</span>';
} else {
    $cnt['notify'] = null;
}

if ($cnt['discus']) {
    $cnt['discus'] = '<span class="off">(' . $cnt['discus'] . ')</span>';
} else {
    $cnt['discus'] = null;
}

if ($cnt['tape']) {
    $cnt['tape'] = '<span class="off">(' . $cnt['tape'] . ')</span>';
} else {
    $cnt['tape'] = null;
}
?>
<!-- ./ lenta -->
<div id="comments" class="menus">
    <div class="webmenu">
        <a href="/user/tape/" class="activ">Лента <?php echo $cnt['tape'];?></a>
    </div>
    <div class="webmenu">
        <a href="/user/discussions/" >Обсуждения  <?php echo $cnt['discus'];?></a>
    </div>
    <div class="webmenu">
        <a href="/user/notification/">Уведомления <?php echo $cnt['notify'];?></a>
    </div>
</div>
<div class="foot">
    <a href="?page=<?php echo $page;?>&amp;read=all"><img src="/style/icons/ok.gif"> Отметить всё как прочитанное</a>
</div><?php

if (!$cnt['k_post']) {
    ?>
<div class="mess">
    Нет новых событий
</div><?php
} else {
        $k_page = k_page($cnt['k_post'], $set['p_str']);
        $page = page($k_page);
        $start = $set['p_str'] * $page - $set['p_str'];
        $q = $db->query(
            "SELECT tp.*, u.nick, u.pol, st.msg AS msg_status, glr.id AS id_gallery, glr.`name` AS name_gallery,
glf.id AS id_foto, glf.`name` AS name_foto, glf.id_gallery AS id_gallery_foto,
glfa.id AS id_foto_avatar, glfa.`name` AS name_foto_avatar, glfa.id_gallery AS id_gallery_avatar,
n.id AS id_note, n.`name` AS name_note, n.msg AS msg_note,
them.id AS id_them, them.id_razdel, them.id_forum, them.name AS name_them, them.text,
uf.`name` AS user_dir_name, (
SELECT COUNT( * ) FROM `status_komm` WHERE `id_status`=tp.id_file AND (tp.`type`='status' OR tp.`type`='status_like')) komm_status, (
SELECT COUNT( * ) FROM `status_like` WHERE `id_status`=tp.id_file AND (tp.`type`='status' OR tp.`type`='status_like')) like_status, (
SELECT COUNT( * ) FROM `status_like` WHERE `id_status`=tp.id_file AND (tp.`type`='status' OR tp.`type`='status_like') AND `id_user`=?i) user_like_status, (
SELECT COUNT( * ) FROM `gallery_komm` WHERE `id_foto`=glf.id) cnt_komm_foto, (
SELECT COUNT( * ) FROM `notes_komm` WHERE `id_notes`=n.id) komm_note
FROM `tape` tp
JOIN `user` u ON u.id=tp.avtor
LEFT JOIN `status` st ON (st.id=tp.id_file AND (tp.`type`='status' OR tp.`type`='status_like'))
LEFT JOIN `gallery` glr ON (glr.id=tp.id_file AND tp.`type`='album')
LEFT JOIN `gallery_foto` glf ON (glf.id=tp.id_file AND tp.`type`='avatar')
LEFT JOIN `gallery_foto` glfa ON glfa.id=tp.avatar
LEFT JOIN `notes` n ON (n.id=tp.id_file AND tp.`type`='notes')
LEFT JOIN `forum_t` them ON (them.id=tp.id_file AND tp.`type`='them')
LEFT JOIN `user_files` uf ON (uf.id=tp.id_file AND tp.`type`='obmen')
WHERE tp.`id_user`=?i ORDER BY `read`, `time` DESC LIMIT ?i, ?i",
            [$user['id'], $user['id'], $start, $set['p_str']]
        );

    while ($post = $q->row()) {
        $type = $post['type'];
        $avtor = ['id' => $post['avtor'], 'nick' => $post['nick'], 'pol' => $post['pol']];
        $name = null;

        if ($post['read'] == 0) {
            $s1 = '<span class="off">&nbsp;';
            $s2 = '</span>';
            $read_list[] = $post['id'];
        } else {
            $s1 = '&nbsp;';
            $s2 = '&nbsp;' . "\n";
        }
        // Помечаем сообщение прочитанным
        $d = opendir('inc/');
        while ($dname = readdir($d)) {
            if ($dname != '.' && $dname != '..') {
                include 'inc/' . $dname;
            }
        } ?>
</div><?php
    }
    if ($k_page > 1) {
        str('?', $k_page, $page);
    }
    if (!empty($read_list)) {
        $db->query(
            'UPDATE `tape` SET `read`="1" WHERE `id` IN (?li)',
            [$read_list]
        );
    }
}
?>

<div class="foot">
    <a href="?page=<?php echo $page;?>&amp;delete=all"><img src="/style/icons/delete.gif"> Очистить ленту</a>
</div>
<div class="foot">
    <img src="/style/icons/str2.gif" alt="*"> <a href="/info.php?id=<?php echo $user['id'];?>"><?php echo $user['nick'];?></a> | <strong>Лента</strong>
</div>
<!-- ./ end lenta -->
<?php

require_once H . 'sys/inc/tfoot.php';
