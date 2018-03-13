<?php
header("Content-type: application/xhtml+xml;charset=UTF-8");

?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
<head>
<title><?echo $set['title'];?></title>
<link rel="stylesheet" href="/style/themes/<?php echo $set['set_them']; ?>/style.css" type="text/css" />
</head>
<body>
<div class="body">
<div class="logo"><img src="/style/themes/default/logo.png"  alt="Logotype" /><br />
DCMS Social - Движок социальной сети</div>
<div class="title">
<?php
echo $set['title']."\n";
ob_start();
?>
</div>