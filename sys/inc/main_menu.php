<?php
$q_menu = $db->query("SELECT * FROM `menu` ORDER BY `pos` ASC");
while ($post_menu = $q_menu->row()) {
    if ($post_menu['type'] == 'link') {
        echo "\n".'<div class="main_menu">'."\n\t";
    }
    if ($post_menu['type'] == 'link') {
        echo '<img src="/style/icons/' . $post_menu['icon'] . '" alt="*" /> ';
    }
    if ($post_menu['type'] == 'link') {
        echo '<a href="' . $post_menu['url'] . '">';
    } else {
        echo "\n".'<div class="menu_razd">'."\n\t";
    }
    
    echo $post_menu['name'];
    
    if ($post_menu['type'] == 'link') {
        echo '</a> ';
    }
    
    if ($post_menu['counter'] != null && is_file(H . $post_menu['counter'])) {
        include H . $post_menu['counter'];
    }
    echo "\n".'</div>';
}
if (user_access('adm_panel_show')) {
    ?>
<div class="main_menu">
	<img src="/style/icons/adm.gif" alt="DS" /> <a href="/plugins/admin/">Админ кабинет</a><?php
    include_once H.'plugins/admin/count.php'; ?>
</div>
	<?php
}
?>