<?php

namespace App\Constants;

enum AssessmentTiming: int
{
    case AUTO80 = 0;
    case EVAL80 = 1;
    case AUTO100 = 2;
    case EVAL100 = 3;

    public function label(): string
    {
        return match ($this) {
            self::AUTO80 => 'auto80',
            self::AUTO100 => 'auto100',
            self::EVAL80 => 'eval80',
            self::EVAL100 => 'eval100',
        };
    }
}
