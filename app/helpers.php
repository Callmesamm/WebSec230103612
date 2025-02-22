<?php

if (!function_exists('isPrime')) {
    function isPrime($number)
    {
        if($number<=1) return false;
        $l = $number - 1;
        while($l>1) {
            if($number%$l==0) return false;
            $l--;
        }
        return true;
    }
}
