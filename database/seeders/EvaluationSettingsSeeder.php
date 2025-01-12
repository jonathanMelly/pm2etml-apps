<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EvaluationSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'appreciationLabels',
                'value' => json_encode(["NA", "PA", "A", "LA"])
            ],
            [
                'key' => 'initialVisibleCategories',
                'value' => json_encode([
                    'PROFESSIONNELLES' => true,
                    'METHODOLOGIQUES' => true,
                    'SOCIALES' => true
                ])
            ],
            [
                'key' => 'initialVisibleCursors',
                'value' => json_encode([
                    'auto80' => true,
                    'auto100' => false,
                    'eval80' => true,
                    'eval100' => false
                ])
            ]
        ];

        foreach ($settings as $setting) {
            // Utiliser updateOrInsert pour éviter la duplication de la clé
            DB::table('evaluation_settings')->updateOrInsert(
                ['key' => $setting['key']], // Chercher la clé
                ['value' => $setting['value']] // Insérer ou mettre à jour la valeur
            );
        }
    }
}
