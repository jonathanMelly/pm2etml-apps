<?php

namespace App\Models;

use App\Constants\AssessmentState;
use App\Services\AssessmentStateMachine;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WorkerContractAssessment extends Model
{
    use HasFactory;

    public function workerContract() : HasOne
    {
        return $this->hasOne(WorkerContract::class);
    }
    // Relations avec les utilisateurs
    public function evaluator() : User
    {
        /* @var $wc WorkerContract */
        $wc = $this->WorkerContract()->firstOrFail();
        return $wc->contract->clients->first();
    }

    public function student(): User
    {
        /* @var $wc WorkerContract */
        $wc = $this->WorkerContract()->firstOrFail();
        return $wc->contract->workers->first()->user;
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(AssessmentCriterion::class);
    }


    /**
     * TODO : non utilisée, laissé à titre d'exemple pour une query avec relation...
     *
     * Retourne toutes les évaluations faites par un professeur pour un étudiant donné
     */
    public static function getAssessments($studentId)
    {

        return WorkerContractAssessment::query()
            ->whereRelation('workerContract.contract.workers.user','id','=',$studentId);
    }

    /**
     * Ajoute un nouvel état à l'historique des états
     */
    public function appendStatus(AssessmentState $newStatus): void
    {
        $currentStatus = $this->status ?? '';
        $newStatusValue = $newStatus->value;

        // Vérifie si le nouvel état n'est pas déjà présent dans l'historique
        if (!str_contains($currentStatus, $newStatusValue)) {
            $this->status = $currentStatus === '' ? $newStatusValue : $currentStatus . ',' . $newStatusValue;
            $this->save();
        }
    }

    /**
     * TODO récupérer le rôle de la session (user()->)
     * Effectue une transition vers un nouvel état
     */
    public function transition(string $role): bool
    {

        $stateMachine = new AssessmentStateMachine($this->assessments()->pluck('timing')->toArray());
        $nextState = $stateMachine->getNextState($role);

        if ($nextState) {
            $this->appendStatus($nextState); // Ajoute le nouvel état à l'historique
            return true;
        }

        return false;
    }


    /**
     * Définir une relation un-à-plusieurs avec le modèle Criteria.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function criteria()
    {
        return $this->hasMany(AssessmentCriterion::class);
    }

}
