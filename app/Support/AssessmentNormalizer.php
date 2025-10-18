<?php

namespace App\Support;

use App\Constants\AssessmentTiming;
use App\Constants\AssessmentState;

class AssessmentNormalizer
{
    /**
     * Normalise un libellé libre de timing vers une valeur string de AssessmentTiming.
     * Retourne null si non reconnu.
     */
    public static function normalizeTimingToTiming(?string $raw): ?string
    {
        if (!$raw) {
            return null;
        }

        $k = strtolower(str_replace(['-', ' '], '_', $raw));

        $map = [
            // anciens identifiants
            'auto_formative' => AssessmentTiming::AUTO_FORMATIVE,
            'autoformative'  => AssessmentTiming::AUTO_FORMATIVE,
            'eval_formative' => AssessmentTiming::EVAL_FORMATIVE,
            'evalformative'  => AssessmentTiming::EVAL_FORMATIVE,
            'auto_finale'    => AssessmentTiming::AUTO_FINALE,
            'autofinale'     => AssessmentTiming::AUTO_FINALE,
            'eval_finale'    => AssessmentTiming::EVAL_FINALE,
            'evalfinale'     => AssessmentTiming::EVAL_FINALE,
            // nouveaux identifiants
            'a_formative1'   => AssessmentTiming::AUTO_FORMATIVE,
            'aformative1'    => AssessmentTiming::AUTO_FORMATIVE,
            'e_formative1'   => AssessmentTiming::EVAL_FORMATIVE,
            'eformative1'    => AssessmentTiming::EVAL_FORMATIVE,
            'a_formative2'   => AssessmentTiming::AUTO_FINALE,
            'aformative2'    => AssessmentTiming::AUTO_FINALE,
            'e_sommative'    => AssessmentTiming::EVAL_FINALE,
            'esommative'     => AssessmentTiming::EVAL_FINALE,
        ];

        if (isset($map[$k])) {
            return $map[$k];
        }

        // Si on reçoit déjà la valeur camelCase exacte d'un timing
        if (in_array($raw, AssessmentTiming::all(), true)) {
            return $raw;
        }

        return null;
    }

    /**
     * Normalise un libellé libre de timing vers l'enum AssessmentState.
     * Retourne null si non reconnu.
     */
    public static function normalizeTimingToState(?string $raw): ?AssessmentState
    {
        if (!$raw) {
            return null;
        }

        $k = strtolower(str_replace(['-', ' '], '_', $raw));

        return match ($k) {
            // anciens identifiants
            'auto_formative', 'autoformative' => AssessmentState::AUTO_FORMATIVE,
            'eval_formative', 'evalformative' => AssessmentState::EVAL_FORMATIVE,
            'auto_finale', 'autofinale'       => AssessmentState::AUTO_FINALE,
            'eval_finale', 'evalfinale'       => AssessmentState::EVAL_FINALE,
            // nouveaux identifiants
            'a_formative1', 'aformative1'     => AssessmentState::AUTO_FORMATIVE,
            'e_formative1', 'eformative1'     => AssessmentState::EVAL_FORMATIVE,
            'a_formative2', 'aformative2'     => AssessmentState::AUTO_FINALE,
            'e_sommative',  'esommative'      => AssessmentState::EVAL_FINALE,
            'not_started', 'not_evaluated'    => AssessmentState::NOT_EVALUATED,
            default => AssessmentState::tryFrom($raw),
        };
    }
}

