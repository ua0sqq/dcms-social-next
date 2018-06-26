<?php

include_once '../../sys/inc/start.php';
include_once '../../sys/inc/compress.php';
include_once '../../sys/inc/sess.php';
include_once '../../sys/inc/home.php';
include_once '../../sys/inc/settings.php';
include_once '../../sys/inc/db_connect.php';
include_once '../../sys/inc/ipua.php';
include_once '../../sys/inc/fnc.php';
include_once '../../sys/inc/adm_check.php';
include_once '../../sys/inc/user.php';

only_reg('/aut.php');

$set['title']='Перевод баллов';
include_once '../../sys/inc/thead.php';
err();
aut();

$m = filter_input(INPUT_POST, 'title', FILTER_VALIDATE_INT);
if ($m) {
    if (in_array($m, range(1, 20))) {
        $money = $m*500;
    } elseif ($m > 20) {
        $money = $user['balls']*2;
        $err = 'Перевод баллов возможен не более чем 20 монет за одну операцию';
    }
    if (!$err) {
        if ($user['balls'] >= $money) {
            $db->query(
        "UPDATE `user` SET `balls`=`balls`-?i, `money`=`money`+?i WHERE `id`=?i",
               [$money, $m, $user['id']]);
            $_SESSION['message'] = 'Поздравляем, пополнение счета успешно произведен';
            header('Location: ?');
            exit;
        } else {
            $_SESSION['err'] = 'Недостаточно баллов для завершения операции';
        }
    }
}
?>
<div class="foot">
    <img src="/style/icons/str2.gif" alt=""> <a href="/info.php"><?php echo $user['nick'];?></a> | Обмен монет
</div>
<div class="mess">
    У вас <strong><?php echo $user['balls'];?></strong> баллов активности.
</div>
<div class="mess">
    <p>C помощью этого сервиса, ты сможешь перевести заработанные баллы активности в монеты</p>
    <p><strong>Курс на <?php echo date('m.d.y');?> по Москве: 1 монета &rArr; 500 баллов активности.</strong></p>
</div><?php

$ratio = (int)floor($user['balls']/500);
if ($ratio) {
    $ratio = ($ratio > 20 ? 20 : $ratio);
    $value = range(1, $ratio); ?>
<form class="main" method="post" action="/user/money/money.php">
    <p>Сумма:</p>
    <p><select name="title"><?php
    foreach ($value as $val) {
        echo '<option value="' . $val . '"><strong>' . $val . ' ' . sclon_value($val, 'монета', 'монеты', 'монет') . '</strong></option>' . "\n";
    } ?>
    </select></p>
    <p><input value="Получить" type="submit" /></p>
</form><?php
} else {
        ?>
<div class="err">
    Не достаточно баллов для совершения операции
</div><?php
    }
?>
<div class="foot">
    <img src="/style/icons/str2.gif" alt=""> <a href="/info.php"><?php echo $user['nick'];?></a> | Обмен монет
</div><?php

include_once '../../sys/inc/tfoot.php';
