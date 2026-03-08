<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['name' => 'English', 'slug' => 'english'],
            ['name' => 'Arabic', 'slug' => 'arabic'],
            ['name' => 'French', 'slug' => 'french'],
            ['name' => 'Spanish', 'slug' => 'spanish'],
            ['name' => 'German', 'slug' => 'german'],
            ['name' => 'Italian', 'slug' => 'italian'],
            ['name' => 'Portuguese', 'slug' => 'portuguese'],
            ['name' => 'Russian', 'slug' => 'russian'],
            ['name' => 'Turkish', 'slug' => 'turkish'],
            ['name' => 'Dutch', 'slug' => 'dutch'],
            ['name' => 'Polish', 'slug' => 'polish'],
            ['name' => 'Romanian', 'slug' => 'romanian'],
            ['name' => 'Hungarian', 'slug' => 'hungarian'],
            ['name' => 'Czech', 'slug' => 'czech'],
            ['name' => 'Japanese', 'slug' => 'japanese'],
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate(
                ['slug' => $language['slug']],
                ['name' => $language['name']]
            );
        }
    }
}