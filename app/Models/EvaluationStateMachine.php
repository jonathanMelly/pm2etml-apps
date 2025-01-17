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
   case NOT_EVALUATED = 'not_evaluated';
   case AUTO80 = 'auto80';
   case EVAL80 = 'eval80';
   case AUTO100 = 'auto100';
   case EVAL100 = 'eval100';
   case PENDING_SIGNATURE = 'pending_signature';
   case COMPLETED = 'completed';

   public function label(): string
   {
      return ucfirst(str_replace('_', ' ', $this->value));
   }
}
class EvaluationStateMachine
{
   private EvaluationState $currentState;
   private ?int $evaluationId;
   private ?array $appreciations;

   // Le constructeur pour initialiser l'état
   public function __construct(?int $evaluationId = null, ?array $appreciations = null)
   {
      if ($evaluationId !== null && $appreciations !== null) {
         // Mise à jour de l'état en fonction des appréciations existantes
         $this->updateStateFromEvaluations();
      } else {
         // Initialisation de l'état à "NOT_EVALUATED"
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

   // Méthode pour mettre à jour l'état de la machine d'évaluation en fonction des évaluations existantes
   private function updateStateFromEvaluations(): void
   {
      // Récupération des niveaux des appréciations
      $levels = collect($this->appreciations)->pluck('level')->filter()->values();

      if ($levels->isNotEmpty()) {
         // Si des niveaux d'évaluation existent, on prend le dernier niveau comme état courant
         $lastLevel = EvaluationLevel::from($levels->last())->label();

         $this->currentState = EvaluationState::from($lastLevel);
      } else {
         // Sinon, l'état est initialisé à "NOT_EVALUATED"
         $this->currentState = EvaluationState::NOT_EVALUATED;
      }
   }

   // Retourne l'état courant de l'évaluation
   public function getCurrentState(): EvaluationState
   {
      return $this->currentState;
   }

   // Vérifie si une transition est possible en fonction du rôle (enseignant ou élève)
   public function canTransition(string $role): bool
   {
      $currentState = $this->getCurrentState()->value;
      return isset(self::TRANSITIONS[$role][$currentState]);
   }

   // Retourne le prochain état possible pour un rôle donné
   public function getNextState(string $role): ?EvaluationState
   {
      $currentState = $this->getCurrentState()->value;
      return self::TRANSITIONS[$role][$currentState] ?? null;
   }

   // Applique une transition d'état si elle est valide
   public function transition(string $role): bool
   {
      $currentState = $this->getCurrentState();

      if ($this->canTransition($role)) {
         $nextState = self::TRANSITIONS[$role][$currentState];
         $this->currentState = $nextState;
         return true;
      }

      // Vérification des signatures si dans l'état "PENDING_SIGNATURE"
      if ($currentState === EvaluationState::PENDING_SIGNATURE) {
         return $this->checkSignaturesAndComplete();
      }

      return false;
   }

   // Ajoute une signature à l'évaluation (enseignant ou étudiant)
   public function addSignature(string $role): bool
   {
      if (!in_array($role, ['teacher', 'student'])) {
         throw new InvalidArgumentException('Invalid role');
      }

      // On suppose que la signature est ajoutée localement dans la collection/appreciation
      $this->appreciations[$role] = true;

      // Vérification des signatures et mise à jour de l'état si elles sont toutes présentes
      return $this->checkSignaturesAndComplete();
   }

   // Vérifie si les signatures sont complètes et marque l'évaluation comme "COMPLETED"
   private function checkSignaturesAndComplete(): bool
   {
      // Si les signatures des deux parties sont présentes, mettre l'état à "COMPLETED"
      if ($this->appreciations['teacher'] && $this->appreciations['student']) {
         $this->currentState = EvaluationState::COMPLETED;
         return true;
      }

      return false; // Toujours en attente de la signature de l'autre partie
   }
}
