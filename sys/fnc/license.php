<?php
if (!function_exists('copyright')) {
    function copyright($fiera)
    {
        return preg_replace("#(\n|\r)*</body>#i", "<div class='license'>&copy; <a  target='_blank' title='Модификация движка Dcms' href='//dcms-social.ru'>DCMS-Social</a> </div>\n</body>", $fiera);
    }
    ob_start("copyright");
}
