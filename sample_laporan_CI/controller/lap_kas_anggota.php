<?php if (! defined('BASEPATH')) exit('No direct script access allowed');
require('./application/third_party/phpoffice/vendor/autoload.php');

//use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class Lap_kas_anggota extends OPPController
{
	public function __construct()
	{
		parent::__construct();
		$this->load->helper('fungsi');
		$this->load->model('general_m');
		$this->load->model('lap_kas_anggota_m');
	}
	
	public function test1(){
		echo phpversion();
	}
	
	public function test(){
		
		
		$arr_anggota = $this->db->from('tbl_anggota')
			->where('aktif', 'Y')
			->order_by('nama', 'asc')
			->limit(10, 120)
			->get();
			
		//print_r($arr_anggota->num_rows());
		
		echo '<table border="1">';
		
		foreach($arr_anggota->result() as $row){
			echo '<tr>';
			echo '<td>';
			echo $row->nama;
			
			$arr_jenis = $this->db->from('jns_simpan')
				->where('tampil', 'Y')
				->where_in('id', [41, 32, 52, 40, 51, 31])
				->order_by('urut', 'asc')
				->get();
				
			
			
			$simpanan_arr = array();
			$simpanan_row_total = 0;
			$simpanan_total = 0;
			foreach ($arr_jenis->result() as $jenis) {
				$simpanan_arr[$jenis->id] = $jenis->jns_simpan;
				$nilai_s = $this->lap_kas_anggota_m->get_jml_simpanans($jenis->id, $row->no_ktp);
				$nilai_p = $this->lap_kas_anggota_m->get_jml_penarikans($jenis->id, $row->no_ktp);

				$s_saldo = $this->lap_kas_anggota_m->get_jml_saldo($row->no_ktp);

				//$bulan_lalu_tak = $this->lap_kas_anggota_m->get_bulan_tak($row->no_ktp);

				$bayar = $this->lap_kas_anggota_m->get_tak_bayar($row->no_ktp);
				$tagih = $this->lap_kas_anggota_m->get_tagih($row->no_ktp);
				$bayar_toserda = $this->lap_kas_anggota_m->get_tak_bayar_toserda($row->no_ktp);
				$tagih_toserda = $this->lap_kas_anggota_m->get_tagih_toserda($row->no_ktp);
				$bayar_lain_lain = $this->lap_kas_anggota_m->get_tak_bayar_lain_lain($row->no_ktp);
				$tagih_lain_lain = $this->lap_kas_anggota_m->get_tagih_lain_lain($row->no_ktp);

				$simpan = $this->lap_kas_anggota_m->get_jml_simpan();
				$jml_tagihan_simpanan = $this->lap_kas_anggota_m->get_jml_tagihan_simpanan($row->no_ktp);

				$sim = $this->lap_kas_anggota_m->get_bayar_simpanan($row->no_ktp);
				$sim_pot = $this->lap_kas_anggota_m->get_bayar_simpanan_pot($row->no_ktp);
				$pin = $this->lap_kas_anggota_m->get_bayar_pinjaman($row->no_ktp);
				$tos = $this->lap_kas_anggota_m->get_tak_bayar_toserda($row->no_ktp);

				$jml_simpan = $this->lap_kas_anggota_m->get_jml_simpans($row->no_ktp);
				$jml_bayar = $this->lap_kas_anggota_m->get_jml_bayars($row->no_ktp);
				$simpanan_row = ($nilai_s->jml_total - $nilai_p->jml_total);
				$simpanan_row_total += $simpanan_row;
				$simpanan_total += $simpanan_row_total;
				//$jml_bulan_lalu = $this->lap_kas_anggota_m->get_bulan($row->no_ktp);
				if (empty($s_saldo)) {
					$s_saldo = 0;
				} else {
					$s_saldo = $s_saldo->tagihan_tak_terbayar;
				}

				if (isset($_REQUEST['periode'])) {
					$tgl_arr = explode('-', $_REQUEST['periode']);
					$thn = $tgl_arr[0];
					$bln = $tgl_arr[1];
				} else {
					$thn = date('Y');
					$bln = date('m');
				}
				//$where = "YEAR(tempo) = '".$thn."' AND  MONTH(tempo) = '".$bln."' ";
				//$potongan_gaji = $this->db->query("SELECT SUM(if(jenis_id = '125', jumlah, 0)) as potongan_gaji
			//FROM tbl_trans_sp
			//where YEAR(`tgl_transaksi`) = '" . $thn . "' and MONTH(`tgl_transaksi`) = '" . $bln . "' and //`no_ktp`='$row->no_ktp'")->row();
			
				$potongan_gaji = $this->db->query("SELECT SUM(jumlah) as potongan_gaji
			FROM tbl_trans_sp
			where YEAR(`tgl_transaksi`) = '" . $thn . "' and jenis_id='125' and MONTH(`tgl_transaksi`) = '" . $bln . "' and `no_ktp`='$row->no_ktp'")->row();
			
				

				$bulan_lalu = $this->db->query("SELECT `tagihan_tak_terbayar` FROM (`v_simpanan_gabung`) where `no_ktp`='$row->no_ktp'")->row(); 						
				$total_tak_bayar = $this->lap_kas_anggota_m->get_tak_bayar_bayar($row->no_ktp); 
				
				
				$bulan_tak = $this->db->query("SELECT SUM(jumlah) AS jml_total FROM tbl_trans_tagihan where `no_ktp`='$row->no_ktp' and jenis_id=8")->row();
				$bulan_lalu_bayar = $this->db->query("SELECT SUM(jumlah) AS jml_total FROM tbl_trans_sp where `no_ktp`='$row->no_ktp' and jenis_id=8")->row();

				if (!empty($potongan_gaji)) {
					$pg = $potongan_gaji->potongan_gaji;
				} else {
					$pg = 0;
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
				if (!empty($bulan_lalu)) {
					$bulan_lalu = $bulan_lalu->tagihan_tak_terbayar;
				} else {
					$bulan_lalu = 0;
				}
				//$bulan_lalu = $this->lap_kas_anggota_m->get_bulan($row->no_ktp);
				$t_tagihan_bulan = $bulan_lalu;

				echo '<table style="width:100%;">
					<tr>
						<td>' . $jenis->jns_simpan . '</td>
						<td class="h_kanan">' . number_format($simpanan_row) . '</td>
					</tr>';
			}

			/*echo '<tr>
						<td><strong> Jumlah Simpanan </strong></td>
						<td class="h_kanan"><strong> ' . number_format($simpanan_row_total) . '</strong></td>
					</tr>

					<tr>
						<td>Tag. Bulan Lalu</td>
						<td class="h_kanan">' . number_format($bulan_tak = $bulan_tak - $bulan_lalu_bayar->jml_total) . '</td>
					</tr>';
			if (empty($bayar->jumlah_bayar)) {
				$bayar = 0;
			} else {
				$bayar = $bayar->jumlah_bayar;
			}
			if (empty($tagih->jumlah_tagihan)) {
				$tagih = 0;
			} else {
				$tagih = $tagih->jumlah_tagihan;
			}
			$hak_tagih = abs($bayar - $tagih);

			if (empty($bayar_toserda->jumlah)) {
				$bayar_toserda = 0;
			} else {
				$bayar_toserda = $bayar_toserda->jumlah;
			}
			if (empty($tagih_toserda->jumlah_bayar)) {
				$tagih_toserda = 0;
			} else {
				$tagih_toserda = $tagih_toserda->jumlah_bayar;
			}
			$hak_tagih_toserda = abs($bayar_toserda - $tagih_toserda);

			if (empty($bayar_lain_lain->jumlah_bayar)) {
				$bayar_lain_lain = 0;
			} else {
				$bayar_lain_lain = $bayar_lain_lain->jumlah_bayar;
			}
			if (empty($tagih_lain_lain->jumlah_bayar)) {
				$tagih_lain_lain = 0;
			} else {
				$tagih_lain_lain = $tagih_lain_lain->jumlah_bayar;
			}
			$piutang_tak_terbayar = $bulan_tak;
			$hak_tagih_lain_lain = abs($bayar_lain_lain - $tagih_lain_lain);
			echo '
					<tr>
						<td>Piutang Takterbayar</td>
						<td class="h_kanan">' . number_format($piutang_tak_terbayar) . '</td>
					</tr>
					
					</table>';
			if (empty($biasa_pinjaman)) {
				$bp_jumlah = 0;
				$bp_sisa_pokok = 0;
			} else {
				$bp_jumlah = $biasa_pinjaman->jumlah;
				$bp_sisa_pokok = $biasa_pinjaman->sisa_pokok;
			}

			if (empty($barang_pinjaman)) {
				$ba_jumlah 		= 0;
				$ba_sisa_pokok 	= 0;
			} else {
				$ba_jumlah 		= $barang_pinjaman->jumlah;
				$ba_sisa_pokok 	= $barang_pinjaman->sisa_pokok;
			}

			if (empty($bank_pinjaman)) {
				$bank_jumlah = 0;
				$bank_sisa_pokok = 0;
			} else {
				$bank_jumlah = $bank_pinjaman->jumlah;
				$bank_sisa_pokok = $bank_pinjaman->sisa_pokok;
			}*/
			
			echo '</td>';
			echo '<tr>';
		}
		echo '</table>';
		
		
	}

	public function index()
	{		
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
		if (isset($_GET['anggota_id']) && $_GET['anggota_id'] > 0) {
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
		
		//echo $offset;
		//exit();
		
		if ($offset > 0) {
			$offset = ($offset * $config['per_page']) - $config['per_page'];
		}
		$this->data["data_anggota"] = $this->lap_kas_anggota_m->get_data_anggota($config["per_page"], $offset);
		$this->data["halaman"] = $this->pagination->create_links();
		$this->data["offset"] = $offset;
		$this->data["data_jns_simpanan"] = $this->lap_kas_anggota_m->get_jenis_simpan();
		$this->data['isi'] = $this->load->view('lap_kas_anggota_list_v', $this->data, TRUE);
		//$this->data['isi'] = 'lap_kas_anggota_list_v';
		
		$this->load->view('themes/layout_utama_v', $this->data);
	}

	function cetak_simpanan()
	{
		$anggota = $this->lap_kas_anggota_m->lap_data_anggota();
		$data_jns_simpanan = $this->lap_kas_anggota_m->get_jenis_simpan();
		if ($anggota == FALSE) {
			redirect('lap_kas_anggota');
			exit();
		}
		$txt_periode_arr = explode('-', $_REQUEST['periode']);
		if (is_array($txt_periode_arr)) {
			$periode = jin_nama_bulan($txt_periode_arr[1]) . ' ' . $txt_periode_arr[0];
		}
		$semua_pengguna = $this->lap_kas_anggota_m->lap_data_anggota();
		$styleJudul = [
			'font' => [
				'color' => [
					'rgb' => 'FFFFFF'
				],
				'bold' => true,
				'size' => 11
			],
			'fill' => [
				'fillType' =>  fill::FILL_SOLID,
				'startColor' => [
					'rgb' => 'e74c3c'
				]
			],
			'alignment' => [
				'horizontal' => Alignment::HORIZONTAL_CENTER
			]

		];
		$spreadsheet = new Spreadsheet;
		$spreadsheet->getActiveSheet()
			->setCellValue('A1', "LAPORAN KAS ANGGOTA PER " . $periode);
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
		foreach ($semua_pengguna as $row) {
			$simpan = $this->db->query("SELECT 
		`c`.`nama` AS `nama`, 
		`c`.`id` AS `anggota_id`, 
		`c`.`no_ktp` AS `no_ktp`, 
		sum(if(`a`.`jenis_id` = 40,if(`a`.`dk` = 'D',`a`.`jumlah`,`a`.`jumlah`*-1),0)) AS `simpanan_pokok`, 
		sum(if(`a`.`jenis_id` = 41,if(`a`.`dk` = 'D',`a`.`jumlah`,`a`.`jumlah`*-1),0)) AS `simpanan_wajib`, 
		sum(if(`a`.`jenis_id` = 32,if(`a`.`dk` = 'D',`a`.`jumlah`,`a`.`jumlah`*-1),0)) AS `simpanan_sukarela`, 
		sum(if(`a`.`jenis_id` = 52,if(`a`.`dk` = 'D',`a`.`jumlah`,`a`.`jumlah`*-1),0)) AS `simpanan_khusus_2`,
		sum(if(`a`.`jenis_id` = 51,if(`a`.`dk` = 'D',`a`.`jumlah`,`a`.`jumlah`*-1),0)) AS `simpanan_khusus_1`,
		sum(if(`a`.`jenis_id` = 31,if(`a`.`dk` = 'D',`a`.`jumlah`,`a`.`jumlah`*-1),0)) AS `tabungan_perumahan`
	FROM ((`tbl_trans_sp` `a` join `jns_simpan` `b` on(`a`.`jenis_id` = `b`.`id`)) join `tbl_anggota` `c` on(`a`.`no_ktp` = `c`.`no_ktp`)) 
	where `c`.`no_ktp`='$row->no_ktp'
	GROUP BY `c`.`no_ktp` 
	order by c.nama asc")->result();

			foreach ($simpan as $r) {
				if (empty($r->simpanan_pokok)) {
					$sp = 0;
				} else {
					$sp = $r->simpanan_pokok;
				}
				if (empty($r->simpanan_wajib)) {
					$sw = 0;
				} else {
					$sw = $r->simpanan_wajib;
				}
				if (empty($r->simpanan_sukarela)) {
					$ss = 0;
				} else {
					$ss = $r->simpanan_sukarela;
				}
				if (empty($r->simpanan_khusus_2)) {
					$sk2 = 0;
				} else {
					$sk2 = $r->simpanan_khusus_2;
				}
				if (empty($r->simpanan_khusus_1)) {
					$sk1 = 0;
				} else {
					$sk1 = $r->simpanan_khusus_1;
				}
				if (empty($r->tabungan_perumahan)) {
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
		header('Content-Disposition: attachment;filename="laporan_simpanan_' . date('Ymd_His') . '.xlsx"');
		header('Cache-Control: max-age=0');
		$writer->save('php://output');
	}

	function simple_table()
	{
		$this->db->select('
        b.no_ktp, 
        b.nama, 
        SUM(CASE WHEN a.jenis_id = 40 THEN a.jumlah ELSE 0 END) AS simpanan_pokok, 
        SUM(CASE WHEN a.jenis_id = 41 THEN a.jumlah ELSE 0 END) AS simpanan_wajib, 
        SUM(CASE WHEN a.jenis_id = 32 THEN a.jumlah ELSE 0 END) AS simpanan_sukarela, 
        SUM(CASE WHEN a.jenis_id = 52 THEN a.jumlah ELSE 0 END) AS simpanan_khusus_2
    ');
		$this->db->from('tbl_anggota b');
		$this->db->join('v_tagihan a', 'a.no_ktp = b.no_ktp', 'left');
		$this->db->where('YEAR(a.tgl_transaksi)', '2024');
		$this->db->where('MONTH(a.tgl_transaksi)', '07');
		$this->db->group_by('b.no_ktp');
		$this->db->order_by('b.nama', 'ASC');

		$query = $this->db->get();
		$result = $query->result();

		$data = [];
		foreach ($result as $row) {
			$data[] = [
				'no_ktp' => $row->no_ktp,
				'nama' => $row->nama,
				'simpanan_pokok' => $row->simpanan_pokok ?: 0,
				'simpanan_wajib' => $row->simpanan_wajib ?: 0,
				'simpanan_sukarela' => $row->simpanan_sukarela ?: 0,
				'simpanan_khusus_2' => $row->simpanan_khusus_2 ?: 0
			];
		}

		$this->data['all'] = $data;
		$this->load->view('test', $this->data);
	}

	function cetak_excel()
	{
		$anggota = $this->lap_kas_anggota_m->lap_data_anggota();
		$data_jns_simpanan = $this->lap_kas_anggota_m->get_jenis_simpan();
		if ($anggota == FALSE) {
			redirect('lap_kas_anggota');
			exit();
		}
		$txt_periode_arr = explode('-', $_REQUEST['periode']);
		if (is_array($txt_periode_arr)) {
			$periode = jin_nama_bulan($txt_periode_arr[1]) . ' ' . $txt_periode_arr[0];
		}
		$semua_pengguna = $this->lap_kas_anggota_m->lap_data_anggota_limit();
		$styleJudul = [
			'font' => [
				'color' => [
					'rgb' => 'FFFFFF'
				],
				'bold' => true,
				'size' => 11
			],
			'fill' => [
				'fillType' =>  fill::FILL_SOLID,
				'startColor' => [
					'rgb' => 'e74c3c'
				]
			],
			'alignment' => [
				'horizontal' => Alignment::HORIZONTAL_CENTER
			]

		];
		$spreadsheet = new Spreadsheet;

		$spreadsheet->getActiveSheet()
			->setCellValue('A1', "POTONGAN KOPERASI PT. KAO INDONESIA " . $periode);
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
		foreach ($semua_pengguna as $row) {
			$this->db->select('
	b.no_ktp, 
	b.nama, 
	SUM(CASE WHEN a.jenis_id = 40 THEN a.jumlah ELSE 0 END) AS simpanan_pokok, 
	SUM(CASE WHEN a.jenis_id = 41 THEN a.jumlah ELSE 0 END) AS simpanan_wajib, 
	SUM(CASE WHEN a.jenis_id = 32 THEN a.jumlah ELSE 0 END) AS simpanan_sukarela, 
	SUM(CASE WHEN a.jenis_id = 52 THEN a.jumlah ELSE 0 END) AS simpanan_khusus_2
	');
			$this->db->from('tbl_anggota b');
			$this->db->join('v_tagihan a', 'a.no_ktp = b.no_ktp', 'left');
			$this->db->where('YEAR(a.tgl_transaksi)', '2024');
			$this->db->where('MONTH(a.tgl_transaksi)', '07');
			$this->db->where('b.no_ktp', $row->no_ktp);
			$this->db->group_by('b.no_ktp');
			$this->db->order_by('b.nama', 'ASC');

			$query = $this->db->get();
			$simpan = $query->result();

			foreach ($simpan as $r) {
				$sw = $r->simpanan_wajib ?: 0;
				$ss = $r->simpanan_sukarela ?: 0;
				$sk = $r->simpanan_khusus_2 ?: 0;
				$sp = $r->simpanan_pokok ?: 0;

				$spreadsheet->setActiveSheetIndex(0)
					->setCellValue('A' . $kolom, $nomor)
					->setCellValue('B' . $kolom, $row->id_tagihan)
					->setCellValue('C' . $kolom, $row->nama)
					->setCellValue('D' . $kolom, nsi_round($sw))
					->setCellValue('E' . $kolom, nsi_round($ss))
					->setCellValue('F' . $kolom, nsi_round($sk))
					->setCellValue('G' . $kolom, nsi_round($sp));
			}


			$kolom++;
			$nomor++;
		}

		$writer = new Xlsx($spreadsheet);
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="laporan_detail_potongan' . date('Ymd_His') . '.xlsx"');
		header('Cache-Control: max-age=0');
		$writer->save('php://output');
	}

	function cetak_tagihan()
	{
		$anggota = $this->lap_kas_anggota_m->lap_data_anggota();
		$data_jns_simpanan = $this->lap_kas_anggota_m->get_jenis_simpan();
		if ($anggota == FALSE) {
			redirect('lap_kas_anggota');
			exit();
		}
		$txt_periode_arr = explode('-', $_REQUEST['periode']);
		if (is_array($txt_periode_arr)) {
			$periode = jin_nama_bulan($txt_periode_arr[1]) . ' ' . $txt_periode_arr[0];
		}
		$semua_pengguna = $this->lap_kas_anggota_m->lap_data_anggota();
		$styleJudul = [
			'font' => [
				'color' => [
					'rgb' => 'FFFFFF'
				],
				'bold' => true,
				'size' => 11
			],
			'fill' => [
				'fillType' =>  fill::FILL_SOLID,
				'startColor' => [
					'rgb' => 'e74c3c'
				]
			],
			'alignment' => [
				'horizontal' => Alignment::HORIZONTAL_CENTER
			]

		];
		$spreadsheet = new Spreadsheet;
		$spreadsheet->getActiveSheet()
			->setCellValue('A1', "POTONGAN KOPERASI PT. KAO INDONESIA " . $periode);
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
		foreach ($semua_pengguna as $row) {
			if (isset($_REQUEST['periode'])) {
				$tgl_arr = explode('-', $_REQUEST['periode']);
				$thn = $tgl_arr[0];
				$bln = $tgl_arr[1];
			} else {
				$thn = date('Y');
				$bln = date('m');
			}

			$simpan = $this->db->query("SELECT `a`.`tgl_transaksi` AS `tanggal`, `b`.`no_ktp` AS `no_ktp`, `b`.`nama` AS `nama`, sum(if(`a`.`jenis_id` = 40,`a`.`jumlah`,0)) AS `simpanan_pokok`, sum(if(`a`.`jenis_id` = 41,`a`.`jumlah`,0)) AS `simpanan_wajib`, sum(if(`a`.`jenis_id` = 32,`a`.`jumlah`,0)) AS `simpanan_sukarela`, sum(if(`a`.`jenis_id` = 52,`a`.`jumlah`,0)) AS `simpanan_khusus_2` 
				FROM (`tbl_anggota` `b` left join `v_tagihan` `a` on(`a`.`no_ktp` = `b`.`no_ktp`)) 
				where YEAR(`a`.`tgl_transaksi`) = '" . $thn . "' and MONTH(`a`.`tgl_transaksi`) = '$bln' and `b`.`no_ktp`='$row->no_ktp' 
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

				if (empty($row->id_tagihan)) {
					$potongan = 0;
				} else if ($row->id_tagihan == '24100162') {
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
		header('Content-Disposition: attachment;filename="laporan_potongan' . date('Ymd_His') . '.xlsx"');
		header('Cache-Control: max-age=0');
		$writer->save('php://output');
	}
}
