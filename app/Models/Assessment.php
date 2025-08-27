<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Assessment extends Model
{
    use HasFactory;

    // Désactive la gestion automatique des `timestamps` si nécessaire
    public $timestamps = true;

    // Colonnes qui peuvent être assignées massivement
    protected $fillable = [
        'worker_contract_assessment_id',
        'date',
        'timing',
        'student_remark',
    ];

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
    public function getCheckedCriteria()
    {
        return $this->criteria()->where('checked', true)->get();
    }

    /**
     * Retourne les critères non cochés
     */
    public function getUncheckedCriteria()
    {
        return $this->criteria()->where('checked', false)->get();
    }
}