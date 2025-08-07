<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bayar_upload extends OperatorController {
	public function __construct() {
		parent::__construct();	
		$this->load->helper('fungsi');
		$this->load->model('bayar_upload_m');
		$this->load->model('general_m');
		//$id_cab = $this->session->userdata('id_cabang');
	}	

	public function index() {
		$this->data['judul_browser'] 	= 'Transaksi';
		$this->data['judul_utama'] 		= 'Transaksi';
		$this->data['judul_sub'] 		= 'Setoran Upload';
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
		$this->data['kas_id'] 	= $this->bayar_upload_m->get_data_kas();
		$this->data['jenis_id'] = $this->general_m->get_id_simpanan();
		$this->data['isi'] 		= $this->load->view('bayar_upload_list_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}

	public function trans_lalu($periode){
		$total_anggota = $this->db->select('count(jumlah) as jumlah')
			->from('tbl_trans_sp')			
			->where('dk', 'D')
			->where_in('jenis_id', array(32, 40, 41))
			->like('tgl_transaksi', $periode)
			->get();

		$simpanan_pokok = $this->db->select('SUM(jumlah) as jumlah')
			->from('tbl_trans_sp')
			->where('jenis_id', 40)
			->where('dk', 'D')
			->like('tgl_transaksi', $periode)
			->get();

		$simpanan_sukarela = $this->db->select('SUM(jumlah) as jumlah')
			->from('tbl_trans_sp')
			->where('jenis_id', 32)
			->where('dk', 'D')
			->like('tgl_transaksi', $periode)
			->get();

		$simpanan_wajib = $this->db->select('SUM(jumlah) as jumlah')
			->from('tbl_trans_sp')
			->where('jenis_id', 41)
			->where('dk', 'D')
			->like('tgl_transaksi', $periode)
			->get();

		echo '<table border="1">';
			echo '<thead>';
				echo '<th>Periode</th>';				
				echo '<th>Total Anggota</th>';				
				echo '<th>Simpanan Sukarela</th>';
				echo '<th>Simpanan Pokok</th>';
				echo '<th>Simpanan Wajib</th>';
			echo '</thead>';
			echo '<tbody>';
				echo '<tr>';
					echo '<td>' . $periode . '</td>';
					echo '<td align="right">' . number_format($total_anggota->row()->jumlah) . '</td>';
					echo '<td align="right">' . number_format($simpanan_sukarela->row()->jumlah) . '</td>';
					echo '<td align="right">' . number_format($simpanan_pokok->row()->jumlah) . '</td>';
					echo '<td align="right">' . number_format($simpanan_wajib->row()->jumlah) . '</td>';
				echo '</tr>';
			echo '</tbody>';
		echo '</table>';
	}

	function import() {
		
		$this->data['judul_browser'] 	= 'Import Data';
		$this->data['judul_utama'] 		= 'Import Data';
		$this->data['judul_sub'] 		= 'Setoran Upload <a href="'.site_url('bayar_upload').'" class="btn btn-sm btn-success">Kembali</a>';
		$this->load->helper(array('form'));
		if($this->input->post('submit')) {
			
			$config['upload_path']   	= FCPATH . 'uploads/temp/';
			$config['allowed_types'] 	= 'xls|xlsx';
			$config['max_size']			= 0;
			$this->load->library('upload', $config);
			if ( ! $this->upload->do_upload('import_bayar_upload')) {
				$this->data['error'] = $this->upload->display_errors();
			} else {
				if(!isset($_GET)) {
					show_404();
				}
				if(isset($_REQUEST['periode'])) {
					$tgl_arr = explode('-', $_REQUEST['periode']);
					$thn = $tgl_arr[0];
					$bln = $tgl_arr[1];
				} else {
					$thn = date('Y');
					$bln = date('m');
				}
				$file = $this->upload->data();
				$this->data['file'] = $file;
				$this->data['lokasi_file'] = $file['full_path'];
				$this->load->library('excel');
				// baca excel
				$objPHPExcel = PHPExcel_IOFactory::load($file['full_path']);
				$no_sheet = 1;
				$header = array();
				$data_list_x = array();
				$data_list = array();
				foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
					if($no_sheet == 1) { // ambil sheet 1 saja
						$no_sheet++;
						$worksheetTitle = $worksheet->getTitle();
						$highestRow = $worksheet->getHighestRow(); // e.g. 10
						$highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
						$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
						$nrColumns = ord($highestColumn) - 64;
						//echo "File ".$worksheetTitle." has ";
						//echo $nrColumns . ' columns';
						//echo ' y ' . $highestRow . ' rows.<br />';
						$data_jml_arr = array();
						//echo 'Data: <table width="100%" cellpadding="3" cellspacing="0"><tr>';
						for ($row = 1; $row <= $highestRow; ++$row) {
						   //echo '<tr>';
							for ($col = 0; $col < $highestColumnIndex; ++$col) {
								$cell = $worksheet->getCellByColumnAndRow($col, $row);
								$val = $cell->getValue();
								$kolom = PHPExcel_Cell::stringFromColumnIndex($col);
								if($row === 1) {
									if($kolom == 'A') {
										$header[$kolom] = 'tgl_transaksi';
									} else {
										$header[$kolom] = $val;
									}
								} else {
									$data_list_x[$row][$kolom] = $val;
								}
							}
						}
					}
				}

				$no = 1;
				foreach ($data_list_x as $data_kolom) {
					if((@$data_kolom['A'] == NULL || trim(@$data_kolom['A'] == '')) ) { continue; }
					foreach ($data_kolom as $kolom => $val) {
						if(in_array($kolom, array('E', 'K', 'L')) ) {
							$val = ltrim($val, "'");
						}
						$data_list[$no][$kolom] = $val;
					}
					$no++;
				}
				//$arr_data = array();
				$this->data['header'] = $header;
				$this->data['values'] = $data_list;
				/*
				$data_import = array(
					'import_anggota_header'		=> $header,
					'import_anggota_values' 	=> $data_list
					);
				$this->session->set_userdata($data_import);
				*/
			}
		}

		$this->data['isi'] = $this->load->view('bayar_upload_import_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}

	function import_db() {
		//echo 'hello world';
		//exit();
		
		//$data = array();
			
		if($this->input->post('submit')) {
			if(!isset($_GET)) {
					show_404();
				}
				if(isset($_REQUEST['periode'])) {
					$tgl_arr = explode('-', $_REQUEST['periode']);
					$thn = $tgl_arr[0];
					$bln = $tgl_arr[1];
				} else {
					$thn = date('Y');
					$bln = date('m');
				}
			$periode = $this->input->post('periode');
			$this->load->model('bayar_upload_m','bayar_upload', TRUE);
			$data_import = $this->input->post('val_arr');
			if($this->bayar_upload->import_db($data_import,$periode)) {
				
				

				
				$sql = "CALL bayar_upload";
		        $XPeriode = $periode;
		        $this->db->query("$sql ('%$XPeriode%')");

			$rec = $this->db->select('count(no_ktp) as total_anggota, sum(jumlah) AS total_jumlah')
				->from('tbl_trans_sp_bayar_temp')
				->like('tgl_transaksi', $XPeriode)
				->get();

			$data['total_anggota'] = $rec->row()->total_anggota;	
			$data['total_jumlah'] = $rec->row()->total_jumlah;

			$this->session->set_flashdata('total_anggota', number_format($rec->row()->total_anggota));
			$this->session->set_flashdata('total_jumlah', number_format($rec->row()->total_jumlah));



		        $this->session->set_flashdata('import', 'OK');
		        //redirect('ctransct');
			} else {
				$this->session->set_flashdata('import', 'NO');
			}
			//hapus semua file di temp
			$files = glob('uploads/temp/*');
			foreach($files as $file){ 
				if(is_file($file)) {
					@unlink($file);
				}
			}
			redirect('bayar_upload/import');
		} else {
			$this->session->set_flashdata('import', 'NO');
			redirect('bayar_upload/import');
		}
	}

	function import_batal() {
		//hapus semua file di temp
		$files = glob('uploads/temp/*');
		foreach($files as $file){ 
			if(is_file($file)) {
				@unlink($file);
			}
		}
		$this->session->set_flashdata('import', 'BATAL');
		redirect('bayar_upload/import');
	}

	function list_anggota() {
		$q = isset($_POST['q']) ? $_POST['q'] : '';
		$data   = $this->general_m->get_data_anggota_ajax($q);
		$i	= 0;
		$rows   = array(); 
		foreach ($data['data'] as $r) {
			if($r->file_pic == '') {
				$rows[$i]['photo'] = '<img src="'.base_url().'assets/theme_admin/img/photo.jpg" alt="default" width="30" height="40" />';
			} else {
				$rows[$i]['photo'] = '<img src="'.base_url().'uploads/anggota/' . $r->file_pic . '" alt="Foto" width="30" height="40" />';
			}
			$rows[$i]['id'] = $r->id;
			$rows[$i]['kode_anggota'] = 'AG'.sprintf('%04d', $r->id) . '<br />' . $r->nama;
			$rows[$i]['nama'] = $r->nama;
			$rows[$i]['no_ktp'] = $r->no_ktp;
			$i++;
		}
		//keys total & rows wajib bagi jEasyUI
		$result = array('total'=>$data['count'],'rows'=>$rows);
		echo json_encode($result); //return nya json
	}

	function get_anggota_by_id() {
		$id = isset($_POST['anggota_id']) ? $_POST['anggota_id'] : '';
		$r   = $this->general_m->get_data_anggota($id);
		$out = '';
		$photo_w = 3 * 30;
		$photo_h = 4 * 30;
		if($r->file_pic == '') {
			$out ='<img src="'.base_url().'assets/theme_admin/img/photo.jpg" alt="default" width="'.$photo_w.'" height="'.$photo_h.'" />'
			.'<br /> ID : '.'AG' . sprintf('%04d', $r->id) . '<br /><input type="hidden" name="no_ktp" value="'.$r->no_ktp.'" />';
		} else {
			$out = '<img src="'.base_url().'uploads/anggota/' . $r->file_pic . '" alt="Foto" width="'.$photo_w.'" height="'.$photo_h.'" />'
			.'<br /> ID : '.'AG' . sprintf('%04d', $r->id) . '<br /><input type="hidden" name="no_ktp" value="'.$r->no_ktp.'" />';
		}
		echo $out;
		exit();
	}

	function ajax_list() {
		$offset = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$limit  = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
		$sort  = isset($_POST['sort']) ? $_POST['sort'] : 'tgl_transaksi';
		$order  = isset($_POST['order']) ? $_POST['order'] : 'desc';
		$kode_transaksi = isset($_POST['kode_transaksi']) ? $_POST['kode_transaksi'] : '';
		$cari_bayar_upload = isset($_POST['cari_bayar_upload']) ? $_POST['cari_bayar_upload'] : '';
		$tgl_dari = isset($_POST['tgl_dari']) ? $_POST['tgl_dari'] : '';
		$tgl_sampai = isset($_POST['tgl_sampai']) ? $_POST['tgl_sampai'] : '';
		$search = array('kode_transaksi' => $kode_transaksi, 
			'cari_bayar_upload' => $cari_bayar_upload,
			'tgl_dari' 		=> $tgl_dari, 
			'tgl_sampai' 	=> $tgl_sampai);
		$offset = ($offset-1)*$limit;
		$data   = $this->bayar_upload_m->get_data_transaksi_ajax($offset,$limit,$search,$sort,$order);
		$i	= 0;
		$rows   = array(); 

		foreach ($data['data'] as $r) {
			$tgl_bayar = explode(' ', $r->tgl_transaksi);
			$txt_tanggal = jin_date_ina($tgl_bayar[0]);
			//$txt_tanggal .= ' - ' . substr($tgl_bayar[1], 0, 5);
			$anggota = $this->general_m->get_data_anggotas($r->no_ktp);
			$rows[$i]['id'] 				= $r->id;
			$rows[$i]['id_txt'] 			='TRD' . sprintf('%05d', $r->id) . '';
			$rows[$i]['tgl_transaksi'] 		= $r->tgl_transaksi;
			$rows[$i]['tgl_transaksi_txt'] 	= $txt_tanggal;
			$rows[$i]['anggota_id']	 		= $anggota->id;
			$rows[$i]['anggota_nama']	 	= $anggota->nama;
			$rows[$i]['no_ktp']	 			= $r->no_ktp;
			$rows[$i]['nama'] 				= $anggota->nama.' <br />'.$anggota->no_ktp;
			$rows[$i]['jumlah'] 			= number_format($r->jumlah);
			$rows[$i]['tagihan_simpanan_wajib'] 	= number_format($r->tagihan_simpanan_wajib);
			$rows[$i]['tagihan_simpanan_pokok'] 	= number_format($r->tagihan_simpanan_pokok);
			$rows[$i]['tagihan_simpanan_sukarela'] 	= number_format($r->tagihan_simpanan_sukarela);
			$rows[$i]['tagihan_simpanan_khusus_2'] 	= number_format($r->tagihan_simpanan_khusus_2);
			$rows[$i]['tagihan_pinjaman'] 	= number_format($r->tagihan_pinjaman);
			$rows[$i]['tagihan_pinjaman_jasa'] 	= number_format($r->tagihan_pinjaman_jasa);
			$rows[$i]['tagihan_toserda'] 	= number_format($r->tagihan_toserda);
			$rows[$i]['jumlah_tagihan'] 	= number_format($r->total_tagihan_simpanan);
			$rows[$i]['selisih'] 			= number_format($r->selisih);
			$rows[$i]['saldo_simpanan_sukarela'] 	= number_format($r->saldo_simpanan_sukarela);
			$rows[$i]['saldo_akhir_simpanan_sukarela'] 		= number_format($r->saldo_akhir_simpanan_sukarela);
			$i++;
		}
		$result = array('total'=>$data['count'],'rows'=>$rows);
		echo json_encode($result); //return nya json
	}

	function get_jenis_simpanan() {
		$id = $this->input->post('jenis_id');
		$jenis_simpanan = $this->general_m->get_id_simpanan();
		foreach ($jenis_simpanan as $row) {
			if($row->id == $id) {
				echo number_format($row->jumlah);
			}
		}
		exit();
	}

	public function create() {
		if(!isset($_POST)) {
			show_404();
		}
		if($this->bayar_upload_m->create()){
			echo json_encode(array('ok' => true, 'msg' => '<div class="text-green"><i class="fa fa-check"></i> Data berhasil disimpan </div>'));
		} else {
			echo json_encode(array('ok' => false, 'msg' => '<div class="text-red"><i class="fa fa-ban"></i> Gagal menyimpan data, pastikan nilai lebih dari <strong>0 (NOL)</strong>. </div>'));
		}
	}

	public function update($id=null) {
		if(!isset($_POST)) {
			show_404();
		}
		if($this->bayar_upload_m->update($id)) {
			echo json_encode(array('ok' => true, 'msg' => '<div class="text-green"><i class="fa fa-check"></i> Data berhasil diubah </div>'));
		} else {
			echo json_encode(array('ok' => false, 'msg' => '<div class="text-red"><i class="fa fa-ban"></i>  Maaf, Data gagal diubah, pastikan nilai lebih dari <strong>0 (NOL)</strong>. </div>'));
		}
	}

	public function ins() {
		if(!isset($_POST)) {
			show_404();
		}
		if(isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}
		if($this->bayar_upload_m->ins()){
			$selisih = $this->bayar_upload_m->get_jml_selisih();
			echo "Success";
			return "Success";
        	//return $load;
			//echo json_encode(array('ok' => true, 'msg' => '<div class="text-green"><i class="fa fa-check"></i> Data berhasil disimpan </div>'));
		} else {
			echo "Failed";
			return "Failed";
			//echo json_encode(array('ok' => false, 'msg' => '<div class="text-red"><i class="fa fa-ban"></i> Gagal menyimpan data, pastikan nilai lebih dari <strong>0 (NOL)</strong>. </div>'));
		}
	}

	public function del() {
		if(!isset($_GET)) {
			show_404();
		}
		if(isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}
		if($this->bayar_upload_m->del()){
			echo json_encode(array('ok' => true, 'msg' => '<div class="text-green"><i class="fa fa-check"></i> Data berhasil diload </div>'));
		} else {
			echo json_encode(array('ok' => false, 'msg' => '<div class="text-red"><i class="fa fa-ban"></i> Gagal load data </div>'));
		}
	}

	public function delete() {
		if(!isset($_POST))	 {
			show_404();
		}
		$id = intval(addslashes($_POST['id']));
		if($this->bayar_upload_m->delete($id))
		{
			echo json_encode(array('ok' => true, 'msg' => '<div class="text-green"><i class="fa fa-check"></i> Data berhasil dihapus </div>'));
		} else {
			echo json_encode(array('ok' => false, 'msg' => '<div class="text-red"><i class="fa fa-ban"></i> Maaf, Data gagal dihapus </div>'));
		}
	}

	function cetak_laporan() {
		$simpanan = $this->bayar_upload_m->lap_data_simpanan();
		if($simpanan == FALSE) {
			//redirect('simpanan');
			echo 'DATA KOSONG<br>Pastikan Filter Tanggal dengan benar.';
			exit();
		}

		$tgl_dari = $_REQUEST['tgl_dari']; 
		$tgl_sampai = $_REQUEST['tgl_sampai']; 

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
			.txt_judul {font-size: 12pt; font-weight: bold; padding-bottom: 12px;}
			.header_kolom {background-color: #cccccc; text-align: center; font-weight: bold;}
			.txt_content {font-size: 10pt; font-style: arial;}
		</style>
		'.$pdf->nsi_box($text = '<span class="txt_judul">Laporan Data Simpanan Anggota <br></span>
			<span> Periode '.jin_date_ina($tgl_dari).' - '.jin_date_ina($tgl_sampai).'</span> ', $width = '100%', $spacing = '0', $padding = '1', $border = '0', $align = 'center').'
		<table width="100%" cellspacing="0" cellpadding="3" border="1" border-collapse= "collapse">
		<tr class="header_kolom">
			<th class="h_tengah" style="width:5%;"> No. </th>
			<th class="h_tengah" style="width:8%;"> No Transaksi</th>
			<th class="h_tengah" style="width:7%;"> Tanggal </th>
			<th class="h_tengah" style="width:13%;"> No KTP </th>
			<th class="h_tengah" style="width:25%;"> Nama Anggota </th>
			<th class="h_tengah" style="width:10%;"> Jenis Simpanan </th>
			<th class="h_tengah" style="width:13%;"> Jumlah  </th>
		</tr>';

		$no =1;
		$jml_simpanan = 0;
		foreach ($simpanan as $row) {
			$anggota= $this->simpanan_m->get_data_anggota($row->anggota_id);
			$jns_simpan= $this->simpanan_m->get_jenis_simpan($row->jenis_id);

			$tgl_bayar = explode(' ', $row->tgl_transaksi);
			$txt_tanggal = jin_date_ina($tgl_bayar[0],'p');

			$jml_simpanan += $row->jumlah;

			// '.'AG'.sprintf('%04d', $row->anggota_id).'
			$html .= '
			<tr>
				<td class="h_tengah" >'.$no++.'</td>
				<td class="h_tengah"> '.'TRD'.sprintf('%05d', $row->id).'</td>
				<td class="h_tengah"> '.$txt_tanggal.'</td>
				<td> '.$anggota->no_ktp.'</td>
				<td class="h_kiri"> '.'AG'.sprintf('%04d', $row->anggota_id).' - '.$anggota->nama.'</td>
				<td> '.$jns_simpan->jns_simpan.'</td>
				<td class="h_kanan"> '.number_format($row->jumlah).'</td>
			</tr>';
		}
		$html .= '
		<tr>
			<td colspan="6" class="h_tengah"><strong> Jumlah Total </strong></td>
			<td class="h_kanan"> <strong>'.number_format($jml_simpanan).'</strong></td>
		</tr>
		</table>';
		$pdf->nsi_html($html);
		$pdf->Output('trans_sp'.date('Ymd_His') . '.pdf', 'I');
	} 

	public function generate_tempo(){
		$no_ktp = $this->input->post('no_ktp'); //2019020001

		$data_pinjaman = $this->db->query("SELECT * FROM tbl_pinjaman_h WHERE no_ktp='$no_ktp'")->row_array();

		$pinjam_id = $data_pinjaman['id'];
		$lama_angsuran = $data_pinjaman['lama_angsuran'];
		$tgl_pinjam = SUBSTR($data_pinjaman['tgl_pinjam'], 0, 10);

		$tgl_pinjam_explode = explode("-", $tgl_pinjam);
		$tgl_pinjam_year = $tgl_pinjam_explode[0];
		$tgl_pinjam_month = $tgl_pinjam_explode[1];
		$tgl_pinjam_date = $tgl_pinjam_explode[2];

		echo $no_ktp.'<br>';
		echo $lama_angsuran.'<br>';
		echo $pinjam_id.'<br>';
		echo $tgl_pinjam.'<br><br>';

		echo 'TEMPO : <br><br>';

		for($i=1; $i<=$lama_angsuran; $i++){
			$tgl_pinjam_month++;

			if($tgl_pinjam_month <= 12){
				$tgl_pinjam_month = $tgl_pinjam_month;
				$tgl_pinjam_year = $tgl_pinjam_year;
			}elseif($tgl_pinjam_month <= 24){
				$tgl_pinjam_month = $tgl_pinjam_month-12;
				$tgl_pinjam_year = $tgl_pinjam_year+1;
			}elseif($tgl_pinjam_month <= 36){
				$tgl_pinjam_month = $tgl_pinjam_month-24;
				$tgl_pinjam_year = $tgl_pinjam_year+2;
			}elseif($tgl_pinjam_month <= 48){
				$tgl_pinjam_month = $tgl_pinjam_month-36;
				$tgl_pinjam_year = $tgl_pinjam_year+3;
			}

			if(strlen($tgl_pinjam_month) == 1){
				$tgl_pinjam_month_v = '0'.$tgl_pinjam_month;
			}elseif(strlen($tgl_pinjam_month) == 2){
				$tgl_pinjam_month_v = $tgl_pinjam_month;
			}

			// echo 'No Urut : '.$i.' , ';
			// echo 'Pinjam ID : '.$pinjam_id.' , ';
			// echo 'NO KTP : '.$no_ktp.' , ';
			// echo 'Tanggal Pinjam : '.$tgl_pinjam.' , ';
			// echo 'Tempo : '.$tgl_pinjam_year.'-'.$tgl_pinjam_month_v.'-'.$tgl_pinjam_date;
			// echo '<br>';

			$this->db->insert('tempo_pinjaman', array(
				'no_urut' => $i,
				'pinjam_id' => $pinjam_id,
				'no_ktp' => $no_ktp,
				'tgl_pinjam' => $tgl_pinjam,
				'tempo' => $tgl_pinjam_year.'-'.$tgl_pinjam_month_v.'-'.$tgl_pinjam_date
			));
		}

		redirect('bayar_upload');

	}

}