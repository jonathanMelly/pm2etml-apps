<?php

namespace App\Constants;

class RemediationStatus
{
    public const REFUSED_BY_CLIENT = -1;
    public const NONE = 0;
    public const ASKED_BY_WORKER = 1;
    public const CONFIRMED_BY_CLIENT = 2;
    public const EVALUATED = 3;
}
