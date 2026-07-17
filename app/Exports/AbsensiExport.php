<?php

namespace App\Exports;

use App\Enums\AbsensiStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Detailed per-record attendance log for a date range — the server-side
 * replacement for the old client-side (SheetJS) doExport.
 */
class AbsensiExport
{
    private const HARI = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    /**
     * @param  Collection  $rows  absensi rows joined with users (nama, jabatan)
     */
    public function __invoke(Collection $rows, string $label, string $filename): StreamedResponse
    {
        $spreadsheet = $this->build($rows, $label);

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function build(Collection $rows, string $label): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Title + meta
        $sheet->setCellValue('A1', 'REKAP ABSENSI DESA MEKARSARI');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Periode: ' . $label);
        $sheet->setCellValue('A3', 'Dicetak: ' . Carbon::now()->translatedFormat('l, d F Y'));
        $sheet->setCellValue('A4', 'Total Data: ' . $rows->count() . ' record');

        // Table header
        $headers = ['No', 'Nama Pegawai', 'Jabatan', 'Tanggal', 'Hari', 'Jam Masuk', 'Jam Keluar', 'Status', 'Keterangan'];
        $headerRow = 6;
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $headerRow, $header);
            $sheet->getStyle($col . $headerRow)->getFont()->setBold(true);
            $sheet->getStyle($col . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD3D3D3');
            $col = str_increment($col);
        }

        $row = $headerRow + 1;
        $no = 1;
        foreach ($rows as $a) {
            $tgl = $a->tanggal ? Carbon::parse($a->tanggal) : null;
            $ket = $a->keterangan ?: '';
            if ($a->status === AbsensiStatus::Hadir->value && $a->jam_masuk) {
                [$hh, $mm] = array_map('intval', explode(':', $a->jam_masuk));
                if ($hh * 60 + $mm > 8 * 60) {
                    $ket = $ket ? $ket . ' (Terlambat)' : 'Terlambat';
                }
            }

            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $a->nama);
            $sheet->setCellValue('C' . $row, $a->jabatan ?: '-');
            $sheet->setCellValue('D' . $row, $a->tanggal);
            $sheet->setCellValue('E' . $row, $tgl ? self::HARI[$tgl->dayOfWeek] : '-');
            $sheet->setCellValue('F' . $row, $a->jam_masuk ?: '-');
            $sheet->setCellValue('G' . $row, $a->jam_keluar ?: '-');
            $sheet->setCellValue('H' . $row, $a->status);
            $sheet->setCellValue('I' . $row, $ket ?: '-');
            $row++;
        }

        // Summary
        $counts = [
            'Total Hadir' => $rows->where('status', AbsensiStatus::Hadir->value)->count(),
            'Total Izin'  => $rows->where('status', AbsensiStatus::Izin->value)->count(),
            'Total Alpha' => $rows->where('status', AbsensiStatus::Alpha->value)->count(),
            'Total SPPD'  => $rows->where('status', AbsensiStatus::Sppd->value)->count(),
        ];
        $row++;
        $sheet->setCellValue('A' . $row, 'RINGKASAN');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        foreach ($counts as $label2 => $val) {
            $row++;
            $sheet->setCellValue('A' . $row, $label2);
            $sheet->setCellValue('B' . $row, $val);
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return $spreadsheet;
    }
}
