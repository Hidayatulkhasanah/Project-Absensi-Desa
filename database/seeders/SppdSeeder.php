<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SppdSeeder extends Seeder
{
    public function run(): void {
        DB::table('sppd')->insert([
            [
                'user_id'           => 3,
                'nomor_sppd'        => '091/SPPD/V/2026',
                'tujuan'            => 'Kab. Bogor',
                'keperluan'         => 'Pelatihan BUMDes',
                'tanggal_berangkat' => '2026-05-15',
                'tanggal_kembali'   => '2026-05-16',
                'status'            => 'menunggu',
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'user_id'           => 4,
                'nomor_sppd'        => '094/SPPD/VI/2026',
                'tujuan'            => 'Kec. Sukaraja',
                'keperluan'         => 'Musrenbang',
                'tanggal_berangkat' => '2026-06-05',
                'tanggal_kembali'   => '2026-06-05',
                'status'            => 'menunggu',
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ]);
    }
}