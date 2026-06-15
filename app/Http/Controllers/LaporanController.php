<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LaporanController extends Controller
{
    public function bulanan(Request $request) {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020',
        ]);

        $data = DB::table('users')
            ->where('aktif', 1)
            ->where('role', '!=', 'admin')
            ->get()
            ->map(function ($user) use ($request) {
                $absensi = DB::table('absensi')
                    ->where('user_id', $user->id)
                    ->whereMonth('tanggal', $request->bulan)
                    ->whereYear('tanggal', $request->tahun)
                    ->selectRaw("
                        COUNT(*) as total,
                        SUM(status = 'hadir') as hadir,
                        SUM(status = 'izin') as izin,
                        SUM(status = 'alpha') as alpha,
                        SUM(status = 'cuti') as cuti
                    ")
                    ->first();

                $persentase = $absensi->total > 0
                    ? round(($absensi->hadir / $absensi->total) * 100, 2)
                    : 0;

                return [
                    'user_id'    => $user->id,
                    'nik'        => $user->nik,
                    'nama'       => $user->nama,
                    'jabatan'    => $user->jabatan,
                    'hadir'      => $absensi->hadir ?? 0,
                    'izin'       => $absensi->izin ?? 0,
                    'alpha'      => $absensi->alpha ?? 0,
                    'cuti'       => $absensi->cuti ?? 0,
                    'persentase' => $persentase,
                ];
            });

        return response()->json([
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'data'  => $data,
        ]);
    }

    public function rekapPegawai(Request $request) {
        $data = DB::table('users')
            ->where('aktif', 1)
            ->where('role', '!=', 'admin')
            ->select('id', 'nik', 'nama', 'jabatan', 'role')
            ->get()
            ->map(function ($user) {
                $total = DB::table('absensi')
                    ->where('user_id', $user->id)
                    ->selectRaw("
                        SUM(status = 'hadir') as hadir,
                        SUM(status = 'izin') as izin,
                        SUM(status = 'alpha') as alpha,
                        SUM(status = 'cuti') as cuti
                    ")
                    ->first();

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

        return response()->json($data);
    }

    // ========== EXPORT EXCEL ==========
    public function exportBulanan(Request $request) {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020',
        ]);

        $data = DB::table('users')
            ->where('aktif', 1)
            ->where('role', '!=', 'admin')
            ->get()
            ->map(function ($user) use ($request) {
                $absensi = DB::table('absensi')
                    ->where('user_id', $user->id)
                    ->whereMonth('tanggal', $request->bulan)
                    ->whereYear('tanggal', $request->tahun)
                    ->selectRaw("
                        COUNT(*) as total,
                        SUM(status = 'hadir') as hadir,
                        SUM(status = 'izin') as izin,
                        SUM(status = 'alpha') as alpha,
                        SUM(status = 'cuti') as cuti
                    ")
                    ->first();

                $persentase = $absensi->total > 0
                    ? round(($absensi->hadir / $absensi->total) * 100, 2)
                    : 0;

                return [
                    'nik'        => $user->nik,
                    'nama'       => $user->nama,
                    'jabatan'    => $user->jabatan,
                    'hadir'      => $absensi->hadir ?? 0,
                    'izin'       => $absensi->izin ?? 0,
                    'alpha'      => $absensi->alpha ?? 0,
                    'cuti'       => $absensi->cuti ?? 0,
                    'persentase' => $persentase,
                ];
            });

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $bulanName = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $sheet->setCellValue('A1', 'LAPORAN ABSENSI ' . strtoupper($bulanName[$request->bulan]) . ' ' . $request->tahun);
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = ['No', 'NIK', 'Nama', 'Jabatan', 'Hadir', 'Izin', 'Alpha', 'Cuti', 'Persentase'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '3', $header);
            $sheet->getStyle($col . '3')->getFont()->setBold(true);
            $sheet->getStyle($col . '3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD3D3D3');
            $col++;
        }

        $row = 4;
        $no = 1;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $item['nik']);
            $sheet->setCellValue('C' . $row, $item['nama']);
            $sheet->setCellValue('D' . $row, $item['jabatan']);
            $sheet->setCellValue('E' . $row, $item['hadir']);
            $sheet->setCellValue('F' . $row, $item['izin']);
            $sheet->setCellValue('G' . $row, $item['alpha']);
            $sheet->setCellValue('H' . $row, $item['cuti']);
            $sheet->setCellValue('I' . $row, $item['persentase'] . '%');
            $row++;
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Laporan_Absensi_' . $request->bulan . '_' . $request->tahun . '.xlsx"');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function exportRekapPegawai() {
        $data = DB::table('users')
            ->where('aktif', 1)
            ->where('role', '!=', 'admin')
            ->select('nik', 'nama', 'jabatan')
            ->get()
            ->map(function ($user) {
                $total = DB::table('absensi')
                    ->where('user_id', $user->id)
                    ->selectRaw("
                        SUM(status = 'hadir') as hadir,
                        SUM(status = 'izin') as izin,
                        SUM(status = 'alpha') as alpha,
                        SUM(status = 'cuti') as cuti
                    ")
                    ->first();

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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'REKAP ABSENSI PEGAWAI DESA MEKARSARI');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $headers = ['No', 'NIK', 'Nama', 'Jabatan', 'Hadir', 'Izin', 'Alpha', 'Cuti'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '3', $header);
            $sheet->getStyle($col . '3')->getFont()->setBold(true);
            $sheet->getStyle($col . '3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFD3D3D3');
            $col++;
        }

        $row = 4;
        $no = 1;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $item['nik']);
            $sheet->setCellValue('C' . $row, $item['nama']);
            $sheet->setCellValue('D' . $row, $item['jabatan']);
            $sheet->setCellValue('E' . $row, $item['hadir']);
            $sheet->setCellValue('F' . $row, $item['izin']);
            $sheet->setCellValue('G' . $row, $item['alpha']);
            $sheet->setCellValue('H' . $row, $item['cuti']);
            $row++;
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Rekap_Pegawai_' . date('Y-m-d') . '.xlsx"');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}