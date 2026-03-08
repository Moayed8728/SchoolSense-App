<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $activities = [
            ['name' => 'Robotics', 'slug' => 'robotics'],
            ['name' => 'Coding Club', 'slug' => 'coding-club'],
            ['name' => 'STEM Lab', 'slug' => 'stem-lab'],
            ['name' => 'Science Club', 'slug' => 'science-club'],

            ['name' => 'Football', 'slug' => 'football'],
            ['name' => 'Basketball', 'slug' => 'basketball'],
            ['name' => 'Swimming', 'slug' => 'swimming'],
            ['name' => 'Martial Arts', 'slug' => 'martial-arts'],

            ['name' => 'Debate', 'slug' => 'debate'],
            ['name' => 'Model United Nations (MUN)', 'slug' => 'mun'],
            ['name' => 'Drama / Performing Arts', 'slug' => 'drama-performing-arts'],
            ['name' => 'Music', 'slug' => 'music'],
            ['name' => 'Art', 'slug' => 'art'],

            ['name' => 'Community Service', 'slug' => 'community-service'],
            ['name' => 'Chess', 'slug' => 'chess'],
        ];

        foreach ($activities as $activity) {
            Activity::updateOrCreate(
                ['slug' => $activity['slug']],
                ['name' => $activity['name']]
            );
        }
    }
}