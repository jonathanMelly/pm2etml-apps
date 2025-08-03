<?php

namespace App\Services;

use App\Constants\AssessmentState;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AssessmentStateMachine
{
    private Collection $appreciations;
    private AssessmentState $currentState;

    /**
     * Constructeur de la machine à états.
     *
     * @param array $appreciations Liste des appréciations (objets ou tableaux) contenant un champ 'timing'.
     */
    public function __construct(array $appreciations)
    {
        Log::debug('AssessmentStateMachine: Constructeur appelé', ['input_appreciations' => $appreciations]);

        $this->appreciations = collect($appreciations);
        $this->currentState = $this->computeState();

        Log::debug('AssessmentStateMachine: État initial calculé', [
            'initial_state' => $this->currentState->value,
            'initial_state_object' => get_class($this->currentState),
        ]);
    }

    /**
     * Calcule l'état actuel à partir des appréciations en utilisant la propriété 'timing'.
     */
    private function computeState(): AssessmentState
    {
        Log::debug('AssessmentStateMachine: computeState démarré', [
            'appreciations_count' => $this->appreciations->count(),
            'appreciations_sample' => $this->appreciations->take(5)->all(),
        ]);

        // On récupère les timings dans les appréciations
        $timings = $this->appreciations->map(function ($item) {
            if (is_array($item) && isset($item['timing'])) {
                return $item['timing'];
            }

            if (is_object($item) && isset($item->timing)) {
                return $item->timing;
            }

            return null;
        });

        Log::debug('AssessmentStateMachine: computeState - timings extraits', [
            'timings' => $timings->all(),
        ]);

        // On filtre uniquement les timings valides reconnus comme état
        $validTimings = $timings->filter(fn($t) => AssessmentState::tryFrom($t) !== null)->values();

        Log::debug('AssessmentStateMachine: computeState - timings valides', [
            'valid_timings' => $validTimings->all(),
        ]);

        if ($validTimings->isEmpty()) {
            Log::debug('AssessmentStateMachine: computeState - Aucun timing valide trouvé, état NOT_EVALUATED');
            return AssessmentState::NOT_EVALUATED;
        }

        // On prend le dernier timing comme état courant
        $lastTiming = $validTimings->last();

        return AssessmentState::from($lastTiming);
    }

    /**
     * Retourne l’état courant.
     */
    public function getCurrentState(): AssessmentState
    {
        return $this->currentState;
    }

    /**
     * Calcule l’état suivant à partir du rôle utilisateur et de l’état actuel.
     *
     * @param string $role Le rôle de l'utilisateur (ex: 'student' ou 'teacher')
     * @return AssessmentState|null L'état suivant ou null si aucun
     */
    public function getNextState(string $role): ?AssessmentState
    {
        Log::debug('AssessmentStateMachine: getNextState appelé', [
            'role' => $role,
            'current_state' => $this->currentState->value,
        ]);

        $transitions = [
            'student' => [
                AssessmentState::NOT_EVALUATED->value => AssessmentState::AUTO80,
                AssessmentState::EVAL80->value        => AssessmentState::AUTO100,
            ],
            'teacher' => [
                AssessmentState::AUTO80->value           => AssessmentState::EVAL80,
                AssessmentState::AUTO100->value          => AssessmentState::EVAL100,
                AssessmentState::EVAL100->value          => AssessmentState::PENDING_SIGNATURE,
                AssessmentState::PENDING_SIGNATURE->value => AssessmentState::COMPLETED,
            ],
        ];

        $roleTransitions = $transitions[$role] ?? [];

        $next = $roleTransitions[$this->currentState->value] ?? null;

        Log::debug('AssessmentStateMachine: getNextState - Résultat', [
            'next_state_value_from_array' => $next?->value ?? null,
            'next_state_value_type' => gettype($next),
        ]);

        return $next;
    }
}