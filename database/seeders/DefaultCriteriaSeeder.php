<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DefaultCriteria;

class DefaultCriteriaSeeder extends Seeder
{
    public function run()
    {
        DefaultCriteria::where('user_id', '=', '0')->delete();
        $criterias = [
            ['name' => 'Régularité', 'category' => 'Professional', 'description' => "Assesses consistency and regularity in task performance.", 'position' => 1],
            ['name' => 'Qualité', 'category' => 'Professional', 'description' => "Measures the accuracy, conformity and quality of work performed.", 'position' => 2],
            ['name' => 'Maîtrise', 'category' => 'Professional', 'description' => "Assesses the level of mastery of technical skills specific to the position.", 'position' => 3],
            ['name' => 'Autonomie', 'category' => 'Professional', 'description' => "Measures the ability to work independently and manage tasks without constant supervision.", 'position' => 4],
            ['name' => 'Organisation', 'category' => 'Methodological', 'description' => "Evaluates the effectiveness in organizing and structuring work processes.", 'position' => 5],
            ['name' => 'Communication', 'category' => 'Methodological', 'description' => "Measures the clarity, relevance and effectiveness of verbal and written communication.", 'position' => 6],
            ['name' => 'Innovation', 'category' => 'Methodological', 'description' => "Assesses the ability to innovate and integrate sustainable practices into the workplace.", 'position' => 7],
            ['name' => 'Esprit d’Équipe', 'category' => 'Social', 'description' => "Measures the ability to collaborate effectively within a multidisciplinary team.", 'position' => 8]
        ];

        foreach ($criterias as $criteria) {
            DefaultCriteria::create(array_merge($criteria, ['user_id' => 0])); // On ajoute 'user_id' à chaque critère
        }
    }
}
