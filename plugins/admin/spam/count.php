<?php
if ($user['group_access']==2) {
    $types = " WHERE `types` = 'chat' ";
} elseif ($user['group_access']==3) {
    $types =" WHERE `types` = 'forum' ";
} elseif ($user['group_access']==4) {
    $types = " WHERE (`types` = 'obmen_komm' OR `types` = 'files_komm') ";
} elseif ($user['group_access']==5) {
    $types = " WHERE `types` = 'lib_komm' ";
} elseif ($user['group_access']==6) {
    $types = " WHERE `types` = 'foto_komm' ";
} elseif ($user['group_access']==11) {
    $types = " WHERE `types` = 'notes_komm' ";
} elseif ($user['group_access']==12) {
    $types = " WHERE `types` = 'guest' ";
} elseif (($user['group_access']>6 && $user['group_access']<10) || $user['group_access']==15) {
    $types = null;
}
$k_p=$db->query("SELECT COUNT(*) FROM `spamus` $types")->el();
echo "($k_p)";
