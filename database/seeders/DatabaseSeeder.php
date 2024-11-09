<?php

namespace Database\Seeders;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            gen_user_roleandper::class, // php artisan db:seed --class=gen_user_roleandper
            categorieSeeder::class, // php artisan db:seed --class=categorieSeeder
            topicSeeder::class, // php artisan db:seed --class=topicSeeder
            
        ]);
    }
}
