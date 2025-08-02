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

    protected $fillable = [
        'worker_contract_id',
        'teacher_id',
        'student_id',
        'job_id',
        'class_id',
        'job_title',
        'status'
    ];

  /**
 * Relation avec le contrat de travailleur.
 * Une évaluation appartient à un contrat de travailleur.
 */
public function workerContract(): \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    // worker_contract_id est la clé étrangère dans la table worker_contract_assessments
    return $this->belongsTo(WorkerContract::class, 'worker_contract_id');
}

    // Relations avec les utilisateurs
    public function evaluator(): User
    {
        /* @var $wc WorkerContract */
        $wc = $this->workerContract()->firstOrFail();
        return $wc->contract->clients->first();
    }

    public function student(): User
    {
        /* @var $wc WorkerContract */
        $wc = $this->workerContract()->firstOrFail();
        return $wc->contract->workers->first()->user;
    }

    // Relations directes 
    public function teacher(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'teacher_id');
    }

    public function studentUser(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'student_id');
    }

    public function job(): HasOne
    {
        return $this->hasOne(Job::class, 'id', 'job_id');
    }

    public function class(): HasOne
    {
        return $this->hasOne(ClassName::class, 'id', 'class_id');
         // Ajuster
    }

 public function assessments()
{
    return $this->hasMany(Assessment::class, 'worker_contract_assessment_id');
}

    /**
     * *
     * Retourne toutes les évaluations faites par un professeur pour un étudiant donné
     */
    public static function getAssessments($studentId)
    {
        return WorkerContractAssessment::query()
            ->whereRelation('workerContract.contract.workers.user', 'id', '=', $studentId);
    }

      /**
     * Ajoute un nouvel état à l'historique des états (séparé par ;)
     */
    public function appendStatus(AssessmentState $newStatus): void
    {
        $currentStatus = $this->status ?? '';
        $newStatusValue = $newStatus->value;

        // Vérifie si le nouvel état n'est pas déjà présent dans l'historique
        if (!str_contains($currentStatus, $newStatusValue)) {
            $this->status = $currentStatus === '' ? $newStatusValue : $currentStatus . ';' . $newStatusValue;
            $this->save();
        }
    }

    /**
     * Récupère tous les statuts sous forme de tableau
     */
    public function getStatuses(): array
    {
        return $this->status ? explode(';', $this->status) : [];
    }

    /**
     * Vérifie si un statut spécifique existe
     */
    public function hasStatus(AssessmentState $status): bool
    {
        return in_array($status->value, $this->getStatuses());
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
     * Définir une relation un-à-plusieurs avec le modèle AssessmentCriterion.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function criteria()
    {
        return $this->hasMany(AssessmentCriterion::class);
    }

    /**
     * Scope pour filtrer par enseignant
     */
    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope pour filtrer par étudiant
     */
    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    /**
     * Scope pour filtrer par classe
     */
    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    /**
     * Scope pour filtrer par job
     */
    public function scopeByJob($query, $jobId)
    {
        return $query->where('job_id', $jobId);
    }
}