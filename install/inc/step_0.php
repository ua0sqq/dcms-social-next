<?
$set['title']='Мастер установки';
include_once 'inc/head.php'; // верхняя часть темы оформления

	if(isset($_GET['yes']) && $_GET['step']=='1')
	{
		$_SESSION['install_step']++;
		header("Location: index.php?$passgen&".SID);
		exit;
	}

	if (isset($_GET['no']))
		echo '<div class="err">Необходимо согласиться с условием</div>';
		
echo "<form method='post' action='?".passgen()."'>";
echo "<input type='submit' name='refresh' value='Обновить' />";
echo "</form>";

?>
		<center>				<div class="mess">
		<b>Добро пожаловать в мастер установки DCMS-Social!</b><br />
		</div>				<div class="nav2">
		<font color='green'>Текущая версия: DCMS-Social v<?echo $set['dcms_version'];?> beta</font>		</div>		<div class="mess">
		<font color='red'>Перед началом установки, рекомендуем проверить наличие более новой версии на оф. сайте <a href="http://dcms-social.ru">Dcms-Social.Ru</a></font>
		</div>				</center>		<div class="nav2">
	-	Официальный сайт поддержки DCMS-Social - <a target="_blank" href="http://dcms-social.ru">Dcms-Social.Ru</a><br />
	-  Наиболее безопасным источником для скачивания движка считается  вышеуказанный официальный сайт<br />
	-   Об ошибках и недоработках просьба сообщать на <a target="_blank" href="http://dcms-social.ru/forum/">форум</a><br />
     - Запрещено снимать копирайт движка (ссылка внизу на Dcms-Social.ru) без наличия <a target="_blank" href="http://dcms-social.ru/plugins/rules/post.php?id=2">лицензии</a><br />

	</div>		<hr />	Для продолжения пользования данной версией нужно согласиться со следущим условием:<br /> <b>Обязуетесь ли вы не снимать копирайт без покупки лицензии?</b><br />
<?

echo "<form method='get' action='index.php'>\n";
echo "<input name='step' value='".($_SESSION['install_step']+1)."' type='hidden' />\n";
echo "<input name='gen' value='$passgen' type='hidden' />\n";
echo "<input value='Да, обязуюсь' name='yes' type='submit' />\n";
echo "<input value='Нет, до свидания' name='no' type=\"submit\" /><br />\n";
echo "</form>\n";

echo "<hr />\n";
echo "<b>Шаг: $_SESSION[install_step]</b>\n";
include_once 'inc/foot.php'; // нижняя часть темы оформления
?>