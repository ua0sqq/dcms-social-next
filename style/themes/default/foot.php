<?php
list($msec, $sec) = explode(chr(32), microtime());
if ($_SERVER['PHP_SELF'] != '/index.php') {
    ?>
	<div class="foot">
	<img src="/style/icons/icon_glavnaya.gif" alt="*" /> <a href="/index.php">На главную</a>
	</div>
<?php
}
?>
<div class="copy">
	&copy; <a href="http://dcms-social.ru"><?=text($_SERVER['HTTP_HOST'])?></a> - <?=date('Y');?> г.
</div>
<div class="foot">
	На сайте: <?php
$cnt = $db->query("SELECT * FROM (
				  SELECT COUNT( * ) on_user FROM `user` WHERE `date_last`>?i) q1, (
				  SELECT COUNT( * ) on_guest FROM `guests` WHERE `date_last`>?i AND `pereh`>?i) q2",
				  [(time()-600), (time()-600), 0])->row();
?><a href="/online.php"><?= $cnt['on_user'];?></a> &amp; <a href="/online_g.php"><?= $cnt['on_guest'];?></a> <?php
if (!$set['web']) {
    echo ' | <a href="/?t=web">Версия для компьютера</a>';
}
?>
</div>
<div class="rekl">
<?php
$page_size = ob_get_length();
ob_end_flush();
rekl(3);
?>
	<p style="text-align: center;">PGen: <?=round(($sec + $msec) - $conf['headtime'], 3)?>сек / sql: <?= $db->query_number ?: '';?></p>
</div>
</div>
</body>
</html>
<?php
exit;
?>