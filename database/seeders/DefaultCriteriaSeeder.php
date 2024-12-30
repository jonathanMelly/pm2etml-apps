<?php

use Illuminate\Database\Seeder;
use App\Models\DefaultCriteria;

class DefaultCriteriaSeeder extends Seeder
{
    public function run()
    {
        $criterias = [
            ['criteria_name' => 'Régularité', 'category' => 'PROFESSIONNELLES', 'description' => "Évalue la constance et la régularité dans l'exécution des tâches.", 'position' => 1],
            ['criteria_name' => 'Qualité', 'category' => 'PROFESSIONNELLES', 'description' => "Mesure la précision, la conformité et la qualité du travail effectué.", 'position' => 2],
            ['criteria_name' => 'Maîtrise', 'category' => 'PROFESSIONNELLES', 'description' => "Évalue le niveau de maîtrise des compétences techniques spécifiques au poste.", 'position' => 3],
            ['criteria_name' => 'Autonomie', 'category' => 'PROFESSIONNELLES', 'description' => "Mesure la capacité à travailler de manière autonome et à gérer les tâches sans supervision constante.", 'position' => 4],
            ['criteria_name' => 'Organisation', 'category' => 'METHODOLOGIQUES', 'description' => "Évalue l'efficacité dans l'organisation et la structuration des processus de travail.", 'position' => 5],
            ['criteria_name' => 'Communication', 'category' => 'METHODOLOGIQUES', 'description' => "Mesure la clarté, la pertinence et l'efficacité de la communication verbale et écrite.", 'position' => 6],
            ['criteria_name' => 'Innovation', 'category' => 'METHODOLOGIQUES', 'description' => "Évalue la capacité à innover et à intégrer des pratiques durables dans le cadre du travail.", 'position' => 7],
            ['criteria_name' => 'Esprit d’Équipe', 'category' => 'SOCIALES', 'description' => "Mesure l'aptitude à collaborer efficacement au sein d'une équipe multidisciplinaire.", 'position' => 8]
        ];

        foreach ($criterias as $criteria) {
            DefaultCriteria::create(array_merge($criteria, ['user_id' => 0])); // On ajoute 'user_id' à chaque critère
        }
    }
}
