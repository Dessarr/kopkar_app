<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lap_angsuran extends OperatorController {
	public function __construct() {
		parent::__construct();
		$this->load->helper('fungsi');
		$this->load->model('lap_angsuran_m');
		$this->load->model('general_m');
		//$this->load->model('target_pinjaman_m');
	}	

	public function index() {
		$this->load->library("pagination");
		$this->data['judul_browser'] = 'Laporan';
		$this->data['judul_utama'] = 'Laporan';
		$this->data['judul_sub'] = 'Angsuran Pinjaman';

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

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			//
		} else {
			$_GET['tgl_dari'] = date('Y') . '-01-01';
			$_GET['tgl_samp'] = date('Y') . '-12-31';
		}

		$config = array();
		$config["base_url"] 		= base_url() . "lap_angsuran/index/halaman";
		if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
		$config['first_url'] 		= $config['base_url'].'?'.http_build_query($_GET);
		$config["total_rows"] 		= $this->lap_angsuran_m->get_jml_data_kas();
		$config["per_page"] 		= 20;
		$config["uri_segment"] 		= 4;
		$config['num_links'] 		= 10;
		$config['use_page_numbers'] = TRUE;

		$config['full_tag_open'] 	= '<ul class="pagination">';
		$config['full_tag_close'] 	= '</ul>';

		$config['first_link'] 		= '&laquo; First';
		$config['first_tag_open'] 	= '<li class="prev page">';
		$config['first_tag_close'] 	= '</li>';

		$config['last_link'] 		= 'Last &raquo;';
		$config['last_tag_open'] 	= '<li class="next page">';
		$config['last_tag_close'] 	= '</li>';

		$config['next_link'] 		= 'Next &rarr;';
		$config['next_tag_open'] 	= '<li class="next page">';
		$config['next_tag_close'] 	= '</li>';

		$config['prev_link'] 		= '&larr; Previous';
		$config['prev_tag_open'] 	= '<li class="prev page">';
		$config['prev_tag_close'] 	= '</li>';

		$config['cur_tag_open'] 	= '<li class="active"><a href="">';
		$config['cur_tag_close'] 	= '</a></li>';

		$config['num_tag_open'] 	= '<li class="page">';
		$config['num_tag_close'] 	= '</li>';

		$this->pagination->initialize($config);
		$offset = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
		if($offset > 0) {
			$offset = ($offset * $config['per_page']) - $config['per_page'];
		}

		//$this->data["detail"] = $this->lap_pinjaman_m->get_transaksi_detail($config["per_page"], $offset);

		$this->data["data_kas"] = $this->lap_angsuran_m->get_transaksi_pinjaman($config["per_page"], $offset);
		$this->data["saldo_awal"] = $this->lap_angsuran_m->get_saldo_awal($config["per_page"], $offset);
		$this->data["saldo_sblm"] = $this->lap_angsuran_m->get_saldo_sblm();
		$this->data["halaman"] = $this->pagination->create_links();
		$this->data["offset"] = $offset;

		//$this->data["pinjaman"] = $this->lap_pinjaman_m->get_transaksi_pinjaman(); 
		
		$this->data['isi'] = $this->load->view('lap_angsuran_list_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}

	

	function cetak() {
		$saldo_sblm = $this->lap_angsuran_m->lap_transaksi_pinjaman();
		if($saldo_sblm == FALSE) {
			echo 'DATA KOSONG';
			exit();
		}

		$tgl_dari = $_REQUEST['tgl_dari'];
		$tgl_samp = $_REQUEST['tgl_samp'];
		$tgl_dari_txt = jin_date_ina($tgl_dari, 'p');
		$tgl_samp_txt = jin_date_ina($tgl_samp, 'p');
		$txt_periode = $tgl_dari_txt . ' - ' . $tgl_samp_txt;

		$this->load->library('Pdf');
		$pdf = new Pdf('L', 'mm', 'A5', true, 'UTF-8', false);
		$pdf->set_nsi_header(TRUE);
		$pdf->AddPage('L');
		$html = '<style>
					.h_tengah {text-align: center; font-size:10pt;}
					.h_kiri {text-align: left; font-size:10pt;}
					.h_kanan {text-align: right; font-size:10pt;}
					.txt_judul {font-size: 12pt; font-weight: bold; padding-bottom: 15px;}
					.txt_isi {font-size: 8pt;}
					.header_kolom {background-color: #cccccc; text-align: center; font-weight: bold;}
				</style>
				'.$pdf->nsi_box($text = '<span class="txt_judul">Laporan Angsuran Pinjaman Periode '.$txt_periode.'</span>', $width = '100%', $spacing = '1', $padding = '1', $border = '0', $align = 'center').'';
		$no = 1;
		$total_saldo = 0;
		$saldo = 0;
		
			$html.= '<br /><br />';
			$html.= '<table width="90%" cellspacing="0" cellpadding="2" border="1" nobr="true" class="txt_isi">
			<tr class="header_kolom">
				<th style="width:3%; vertical-align: middle"> No</th>
				<th style="width:8%; vertical-align: middle"> Tanggal Pinjam </th>
				<th style="width:15%; vertical-align: middle"> Nama </th>
				<th style="width:8%; vertical-align: middle"> Pinjaman Awal </th>
				<th style="width:5%; vertical-align: middle"> JW</th>
				<th style="width:5%; vertical-align: middle"> % </th>
				<th style="width:8%; vertical-align: middle"> Saldo Pinjaman </th>
				<th style="width:8%; vertical-align: middle"> Pokok </th>
				<th style="width:8%; vertical-align: middle"> Bunga </th>
				<th style="width:8%; vertical-align: middle"> Denda </th>
				<th style="width:8%; vertical-align: middle"> Jumlah </th>
				<th style="width:8%; vertical-align: middle"> Saldo Akhir</th>
				<th style="width:6%; vertical-align: middle"> Angsuran Ke</th>
				<th style="width:10%; vertical-align: middle"> Tgl. Bayar</th>
			</tr>';
			$no = 1;
			$d = 0;
			$k = 0;
			foreach ($saldo_sblm as $rows) {
				$jum = $rows->jumlah_bayar + $rows->bunga + $rows->denda_rp;
				$jum2 = $rows->jumlah_bayar + $rows->sisa_pokok;

				$tglD = explode(' ', $rows->tgl_pinjam);
				$txt_tanggalD = jin_date_ina($tglD[0],'p');
				$tglB = explode(' ', $rows->tgl_bayar);
				$txt_tanggalB = jin_date_ina($tglB[0],'p');
				
				$html.= '<tr>
					<td> '.$no++.' </td>
					<td> '.$txt_tanggalD.' </td>
					<td> '.$rows->no_ktp.'/'.$rows->nama.'</td>
					<td style="text-align: right"> '.number_format(nsi_round($rows->jumlah)).' </td>
					<td style="text-align: center"> '.$rows->lama_angsuran.' </td>
					<td style="text-align: center"> '.number_format(nsi_round($rows->jumlah_bunga)).' </td>
					<td style="text-align: right"> '.number_format(nsi_round($jum2)).' </td>
					<td style="text-align: right"> '.number_format(nsi_round($rows->jumlah_bayar)).' </td>
					<td style="text-align: right"> '.number_format(nsi_round($rows->bunga)).' </td>
					<td style="text-align: right"> '.number_format(nsi_round($rows->denda_rp)).' </td>
					<td style="text-align: right"> '.number_format(nsi_round($jum)).' </td>
					<td style="text-align: right"> '.number_format(nsi_round($rows->sisa_pokok)).' </td>
					<td style="text-align: center"> '.number_format(nsi_round($rows->bln_sudah_angsur)).' </td>
					<td style="text-align: center"> '.$txt_tanggalB.' </td>
				</tr>';
			}
			$html.= '</table>';

		$nilai_d = $this->lap_angsuran_m->get_jml_d();
		$d = $nilai_d->jml_total;

		
		$pdf->nsi_html($html);
		$pdf->Output('lap_angsuran'.date('Ymd_His') . '.pdf', 'I');
	}
}