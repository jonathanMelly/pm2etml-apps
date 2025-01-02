<?php

use Illuminate\Database\Seeder;
use App\Models\DefaultCriteria;

class DefaultCriteriaSeeder extends Seeder
{
    public function run()
    {
        $criterias = [
            ['criteria_name' => 'Régularité', 'category' => 'Professional', 'description' => "Assesses consistency and regularity in task performance.", 'position' => 1],
            ['criteria_name' => 'Qualité', 'category' => 'Professional', 'description' => "Measures the accuracy, conformity and quality of work performed.", 'position' => 2],
            ['criteria_name' => 'Maîtrise', 'category' => 'Professional', 'description' => "Assesses the level of mastery of technical skills specific to the position.", 'position' => 3],
            ['criteria_name' => 'Autonomie', 'category' => 'Professional', 'description' => "Measures the ability to work independently and manage tasks without constant supervision.", 'position' => 4],
            ['criteria_name' => 'Organisation', 'category' => 'Methodological', 'description' => "Evaluates the effectiveness in organizing and structuring work processes.", 'position' => 5],
            ['criteria_name' => 'Communication', 'category' => 'Methodological', 'description' => "Measures the clarity, relevance and effectiveness of verbal and written communication.", 'position' => 6],
            ['criteria_name' => 'Innovation', 'category' => 'Methodological', 'description' => "Assesses the ability to innovate and integrate sustainable practices into the workplace.", 'position' => 7],
            ['criteria_name' => 'Esprit d’Équipe', 'category' => 'Social', 'description' => "Measures the ability to collaborate effectively within a multidisciplinary team.", 'position' => 8]
        ];

        foreach ($criterias as $criteria) {
            DefaultCriteria::create(array_merge($criteria, ['user_id' => 0])); // On ajoute 'user_id' à chaque critère
        }
    }
}
