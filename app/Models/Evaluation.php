<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use App\Models\EvaluationStateMachine;
use App\Models\EvaluationState;

class Evaluation extends Model
{
    use HasFactory;

    // Indique que l'ID de la table `evaluations` est un BigInt
    protected $primaryKey = 'id';

    // Désactive la gestion automatique des `timestamps`
    public $timestamps = false;

    // Indique les colonnes qui peuvent être assignées massivement
    protected $fillable = [
        'evaluator_id',
        'student_id',
        'project_name',
        'student_remark',
        'created_at',
        'status'
    ];

    // Relations avec les utilisateurs
    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function appreciations()
    {
        return $this->hasMany(Appreciation::class);
    }

    /**
     * Retourne toutes les évaluations faites par un professeur pour un étudiant donné
     */
    public static function getEvaluationsByStudent($studentId)
    {
        return self::where('student_id', $studentId)->get();
    }

    /**
     * Crée une évaluation pour un étudiant avec un statut initial "not_evaluated"
     */
    public static function createEvaluation($evaluatorId, $studentId, $projectName, $remark = null)
    {
        return self::create([
            'evaluator_id' => $evaluatorId,
            'student_id' => $studentId,
            'project_name' => $projectName,
            'student_remark' => $remark,
            'created_at' => now(),
            'status' => EvaluationState::NOT_EVALUATED->value // Statut initial
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
    public function getCurrentStatus(): EvaluationState
    {
        $statusHistory = $this->getStatusHistory();
        $lastStatus = end($statusHistory);

        if ($lastStatus) {
            return EvaluationState::from($lastStatus);
        }

        throw new InvalidArgumentException('Aucun état trouvé pour cette évaluation.');
    }

    /**
     * Ajoute un nouvel état à l'historique des états
     */
    public function appendStatus(EvaluationState $newStatus): void
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
     * Vérifie si une transition d'état est possible pour un rôle donné
     */
    public function canTransition(string $role): bool
    {
        $stateMachine = new EvaluationStateMachine($this->id, $this->appreciations()->pluck('level')->toArray());
        return $stateMachine->canTransition($role);
    }

    /**
     * Effectue une transition vers un nouvel état
     */
    public function transition(string $role): bool
    {
        if (!$this->canTransition($role)) {
            return false;
        }

        $stateMachine = new EvaluationStateMachine($this->id, $this->appreciations()->pluck('level')->toArray());
        $nextState = $stateMachine->getNextState($role);

        if ($nextState) {
            $this->appendStatus($nextState); // Ajoute le nouvel état à l'historique
            return true;
        }

        return false;
    }

    /**
     * Vérifie si l'évaluation peut être complétée via les signatures
     */
    public function completeWithSignatures(): bool
    {
        $stateMachine = new EvaluationStateMachine($this->id, $this->appreciations()->pluck('level')->toArray());

        if ($stateMachine->checkSignaturesAndComplete()) {
            $this->appendStatus(EvaluationState::COMPLETED); // Ajoute l'état COMPLETED à l'historique
            return true;
        }

        return false;
    }

}
