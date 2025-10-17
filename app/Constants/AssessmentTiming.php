<?php

namespace App\Constants;

class AssessmentTiming
{
    // Types d’évaluations
    public const AUTO_FORMATIVE = 'aFormative1';
    public const EVAL_FORMATIVE = 'eFormative1';
    public const AUTO_FINALE    = 'aFormative2';
    public const EVAL_FINALE    = 'eSommative';

    // Labels d’appréciation (notation qualitative)
    public const APPRECIATION_LABELS = ['NA', 'PA', 'A', 'LA'];

    /**
     * Retourne tous les types d’évaluations.
     */
    public static function all(): array
    {
        return [
            self::AUTO_FORMATIVE,
            self::EVAL_FORMATIVE,
            self::AUTO_FINALE,
            self::EVAL_FINALE,
        ];
    }

    /**
     * Retourne les libellés complets des types d’évaluation.
     */
    public static function labels(): array
    {
        return [
            self::AUTO_FORMATIVE => 'Formative 1',
            self::EVAL_FORMATIVE => 'Formative +',
            self::AUTO_FINALE    => 'Formative 2',
            self::EVAL_FINALE    => 'Sommative +',
        ];
    }

    /**
     * Retourne la liste des labels d’appréciation (notation qualitative).
     */
    public static function appreciationLabels(): array
    {
        return self::APPRECIATION_LABELS;
    }

    public static function shortLabels(): array
    {
        return [
            self::AUTO_FORMATIVE => 'ELEV-F1',  // Élève - Formative 1
            self::EVAL_FORMATIVE => 'ENS-F1',   // Enseignant - Formative 1
            self::AUTO_FINALE    => 'ELEV-F2', // Élève - Formative 2 (optionnelle)
            self::EVAL_FINALE    => 'ENS-S',   // Enseignant - Sommative
        ];
    }
}

