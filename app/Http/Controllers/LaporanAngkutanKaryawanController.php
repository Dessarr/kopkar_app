<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\View_AngkutanKaryawan;
use App\Models\TblTransAngkutan;
use App\Models\data_mobil;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class LaporanAngkutanKaryawanController extends Controller
{
    public function index(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y') . '-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y') . '-12-31');

        // Get data bus
        $dataBus = $this->getDataBus($tgl_dari, $tgl_samp);
        $jmlBus = $this->getJmlBus($tgl_dari, $tgl_samp);
        $jmlBusTahun = $this->getJmlBusTahun($tgl_dari, $tgl_samp);
        $jmlBusTahunPajak = $this->getJmlBusTahunPajak($tgl_dari, $tgl_samp);

        // Get data operasional
        $dataOperasional = $this->getDataOperasional($tgl_dari, $tgl_samp);
        $jmlOperasional = $this->getJmlOperasional($tgl_dari, $tgl_samp);
        $jmlOperasionalTahun = $this->getJmlOperasionalTahun($tgl_dari, $tgl_samp);

        // Get data admin
        $dataAdmin = $this->getDataAdmin($tgl_dari, $tgl_samp);
        $jmlAdmin = $this->getJmlAdmin($tgl_dari, $tgl_samp);
        $jmlAdminTahun = $this->getJmlAdminTahun($tgl_dari, $tgl_samp);

        return view('laporan.angkutan_karyawan', compact(
            'dataBus',
            'jmlBus',
            'jmlBusTahun',
            'jmlBusTahunPajak',
            'dataOperasional',
            'jmlOperasional',
            'jmlOperasionalTahun',
            'dataAdmin',
            'jmlAdmin',
            'jmlAdminTahun',
            'tgl_dari',
            'tgl_samp'
        ));
    }

    public function exportPdf(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y') . '-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y') . '-12-31');

        // Get all data
        $dataBus = $this->getDataBus($tgl_dari, $tgl_samp);
        $jmlBus = $this->getJmlBus($tgl_dari, $tgl_samp);
        $jmlBusTahun = $this->getJmlBusTahun($tgl_dari, $tgl_samp);
        $jmlBusTahunPajak = $this->getJmlBusTahunPajak($tgl_dari, $tgl_samp);
        $dataOperasional = $this->getDataOperasional($tgl_dari, $tgl_samp);
        $jmlOperasional = $this->getJmlOperasional($tgl_dari, $tgl_samp);
        $jmlOperasionalTahun = $this->getJmlOperasionalTahun($tgl_dari, $tgl_samp);
        $dataAdmin = $this->getDataAdmin($tgl_dari, $tgl_samp);
        $jmlAdmin = $this->getJmlAdmin($tgl_dari, $tgl_samp);
        $jmlAdminTahun = $this->getJmlAdminTahun($tgl_dari, $tgl_samp);

        $tgl_periode_txt = Carbon::parse($tgl_dari)->format('d/m/Y') . ' - ' . Carbon::parse($tgl_samp)->format('d/m/Y');

        $pdf = PDF::loadView('laporan.pdf.angkutan_karyawan', compact(
            'dataBus',
            'jmlBus',
            'jmlBusTahun',
            'jmlBusTahunPajak',
            'dataOperasional',
            'jmlOperasional',
            'jmlOperasionalTahun',
            'dataAdmin',
            'jmlAdmin',
            'jmlAdminTahun',
            'tgl_periode_txt'
        ));

        return $pdf->download('laporan_angkutan_karyawan_' . date('Ymd') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $tgl_dari = $request->input('tgl_dari', date('Y') . '-01-01');
        $tgl_samp = $request->input('tgl_samp', date('Y') . '-12-31');

        // Get all data
        $dataBus = $this->getDataBus($tgl_dari, $tgl_samp);
        $jmlBus = $this->getJmlBus($tgl_dari, $tgl_samp);
        $jmlBusTahun = $this->getJmlBusTahun($tgl_dari, $tgl_samp);
        $jmlBusTahunPajak = $this->getJmlBusTahunPajak($tgl_dari, $tgl_samp);
        $dataOperasional = $this->getDataOperasional($tgl_dari, $tgl_samp);
        $jmlOperasional = $this->getJmlOperasional($tgl_dari, $tgl_samp);
        $jmlOperasionalTahun = $this->getJmlOperasionalTahun($tgl_dari, $tgl_samp);
        $dataAdmin = $this->getDataAdmin($tgl_dari, $tgl_samp);
        $jmlAdmin = $this->getJmlAdmin($tgl_dari, $tgl_samp);
        $jmlAdminTahun = $this->getJmlAdminTahun($tgl_dari, $tgl_samp);

        $tgl_periode_txt = Carbon::parse($tgl_dari)->format('d/m/Y') . ' - ' . Carbon::parse($tgl_samp)->format('d/m/Y');

        // Create Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'LAPORAN BUS ANGKUTAN KARYAWAN');
        $sheet->setCellValue('A2', 'Periode: ' . $tgl_periode_txt);
        $sheet->mergeCells('A1:N1');
        $sheet->mergeCells('A2:N2');

        // Style title
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal('center');

        // Penghasilan Jasa Sewa Bus
        $sheet->setCellValue('A4', 'PENGHASILAN JASA SEWA BUS');
        $sheet->mergeCells('A4:N4');
        $sheet->getStyle('A4')->getFont()->setBold(true);

        // Header table
        $headers = ['No Polisi', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Total'];
        $col = 'A';
        $row = 6;
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $row, $header);
            $col++;
        }

        // Style header
        $sheet->getStyle('A6:N6')->getFont()->setBold(true);
        $sheet->getStyle('A6:N6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');

        // Data bus
        $row = 7;
        foreach ($dataBus as $bus) {
            $sheet->setCellValue('A' . $row, $bus->no_polisi);
            $sheet->setCellValue('B' . $row, $bus->Jan);
            $sheet->setCellValue('C' . $row, $bus->Feb);
            $sheet->setCellValue('D' . $row, $bus->Mar);
            $sheet->setCellValue('E' . $row, $bus->Apr);
            $sheet->setCellValue('F' . $row, $bus->May);
            $sheet->setCellValue('G' . $row, $bus->Jun);
            $sheet->setCellValue('H' . $row, $bus->Jul);
            $sheet->setCellValue('I' . $row, $bus->Aug);
            $sheet->setCellValue('J' . $row, $bus->Sep);
            $sheet->setCellValue('K' . $row, $bus->Oct);
            $sheet->setCellValue('L' . $row, $bus->Nov);
            $sheet->setCellValue('M' . $row, $bus->Dec);
            $sheet->setCellValue('N' . $row, $bus->TOTAL);
            $row++;
        }

        // Total row
        $sheet->setCellValue('A' . $row, 'JUMLAH');
        $sheet->setCellValue('B' . $row, $jmlBusTahun->jml_total_jan ?? 0);
        $sheet->setCellValue('C' . $row, $jmlBusTahun->jml_total_feb ?? 0);
        $sheet->setCellValue('D' . $row, $jmlBusTahun->jml_total_mar ?? 0);
        $sheet->setCellValue('E' . $row, $jmlBusTahun->jml_total_apr ?? 0);
        $sheet->setCellValue('F' . $row, $jmlBusTahun->jml_total_may ?? 0);
        $sheet->setCellValue('G' . $row, $jmlBusTahun->jml_total_jun ?? 0);
        $sheet->setCellValue('H' . $row, $jmlBusTahun->jml_total_jul ?? 0);
        $sheet->setCellValue('I' . $row, $jmlBusTahun->jml_total_aug ?? 0);
        $sheet->setCellValue('J' . $row, $jmlBusTahun->jml_total_sep ?? 0);
        $sheet->setCellValue('K' . $row, $jmlBusTahun->jml_total_oct ?? 0);
        $sheet->setCellValue('L' . $row, $jmlBusTahun->jml_total_nov ?? 0);
        $sheet->setCellValue('M' . $row, $jmlBusTahun->jml_total_dec ?? 0);
        $sheet->setCellValue('N' . $row, $jmlBus->jml_total ?? 0);

        // Style total row
        $sheet->getStyle('A' . $row . ':N' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':N' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('E6E6E6');

        // Auto size columns
        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Set number format for currency columns
        $sheet->getStyle('B6:N' . $row)->getNumberFormat()->setFormatCode('#,##0');

        // Create file
        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan_angkutan_karyawan_' . date('Ymd') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    // Private methods for data retrieval
    private function getDataBus($tgl_dari, $tgl_samp)
    {
        return DB::table('v_angkutan_karyawan')
            ->select('no_polisi', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'TOTAL')
            ->whereBetween(DB::raw('DATE(tgl_catat)'), [$tgl_dari, $tgl_samp])
            ->groupBy('no_polisi', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'TOTAL')
            ->get();
    }

    private function getJmlBus($tgl_dari, $tgl_samp)
    {
        return DB::table('v_angkutan_karyawan')
            ->select(DB::raw('SUM(TOTAL) as jml_total'))
            ->whereBetween(DB::raw('DATE(tgl_catat)'), [$tgl_dari, $tgl_samp])
            ->first();
    }

    private function getJmlBusTahun($tgl_dari, $tgl_samp)
    {
        return DB::table('v_angkutan_karyawan')
            ->select(
                DB::raw('SUM(Jan) as jml_total_jan'),
                DB::raw('SUM(Feb) as jml_total_feb'),
                DB::raw('SUM(Mar) as jml_total_mar'),
                DB::raw('SUM(Apr) as jml_total_apr'),
                DB::raw('SUM(May) as jml_total_may'),
                DB::raw('SUM(Jun) as jml_total_jun'),
                DB::raw('SUM(Jul) as jml_total_jul'),
                DB::raw('SUM(Aug) as jml_total_aug'),
                DB::raw('SUM(Sep) as jml_total_sep'),
                DB::raw('SUM(Oct) as jml_total_oct'),
                DB::raw('SUM(Nov) as jml_total_nov'),
                DB::raw('SUM(`Dec`) as jml_total_dec')
            )
            ->whereBetween(DB::raw('DATE(tgl_catat)'), [$tgl_dari, $tgl_samp])
            ->first();
    }

    private function getJmlBusTahunPajak($tgl_dari, $tgl_samp)
    {
        return DB::table('v_angkutan_karyawan')
            ->select(
                DB::raw('(SUM(Jan)*2)/100 as jml_total_jan_pajak'),
                DB::raw('(SUM(Feb)*2)/100 as jml_total_feb_pajak'),
                DB::raw('(SUM(Mar)*2)/100 as jml_total_mar_pajak'),
                DB::raw('(SUM(Apr)*2)/100 as jml_total_apr_pajak'),
                DB::raw('(SUM(May)*2)/100 as jml_total_may_pajak'),
                DB::raw('(SUM(Jun)*2)/100 as jml_total_jun_pajak'),
                DB::raw('(SUM(Jul)*2)/100 as jml_total_jul_pajak'),
                DB::raw('(SUM(Aug)*2)/100 as jml_total_aug_pajak'),
                DB::raw('(SUM(Sep)*2)/100 as jml_total_sep_pajak'),
                DB::raw('(SUM(Oct)*2)/100 as jml_total_oct_pajak'),
                DB::raw('(SUM(Nov)*2)/100 as jml_total_nov_pajak'),
                DB::raw('(SUM(`Dec`)*2)/100 as jml_total_dec_pajak')
            )
            ->whereBetween(DB::raw('DATE(tgl_catat)'), [$tgl_dari, $tgl_samp])
            ->first();
    }

    private function getDataOperasional($tgl_dari, $tgl_samp)
    {
        return DB::table('v_biaya_operasional')
            ->select('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'TOTAL')
            ->whereBetween(DB::raw('DATE(tgl_catat)'), [$tgl_dari, $tgl_samp])
            ->groupBy('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'TOTAL')
            ->get();
    }

    private function getJmlOperasional($tgl_dari, $tgl_samp)
    {
        return DB::table('v_biaya_operasional')
            ->select(DB::raw('SUM(TOTAL) as jml_total'))
            ->whereBetween(DB::raw('DATE(tgl_catat)'), [$tgl_dari, $tgl_samp])
            ->first();
    }

    private function getJmlOperasionalTahun($tgl_dari, $tgl_samp)
    {
        return DB::table('v_biaya_operasional')
            ->select(
                DB::raw('SUM(Jan) as jml_total_jan'),
                DB::raw('SUM(Feb) as jml_total_feb'),
                DB::raw('SUM(Mar) as jml_total_mar'),
                DB::raw('SUM(Apr) as jml_total_apr'),
                DB::raw('SUM(May) as jml_total_may'),
                DB::raw('SUM(Jun) as jml_total_jun'),
                DB::raw('SUM(Jul) as jml_total_jul'),
                DB::raw('SUM(Aug) as jml_total_aug'),
                DB::raw('SUM(Sep) as jml_total_sep'),
                DB::raw('SUM(Oct) as jml_total_oct'),
                DB::raw('SUM(Nov) as jml_total_nov'),
                DB::raw('SUM(`Dec`) as jml_total_dec')
            )
            ->whereBetween(DB::raw('DATE(tgl_catat)'), [$tgl_dari, $tgl_samp])
            ->first();
    }

    private function getDataAdmin($tgl_dari, $tgl_samp)
    {
        return DB::table('v_biaya_usaha')
            ->select('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'TOTAL')
            ->whereBetween(DB::raw('DATE(tgl_catat)'), [$tgl_dari, $tgl_samp])
            ->groupBy('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'TOTAL')
            ->get();
    }

    private function getJmlAdmin($tgl_dari, $tgl_samp)
    {
        return DB::table('v_biaya_usaha')
            ->select(DB::raw('SUM(TOTAL) as jml_total'))
            ->whereBetween(DB::raw('DATE(tgl_catat)'), [$tgl_dari, $tgl_samp])
            ->first();
    }

    private function getJmlAdminTahun($tgl_dari, $tgl_samp)
    {
        return DB::table('v_biaya_usaha')
            ->select(
                DB::raw('SUM(Jan) as jml_total_jan'),
                DB::raw('SUM(Feb) as jml_total_feb'),
                DB::raw('SUM(Mar) as jml_total_mar'),
                DB::raw('SUM(Apr) as jml_total_apr'),
                DB::raw('SUM(May) as jml_total_may'),
                DB::raw('SUM(Jun) as jml_total_jun'),
                DB::raw('SUM(Jul) as jml_total_jul'),
                DB::raw('SUM(Aug) as jml_total_aug'),
                DB::raw('SUM(Sep) as jml_total_sep'),
                DB::raw('SUM(Oct) as jml_total_oct'),
                DB::raw('SUM(Nov) as jml_total_nov'),
                DB::raw('SUM(`Dec`) as jml_total_dec')
            )
            ->whereBetween(DB::raw('DATE(tgl_catat)'), [$tgl_dari, $tgl_samp])
            ->first();
    }
} 