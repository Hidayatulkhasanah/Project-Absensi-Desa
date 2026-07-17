<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AbsensiSeeder extends Seeder
{
    public function run(): void
    {
        // Regular pegawai only — admin/operator don't clock in/out.
        $pegawaiIds = DB::table('users')
            ->where('role', 'user')
            ->pluck('id');

        $rows = [];

        // Last 15 weekdays (excluding today), so Laporan/Rekap has a real month
        // of history and Absensi CRUD has plenty of rows to edit/delete.
        $day = Carbon::yesterday();
        $daysCollected = 0;
        while ($daysCollected < 15) {
            if (!$day->isWeekend()) {
                foreach ($pegawaiIds as $userId) {
                    $pattern = ($userId + $day->day) % 10;

                    if ($pattern <= 6) {
                        // Hadir — jam masuk varies slightly around 08:00
                        $menitTelat = $pattern; // 0-6 menit variasi
                        $rows[] = [
                            'user_id'    => $userId,
                            'tanggal'    => $day->toDateString(),
                            'status'     => 'Hadir',
                            'jam_masuk'  => Carbon::createFromTime(7, 54)->addMinutes($menitTelat)->toTimeString(),
                            'jam_keluar' => Carbon::createFromTime(16, 0)->addMinutes($menitTelat)->toTimeString(),
                            'latitude'   => null,
                            'longitude'  => null,
                            'foto_path'  => null,
                            'keterangan' => null,
                            'created_at' => $day,
                            'updated_at' => $day,
                        ];
                    } elseif ($pattern == 7) {
                        $rows[] = [
                            'user_id'    => $userId,
                            'tanggal'    => $day->toDateString(),
                            'status'     => 'Izin',
                            'jam_masuk'  => null,
                            'jam_keluar' => null,
                            'latitude'   => null,
                            'longitude'  => null,
                            'foto_path'  => null,
                            'keterangan' => 'Keperluan keluarga',
                            'created_at' => $day,
                            'updated_at' => $day,
                        ];
                    } elseif ($pattern == 8) {
                        $rows[] = [
                            'user_id'    => $userId,
                            'tanggal'    => $day->toDateString(),
                            'status'     => 'cuti',
                            'jam_masuk'  => null,
                            'jam_keluar' => null,
                            'latitude'   => null,
                            'longitude'  => null,
                            'foto_path'  => null,
                            'keterangan' => 'Cuti tahunan',
                            'created_at' => $day,
                            'updated_at' => $day,
                        ];
                    } else {
                        $rows[] = [
                            'user_id'    => $userId,
                            'tanggal'    => $day->toDateString(),
                            'status'     => 'Alpha',
                            'jam_masuk'  => null,
                            'jam_keluar' => null,
                            'latitude'   => null,
                            'longitude'  => null,
                            'foto_path'  => null,
                            'keterangan' => null,
                            'created_at' => $day,
                            'updated_at' => $day,
                        ];
                    }
                }
                $daysCollected++;
            }
            $day->subDay();
        }

        // Today — a couple of pegawai already checked in (some still open,
        // one fully checked out) so the live dashboard widget has data too.
        $today = Carbon::today();
        foreach ($pegawaiIds->take(3) as $i => $userId) {
            $rows[] = [
                'user_id'    => $userId,
                'tanggal'    => $today->toDateString(),
                'status'     => 'Hadir',
                'jam_masuk'  => '07:58:00',
                'jam_keluar' => $i === 0 ? '16:05:00' : null,
                'latitude'   => null,
                'longitude'  => null,
                'foto_path'  => null,
                'keterangan' => null,
                'created_at' => $today,
                'updated_at' => $today,
            ];
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('absensi')->insert($chunk);
        }
    }
}
