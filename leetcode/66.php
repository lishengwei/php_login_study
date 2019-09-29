<?php

$array = [1, 2, 3];

var_dump(plusOne($array));

function plusOne($digits)
{
    $i       = 0;
    $lastKey = count($digits) - 1;
    $value   = $digits[$lastKey] + 1;
    if ($value > 9) {
        $i     = 1;
        $value = 0;
    }
    $digits[$lastKey] = $value;
    while ($i > 0) {
        $lastKey--;
        if ($lastKey >= 0) {
            $value = $digits[$lastKey] + $i;
            if ($value > 9) {
                $i     = 1;
                $value = 0;
            } else {
                $i = 0;
            }
            $digits[$lastKey] = $value;
        } else {
            array_unshift($digits, $i);
            break;
        }
    }
    return $digits;
}