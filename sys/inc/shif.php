<?php

function shif($string)
{
    global $set;
    $key=$set['shif'];
    $string1=md5((string)$string);
    $string2=md5($key);
    return md5($key.$string1.$string2.$key);
}

function cookie_encrypt($string, $id = 0)
{
    $ks = openssl_cipher_iv_length($method = 'AES-256-CBC');
    $key = substr(md5($id.@$_SERVER['HTTP_USER_AGENT']), 0, $ks);
    if (!$iv = @file_get_contents(H . 'sys/dat/shif_iv.dat')) {
        $iv = openssl_random_pseudo_bytes($ks);
        file_put_contents(H . 'sys/dat/shif_iv.dat', base64_encode($iv));
        chmod(H . 'sys/dat/shif_iv.dat', 0644);
    }
    $string = openssl_encrypt($string, $method, $key, $options=OPENSSL_RAW_DATA, base64_decode($iv));
    $string = base64_encode($string);
    return $string;
}

function cookie_decrypt($string, $id=0)
{
    $string=base64_decode($string);

    $ks = openssl_cipher_iv_length($method = 'AES-256-CBC');
    $key = substr(md5($id.@$_SERVER['HTTP_USER_AGENT']), 0, $ks);
    if (!$iv = file_get_contents(H . 'sys/dat/shif_iv.dat')) {
        $iv = openssl_random_pseudo_bytes($ks);
        file_put_contents(H . 'sys/dat/shif_iv.dat', base64_encode($iv));
        chmod(H . 'sys/dat/shif_iv.dat', 0644);
    }
    $string = openssl_decrypt($string, $method, $key, $options=OPENSSL_RAW_DATA, base64_decode($iv));
    return $string;
}
