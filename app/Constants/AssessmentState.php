<?php

namespace App\Constants;

enum AssessmentState: string
{
    case NOT_EVALUATED     = 'not_started';
    case AUTO_FORMATIVE    = 'autoFormative';
    case EVAL_FORMATIVE    = 'evalFormative';
    case AUTO_FINALE       = 'autoFinale';
    case EVAL_FINALE       = 'evalFinale';
    case PENDING_SIGNATURE = 'pending_signature';
    case COMPLETED         = 'completed';
}
