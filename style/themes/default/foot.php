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
<center>
&copy; <a href="http://dcms-social.ru" style="text-transform: capitalize;"><?=text($_SERVER['HTTP_HOST'])?></a> - <?=date('Y');?> г.
</center>
</div>
<div class="foot">
На сайте: 
<a href="/online.php"><?=$db->query("SELECT COUNT(*) FROM `user` WHERE `date_last` > ".(time()-600)."")->el()?></a> &amp; 
<a href="/online_g.php"><?=$db->query("SELECT COUNT(*) FROM `guests` WHERE `date_last` > ".(time()-600)." AND `pereh` > '0'")->el()?></a> 
<?php
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
<center>
PGen: <?=round(($sec + $msec) - $conf['headtime'], 3)?>сек / sql: <?= $db->query_number ?: ''; ?>
</center>
</div>
</div>
</body>
</html>
<?php
exit;
?>