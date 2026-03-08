<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;


class SchoolSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $schools = [
            [
                'name' => 'American International School of Jeddah (AISJ)',
                'websiteUrl' => 'http://aisj.edu.sa/',
            ],
            [
                'name' => 'Jeddah Knowledge International School (JKS)',
                'websiteUrl' => 'https://www.jks.edu.sa/',
            ],
            [
                'name' => 'British International School of Jeddah (BISJ)',
                'websiteUrl' => 'https://bis-jeddah.com/',
            ],
            [
                'name' => 'Jeddah Prep and Grammar School (JPGS)',
                'websiteUrl' => 'https://www.jpgs.org/',
            ],
            [
                'name' => 'Jeddah Campus International School',
                'websiteUrl' => 'https://jcs.edu.sa/',
            ],
            [
                'name' => 'Jeddah International School (JIS)',
                'websiteUrl' => 'https://jischool.org/',
            ],
            [
                'name' => 'Alhejaz International School',
                'websiteUrl' => 'https://alhejaz.edu.sa/',
            ],
            [
                'name' => 'Jeddah Private International Schools (JPIS)',
                'websiteUrl' => 'https://jps.edu.sa/',
            ],
            [
                'name' => 'Al Hukamaa International School',
                'websiteUrl' => 'https://hukamaainternationalschools.com/',
            ],
            [
                'name' => 'Al Fakhama International School (Directory Listing)',
                'websiteUrl' => 'https://yaschools.com/en/school/al-fakhama-international-school',
            ],
            [
                'name' => 'Dauha Al-Uloom International School',
                'websiteUrl' => 'https://www.dauhaschool.com/',
            ],
            [
                'name' => 'International Philippine School in Jeddah (IPSJ)',
                'websiteUrl' => 'https://ipsjksa.com/',
            ],
            [
                'name' => 'Pakistan International School Jeddah (Cambridge Section Portal)',
                'websiteUrl' => 'https://pisj-ccs.edupage.org/',
            ],
            [
                'name' => 'International Indian School, Jeddah (IISJED)',
                'websiteUrl' => 'https://www.iisjedinfo.com/',
            ],
            [
                'name' => 'German International School Jeddah (DISJ)',
                'websiteUrl' => 'https://disj.de/',
            ],
        ];

        foreach ($schools as $row) {
            School::updateOrCreate(
                ['websiteUrl' => $row['websiteUrl']],
                [
                    'name' => $row['name'],
                    'country' => 'SA',
                    'city' => 'Jeddah',
                    'address' => null,

                    'websiteUrl' => $row['websiteUrl'],
                    'contactEmail' => null,
                    'contactPhone' => null,
                    'contactPageUrl' => null,

                    'description' => 'Seeded from publicly available official website link. Detailed data to be verified by school representative or admin.',
                    'feesMin' => null,
                    'feesMax' => null,
                    'currency' => 'SAR',
                    'feePeriod' => 'yearly',
                ]
            );
        }
    }
}