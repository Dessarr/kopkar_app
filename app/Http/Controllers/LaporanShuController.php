<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblPinjamanH;
use App\Models\TblPinjamanD;
use App\Models\jns_akun;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;

class LaporanShuController extends Controller
{
    public function index(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        
        // Get detailed SHU data using views
        $data = $this->getShuDataFromView($tgl_dari, $tgl_samp);
        
        return view('laporan.shu', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data
        ]);
    }

    private function getShuDataFromView($tgl_dari, $tgl_samp)
    {
        // Calculate laba pinjaman from v_hitung_pinjaman
        $laba_pinjaman = DB::table('v_hitung_pinjaman')
            ->whereBetween('tgl_pinjam', [$tgl_dari, $tgl_samp])
            ->select(DB::raw('SUM(jumlah) as total_pinjaman'),
                     DB::raw('SUM(total_bayar) as total_angsuran'))
            ->first();
        
        $laba_pinjaman_value = ($laba_pinjaman->total_angsuran ?? 0) - ($laba_pinjaman->total_pinjaman ?? 0);
        
        // Get total simpanan anggota from v_shu (menggunakan jns_trans = 155)
        $total_simpanan = DB::table('v_shu')
            ->whereBetween('tgl_transaksi', [$tgl_dari, $tgl_samp])
            ->where('dk', 'D')
            ->where('jns_trans', 155) // Simpanan berdasarkan data yang ada
            ->sum('jumlah_bayar');
        
        // Get total pendapatan anggota from v_shu (menggunakan jns_trans = 155)
        $total_pendapatan_anggota = DB::table('v_shu')
            ->whereBetween('tgl_transaksi', [$tgl_dari, $tgl_samp])
            ->where('dk', 'K')
            ->where('jns_trans', 155) // Pendapatan berdasarkan data yang ada
            ->sum('jumlah_bayar');
        
        // Calculate SHU (using dummy values for now - can be replaced with real calculations)
        $shu_sebelum_pajak = 433866900; // Dummy value from the image
        $tax_rate = 5; // 5% PPh
        $pajak_pph = $shu_sebelum_pajak * ($tax_rate / 100);
        $shu_setelah_pajak = $shu_sebelum_pajak - $pajak_pph;
        
        // Calculate fund distribution
        $dana_cadangan = $shu_setelah_pajak * 0.40; // 40%
        $jasa_anggota = $shu_setelah_pajak * 0.40; // 40%
        $dana_pengurus = $shu_setelah_pajak * 0.05; // 5%
        $dana_karyawan = $shu_setelah_pajak * 0.05; // 5%
        $dana_pendidikan = $shu_setelah_pajak * 0.05; // 5%
        $dana_sosial = $shu_setelah_pajak * 0.05; // 5%
        
        // Calculate member distribution
        $jasa_usaha = $jasa_anggota * 0.70; // 70% of jasa anggota
        $jasa_modal = $jasa_anggota * 0.30; // 30% of jasa anggota
        
        return [
            'shu_sebelum_pajak' => $shu_sebelum_pajak,
            'pajak_pph' => $pajak_pph,
            'tax_rate' => $tax_rate,
            'shu_setelah_pajak' => $shu_setelah_pajak,
            'laba_pinjaman' => $laba_pinjaman_value,
            'total_simpanan' => $total_simpanan,
            'total_pendapatan_anggota' => $total_pendapatan_anggota,
            'dana_cadangan' => $dana_cadangan,
            'jasa_anggota' => $jasa_anggota,
            'dana_pengurus' => $dana_pengurus,
            'dana_karyawan' => $dana_karyawan,
            'dana_pendidikan' => $dana_pendidikan,
            'dana_sosial' => $dana_sosial,
            'jasa_usaha' => $jasa_usaha,
            'jasa_modal' => $jasa_modal
        ];
    }



    public function exportPdf(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        
        // Get detailed data using views
        $data = $this->getShuDataFromView($tgl_dari, $tgl_samp);
        
        $pdf = Pdf::loadView('laporan.pdf.shu', [
            'tgl_dari' => $tgl_dari,
            'tgl_samp' => $tgl_samp,
            'data' => $data
        ]);
        
        return $pdf->download('laporan_shu_'.$tgl_dari.'_'.$tgl_samp.'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y').'-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y').'-12-31');
        
        // Get detailed data using views
        $data = $this->getShuDataFromView($tgl_dari, $tgl_samp);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set title
        $sheet->setCellValue('A1', 'LAPORAN PEMBAGIAN SHU PERIODE ' . Carbon::parse($tgl_dari)->format('d M Y') . ' - ' . Carbon::parse($tgl_samp)->format('d M Y'));
        $sheet->setCellValue('A2', 'Koperasi Karyawan');
        $sheet->setCellValue('A3', 'Dicetak pada: ' . Carbon::now()->format('d M Y H:i:s'));
        $sheet->mergeCells('A1:C1');
        $sheet->mergeCells('A2:C2');
        $sheet->mergeCells('A3:C3');
        
        // Style title
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A3')->getFont()->setSize(12);
        
        // Ringkasan SHU section
        $rowNum = 5;
        $sheet->setCellValue('A'.$rowNum, 'RINGKASAN SHU');
        $sheet->mergeCells('A'.$rowNum.':B'.$rowNum);
        $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true);
        
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'SHU Sebelum Pajak');
        $sheet->setCellValue('B'.$rowNum, 'Rp ' . number_format($data['shu_sebelum_pajak'], 0, ',', '.'));
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'Pajak PPh (' . $data['tax_rate'] . '%)');
        $sheet->setCellValue('B'.$rowNum, 'Rp ' . number_format($data['pajak_pph'], 0, ',', '.'));
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'SHU Setelah Pajak');
        $sheet->setCellValue('B'.$rowNum, 'Rp ' . number_format($data['shu_setelah_pajak'], 0, ',', '.'));
        $sheet->getStyle('A'.$rowNum.':B'.$rowNum)->getFont()->setBold(true);
        
        // Pembagian SHU untuk Dana-Dana section
        $rowNum += 3;
        $sheet->setCellValue('A'.$rowNum, 'PEMBAGIAN SHU UNTUK DANA-DANA');
        $sheet->mergeCells('A'.$rowNum.':C'.$rowNum);
        $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true);
        
        $rowNum++;
        $sheet->setCellValue('A'.$rowNum, 'Dana');
        $sheet->setCellValue('B'.$rowNum, 'Persentase');
        $sheet->setCellValue('C'.$rowNum, 'Jumlah');
        
        // Style headers
        $sheet->getStyle('A'.$rowNum.':C'.$rowNum)->getFont()->setBold(true);
        $sheet->getStyle('A'.$rowNum.':C'.$rowNum)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');
        
        // Add fund distribution data
        $rowNum++;
        $funds = [
            ['Dana Cadangan', '40%', $data['dana_cadangan']],
            ['Jasa Anggota', '40%', $data['jasa_anggota']],
            ['Dana Pengurus', '5%', $data['dana_pengurus']],
            ['Dana Karyawan', '5%', $data['dana_karyawan']],
            ['Dana Pendidikan', '5%', $data['dana_pendidikan']],
            ['Dana Sosial', '5%', $data['dana_sosial']]
        ];
        
        foreach ($funds as $fund) {
            $sheet->setCellValue('A'.$rowNum, $fund[0]);
            $sheet->setCellValue('B'.$rowNum, $fund[1]);
            $sheet->setCellValue('C'.$rowNum, 'Rp ' . number_format($fund[2], 0, ',', '.'));
            $rowNum++;
        }
        
        // Pembagian SHU Anggota section
        $rowNum += 2;
        $sheet->setCellValue('A'.$rowNum, 'PEMBAGIAN SHU ANGGOTA');
        $sheet->mergeCells('A'.$rowNum.':C'.$rowNum);
        $sheet->getStyle('A'.$rowNum)->getFont()->setBold(true);
        $rowNum++;
        
        $sheet->setCellValue('A'.$rowNum, 'Keterangan');
        $sheet->setCellValue('B'.$rowNum, 'Persentase');
        $sheet->setCellValue('C'.$rowNum, 'Jumlah');
        
        // Style headers
        $sheet->getStyle('A'.$rowNum.':C'.$rowNum)->getFont()->setBold(true);
        $sheet->getStyle('A'.$rowNum.':C'.$rowNum)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE0E0E0');
        
        // Add member distribution data
        $rowNum++;
        $members = [
            ['Jasa Usaha', '70%', $data['jasa_usaha']],
            ['Jasa Modal', '30%', $data['jasa_modal']],
            ['Total Pendapatan Anggota', '-', $data['total_pendapatan_anggota']],
            ['Total Simpanan Anggota', '-', $data['total_simpanan']]
        ];
        
        foreach ($members as $member) {
            $sheet->setCellValue('A'.$rowNum, $member[0]);
            $sheet->setCellValue('B'.$rowNum, $member[1]);
            $sheet->setCellValue('C'.$rowNum, 'Rp ' . number_format($member[2], 0, ',', '.'));
            $rowNum++;
        }
        
        // Auto-size columns
        foreach (range('A', 'C') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_shu_'.$tgl_dari.'_'.$tgl_samp.'.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
} 