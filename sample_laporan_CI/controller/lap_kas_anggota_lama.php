<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require('./application/third_party/phpoffice/vendor/autoload.php');

//use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class Lap_kas_anggota extends OPPController {
	public function __construct() {
			parent::__construct();	
			$this->load->helper('fungsi');
			$this->load->model('general_m');
			$this->load->model('lap_kas_anggota_m');
		}	

	public function index() {
		$this->load->library("pagination");
		$this->data['judul_browser'] = 'Laporan';
		$this->data['judul_utama'] = 'Laporan';
		$this->data['judul_sub'] = 'Data Kas Anggota';
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
 
		$config = array();
		$config["base_url"] = base_url() . "lap_kas_anggota/index/halaman";
		$jumlah_row = $this->lap_kas_anggota_m->get_jml_data_anggota();
		if(isset($_GET['anggota_id']) && $_GET['anggota_id'] > 0) {
			$jumlah_row = 1;
		}
		$config["total_rows"] = $jumlah_row;
		$config["per_page"] = 10;
		$config["uri_segment"] = 4;
		$config['use_page_numbers'] = TRUE;

		$config['full_tag_open'] = '<ul class="pagination">';
		$config['full_tag_close'] = '</ul>';

		$config['first_link'] = '&laquo; First';
		$config['first_tag_open'] = '<li class="prev page">';
		$config['first_tag_close'] = '</li>';

		$config['last_link'] = 'Last &raquo;';
		$config['last_tag_open'] = '<li class="next page">';
		$config['last_tag_close'] = '</li>';

		$config['next_link'] = 'Next &rarr;';
		$config['next_tag_open'] = '<li class="next page">';
		$config['next_tag_close'] = '</li>';

		$config['prev_link'] = '&larr; Previous';
		$config['prev_tag_open'] = '<li class="prev page">';
		$config['prev_tag_close'] = '</li>';

		$config['cur_tag_open'] = '<li class="active"><a href="">';
		$config['cur_tag_close'] = '</a></li>';

		$config['num_tag_open'] = '<li class="page">';
		$config['num_tag_close'] = '</li>';

		$this->pagination->initialize($config);
		$offset = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
		if($offset > 0) {
			$offset = ($offset * $config['per_page']) - $config['per_page'];
		}
		$this->data["data_anggota"] = $this->lap_kas_anggota_m->get_data_anggota($config["per_page"], $offset);
		$this->data["halaman"] = $this->pagination->create_links();
		$this->data["offset"] = $offset;
		$this->data["data_jns_simpanan"] = $this->lap_kas_anggota_m->get_jenis_simpan();
		$this->data['isi'] = $this->load->view('lap_kas_anggota_list_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}

	function cetak_simpanan() {
		$anggota = $this->lap_kas_anggota_m->lap_data_anggota();
		$data_jns_simpanan = $this->lap_kas_anggota_m->get_jenis_simpan();
		if($anggota == FALSE) {
			redirect('lap_kas_anggota');
			exit();
		}
		$txt_periode_arr = explode('-', $_REQUEST['periode']);
		if(is_array($txt_periode_arr)) {
			$periode = jin_nama_bulan($txt_periode_arr[1]) . ' ' . $txt_periode_arr[0];
		}
		$semua_pengguna = $this->lap_kas_anggota_m->lap_data_anggota();
		$styleJudul = [
			'font' => [
				'color' => [
					'rgb' => 'FFFFFF'
				],
				'bold'=>true,
				'size'=>11
			],
			'fill'=>[
				'fillType' =>  fill::FILL_SOLID,
				'startColor' => [
					'rgb' => 'e74c3c'
				]
			],
			'alignment'=>[
				'horizontal' => Alignment::HORIZONTAL_CENTER
			]
		 
		];
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()
			->setCellValue('A1', "LAPORAN KAS ANGGOTA PER JULI ".$periode);
		$spreadsheet->getActiveSheet()
			->mergeCells("A1:I1");
		$spreadsheet->getActiveSheet()
			->getStyle('A1')
			->getFont()
			->setSize(14);
		$spreadsheet->getActiveSheet()
			->getStyle('A1')
			->getAlignment()
			->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A2', 'No')
            ->setCellValue('B2', 'ID')
            ->setCellValue('C2', 'Nama')
            ->setCellValue('D2', 'Simpanan Wajib')
            ->setCellValue('E2', 'Simpanan Sukarela')
            ->setCellValue('F2', 'Simpanan Khusus 2')
            ->setCellValue('G2', 'Simpanan Pokok')
            ->setCellValue('H2', 'Simpanan Khusus 1')
            ->setCellValue('I2', 'Tabungan Perumahan');

        $kolom = 3;
        $nomor = 1;
          	foreach($semua_pengguna as $row) {
				$simpan = $this->db->query("SELECT 
		`c`.`nama` AS `nama`, 
		`c`.`id` AS `anggota_id`, 
		`c`.`no_ktp` AS `no_ktp`, 
		sum(if(`a`.`jenis_id` = 40,if(`a`.`dk` = `D`,`a`.`jumlah`,`-``a`.`jumlah`),0)) AS `simpanan_pokok`, 
		sum(if(`a`.`jenis_id` = 41,if(`a`.`dk` = `D`,`a`.`jumlah`,`-``a`.`jumlah`),0)) AS `simpanan_wajib`, 
		sum(if(`a`.`jenis_id` = 32,if(`a`.`dk` = `D`,`a`.`jumlah`,`-``a`.`jumlah`),0)) AS `simpanan_sukarela`, 
		sum(if(`a`.`jenis_id` = 52,if(`a`.`dk` = `D`,`a`.`jumlah`,`-``a`.`jumlah`),0)) AS `simpanan_khusus_2`,
		sum(if(`a`.`jenis_id` = 51,if(`a`.`dk` = `D`,`a`.`jumlah`,`-``a`.`jumlah`),0)) AS `simpanan_khusus_1`,
		sum(if(`a`.`jenis_id` = 31,if(`a`.`dk` = `D`,`a`.`jumlah`,`-``a`.`jumlah`),0)) AS `tabungan_perumahan`
	FROM ((`tbl_trans_sp` `a` join `jns_simpan` `b` on(`a`.`jenis_id` = `b`.`id`)) join `tbl_anggota` `c` on(`a`.`no_ktp` = `c`.`no_ktp`)) 
	where `c`.`no_ktp`='$row->no_ktp'
	GROUP BY `c`.`no_ktp` 
	order by c.nama asc")->result();

          		foreach ($simpan as $r) {
          			if(empty($r->simpanan_pokok)){
          				$sp = 0;
					} else {
						$sp = $r->simpanan_pokok;
					} 
					if(empty($r->simpanan_wajib)){
						$sw = 0;
					} else {
						$sw = $r->simpanan_wajib;
					}
					if(empty($r->simpanan_sukarela)){
						$ss = 0;
					} else {
						$ss = $r->simpanan_sukarela;
					}
					if(empty($r->simpanan_khusus_2)){
						$sk2 = 0;
					} else {
						$sk2 = $r->simpanan_khusus_2;
					}
					if(empty($r->simpanan_khusus_1)){
						$sk1 = 0;
					} else {
						$sk1 = $r->simpanan_khusus_1;
					}
					if(empty($r->tabungan_perumahan)){
						$tp = 0;
					} else {
						$tp = $r->tabungan_perumahan;
					}
					
              		$spreadsheet->setActiveSheetIndex(0)
                	->setCellValue('A' . $kolom, $nomor)
                	->setCellValue('B' . $kolom, $row->id_tagihan)
                	->setCellValue('C' . $kolom, $row->nama)
                	->setCellValue('D' . $kolom, nsi_round($sw))
                	->setCellValue('E' . $kolom, nsi_round($ss))
                	->setCellValue('F' . $kolom, nsi_round($sk2))
                	->setCellValue('G' . $kolom, nsi_round($sp))
                	->setCellValue('H' . $kolom, nsi_round($sk1))
                	->setCellValue('I' . $kolom, nsi_round($tp));
            	}
            
               	$kolom++;
               	$nomor++;
          	}

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
	  	header('Content-Disposition: attachment;filename="laporan_simpanan_'.date('Ymd_His') .'.xlsx"');
	  	header('Cache-Control: max-age=0');
	  	$writer->save('php://output');
	}

	function cetak_excel() {
		$anggota = $this->lap_kas_anggota_m->lap_data_anggota();
		$data_jns_simpanan = $this->lap_kas_anggota_m->get_jenis_simpan();
		if($anggota == FALSE) {
			redirect('lap_kas_anggota');
			exit();
		}
		$txt_periode_arr = explode('-', $_REQUEST['periode']);
		if(is_array($txt_periode_arr)) {
			$periode = jin_nama_bulan($txt_periode_arr[1]) . ' ' . $txt_periode_arr[0];
		}
		$semua_pengguna = $this->lap_kas_anggota_m->lap_data_anggota();
		$styleJudul = [
			'font' => [
				'color' => [
					'rgb' => 'FFFFFF'
				],
				'bold'=>true,
				'size'=>11
			],
			'fill'=>[
				'fillType' =>  fill::FILL_SOLID,
				'startColor' => [
					'rgb' => 'e74c3c'
				]
			],
			'alignment'=>[
				'horizontal' => Alignment::HORIZONTAL_CENTER
			]
		 
		];
        $spreadsheet = new Spreadsheet;

        $spreadsheet->getActiveSheet()
			->setCellValue('A1', "POTONGAN KOPERASI PT. KAO INDONESIA ".$periode);
		$spreadsheet->getActiveSheet()
			->mergeCells("A1:S1");
		$spreadsheet->getActiveSheet()
			->getStyle('A1')
			->getFont()
			->setSize(14);
		$spreadsheet->getActiveSheet()
			->getStyle('A1')
			->getAlignment()
			->setHorizontal(Alignment::HORIZONTAL_CENTER);

		$spreadsheet->getActiveSheet()->getStyle('H2:J2')->getFill()
    		->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    		->getStartColor()->setARGB('FFFF7F50');
    	$spreadsheet->getActiveSheet()->getStyle('K2:M2')->getFill()
    		->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    		->getStartColor()->setARGB('FF87CEFA');
    	$spreadsheet->getActiveSheet()->getStyle('N2:P2')->getFill()
    		->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    		->getStartColor()->setARGB('FF66CDAA');
    	$spreadsheet->getActiveSheet()->getStyle('Q2')->getFill()
    		->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    		->getStartColor()->setARGB('FF708090');

        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A2', 'No')
            ->setCellValue('B2', 'ID')
            ->setCellValue('C2', 'Nama')
            ->setCellValue('D2', 'Simpanan Wajib')
            ->setCellValue('E2', 'Simpanan Sukarela')
            ->setCellValue('F2', 'Simpanan Khusus 2')
            ->setCellValue('G2', 'Simpanan Pokok')
            ->setCellValue('H2', 'Ke/Dr')
            ->setCellValue('I2', 'Pinj. Biasa')
            ->setCellValue('J2', 'Jasa')
            ->setCellValue('K2', 'Ke/Dr')
            ->setCellValue('L2', 'Pinj. BANK')
            ->setCellValue('M2', 'Jasa')
            ->setCellValue('N2', 'Ke/Dr')
            ->setCellValue('O2', 'Pinj. Barang')
            ->setCellValue('P2', 'Jasa')
            ->setCellValue('Q2', 'TOSERDA')
            ->setCellValue('R2', 'Lain-Lain')
            ->setCellValue('S2', 'Tagihan Bulan Ini')
            ->setCellValue('T2', 'Potongan Gaji')
            ->setCellValue('U2', 'Total Pembayaran')
            ->setCellValue('V2', 'Tagihan Bulan Lalu')
            ->setCellValue('W2', 'Piutang Takterbayar');

        $kolom = 3;
        $nomor = 1;
          	foreach($semua_pengguna as $row) {
          		if(isset($_REQUEST['periode'])) {
					$tgl_arr = explode('-', $_REQUEST['periode']);
					$thn = $tgl_arr[0];
					$bln = $tgl_arr[1];
				} else {
					$thn = date('Y');			
					$bln = date('m');			
				}
				$simpan = $this->db->query("SELECT `a`.`tgl_transaksi` AS `tanggal`, `b`.`no_ktp` AS `no_ktp`, `b`.`nama` AS `nama`, sum(if(`a`.`jenis_id` = 40,`a`.`jumlah`,0)) AS `simpanan_pokok`, sum(if(`a`.`jenis_id` = 41,`a`.`jumlah`,0)) AS `simpanan_wajib`, sum(if(`a`.`jenis_id` = 32,`a`.`jumlah`,0)) AS `simpanan_sukarela`, sum(if(`a`.`jenis_id` = 52,`a`.`jumlah`,0)) AS `simpanan_khusus_2` 
				FROM (`tbl_anggota` `b` left join `v_tagihan` `a` on(`a`.`no_ktp` = `b`.`no_ktp`)) 
				where YEAR(`a`.`tgl_transaksi`) = '".$thn."' and MONTH(`a`.`tgl_transaksi`) = '$bln' and `b`.`no_ktp`='$row->no_ktp' 
				order by b.nama asc")->result();

				$bank = $this->db->query("SELECT 
				`a`.`tgl_pinjam` AS `tanggal`, 
				`a`.`no_ktp` AS `no_ktp`, 
				`a`.`nama` AS `nama`, 
				`a`.`lunas` AS `lunas`,
				SUM(if(`a`.`jenis` = 1,`a`.`angsuran`,0)) AS `ke_biasa`,
                SUM(IF(`a`.`jenis` = 1, `a`.`lama_angsuran`, 0)) 'dari_biasa',
				SUM(if(`a`.`jenis` = 1,`a`.`jumlah`,0)) AS `biasa`,
				SUM(if(`a`.`jenis` = 1,`a`.`jasa`,0)) AS `jasa_biasa`,

				SUM(if(`a`.`jenis` = 2,`a`.`angsuran`,0)) AS `ke_bank`,
                SUM(IF(`a`.`jenis` = 2, `a`.`lama_angsuran`, 0)) 'dari_bank',
				SUM(if(`a`.`jenis` = 2,`a`.`jumlah`,0)) AS `bank`,
				SUM(if(`a`.`jenis` = 2,`a`.`jasa`,0)) AS `jasa_bank`,

				SUM(if(`a`.`jenis` = 3,`a`.`angsuran`,0)) AS `ke_barang`,
                SUM(IF(`a`.`jenis` = 3, `a`.`lama_angsuran`, 0)) 'dari_barang',
				SUM(if(`a`.`jenis` = 3,`a`.`jumlah`,0)) AS `barang`,
				SUM(if(`a`.`jenis` = 3,`a`.`jasa`,0)) AS `jasa_barang`

				FROM `v_lap_bank` `a` 
				where a.lunas='Belum' and `a`.`no_ktp`='$row->no_ktp'")->result();

				$bank_bayar = $this->db->query("SELECT 
				`a`.`tgl_bayar` AS `tanggal`, 
				`b`.`no_ktp` AS `no_ktp`, 
				`b`.`lunas` AS `lunas`,
				COUNT(if(`b`.`jenis_pinjaman` = 1,`b`.`jumlah_angsuran`,0)) AS `ke_biasa`,
                SUM(IF(`b`.`jenis_pinjaman` = 1, `b`.`lama_angsuran`, 0)) 'dari_biasa',
				SUM(if(`b`.`jenis_pinjaman` = 1,`b`.`jumlah_angsuran`,0)) AS `biasa`,
				SUM(if(`b`.`jenis_pinjaman` = 1,`b`.`bunga_rp`,0)) AS `jasa_biasa`,

				SUM(if(`b`.`jenis_pinjaman` = 2,`b`.`jumlah_angsuran`,0)) AS `ke_bank`,
                SUM(IF(`b`.`jenis_pinjaman` = 2, `b`.`lama_angsuran`, 0)) 'dari_bank',
				SUM(if(`b`.`jenis_pinjaman` = 2,`b`.`jumlah_angsuran`,0)) AS `bank`,
				SUM(if(`b`.`jenis_pinjaman` = 2,`b`.`bunga_rp`,0)) AS `jasa_bank`,

				SUM(if(`b`.`jenis_pinjaman` = 3,`b`.`jumlah_angsuran`,0)) AS `ke_barang`,
                SUM(IF(`b`.`jenis_pinjaman` = 3, `b`.`lama_angsuran`, 0)) 'dari_barang',
				SUM(if(`b`.`jenis_pinjaman` = 3,`b`.`jumlah_angsuran`,0)) AS `barang`,
				SUM(if(`b`.`jenis_pinjaman` = 3,`b`.`bunga_rp`,0)) AS `jasa_barang`

				FROM `tbl_pinjaman_d` `a` JOIN tbl_pinjaman_h b on a.pinjam_id=b.id
				where b.lunas='Belum' and `b`.`no_ktp`='$row->no_ktp'")->result();

          		foreach ($simpan as $r) {
          			if(empty($r->simpanan_wajib)){
						$sw = 0;
					} else {
						$sw = $r->simpanan_wajib;
					} 
					if(empty($r->simpanan_sukarela)){
						$ss = 0;
					} else {
						$ss = $r->simpanan_sukarela;
					}
					if(empty($r->simpanan_khusus_2)){
						$sk = 0;
					} else {
						$sk = $r->simpanan_khusus_2;
					}
					if(empty($r->simpanan_pokok)){
						$sp = 0;
					} else {
						$sp = $r->simpanan_pokok;
					}

              		$spreadsheet->setActiveSheetIndex(0)
                	->setCellValue('A' . $kolom, $nomor)
                	->setCellValue('B' . $kolom, $row->id_tagihan)
                	->setCellValue('C' . $kolom, $row->nama)
                	->setCellValue('D' . $kolom, nsi_round($sw))
                	->setCellValue('E' . $kolom, nsi_round($ss))
                	->setCellValue('F' . $kolom, nsi_round($sk))
                	->setCellValue('G' . $kolom, nsi_round($sp));
                
                foreach ($bank as $b) {
                	foreach ($bank_bayar as $bb) {

                	$bulan_tak = $this->db->query("SELECT SUM(jumlah) AS jml_total FROM tbl_trans_tagihan where `no_ktp`='$row->no_ktp' and jenis_id=8")->row();

                	$bank_lunas = $bb->biasa + $bb->jasa_biasa + $bb->bank + $bb->jasa_bank + $bb->barang + $bb->jasa_barang;

                	$toserda 	= $this->lap_kas_anggota_m->get_jml_toserda($row->no_ktp);
                	$lain_lain 	= $this->lap_kas_anggota_m->get_jml_lain_lain($row->no_ktp);
                	$jml_simpan = $this->lap_kas_anggota_m->get_jml_simpans($row->no_ktp);

                	$sim = $this->lap_kas_anggota_m->get_bayar_simpanan($row->no_ktp);
					$pin = $this->lap_kas_anggota_m->get_bayar_pinjaman($row->no_ktp);
					$tos = $this->lap_kas_anggota_m->get_tak_bayar_toserda($row->no_ktp);
                	
                	$bayar_simpanan = $this->lap_kas_anggota_m->get_bayar_simpanan($row->no_ktp);
                	$total_tak_bayar = $this->lap_kas_anggota_m->get_tak_bayar_bayar($row->no_ktp);
                	if (!empty($lain_lain)) {
						$b_lain_lain = $lain_lain->jumlah_bayar;
					} else {
						$b_lain_lain = 0;
					}
                	if (!empty($toserda)) {
						$b_toserda = $toserda->jumlah_bayar;
					} else {
						$b_toserda = 0;
					}
					if (!empty($bulan_tak)) {
						$bulan_tak = $bulan_tak->jml_total;
					} else {
						$bulan_tak = 0;
					}

					if (empty($total_tak_bayar)) {
						$total_tak_bayar = 0;
					} else {
						$total_tak_bayar = $total_tak_bayar->tagihan_tak_terbayar;
					}

					$jumlah = $jml_simpan->jml_total + $b->biasa + $b->jasa_biasa + $b->bank + $b->jasa_bank + $b->barang + $b->jasa_barang + $b_toserda + $b_lain_lain;

					if(empty($sim)){
						$sim = 0;
					} else {
						$sim = $sim->jumlah_bayar;
					}
					if(empty($pin)){
						$pin = 0;
					} else {
						$pin = $pin->jumlah_bayar;
					}
					if(empty($tos)){
						$tos = 0;
					} else {
						$tos = $tos->jumlah;
					}

					if(empty($row->id_tagihan)){
                		$potongan = 0;
                	} else if ($row->id_tagihan == '24100162'){
                		$potongan = 0;
                	} else {
                		$potongan = ($sim + $pin + $tos);
                	}

                	if(empty($bayar_simpanan)){
                		$simpanan = 0;
                	} else {
                		$simpanan = $bayar_simpanan->jumlah_bayar;
                	}
					
					$tot_bayar = ($sim + $pin + $tos);
					$piutang_tak_terbayar = ($jumlah - $potongan);
                	$spreadsheet->setActiveSheetIndex(0)
                	->setCellValue('H' . $kolom, $b->ke_biasa.'/'.$b->dari_biasa)
                	->setCellValue('I' . $kolom, nsi_round($b->biasa))
                	->setCellValue('J' . $kolom, nsi_round($b->jasa_biasa))
                	->setCellValue('K' . $kolom, $b->ke_bank.'/'.$b->dari_bank)
                	->setCellValue('L' . $kolom, nsi_round($b->bank))
                	->setCellValue('M' . $kolom, nsi_round($b->jasa_bank))
                	->setCellValue('N' . $kolom, $b->ke_barang.'/'.$b->dari_barang)
                	->setCellValue('O' . $kolom, nsi_round($b->barang))
                	->setCellValue('P' . $kolom, nsi_round($b->jasa_barang))
                	->setCellValue('Q' . $kolom, nsi_round($b_toserda))
                	->setCellValue('R' . $kolom, nsi_round($b_lain_lain))
                	->setCellValue('S' . $kolom, nsi_round($jumlah))
                	->setCellValue('T' . $kolom, nsi_round($potongan))
                	->setCellValue('U' . $kolom, nsi_round($tot_bayar))
                	->setCellValue('V' . $kolom, nsi_round($bulan_tak))
                	->setCellValue('W' . $kolom, nsi_round($piutang_tak_terbayar));
                
            	}
            	}
            	}
            
               	$kolom++;
               	$nomor++;
          	}

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
	  	header('Content-Disposition: attachment;filename="laporan_detail_potongan'.date('Ymd_His') .'.xlsx"');
	  	header('Cache-Control: max-age=0');
	  	$writer->save('php://output');
	}
	
	function cetak_tagihan() {
		$anggota = $this->lap_kas_anggota_m->lap_data_anggota();
		$data_jns_simpanan = $this->lap_kas_anggota_m->get_jenis_simpan();
		if($anggota == FALSE) {
			redirect('lap_kas_anggota');
			exit();
		}
		$txt_periode_arr = explode('-', $_REQUEST['periode']);
		if(is_array($txt_periode_arr)) {
			$periode = jin_nama_bulan($txt_periode_arr[1]) . ' ' . $txt_periode_arr[0];
		}
		$semua_pengguna = $this->lap_kas_anggota_m->lap_data_anggota();
		$styleJudul = [
			'font' => [
				'color' => [
					'rgb' => 'FFFFFF'
				],
				'bold'=>true,
				'size'=>11
			],
			'fill'=>[
				'fillType' =>  fill::FILL_SOLID,
				'startColor' => [
					'rgb' => 'e74c3c'
				]
			],
			'alignment'=>[
				'horizontal' => Alignment::HORIZONTAL_CENTER
			]
		 
		];
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()
			->setCellValue('A1', "POTONGAN KOPERASI PT. KAO INDONESIA ".$periode);
		$spreadsheet->getActiveSheet()
			->mergeCells("A1:I1");
		$spreadsheet->getActiveSheet()
			->getStyle('A1')
			->getFont()
			->setSize(14);
		$spreadsheet->getActiveSheet()
			->getStyle('A1')
			->getAlignment()
			->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $spreadsheet->setActiveSheetIndex(0)
            ->setCellValue('A3', 'No')
            ->setCellValue('B3', 'ID Karyawan')
            ->setCellValue('C3', 'Nama')
            ->setCellValue('D3', 'Jumlah')
            ->setCellValue('F3', 'TOTAL');

        $kolom = 4;
        $nomor = 1;
        	//$where = "YEAR(tempo) = '".$thn."' AND  MONTH(tempo) = '".$bln."' ";
          	foreach($semua_pengguna as $row) {
          		if(isset($_REQUEST['periode'])) {
					$tgl_arr = explode('-', $_REQUEST['periode']);
					$thn = $tgl_arr[0];
					$bln = $tgl_arr[1];
				} else {
					$thn = date('Y');			
					$bln = date('m');			
				}
				
				$simpan = $this->db->query("SELECT `a`.`tgl_transaksi` AS `tanggal`, `b`.`no_ktp` AS `no_ktp`, `b`.`nama` AS `nama`, sum(if(`a`.`jenis_id` = 40,`a`.`jumlah`,0)) AS `simpanan_pokok`, sum(if(`a`.`jenis_id` = 41,`a`.`jumlah`,0)) AS `simpanan_wajib`, sum(if(`a`.`jenis_id` = 32,`a`.`jumlah`,0)) AS `simpanan_sukarela`, sum(if(`a`.`jenis_id` = 52,`a`.`jumlah`,0)) AS `simpanan_khusus_2` 
				FROM (`tbl_anggota` `b` left join `v_tagihan` `a` on(`a`.`no_ktp` = `b`.`no_ktp`)) 
				where YEAR(`a`.`tgl_transaksi`) = '".$thn."' and MONTH(`a`.`tgl_transaksi`) = '$bln' and `b`.`no_ktp`='$row->no_ktp' 
				order by b.nama asc")->result();

				$bank = $this->db->query("SELECT 
				`a`.`tgl_pinjam` AS `tanggal`, 
				`a`.`no_ktp` AS `no_ktp`, 
				`a`.`nama` AS `nama`, 
				`a`.`lunas` AS `lunas`,
				SUM(if(`a`.`jenis` = 1,`a`.`angsuran`,0)) AS `ke_biasa`,
                SUM(IF(`a`.`jenis` = 1, `a`.`lama_angsuran`, 0)) 'dari_biasa',
				SUM(if(`a`.`jenis` = 1,`a`.`jumlah`,0)) AS `biasa`,
				SUM(if(`a`.`jenis` = 1,`a`.`jasa`,0)) AS `jasa_biasa`,

				SUM(if(`a`.`jenis` = 2,`a`.`angsuran`,0)) AS `ke_bank`,
                SUM(IF(`a`.`jenis` = 2, `a`.`lama_angsuran`, 0)) 'dari_bank',
				SUM(if(`a`.`jenis` = 2,`a`.`jumlah`,0)) AS `bank`,
				SUM(if(`a`.`jenis` = 2,`a`.`jasa`,0)) AS `jasa_bank`,

				SUM(if(`a`.`jenis` = 3,`a`.`angsuran`,0)) AS `ke_barang`,
                SUM(IF(`a`.`jenis` = 3, `a`.`lama_angsuran`, 0)) 'dari_barang',
				SUM(if(`a`.`jenis` = 3,`a`.`jumlah`,0)) AS `barang`,
				SUM(if(`a`.`jenis` = 3,`a`.`jasa`,0)) AS `jasa_barang`

				FROM `v_lap_bank` `a` 
				where a.lunas='Belum' and `a`.`no_ktp`='$row->no_ktp'")->result();

					$spreadsheet->getActiveSheet()->getStyle('D3:D465')->getNumberFormat()
    				->setFormatCode('#,##0.00');
    				$spreadsheet->getActiveSheet()->getStyle('F4')->getNumberFormat()
    				->setFormatCode('#,##0.00');

					$spreadsheet->getActiveSheet()
					->getStyle('D3:D466')
					->getAlignment()
					->setHorizontal(Alignment::HORIZONTAL_RIGHT);

              		$spreadsheet->setActiveSheetIndex(0)
                	->setCellValue('A' . $kolom, $nomor)
                	->setCellValue('B' . $kolom, $row->id_tagihan)
                	->setCellValue('C' . $kolom, $row->nama);

                	foreach ($bank as $b) {
                	
                	$toserda 	= $this->lap_kas_anggota_m->get_jml_toserda($row->no_ktp);
                	$lain_lain 	= $this->lap_kas_anggota_m->get_jml_lain_lain($row->no_ktp);
                	$jml_simpan = $this->lap_kas_anggota_m->get_jml_simpans($row->no_ktp);

                	if (!empty($lain_lain)) {
						$b_lain_lain = $lain_lain->jumlah_bayar;
						} else {
						$b_lain_lain = 0;
					}
                	if (!empty($toserda)) {
						$b_toserda = $toserda->jumlah_bayar;
						} else {
						$b_toserda = 0;
					}
					
					$jumlah = $jml_simpan->jml_total + $b->biasa + $b->jasa_biasa + $b->bank + $b->jasa_bank + $b->barang + $b->jasa_barang + $b_toserda + $b_lain_lain;

					if(empty($row->id_tagihan)){
                		$potongan = 0;
                	} else if ($row->id_tagihan == '24100162'){
                		$potongan = 0;
                	} else {
                		$potongan = $jumlah;
                	}
	                
	                $spreadsheet->setActiveSheetIndex(0)
	                	->setCellValue('D' . $kolom, nsi_round($jumlah));
	                }
                
               	$kolom++;
               	$nomor++;
          	}
          	$spreadsheet->setActiveSheetIndex(0)->setCellValue('F4', '=SUM(D4:D466)');

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.ms-excel');
	  	header('Content-Disposition: attachment;filename="laporan_potongan'.date('Ymd_His') .'.xlsx"');
	  	header('Cache-Control: max-age=0');
	  	$writer->save('php://output');
	}
	
}