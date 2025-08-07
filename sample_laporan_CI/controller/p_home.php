<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class P_home extends MY_Controller {

	public function __construct() {
		parent::__construct();
		if ($this->session->userdata('status') != "login" && $this->session->userdata('level') != "pusat"){
            redirect(base_url("login"));
        }

		$this->load->helper('fungsi');
		$this->load->model('p_home_m');
	}	
	
	public function index() {
		//$id_cab = $this->session->userdata('id_cabang');

		$this->data['judul_browser'] = 'Beranda';
		$this->data['judul_utama'] = 'Beranda';
		$this->data['judul_sub'] = 'Menu Utama';

		$this->data['anggota_all'] = $this->p_home_m->get_anggota_all();
		$this->data['anggota_aktif'] = $this->p_home_m->get_anggota_aktif();
		$this->data['anggota_non'] = $this->p_home_m->get_anggota_non();
		$this->data['jml_simpanan'] = $this->p_home_m->get_jml_simpanan();
		$this->data['jml_penarikan'] = $this->p_home_m->get_jml_penarikan();
		$this->data['jml_pinjaman'] = $this->p_home_m->get_jml_pinjaman();
		$this->data['jml_angsuran'] = $this->p_home_m->get_jml_angsuran();
		$this->data['jml_denda'] = $this->p_home_m->get_jml_denda();
		$this->data['peminjam'] = $this->p_home_m->get_peminjam_bln_ini();
		$this->data['peminjam_aktif'] = $this->p_home_m->get_peminjam_aktif();
		$this->data['peminjam_lunas'] = $this->p_home_m->get_peminjam_lunas();
		$this->data['peminjam_belum'] = $this->p_home_m->get_peminjam_belum();
		$this->data['kas_debet'] = $this->p_home_m->get_jml_debet();
		$this->data['kas_kredit'] = $this->p_home_m->get_jml_kredit();
		$this->data['user_aktif'] = $this->p_home_m->get_user_aktif();
		$this->data['user_non'] = $this->p_home_m->get_user_non();
		
		$this->data['isi'] = $this->load->view('p_home_list_v', $this->data, TRUE);
		$this->load->view('themes/p_layout_utama_v', $this->data);
	}

	public function no_akses() {
		$this->data['judul_browser'] = 'Tidak Ada Akses';
		$this->data['judul_utama'] = 'Tidak Ada Akses';
		$this->data['judul_sub'] = '';

		$this->data['isi'] = '<div class="alert alert-danger">Anda tidak memiliki Akses.</div>';
		$this->load->view('themes/p_layout_utama_v', $this->data);
	}

}
