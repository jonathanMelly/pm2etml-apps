<?php

namespace App\Policies;

use App\Models\User;
use App\Constants\RoleName;

class EvaluationPolicy
{
   /**
    * Détermine si l'utilisateur peut stocker une évaluation.
    *
    * @param User $user
    * @return bool
    */
   public function storeEvaluation(User $user): bool
   {
      return $user->role === RoleName::TEACHER || $user->role === RoleName::STUDENT;
   }
}
