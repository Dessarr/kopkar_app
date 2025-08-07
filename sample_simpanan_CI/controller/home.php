<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//ini_set('max_execution_time', 0); 
//ini_set('memory_limit','2048M');


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
		$this->data['judul_browser'] = 'Beranda';
		$this->data['judul_utama'] = 'Beranda';
		$this->data['judul_sub'] = 'Menu Utama';

		$this->data['anggota_all'] = $this->home_m->get_anggota_all();
		$this->data['anggota_aktif'] = $this->home_m->get_anggota_aktif();
		$this->data['anggota_non'] = $this->home_m->get_anggota_non();
		//$this->data['jml_simpanan'] = $this->home_m->get_jml_simpanan();
		//$this->data['jml_penarikan'] = $this->home_m->get_jml_penarikan();
		$this->data['jml_pinjaman'] = $this->home_m->get_jml_pinjaman();
		$this->data['pinjaman_bulan_lalu'] = $this->home_m->get_jml_pinjaman_bulan_lalu();
		$this->data['pinjaman_bulan'] = $this->home_m->get_jml_pinjaman_bulan();
		$this->data['pinjaman_pokok'] = $this->home_m->get_jml_pokok_bulan();
		$this->data['nama_simpanan'] = $this->home_m->get_nama_simpanan();

		$this->data['belum_lunas'] = $this->home_m->get_belum_lunas();
		$this->data['berjangka'] = $this->home_m->get_berjangka();
		$this->data['sukarela'] = $this->home_m->get_sukarela();
		$this->data['pokok'] = $this->home_m->get_pokok();
		$this->data['wajib'] = $this->home_m->get_wajib();
		$this->data['khusus_1'] = $this->home_m->get_khusus_1();
		$this->data['khusus_2'] = $this->home_m->get_khusus_2();

		$this->data['berjangka_penerimaan'] = $this->home_m->get_berjangka_penerimaan();
		$this->data['sukarela_penerimaan'] = $this->home_m->get_sukarela_penerimaan();
		$this->data['pokok_penerimaan'] = $this->home_m->get_pokok_penerimaan();
		$this->data['wajib_penerimaan'] = $this->home_m->get_wajib_penerimaan();
		$this->data['khusus_1_penerimaan'] = $this->home_m->get_khusus_1_penerimaan();
		$this->data['khusus_2_penerimaan'] = $this->home_m->get_khusus_2_penerimaan();

		$this->data['berjangka_penarikan'] = $this->home_m->get_berjangka_penarikan();
		$this->data['sukarela_penarikan'] = $this->home_m->get_sukarela_penarikan();
		$this->data['pokok_penarikan'] = $this->home_m->get_pokok_penarikan();
		$this->data['wajib_penarikan'] = $this->home_m->get_wajib_penarikan();
		$this->data['khusus_1_penarikan'] = $this->home_m->get_khusus_1_penarikan();
		$this->data['khusus_2_penarikan'] = $this->home_m->get_khusus_2_penarikan();
		
		$this->data['jml_angsuran'] = $this->home_m->get_jml_angsuran();
		$this->data['jml_denda'] = $this->home_m->get_jml_denda();
		$this->data['peminjam'] = $this->home_m->get_peminjam_bln_ini();

		$this->data['peminjam_aktif_bulan_lalu'] = $this->home_m->get_peminjam_aktif_bulan_lalu();
		$this->data['peminjam_aktif_bulan_ini'] = $this->home_m->get_peminjam_aktif_bulan_ini();
		$this->data['peminjam_lunas'] = $this->home_m->get_peminjam_lunas();
		$this->data['peminjam_belum'] = $this->home_m->get_peminjam_belum();
		$this->data['saldo_debet_sblm'] = $this->home_m->get_saldo_debet();
		$this->data['saldo_kredit_sblm'] = $this->home_m->get_saldo_kredit();

		$this->data['kas_debet'] = $this->home_m->get_jml_debet();
		$this->data['kas_kredit'] = $this->home_m->get_jml_kredit();
		//$this->data['user_aktif'] = $this->home_m->get_user_aktif();
		//$this->data['user_non'] = $this->home_m->get_user_non();
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
