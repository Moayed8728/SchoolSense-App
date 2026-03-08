<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\SchoolSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\CurriculumSeeder;
use Database\Seeders\ActivitySeeder;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\SchoolFeeBandSeeder;
use Database\Seeders\SchoolContactSourceSeeder;
use Database\Seeders\SchoolContactExtractionSeeder;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SchoolSeeder::class,
        ]);

        $this->call([
            UserSeeder::class,
        ]);

        $this->call([
            CurriculumSeeder::class,
        ]);

        $this->call([
            ActivitySeeder::class,
        ]);

        $this->call([
            LanguageSeeder::class,
        ]);

        
    }
}
