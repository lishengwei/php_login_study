<?php

$nums1 = [1, 2, 3, 0, 0, 0];
$m     = 3;
$nums2 = [2, 5, 6];
$n     = 3;

merge($nums1, $m, $nums2, $n);

var_dump($nums1);

function merge(&$nums1, $m, $nums2, $n)
{
    $len1 = $m - 1;
    $len2 = $n - 1;
    $len  = $m + $n - 1;
    while ($len1 >= 0 || $len2 >= 0) {
        if ($len1 >= 0 && $len2 >= 0) {
            $nums1[$len] = $nums1[$len1] > $nums2[$len2] ? $nums1[$len1--] : $nums2[$len2--];
        } else if ($len2 >= 0) {
            $nums1[$len] = $nums2[$len2];
            $len2--;
        } else {
            break;
        }
        $len--;
    }
}