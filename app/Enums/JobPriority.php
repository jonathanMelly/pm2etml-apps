<?php

namespace App\Enums;

enum JobPriority:int
{
    case MANDATORY          = 0;
    case HIGHLY_RECOMMENDED = 1;
    case RECOMMENDED        = 2;
    case BEYOND             = 3;
}
