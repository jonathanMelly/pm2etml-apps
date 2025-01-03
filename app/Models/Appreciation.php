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

    // Indique les colonnes qui peuvent être assignées massivement
    protected $fillable = [
        'evaluation_id',
        'date',
        'level',
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
     * Mise à jour de la date d'une appréciation
     */
    public function updateAppreciationDate($newDate)
    {
        $this->date = $newDate;
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
     * Récupère une appréciation par sa date pour une évaluation spécifique
     */
    public static function getAppreciationByDateAndEvaluation($date, $evaluationId)
    {
        return self::where('date', $date)
            ->where('evaluation_id', $evaluationId)
            ->first();
    }

    /**
     * Vérifie s'il existe une appréciation pour une évaluation et une date spécifiques
     */
    public static function hasAppreciation($evaluationId, $date)
    {
        return self::where('evaluation_id', $evaluationId)
            ->where('date', $date)
            ->exists();
    }
}
