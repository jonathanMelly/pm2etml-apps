<?php

namespace App\Exports;

enum EvaluationResult: string
{
    case NON_ACQUIS = "na";
    case PARTIELLEMENT_ACQUIS = "pa";
    case ACQUIS = "a";
    case LARGEMENT_ACQUIS = "la";

    function isSuccess(): bool
    {
        return $this === EvaluationResult::ACQUIS || $this === EvaluationResult::LARGEMENT_ACQUIS;
    }

}
