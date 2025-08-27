<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AssessmentCriterionTemplate extends Model
{
    use HasFactory;

    // Colonnes qui peuvent être assignées massivement
    protected $fillable = [
        'name',
        'description',
        'user_id',
        'assessment_criterion_category_id',
        'position',
    ];

    // Casts pour les types de données
    protected $casts = [
        'position' => 'integer',
        'user_id' => 'integer',
        'assessment_criterion_category_id' => 'integer',
    ];

    /**
     * Relation avec la catégorie
     */
    public function category()
    {
        return $this->belongsTo(
            \App\Models\AssessmentCriterionCategory::class,
            'assessment_criterion_category_id',
            'id'
        );
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation: UN TEMPLATE est utilisé par PLUSIEURS CRITÈRES
     */
    public function criteria()
    {
        return $this->hasMany(AssessmentCriterion::class, 'template_id');
    }

    /**
     * Récupère les critères personnalisés d'un utilisateur
     */
    public static function getUserCriteria($userId): Collection
    {
        return self::where('user_id', $userId)
            ->with('category')
            ->orderBy('position')
            ->get();
    }

    /**
     * Récupère les critères par défaut
     */
    public static function getDefaultCriteria(): Collection
    {
        return self::where('user_id', 0)
            ->with('category')
            ->orderBy('position')
            ->get();
    }

    /**
     * Récupère les critères actifs (personnalisés ou par défaut)
     */
    public static function getActiveCriteria($userId): Collection
    {
        $userCriteria = self::getUserCriteria($userId);
        
        // Si l'utilisateur n'a pas de critères personnalisés, utiliser les critères par défaut
        if ($userCriteria->isEmpty()) {
            return self::getDefaultCriteria();
        }
        
        return $userCriteria;
    }

    /**
     * Sauvegarde les critères personnalisés d'un utilisateur
     */
    public static function saveUserCriteria($criteriasData, $userId)
    {
        // Validation des données
        if (!is_array($criteriasData)) {
            Log::warning('Données de critères invalides', [
                'user_id' => $userId,
                'data_type' => gettype($criteriasData),
            ]);
            return false;
        }

        // Supprimer uniquement les critères personnalisés pour l'utilisateur
        self::where('user_id', $userId)->delete();

        foreach ($criteriasData as $index => $data) {
            // Limite à 8 critères maximum (positions 1-8)
            if ($index >= 8) {
                Log::warning('Tentative d\'assignation d\'une position supérieure à 8', [
                    'user_id' => $userId,
                    'invalid_index' => $index,
                    'criterias_data' => $criteriasData,
                ]);
                return false;
            }

            $position = $index + 1;

            // Créer un nouveau critère
            $criteria = new self();
            $criteria->name = $data['name'] ?? 'Critère ' . $position;
            $criteria->description = $data['description'] ?? '';
            $criteria->user_id = $userId;
            $criteria->position = $position;
            $criteria->assessment_criterion_category_id = $data['category_id'] ?? 1;

            $criteria->save();
        }

        return true;
    }

    /**
     * Réinitialise les critères personnalisés d'un utilisateur
     */
    public static function resetUserCriteria($userId)
    {
        // Supprimer uniquement les critères personnalisés pour l'utilisateur
        self::where('user_id', $userId)->delete();
        return 'Critères personnalisés réinitialisés avec succès !';
    }

    /**
     * Vérifie si c'est un critère par défaut
     */
    public function isDefault(): bool
    {
        return $this->user_id === 0;
    }

    /**
     * Vérifie si c'est un critère personnalisé
     */
    public function isCustom(): bool
    {
        return $this->user_id !== 0;
    }

    /**
     * Scope pour les critères par défaut
     */
    public function scopeDefault($query)
    {
        return $query->where('user_id', 0);
    }

    /**
     * Scope pour les critères personnalisés
     */
    public function scopeCustom($query, $userId = null)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }
        return $query->where('user_id', '>', 0);
    }

    /**
     * Scope pour trier par position
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('position');
    }

    /**
     * Retourne le nom de la catégorie
     */
    public function getCategoryNameAttribute()
    {
        return $this->category ? $this->category->name : 'Non catégorisé';
    }
}