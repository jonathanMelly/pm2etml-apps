<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'created_at'
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
     * Retourne toutes les évaluations faites par un professeur pour un projet spécifique
     */
    public static function getEvaluationsByProject($projectName)
    {
        return self::where('project_name', $projectName)->get();
    }

    /**
     * Vérifie si un étudiant a été évalué par un professeur donné
     */
    public static function isEvaluatedBy($studentId, $professorId)
    {
        return self::where('student_id', $studentId)
            ->where('evaluator_id', $professorId)
            ->exists();
    }

    /**
     * Récupère les évaluations d'un professeur pour une année scolaire spécifique
     * Supposons que tu ajoutes une année scolaire à la table, 
     * sinon tu pourrais remplacer ce champ par un attribut ou en rajouter un dans la table.
     */
    public static function getEvaluationsByYear($year)
    {
        return self::whereYear('created_at', $year)->get();
    }

    /**
     * Retourne les remarques d'un étudiant pour un projet particulier
     */
    public function getRemarkByProject($projectName)
    {
        return $this->where('student_id', $this->student_id)
            ->where('project_name', $projectName)
            ->value('student_remark');
    }

    /**
     * Crée une évaluation pour un étudiant
     */
    public static function createEvaluation($evaluatorId, $studentId, $projectName, $remark = null)
    {
        return self::create([
            'evaluator_id' => $evaluatorId,
            'student_id' => $studentId,
            'project_name' => $projectName,
            'student_remark' => $remark,
            'created_at' => now()
        ]);
    }

    /**
     * Mise à jour d'une évaluation existante avec une nouvelle remarque
     */
    public function updateRemark($remark)
    {
        $this->student_remark = $remark;
        $this->save();
    }

    /**
     * Supprimer une évaluation par l'étudiant et le projet
     */
    public function deleteEvaluationByStudentAndProject($studentId, $projectName)
    {
        return self::where('student_id', $studentId)
            ->where('project_name', $projectName)
            ->delete();
    }
}
