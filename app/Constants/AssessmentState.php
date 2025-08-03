<?php

namespace App\Constants;

enum AssessmentState: string
{
    // État initial : aucune évaluation encore effectuée
    case NOT_EVALUATED     = 'not_evaluated';

    // Auto-évaluation de l'élève à mi-parcours (80%)
    case AUTO80            = 'auto80';

    // Évaluation de l'enseignant à mi-parcours (80%)
    case EVAL80            = 'eval80';

    // Auto-évaluation de l'élève en fin de période (100%)
    case AUTO100           = 'auto100';

    // Évaluation de l'enseignant en fin de période (100%)
    case EVAL100           = 'eval100';

    // En attente de signature (étape de validation finale)
    case PENDING_SIGNATURE = 'pending_signature';

    // Évaluation entièrement complétée et signée
    case COMPLETED         = 'completed';

    /**
     * Retourne un libellé lisible pour l'état.
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::NOT_EVALUATED     => 'Non évalué',
            self::AUTO80            => 'Auto-évaluation 80%',
            self::EVAL80            => 'Évaluation enseignant 80%',
            self::AUTO100           => 'Auto-évaluation 100%',
            self::EVAL100           => 'Évaluation enseignant 100%',
            self::PENDING_SIGNATURE => 'En attente de signature',
            self::COMPLETED         => 'Complétée',
        };
    }


    public function toAssessmentState(): AssessmentState
{
    return match ($this) {
        self::AUTO80  => AssessmentState::AUTO80,
        self::EVAL80  => AssessmentState::EVAL80,
        self::AUTO100 => AssessmentState::AUTO100,
        self::EVAL100 => AssessmentState::EVAL100,
    };
}
}
