<?php

namespace Database\Factories;

use App\Constants\MorphTargets;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobDefinitionMainImageAttachment>
 */
class JobDefinitionMainImageAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->text(10).'.png',
            'storage_path' => $this->faker->text(10).'-storage.png',
            'attachable_type' => MorphTargets::MORPH2_JOB_DEFINITION,
        ];
    }
}
