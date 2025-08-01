<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentCriterionCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Casts pour les types de données
    protected $casts = [
        'name' => 'string',
    ];

    /**
     * Relation: UNE CATÉGORIE a PLUSIEURS TEMPLATES
     * AssessmentCriterionCategory (1) → (N) AssessmentCriterionTemplate
     */
    public function templates(): HasMany
    {
        return $this->hasMany(AssessmentCriterionTemplate::class, 'assessment_criterion_category_id');
    }

    /**
     * Relation: UNE CATÉGORIE a PLUSIEURS CRITÈRES (via les templates)
     * AssessmentCriterionCategory (1) → (N) AssessmentCriterion
     */
    public function criteria(): HasMany
    {
        return $this->hasMany(AssessmentCriterion::class, 'template_id')
                    ->join('assessment_criterion_templates', 'assessment_criterion_templates.id', '=', 'assessment_criteria.template_id')
                    ->whereColumn('assessment_criterion_templates.assessment_criterion_category_id', 'assessment_criteria.template_id');
    }

    /**
     * Récupère une catégorie par son nom
     */
    public static function findByName(string $name)
    {
        return self::where('name', $name)->first();
    }

    /**
     * Crée une catégorie si elle n'existe pas déjà
     */
    public static function findOrCreate(string $name)
    {
        $category = self::findByName($name);
        if (!$category) {
            $category = self::create(['name' => $name]);
        }
        return $category;
    }

    /**
     * Retourne le nombre de templates dans cette catégorie
     */
    public function getTemplateCountAttribute()
    {
        return $this->templates()->count();
    }

    /**
     * Retourne le nombre de critères utilisant cette catégorie
     */
    public function getCriteriaCountAttribute()
    {
        return $this->templates()->join('assessment_criteria', 'assessment_criteria.template_id', '=', 'assessment_criterion_templates.id')->count();
    }

    /**
     * Scope pour rechercher par nom
     */
    public function scopeByName($query, $name)
    {
        return $query->where('name', 'like', '%' . $name . '%');
    }

    /**
     * Scope pour trier par nom
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    /**
     * Mutateur pour s'assurer que le nom est propre
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = trim(ucfirst(strtolower($value)));
    }

    /**
     * Accesseur pour obtenir le nom avec la première lettre en majuscule
     */
    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }
}