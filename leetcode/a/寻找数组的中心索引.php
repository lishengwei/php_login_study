<?php

$nums   = [1, 7, 3, 6, 5, 6];
$result = pivotIndex($nums);
var_dump($result);

$nums   = [-1, -1, -1, -1, -1, 0];
$result = pivotIndex($nums);
var_dump($result);

function pivotIndex($nums)
{
    $sum      = 0;
    $leftSums = [];
    foreach ($nums as $k => $v) {
        if ($k == 0) {
            $leftSums[0] = 0;
        }
        $sum          += $v;
        $leftSums[$k + 1] = $sum;
    }
    foreach ($nums as $i => $v1) {
        $left  = $leftSums[$i];
        $right = $sum - $left - $nums[$i];
        if ($left == $right) {
            return $i;
        }
    }
    return -1;
}