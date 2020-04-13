<?php


namespace App\Helper;


class NumericHelper
{
    public static function isZero(float $value): bool
    {
        $epsilon = 0.00001;
        return abs(0.0 - $value) < $epsilon;
    }

    public static function isFloat(string $value): bool
    {
        return preg_match("/^\\d+\\.\\d+$/", $value) === 1;
    }
}