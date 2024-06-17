<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            'devops:integration continue',
            'devops:github actions',
            'devops:lamp avec docker compose',
            'prog:calculatrice mvvm',
            'prog:classification avec ia',
            'prog:tri rapide',
            'web:php e-commerce',
            'web:integration de ckeditor',
            'web:tailwind css',
            'infra:dns multicast',
            'infra:load balancer',
            'infra:système voip',
        ] as $skillAndGroup) {
            Skill::firstOrCreateFromString($skillAndGroup);
        }

    }
}
