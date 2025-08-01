<?php

namespace Database\Seeders;

use App\Models\AssessmentCriterionCategory;
use Illuminate\Database\Seeder;
use App\Models\AssessmentCriterionTemplate;
use Illuminate\Support\Facades\DB;

class AssessmentCriterionTemplateSeeder extends Seeder
{
    public function run()
    {
        // Vérifier que les catégories existent, sinon les créer
        $professionalSkills = AssessmentCriterionCategory::firstOrCreate(
            ['name' => 'Professional skills'],
            ['name' => 'Professional skills']
        );
        
        $methodologicalSkills = AssessmentCriterionCategory::firstOrCreate(
            ['name' => 'Methodological skills'],
            ['name' => 'Methodological skills']
        );
        
        $socialSkills = AssessmentCriterionCategory::firstOrCreate(
            ['name' => 'Social skills'],
            ['name' => 'Social skills']
        );

        $criterias = [
            ['name' => 'Régularité', 'assessment_criterion_category_id' => $professionalSkills->id, 'description' => "Assesses consistency and regularity in task performance.", 'position' => 1],
            ['name' => 'Qualité', 'assessment_criterion_category_id' => $professionalSkills->id, 'description' => "Measures the accuracy, conformity and quality of work performed.", 'position' => 2],
            ['name' => 'Maîtrise', 'assessment_criterion_category_id' => $professionalSkills->id, 'description' => "Assesses the level of mastery of technical skills specific to the position.", 'position' => 3],
            ['name' => 'Autonomie', 'assessment_criterion_category_id' => $professionalSkills->id, 'description' => "Measures the ability to work independently and manage tasks without constant supervision.", 'position' => 4],

            ['name' => 'Organisation', 'assessment_criterion_category_id' => $methodologicalSkills->id, 'description' => "Evaluates the effectiveness in organizing and structuring work processes.", 'position' => 5],
            ['name' => 'Communication', 'assessment_criterion_category_id' => $methodologicalSkills->id, 'description' => "Measures the clarity, relevance and effectiveness of verbal and written communication.", 'position' => 6],
            ['name' => 'Innovation', 'assessment_criterion_category_id' => $methodologicalSkills->id, 'description' => "Assesses the ability to innovate and integrate sustainable practices into the workplace.", 'position' => 7],

            ['name' => 'Esprit d\'Équipe', 'assessment_criterion_category_id' => $socialSkills->id, 'description' => "Measures the ability to collaborate effectively within a multidisciplinary team.", 'position' => 8]
        ];

        foreach ($criterias as $criteria) {
            // Utiliser updateOrInsert pour éviter les doublons
            DB::table('assessment_criterion_templates')->updateOrInsert(
                [
                    'name' => $criteria['name'],
                    'user_id' => null // NULL au lieu de 0 pour les templates système
                ],
                array_merge($criteria, ['user_id' => null, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}