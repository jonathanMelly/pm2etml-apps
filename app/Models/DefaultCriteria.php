<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefaultCriteria extends Model
{
    use HasFactory;

    protected $fillable = [
        'criteria_name',
        'category',
        'description',
        'user_id',
        'position'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function getUserCriterias($userId)
    {
        return self::where('user_id', $userId)->orderBy('position')->get();
    }

    public static function getDefaultCriterias()
    {
        return self::where('user_id', 0)->orderBy('position')->get();
    }


    public static function saveUserCriterias($criteriasData, $userId)
    {
        // Supprimer uniquement les critères personnalisés pour l'utilisateur connecté
        self::where('user_id', $userId)->delete();

        foreach ($criteriasData as $index => $data) {
            // Si l'index dépasse 8, on log et on arrête la fonction
            if ($index >= 8) {
                Log::warning('Tentative d\'assignation d\'une position supérieure à 8 pour l\'utilisateur', [
                    'user_id' => $userId,
                    'invalid_index' => $index,
                    'criterias_data' => $criteriasData,
                ]);
                return false; // Quitte la fonction
            }

            // La position est l'index + 1
            $position = $index + 1;

            // On crée un nouveau critère ou met à jour l'existant pour l'utilisateur
            $criteria = self::where('user_id', $userId)->where('position', $position)->first() ?? new self();

            $criteria->name = $data['name'];
            $criteria->category = $data['category'];
            $criteria->description = $data['description'];
            $criteria->user_id = $userId; // Associe le critère à l'utilisateur connecté
            $criteria->position = $position; // Utilise la colonne position pour l'ordre

            $criteria->save();
        }

        return true;
    }


    public static function resetUserCriterias($userId)
    {
        // Supprimer uniquement les critères personnalisés pour l'utilisateur connecté
        self::where('user_id', $userId)->delete();

        return 'Critères personnalisés réinitialisés avec succès !';
    }

    public static function cloneDefaultCriteriasForUser($userId)
    {
        $defaultCriterias = self::where('user_id', 0)->orderBy('position')->get();

        foreach ($defaultCriterias as $defaultCriteria) {
            $criteria = $defaultCriteria->replicate();
            $criteria->user_id = $userId;
            $criteria->id = null; // Laisse Laravel générer un nouvel ID
            $criteria->save();
        }

        return 'Critères par défaut clonés pour l\'utilisateur avec succès !';
    }
}
