<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MemberLoanReportExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $loanData;
    protected $statistics;
    protected $member;
    protected $tgl_dari;
    protected $tgl_samp;

    public function __construct($loanData, $statistics, $member, $tgl_dari, $tgl_samp)
    {
        $this->loanData = $loanData;
        $this->statistics = $statistics;
        $this->member = $member;
        $this->tgl_dari = $tgl_dari;
        $this->tgl_samp = $tgl_samp;
    }

    public function array(): array
    {
        $data = [];
        
        foreach ($this->loanData as $loan) {
            $data[] = [
                \Carbon\Carbon::parse($loan['tgl_pinjam'])->format('d/m/Y'),
                $loan['jns_pinjaman'] == '1' ? 'Pinjaman Biasa' : ($loan['jns_pinjaman'] == '2' ? 'Pinjaman Barang' : $loan['jns_pinjaman']),
                $loan['jumlah'],
                $loan['lama_angsuran'] . ' bulan',
                $loan['angsuran_per_bulan'],
                $loan['total_tagihan'],
                $loan['jml_bayar'],
                $loan['sisa_tagihan'],
                $loan['angsuran_count'] . '/' . $loan['total_angsuran'],
                $loan['progress'] . '%',
                $loan['status'],
                \Carbon\Carbon::parse($loan['tempo'])->format('d/m/Y'),
                $loan['keterangan'] ?? '-'
            ];
        }
        
        return $data;
    }

    public function headings(): array
    {
        return [
            'Tanggal Pinjaman',
            'Jenis Pinjaman',
            'Jumlah Pinjaman',
            'Lama Angsuran',
            'Angsuran per Bulan',
            'Total Tagihan',
            'Total Dibayar',
            'Sisa Tagihan',
            'Progress Angsuran',
            'Progress %',
            'Status',
            'Jatuh Tempo',
            'Keterangan'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header styles
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1f2937']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Data styles
        $sheet->getStyle('A2:M' . (count($this->loanData) + 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Number formatting for currency columns
        $sheet->getStyle('C2:C' . (count($this->loanData) + 1))->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('E2:E' . (count($this->loanData) + 1))->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('F2:F' . (count($this->loanData) + 1))->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('G2:G' . (count($this->loanData) + 1))->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('H2:H' . (count($this->loanData) + 1))->getNumberFormat()->setFormatCode('#,##0');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // Tanggal Pinjaman
            'B' => 18, // Jenis Pinjaman
            'C' => 18, // Jumlah Pinjaman
            'D' => 15, // Lama Angsuran
            'E' => 18, // Angsuran per Bulan
            'F' => 18, // Total Tagihan
            'G' => 18, // Total Dibayar
            'H' => 18, // Sisa Tagihan
            'I' => 18, // Progress Angsuran
            'J' => 12, // Progress %
            'K' => 15, // Status
            'L' => 15, // Jatuh Tempo
            'M' => 25, // Keterangan
        ];
    }

    public function title(): string
    {
        return 'Laporan Pinjaman';
    }
}
