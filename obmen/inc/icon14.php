<?php
if (is_file(H.'style/themes/'.$set['set_them'].'/loads/14/'.$ras.'.png')) {
    echo "<img src='/style/themes/$set[set_them]/loads/14/$ras.png' alt='$ras' title='Расширение файла $ras'/>\n";
} else {
        echo "<img src='/style/themes/$set[set_them]/loads/14/file.png' alt='file' title='Неизвестное расширение'/>\n";
}
