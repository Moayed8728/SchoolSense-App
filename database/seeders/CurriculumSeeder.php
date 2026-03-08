<?php

namespace Database\Seeders;

use App\Models\Curriculum;
use Illuminate\Database\Seeder;

class CurriculumSeeder extends Seeder
{
    public function run(): void
    {
        $curricula = [
            ['name' => 'American', 'slug' => 'american'],
            ['name' => 'British', 'slug' => 'british'],
            ['name' => 'International Baccalaureate (IB)', 'slug' => 'ib'],
            ['name' => 'Cambridge', 'slug' => 'cambridge'],

            ['name' => 'International (General)', 'slug' => 'international'],
            ['name' => 'Indian (CBSE)', 'slug' => 'indian-cbse'],
            ['name' => 'Pakistani', 'slug' => 'pakistani'],
            ['name' => 'Philippine', 'slug' => 'philippine'],

            ['name' => 'French', 'slug' => 'french'],
            ['name' => 'German', 'slug' => 'german'],
        ];

        foreach ($curricula as $curriculum) {
            Curriculum::updateOrCreate(
                ['slug' => $curriculum['slug']],
                ['name' => $curriculum['name']]
            );
        }
    }
}