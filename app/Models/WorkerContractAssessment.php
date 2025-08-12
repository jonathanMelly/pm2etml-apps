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
        'timing'
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

    //TODO HCS amuse-toi ;-)
    public function getStatus()
    {
        return (new AssessmentStateMachine($this->assessments()))->getCurrentState();
    }

    public function student(): User
    {
        /* @var $wc WorkerContract */
        $wc = $this->workerContract()->firstOrFail();
        return $wc->contract->workers->first()->user;
    }

    // Relations directes
    public function teacher()
    {
        return $this->workerContract->contract->clients->first();
    }

    public function studentUser()
    {
        return $this->workerContract->contract->workers->first();
    }

    public function job()
    {
        return $this->workerContract->contract->jobDefinition;
    }

    public function class(): HasOne
    {
        return $this->workerContract->contract->workers->first()->group->groupName;
    }

    // Casts pour les types de données
    protected $casts = [
        'date' => 'datetime',
    ];

    /**
     * Relation avec WorkerContractAssessment (many to one)
     */
    public function workerContractAssessment()
    {
        return $this->belongsTo(WorkerContractAssessment::class);
    }

    /**
     * Relation avec les critères d'évaluation (one to many)
     */
    public function criteria()
    {
        return $this->hasMany(AssessmentCriterion::class, 'assessment_id');
    }

    /**
     * Créer une nouvelle appréciation avec ses critères
     */
    public static function createAssessment(array $data)
    {
        $assessment = self::create([
            'worker_contract_assessment_id' => $data['worker_contract_assessment_id'],
            'date' => $data['date'] ?? now(),
            'timing' => $data['timing'] ?? '',
            'student_remark' => $data['student_remark'] ?? '',
        ]);

        // Créer les critères si fournis
        if (isset($data['criteria']) && is_array($data['criteria'])) {
            foreach ($data['criteria'] as $criterionData) {
                $assessment->criteria()->create(array_merge($criterionData, [
                    'assessment_id' => $assessment->id
                ]));
            }
        }

        return $assessment;
    }

    /**
     * Met à jour une appréciation et ses critères
     */
    public function updateAssessment(array $data)
    {
        $this->update([
            'date' => $data['date'] ?? $this->date,
            'timing' => $data['timing'] ?? $this->timing,
            'student_remark' => $data['student_remark'] ?? $this->student_remark,
        ]);

        return $this;
    }

    /**
     * Supprime une appréciation et tous ses critères
     */
    public function deleteAssessment()
    {
        // Supprime d'abord les critères
        $this->criteria()->delete();

        // Puis supprime l'appréciation
        return $this->delete();
    }

    /**
     * Retourne toutes les appréciations pour une évaluation spécifique
     */
    public static function getAssessmentsByWorkerContractAssessment($workerContractAssessmentId)
    {
        return self::where('worker_contract_assessment_id', $workerContractAssessmentId)->get();
    }

    /**
     * Scope pour filtrer par timing
     */
    public function scopeByTiming($query, $timing)
    {
        return $query->where('timing', $timing);
    }

    /**
     * Scope pour filtrer par date
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope pour filtrer par période
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Retourne le nombre de critères pour cette appréciation
     */
    public function getCriteriaCountAttribute()
    {
        return $this->criteria()->count();
    }

    /**
     * Retourne les critères cochés
     */
    public function getActiveCriteria()
    {
        return $this->criteria()->where('active', true)->get();
    }

    /**
     * Retourne les critères non cochés
     */
    public function getInactiveCriteria()
    {
        return $this->criteria()->where('active', false)->get();
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
