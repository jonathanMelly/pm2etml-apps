<?php

namespace App\Models;

use App\Services\AssessmentStateMachine;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class WorkerContractAssessment extends Model
{
    use HasFactory;

    /**
     * Champs autorisés à l’écriture.
     */
    protected $fillable = [
        'worker_contract_id',
        'teacher_id',
        'student_id',
        'job_id',
        'class_id',
        'job_title',
        'status',
        'student_remark',
    ];

    /* -----------------------------------------------------------------
     |  Relations principales
     | -----------------------------------------------------------------
     */

    public function workerContract(): BelongsTo
    {
        return $this->belongsTo(WorkerContract::class, 'worker_contract_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'worker_contract_assessment_id');
    }

    /* -----------------------------------------------------------------
     |  Accesseurs et méthodes utilitaires
     | -----------------------------------------------------------------
     */

    public function getStatus(): string
    {
        return (new AssessmentStateMachine($this->assessments))->getCurrentState();
    }

    public function getCriteriaCountAttribute(): int
    {
        return $this->assessments->flatMap->criteria->count();
    }

    /* -----------------------------------------------------------------
     |  Méthodes métier
     | -----------------------------------------------------------------
     */

    public static function createWithAssessments(array $data): self
    {
        return DB::transaction(function () use ($data) {
            // Création de l’évaluation principale
            $evaluation = self::create([
                'worker_contract_id' => $data['worker_contract_id'],
                'teacher_id' => $data['teacher_id'] ?? null,
                'student_id' => $data['student_id'] ?? null,
                'job_id' => $data['job_id'] ?? null,
                'class_id' => $data['class_id'] ?? null,
                'job_title' => $data['job_title'] ?? '',
                'status' => $data['status'] ?? 'not_started',
            ]);

            // Création des sous-évaluations (assessments)
            foreach ($data['appreciations'] ?? [] as $app) {
                $assessment = $evaluation->assessments()->create([
                    'timing' => $app['level'] ?? '',
                    'date' => $app['date'] ?? now(),
                    'student_remark' => $app['student_remark'] ?? $data['student_remark'] ?? null,
                ]);

                // Critères associés à chaque assessment
                foreach ($app['criteria'] ?? [] as $index => $criterion) {
                    $assessment->criteria()->create([
                        'timing' => $assessment->timing,
                        'template_id' => $criterion['id'],
                        'value' => $criterion['value'],
                        'checked' => $criterion['checked'] ?? false,
                        'remark_criteria' => $criterion['remark'] ?? null,
                        'position' => $index + 1,
                    ]);
                }
            }

            return $evaluation;
        });
    }

    public function updateWithAssessments(array $data): self
    {
        return DB::transaction(function () use ($data) {
            // Mise à jour de l'évaluation principale (le champ student_remark n'existe pas ici)
            $this->update([
                'status' => $data['status'] ?? $this->status,
            ]);

            // Mise à jour ou création des sous-évaluations
            foreach ($data['appreciations'] ?? [] as $app) {
                $assessment = $this->assessments()
                    ->firstOrCreate(
                        ['timing' => $app['level']],
                        ['date' => $app['date'] ?? now()]
                    );

                // Mettre à jour la remarque élève au niveau de l'assessment
                $assessment->update([
                    'student_remark' => $app['student_remark'] ?? $data['student_remark'] ?? $assessment->student_remark,
                ]);

                $assessment->criteria()->delete();

                foreach ($app['criteria'] ?? [] as $index => $criterion) {
                    $assessment->criteria()->create([
                        'timing' => $assessment->timing,
                        'template_id' => $criterion['id'],
                        'value' => $criterion['value'],
                        'checked' => $criterion['checked'] ?? false,
                        'remark_criteria' => $criterion['remark'] ?? null,
                        'position' => $index + 1,
                    ]);
                }
            }

            return $this;
        });
    }

    public function deleteWithAssessments(): bool
    {
        return DB::transaction(function () {
            foreach ($this->assessments as $assessment) {
                $assessment->criteria()->delete();
                $assessment->delete();
            }
            return $this->delete();
        });
    }

    /**
     * Ajoute ou met à jour un assessment spécifique avec ses critères.
     */
    public function addAssessmentWithCriteria(string $timing, array $criteriaData, ?string $studentRemark = null)
    {
        return DB::transaction(function () use ($timing, $criteriaData, $studentRemark) {
            $assessment = $this->assessments()->updateOrCreate(
                ['timing' => $timing],
                ['date' => now(), 'student_remark' => $studentRemark]
            );

            $assessment->criteria()->delete();

            foreach ($criteriaData as $index => $criterion) {
                $assessment->criteria()->create([
                    'template_id' => $criterion['id'],
                    'value' => $criterion['value'],
                    'checked' => $criterion['checked'] ?? false,
                    'remark_criteria' => $criterion['remark'] ?? '',
                    'position' => $index + 1,
                    'timing' => $timing,
                ]);
            }

            return $assessment;
        });
    }

    /* -----------------------------------------------------------------
     |  Scopes de requêtes
     | -----------------------------------------------------------------
     */

    public function scopeByTeacher($query, int $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeByStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }
}
