<?php
function copy_remote_file($url, $local_filename = false)
{
    $ssl_verify = (parse_url($url)['host'] == filter_input(INPUT_SERVER, 'HTTP_HOST', FILTER_DEFAULT) ? false : true);
    $ch = curl_init();
    $header[0] = 'Accept: text/xml,application/xml,application/xhtml+xml,';
    $header[0] .= 'text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5';
    $header[] = 'Cache-Control: max-age=0';
    $header[] = 'Connection: keep-alive';
    $header[] = 'Keep-Alive: 300';
    $header[] = 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7';
    $header[] = 'Accept-Language: en-us,en;q=0.5';
    $header[] = 'Pragma: ';
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT,
        'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0 Firefox/5.0');
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl_verify);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $ssl_verify);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    $data = curl_exec($ch);
    curl_close($ch);

    if ($data && $local_filename) {
        $fp = fopen($local_filename, 'wb');

        if ($fp) {
            fwrite($fp, $data);
            fclose($fp);
        } else {
            fclose($fp);
            return false;
        }
    } elseif ($data) {
        return $data;
    } else {
        return false;
    }

    return true;
}
?>