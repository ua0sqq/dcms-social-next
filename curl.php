<?php
$text = file('pass.php', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
file_put_contents('output.php',$text); 
var_dump($text);
