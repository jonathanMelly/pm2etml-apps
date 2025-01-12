<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationSetting extends Model
{
    protected $table = 'evaluation_settings';

    // Cast 'value' en tableau automatiquement si applicable
    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Récupère un paramètre en fonction de sa clé
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getByKey(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    /**
     * Met à jour ou crée un paramètre.
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public static function setByKey(string $key, $value): self
    {
        return self::updateOrCreate(
            ['key' => $key], // Rechercher par clé
            ['value' => is_array($value) ? json_encode($value) : $value] // Sauvegarder le JSON si nécessaire
        );
    }


    // Ajouter une méthode pour récupérer les paramètres visibles
    public static function getVisibleCursors()
    {
        $setting = self::where('key', 'initialVisibleCursors')->first();

        if ($setting) {
            return $setting->value;
        }

        // Retourner une valeur par défaut ou gérer l'exception
        return []; // ou une valeur par défaut appropriée
    }
}
