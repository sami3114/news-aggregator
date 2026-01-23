<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,      // 10 users
            AuthorSeeder::class,    // 8 authors
            CategorySeeder::class,  // 10 categories
            ArticleSeeder::class,   // 15 articles with relationships
        ]);
    }
}
