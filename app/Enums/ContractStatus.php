<?php

namespace App\Enums;

enum ContractStatus:int
{
    case CANCELLED   = 0;
    case BLOCKED     = 1;

    case REGISTERED  = 5;
    case IN_PROGRESS = 6;
    case DELIVERED   = 7;
    case EVALUATED   = 8;

}
