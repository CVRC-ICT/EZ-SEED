<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MunicipalitySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Batanes' => [
                'Basco', 'Itbayat', 'Ivana', 'Mahatao', 'Sabtang', 'Uyugan',
            ],
            'Cagayan' => [
                'Abulug', 'Alcala', 'Allacapan', 'Amulung', 'Aparri', 'Baggao',
                'Ballesteros', 'Buguey', 'Calayan', 'Camalaniugan', 'Claveria',
                'Enrile', 'Gattaran', 'Gonzaga', 'Iguig', 'Lal-lo', 'Lasam',
                'Pamplona', 'Peñablanca', 'Piat', 'Rizal', 'Sanchez Mira',
                'Santa Ana', 'Santa Praxedes', 'Santa Teresita', 'Santo Niño',
                'Solana', 'Tuao', 'Tuguegarao City',
            ],
            'Isabela' => [
                'Alicia', 'Angadanan', 'Aurora', 'Benito Soliven', 'Burgos',
                'Cabagan', 'Cabatuan', 'Cauayan City', 'Cordon', 'Delfin Albano',
                'Dinapigue', 'Divilacan', 'Echague', 'Gamu', 'Ilagan City',
                'Jones', 'Luna', 'Maconacon', 'Mallig', 'Naguilian', 'Palanan',
                'Quezon', 'Quirino', 'Ramon', 'Reina Mercedes', 'Roxas',
                'San Agustin', 'San Guillermo', 'San Isidro', 'San Manuel',
                'San Mariano', 'San Mateo', 'San Pablo', 'Santa Maria', 'Santiago City',
                'Santo Tomas', 'Tumauini',
            ],
            'Nueva Vizcaya' => [
                'Alfonso Castañeda', 'Ambaguio', 'Aritao', 'Bagabag', 'Bambang',
                'Bayombong', 'Diadi', 'Dupax del Norte', 'Dupax del Sur',
                'Kasibu', 'Kayapa', 'Quezon', 'Solano', 'Villaverde',
            ],
            'Quirino' => [
                'Aglipay', 'Cabarroguis', 'Diffun', 'Maddela', 'Nagtipunan',
                'Saguday',
            ],
        ];

        foreach ($data as $provinceName => $municipalities) {
            $province = DB::table('provinces')->where('name', $provinceName)->first();

            if (! $province) {
                continue;
            }

            foreach ($municipalities as $index => $municipalityName) {
                DB::table('municipalities')->updateOrInsert(
                    [
                        'province_id' => $province->id,
                        'name' => $municipalityName,
                    ],
                    [
                        'code' => strtoupper(substr($provinceName, 0, 3)) . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}