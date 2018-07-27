</td></tr>
</table>
</td></tr>
</table></div></div>
<?php
rekl(3);
$cnt_online = $db->query('SELECT (
SELECT COUNT( * ) FROM `user`) all_user, (
SELECT COUNT( * ) FROM `user` WHERE `date_last`>?i) online, (
SELECT COUNT( * ) FROM `guests` WHERE `date_last`>?i AND `pereh`>0) guests',
            [(time() - 600), (time() - 600)])->row();
?>
<nav id="footer" style="padding: 0;">
    <ul class="gorisontal">
        <li><a href="/user/users.php">Зарегистрировано (<?php echo $cnt_online['all_user'];?>)</a></li>
        <li><a href="/online.php">Онлайн (<?php echo $cnt_online['online'];?>)</a></li>
        <li><a href="/online_g.php">Гостей (<?php echo $cnt_online['guests'];?>)</a></li>
        <li><a href="/?t=wap">Wap версия </a></li>
        <li><a href="/index.php"><span style="text-transform: capitalize;">&copy; <?php echo htmlspecialchars($_SERVER['HTTP_HOST']);?> - <?php echo date('Y');?> г.</span></a></li>
        <?php
list($msec, $sec) = explode(chr(32), microtime());
$page_size = ob_get_length();
ob_end_flush();
if (!isset($_SESSION['traf'])) {
    $_SESSION['traf'] = 0;
}
$_SESSION['traf'] += $page_size;
?><li><a href="//dcms-social.ru/"><span style="color:white;">DCMS-Social <?php
echo (isset($user) && $user['group_access'] > 6 ? 'Sql: ' . $db->query_number ?: '' : '')?></span></a></li>                    
    </ul>
</nav>
</body>
</html><?php
exit;                    

?>