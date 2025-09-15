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
    protected $paymentData;

    public function __construct($paymentData)
    {
        $this->paymentData = $paymentData;
    }

    public function collection()
    {
        return $this->paymentData;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Jenis Pinjaman',
            'Angsuran Ke',
            'Pokok (Rp)',
            'Jasa (Rp)',
            'Denda (Rp)',
            'Jumlah Bayar (Rp)',
            'Status',
            'Keterangan'
        ];
    }

    public function map($payment): array
    {
        return [
            \Carbon\Carbon::parse($payment->tgl_bayar)->format('d M Y'),
            $payment->jenis_pinjaman_text,
            $payment->angsuran_ke,
            $payment->jumlah_bayar,
            $payment->bunga,
            $payment->denda_rp > 0 ? $payment->denda_rp : 0,
            $payment->total_bayar,
            $payment->status_pembayaran,
            $payment->ket_bayar ?? '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // Tanggal
            'B' => 20, // Jenis Pinjaman
            'C' => 12, // Angsuran Ke
            'D' => 15, // Pokok
            'E' => 15, // Jasa
            'F' => 15, // Denda
            'G' => 18, // Jumlah Bayar
            'H' => 15, // Status
            'I' => 25, // Keterangan
        ];
    }
}