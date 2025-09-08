<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MemberPaymentReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $payments;

    public function __construct($payments)
    {
        $this->payments = $payments;
    }

    public function collection()
    {
        return $this->payments;
    }

    public function headings(): array
    {
        return [
            'Tanggal Pembayaran',
            'Jenis Pinjaman',
            'Angsuran Ke',
            'Tanggal Tempo',
            'Pokok (Rp)',
            'Jasa/Bunga (Rp)',
            'Denda (Rp)',
            'Total Bayar (Rp)',
            'Status Pembayaran',
            'Keterangan'
        ];
    }

    public function map($payment): array
    {
        return [
            \Carbon\Carbon::parse($payment->tgl_bayar)->format('d/m/Y'),
            $payment->jns_pinjaman == '1' ? 'Pinjaman Biasa' : 'Pinjaman Barang',
            $payment->angsuran_ke,
            \Carbon\Carbon::parse($payment->tgl_tempo)->format('d/m/Y'),
            number_format($payment->jumlah_bayar, 0, ',', '.'),
            number_format($payment->bunga, 0, ',', '.'),
            number_format($payment->denda_rp, 0, ',', '.'),
            number_format($payment->total_bayar, 0, ',', '.'),
            $payment->status_pembayaran,
            $payment->ket_bayar ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'E5E7EB',
                    ],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18, // Tanggal Pembayaran
            'B' => 20, // Jenis Pinjaman
            'C' => 12, // Angsuran Ke
            'D' => 18, // Tanggal Tempo
            'E' => 15, // Pokok
            'F' => 15, // Jasa/Bunga
            'G' => 15, // Denda
            'H' => 18, // Total Bayar
            'I' => 18, // Status Pembayaran
            'J' => 25, // Keterangan
        ];
    }
}
