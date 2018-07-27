<?php
$pattern = 'SELECT COUNT( * ) FROM `spamus` WHERE ?w';
if ($user['group_access']==2) {
    $where = ['types' => 'chat'];
} elseif ($user['group_access']==3) {
    $where = ['types' => 'forum'];
} elseif ($user['group_access']==15) {
    $where = ['types' => ['obmen_komm', 'files_komm']];
} elseif ($user['group_access']==5) {
    $where = ['types' => 'lib_komm'];
} elseif ($user['group_access']==6) {
    $where = ['types' => 'foto_komm'];
} elseif ($user['group_access']==11) {
    $where = ['types' => 'notes_komm'];
} elseif ($user['group_access']==12) {
    $where = ['types' => 'guest'];
} elseif (($user['group_access']>6 && $user['group_access']<10) || $user['group_access']==15) {
    $types = null;$where = 1;$pattern = 'SELECT COUNT( * ) FROM `spamus` WHERE ?i';
}
$k_p = $db->query($pattern, [$where])->el();
echo '(' . $k_p . ')';
