<?php

namespace App\Models;

use InvalidArgumentException;

enum EvaluationLevel: int
{
   case AUTO80 = 0;
   case AUTO100 = 1;
   case EVAL80 = 2;
   case EVAL100 = 3;

   public function label(): string
   {
      return match ($this) {
         self::AUTO80 => 'auto80',
         self::AUTO100 => 'auto100',
         self::EVAL80 => 'eval80',
         self::EVAL100 => 'eval100',
      };
   }
}

enum EvaluationState: string
{
   case NOT_EVALUATED = 'not_evaluated';       // Évaluation non réalisée
   case AUTO80 = 'auto80';                     // Évaluation automatique à 80%
   case EVAL80 = 'eval80';                     // Évaluation manuelle à 80%
   case AUTO100 = 'auto100';                   // Évaluation automatique à 100%
   case EVAL100 = 'eval100';                   // Évaluation manuelle à 100%
   case PENDING_SIGNATURE = 'pending_signature'; // En attente de signature
   case COMPLETED = 'completed';               // Évaluation complétée

   public function getLabel(): string
   {
      return ucfirst(str_replace('_', ' ', $this->value));
   }
}

class EvaluationStateMachine
{
   private EvaluationState $currentState;
   private ?int $evaluationId;
   private array $appreciations;
   private array $acknowledgments = []; // Stocke les quittances des étapes

   public function __construct(?int $evaluationId = null, ?array $appreciations = [])
   {
      $this->evaluationId = $evaluationId;
      $this->appreciations = $appreciations ?? [];

      if ($evaluationId !== null && !empty($appreciations)) {
         $this->updateStateFromEvaluations();
      } else {
         $this->currentState = EvaluationState::NOT_EVALUATED;
      }
   }

   private const TRANSITIONS = [
      'teacher' => [
         EvaluationState::NOT_EVALUATED->value => EvaluationState::EVAL80,
         EvaluationState::AUTO80->value => EvaluationState::EVAL80,
         EvaluationState::EVAL80->value => EvaluationState::EVAL100,
         EvaluationState::AUTO100->value => EvaluationState::EVAL100,
         EvaluationState::EVAL100->value => EvaluationState::PENDING_SIGNATURE,
         EvaluationState::PENDING_SIGNATURE->value => EvaluationState::COMPLETED,
      ],
      'student' => [
         EvaluationState::NOT_EVALUATED->value => EvaluationState::AUTO80,
         EvaluationState::AUTO80->value => EvaluationState::AUTO100,
         EvaluationState::EVAL80->value => EvaluationState::AUTO100,
         EvaluationState::EVAL100->value => EvaluationState::PENDING_SIGNATURE,
         EvaluationState::PENDING_SIGNATURE->value => EvaluationState::COMPLETED,
      ],
   ];

   private function updateStateFromEvaluations(): void
   {
      if (!is_array($this->appreciations)) {
         $this->currentState = EvaluationState::NOT_EVALUATED;
         return;
      }

      $levels = collect($this->appreciations)->pluck('level')->filter()->values();

      if ($levels->isNotEmpty()) {
         $lastLevel = EvaluationLevel::from($levels->last())->label();
         $this->currentState = EvaluationState::tryFrom($lastLevel) ?? EvaluationState::NOT_EVALUATED;
      } else {
         $this->currentState = EvaluationState::NOT_EVALUATED;
      }
   }

   public function getCurrentState(): EvaluationState
   {
      return $this->currentState;
   }

   public function canTransition(string $role): bool
   {
      return isset(self::TRANSITIONS[$role][$this->currentState->value]);
   }


   public function getNextState(string $role): ?EvaluationState
   {
      // Obtenir le prochain état à partir des transitions définies
      $nextStateValue = self::TRANSITIONS[$role][$this->currentState->value] ?? null;

      // Vérifier que $nextStateValue est bien du type EvaluationState avant de l'utiliser
      if ($nextStateValue instanceof EvaluationState) {
         return $nextStateValue;
      }

      // Si $nextStateValue n'est pas du type EvaluationState, retourner null
      return null;
   }


   public function transition(string $role): bool
   {
      if ($this->canTransition($role)) {
         $this->currentState = EvaluationState::from(self::TRANSITIONS[$role][$this->currentState->value]);
         return true;
      }

      if ($this->currentState === EvaluationState::PENDING_SIGNATURE) {
         return $this->checkSignaturesAndComplete();
      }

      return false;
   }

   /// v2 ?
   public function addSignature(string $role): bool
   {
      if (!in_array($role, ['teacher', 'student'])) {
         throw new InvalidArgumentException('Invalid role');
      }

      $this->appreciations[$role] = true;
      return $this->checkSignaturesAndComplete();
   }

   private function checkSignaturesAndComplete(): bool
   {
      if (!empty($this->appreciations['teacher']) && !empty($this->appreciations['student'])) {
         $this->currentState = EvaluationState::COMPLETED;
         return true;
      }

      return false;
   }

   // Confirme que l'élève ou l'enseignant valide une étape
   public function acknowledgeState(string $role): bool
   {
      if (!in_array($role, ['teacher', 'student'])) {
         throw new InvalidArgumentException('Invalid role');
      }

      // Enregistre la quittance pour l'état actuel
      $this->acknowledgments[$this->currentState->value] = true;
      return true;
   }

   // Vérifie si l'état actuel a été quittancé
   public function isAcknowledged(EvaluationState $state): bool
   {
      return !empty($this->acknowledgments[$state->value]);
   }
}
