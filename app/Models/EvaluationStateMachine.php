<?php

namespace App\Models;

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

class EvaluationStateMachine
{
   private string $state = 'START';

   private const TRANSITIONS = [
      'START' => [
         ['role' => 'student', 'level' => EvaluationLevel::AUTO80, 'nextState' => 'AUTO80_IN_PROGRESS'],
         ['role' => 'teacher', 'level' => EvaluationLevel::EVAL80, 'nextState' => 'EVAL80_DONE'],
      ],
      'AUTO80_IN_PROGRESS' => [
         ['role' => 'teacher', 'level' => EvaluationLevel::EVAL80, 'nextState' => 'EVAL80_DONE'],
      ],
      'EVAL80_DONE' => [
         ['role' => 'student', 'level' => EvaluationLevel::AUTO100, 'nextState' => 'AUTO100_IN_PROGRESS'],
         ['role' => 'teacher', 'level' => EvaluationLevel::EVAL100, 'nextState' => 'COMPLETE'],
      ],
      'AUTO100_IN_PROGRESS' => [
         ['role' => 'teacher', 'level' => EvaluationLevel::EVAL100, 'nextState' => 'COMPLETE'],
      ],
   ];

   public function transition(string $role, EvaluationLevel $level): bool
   {
      foreach (self::TRANSITIONS[$this->state] ?? [] as $transition) {
         if ($transition['role'] === $role && $transition['level'] === $level) {
            $this->state = $transition['nextState'];
            return true;
         }
      }

      return false; // Transition invalide
   }

   public function getState(): string
   {
      return $this->state;
   }


}
