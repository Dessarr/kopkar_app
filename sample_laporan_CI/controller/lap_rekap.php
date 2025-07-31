<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lap_rekap extends OperatorController {
	public function __construct() {
		parent::__construct();
		$this->load->helper('fungsi');
		$this->load->model('lap_rekap_m');
		$this->load->model('general_m');
		//$this->load->model('pengeluaran_pinjaman_m');
	}	

	public function index() {
		$this->load->library("pagination");
		$this->data['judul_browser'] = 'Laporan';
		$this->data['judul_utama'] = 'Laporan';
		$this->data['judul_sub'] = 'Rekapitulasi Tagihan';

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

		

		$config = array();
		$config["base_url"] 		= base_url() . "lap_rekap/index/halaman";
		if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
		$config['first_url'] 		= $config['base_url'].'?'.http_build_query($_GET);
		$config["total_rows"] 		= $this->lap_rekap_m->get_jml_data_kas();
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

		$this->data["data_kas"] = $this->lap_rekap_m->get_transaksi_pinjaman($config["per_page"], $offset);
		$this->data["saldo_awal"] = $this->lap_rekap_m->get_saldo_awal($config["per_page"], $offset);
		$this->data["saldo_sblm"] = $this->lap_rekap_m->get_saldo_sblm();
		$this->data["halaman"] = $this->pagination->create_links();
		$this->data["offset"] = $offset;

		//$this->data["pinjaman"] = $this->lap_pinjaman_m->get_transaksi_pinjaman(); 
		//$this->data['tagihan'] = $this->lap_rekap_m->get_tagihan();
		$this->data['isi'] = $this->load->view('lap_rekap_list_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}

	function cetak() {
		$saldo_sblm = $this->lap_rekap_m->lap_transaksi_pinjaman();
		if($saldo_sblm == FALSE) {
			echo 'DATA KOSONG';
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
					.h_tengah {text-align: center; font-size:10pt;}
					.h_kiri {text-align: left; font-size:10pt;}
					.h_kanan {text-align: right; font-size:10pt;}
					.txt_judul {font-size: 12pt; font-weight: bold; padding-bottom: 15px;}
					.txt_isi {font-size: 8pt;}
					.header_kolom {background-color: #cccccc; text-align: center; font-weight: bold;}
				</style>
				'.$pdf->nsi_box($text = '<span class="txt_judul">Laporan Rekapitulasi Tagihan Periode '.$txt_periode.'</span>', $width = '100%', $spacing = '1', $padding = '1', $border = '0', $align = 'center').'';
		$no = 1;
		$total_saldo = 0;
		$saldo = 0;
		
			$html.= '<br /><br />';
			$html.= '<table width="98%" cellspacing="0" cellpadding="2" border="1" nobr="true" class="txt_isi">
			<tr class="header_kolom">
				<th style="width:5%; vertical-align: middle "> No </th>
				<th style="width:10%; vertical-align: middle "> Tanggal </th>
				<th style="width:8%; vertical-align: middle "> Tagihan Hari Ini </th>
				<th style="width:10%; vertical-align: middle "> Target Pokok </th>
				<th style="width:10%; vertical-align: middle "> Target Bunga </th>
				<th style="width:8%; vertical-align: middle "> Tagihan Masuk</th>
				<th style="width:10%; vertical-align: middle "> Realisasi Pokok </th>
				<th style="width:10%; vertical-align: middle "> Realisasi Bunga </th>
				<th style="width:10%; vertical-align: middle "> Tagihan Bermasalah </th>
				<th style="width:10%; vertical-align: middle "> Tidak Bayar Pokok </th>
				<th style="width:10%; vertical-align: middle "> Tidak Bayar Bunga </th>
			</tr>';
			$no = 1;
			$d = 0;
			$k = 0;
			$tahun = substr($tanggal, 0, 4);
		$bulan = substr($tanggal, 5, 2);
		$tanggals = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
		for ($i=1; $i < $tanggals+1; $i++) { 
		$t = $tanggal.'-'.$i;
		$tglD = explode(' ', $t);
		$txt_tanggalD = jin_date_ina($tglD[0],'p');
		$jt = $this->db->query("SELECT pinjam_id,count(tempo) AS jml_tagihan FROM tempo_pinjaman where tempo BETWEEN '".$t."' AND '".$t."'")->row();
		$tp = $this->db->query("SELECT SUM(jumlah/lama_angsuran) as target_pokok, SUM((jumlah*bunga)/100) as target_bunga FROM tbl_pinjaman_h where id='".$jt->pinjam_id."'")->row();
		$tm = $this->db->query("SELECT COUNT(tgl_bayar) as tagihan_masuk, SUM(jumlah_bayar) as realisasi_pokok, SUM(bunga) as realisasi_bunga FROM tbl_pinjaman_d where tgl_bayar BETWEEN '".$tanggal."-01' AND '".$tanggal."-31' and pinjam_id='".$jt->pinjam_id."'")->row();
		$tagihan_bermasalah = $jt->jml_tagihan - $tm->tagihan_masuk;
		$tbp = $this->db->query("SELECT (sum((`tbl_pinjaman_h`.`jumlah` / `tbl_pinjaman_h`.`lama_angsuran`)) - sum(`tbl_pinjaman_d`.`jumlah_bayar`)) AS `tidak_bayar_pokok`, (sum(((`tbl_pinjaman_h`.`jumlah` * `tbl_pinjaman_h`.`bunga`) / 100)) - sum(`tbl_pinjaman_d`.`bunga`)) AS `tidak_bayar_bunga` FROM tbl_pinjaman_h JOIN tbl_pinjaman_d on tbl_pinjaman_h.id=tbl_pinjaman_d.pinjam_id where tbl_pinjaman_d.tgl_bayar BETWEEN '".$tanggal."-01' AND '".$tanggal."-31' and tbl_pinjaman_d.pinjam_id='".$jt->pinjam_id."'")->row();
	
				$html.= '<tr>
					<td style="text-align: center"> '.$no++.' </td>
					<td style="text-align: center"> '.$txt_tanggalD.' </td>
					<td style="text-align: center"> '.$jt->jml_tagihan.' </td>
					<td style="text-align: right"> '.number_format(nsi_round($tp->target_pokok)).' </td>
					<td style="text-align: right"> '.number_format(nsi_round($tp->target_bunga)).' </td>
					<td style="text-align: center"> '.$tm->tagihan_masuk.' </td>
					<td style="text-align: right"> '.number_format(nsi_round($tm->realisasi_pokok)).' </td>
					<td style="text-align: right"> '.number_format(nsi_round($tm->realisasi_bunga)).' </td>
					<td style="text-align: center"> '.$tagihan_bermasalah.' </td>
					<td style="text-align: right"> '.number_format(nsi_round(abs($tbp->tidak_bayar_pokok))).' </td>
					<td style="text-align: right"> '.number_format(nsi_round($tbp->tidak_bayar_bunga)).' </td>
				</tr>';
			}
			$html.= '</table>';

		//$nilai_d = $this->lap_rekap_m->get_jml_d();
		//$d = $nilai_d->jml_total;

		$html.= '<br /><br />';
		$pdf->nsi_html($html);
		$pdf->Output('lap_rekap'.date('Ymd_His') . '.pdf', 'I');
	}
}