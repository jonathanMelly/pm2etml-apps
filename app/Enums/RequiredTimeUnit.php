<?php

namespace App\Enums;

enum RequiredTimeUnit: int
{
    case PERIOD = 45;
    case HOUR = 60;

    public static function Convert(int $time, RequiredTimeUnit $sourceUnit, RequiredTimeUnit $destinationUnit): float
    {
        if ($sourceUnit === $destinationUnit) {
            return $time;
        }

        return $time * $sourceUnit->value / $destinationUnit->value;

    }
}
