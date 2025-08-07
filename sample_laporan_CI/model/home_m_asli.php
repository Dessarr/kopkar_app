<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home_m extends CI_Model {
	
	public function __construct() {
		parent::__construct();
	}

	//hitung jumlah anggota total
	function get_anggota_all() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('tbl_anggota');
		$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->num_rows();
	}

	//hitung jumlah anggota aktif
	function get_anggota_aktif() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('tbl_anggota');
		$this->db->where('aktif','Y');
		$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->num_rows();
	}

	//hitung jumlah anggota tdk aktif
	function get_anggota_non() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('tbl_anggota');
		$this->db->where('aktif','N');
		$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->num_rows();
	}

	
	//menghitung jumlah simpanan
	function get_jml_simpanan() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('tbl_trans_sp');
		$this->db->where('dk','D');
		$this->db->where('id_cabang', $id_cab);

		//$thn = date('Y');			
		//$bln = date('m');			
		//$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$bln."' ";
		//$this->db->where($where);

		$query = $this->db->get();
		return $query->row();
	}

	//menghitung jumlah penarikan
	function get_jml_penarikan() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('tbl_trans_sp');
		$this->db->where('dk','K');
		$this->db->where('id_cabang', $id_cab);

		//$thn = date('Y');			
		//$bln = date('m');			
		//$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$bln."' ";
		//$this->db->where($where);

		$query = $this->db->get();
		return $query->row();
	}

	//hitung jumlah peminjam aktif
	function get_peminjam_aktif() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('v_hitung_pinjaman');
		
		//$tgl_dari = date('Y') . '-01-01';
		//$tgl_samp = date('Y') . '-12-31';
		
		$this->db->where('id_cabang', $id_cab);
		//$this->db->where('DATE(tgl_pinjam) >= ', ''.$tgl_dari.'');
		//$this->db->where('DATE(tgl_pinjam) <= ', ''.$tgl_samp.'');

		$query = $this->db->get();
		return $query->num_rows();
	}

	//hitung jumlah peminjam lunas
	function get_peminjam_lunas() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('v_hitung_pinjaman');
		$this->db->where('lunas','Lunas');
		$this->db->where('id_cabang', $id_cab);

		//$tgl_dari = date('Y') . '-01-01';
		//$tgl_samp = date('Y') . '-12-31';
		
		//$this->db->where('DATE(tgl_pinjam) >= ', ''.$tgl_dari.'');
		//$this->db->where('DATE(tgl_pinjam) <= ', ''.$tgl_samp.'');

		$query = $this->db->get();
		return $query->num_rows();
	}

	//hitung jumlah peminjam belum lunas
	function get_peminjam_belum() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('v_hitung_pinjaman');
		$this->db->where('lunas','Belum');
		$this->db->where('id_cabang', $id_cab);

		//$tgl_dari = date('Y') . '-01-01';
		//$tgl_samp = date('Y') . '-12-31';
		
		//$this->db->where('DATE(tgl_pinjam) >= ', ''.$tgl_dari.'');
		//$this->db->where('DATE(tgl_pinjam) <= ', ''.$tgl_samp.'');

		$query = $this->db->get();
		return $query->num_rows();
	}

	//menghitung jumlah pinjaman Rp
	function get_jml_pinjaman() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(tagihan) AS jml_total');
		$this->db->from('v_hitung_pinjaman');
		$this->db->where('id_cabang', $id_cab);

		//$tgl_dari = date('Y') . '-01-01';
		//$tgl_samp = date('Y') . '-12-31';
		
		//$this->db->where('DATE(tgl_pinjam) >= ', ''.$tgl_dari.'');
		//$this->db->where('DATE(tgl_pinjam) <= ', ''.$tgl_samp.'');

		$query = $this->db->get();
		return $query->row();
	}

	//menghitung jumlah angsuran
	function get_jml_angsuran() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah_bayar) AS jml_total');
		$this->db->from('v_rekap_angsuran');
		$this->db->where('id_cabang', $id_cab);

		//$tgl_dari = date('Y') . '-01-01';
		//$tgl_samp = date('Y') . '-12-31';
		
		//$this->db->where('DATE(tgl_bayar) >= ', ''.$tgl_dari.'');
		//$this->db->where('DATE(tgl_bayar) <= ', ''.$tgl_samp.'');

		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_denda() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(denda_rp) AS total_denda');
		$this->db->from('tbl_pinjaman_d');
		$this->db->where('id_cabang', $id_cab);

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_bayar) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_bayar) <= ', ''.$tgl_samp.'');

		$query = $this->db->get();
		return $query->row();
	}

	function get_peminjam_bln_ini() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('v_hitung_pinjaman');
		//$this->db->where('lunas','Belum');
		$this->db->where('id_cabang', $id_cab);

		//$thn = date('Y');			
		//$bln = date('m');			
		//$where = "YEAR(tgl_pinjam) = '".$thn."' AND  MONTH(tgl_pinjam) = '".$bln."' ";
		//$this->db->where($where);

		$query = $this->db->get();
		return $query->num_rows();
	}

	//menghitung jumlah kas debet
	function get_jml_debet() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(debet) AS jml_total');
		$this->db->from('v_transaksi');
		$this->db->where('id_cabang', $id_cab);
		//$thn = date('Y');			
		//$bln = date('m');			
		//$where = "YEAR(tgl) = '".$thn."' AND  MONTH(tgl) = '".$bln."' ";
		//$this->db->where($where);
		$query = $this->db->get();
		return $query->row();
	}

	//menghitung jumlah kas kredit
	function get_jml_kredit() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(kredit) AS jml_total');
		$this->db->from('v_transaksi');
		$this->db->where('id_cabang', $id_cab);
		//$thn = date('Y');			
		//$bln = date('m');			
		//$where = "YEAR(tgl) = '".$thn."' AND  MONTH(tgl) = '".$bln."' ";
		//$this->db->where($where);
		$query = $this->db->get();
		return $query->row();
	}

	//hitung jumlah user aktif
	function get_user_aktif() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('tbl_user');
		$this->db->where('aktif','Y');
		$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->num_rows();
	}

	//hitung jumlah anggota tdk aktif
	function get_user_non() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('tbl_user');
		$this->db->where('aktif','N');
		$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->num_rows();
	}
}