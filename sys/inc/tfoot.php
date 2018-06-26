<?php
if (file_exists(H."style/themes/$set[set_them]/foot.php")) {
    include_once H."style/themes/$set[set_them]/foot.php";
} else {
    list($msec, $sec) = explode(chr(32), microtime());
    echo '<div class="foot">' . "\n";
    $cnt = $db->query(
        'SELECT * FROM (
					  SELECT COUNT(*) all_user FROM `user`)q, (
					  SELECT COUNT(*) online FROM `user` WHERE `date_last` > ?i)q2, (
					  SELECT COUNT(*) guest FROM `guests` WHERE `date_last` > ?i AND `pereh` > 0',
                      [time()-600, time()-600]
    )->row();
    echo '<a href="/">На главную</a><br />' . "\n";
    echo '<a href="/user/users.php">Регистраций: ' . $cnt['all_user'] . '</a><br />' . "\n";
    echo '<a href="/online.php">Сейчас на сайте: ' . $cnt['online'] . '</a><br />' . "\n";
    echo '<a href="/online_g.php">Гостей на сайте: ' . $cnt['guest'] . '</a><br />' . "\n";
    $page_size = ob_get_length();
    ob_end_flush();
    if (!isset($_SESSION['traf'])) {
        $_SESSION['traf'] = 0;
    }
    $_SESSION['traf'] += $page_size;
    echo "\n"	.
    'Вес страницы: '.round($page_size / 1024, 2).' Кб<br />' . "\n" .
    'Ваш трафик: '.round($_SESSION['traf'] / 1024, 2).' Кб <br />' . "\n" .
    'Генерация страницы: '.round(($sec + $msec) - $conf['headtime'], 3).'сек' . "\n";
    echo '</div>' . "\n";
    echo '<div class="rekl">' . "\n";
    rekl(3);
    echo '</div>' . "\n";
    echo '</div>' . "\n" . '</body>' . "\n" . '</html>';
}
exit;
