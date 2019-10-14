<?php
/**
 * 买卖股票的最佳时机 II
 * @param Integer[] $prices
 * @return Integer
 */

$a = [1,2,3,4,5];
var_dump(maxProfit($a));

function maxProfit($prices) {
    $length = count($prices);
    $profit = 0;
    for ($i = 1; $i < $length; $i++) {
        $temp = $prices[$i] - $prices[$i - 1];
        if ($temp > 0) {
            $profit += $temp;
        }
    }
    return $profit;
}