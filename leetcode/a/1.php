<?php
$nums = [1,2,3,3,0,0,0];

var_dump(removeDuplicates($nums));
var_dump($nums);

function removeDuplicates(&$nums) {
    $i = 0;
    while(true) {
        if (!isset($nums[$i + 1])) {
            break;
        }
        if ($nums[$i + 1] == $nums[$i]) {
            unset($nums[$i]);
        }
        $i++;
    }
    return count($nums);
}

function removeDuplicates2(&$nums) {
    $i = 0;
    while(true) {
        if (!isset($nums[$i + 1])) {
            break;
        }
        if ($nums[$i + 1] == $nums[$i]) {
            unset($nums[$i]);
        }
        $i++;
    }
    return count($nums);
}