<?php

namespace Database\Seeders;

use App\Models\Faculty;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FacultySeeder extends Seeder
{
    /**
     * Seeds the 12 active faculties at Universitas Diponegoro.
     * Faculty IDs are locked to 1–12 to match the IoT node FacultyId
     * assignments on each Arduino/ESP32 device (see image: FacultyId table).
     */
    public function run(): void
    {
        $faculties = [
            ['id' => 1,  'name' => 'Fakultas Teknik',                       'code' => 'FT'],
            ['id' => 2,  'name' => 'Fakultas Ekonomika dan Bisnis',         'code' => 'FEB'],
            ['id' => 3,  'name' => 'Fakultas Hukum',                        'code' => 'FH'],
            ['id' => 4,  'name' => 'Fakultas Kedokteran',                   'code' => 'FK'],
            ['id' => 5,  'name' => 'Fakultas Sains dan Matematika',         'code' => 'FSM'],
            ['id' => 6,  'name' => 'Fakultas Peternakan dan Pertanian',     'code' => 'FPP'],
            ['id' => 7,  'name' => 'Fakultas Ilmu Sosial dan Ilmu Politik', 'code' => 'FISIP'],
            ['id' => 8,  'name' => 'Fakultas Psikologi',                    'code' => 'FPsi'],
            ['id' => 9,  'name' => 'Fakultas Ilmu Budaya',                  'code' => 'FIB'],
            ['id' => 10, 'name' => 'Fakultas Kesehatan Masyarakat',         'code' => 'FKM'],
            ['id' => 11, 'name' => 'Fakultas Perikanan dan Ilmu Kelautan',  'code' => 'FPIK'],
            ['id' => 12, 'name' => 'Sekolah Vokasi',                        'code' => 'SV'],
            ['id' => 13, 'name' => 'Node Prototype (Test)',                'code' => 'TEST'],
        ];

        // Temporarily allow manual ID insertion on the auto-increment column
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Hapus Sekolah Pascasarjana secara permanen jika masih ada
        Faculty::where('name', 'like', '%Pascasarjana%')->orWhere('code', 'SPs')->delete();
        // Hapus node 14 jika ada
        Faculty::where('id', 14)->delete();

        foreach ($faculties as $faculty) {
            Faculty::unguarded(function () use ($faculty) {
                Faculty::updateOrCreate(
                    ['id' => $faculty['id']],
                    array_merge($faculty, ['is_active' => true])
                );
            });
        }

        // Restore auto-increment to 14 so future inserts don't collide
        DB::statement('ALTER TABLE faculties AUTO_INCREMENT = 14');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
