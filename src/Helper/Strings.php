<?php

namespace App\Helper;

class Strings
{

    public static function str_contains_any(string $haystack, array $needles): bool
    {
        return array_reduce($needles, static fn($a, $n) => $a || str_contains($haystack, $n), false);
    }


}
