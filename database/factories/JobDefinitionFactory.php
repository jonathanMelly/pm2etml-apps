<?php

namespace Database\Factories;

use App\Enums\JobPriority;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobDefinition>
 */
class JobDefinitionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {

        $names=['Android task monitoring',
            'Sentiment analysis for product rating',
            'Fingerprint-based ATM system',
            'Advanced employee management system',
            'Image encryption using AES algorithm',
            'Fingerprint voting system',
            'Weather forecasting system',
            'Android local train ticketing system',
            'Railway tracking and arrival time prediction system',
            'Android Patient Tracker',
            'Opinion mining for social networking platforms',
            'Automated payroll system with GPS tracking and image capture',
            'Data leakage detection system',
            'Credit card fraud detection',
            'AI shopping system',
            'Camera motion sensor system',
            'Bug tracker',
            'e-Learning platform',
            'Smart health prediction system',
            'Software piracy protection system'];

        return [
            'name' => $this->faker->randomElement($names).' Version '.$this->faker->randomDigit().'.'.$this->faker->randomDigit(),
            'published_date' => $this->faker->date,
            'priority' => $this->faker->numberBetween(JobPriority::cases()[0]->value,JobPriority::cases()[count(JobPriority::cases())-1]->value),
            'description' => $this->faker->realText(150),
            'max_workers' => $this->faker->numberBetween(1,5),
            'required_xp_years' => $this->faker->numberBetween(0,3),
        ];
    }
}