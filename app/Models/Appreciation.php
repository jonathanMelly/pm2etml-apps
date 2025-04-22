<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appreciation extends Model
{
    use HasFactory;

    // Indique que l'ID de la table `appreciations` est un BigInt
    protected $primaryKey = 'id';

    // Désactive la gestion automatique des `timestamps`
    public $timestamps = false;

    protected $fillable = [
        'evaluation_id',
        'date',
        'level',
        'signatures',
    ];

    // Transforme automatiquement JSON ↔ Tableau PHP
    protected $casts = [
        'signatures' => 'array',
    ];

    /**
     * Relation avec l'évaluation (un à plusieurs)
     */
    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class, 'evaluation_id');
    }

    /**
     * Définir une relation un-à-plusieurs avec le modèle Criteria.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function criteria()
    {
        return $this->hasMany(Criteria::class, 'appreciation_id');
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
