<?php
if (isset($_SESSION['refer']) && $_SESSION['refer'] != null
	&& !preg_match('#(rules)|(smiles)|(secure)|(aut)|(reg)|(umenu)|(zakl)|(mail)|(anketa)|(settings)|(avatar)|(info)\.php#', $_SERVER['SCRIPT_NAME'])) {
    $_SESSION['refer'] = null;
}
function otkuda($ref)
{
    if (preg_match('#^/forum/#', $ref)) {
        $mesto = ' <a href="/forum/">сидит в форуме</a> ';
    } elseif (preg_match('#^/chat/#', $ref)) {
        $mesto = ' <a href="/chat/">сидит в чате</a> ';
    } elseif (preg_match('#^/news/#', $ref)) {
        $mesto = ' <a href="/news/">читает новости</a> ';
    } elseif (preg_match('#^/guest/#', $ref)) {
        $mesto = ' <a href="/guest/">пишет в гостевой</a> ';
    } elseif (preg_match('#^/user/users\.php#', $ref)) {
        $mesto = ' <a href="/user/users.php">cмотрит обитателей</a> ';
    } elseif (preg_match('#^/online\.php#', $ref)) {
        $mesto = ' <a href="/online.php">cмотрит кто онлайн</a> ';
    } elseif (preg_match('#^/online_g\.php#', $ref)) {
        $mesto = ' <a href="/online_g.php">cмотрит кто в гостях</a> ';
    } elseif (preg_match('#^/reg\.php#', $ref)) {
        $mesto = ' <a href="/reg.php">хочет зарегистрироваться</a> ';
    } elseif (preg_match('#^/obmen/#', $ref)) {
        $mesto = ' <a href="/obmen/">cидит в зоне обмена</a> ';
    } elseif (preg_match('#^/aut\.php#', $ref)) {
        $mesto = ' <a href="/aut.php">хочет авторизоваться</a> ';
    } elseif (preg_match('#^/index\.php#', $ref)) {
        $mesto = ' <a href="/index.php">на главной</a> ';
    } elseif (preg_match('#^/\??$#', $ref)) {
        $mesto = ' <a href="/index.php">на главной</a> ';
    } else {
        $mesto = ' <a href="/index.php">где то на сайте</a> ';
    }
    return $mesto;
}
