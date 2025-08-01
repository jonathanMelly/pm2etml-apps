<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentCriterion extends Model
{
    use HasFactory;

    // Désactive la gestion automatique des `timestamps`
    public $timestamps = false;

    // Indique les colonnes qui peuvent être assignées massivement
    protected $fillable = [
        'timing',           // level renommé en timing
        'assessment_id',    // lien vers l'appréciation
        'template_id',      // remplace name (pointe vers template)
        'value',            // na,pa,a,la
        'checked',
        'remark_criteria',
        'position',
    ];

    // Casts pour les types de données
    protected $casts = [
        'value' => 'integer',
        'checked' => 'boolean',
        'position' => 'integer',
    ];

    /**
     * Relation avec l'appréciation (many to one)
     */
    public function assessment()
    {
        return $this->belongsTo(Assessment::class, 'assessment_id');
    }

    /**
     * Relation avec le template (many to one)
     */
    public function template()
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    /**
     * Retourne les critères d'une appréciation spécifique
     */
    public static function getCriteriaByAppreciation($assessmentId)
    {
        return self::where('assessment_id', $assessmentId)->get();
    }

    /**
     * Crée un critère pour une appréciation spécifique
     */
    public static function createCriteria($assessmentId, $timing, $templateId, $value, $checked, $remark, $position)
    {
        return self::create([
            'assessment_id' => $assessmentId,
            'timing' => $timing,
            'template_id' => $templateId,
            'value' => $value,
            'checked' => $checked,
            'remark_criteria' => $remark,
            'position' => $position
        ]);
    }

    /**
     * Met à jour un critère
     */
    public function updateCriteria($timing, $templateId, $value, $checked, $remark)
    {
        $this->timing = $timing;
        $this->template_id = $templateId;
        $this->value = $value;
        $this->checked = $checked;
        $this->remark_criteria = $remark;
        $this->save();
    }

    /**
     * Supprime un critère en fonction de son ID
     */
    public static function deleteCriteriaById($id)
    {
        return self::where('id', $id)->delete();
    }

    /**
     * Vérifie si une valeur est valide (entre 0 et 3 inclus)
     */
    public static function isValidValue($value)
    {
        return in_array($value, [0, 1, 2, 3]);
    }

    /**
     * Retourne le nom de la valeur
     */
    public static function getValueName($value)
    {
        $valueNames = [
            0 => 'NA',
            1 => 'PA',
            2 => 'A',
            3 => 'LA'
        ];

        return $valueNames[$value] ?? 'Unknown';
    }

    /**
     * Scope pour filtrer par timing
     */
    public function scopeByTiming($query, $timing)
    {
        return $query->where('timing', $timing);
    }

    /**
     * Scope pour filtrer par template
     */
    public function scopeByTemplate($query, $templateId)
    {
        return $query->where('template_id', $templateId);
    }

    /**
     * Scope pour filtrer par valeur
     */
    public function scopeByValue($query, $value)
    {
        return $query->where('value', $value);
    }

    /**
     * Scope pour filtrer par statut checked
     */
    public function scopeChecked($query, $checked = true)
    {
        return $query->where('checked', $checked);
    }
}