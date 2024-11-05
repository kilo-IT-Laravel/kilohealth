<?php

namespace Database\Factories;

use App\Models\categorie;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Categorie>
 */
class categorieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = categorie::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word() . '-' . $this->faker->unique()->numberBetween(1, 10000);
        $slug = Str::slug($name);
        return [
            'name' => $name,
            'slug' => $slug,
            'icon' => fake()->imageUrl(100, 100)
        ];
    }
}
