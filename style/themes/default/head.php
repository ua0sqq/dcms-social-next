<?php
$set['web'] = false;
header("Content-type: text/html");

?>
<!DOCTYPE html>
<html lang="ru">
<head>
<title>
<?=$set['title']?>
</title>
<meta name="viewport" content="initial-scale = 1.0,maximum-scale = 1.0" />
<link rel="stylesheet" href="/style/themes/<?=$set['set_them']?>/style.css" type="text/css" />
</head>
<body>
<div class="body">
<?php
if (isset($_SESSION['message'])) {
    echo '<div class="msg">' . $_SESSION['message'] . '</div>';
    $_SESSION['message'] = null;
}
if ($_SERVER['PHP_SELF'] == '/index.php') {
    ?>
	<div style="text-align: center;" class="logo">
	<img src="/style/themes/<?=$set['set_them']?>/logo.png" alt="logo" /><br />
	<?=$set['title']?>
	</div>
	<?php
}
?>