<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lap_pinjaman extends OperatorController {
	public function __construct() {
		parent::__construct();
		$this->load->helper('fungsi');
		$this->load->model('lap_pinjaman_m');
		$this->load->model('general_m');
		$this->load->model('pinjaman_m');
	}	

	public function index() {
		$this->load->library("pagination");
		$this->data['judul_browser'] = 'Laporan';
		$this->data['judul_utama'] = 'Laporan';
		$this->data['judul_sub'] = 'Pinjaman';

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
		$config["base_url"] 		= base_url() . "lap_pinjaman/index/halaman";
		if (count($_GET) > 0) $config['suffix'] = '?' . http_build_query($_GET, '', "&");
		$config['first_url'] 		= $config['base_url'].'?'.http_build_query($_GET);
		$config["total_rows"] 		= $this->lap_pinjaman_m->get_jml_data_kas();
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

		$this->data["data_kas"] = $this->lap_pinjaman_m->get_transaksi_pinjaman($config["per_page"], $offset);
		$this->data["saldo_awal"] = $this->lap_pinjaman_m->get_saldo_awal($config["per_page"], $offset);
		$this->data["saldo_sblm"] = $this->lap_pinjaman_m->get_saldo_sblm();
		$this->data["halaman"] = $this->pagination->create_links();
		$this->data["offset"] = $offset;

		//$this->data["pinjaman"] = $this->lap_pinjaman_m->get_transaksi_pinjaman(); 
		
		$this->data['isi'] = $this->load->view('lap_pinjaman_list_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}

	function cetak_laporan() {
		$data_pinjam = $this->pinjaman_m->lap_data_pinjaman();
		if($data_pinjam == FALSE) {
			echo 'DATA KOSONG<br>Pastikan Filter Tanggal dengan benar.';
			exit();
		}

		$tgl_dari = $_REQUEST['tgl_dari']; 
		$tgl_sampai = $_REQUEST['tgl_sampai']; 
		$cari_status = $_REQUEST['cari_status']; 

		if ($cari_status == "") {
			$status = "Status Pelunasan : Semua";
		} else {
			$status = "Status Pelunasan :". $cari_status ;
		}

		$this->load->library('Pdf');
		$pdf = new Pdf('L', 'mm', 'A4', true, 'UTF-8', false);
		$pdf->set_nsi_header(TRUE);
		$pdf->AddPage('L');
		$html = '';
		$html .= '
		<style>
			.h_tengah {text-align: center;}
			.h_kiri {text-align: left;}
			.h_kanan {text-align: right;}
			.txt_judul {font-size: 15pt; font-weight: bold; padding-bottom: 12px;}
			.header_kolom {background-color: #cccccc; text-align: center; font-weight: bold;}
		</style>
		'.$pdf->nsi_box($text = '<span class="txt_judul">Laporan Data Pinjaman <br></span> <span> Periode '.jin_date_ina($tgl_dari).' - '.jin_date_ina($tgl_sampai).' | '.$status.'</span> ', $width = '100%', $spacing = '0', $padding = '1', $border = '0', $align = 'center').'
		<table width="100%" cellspacing="0" cellpadding="3" border="1" nobr="true">
			<tr class="header_kolom">
				<th style="width:3%;" > No </th>
				<th style="width:28%;"> Identitas Anggota</th>
				<th style="width:25%;"> Pinjaman  </th>
				<th style="width:22%;"> Hitungan </th>
				<th style="width:22%;"> Tagihan  </th>
			</tr>';
		$no =1;
		$batas = 1;
		$total_pinjaman = 0;
		$total_denda = 0;
		$total_tagihan = 0;
		$tot_sdh_dibayar = 0;
		$tot_sisa_tagihan = 0;
		foreach ($data_pinjam as $r) {
			if($batas == 0) {
				$html .= '
				<tr class="header_kolom" pagebreak="false">
					<th style="width:3%;" > No </th>
					<th style="width:27%;"> Identitas Anggota</th>
					<th style="width:26%;"> Pinjaman  </th>
					<th style="width:22%;"> Simulasi </th>
					<th style="width:22%;"> Tagihan  </th>
				</tr>';
				$batas = 1;
			}
			$batas++;

			$barang = $this->pinjaman_m->get_data_barang($r->barang_id);   
			$anggota = $this->general_m->get_data_anggota($r->anggota_id);   
			$jml_bayar = $this->general_m->get_jml_bayar($r->id); 
			$jml_denda = $this->general_m->get_jml_denda($r->id); 
			$jml_adm = $this->general_m->get_jml_adm($r->id);
			$jml_bunga = $this->general_m->get_jml_bunga($r->id); 
			$jml_tagihan = $r->tagihan;
			$sisa_tagihan = $jml_tagihan - $jml_bayar->total;


			//total pinjaman
			$total_pinjaman += @$r->jumlah;
			//total tagihan
			$total_tagihan += $jml_tagihan;
			//total dibayar
			$tot_sdh_dibayar += $jml_bayar->total;
			//sisa tagihan
			$tot_sisa_tagihan += $sisa_tagihan;

			//jabatan
			if ($anggota->jabatan_id == "1"){
				$jabatan = "Pengurus";
			} else {
				$jabatan = "Anggota";
			}

			//jk
			if ($anggota->jk == "L"){
				$jk = "Laki-laki";
			} else {
				$jk = "Perempuan";
			}

			$tgl_pinjam = explode(' ', $r->tgl_pinjam);
			$txt_tanggal = jin_date_ina($tgl_pinjam[0],'full');

			$tgl_tempo = explode(' ', $r->tempo);
			$txt_tempo = jin_date_ina($tgl_tempo[0],'full');

			// AG'.sprintf('%04d',$anggota->id).'
			$html .= '
			<tr nobr="true">
				<td class="h_tengah">'.$no++.' </td>
				<td>
					<table width="100%"> 
						<tr>
							<td width="20%">ID </td><td width="5%">:</td><td class="h_kiri" width="75%">AG'.sprintf('%04d',$anggota->id).'</td>
						</tr>
						<tr>
							<td>Nama </td>
							<td>:</td>
							<td class="h_kiri"><strong>'.strtoupper($anggota->nama).'</strong></td>
						</tr>
						<tr>
							<td>Dept </td>
							<td>:</td>
							<td class="h_kiri">'.$anggota->departement.'</td>
						</tr>
						<tr>
							<td>L/P </td>
							<td>:</td>
							<td class="h_kiri">'.$jk.' </td>
						</tr>
						<tr>
							<td>Jabatan </td>
							<td>:</td>
							<td class="h_kiri">'.$jabatan.' </td>
						</tr>
						<tr>
							<td>Alamat </td>
							<td>:</td>
							<td class="h_kiri">'.$anggota->alamat.'<br>Telp. '. $anggota->notelp.'</td>
						</tr>
					</table>
				</td>
				<td>
					<table width="100%">
						<tr>
							<td width="44%"> Nomor Kontrak</td>
							<td width="6%">:</td>
							<td width="50%" class="h_kiri">'.'PJ'.sprintf('%05d',$r->id).'</td>
						</tr>
						<tr>
							<td> Tanggal Pinjam</td>
							<td>:</td>
							<td class="h_kiri">'.$txt_tanggal.'</td>
						</tr>
						<tr>
							<td> Tanggal Tempo</td>
							<td>:</td>
							<td class="h_kiri">'.$txt_tempo.'</td>
						</tr>
						<tr>
							<td> Pokok Pinjaman</td>
							<td>:</td>
							<td class="h_kiri">'.number_format(@$r->jumlah).'</td>
						</tr>
						<tr>
							<td> Lama Pinjaman</td>
							<td>:</td>
							<td class="h_kiri">'.number_format(@$r->lama_angsuran).' Bulan</td>
						</tr>
						<tr>
							<td> Status Lunas</td>
							<td>:</td>
							<td class="h_kiri">'.@$r->lunas.'</td>
						</tr>
					</table>
				</td>
				<td>
					<table> 
						<tr>
							<td>Pokok Angsuran </td> 
							<td class="h_kanan"> '.number_format(@$r->pokok_angsuran).' </td>
						</tr>
						<tr>
							<td>Bunga Pinjaman </td>
							<td class="h_kanan"> '.number_format(@$r->pokok_bunga).'</td>
						</tr>
						<tr>
							<td>Jumlah Angsuran </td>
							<td class="h_kanan"> '.number_format(nsi_round(@$r->ags_per_bulan)).'</td>
						</tr>
					</table>
				</td>
				<td>
					<table> 
						<tr>
							<td>Jumlah Tagihan </td> 
							<td class="h_kanan"> '.number_format(nsi_round($r->tagihan)).' </td>
						</tr>
						<tr>
							<td>Total Bunga </td> 
							<td class="h_kanan"> '.number_format(nsi_round($jml_bunga->total_bunga)).' </td>
						</tr>
						<tr>
							<td>Total Denda </td> 
							<td class="h_kanan"> '.number_format(nsi_round($jml_denda->total_denda)).' </td>
						</tr>
						<tr>
							<td>Total Biaya Adm </td> 
							<td class="h_kanan"> '.number_format(nsi_round($jml_adm->total_adm)).' </td>
						</tr>
						<tr>
							<td>Dibayar </td>
							<td class="h_kanan"> '.number_format(nsi_round($jml_bayar->total)).'</td>
						</tr>
						<tr>
							<td>Sisa Tagihan </td>
							<td class="h_kanan"><strong>'.number_format(nsi_round($sisa_tagihan)).'</strong></td>
						</tr>
					</table>
				</td>
			</tr>';
			}

		$html .= '
				<tr>
					<td colspan="4" class="h_kanan"> <strong> Total Pokok Pinjaman </strong> </td>
					<td class="h_kanan"><strong> '.number_format(nsi_round($total_pinjaman)).' </strong></td>
					<td></td>
				</tr>
				<tr>
					<td colspan="4" class="h_kanan"> <strong> Total Tagihan </strong> </td>
					<td class="h_kanan"><strong>'.number_format(nsi_round($total_tagihan)).'</strong></td>
					<td></td>
				</tr>
				<tr>
					<td colspan="4" class="h_kanan"> <strong> Total Dibayar </strong> </td>
					<td class="h_kanan"><strong>'.number_format(nsi_round($tot_sdh_dibayar)).'</strong></td>
					<td></td>
				</tr>
				<tr>
					<td colspan="4" class="h_kanan"> <strong> Sisa Tagihan </strong> </td>
					<td class="h_kanan"><strong>'.number_format(nsi_round($tot_sisa_tagihan)).'</strong></td>
					<td></td>
				</tr>
			</table>';
		$pdf->nsi_html($html);
		ob_end_clean();
		$pdf->Output('pinjam'.date('Ymd_His') . '.pdf', 'I');
	} 

	function cetak() {
		$saldo_sblm = $this->lap_pinjaman_m->lap_transaksi_pinjaman();
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
				'.$pdf->nsi_box($text = '<span class="txt_judul">Laporan Pinjaman Periode '.$txt_periode.'</span>', $width = '100%', $spacing = '1', $padding = '1', $border = '0', $align = 'center').'';
		$no = 1;
		$total_saldo = 0;
		$saldo = 0;
		
			$html.= '<h3>Pinjaman</h3>';
			$html.= '<table  width="90%" cellspacing="0" cellpadding="3" border="1" nobr="true">
			<tr class="header_kolom">
				<th class="h_tengah" style="width:3%; vertical-align: middle "> No</th>
				<th class="h_tengah" style="width:10%; vertical-align: middle "> Tanggal Pinjam </th>
				<th class="h_tengah" style="width:10%; vertical-align: middle "> Nama </th>
				<th class="h_tengah" style="width:10%; vertical-align: middle "> Pokok Pinjaman </th>
				<th class="h_tengah" style="width:5%; vertical-align: middle "> Lama Angsuran (Bulan)</th>
				<th class="h_tengah" style="width:5%; vertical-align: middle "> Bunga (%) </th>
				<th class="h_tengah" style="width:8%; vertical-align: middle "> Biaya Adm </th>
				<th class="h_tengah" style="width:5%; vertical-align: middle "> Denda (Rp) </th>
				<th class="h_tengah" style="width:10%; vertical-align: middle "> Pokok Angsuran </th>
				<th class="h_tengah" style="width:5%; vertical-align: middle "> Bunga Pinjaman </th>
				<th class="h_tengah" style="width:10%; vertical-align: middle "> Angsuran / Bulan </th>
				<th class="h_tengah" style="width:10%; vertical-align: middle "> Jumlah Bayar </th>
				<th class="h_tengah" style="width:5%; vertical-align: middle "> Sisa Angsuran (Bulan)</th>
				<th class="h_tengah" style="width:10%; vertical-align: middle "> Sisa Tagihan</th>
				<th class="h_tengah" style="width:5%; vertical-align: middle "> Status </th>
			</tr>';
			$no = 1;
			$d = 0;
			$k = 0;
			foreach ($saldo_sblm as $rows) {
				$tglD = explode(' ', $rows->tgl_pinjam);
				$txt_tanggalD = jin_date_ina($tglD[0],'p');
				$jml_bayar = $this->general_m->get_jml_bayar($rows->id);
				$sisa_angsuran = $rows->lama_angsuran - $rows->bln_sudah_angsur;
				$sisa_tagihan = $rows->jumlah - $jml_bayar->total;
				
				$html.= '<tr>
					<td class="h_tengah"> '.$no++.' </td>
					<td class="h_tengah"> '.$txt_tanggalD.' </td>
					<td> AG'.sprintf('%04d', $rows->id).'/'.$rows->nama.'</td>
					<td class="h_kanan"> '.number_format(nsi_round($rows->jumlah)).' </td>
					<td class="h_tengah"> '.$rows->lama_angsuran.' </td>
					<td class="h_tengah"> '.$rows->bunga.' </td>
					<td class="h_kanan"> '.number_format(nsi_round($rows->biaya_adm)).' </td>
					<td class="h_kanan"> '.number_format(nsi_round($rows->denda_rp)).' </td>
					<td class="h_kanan"> '.number_format(nsi_round($rows->pokok_angsuran)).' </td>
					<td class="h_kanan"> '.number_format(nsi_round($rows->pokok_bunga)).' </td>
					<td class="h_kanan"> '.number_format(nsi_round($rows->ags_per_bulan)).' </td>
					<td class="h_kanan"> '.number_format(nsi_round($jml_bayar->total)).' </td>
					<td class="h_tengah"> '.$sisa_angsuran.' </td>
					<td class="h_kanan"> '.number_format(nsi_round($sisa_tagihan)).' </td>
					<td class="h_tengah"> '.$rows->lunas.' </td>
				</tr>';
			}
			$html.= '</table>';

		$nilai_d = $this->lap_pinjaman_m->get_jml_d();
		$d = $nilai_d->jml_total;

		$html.= '<br /><br /><table class="table table-bordered">
				<tr class="header_kolom">
					<th class="h_kanan" style="width:100%; vertical-align: middle">TOTAL : '.number_format($d).'</th>
				</tr>
			</table>';
		$pdf->nsi_html($html);
		ob_end_clean();
		$pdf->Output('lap_pinjaman'.date('Ymd_His') . '.pdf', 'I');
	}
}