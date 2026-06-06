<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbsensiSeeder extends Seeder
{
    public function run(): void {
        DB::table('absensi')->insert([
            [
                'user_id'    => 1,
                'tanggal'    => now()->toDateString(),
                'status'     => 'hadir',
                'jam_masuk'  => '08:00:00',
                'jam_keluar' => '16:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id'    => 2,
                'tanggal'    => now()->toDateString(),
                'status'     => 'hadir',
                'jam_masuk'  => '08:05:00',
                'jam_keluar' => '16:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id'    => 3,
                'tanggal'    => now()->toDateString(),
                'status'     => 'alpha',
                'jam_masuk'  => null,
                'jam_keluar' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}