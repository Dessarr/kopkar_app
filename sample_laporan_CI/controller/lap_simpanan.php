<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lap_simpanan extends OperatorController {
	public function __construct() {
		parent::__construct();
		$this->load->helper('fungsi');
		$this->load->model('lap_simpanan_m');
		$this->load->model('general_m');
	}	

	public function index() {
		$this->data['judul_browser'] = 'Laporan';
		$this->data['judul_utama'] = 'Laporan';
		$this->data['judul_sub'] = 'Buku Besar';

		$this->data['css_files'][] = base_url() . 'assets/easyui/themes/default/easyui.css';
		$this->data['css_files'][] = base_url() . 'assets/easyui/themes/icon.css';
		$this->data['js_files'][] = base_url() . 'assets/easyui/jquery.easyui.min.js';

		#include tanggal
		$this->data['css_files'][] = base_url() . 'assets/extra/bootstrap_date_time/css/bootstrap-datetimepicker.min.css';
		$this->data['js_files'][] = base_url() . 'assets/extra/bootstrap_date_time/js/bootstrap-datetimepicker.min.js';
		$this->data['js_files'][] = base_url() . 'assets/extra/bootstrap_date_time/js/locales/bootstrap-datetimepicker.id.js';

		#include daterange
		$this->data['css_files'][] = base_url() . 'assets/theme_admin/css/daterangepicker/daterangepicker-bs3.css';
		$this->data['js_files'][] = base_url() . 'assets/theme_admin/js/plugins/daterangepicker/daterangepicker.js';

		//number_format
		$this->data['js_files'][] = base_url() . 'assets/extra/fungsi/number_format.js';
		$this->data["nama_kas"] = $this->lap_simpanan_m->get_nama_kas(); 
		$this->data['isi'] = $this->load->view('lap_simpanan_list_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}

	function cetak() {
		$nama_kas = $this->lap_simpanan_m->get_nama_kas(); 
		if($nama_kas == FALSE) {
			redirect('lap_simpanan');
			exit();
		}

		if(isset($_REQUEST['periode'])) {
			$tanggal = $_REQUEST['periode'];
		} else {
			$tanggal = date('Y-m');
		}

		$txt_periode_arr = explode('-', $tanggal);
		if(is_array($txt_periode_arr)) {
			$txt_periode = jin_nama_bulan($txt_periode_arr[1]) . ' ' . $txt_periode_arr[0];
		}

		
		$this->load->library('Pdf');
		$pdf = new Pdf('L', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->set_nsi_header(TRUE);
		$pdf->AddPage('L');
		$html = '<style>
					.h_tengah {text-align: center;}
					.h_kiri {text-align: left;}
					.h_kanan {text-align: right;}
					.txt_judul {font-size: 12pt; font-weight: bold; padding-bottom: 15px;}
					.header_kolom {background-color: #cccccc; text-align: center; font-weight: bold;}
				</style>
				'.$pdf->nsi_box($text = '<span class="txt_judul">Laporan Simpanan Periode '.$txt_periode.'</span>', $width = '100%', $spacing = '1', $padding = '1', $border = '0', $align = 'center').'';
		$no = 1;
		$total_saldo = 0;
		$saldo = 0;
		foreach ($nama_kas as $key) {
			$transD = $this->lap_simpanan_m->get_transaksi_kas($key->id);

			$html.= '<h3>'.$key->jns_simpan.'</h3>';
			$html.= '<table width="90%" cellspacing="0" cellpadding="3" border="1" nobr="true">
			<tr class="header_kolom">
				<th class="h_tengah" style="width:5%;"> No</th>
				<th class="h_tengah" style="width:10%;"> Tanggal </th>
				<th class="h_tengah" style="width:45%;"> Nama</th>
				<th class="h_tengah" style="width:10%;"> Debet </th>
				<th class="h_tengah" style="width:10%;"> Kredit </th>
			</tr>';
			$no = 1;
			$d = 0;
			$k = 0;
			foreach ($transD as $rows) {
				$tglD = explode(' ', $rows->tgl_transaksi);
				$txt_tanggalD = jin_date_ina($tglD[0],'p');

				$html.= '<tr>
					<td class="h_tengah"> '.$no++.' </td>
					<td class="h_tengah"> '.$txt_tanggalD.' </td>
					<td> '.$rows->nama.'</td>
					<td class="h_kanan"> '.number_format(nsi_round($rows->Debet)).' </td>
					<td class="h_kanan"> '.number_format(nsi_round($rows->Kredit)).' </td>
				</tr>';
			}
			$html.= '</table>';
			
		}

			$nilai_d = $this->lap_simpanan_m->get_jml_d();
			$nilai_k = $this->lap_simpanan_m->get_jml_k();

			$d = $nilai_d->jml_total; 
			$k = $nilai_k->jml_total;

		$html.= '<br><br><table width="90%" cellspacing="0" cellpadding="3" border="1" nobr="true">
			<tr class="header_kolom">
				<th class="h_kanan" style="width:60%; vertical-align: middle">TOTAL</th>
				<th class="h_kanan" style="width:10%; vertical-align: middle">'.number_format($d).'</th>
				<th class="h_kanan" style="width:10%; vertical-align: middle">'.number_format($k).'</th>	
			</tr>
		</table>';
		$pdf->nsi_html($html);
		$pdf->Output('lap_simpanan'.date('Ymd_His') . '.pdf', 'I');
	}
}