<?php

namespace Database\Seeders;

use App\Models\AssessmentCriterionCategory;
use Illuminate\Database\Seeder;

class AssesmentCriterionCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Professional skills'],
            ['name' => 'Methodological skills'],
            ['name' =>  'Social skills']
        ];

        foreach ($categories as $category) {
            AssessmentCriterionCategory::create($category);
        }
    }
}
