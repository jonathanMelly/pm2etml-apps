<?php
namespace Database\Seeders;

use App\Models\AssessmentCriterionCategory;
use Illuminate\Database\Seeder;
use App\Models\AssessmentCriterionTemplate;

class AssessmentCriterionTemplateSeeder extends Seeder
{
    public function run()
    {
        $professionalSkillsId = AssessmentCriterionCategory::where('name','=','Professional skills')->firstOrFail()->id;
        $methodologicalSkillsId = AssessmentCriterionCategory::where('name','=','Methodological skills')->firstOrFail()->id;
        $criterias = [
            ['name' => 'Régularité', 'assessment_criterion_category_id' => $professionalSkillsId, 'description' => "Assesses consistency and regularity in task performance.", 'position' => 1],
            ['name' => 'Qualité', 'assessment_criterion_category_id' => $professionalSkillsId, 'description' => "Measures the accuracy, conformity and quality of work performed.", 'position' => 2],
            ['name' => 'Maîtrise', 'assessment_criterion_category_id' => $professionalSkillsId, 'description' => "Assesses the level of mastery of technical skills specific to the position.", 'position' => 3],
            ['name' => 'Autonomie', 'assessment_criterion_category_id' => $professionalSkillsId, 'description' => "Measures the ability to work independently and manage tasks without constant supervision.", 'position' => 4],

            ['name' => 'Organisation', 'assessment_criterion_category_id' => $methodologicalSkillsId, 'description' => "Evaluates the effectiveness in organizing and structuring work processes.", 'position' => 5],
            ['name' => 'Communication', 'assessment_criterion_category_id' => $methodologicalSkillsId, 'description' => "Measures the clarity, relevance and effectiveness of verbal and written communication.", 'position' => 6],
            ['name' => 'Innovation', 'assessment_criterion_category_id' => $methodologicalSkillsId, 'description' => "Assesses the ability to innovate and integrate sustainable practices into the workplace.", 'position' => 7],

            ['name' => 'Esprit d’Équipe', 'assessment_criterion_category_id' => AssessmentCriterionCategory::where('name','=','Social skills')->firstOrFail()->id, 'description' => "Measures the ability to collaborate effectively within a multidisciplinary team.", 'position' => 8]
        ];

        foreach ($criterias as $criteria) {
            AssessmentCriterionTemplate::create(array_merge($criteria, ['user_id' => 0])); // On ajoute 'user_id' à chaque critère
        }
    }
}
