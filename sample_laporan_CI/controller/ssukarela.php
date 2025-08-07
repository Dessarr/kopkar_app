<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ssukarela extends OperatorController {
	public function __construct() {
		parent::__construct();	
		$this->load->helper('fungsi');
		$this->load->model('ssukarela_m');
		$this->load->model('general_m');
		//$id_cab = $this->session->userdata('id_cabang');
	}	

	public function index() {
		$this->data['judul_browser'] 	= 'Transaksi';
		$this->data['judul_utama'] 		= 'Transaksi';
		$this->data['judul_sub'] 		= 'Invoice';

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
		$this->data['kas_id'] = $this->ssukarela_m->get_data_kas();
		$this->data['jenis_id'] = $this->general_m->get_id_simpanan();
		$this->data['isi'] = $this->load->view('ssukarela_list_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
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
		$cari_ssukarela = isset($_POST['cari_ssukarela']) ? $_POST['cari_ssukarela'] : '';
		$tgl_dari = isset($_POST['tgl_dari']) ? $_POST['tgl_dari'] : '';
		$tgl_sampai = isset($_POST['tgl_sampai']) ? $_POST['tgl_sampai'] : '';
		$search = array('kode_transaksi' => $kode_transaksi, 
			'cari_ssukarela' => $cari_ssukarela,
			'tgl_dari' 		=> $tgl_dari, 
			'tgl_sampai' 	=> $tgl_sampai);
		$offset = ($offset-1)*$limit;
		$data   = $this->ssukarela_m->get_data_transaksi_ajax($offset,$limit,$search,$sort,$order);
		$i	= 0;
		$rows   = array(); 
		foreach ($data['data'] as $r) {
			$tgl_bayar = explode(' ', $r->tgl_transaksi);
			$txt_tanggal = jin_date_ina($tgl_bayar[0]);
			//$txt_tanggal .= ' - ' . substr($tgl_bayar[1], 0, 5);
			$anggota 		= $this->general_m->get_data_anggotas($r->no_ktp);
			//$nama_simpanan 	= $this->general_m->get_jns_simpanan($r->jenis_id);
			$rows[$i]['id'] 				= $r->id;
			$rows[$i]['id_txt'] 			='TRD' . sprintf('%05d', $r->id) . '';
			$rows[$i]['tgl_transaksi'] 		= $r->tgl_transaksi;
			$rows[$i]['tgl_transaksi_txt'] 	= $txt_tanggal;
			$rows[$i]['anggota_id']	 		= $r->anggota_id;
			$rows[$i]['no_ktp']	 			= $r->no_ktp;
			//$rows[$i]['anggota_id_txt'] 	= 'AG' . sprintf('%04d', $r->anggota_id);
			$rows[$i]['anggota_id_txt'] 	= $r->no_ktp;
			$rows[$i]['nama'] 				= $anggota->nama.' <br />'.$anggota->no_ktp;
			//$rows[$i]['departement'] 		= $anggota->departement;
			//$rows[$i]['jenis_id'] 			= $r->jns_simpan;
			$rows[$i]['jenis_id_txt'] 		= $r->jns_simpan;
			$rows[$i]['jumlah'] 			= number_format($r->jumlah);
			$rows[$i]['ket'] 				= $r->keterangan;
			//$rows[$i]['user'] 				= $r->user_name;
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

	public function view() {
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
		if($this->ssukarela_m->view()){
			echo json_encode(array('ok' => true, 'msg' => '<div class="text-green"><i class="fa fa-check"></i> Data berhasil diload </div>'));
		} else {
			echo json_encode(array('ok' => false, 'msg' => '<div class="text-red"><i class="fa fa-ban"></i> Gagal load data </div>'));
		}
	}

	public function muat() {
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
		if($this->ssukarela_m->load()){
			echo json_encode(array('ok' => true, 'msg' => '<div class="text-green"><i class="fa fa-check"></i> Data berhasil diload </div>'));
		} else {
			echo json_encode(array('ok' => false, 'msg' => '<div class="text-red"><i class="fa fa-ban"></i> Gagal load data </div>'));
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
		if($this->ssukarela_m->del()){
			echo json_encode(array('ok' => true, 'msg' => '<div class="text-green"><i class="fa fa-check"></i> Data berhasil diload </div>'));
		} else {
			echo json_encode(array('ok' => false, 'msg' => '<div class="text-red"><i class="fa fa-ban"></i> Gagal load data </div>'));
		}
	}

	public function ins() {
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
		if($this->ssukarela_m->ins()){
			echo json_encode(array('ok' => true, 'msg' => '<div class="text-green"><i class="fa fa-check"></i> Data berhasil diload </div>'));
		} else {
			echo json_encode(array('ok' => false, 'msg' => '<div class="text-red"><i class="fa fa-ban"></i> Gagal load data </div>'));
		}
	}

	public function create() {
		if(!isset($_POST)) {
			show_404();
		}
		if($this->ssukarela_m->create()){
			echo json_encode(array('ok' => true, 'msg' => '<div class="text-green"><i class="fa fa-check"></i> Data berhasil disimpan </div>'));
		}else
		{
			echo json_encode(array('ok' => false, 'msg' => '<div class="text-red"><i class="fa fa-ban"></i> Gagal menyimpan data, pastikan nilai lebih dari <strong>0 (NOL)</strong>. </div>'));
		}
	}

	public function update($id=null) {
		if(!isset($_POST)) {
			show_404();
		}
		if($this->ssukarela_m->update($id)) {
			echo json_encode(array('ok' => true, 'msg' => '<div class="text-green"><i class="fa fa-check"></i> Data berhasil diubah </div>'));
		} else {
			echo json_encode(array('ok' => false, 'msg' => '<div class="text-red"><i class="fa fa-ban"></i>  Maaf, Data gagal diubah, pastikan nilai lebih dari <strong>0 (NOL)</strong>. </div>'));
		}

	}
	public function delete() {
		if(!isset($_POST))	 {
			show_404();
		}
		$id = intval(addslashes($_POST['id']));
		if($this->ssukarela_m->delete($id))
		{
			echo json_encode(array('ok' => true, 'msg' => '<div class="text-green"><i class="fa fa-check"></i> Data berhasil dihapus </div>'));
		} else {
			echo json_encode(array('ok' => false, 'msg' => '<div class="text-red"><i class="fa fa-ban"></i> Maaf, Data gagal dihapus </div>'));
		}
	}
}