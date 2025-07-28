<?php

namespace App\Models;

use App\Constants\AssessmentState;
use App\Services\AssessmentStateMachine;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use InvalidArgumentException;

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

    public function student()
    {
        /* @var $wc WorkerContract */
        $wc = $this->WorkerContract()->firstOrFail();
        return $wc->contract->workers->first();
    }

    public function assessments()
    {
        return $this->hasMany(AssessmentCriterion::class);
    }

    /**
     * Retourne toutes les évaluations faites par un professeur pour un étudiant donné
     */
    public static function getAssessments($studentId)
    {

        return WorkerContractAssessment::query()
            ->whereRelation('contract.groupMember.user','id','=',$studentId);
    }

    /**
     * Crée une évaluation pour un étudiant avec un statut initial "not_evaluated"
     */
    public static function createAssessment($comment = null)
    {
        return self::create([
            'comment' => $comment,
            'status' => AssessmentState::NOT_EVALUATED->value // Statut initial
        ]);
    }

    /**
     * Récupère tous les états traversés sous forme de tableau
     */
    public function getStatusHistory(): array
    {
        return explode(',', $this->status ?? '');
    }

    /**
     * Récupère l'état actuel sous forme d'énumération
     */
    public function getCurrentStatus(): AssessmentState
    {
        $statusHistory = $this->getStatusHistory();
        $lastStatus = end($statusHistory);

        if ($lastStatus) {
            return AssessmentState::from($lastStatus);
        }

        throw new InvalidArgumentException('Aucun état trouvé pour cette évaluation.');
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
        return $this->hasMany(AssessmentCriterion::class, 'appreciation_id');
    }

    /**
     * Retourne toutes les appréciations pour une évaluation donnée
     */
    public static function getAppreciationsByEvaluation($evaluationId)
    {
        return self::where('evaluation_id', $evaluationId)->get();
    }

    /**
     * Crée une appréciation pour une évaluation spécifique
     */
    public static function createAppreciation($evaluationId, $date)
    {
        return self::create([
            'evaluation_id' => $evaluationId,
            'date' => $date
        ]);
    }

    /**
     * Mise à jour de la date et du niveau d'une appréciation
     */
    public function updateAppreciation($newDate, $newLevel)
    {
        $this->date = $newDate;
        $this->level = $newLevel;
        $this->save();
    }


    /**
     * Supprimer une appréciation en fonction de son ID
     */
    public static function deleteAppreciationById($id)
    {
        return self::where('id', $id)->delete();
    }

    /**
     * Récupère une appréciation par sa date et niveau pour une évaluation spécifique
     */
    public static function getAppreciationByDateAndLevel($date, $level, $evaluationId)
    {
        return self::where('date', $date)
            ->where('evaluation_id', $evaluationId)
            ->where('level', $level)
            ->first();
    }

    /**
     * Vérifie s'il existe une appréciation pour une évaluation et une date spécifiques avec un niveau donné
     */
    public static function hasAppreciation($evaluationId, $date, $level)
    {
        return self::where('evaluation_id', $evaluationId)
            ->where('date', $date)
            ->where('level', $level)
            ->exists();
    }

    public function isFullySigned(): bool
    {
        $signatures = $this->signatures ?? ['teacher' => false, 'student' => false];
        return $signatures['teacher'] && $signatures['student']; // Vrai si les deux ont signé
    }

    public function addSignature(string $role): void
    {
        if (!in_array($role, ['teacher', 'student'])) {
            throw new \InvalidArgumentException('Role must be either "teacher" ou "student".');
        }

        $signatures = $this->signatures ?? ['teacher' => false, 'student' => false];
        $signatures[$role] = true; // Met le rôle à "true" pour indiquer qu'il a signé
        $this->signatures = $signatures;
        $this->save();
    }


    public function hasSigned(string $role): bool
    {
        if (!in_array($role, ['teacher', 'student'])) {
            throw new \InvalidArgumentException('Role must be either "teacher" or "student".');
        }

        $signatures = $this->signatures ?? ['teacher' => false, 'student' => false];
        return $signatures[$role]; // Retourne vrai ou faux
    }

}
