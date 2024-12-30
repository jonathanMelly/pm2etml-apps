<?php

namespace App\Services;

use Illuminate\Support\Str;

class EvaluationBuilder
{
   /**
    * Créer un JSON basé sur la structure template avec les données fournies.
    *
    * @param object $project         Données du projet (incluant son nom).
    * @param object $evaluator       Données de l'évaluateur (nom, prénom).
    * @param object $student         Données de l'étudiant (nom, prénom, classe).
    * @param array  $appreciations   Données facultatives des appréciations.
    * @return array
    */
   public static function createJson($project, $evaluator, $student, $appreciations = [])
   {
      // Charger la structure de base
      $json = [
         'id' => Str::uuid()->toString(),
         'evaluator' => $evaluator->firstname . ' ' . $evaluator->lastname,
         'projectName' => $project->title,
         'studentLastName' => $student->lastname,
         'studentFirstName' => $student->firstname,
         'studentClass' => $student->class ?? 'Non défini',
         'studentRemark' => '',
         'appreciations' => $appreciations,
      ];

      return $json;
   }

   /**
    * Ajouter une appréciation au JSON.
    *
    * @param array  $json            Le JSON dans lequel insérer l'appréciation.
    * @param string $date            Date de l'appréciation.
    * @param string $level           Niveau de l'évaluation.
    * @param array  $criteria        Liste des critères pour cette appréciation.
    * @return void
    */
   public static function addAppreciation(&$json, $date, $level, $criteria)
   {
      $json['appreciations'][] = [
         'date' => $date,
         'level' => $level,
         'criteria' => $criteria,
      ];
   }
}
