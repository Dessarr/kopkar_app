<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lap_rugi_laba extends OperatorController {

public function __construct() {
		parent::__construct();	
		$this->load->helper('fungsi');
		$this->load->model('general_m');
		$this->load->model('lap_rugi_laba_m');
	}	

	public function index() {
		$this->load->library("pagination");
		$this->data['judul_browser'] 	= 'Laporan';
		$this->data['judul_utama'] 		= 'Laporan';
		$this->data['judul_sub'] 		= 'Rugi Laba';
		$this->data['css_files'][] = base_url() . 'assets/easyui/themes/default/easyui.css';
		$this->data['css_files'][] = base_url() . 'assets/easyui/themes/icon.css';
		$this->data['js_files'][] = base_url() . 'assets/easyui/jquery.easyui.min.js';

		#include tanggal
		$this->data['css_files'][] = base_url() . 'assets/extra/bootstrap_date_time/css/bootstrap-datetimepicker.min.css';
		$this->data['js_files'][] = base_url() . 'assets/extra/bootstrap_date_time/js/bootstrap-datetimepicker.min.js';
		$this->data['js_files'][] = base_url() . 'assets/extra/bootstrap_date_time/js/locales/bootstrap-datetimepicker.id.js';

			#include seach
		$this->data['css_files'][] = base_url() . 'assets/theme_admin/css/daterangepicker/daterangepicker-bs3.css';
		$this->data['js_files'][] = base_url() . 'assets/theme_admin/js/plugins/daterangepicker/daterangepicker.js';

		$this->data['data_penjualan'] 	= $this->lap_rugi_laba_m->get_data_penjualan();
		$this->data['jml_penjualan'] 	= $this->lap_rugi_laba_m->get_jml_penjualan();
		$this->data['jml_awal'] 		= $this->lap_rugi_laba_m->get_jml_awal();
		$this->data['data_pembelian'] 	= $this->lap_rugi_laba_m->get_data_pembelian();

		$this->data['jml_awal_persediaan'] 	= $this->lap_rugi_laba_m->get_persediaan_awal();

		$this->data['jml_pembelian'] 	= $this->lap_rugi_laba_m->get_jml_pembelian();
		$this->data['jml_pembelian_akhir'] 	= $this->lap_rugi_laba_m->get_jml_pembelian_akhir();
		$this->data['data_biaya_usaha'] 	= $this->lap_rugi_laba_m->get_data_biaya_usaha();
		$this->data['jml_biaya_usaha'] 		= $this->lap_rugi_laba_m->get_jml_biaya_usaha();
		
		$this->data['isi'] = $this->load->view('lap_rugi_laba_list_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}

	function cetak() {
		$data_penjualan 	= $this->lap_rugi_laba_m->get_data_penjualan();
		$jml_penjualan 	= $this->lap_rugi_laba_m->get_jml_penjualan();
		$jml_penjualan_tahun 		= $this->lap_rugi_laba_m->get_jml_penjualan_tahun();

		$jml_awal = $this->lap_rugi_laba_m->get_jml_awal();
		$jml_awal_persediaan = $this->lap_rugi_laba_m->get_persediaan_awal();
		$jml_awal_tahun = $this->lap_rugi_laba_m->get_jml_awal_tahun();
		$data_pembelian = $this->lap_rugi_laba_m->get_data_pembelian();
		$jml_pembelian = $this->lap_rugi_laba_m->get_jml_pembelian();
		$jml_pembelian_tahun = $this->lap_rugi_laba_m->get_jml_pembelian_tahun();
		$jml_pembelian_awal_tahun = $this->lap_rugi_laba_m->get_jml_awal_pembelian_tahun();
		$jml_pembelian_akhir_tahun = $this->lap_rugi_laba_m->get_jml_pembelian_akhir_tahun();
		$jml_pembelian_akhir = $this->lap_rugi_laba_m->get_jml_pembelian_akhir();

		$data_biaya_usaha = $this->lap_rugi_laba_m->get_data_biaya_usaha();
		$jml_biaya_usaha_tahun = $this->lap_rugi_laba_m->get_jml_biaya_usaha_tahun();
		$jml_total_usaha 	= $this->lap_rugi_laba_m->get_jml_biaya_tahun();

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$tgl_dari_txt = jin_date_ina($tgl_dari, 'p');
		$tgl_samp_txt = jin_date_ina($tgl_samp, 'p');
		$tgl_periode_txt = $tgl_dari_txt . ' - ' . $tgl_samp_txt;

     $this->load->library('Pdf');
     $pdf = new Pdf('L', 'mm', 'A3', true, 'UTF-8', false);
     $pdf->set_nsi_header(TRUE);
     $pdf->AddPage('L');
     $html = '
         <style>
             .h_tengah {text-align: center;}
             .h_kiri {text-align: left;}
             .h_kanan {text-align: right;}
             .txt_judul {font-size: 12pt; font-weight: bold; padding-bottom: 15px;}
             .header_kolom {background-color: #cccccc; text-align: center; font-weight: bold;}
         </style>
         '.$pdf->nsi_box($text = '<span class="txt_judul">Laporan Rugi Laba Periode '.$tgl_periode_txt.'</span>', $width = '100%', $spacing = '1', $padding = '1', $border = '0', $align = 'center').'';

		$html .= 
		'<h3> Pendapatan Usaha </h3>
			<table width="100%" cellspacing="0" cellpadding="2" border="1">
				<tr class="header_kolom">
					<th style="width:7%; vertical-align: left; text-align:left"> Pendapatan Usaha</th>
					<th style="width:7%; vertical-align: middle; text-align:center">Jan </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Feb </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Mar </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Apr </th>
					<th style="width:7%; vertical-align: middle; text-align:center">May </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Jun </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Jul </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Aug </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Sep </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Oct </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Nov </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Dec </th>
					<th style="width:8%; vertical-align: middle; text-align:center"> Total  </th>
				</tr>
				</table>';
				$no=1;
				foreach ($data_penjualan as $rows) {
					$html .= '
					<table width="100%" cellspacing="0" cellpadding="2" border="1">
						<tr>
							<td width="7%" class="h_kiri"> '.$rows->jns_trans.'</td>
							<td width="7%" class="h_kanan"> '.number_format(nsi_round($rows->Jan)).'</td>
							<td width="7%" class="h_kanan"> '.number_format(nsi_round($rows->Feb)).'</td>
							<td width="7%" class="h_kanan"> '.number_format(nsi_round($rows->Mar)).'</td>
							<td width="7%" class="h_kanan"> '.number_format(nsi_round($rows->Apr)).'</td>
							<td width="7%" class="h_kanan"> '.number_format(nsi_round($rows->May)).'</td>
							<td width="7%" class="h_kanan"> '.number_format(nsi_round($rows->Jun)).'</td>
							<td width="7%" class="h_kanan"> '.number_format(nsi_round($rows->Jul)).'</td>
							<td width="7%" class="h_kanan"> '.number_format(nsi_round($rows->Aug)).'</td>
							<td width="7%" class="h_kanan"> '.number_format(nsi_round($rows->Sep)).'</td>
							<td width="7%" width="7%" class="h_kanan"> '.number_format(nsi_round($rows->Oct)).'</td>
							<td width="7%" class="h_kanan"> '.number_format(nsi_round($rows->Nov)).'</td>
							<td width="7%" class="h_kanan"> '.number_format(nsi_round($rows->Dec)).'</td>
							<td width="8%" class="h_kanan"> '.number_format(nsi_round($rows->TOTAL)).'</td>
						</tr>
					</table>';
				$no++;
				}
				$pendapatan = ($jml_penjualan->jml_total);
				$html .= 
				'<table width="100%" cellspacing="0" cellpadding="2" border="1">
					<tr class="header_kolom">
					<th width="7%"> Jumlah</th>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($penj_jan = $jml_penjualan_tahun->jml_total_jan)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($penj_feb = $jml_penjualan_tahun->jml_total_feb)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($penj_mar = $jml_penjualan_tahun->jml_total_mar)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($penj_apr = $jml_penjualan_tahun->jml_total_apr)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($penj_may = $jml_penjualan_tahun->jml_total_may)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($penj_jun = $jml_penjualan_tahun->jml_total_jun)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($penj_jul = $jml_penjualan_tahun->jml_total_jul)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($penj_aug = $jml_penjualan_tahun->jml_total_aug)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($penj_sep = $jml_penjualan_tahun->jml_total_sep)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($penj_oct = $jml_penjualan_tahun->jml_total_oct)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($penj_nov = $jml_penjualan_tahun->jml_total_nov)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($penj_dec = $jml_penjualan_tahun->jml_total_dec)).'</td>
					<td width="8%" class="h_kanan">'.number_format(nsi_round($pendapatan)).'</td>
					</tr>
				</table>';

			$html .= '
			<h3> Harga Pokok Penjualan </h3>
			';
			$html .= '
			<table width="100%" cellspacing="0" cellpadding="3" border="1">
				<tr class="header_kolom">
					<th style="width:7%; vertical-align: middle; text-align:center"> </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Jan </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Feb </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Mar </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Apr </th>
					<th style="width:7%; vertical-align: middle; text-align:center">May </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Jun </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Jul </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Aug </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Sep </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Oct </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Nov </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Dec </th>
					<th style="width:8%; vertical-align: middle; text-align:center"> Total  </th>
				</tr>
				';
				$no=1;
				foreach ($data_pembelian as $rows) {
					$html .= '
						<tr>
							<td style="width:7%; vertical-align: left; text-align:left"> '.$rows->jns_trans.'</td>
							<td class="h_kanan"> '.number_format(nsi_round($jml_pembelian_tahun->jml_total_jan)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($jml_pembelian_tahun->jml_total_feb)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($jml_pembelian_tahun->jml_total_mar)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($jml_pembelian_tahun->jml_total_apr)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($jml_pembelian_tahun->jml_total_may)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($jml_pembelian_tahun->jml_total_jun)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($jml_pembelian_tahun->jml_total_jul)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($jml_pembelian_tahun->jml_total_aug)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($jml_pembelian_tahun->jml_total_sep)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($jml_pembelian_tahun->jml_total_oct)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($jml_pembelian_tahun->jml_total_nov)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($jml_pembelian_tahun->jml_total_dec)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($jml_pembelian->jml_total)).'</td>
						</tr>
					';
				$no++;
				}	
				$html .= '
			</table>
			';

			$awal = ($jml_awal->persediaan_awal_jan + $jml_awal->persediaan_awal_feb + $jml_awal->persediaan_awal_mar + $jml_awal->persediaan_awal_apr + $jml_awal->persediaan_awal_may + $jml_awal->persediaan_awal_jun + $jml_awal->persediaan_awal_jul + $jml_awal->persediaan_awal_aug + $jml_awal->persediaan_awal_sep + $jml_awal->persediaan_awal_oct + $jml_awal->persediaan_awal_nov + $jml_awal->persediaan_awal_dec);

			$html .= '
			<table width="100%" cellspacing="0" cellpadding="2" border="1">
				<tr class="header_kolom">
					<td style="width:7%; vertical-align: left; text-align:left">Persediaan Awal Brg Dagangan</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_awal->persediaan_awal_jan)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_awal->persediaan_awal_feb)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_awal->persediaan_awal_mar)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_awal->persediaan_awal_apr)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_awal->persediaan_awal_may)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_awal->persediaan_awal_jun)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_awal->persediaan_awal_jul)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_awal->persediaan_awal_aug)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_awal->persediaan_awal_sep)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_awal->persediaan_awal_oct)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_awal->persediaan_awal_nov)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_awal->persediaan_awal_dec)).'</td>
					<td width="8%" class="h_kanan">'.number_format(nsi_round($awal)).'</td>
					
				</tr>
			</table>';

			$html .= '
			<table width="100%" cellspacing="0" cellpadding="2" border="1">
				<tr class="header_kolom">
					<td style="width:7%; vertical-align: left; text-align:left">Pembelian Bersih</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_pembelian_tahun->jml_total_jan)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_pembelian_tahun->jml_total_feb)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_pembelian_tahun->jml_total_mar)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_pembelian_tahun->jml_total_apr)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_pembelian_tahun->jml_total_may)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_pembelian_tahun->jml_total_jun)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_pembelian_tahun->jml_total_jul)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_pembelian_tahun->jml_total_aug)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_pembelian_tahun->jml_total_sep)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_pembelian_tahun->jml_total_oct)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_pembelian_tahun->jml_total_nov)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_pembelian_tahun->jml_total_dec)).'</td>
					<td width="8%" class="h_kanan">'.number_format(nsi_round($bersih = ($jml_pembelian->jml_total))).'</td>
				</tr>
			</table>';

			$html .= '<table width="100%" cellspacing="0" cellpadding="2" border="1">
				<tr class="header_kolom">
					<td style="width:7%; vertical-align: left; text-align:left">Barang yang Tersedia Untuk Dijual</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($sedia_jan = $jml_pembelian_tahun->jml_total_jan + $jml_pembelian_awal_tahun->jml_total_jan)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($sedia_feb = $jml_pembelian_tahun->jml_total_feb + $jml_pembelian_awal_tahun->jml_total_feb)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($sedia_mar = $jml_pembelian_tahun->jml_total_mar + $jml_pembelian_awal_tahun->jml_total_mar)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($sedia_apr = $jml_pembelian_tahun->jml_total_apr + $jml_pembelian_awal_tahun->jml_total_apr)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($sedia_may = $jml_pembelian_tahun->jml_total_may + $jml_pembelian_awal_tahun->jml_total_may)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($sedia_jun = $jml_pembelian_tahun->jml_total_jun + $jml_pembelian_awal_tahun->jml_total_jun)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($sedia_jul = $jml_pembelian_tahun->jml_total_jul + $jml_pembelian_awal_tahun->jml_total_jul)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($sedia_aug = $jml_pembelian_tahun->jml_total_aug + $jml_pembelian_awal_tahun->jml_total_aug)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($sedia_sep = $jml_pembelian_tahun->jml_total_sep + $jml_pembelian_awal_tahun->jml_total_sep)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($sedia_oct = $jml_pembelian_tahun->jml_total_oct + $jml_pembelian_awal_tahun->jml_total_oct)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($sedia_nov = $jml_pembelian_tahun->jml_total_nov + $jml_pembelian_awal_tahun->jml_total_nov)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($sedia_dec = $jml_pembelian_tahun->jml_total_dec + $jml_pembelian_awal_tahun->jml_total_dec)).'</td>
					<td width="8%" class="h_kanan">'.number_format(nsi_round($bersih = ($jml_pembelian->jml_total + 4328182))).'</td>
				</tr>
			</table>';

			$html .= '<table width="100%" cellspacing="0" cellpadding="2" border="1">
				<tr class="header_kolom">
					<td style="width:7%; vertical-align: left; text-align:left">Persediaan Akhir Brng Dagangan</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($ahir_jan = $jml_pembelian_akhir_tahun->jml_total_jan)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($ahir_feb = $jml_pembelian_akhir_tahun->jml_total_feb)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($ahir_mar = $jml_pembelian_akhir_tahun->jml_total_mar)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($ahir_apr = $jml_pembelian_akhir_tahun->jml_total_apr)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($ahir_may = $jml_pembelian_akhir_tahun->jml_total_may)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($ahir_jun = $jml_pembelian_akhir_tahun->jml_total_jun)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($ahir_jul = $jml_pembelian_akhir_tahun->jml_total_jul)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($ahir_aug = $jml_pembelian_akhir_tahun->jml_total_aug)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($ahir_sep = $jml_pembelian_akhir_tahun->jml_total_sep)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($ahir_oct = $jml_pembelian_akhir_tahun->jml_total_oct)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($ahir_nov = $jml_pembelian_akhir_tahun->jml_total_nov)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($ahir_dec = $jml_pembelian_akhir_tahun->jml_total_dec)).'</td>
					<td width="8%" class="h_kanan">'.number_format(nsi_round($bersih_akhir = ($jml_pembelian_akhir->jml_total))).'</td>
				</tr>
			</table>';

			$html .= '<table width="100%" cellspacing="0" cellpadding="2" border="1">
				<tr class="header_kolom">
					<td style="width:7%; vertical-align: left; text-align:left">Total HPP</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($hpp_jan = $sedia_jan - $ahir_jan)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($hpp_feb = $sedia_feb - $ahir_feb)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($hpp_mar = $sedia_mar - $ahir_mar)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($hpp_apr = $sedia_apr - $ahir_apr)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($hpp_may = $sedia_may - $ahir_may)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($hpp_jun = $sedia_jun - $ahir_jun)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($hpp_jul = $sedia_jul - $ahir_jul)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($hpp_aug = $sedia_aug - $ahir_aug)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($hpp_sep = $sedia_sep - $ahir_sep)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($hpp_oct = $sedia_oct - $ahir_oct)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($hpp_nov = $sedia_nov - $ahir_nov)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($hpp_dec = $sedia_dec - $ahir_dec)).'</td>
					<td width="8%" class="h_kanan">'.number_format(nsi_round($hpp_tot = $tot_bersih = ($bersih - $bersih_akhir))).'</td>
				</tr>
			</table>';

			$html .= '<h3> Rugi Laba Kotor </h3>';
			$html .= '
			<table width="100%" cellspacing="0" cellpadding="3" border="1">
				<tr class="header_kolom">
					<th style="width:7%; vertical-align: middle; text-align:middle"> </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Jan </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Feb </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Mar </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Apr </th>
					<th style="width:7%; vertical-align: middle; text-align:center">May </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Jun </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Jul </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Aug </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Sep </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Oct </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Nov </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Dec </th>
					<th style="width:8%; vertical-align: middle; text-align:center"> Total  </th>
				</tr>
			</table>';
			$html .= '
			<table width="100%" cellspacing="0" cellpadding="2" border="1">
				<tr>
					<td style="width:7%; vertical-align: left; text-align:left">Jumlah</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_kotor_jan = $penj_jan - $hpp_jan)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_kotor_feb = $penj_feb - $hpp_feb)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_kotor_mar = $penj_mar - $hpp_mar)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_kotor_apr = $penj_apr - $hpp_apr)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_kotor_may = $penj_may - $hpp_may)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_kotor_jun = $penj_jun - $hpp_jun)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_kotor_jul = $penj_jul - $hpp_jul)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_kotor_aug = $penj_aug - $hpp_aug)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_kotor_sep = $penj_sep - $hpp_sep)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_kotor_oct = $penj_oct - $hpp_oct)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_kotor_nov = $penj_nov - $hpp_nov)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_kotor_dec = $penj_dec - $hpp_dec)).'</td>
					<td width="8%" class="h_kanan">'.number_format(nsi_round($laba_kotor = ($laba_kotor_jan + $laba_kotor_feb + $laba_kotor_mar + $laba_kotor_apr + $laba_kotor_may + $laba_kotor_jun + $laba_kotor_jul + $laba_kotor_aug + $laba_kotor_sep + $laba_kotor_oct + $laba_kotor_nov + $laba_kotor_dec))).'</td>
				</tr>
			</table>';
			
			if(empty($laba_kotor_jan)){
				$persen_jan = 0;
			} else {
				$persen_jan = (($laba_kotor_jan/$penj_jan) * 100);
			}
			if(empty($laba_kotor_feb)){
				$persen_feb = 0;
			} else {
				$persen_feb = (($laba_kotor_feb/$penj_feb) * 100);
			}
			if(empty($laba_kotor_mar)){
				$persen_mar = 0;
			} else {
				$persen_mar = (($laba_kotor_mar/$penj_mar) * 100);
			}
			if(empty($laba_kotor_apr)){
				$persen_apr = 0;
			} else {
				$persen_apr = (($laba_kotor_apr/$penj_apr) * 100);
			}
			if(empty($laba_kotor_may)){
				$persen_may = 0;
			} else {
				$persen_may = (($laba_kotor_may/$penj_may) * 100);
			}
			if(empty($laba_kotor_jun)){
				$persen_jun = 0;
			} else {
				$persen_jun = (($laba_kotor_jun/$penj_jun) * 100);
			}
			if(empty($laba_kotor_jul)){
				$persen_jul = 0;
			} else {
				$persen_jul = (($laba_kotor_jul/$penj_jul) * 100);
			}
			if(empty($laba_kotor_aug)){
				$persen_aug = 0;
			} else {
				$persen_aug = (($laba_kotor_aug/$penj_aug) * 100);
			}
			if(empty($laba_kotor_sep)){
				$persen_sep = 0;
			} else {
				$persen_sep = (($laba_kotor_sep/$penj_sep) * 100);
			}
			if(empty($laba_kotor_oct)){
				$persen_oct = 0;
			} else {
				$persen_oct = (($laba_kotor_oct/$penj_oct) * 100);
			}
			if(empty($laba_kotor_nov)){
				$persen_nov = 0;
			} else {
				$persen_nov = (($laba_kotor_nov/$penj_nov) * 100);
			}
			if(empty($laba_kotor_dec)){
				$persen_dec = 0;
			} else {
				$persen_dec = (($laba_kotor_dec/$penj_dec) * 100);
			}
			if(empty($laba_kotor)){
				$persen_tot = 0;
			} else {
				$persen_tot = (($laba_kotor/$pendapatan) * 100);
			}
			
			$html .= '<table width="100%" cellspacing="0" cellpadding="2" border="1">
				<tr class="header_kolom">
					<td style="width:7%; vertical-align: left; text-align:left">Persentase</td>
					<td width="7%" class="h_kanan">'.number_format($persen_jan,2).'</td>
					<td width="7%" class="h_kanan">'.number_format($persen_feb,2).'</td>
					<td width="7%" class="h_kanan">'.number_format($persen_mar,2).'</td>
					<td width="7%" class="h_kanan">'.number_format($persen_apr,2).'</td>
					<td width="7%" class="h_kanan">'.number_format($persen_may,2).'</td>
					<td width="7%" class="h_kanan">'.number_format($persen_jun,2).'</td>
					<td width="7%" class="h_kanan">'.number_format($persen_jul,2).'</td>
					<td width="7%" class="h_kanan">'.number_format($persen_aug,2).'</td>
					<td width="7%" class="h_kanan">'.number_format($persen_sep,2).'</td>
					<td width="7%" class="h_kanan">'.number_format($persen_oct,2).'</td>
					<td width="7%" class="h_kanan">'.number_format($persen_nov,2).'</td>
					<td width="7%" class="h_kanan">'.number_format($persen_dec,2).'</td>
					<td width="8%" class="h_kanan">'.number_format($persen_tot,2).'</td>
				</tr>
			</table>';
			$html .= '<h3> Biaya - Biaya Usaha </h3>';
			$html .= '
			<table width="100%" cellspacing="0" cellpadding="3" border="1">
				<tr class="header_kolom">
					<th style="width:7%; vertical-align: middle; text-align:middle"> </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Jan </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Feb </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Mar </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Apr </th>
					<th style="width:7%; vertical-align: middle; text-align:center">May </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Jun </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Jul </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Aug </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Sep </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Oct </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Nov </th>
					<th style="width:7%; vertical-align: middle; text-align:center">Dec </th>
					<th style="width:8%; vertical-align: middle; text-align:center"> Total  </th>
				</tr>';
				$no=1;
				foreach ($data_biaya_usaha as $rows) {
					$html .= '
						<tr>
							<td style="width:7%; vertical-align: left; text-align:left"> '.$rows->jns_trans.'</td>
							<td class="h_kanan"> '.number_format(nsi_round($rows->Jan)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($rows->Feb)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($rows->Mar)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($rows->Apr)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($rows->May)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($rows->Jun)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($rows->Jul)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($rows->Aug)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($rows->Sep)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($rows->Oct)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($rows->Nov)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($rows->Dec)).'</td>
							<td class="h_kanan"> '.number_format(nsi_round($rows->TOTAL)).'</td>
						</tr>
					';
				$no++;
				}	
			$html .='
			</table>';
			$tot_biaya_usaha = ($jml_total_usaha->jml_total);
			$html .= 
				'<table width="100%" cellspacing="0" cellpadding="2" border="1">
					<tr class="header_kolom">
					<td style="width:7%; vertical-align: left; text-align:left"> Jumlah</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($biaya_jan = $jml_biaya_usaha_tahun->jml_total_jan)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($biaya_feb = $jml_biaya_usaha_tahun->jml_total_feb)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($biaya_mar = $jml_biaya_usaha_tahun->jml_total_mar)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($biaya_apr = $jml_biaya_usaha_tahun->jml_total_apr)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($biaya_may = $jml_biaya_usaha_tahun->jml_total_may)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($biaya_jun = $jml_biaya_usaha_tahun->jml_total_jun)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($biaya_jul = $jml_biaya_usaha_tahun->jml_total_jul)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($biaya_aug = $jml_biaya_usaha_tahun->jml_total_aug)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($biaya_sep = $jml_biaya_usaha_tahun->jml_total_sep)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($biaya_oct = $jml_biaya_usaha_tahun->jml_total_oct)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($biaya_nov = $jml_biaya_usaha_tahun->jml_total_nov)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($biaya_dec = $jml_biaya_usaha_tahun->jml_total_dec)).'</td>
					<td width="8%" class="h_kanan">'.number_format(nsi_round($tot_biaya_usaha)).'</td>
					</tr>
				</table>';
			
				$html .= 
				'<table width="100%" cellspacing="0" cellpadding="2" border="1">
					<tr class="header_kolom">
					<td style="width:7%; vertical-align: left; text-align:left"> Rugi Laba Usaha</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_jan = $laba_kotor_jan - $biaya_jan)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_feb = $laba_kotor_feb - $biaya_feb)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_mar = $laba_kotor_mar - $biaya_mar)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_apr = $laba_kotor_apr - $biaya_apr)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_may = $laba_kotor_may - $biaya_may)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_jun = $laba_kotor_jun - $biaya_jun)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_jul = $laba_kotor_jul - $biaya_jul)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_aug = $laba_kotor_aug - $biaya_aug)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_sep = $laba_kotor_sep - $biaya_sep)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_oct = $laba_kotor_oct - $biaya_oct)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_nov = $laba_kotor_nov - $biaya_nov)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_dec = $laba_kotor_dec - $biaya_dec)).'</td>
					<td width="8%" class="h_kanan">'.number_format(nsi_round($laba_bersih_tot = $laba_bersih_jan + $laba_bersih_feb + $laba_bersih_mar + $laba_bersih_apr + $laba_bersih_may + $laba_bersih_jun + $laba_bersih_jul + $laba_bersih_aug + $laba_bersih_sep + $laba_bersih_oct + $laba_bersih_nov + $laba_bersih_dec)).'</td>
					</tr>
				</table>';
				$html .= 
				'<table width="100%" cellspacing="0" cellpadding="2" border="1">
					<tr class="header_kolom">
					<td style="width:7%; vertical-align: left; text-align:left"> Rugi Laba Sebelum Pajak (EBT)</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_jan = $laba_kotor_jan - $biaya_jan)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_feb = $laba_kotor_feb - $biaya_feb)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_mar = $laba_kotor_mar - $biaya_mar)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_apr = $laba_kotor_apr - $biaya_apr)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_may = $laba_kotor_may - $biaya_may)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_jun = $laba_kotor_jun - $biaya_jun)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_jul = $laba_kotor_jul - $biaya_jul)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_aug = $laba_kotor_aug - $biaya_aug)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_sep = $laba_kotor_sep - $biaya_sep)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_oct = $laba_kotor_oct - $biaya_oct)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_nov = $laba_kotor_nov - $biaya_nov)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_bersih_dec = $laba_kotor_dec - $biaya_dec)).'</td>
					<td width="8%" class="h_kanan">'.number_format(nsi_round($laba_bersih_tot = $laba_bersih_jan + $laba_bersih_feb + $laba_bersih_mar + $laba_bersih_apr + $laba_bersih_may + $laba_bersih_jun + $laba_bersih_jul + $laba_bersih_aug + $laba_bersih_sep + $laba_bersih_oct + $laba_bersih_nov + $laba_bersih_dec)).'</td>
					</tr>
				</table>';
				$html .= 
				'<table width="100%" cellspacing="0" cellpadding="2" border="1">
					<tr class="header_kolom">
					<td style="width:7%; vertical-align: left; text-align:left"> PPH 12.5%</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round(($pajak_jan = $laba_bersih_jan * 12.5)/100)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round(($pajak_feb = $laba_bersih_feb * 12.5)/100)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round(($pajak_mar = $laba_bersih_mar * 12.5)/100)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round(($pajak_apr = $laba_bersih_apr * 12.5)/100)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round(($pajak_may = $laba_bersih_may * 12.5)/100)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round(($pajak_jun = $laba_bersih_jun * 12.5)/100)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round(($pajak_jul = $laba_bersih_jul * 12.5)/100)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round(($pajak_aug = $laba_bersih_aug * 12.5)/100)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round(($pajak_sep = $laba_bersih_sep * 12.5)/100)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round(($pajak_oct = $laba_bersih_oct * 12.5)/100)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round(($pajak_nov = $laba_bersih_nov * 12.5)/100)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round(($pajak_dec = $laba_bersih_dec * 12.5)/100)).'</td>
					<td width="8%" class="h_kanan">'.number_format(nsi_round(($pajak_total = $laba_bersih_tot * 12.5)/100)).'</td>
					</tr>
				</table>';
				$html .= 
				'<table width="100%" cellspacing="0" cellpadding="2" border="1">
					<tr class="header_kolom">
					<td style="width:7%; vertical-align: left; text-align:left"> Rugi Laba Setelah Pajak (EAT)</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_set_pajak_jan = $laba_bersih_jan - ($laba_bersih_jan * 12.5)/100)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_set_pajak_feb = $laba_bersih_feb - ($laba_bersih_feb * 12.5)/100)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_set_pajak_mar = $laba_bersih_mar - ($laba_bersih_mar * 12.5)/100)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_set_pajak_apr = $laba_bersih_apr - ($laba_bersih_apr * 12.5)/100)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_set_pajak_may = $laba_bersih_may - ($laba_bersih_may * 12.5)/100)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_set_pajak_jun = $laba_bersih_jun - ($laba_bersih_jun * 12.5)/100)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_set_pajak_jul = $laba_bersih_jul - ($laba_bersih_jul * 12.5)/100)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_set_pajak_aug = $laba_bersih_aug - ($laba_bersih_aug * 12.5)/100)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_set_pajak_sep = $laba_bersih_sep - ($laba_bersih_sep * 12.5)/100)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_set_pajak_oct = $laba_bersih_oct - ($laba_bersih_oct * 12.5)/100)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_set_pajak_nov = $laba_bersih_nov - ($laba_bersih_nov * 12.5)/100)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($laba_set_pajak_dec = $laba_bersih_dec - ($laba_bersih_dec * 12.5)/100)).'</td>
					<td width="8%" class="h_kanan">'.number_format(nsi_round($laba_set_pajak_tot = $laba_bersih_tot - ($laba_bersih_tot * 12.5)/100)).'</td>
					</tr>
				</table>';
				$html .='<br /><br /><br /><br /><br />';
				$html .= 
				'<h3> SHU yang dibagikan </h3>
				<table width="100%" cellspacing="0" cellpadding="2" border="1">
					<tr class="header_kolom">
						<th style="width:7%; vertical-align: middle; text-align:center" > SHU Dana  </th>
						<th style="width:7%; vertical-align: middle; text-align:center">Jan </th>
						<th style="width:7%; vertical-align: middle; text-align:center">Feb </th>
						<th style="width:7%; vertical-align: middle; text-align:center">Mar </th>
						<th style="width:7%; vertical-align: middle; text-align:center">Apr </th>
						<th style="width:7%; vertical-align: middle; text-align:center">May </th>
						<th style="width:7%; vertical-align: middle; text-align:center">Jun </th>
						<th style="width:7%; vertical-align: middle; text-align:center">Jul </th>
						<th style="width:7%; vertical-align: middle; text-align:center">Aug </th>
						<th style="width:7%; vertical-align: middle; text-align:center">Sep </th>
						<th style="width:7%; vertical-align: middle; text-align:center">Oct </th>
						<th style="width:7%; vertical-align: middle; text-align:center">Nov </th>
						<th style="width:7%; vertical-align: middle; text-align:center">Dec </th>
						<th style="width:8%; vertical-align: middle; text-align:center"> Total  </th>
					</tr>
				';

				$html .='
					<tr>
						<td>Anggota (50%) </td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_jan * 50)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_feb * 50)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_mar * 50)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_apr * 50)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_may * 50)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_jun * 50)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_jul * 50)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_aug * 50)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_sep * 50)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_oct * 50)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_nov * 50)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_dec * 50)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_tot * 50)/100)).'</td>
					</tr>
					<tr>
						<td>Cadangan (20%) </td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_jan * 20)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_feb * 20)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_mar * 20)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_apr * 20)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_may * 20)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_jun * 20)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_jul * 20)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_aug * 20)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_sep * 20)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_oct * 20)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_nov * 20)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_dec * 20)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_tot * 20)/100)).'</td>
					</tr>
					<tr>
						<td>Pegawai (10%) </td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_jan * 10)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_feb * 10)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_mar * 10)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_apr * 10)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_may * 10)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_jun * 10)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_jul * 10)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_aug * 10)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_sep * 10)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_oct * 10)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_nov * 10)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_dec * 10)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_tot * 10)/100)).'</td>
					</tr>

					<tr>
						<td>Pmbngnn Daerah Kerja (5%) </td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_jan * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_feb * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_mar * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_apr * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_may * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_jun * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_jul * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_aug * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_sep * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_oct * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_nov * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_dec * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_tot * 5)/100)).'</td>
					</tr>

					<tr>
						<td>Sosial (5%) </td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_jan * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_feb * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_mar * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_apr * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_may * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_jun * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_jul * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_aug * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_sep * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_oct * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_nov * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_dec * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_tot * 5)/100)).'</td>
					</tr>

					<tr>
						<td>Kesejahteraan Pegawai (5%) </td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_jan * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_feb * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_mar * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_apr * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_may * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_jun * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_jul * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_aug * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_sep * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_oct * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_nov * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_dec * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_tot * 5)/100)).'</td>
					</tr>

					<tr>
						<td>Pendidikan (5%) </td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_jan * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_feb * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_mar * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_apr * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_may * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_jun * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_jul * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_aug * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_sep * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_oct * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_nov * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_dec * 5)/100)).'</td>
						<td class="h_kanan">'.number_format(nsi_round(($laba_set_pajak_tot * 5)/100)).'</td>
					</tr>

					<tr class="header_kolom">
						<td>Total (100%) </td>
						<td class="h_kanan">'.number_format(nsi_round($laba_set_pajak_jan)).'</td>
						<td class="h_kanan">'.number_format(nsi_round($laba_set_pajak_feb)).'</td>
						<td class="h_kanan">'.number_format(nsi_round($laba_set_pajak_mar)).'</td>
						<td class="h_kanan">'.number_format(nsi_round($laba_set_pajak_apr)).'</td>
						<td class="h_kanan">'.number_format(nsi_round($laba_set_pajak_may)).'</td>
						<td class="h_kanan">'.number_format(nsi_round($laba_set_pajak_jun)).'</td>
						<td class="h_kanan">'.number_format(nsi_round($laba_set_pajak_jul)).'</td>
						<td class="h_kanan">'.number_format(nsi_round($laba_set_pajak_aug)).'</td>
						<td class="h_kanan">'.number_format(nsi_round($laba_set_pajak_sep)).'</td>
						<td class="h_kanan">'.number_format(nsi_round($laba_set_pajak_oct)).'</td>
						<td class="h_kanan">'.number_format(nsi_round($laba_set_pajak_nov)).'</td>
						<td class="h_kanan">'.number_format(nsi_round($laba_set_pajak_dec)).'</td>
						<td class="h_kanan">'.number_format(nsi_round($laba_set_pajak_tot)).'</td>
					</tr>
				</table>';

		$pdf->nsi_html($html);
		//ob_end_clean();
		$pdf->Output('lap_rugi_laba_'.date('Ymd_His') . '.pdf', 'I');
	} 
}