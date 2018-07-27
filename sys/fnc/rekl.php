<?php
function rekl($sel)
{
    global $set;
    
    // для страниц, кроме главной, у нас другая позиция
    if ($sel == 3 && $_SERVER['PHP_SELF'] != '/index.php') {
        $sel = 4;
    }
    $q = go\DB\query(
        "SELECT * FROM `rekl` WHERE `sel` = ?string AND `time_last` > ?i ORDER BY id ASC",
                     [$sel, time()]
    );
    while ($post = $q->row()) {
        if ($sel == 2) {
            echo icons('rekl.png', 'code');
        }
        if ($post['dop_str'] == 1) {
            echo "\t".'<p><a' . ($set['web'] ? ' target="_blank"' : null) . ' href="http://' . $_SERVER['SERVER_NAME'] . '/go.php?go=' . $post['id'] . '">';
        } else {
            echo '<p><a' . ($set['web'] ? ' target="_blank"' : null) . ' href="' . $post['link'] . '">';
        }
        if ($post['img'] == null) {
            echo $post['name'];
        } else {
            echo '<img src="' . $post['img'] . '" alt="' . $post['name'] . '" />';
        }
        echo '</a></p>'."\n";
    }
}
