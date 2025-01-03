<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Criteria extends Model
{
    use HasFactory;

    // Indique que l'ID de la table `criteria` est un BigInt
    protected $primaryKey = 'id';

    // Désactive la gestion automatique des `timestamps`
    public $timestamps = false;

    // Indique les colonnes qui peuvent être assignées massivement
    protected $fillable = [
        'level',
        'appreciation_id',
        'name',
        'value',
        'checked',
        'remark',
        'position'
    ];

    /**
     * Relation avec l'appréciation (un à plusieurs)
     */
    public function appreciation()
    {
        return $this->belongsTo(Appreciation::class, 'appreciation_id');
    }

    /**
     * Retourne les critères d'une appréciation spécifique
     */
    public static function getCriteriaByAppreciation($appreciationId)
    {
        return self::where('appreciation_id', $appreciationId)->get();
    }

    /**
     * Crée un critère pour une appréciation spécifique
     */
    public static function createCriteria($appreciationId, $level, $name, $value, $checked, $remark, $position)
    {
        return self::create([
            'appreciation_id' => $appreciationId,
            'level' => $level,
            'name' => $name,
            'value' => $value,
            'checked' => $checked,
            'remark' => $remark,
            'position' => $position
        ]);
    }

    public function criteria()
    {
        return $this->hasMany(Criteria::class);
    }


    /**
     * Met à jour un critère par son ID
     */
    public function updateCriteria($level, $name, $value, $checked, $remark)
    {
        $this->level = $level;
        $this->name = $name;
        $this->value = $value;
        $this->checked = $checked;
        $this->remark = $remark;
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
     * Retourne le nom de la valeur (par exemple, "NA", "PA", "A", "LA")
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
}
