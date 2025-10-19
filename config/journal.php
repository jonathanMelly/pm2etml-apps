<?php

return [
    // Types d'activités (gestion de projet) proposés pour chaque ligne
    // clé => [label (identique à la clé), icône FontAwesome, classe de couleur (daisyUI badge-*)]
    'activity_types' => [
        'Initiation' => [
            'icon' => 'fa-compass',
            'color' => 'badge-primary',
        ],
        'Planification' => [
            'icon' => 'fa-calendar-check',
            'color' => 'badge-info',
        ],
        'Réalisation' => [
            'icon' => 'fa-screwdriver-wrench',
            'color' => 'badge-success',
        ],
        'Tests & Qualité' => [
            'icon' => 'fa-vial-circle-check',
            'color' => 'badge-warning',
        ],
        'Documentation' => [
            'icon' => 'fa-file-lines',
            'color' => 'badge-secondary',
        ],
        'Communication' => [
            'icon' => 'fa-comments',
            'color' => 'badge-accent',
        ],
        'Gestion des risques' => [
            'icon' => 'fa-triangle-exclamation',
            'color' => 'badge-error',
        ],
        'Réunion/Coordination' => [
            'icon' => 'fa-people-group',
            'color' => 'badge-outline',
        ],
        'Livraison/Clôture' => [
            'icon' => 'fa-box',
            'color' => 'badge-neutral',
        ],
    ],

    // Suggestions d'appréciation (enseignant) regroupées par niveaux
    'appreciations' => [
        'Insuffisant' => [
            "Ne convient pas à ce qui est attendu",
            "Manque de précision",
            "Absence de réflexion personnelle",
            "Peu ou pas de critique",
            "Aucun lien ou référence fourni",
        ],
        'À renforcer' => [
            "À améliorer",
            "Quelques éléments pertinents, mais insuffisants",
            "Références ou liens à ajouter",
            "Approche critique à renforcer",
            "Développer davantage la réflexion personnelle",
        ],
        'Satisfaisant' => [
            "Satisfaisant",
            "Répond globalement aux attentes",
            "Contient des références et des liens utiles",
            "Présente une critique pertinente",
            "Intègre une réflexion personnelle clair",
        ],
    ],
];
