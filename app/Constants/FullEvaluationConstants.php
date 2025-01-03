<?php

namespace App\Constants;

class FullEvaluationConstants
{
   // Labels d'appréciation
   public const APPRECIATION_LABELS = ["NA", "PA", "A", "LA"];


   // Initialisation de l'état
   public const INITIAL_VISIBLE_CATEGORIES = [
      'PROFESSIONNELLES' => true,
      'METHODOLOGIQUES' => true,
      'SOCIALES' => true
   ];

   public const INITIAL_VISIBLE_CURSORS = [
      'auto80' => true,
      'auto100' => false,
      'eval80' => true,
      'eval100' => false
   ];

   // Liste des niveaux d'évaluation
   public const EVALUATION_LEVELS = ['auto80', 'eval80', 'auto100', 'eval100'];

   /**
    * Valide si le niveau d'évaluation est valide.
    *
    * @param string $level
    * @return bool
    */
   public static function isValidLevel(string $level): bool
   {
      return in_array($level, self::EVALUATION_LEVELS);
   }

   /**
    * Récupère l'index d'un niveau d'évaluation.
    *
    * @param string $level
    * @return int|null L'index du niveau ou null si non trouvé.
    */
   public static function getLevelIndex(string $level): ?int
   {
      // Cherche l'index du niveau dans le tableau EVALUATION_LEVELS
      $index = array_search($level, self::EVALUATION_LEVELS);

      // Si l'index est trouvé, on retourne cet index
      // Si non trouvé, on retourne null
      return $index === false ? null : $index;
   }

   // // valeur par défaut des critères 
   // public const CRITERIAS_LIST = [
   //    [
   //       'id' => 1,
   //       'name' => 'Régularité',
   //       'category' => 'PROFESSIONNELLES',
   //       'description' => "Évalue la constance et la régularité dans l'exécution des tâches."
   //    ],
   //    [
   //       'id' => 2,
   //       'name' => 'Qualité',
   //       'category' => 'PROFESSIONNELLES',
   //       'description' => "Mesure la précision, la conformité et la qualité du travail effectué."
   //    ],
   //    [
   //       'id' => 3,
   //       'name' => 'Maîtrise',
   //       'category' => 'PROFESSIONNELLES',
   //       'description' => "Évalue le niveau de maîtrise des compétences techniques spécifiques au poste."
   //    ],
   //    [
   //       'id' => 4,
   //       'name' => 'Autonomie',
   //       'category' => 'PROFESSIONNELLES',
   //       'description' => "Mesure la capacité à travailler de manière autonome et à gérer les tâches sans supervision constante."
   //    ],
   //    [
   //       'id' => 5,
   //       'name' => 'Organisation',
   //       'category' => 'METHODOLOGIQUES',
   //       'description' => "Évalue l'efficacité dans l'organisation et la structuration des processus de travail."
   //    ],
   //    [
   //       'id' => 6,
   //       'name' => 'Communication',
   //       'category' => 'METHODOLOGIQUES',
   //       'description' => "Mesure la clarté, la pertinence et l'efficacité de la communication verbale et écrite."
   //    ],
   //    [
   //       'id' => 7,
   //       'name' => 'Innovation',
   //       'category' => 'METHODOLOGIQUES',
   //       'description' => "Évalue la capacité à innover et à intégrer des pratiques durables dans le cadre du travail."
   //    ],
   //    [
   //       'id' => 8,
   //       'name' => 'Esprit d’Équipe',
   //       'category' => 'SOCIALES',
   //       'description' => "Mesure l'aptitude à collaborer efficacement au sein d'une équipe multidisciplinaire."
   //    ]
   // ];

   // Structure de sauvegarde JSON pour l'évaluation
   public const JSON_TEMPLATE = [
      'id' => '',              // Identifiant unique de l'évaluation
      'teacher' => '',       // Nom de l'évaluateur du projet
      'projectName' => '',     // Nom du projet
      'studentLastName' => '', // Nom de famille de l'étudiant
      'studentFirstName' => '', // Prénom de l'étudiant
      'studentClass' => '',    // Classe de l'étudiant
      'studentRemark' => '',   // Remarques générales sur l'étudiant
      'appreciations' => [     // Tableau des appréciations
         [
            'date' => '',        // Date de l'appréciation
            'level' => '',       // Niveau d'évaluation (auto, formative, summative, etc.)
            'criteria' => [      // Critères de cette appréciation
               [
                  'id' => '',      // Identifiant unique du critère
                  'name' => '',    // Nom du critère
                  'value' => '',   // Valeur attribuée au critère
                  'checked' => false, // Statut booléen du critère (vrai ou faux)
               ]
            ]
         ]
      ]
   ];
}
