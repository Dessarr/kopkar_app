<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MemberSavingsReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $savingsData;
    protected $member;
    protected $tgl_dari;
    protected $tgl_samp;

    public function __construct($savingsData, $member, $tgl_dari, $tgl_samp)
    {
        $this->savingsData = $savingsData;
        $this->member = $member;
        $this->tgl_dari = $tgl_dari;
        $this->tgl_samp = $tgl_samp;
    }

    public function collection()
    {
        return $this->savingsData->getCollection();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Transaksi',
            'Jenis Simpanan',
            'Jumlah Setoran',
            'Status',
            'Keterangan',
            'User Input'
        ];
    }

    public function map($saving): array
    {
        static $counter = 0;
        $counter++;

        return [
            $counter,
            \Carbon\Carbon::parse($saving->tgl_transaksi)->format('d/m/Y'),
            $this->getSavingsTypeText($saving->jenis_id, $saving->jenis_simpanan_nama),
            $saving->jumlah,
            $this->determineSavingsStatus($saving),
            $saving->keterangan ?: 'Setoran simpanan',
            $saving->user_name ?: '-'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // No
            'B' => 15,  // Tanggal
            'C' => 20,  // Jenis Simpanan
            'D' => 18,  // Jumlah
            'E' => 15,  // Status
            'F' => 30,  // Keterangan
            'G' => 15,  // User Input
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
            ],
            // Data rows
            'A:G' => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB']
                    ]
                ]
            ],
            // Number formatting for amount column
            'D' => [
                'numberFormat' => [
                    'formatCode' => '#,##0'
                ]
            ]
        ];
    }

    private function getSavingsTypeText($jenisId, $jenisNama)
    {
        if (empty($jenisNama)) {
            return 'Toserda';
        }
        
        switch ($jenisId) {
            case 1:
                return 'Simpanan Wajib';
            case 2:
                return 'Simpanan Sukarela';
            case 3:
                return 'Simpanan Khusus';
            default:
                return $jenisNama ?: 'Toserda';
        }
    }

    private function determineSavingsStatus($saving)
    {
        if ($saving->jenis_id == 1) {
            return 'Wajib';
        } elseif ($saving->jenis_id == 2) {
            return 'Sukarela';
        } else {
            return 'Toserda';
        }
    }
}
