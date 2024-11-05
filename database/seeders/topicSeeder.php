<?php

namespace Database\Seeders;

use App\Models\categorie;
use App\Models\topic as ModelsTopic;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class topicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        categorie::all()->each(function ($category) {
            ModelsTopic::factory()->count(5)->create(['categorie_id' => $category->id]);
        });
    }
}
