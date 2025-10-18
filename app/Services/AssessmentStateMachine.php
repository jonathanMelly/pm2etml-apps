<?php

namespace App\Services;

use App\Constants\AssessmentState;
use App\Constants\AssessmentTiming;
use App\Constants\AssessmentWorkflowState;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Support\AssessmentNormalizer;

class AssessmentStateMachine
{
    /** @var \Illuminate\Support\Collection<int, array> */
    private Collection $items;

    private AssessmentState $currentState;
    private array $timings = [];

    /**
     * @param  iterable  $appreciations  Liste d’items (Eloquent models, arrays…) contenant un champ 'timing'
     */
    public function __construct(iterable $appreciations)
    {
        // Uniformiser en Collection d'arrays
        if ($appreciations instanceof Collection) {
            $this->items = $appreciations->map(fn ($it) => $this->toArrayItem($it));
        } else {
            $this->items = collect($appreciations)->map(fn ($it) => $this->toArrayItem($it));
        }

        Log::debug('ASM: constructor', ['count' => $this->items->count()]);

        $this->currentState = $this->computeState();
        Log::debug('ASM: initial state', ['state' => $this->currentState->value]);
    }

    private function toArrayItem($it): array
    {
        if (is_array($it)) {
            return $it;
        }

        // Eloquent/objets
        return [
            'timing' => $it->timing ?? null,
        ];
    }

    /**
     * Normalise un timing libre (auto_formative, autoFormative, AUTO-FINALE, …) vers une valeur d’enum valide.
     */
    private function normalizeTiming(?string $raw): ?AssessmentState
    {
        return AssessmentNormalizer::normalizeTimingToState($raw);
    }

    /**
     * Calcule l’état courant par **priorité métier** :
     * EVAL_FINALE > EVAL_FORMATIVE > AUTO_FINALE > AUTO_FORMATIVE > NOT_EVALUATED
     */
    private function computeState(): AssessmentState
    {
        $normalized = $this->items
            ->map(fn ($it) => $this->normalizeTiming($it['timing'] ?? null))
            ->filter(); // supprime null

        if ($normalized->isEmpty()) {
            return AssessmentState::NOT_EVALUATED;
        }

        // Mémoriser les timings présents (pour workflow enrichi)
        $this->timings = $normalized->map(fn($s) => $s->value)->unique()->values()->all();

        // Priorité décroissante
        $priority = [
            AssessmentState::EVAL_FINALE->value     => 5,
            AssessmentState::EVAL_FORMATIVE->value  => 4,
            AssessmentState::AUTO_FINALE->value     => 3,
            AssessmentState::AUTO_FORMATIVE->value  => 2,
            AssessmentState::NOT_EVALUATED->value   => 1,
        ];

        $best = $normalized->sortByDesc(fn ($state) => $priority[$state->value] ?? 0)->first();

        return $best ?? AssessmentState::NOT_EVALUATED;
    }

    /**
     * État courant (enum).
     */
    public function getCurrentState(): AssessmentState
    {
        return $this->currentState;
    }

    /**
     * État de workflow enrichi basé sur les timings présents et un statut global optionnel.
     * @param string|null $evaluationStatus Statut global (ex: pending_signature, completed)
     */
    public function getWorkflowState(?string $evaluationStatus = null): AssessmentWorkflowState
    {
        $has = fn(string $value) => in_array($value, $this->timings, true);

        $hasAutoF = $has(AssessmentState::AUTO_FORMATIVE->value) || $has(AssessmentTiming::AUTO_FORMATIVE);
        $hasEvalF = $has(AssessmentState::EVAL_FORMATIVE->value) || $has(AssessmentTiming::EVAL_FORMATIVE);
        $hasAutoS = $has(AssessmentState::AUTO_FINALE->value)    || $has(AssessmentTiming::AUTO_FINALE);
        $hasEvalS = $has(AssessmentState::EVAL_FINALE->value)    || $has(AssessmentTiming::EVAL_FINALE);

        // Clôture / signature prioritaire si éval finale faite
        if ($hasEvalS) {
            if ($evaluationStatus === AssessmentState::COMPLETED->value) {
                return AssessmentWorkflowState::CLOSED_BY_TEACHER;
            }
            // Signature supprimée: si ENS‑S présent et non terminé → TEACHER_SUMMATIVE_DONE
            return AssessmentWorkflowState::TEACHER_SUMMATIVE_DONE;
        }

        // Sommatif élève optionnel → s'il a été fait, on attend l'enseignant
        if ($hasAutoS && !$hasEvalS) {
            // Respecter un statut enregistré plus précis s'il existe
            if ($evaluationStatus === AssessmentWorkflowState::TEACHER_ACK_FORMATIVE2->value) {
                return AssessmentWorkflowState::TEACHER_ACK_FORMATIVE2;
            }
            if ($evaluationStatus === AssessmentWorkflowState::WAITING_TEACHER_VALIDATION_F2->value) {
                return AssessmentWorkflowState::WAITING_TEACHER_VALIDATION_F2;
            }
            return AssessmentWorkflowState::WAITING_TEACHER_SUMMATIVE;
        }

        // Phase formative détaillée
        if ($hasEvalF) {
            // L'enseignant a réalisé son évaluation formative
            // Priorités: attente validation élève (si activée) > formative close > proposer sommative élève optionnelle
            if ($evaluationStatus === AssessmentWorkflowState::WAITING_STUDENT_VALIDATION_F->value) {
                return AssessmentWorkflowState::WAITING_STUDENT_VALIDATION_F;
            }
            if ($evaluationStatus === AssessmentWorkflowState::FORMATIVE_VALIDATED->value) {
                return AssessmentWorkflowState::FORMATIVE_VALIDATED;
            }
            // défaut: invitons l'élève à une éventuelle sommative (optionnelle)
            return AssessmentWorkflowState::WAITING_STUDENT_FORMATIVE2_OPT;
        }

        // Élève a fait l'auto formative → en attente de validation / prise en compte par l'enseignant
        if ($hasAutoF && !$hasEvalF) {
            // Si l'enseignant a accusé réception, refléter l'ACK
            if ($evaluationStatus === AssessmentWorkflowState::TEACHER_ACK_FORMATIVE->value) {
                return AssessmentWorkflowState::TEACHER_ACK_FORMATIVE;
            }
            return AssessmentWorkflowState::WAITING_TEACHER_VALIDATION_F;
        }

        // Par défaut: première étape formative élève
        return AssessmentWorkflowState::WAITING_STUDENT_FORMATIVE;
    }

    /**
     * Transition suivante selon le rôle.
     *
     * Règles proposées (ajuste si besoin) :
     * - student : NOT_EVALUATED → AUTO_FORMATIVE → AUTO_FINALE
     * - teacher : AUTO_FORMATIVE → EVAL_FORMATIVE → AUTO_FINALE → EVAL_FINALE → PENDING_SIGNATURE → COMPLETED
     */
    public function getNextState(string $role): ?AssessmentState
    {
        $role = strtolower($role);

        $studentFlow = [
            AssessmentState::NOT_EVALUATED->value => AssessmentState::AUTO_FORMATIVE,
            AssessmentState::AUTO_FORMATIVE->value => AssessmentState::AUTO_FINALE,
            // après éval formative enseignant, l'élève peut faire F2
            AssessmentState::EVAL_FORMATIVE->value => AssessmentState::AUTO_FINALE,
        ];

        $teacherFlow = [
            AssessmentState::AUTO_FORMATIVE->value   => AssessmentState::EVAL_FORMATIVE,
            AssessmentState::EVAL_FORMATIVE->value   => AssessmentState::AUTO_FINALE,
            AssessmentState::AUTO_FINALE->value      => AssessmentState::EVAL_FINALE,
            AssessmentState::EVAL_FINALE->value      => AssessmentState::PENDING_SIGNATURE,
            AssessmentState::PENDING_SIGNATURE->value => AssessmentState::COMPLETED,
        ];

        $map = $role === 'student' ? $studentFlow : ($role === 'teacher' ? $teacherFlow : []);

        return $map[$this->currentState->value] ?? null;
    }
}


