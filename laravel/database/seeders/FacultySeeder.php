<?php

namespace Database\Seeders;

use App\Models\Faculty;
use Illuminate\Database\Seeder;

class FacultySeeder extends Seeder
{
    /**
     * Seeds all 13 active faculties at Universitas Diponegoro.
     */
    public function run(): void
    {
        $faculties = [
            ['name' => 'Fakultas Teknik',                    'code' => 'FT'],
            ['name' => 'Fakultas Ekonomika dan Bisnis',      'code' => 'FEB'],
            ['name' => 'Fakultas Hukum',                     'code' => 'FH'],
            ['name' => 'Fakultas Kedokteran',                'code' => 'FK'],
            ['name' => 'Fakultas MIPA',                      'code' => 'FMIPA'],
            ['name' => 'Fakultas Peternakan dan Pertanian',  'code' => 'FPP'],
            ['name' => 'Fakultas Ilmu Sosial dan Ilmu Politik', 'code' => 'FISIP'],
            ['name' => 'Fakultas Psikologi',                 'code' => 'FPsi'],
            ['name' => 'Fakultas Ilmu Budaya',               'code' => 'FIB'],
            ['name' => 'Fakultas Kesehatan Masyarakat',      'code' => 'FKM'],
            ['name' => 'Fakultas Perikanan dan Ilmu Kelautan', 'code' => 'FPIK'],
            ['name' => 'Sekolah Vokasi',                     'code' => 'SV'],
            ['name' => 'Sekolah Pascasarjana',               'code' => 'SPs'],
            ['name' => 'Node Prototype (Test)',              'code' => 'TEST'],
        ];

        foreach ($faculties as $faculty) {
            Faculty::firstOrCreate(
                ['code' => $faculty['code']],
                array_merge($faculty, ['is_active' => true])
            );
        }
    }
}
