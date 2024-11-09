<?php

namespace Database\Factories;

use App\Models\categorie;
use App\Models\topic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\topic>
 */
class topicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = topic::class;

    public function definition(): array
    {
        $cateogry = categorie::factory()->create();

        return [
            'name' => fake()->unique()->words(3, true),
            'categorie_id' => $cateogry->id,
        ];
    }
}
