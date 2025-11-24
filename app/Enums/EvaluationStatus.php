<?php

namespace App\Enums;

enum EvaluationStatus: string
{
    case NON_ACQUIS = 'na';
    case PARTIELLEMENT_ACQUIS = 'pa';
    case ACQUIS = 'a';
    case LARGEMENT_ACQUIS = 'la';
}
