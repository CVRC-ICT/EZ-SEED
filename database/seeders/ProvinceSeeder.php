<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = [
            ['name' => 'Batanes',        'code' => '0209'],
            ['name' => 'Cagayan',        'code' => '0215'],
            ['name' => 'Isabela',        'code' => '0231'],
            ['name' => 'Nueva Vizcaya',  'code' => '0250'],
            ['name' => 'Quirino',        'code' => '0257'],
        ];

        foreach ($provinces as $province) {
            DB::table('provinces')->updateOrInsert(
                ['name' => $province['name']],
                [
                    'code' => $province['code'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}