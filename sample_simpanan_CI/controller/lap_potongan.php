<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lap_potongan extends OperatorController {

public function __construct() {
		parent::__construct();	
		$this->load->helper('fungsi');
		$this->load->model('general_m');
		$this->load->model('lap_potongan_m');
	}	

	public function index() {
		$this->load->library("pagination");
		$this->data['judul_browser'] 	= 'Laporan';
		$this->data['judul_utama'] 		= 'Laporan';
		$this->data['judul_sub'] 		= 'Potongan';
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

		
		$this->data['isi'] = $this->load->view('lap_potongan_list_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}

	function cetak() {
		$data_bus 			= $this->lap_potongan_m->get_data_bus();
		$jml_bus 			= $this->lap_potongan_m->get_jml_bus();
		$jml_bus_tahun 		= $this->lap_potongan_m->get_jml_bus_tahun();
		$jml_bus_tahun_pajak= $this->lap_potongan_m->get_jml_bus_tahun_pajak();
		
		$data_operasional 	= $this->lap_potongan_m->get_data_operasional();
		$jml_operasional 	= $this->lap_potongan_m->get_jml_operasional();
		$jml_operasional_tahun 		= $this->lap_potongan_m->get_jml_operasional_tahun();

		$data_admin 		= $this->lap_potongan_m->get_data_admin();
		$jml_admin 			= $this->lap_potongan_m->get_jml_admin();
		$jml_admin_tahun 	= $this->lap_potongan_m->get_jml_admin_tahun();

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
         '.$pdf->nsi_box($text = '<span class="txt_judul">Laporan Bus Angkutan Karyawan Periode '.$tgl_periode_txt.'</span>', $width = '100%', $spacing = '1', $padding = '1', $border = '0', $align = 'center').'';

		$html .= 
		'<h3> Penghasilan Jasa Sewa Bus </h3>
			<table width="100%" cellspacing="0" cellpadding="3" border="1">
				<tr class="header_kolom">
					<th style="width:7%; vertical-align: middle; text-align:center"> No Polisi</th>
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
				foreach ($data_bus as $rows) {
					$html .= '
					<table width="100%" cellspacing="0" cellpadding="3" border="1">
						<tr>
							<td width="7%" class="h_kiri"> '.$rows->no_polisi.'</td>
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
				$pendapatan = ($jml_bus->jml_total);
				$html .= 
				'<table width="100%" cellspacing="0" cellpadding="3" border="1">
					<tr class="header_kolom">
					<th width="7%"> Jumlah</th>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_jan)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_feb)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_mar)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_apr)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_may)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_jun)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_jul)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_aug)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_sep)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_oct)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_nov)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_dec)).'</td>
					<td width="8%" class="h_kanan">'.number_format(nsi_round($pendapatan)).'</td>
					</tr>
				</table>';

				$html .= 
				'<table width="100%" cellspacing="0" cellpadding="3" border="1">
					<tr class="header_kolom">
					<td width="7%" class="h_kiri"> Pajak (2%)</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun_pajak->jml_total_jan_pajak)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun_pajak->jml_total_feb_pajak)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun_pajak->jml_total_mar_pajak)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun_pajak->jml_total_apr_pajak)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun_pajak->jml_total_may_pajak)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun_pajak->jml_total_jun_pajak)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun_pajak->jml_total_jul_pajak)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun_pajak->jml_total_aug_pajak)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun_pajak->jml_total_sep_pajak)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun_pajak->jml_total_oct_pajak)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun_pajak->jml_total_nov_pajak)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun_pajak->jml_total_dec_pajak)).'</td>
					<td width="8%" class="h_kanan">'.number_format(nsi_round(($pendapatan*2)/100)).'</td>
				</tr>
				</table>';

				$html .= 
				'<table width="100%" cellspacing="0" cellpadding="3" border="1">
					<tr class="header_kolom">
					<td width="7%" class="h_kiri"> Setelah Pajak</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_jan - $jml_bus_tahun_pajak->jml_total_jan_pajak)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_feb - $jml_bus_tahun_pajak->jml_total_feb_pajak)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_mar - $jml_bus_tahun_pajak->jml_total_mar_pajak)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_apr - $jml_bus_tahun_pajak->jml_total_apr_pajak)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_may - $jml_bus_tahun_pajak->jml_total_may_pajak)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_jun - $jml_bus_tahun_pajak->jml_total_jun_pajak)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_jul - $jml_bus_tahun_pajak->jml_total_jul_pajak)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_aug - $jml_bus_tahun_pajak->jml_total_aug_pajak)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_sep - $jml_bus_tahun_pajak->jml_total_sep_pajak)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_oct - $jml_bus_tahun_pajak->jml_total_oct_pajak)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_nov - $jml_bus_tahun_pajak->jml_total_nov_pajak)).'</td>
					<td width="7%" class="h_kanan">'.number_format(nsi_round($jml_bus_tahun->jml_total_dec - $jml_bus_tahun_pajak->jml_total_dec_pajak)).'</td>
					<td width="8%" class="h_kanan">'.number_format(nsi_round($pendapatan - ($pendapatan*2)/100)).'</td>
				</tr>
				</table>
					';

		$html .= '
		<h3> Biaya Operasional </h3>
			<table width="100%" cellspacing="0" cellpadding="3" border="1">
				<tr class="header_kolom">
					<th style="width:7%; vertical-align: middle; text-align:center" > Biaya </th>
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
				foreach ($data_operasional as $rows) {
					$html .= '
						<tr>
							<td class="h_kiri"> '.$rows->jns_trans.'</td>
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
						</tr>';
				$no++;
				}
				$biaya_operasional = ($jml_operasional->jml_total);
		$html .= 
				'<tr class="header_kolom">
					<td class="h_kiri"> Jumlah</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_operasional_tahun->jml_total_jan)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_operasional_tahun->jml_total_feb)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_operasional_tahun->jml_total_mar)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_operasional_tahun->jml_total_apr)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_operasional_tahun->jml_total_may)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_operasional_tahun->jml_total_jun)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_operasional_tahun->jml_total_jul)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_operasional_tahun->jml_total_aug)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_operasional_tahun->jml_total_sep)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_operasional_tahun->jml_total_oct)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_operasional_tahun->jml_total_nov)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_operasional_tahun->jml_total_dec)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($biaya_operasional)).'</td>
				</tr>';
		$html .= '</table>';

		$html .= 
		'<h3> Biaya adm dan umum </h3>
			<table width="100%" cellspacing="0" cellpadding="3" border="1">
				<tr class="header_kolom">
					<th style="width:7%; vertical-align: middle; text-align:center" > Biaya </th>
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
				foreach ($data_admin as $rows) {
					$html .= '
						<tr>
							<td class="h_kiri"> '.$rows->jns_trans.'</td>
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
						</tr>';
				$no++;
				}
				$biaya_admin = ($jml_admin->jml_total);
				$html .= 
				'<tr class="header_kolom">
					<td class="h_kiri"> Jumlah</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_jan)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_feb)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_mar)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_apr)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_may)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_jun)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_jul)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_aug)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_sep)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_oct)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_nov)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_dec)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($biaya_admin)).'</td>
				</tr>';
		$html .= 
		'</table>';
		$html .='<br />';
		$html .= 
		'<h3> Jumlah Biaya Usaha </h3>
			<table width="100%" cellspacing="0" cellpadding="3" border="1">
				<tr class="header_kolom">
					<th style="width:7%; vertical-align: middle; text-align:center" > Biaya </th>
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
		$html .= 
				'<tr class="header_kolom">
					<td class="h_kiri"> Jumlah</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_jan + $jml_operasional_tahun->jml_total_jan)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_feb + $jml_operasional_tahun->jml_total_feb)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_mar + $jml_operasional_tahun->jml_total_mar)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_apr + $jml_operasional_tahun->jml_total_apr)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_may + $jml_operasional_tahun->jml_total_may)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_jun + $jml_operasional_tahun->jml_total_jun)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_jul + $jml_operasional_tahun->jml_total_jul)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_aug + $jml_operasional_tahun->jml_total_aug)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_sep + $jml_operasional_tahun->jml_total_sep)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_oct + $jml_operasional_tahun->jml_total_oct)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_nov + $jml_operasional_tahun->jml_total_nov)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($jml_admin_tahun->jml_total_dec + $jml_operasional_tahun->jml_total_dec)).'</td>
					<td class="h_kanan">'.number_format(nsi_round($biaya_admin + $biaya_operasional)).'</td>
				</tr>
			</table>';
		$html .= 
		'<h3> Pendapatan Hasil Usaha </h3>
			<table width="100%" cellspacing="0" cellpadding="3" border="1">
				<tr class="header_kolom">
					<th style="width:7%; vertical-align: middle; text-align:center" > PHU </th>
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
		
		$html.= '
		<tr class="header_kolom">
			<td class="h_kanan"> Jumlah </td>
			<td class="h_kanan">'.number_format(nsi_round(($jml_bus_tahun->jml_total_jan - $jml_bus_tahun_pajak->jml_total_jan_pajak) - ($jml_admin_tahun->jml_total_jan + $jml_operasional_tahun->jml_total_jan))).'</td>
			<td class="h_kanan">'.number_format(nsi_round(($jml_bus_tahun->jml_total_feb - $jml_bus_tahun_pajak->jml_total_feb_pajak) - ($jml_admin_tahun->jml_total_feb + $jml_operasional_tahun->jml_total_feb))).'</td>
			<td class="h_kanan">'.number_format(nsi_round(($jml_bus_tahun->jml_total_mar - $jml_bus_tahun_pajak->jml_total_mar_pajak) - ($jml_admin_tahun->jml_total_mar + $jml_operasional_tahun->jml_total_mar))).'</td>
			<td class="h_kanan">'.number_format(nsi_round(($jml_bus_tahun->jml_total_apr - $jml_bus_tahun_pajak->jml_total_apr_pajak) - ($jml_admin_tahun->jml_total_apr + $jml_operasional_tahun->jml_total_apr))).'</td>
			<td class="h_kanan">'.number_format(nsi_round(($jml_bus_tahun->jml_total_may - $jml_bus_tahun_pajak->jml_total_may_pajak) - ($jml_admin_tahun->jml_total_may + $jml_operasional_tahun->jml_total_may))).'</td>
			<td class="h_kanan">'.number_format(nsi_round(($jml_bus_tahun->jml_total_jun - $jml_bus_tahun_pajak->jml_total_jun_pajak) - ($jml_admin_tahun->jml_total_jun + $jml_operasional_tahun->jml_total_jun))).'</td>
			<td class="h_kanan">'.number_format(nsi_round(($jml_bus_tahun->jml_total_jul - $jml_bus_tahun_pajak->jml_total_jul_pajak) - ($jml_admin_tahun->jml_total_jul + $jml_operasional_tahun->jml_total_jul))).'</td>
			<td class="h_kanan">'.number_format(nsi_round(($jml_bus_tahun->jml_total_aug - $jml_bus_tahun_pajak->jml_total_aug_pajak) - ($jml_admin_tahun->jml_total_aug + $jml_operasional_tahun->jml_total_aug))).'</td>
			<td class="h_kanan">'.number_format(nsi_round(($jml_bus_tahun->jml_total_sep - $jml_bus_tahun_pajak->jml_total_sep_pajak) - ($jml_admin_tahun->jml_total_sep + $jml_operasional_tahun->jml_total_sep))).'</td>
			<td class="h_kanan">'.number_format(nsi_round(($jml_bus_tahun->jml_total_oct - $jml_bus_tahun_pajak->jml_total_oct_pajak) - ($jml_admin_tahun->jml_total_oct + $jml_operasional_tahun->jml_total_oct))).'</td>
			<td class="h_kanan">'.number_format(nsi_round(($jml_bus_tahun->jml_total_nov - $jml_bus_tahun_pajak->jml_total_nov_pajak) - ($jml_admin_tahun->jml_total_nov + $jml_operasional_tahun->jml_total_nov))).'</td>
			<td class="h_kanan">'.number_format(nsi_round(($jml_bus_tahun->jml_total_dec - $jml_bus_tahun_pajak->jml_total_dec_pajak) - ($jml_admin_tahun->jml_total_dec + $jml_operasional_tahun->jml_total_dec))).'</td>
			<td class="h_kanan">'.number_format(nsi_round(($pendapatan - ($pendapatan*2)/100) - ($biaya_admin + $biaya_operasional))).'</td>
		</tr>
		</table>';
		$html .= 
		'<h3> SHU yang dibagikan </h3>
			<table width="100%" cellspacing="0" cellpadding="3" border="1">
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
				$shu_jan = ($jml_bus_tahun->jml_total_jan - $jml_bus_tahun_pajak->jml_total_jan_pajak) - ($jml_admin_tahun->jml_total_jan + $jml_operasional_tahun->jml_total_jan);
				$shu_feb = ($jml_bus_tahun->jml_total_feb - $jml_bus_tahun_pajak->jml_total_feb_pajak) - ($jml_admin_tahun->jml_total_feb + $jml_operasional_tahun->jml_total_feb);
				$shu_mar = ($jml_bus_tahun->jml_total_mar - $jml_bus_tahun_pajak->jml_total_mar_pajak) - ($jml_admin_tahun->jml_total_mar + $jml_operasional_tahun->jml_total_mar);
				$shu_apr = ($jml_bus_tahun->jml_total_apr - $jml_bus_tahun_pajak->jml_total_apr_pajak) - ($jml_admin_tahun->jml_total_apr + $jml_operasional_tahun->jml_total_apr);
				$shu_may = ($jml_bus_tahun->jml_total_may - $jml_bus_tahun_pajak->jml_total_may_pajak) - ($jml_admin_tahun->jml_total_may + $jml_operasional_tahun->jml_total_may);
				$shu_jun = ($jml_bus_tahun->jml_total_jun - $jml_bus_tahun_pajak->jml_total_jun_pajak) - ($jml_admin_tahun->jml_total_jun + $jml_operasional_tahun->jml_total_jun);
				$shu_jul = ($jml_bus_tahun->jml_total_jul - $jml_bus_tahun_pajak->jml_total_jul_pajak) - ($jml_admin_tahun->jml_total_jul + $jml_operasional_tahun->jml_total_jul);
				$shu_aug = ($jml_bus_tahun->jml_total_aug - $jml_bus_tahun_pajak->jml_total_aug_pajak) - ($jml_admin_tahun->jml_total_aug + $jml_operasional_tahun->jml_total_aug);
				$shu_sep = ($jml_bus_tahun->jml_total_sep - $jml_bus_tahun_pajak->jml_total_sep_pajak) - ($jml_admin_tahun->jml_total_sep + $jml_operasional_tahun->jml_total_sep);
				$shu_oct = ($jml_bus_tahun->jml_total_oct - $jml_bus_tahun_pajak->jml_total_oct_pajak) - ($jml_admin_tahun->jml_total_oct + $jml_operasional_tahun->jml_total_oct);
				$shu_nov = ($jml_bus_tahun->jml_total_nov - $jml_bus_tahun_pajak->jml_total_nov_pajak) - ($jml_admin_tahun->jml_total_nov + $jml_operasional_tahun->jml_total_nov);
				$shu_dec = ($jml_bus_tahun->jml_total_dec - $jml_bus_tahun_pajak->jml_total_dec_pajak) - ($jml_admin_tahun->jml_total_dec + $jml_operasional_tahun->jml_total_dec);
				$html.='
				<tr>
					<td>Anggota (50%) </td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jan * 50)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_feb * 50)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_mar * 50)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_apr * 50)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_may * 50)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jun * 50)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jul * 50)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_aug * 50)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_sep * 50)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_oct * 50)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_nov * 50)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_dec * 50)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jan + $shu_feb + $shu_mar + $shu_apr + $shu_may + $shu_jun + $shu_jul + $shu_aug + $shu_sep + $shu_oct + $shu_nov + $shu_dec) *50)/100).'</td>
				</tr>
				<tr>
					<td>Cadangan (20%) </td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jan * 20)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_feb * 20)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_mar * 20)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_apr * 20)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_may * 20)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jun * 20)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jul * 20)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_aug * 20)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_sep * 20)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_oct * 20)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_nov * 20)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_dec * 20)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jan + $shu_feb + $shu_mar + $shu_apr + $shu_may + $shu_jun + $shu_jul + $shu_aug + $shu_sep + $shu_oct + $shu_nov + $shu_dec) *20)/100).'</td>
				</tr>
				<tr>
					<td>Pegawai (10%) </td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jan * 10)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_feb * 10)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_mar * 10)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_apr * 10)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_may * 10)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jun * 10)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jul * 10)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_aug * 10)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_sep * 10)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_oct * 10)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_nov * 10)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_dec * 10)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jan + $shu_feb + $shu_mar + $shu_apr + $shu_may + $shu_jun + $shu_jul + $shu_aug + $shu_sep + $shu_oct + $shu_nov + $shu_dec) *10)/100).'</td>
				</tr>
				<tr>
					<td>Pmbngnn Daerah Kerja (5%) </td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jan * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_feb * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_mar * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_apr * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_may * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jun * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jul * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_aug * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_sep * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_oct * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_nov * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_dec * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jan + $shu_feb + $shu_mar + $shu_apr + $shu_may + $shu_jun + $shu_jul + $shu_aug + $shu_sep + $shu_oct + $shu_nov + $shu_dec) *5)/100).'</td>
				</tr>
				<tr>
					<td>Sosial (5%) </td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jan * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_feb * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_mar * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_apr * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_may * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jun * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jul * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_aug * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_sep * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_oct * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_nov * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_dec * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jan + $shu_feb + $shu_mar + $shu_apr + $shu_may + $shu_jun + $shu_jul + $shu_aug + $shu_sep + $shu_oct + $shu_nov + $shu_dec) *5)/100).'</td>
				</tr>
				<tr>
					<td>Kesejahteraan Pegawai (5%) </td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jan * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_feb * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_mar * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_apr * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_may * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jun * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jul * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_aug * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_sep * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_oct * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_nov * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_dec * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jan + $shu_feb + $shu_mar + $shu_apr + $shu_may + $shu_jun + $shu_jul + $shu_aug + $shu_sep + $shu_oct + $shu_nov + $shu_dec) *5)/100).'</td>
				</tr>
				<tr>
					<td>Pendidikan (5%) </td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jan * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_feb * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_mar * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_apr * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_may * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jun * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jul * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_aug * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_sep * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_oct * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_nov * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_dec * 5)/100)).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jan + $shu_feb + $shu_mar + $shu_apr + $shu_may + $shu_jun + $shu_jul + $shu_aug + $shu_sep + $shu_oct + $shu_nov + $shu_dec) *5)/100).'</td>
				</tr>
				<tr class="header_kolom">
					<td> Total (100%) </td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jan))).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_feb))).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_mar))).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_apr))).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_may))).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jun))).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jul))).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_aug))).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_sep))).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_oct))).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_nov))).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_dec))).'</td>
					<td class="h_kanan">'.number_format(nsi_round(($shu_jan + $shu_feb + $shu_mar + $shu_apr + $shu_may + $shu_jun + $shu_jul + $shu_aug + $shu_sep + $shu_oct + $shu_nov + $shu_dec))).'</td>
				</tr>
			</table>';
		
		$pdf->nsi_html($html);
		//ob_end_clean();
		$pdf->Output('lap_potongan_karyawan'.date('Ymd_His') . '.pdf', 'I');
	} 
}