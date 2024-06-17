<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class AcademicPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = now()->year;
        for ($i = -5; $i < 100; $i++) {
            $start = CarbonImmutable::create($currentYear + $i, 8, 1);
            $end = $start->addYear()->subDay();
            AcademicPeriod::create([
                'start' => $start,
                'end' => $end,
            ]);
        }
    }
}
