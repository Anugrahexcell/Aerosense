<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Permanently remove Sekolah Pascasarjana and move Node Prototype (Test) from ID 14 to ID 13.
     */
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // 1. Hapus Sekolah Pascasarjana secara permanen
        $pasca = DB::table('faculties')
            ->where('id', 13)
            ->orWhere('name', 'like', '%Pascasarjana%')
            ->orWhere('code', 'SPs')
            ->first();

        if ($pasca) {
            DB::table('sensor_readings')->where('faculty_id', $pasca->id)->delete();
            DB::table('air_quality_trends')->where('faculty_id', $pasca->id)->delete();
            DB::table('air_quality_predictions')->where('faculty_id', $pasca->id)->delete();
            DB::table('faculties')->where('id', $pasca->id)->delete();
        }

        // 2. Pindahkan Node Prototype (Test) dari ID 14 ke ID 13
        $proto14 = DB::table('faculties')->where('id', 14)->first();
        $protoAny = DB::table('faculties')->where('name', 'like', '%Prototype%')->first();

        $protoTarget = $proto14 ?: $protoAny;

        if ($protoTarget) {
            // Update relasi dari ID lama ke ID 13
            DB::table('sensor_readings')->where('faculty_id', $protoTarget->id)->update(['faculty_id' => 13]);
            DB::table('air_quality_trends')->where('faculty_id', $protoTarget->id)->update(['faculty_id' => 13]);
            DB::table('air_quality_predictions')->where('faculty_id', $protoTarget->id)->update(['faculty_id' => 13]);

            // Update faculty record menjadi ID 13
            DB::table('faculties')->where('id', $protoTarget->id)->delete();
            DB::table('faculties')->insert([
                'id'         => 13,
                'name'       => 'Node Prototype (Test)',
                'code'       => 'TEST',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            // Jika belum ada record prototype, buatkan langsung di ID 13
            DB::table('faculties')->insert([
                'id'         => 13,
                'name'       => 'Node Prototype (Test)',
                'code'       => 'TEST',
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Pastikan tidak ada lagi ID 14
        DB::table('faculties')->where('id', 14)->delete();

        // 4. Reset auto increment agar data berikutnya mulai dari 14
        DB::statement('ALTER TABLE faculties AUTO_INCREMENT = 14');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed as Pascasarjana is permanently removed per request
    }
};

