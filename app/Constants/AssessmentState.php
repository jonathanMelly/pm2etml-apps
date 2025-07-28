<?php

namespace App\Constants;

enum AssessmentState: string
{
    case NOT_EVALUATED = 'not_evaluated';       // Évaluation non réalisée
    case AUTO80 = 'auto80';                     // Évaluation élève à 80%
    case EVAL80 = 'eval80';                     // Évaluation enseignant à 80%
    case AUTO100 = 'auto100';                   // Évaluation élève à 100%
    case EVAL100 = 'eval100';                   // Évaluation enseignant à 100%
    case PENDING_SIGNATURE = 'pending_signature'; // En attente de signature
    case COMPLETED = 'completed';               // Évaluation complétée

    public function getLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->value));
    }
}
