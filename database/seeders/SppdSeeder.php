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
                'keterangan'        => null,
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
                'keterangan'        => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'user_id'           => 5,
                'nomor_sppd'        => '088/SPPD/IV/2026',
                'tujuan'            => 'Kota Bogor',
                'keperluan'         => 'Rakor Kesejahteraan Rakyat',
                'tanggal_berangkat' => '2026-04-20',
                'tanggal_kembali'   => '2026-04-21',
                'status'            => 'disetujui',
                'keterangan'        => 'Disetujui, gunakan kendaraan dinas.',
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'user_id'           => 6,
                'nomor_sppd'        => '090/SPPD/V/2026',
                'tujuan'            => 'Bandung',
                'keperluan'         => 'Bimtek Pengelolaan Keuangan Desa',
                'tanggal_berangkat' => '2026-05-10',
                'tanggal_kembali'   => '2026-05-12',
                'status'            => 'ditolak',
                'keterangan'        => 'Bentrok dengan jadwal tutup buku bulanan.',
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'user_id'           => 7,
                'nomor_sppd'        => '099/SPPD/VII/2026',
                'tujuan'            => 'Kec. Sukaraja',
                'keperluan'         => 'Koordinasi Perencanaan Pembangunan',
                'tanggal_berangkat' => now()->addDays(5)->toDateString(),
                'tanggal_kembali'   => now()->addDays(5)->toDateString(),
                'status'            => 'menunggu',
                'keterangan'        => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ]);
    }
}