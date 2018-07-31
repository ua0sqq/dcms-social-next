<?php

include_once '../../sys/inc/start.php';
include_once H . 'sys/inc/compress.php';
include_once H . 'sys/inc/sess.php';
include_once H . 'sys/inc/settings.php';
include_once H . 'sys/inc/db_connect.php';
include_once H . 'sys/inc/ipua.php';
include_once H . 'sys/inc/fnc.php';
include_once H . 'sys/inc/user.php';

only_reg();

$get_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($get_id) {
    $ank = get_user($get_id);
} else {
    $ank['id'] = $user['id'];
}

if (!$ank || $user['id'] == $ank['id']) {
    header('Location: /index.php?' . SID);
    exit;
}
if (isset($_GET['act']) && $_POST['money']) {
    $money = abs(intval($_POST['money']));
    if ($user['money'] < $money) {
        $err = 'У вас не достаточно средств для перевода';
    }
    if (!isset($err)) {
        $db->query("UPDATE `user` SET `money`=`money`+?i WHERE `id`=?i",
                   [$money, $ank['id']]);
        $db->query("UPDATE `user` SET `money`=`money`-?i WHERE `id`=?i",
                   [$money, $user['id']]);
        $msg = 'Пользователь [b]' . $user['nick'] . '[/b] перевeл вам средства в колличестве [b] ' . $money . ' [/b] ' . sclon_value($money, 'монеты', 'монет', 'монет') . '! [br]Не забудьте сказать спасибо!';
        $db->query("INSERT INTO `mail` (`id_user`, `id_kont`, `msg`, `time`) VALUES(?i, ?i, ?, ?i)",
                   [0, $ank['id'], $msg, time()]);
        
        $_SESSION['message'] = 'Перевод успешно выполнен';
        header('Location: /info.php?id=' . $ank['id']);
        exit;
    }
}

$set['title'] = 'Перевод монет'; // заголовок страницы
include_once H . 'sys/inc/thead.php';
title();
aut();
err();

?>
<div class="foot">
    <img src="/style/icons/str2.gif" alt=""> <a href="/info.php?id=<?php echo $ank['id'];?>"><?php echo $ank['nick'];?></a> | Перевод
</div><?php

if ($user['money'] <= 1) {
?>
<div class="mess"><?php

    if ($user['pol'] == 0) {
?>
    <p>Извини <strong>красавица,</strong><?php

    } else {
?>
    Извини <strong>братан,</strong><?php

    }
?>
    но чтобы переводить монеты другим обитателям необходимо набрать минимум <strong>2</strong> монеты</p>
    <p>У вас <strong><?php echo $user['money'];?> </strong><?php echo sclon_value($user['money'], 'монета', 'монеты', 'монет');?>
</div><?php

} else {
?>
<div class="mess">
    У вас: <strong><?php echo $user['money'];?></strong> <?php echo sclon_value($user['money'], 'монета', 'монеты', 'монет');?>
</div>
<form class="main" action="?id=<?php echo $ank['id'];?>&amp;act" method="post">
    <p>Количество монет:
    <p><input type="text" name="money" value="1" />
    <p><input class="submit" type="submit" value="Перевести" /></p>
</form><?php

}
?>
<div class="foot">
    <img src="/style/icons/str2.gif" alt=""> <a href="/info.php?id=<?php echo $ank['id'];?>"><?php echo $ank['nick'];?></a> | Перевод
</div><?php

include_once H . 'sys/inc/tfoot.php';
