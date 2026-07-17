<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Exports\LaporanBulananExport;
use App\Exports\RekapPegawaiExport;
use App\Models\Absensi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LaporanController extends Controller
{
    /**
     * Paginate an already-computed in-memory collection. bulananData/rekapData
     * are small aggregate results (one row per active pegawai), so slicing in
     * PHP is simpler and just as cheap as a second DB round trip would be.
     */
    private function paginateCollection(Collection $items, Request $request): LengthAwarePaginator
    {
        $perPage = $request->integer('per_page', 15);
        $page = $request->integer('page', 1);

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    /**
     * Aggregate hadir/izin/alpha/cuti counts for active non-admin pegawai in one
     * GROUP BY query instead of one query per pegawai (was N+1: 1 + count(users)).
     */
    private function pegawaiAbsensiStats(?int $bulan = null, ?int $tahun = null)
    {
        $users = User::where('aktif', 1)
            ->where('role', '!=', UserRole::Admin->value)
            ->select('id', 'nik', 'nama', 'jabatan', 'role')
            ->get();

        $statsQuery = Absensi::query()
            ->whereIn('user_id', $users->pluck('id'))
            ->selectRaw("
                user_id,
                COUNT(*) as total,
                SUM(status = 'hadir') as hadir,
                SUM(status = 'izin') as izin,
                SUM(status = 'alpha') as alpha,
                SUM(status = 'cuti') as cuti
            ")
            ->groupBy('user_id');

        if ($bulan !== null && $tahun !== null) {
            $statsQuery->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
        }

        $stats = $statsQuery->get()->keyBy('user_id');

        return [$users, $stats];
    }

    private function bulananData(int $bulan, int $tahun)
    {
        [$users, $stats] = $this->pegawaiAbsensiStats($bulan, $tahun);

        return $users->map(function ($user) use ($stats) {
            $absensi = $stats->get($user->id);
            $total = $absensi->total ?? 0;
            $hadir = $absensi->hadir ?? 0;
            $persentase = $total > 0 ? round(($hadir / $total) * 100, 2) : 0;

            return [
                'user_id'    => $user->id,
                'nik'        => $user->nik,
                'nama'       => $user->nama,
                'jabatan'    => $user->jabatan,
                'hadir'      => $hadir,
                'izin'       => $absensi->izin ?? 0,
                'alpha'      => $absensi->alpha ?? 0,
                'cuti'       => $absensi->cuti ?? 0,
                'persentase' => $persentase,
            ];
        });
    }

    private function rekapData()
    {
        [$users, $stats] = $this->pegawaiAbsensiStats();

        return $users->map(function ($user) use ($stats) {
            $total = $stats->get($user->id);
            return [
                'nik'     => $user->nik,
                'nama'    => $user->nama,
                'jabatan' => $user->jabatan,
                'hadir'   => $total->hadir ?? 0,
                'izin'    => $total->izin ?? 0,
                'alpha'   => $total->alpha ?? 0,
                'cuti'    => $total->cuti ?? 0,
            ];
        });
    }

    // ?paginate=1 returns Laravel's paginator shape for the Laporan Bulanan
    // table; the Excel export relies on the full unpaginated list.
    public function bulanan(Request $request) {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020',
        ]);

        $data = $this->bulananData((int) $request->bulan, (int) $request->tahun);

        if ($request->boolean('paginate')) {
            return response()->json(array_merge(
                ['bulan' => $request->bulan, 'tahun' => $request->tahun],
                $this->paginateCollection($data, $request)->toArray(),
            ));
        }

        return response()->json([
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'data'  => $data,
        ]);
    }

    // ?paginate=1 returns Laravel's paginator shape for the Rekap Pegawai
    // table; pgRekap's client-side search/sort and exportRekapExcel() rely
    // on the full unpaginated list, so pagination stays strictly opt-in.
    public function rekapPegawai(Request $request) {
        $data = $this->rekapData();

        if ($request->boolean('paginate')) {
            return response()->json($this->paginateCollection($data, $request));
        }

        return response()->json($data);
    }

    // ========== EXPORT EXCEL ==========
    public function exportBulanan(Request $request, LaporanBulananExport $export) {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020',
        ]);

        return $export($this->bulananData((int) $request->bulan, (int) $request->tahun), (int) $request->bulan, (int) $request->tahun);
    }

    public function exportRekapPegawai(RekapPegawaiExport $export) {
        return $export($this->rekapData());
    }
}
