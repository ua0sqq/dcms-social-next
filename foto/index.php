<?php
include_once '../sys/inc/start.php';
include_once '../sys/inc/compress.php';
include_once '../sys/inc/sess.php';
include_once '../sys/inc/home.php';
include_once '../sys/inc/settings.php';
include_once '../sys/inc/db_connect.php';
include_once '../sys/inc/ipua.php';
include_once '../sys/inc/fnc.php';
include_once '../sys/inc/user.php';
$args = [
        'act' => FILTER_DEFAULT,
        'edit' => FILTER_DEFAULT,
        'ok' => FILTER_DEFAULT,
        'acth' => FILTER_DEFAULT,
        'avatar' => FILTER_DEFAULT,
        'id' =>  [
                     'filter' => FILTER_VALIDATE_INT,
                     'options' => [
                                   'default' => 0,
                                   'min_range' => 1
                                   ]
                     ],
        'fav' =>  [
                     'filter' => FILTER_VALIDATE_INT,
                     'options' => [
                                   'default' => 0,
                                   'max_range' => 1
                                   ]
                     ],
        'id_gallery' =>  [
                     'filter' => FILTER_VALIDATE_INT,
                     'options' => [
                                   'default' => 0,
                                   'min_range' => 1
                                   ]
                     ],
        'id_foto' => [
                     'filter' => FILTER_VALIDATE_INT,
                     'options' => [
                                   'default' => 0,
                                   'min_range' => 1
                                   ]
                     ],
        'id_user' => [
                     'filter' => FILTER_VALIDATE_INT,
                     'options' => [
                                   'default' => 0,
                                   'min_range' => 1
                                   ]
                     ],
        'size' =>  [
                     'filter' => FILTER_VALIDATE_INT,
                     'options' => [
                                   'default' => 0,
                                   'min_range' => 1
                                   ]
                     ],
        'delete' =>  [
                     'filter' => FILTER_VALIDATE_INT,
                     'options' => [
                                   'default' => 0,
                                   'min_range' => 1
                                   ]
                     ],
        'rating' => FILTER_VALIDATE_INT,
        'page' => FILTER_VALIDATE_INT
    ];
$input_get = filter_input_array(INPUT_GET, $args);
unset($args);
$input_post = filter_input_array(INPUT_POST, FILTER_DEFAULT);
$input_get['page'] = $input_get['page'] ? $input_get['page'] : 'end';

if (isset($input_get['acth']) && $input_get['acth'] == 'show_foto' && isset($input_get['id_gallery']) && isset($input_get['id_foto'])) {
    include_once 'inc/user_show_foto.php';
}
if (isset($input_get['acth']) && $input_get['acth'] == 'user_gallery' && isset($input_get['id_gallery'])) {
    include_once 'inc/user_gallery_show.php';
} elseif (isset($input_get['acth']) && $input_get['acth'] == 'user_gallery') {
    include_once 'inc/user_gallery.php';
} else {
    include_once 'inc/all_gallery.php';
}

include_once '../sys/inc/tfoot.php';
