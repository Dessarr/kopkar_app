<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends MY_Controller {

	public function __construct() {
		parent::__construct();
		if ($this->session->userdata('status') != "login" && $this->session->userdata('level') != "admin"){
            redirect(base_url("login"));
        }
		$this->load->helper('fungsi');
		$this->load->model('home_m');
	}	
	
	public function index() {
		$id_cab = $this->session->userdata('id_cabang');

		$this->data['judul_browser'] = 'Beranda';
		$this->data['judul_utama'] = 'Beranda';
		$this->data['judul_sub'] = 'Menu Utama';

		$this->data['anggota_all'] = $this->home_m->get_anggota_all($id_cab);
		$this->data['anggota_aktif'] = $this->home_m->get_anggota_aktif($id_cab);
		$this->data['anggota_non'] = $this->home_m->get_anggota_non($id_cab);
		$this->data['jml_simpanan'] = $this->home_m->get_jml_simpanan($id_cab);
		$this->data['jml_penarikan'] = $this->home_m->get_jml_penarikan($id_cab);
		$this->data['jml_pinjaman'] = $this->home_m->get_jml_pinjaman($id_cab);
		$this->data['jml_angsuran'] = $this->home_m->get_jml_angsuran($id_cab);
		$this->data['jml_denda'] = $this->home_m->get_jml_denda($id_cab);
		$this->data['peminjam'] = $this->home_m->get_peminjam_bln_ini($id_cab);
		$this->data['peminjam_aktif'] = $this->home_m->get_peminjam_aktif($id_cab);
		$this->data['peminjam_lunas'] = $this->home_m->get_peminjam_lunas($id_cab);
		$this->data['peminjam_belum'] = $this->home_m->get_peminjam_belum($id_cab);
		$this->data['kas_debet'] = $this->home_m->get_jml_debet($id_cab);
		$this->data['kas_kredit'] = $this->home_m->get_jml_kredit($id_cab);
		$this->data['user_aktif'] = $this->home_m->get_user_aktif($id_cab);
		$this->data['user_non'] = $this->home_m->get_user_non($id_cab);
		$this->data['isi'] = $this->load->view('home_list_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}

	public function no_akses() {
		$this->data['judul_browser'] = 'Tidak Ada Akses';
		$this->data['judul_utama'] = 'Tidak Ada Akses';
		$this->data['judul_sub'] = '';

		$this->data['isi'] = '<div class="alert alert-danger">Anda tidak memiliki Akses.</div>';
		$this->load->view('themes/layout_utama_v', $this->data);
	}

}
