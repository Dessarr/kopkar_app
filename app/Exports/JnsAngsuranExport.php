<?php

namespace App\Exports;

use App\Models\jns_angsuran;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class JnsAngsuranExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected $search;
    protected $statusAktif;
    protected $kategori;

    public function __construct($search = null, $statusAktif = null, $kategori = null)
    {
        $this->search = $search;
        $this->statusAktif = $statusAktif;
        $this->kategori = $kategori;
    }

    public function collection()
    {
        $query = jns_angsuran::query();

        if ($this->search) {
            $query->search($this->search);
        }

        if ($this->statusAktif) {
            $query->byStatusAktif($this->statusAktif);
        }

        if ($this->kategori) {
            $query->byKategori($this->kategori);
        }

        return $query->ordered()->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Jumlah Bulan',
            'Kategori',
            'Status Aktif',
            'Keterangan',
            'Tanggal Dibuat',
        ];
    }

    public function map($angsuran): array
    {
        return [
            $angsuran->id,
            $angsuran->ket . ' Bulan',
            $angsuran->kategori_angsuran,
            $angsuran->status_aktif_text,
            $angsuran->ket_formatted,
            '-',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // ID
            'B' => 15,  // Jumlah Bulan
            'C' => 20,  // Kategori
            'D' => 15,  // Status Aktif
            'E' => 20,  // Keterangan
            'F' => 20,  // Tanggal Dibuat
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style untuk header
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '20B2AA'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Set border untuk semua cell
                $sheet->getStyle('A1:F' . ($sheet->getHighestRow()))
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Set alignment untuk data
                $sheet->getStyle('A2:F' . ($sheet->getHighestRow()))
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Set row height untuk header
                $sheet->getRowDimension(1)->setRowHeight(25);

                // Set row height untuk data
                for ($i = 2; $i <= $sheet->getHighestRow(); $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(20);
                }

                // Add summary info
                $lastRow = $sheet->getHighestRow();
                $summaryRow = $lastRow + 2;
                
                $sheet->setCellValue('A' . $summaryRow, 'Total Data: ' . $lastRow);
                $sheet->setCellValue('C' . $summaryRow, 'Dicetak: ' . date('d/m/Y H:i:s'));
                
                $sheet->getStyle('A' . $summaryRow . ':F' . $summaryRow)
                    ->getFont()
                    ->setBold(true)
                    ->setSize(10);
            },
        ];
    }
}