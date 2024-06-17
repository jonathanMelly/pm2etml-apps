<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $fn = $this->faker->firstName();
        $ln = $this->faker->lastName();

        //$email = $fn.$ln.'@'.$faker->safeEmailDomain();
        $email = $this->faker->unique()->email(); // strtolower($fn).'.'.strtolower($ln).'@eduvaud.ch';

        return [
            'username' => 'p'.$this->faker->randomLetter().$this->faker->randomNumber(2).$this->faker->lexify('???').'@eduvaud.ch',
            'firstname' => $fn,
            'lastname' => $ln,
            'email' => $email,
            'remember_token' => Str::random(10),
        ];
    }
}
