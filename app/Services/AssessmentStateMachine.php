<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class AssessmentStateMachine
{
   private \App\Constants\AssessmentState $currentState;
   private array $appreciations;
   private array $acknowledgments = []; // Stocke les quittances des étapes

   public function __construct(?array $appreciations = [])
   {
      $this->appreciations =$appreciations??[];
      $this->computeState();
   }

   private const TRANSITIONS = [
      'teacher' => [
         \App\Constants\AssessmentState::NOT_EVALUATED->value => \App\Constants\AssessmentState::EVAL80,
         \App\Constants\AssessmentState::AUTO80->value => \App\Constants\AssessmentState::EVAL80,
         \App\Constants\AssessmentState::EVAL80->value => \App\Constants\AssessmentState::EVAL100,
         \App\Constants\AssessmentState::AUTO100->value => \App\Constants\AssessmentState::EVAL100,
         \App\Constants\AssessmentState::EVAL100->value => \App\Constants\AssessmentState::PENDING_SIGNATURE,
         \App\Constants\AssessmentState::PENDING_SIGNATURE->value => \App\Constants\AssessmentState::COMPLETED,
      ],
      'student' => [
         \App\Constants\AssessmentState::NOT_EVALUATED->value => \App\Constants\AssessmentState::AUTO80,
         \App\Constants\AssessmentState::AUTO80->value => \App\Constants\AssessmentState::AUTO100,
         \App\Constants\AssessmentState::EVAL80->value => \App\Constants\AssessmentState::AUTO100,
         \App\Constants\AssessmentState::AUTO100->value => \App\Constants\AssessmentState::EVAL100,
         \App\Constants\AssessmentState::EVAL100->value => \App\Constants\AssessmentState::PENDING_SIGNATURE,
         \App\Constants\AssessmentState::PENDING_SIGNATURE->value => \App\Constants\AssessmentState::COMPLETED,
      ],
   ];

   private function computeState(): void
   {
      if (empty($this->appreciations)) {
         $this->currentState = \App\Constants\AssessmentState::NOT_EVALUATED;
         return;
      }

      // Extraire les niveaux valides
      $levels = collect($this->appreciations)
         ->pluck('level')
         ->filter(fn($level) => is_int($level) && \App\Constants\AssessmentTiming::tryFrom($level) !== null)
         ->values();

      if ($levels->isEmpty()) {
         $this->currentState = \App\Constants\AssessmentState::NOT_EVALUATED;
         return;
      }

      // Récupérer le dernier niveau d’évaluation
      $lastLevel = \App\Constants\AssessmentTiming::from($levels->last())->label();

      // Vérifier si ce label correspond à un état valide
      $this->currentState = \App\Constants\AssessmentState::tryFrom($lastLevel) ?? \App\Constants\AssessmentState::NOT_EVALUATED;
   }


   public function getCurrentState(): \App\Constants\AssessmentState
   {
      return $this->currentState;
   }

   public function canTransition(string $role): bool
   {
      return isset(self::TRANSITIONS[$role][$this->currentState->value]);
   }

   public function getNextState(string $role): ?\App\Constants\AssessmentState
   {
      // Obtenir le prochain état à partir des transitions définies
      $nextStateValue = self::TRANSITIONS[$role][$this->currentState->value] ?? null;

      // Vérifier que $nextStateValue est bien du type EvaluationState avant de l'utiliser
      if ($nextStateValue instanceof \App\Constants\AssessmentState) {
         return $nextStateValue;
      }

      // Si $nextStateValue n'est pas du type EvaluationState, retourner null
      return null;
   }

   public function transition(string $role): bool
   {
      // Log du rôle et de l'état actuel
      Log::info('Tentative de transition', [
         'role' => $role,
         'current_state' => $this->currentState->value,
         'current_state_type' => gettype($this->currentState->value)
      ]);

      // Vérifie si une transition est possible avec le rôle et l'état actuel
      if ($this->canTransition($role)) {
         // Récupère la valeur de la transition pour l'état actuel
         $nextStateValue = self::TRANSITIONS[$role][$this->currentState->value] ?? null;

         // Log de la valeur de la prochaine transition
         Log::info('Valeur de la prochaine transition', [
            'next_state_value' => $nextStateValue,
            'next_state_value_type' => gettype($nextStateValue)
         ]);

         // Vérifie que la valeur de l'état suivant est valide
         if ($nextStateValue) {
            // Vérifie si la valeur de l'état suivant est valide
            if (\App\Constants\AssessmentState::tryFrom($nextStateValue->value)) {
               // Effectue la transition
               $this->currentState = \App\Constants\AssessmentState::from($nextStateValue->value);
               // Utilisation de la valeur de la transition
               Log::info('Transition réussie', [
                  'new_state' => $this->currentState->value,
                  'new_state_type' => gettype($this->currentState->value)
               ]);
               return true;
            } else {
               // Log d'erreur si la valeur n'est pas un état valide
               Log::error('Échec de la transition : état suivant invalide', [
                  'next_state_value' => $nextStateValue,
                  'next_state_value_type' => gettype($nextStateValue)
               ]);
            }
         } else {
            // Log d'erreur si la valeur de transition est nulle
            Log::error('Échec de la transition : valeur de transition manquante', [
               'role' => $role,
               'current_state' => $this->currentState->value
            ]);
         }
      }

      // Si l'état actuel est PENDING_SIGNATURE, vérifie les signatures
      if ($this->currentState === \App\Constants\AssessmentState::PENDING_SIGNATURE) {
         return $this->checkSignaturesAndComplete();
      }

      // Log d'erreur si la transition échoue pour une autre raison
      Log::error('Échec de la transition', [
         'role' => $role,
         'current_state' => $this->currentState->value,
         'current_state_type' => gettype($this->currentState->value)
      ]);

      // Si aucune condition n'est remplie, retourne false pour indiquer que la transition a échoué
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
         $this->currentState = \App\Constants\AssessmentState::COMPLETED;
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
   public function isAcknowledged(\App\Constants\AssessmentState $state): bool
   {
      return !empty($this->acknowledgments[$state->value]);
   }
}
