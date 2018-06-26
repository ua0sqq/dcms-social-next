<?php

/**
 * склонение слов от числа
 * @example echo sclon_value(1,'год','года','лет');// год
 * @param  int $num             число
 * @param  string $nominative      именительный
 * @param  string $genitive        родительный
 * @param  string $genitive_plural родительный мн. ч.
 * @return  string
 */
function sclon_value($num, $nominative, $genitive, $genitive_plural)
{
    if ($num !== null) {
        $num = abs($num) % 100;
        $num1 = $num % 10;
        if ($num > 10 && $num < 20) {
            return $genitive_plural;
        }
        if ($num1 > 1 && $num1 < 5) {
            return $genitive;
        }
        if ($num1 == 1) {
            return $nominative;
        }
        return $genitive_plural;
    } else {
        return null;
    }
}