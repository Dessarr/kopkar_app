<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lap_potongan_m extends CI_Model {

	public function __construct() {
		parent::__construct();
	}

	function get_transaksi_pinjaman($limit, $start) {
		//$id_cab = $this->session->userdata('id_cabang');
		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}

		$this->db->select('*');
		$this->db->from('v_rekap_det_angsuran');
		//$this->db->where('id_cabang', $id_cab);
		$this->db->where('DATE(tgl_pinjam) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_pinjam) <= ', ''.$tgl_samp.'');
		$this->db->group_by('id');
		$this->db->order_by('tgl_pinjam', 'ASC');
		$this->db->limit($limit, $start);
		$query = $this->db->get();

		if($query->num_rows()>0) {
			$out = $query->result();
			return $out;
		} else {
			return array();
		}
	}

	function lap_transaksi_pinjaman() {
		//$id_cab = $this->session->userdata('id_cabang');
		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}

		$this->db->select('*');
		$this->db->from('v_rekap_det_angsuran');
		//$this->db->where('id_cabang', $id_cab);
		$this->db->where('DATE(tgl_pinjam) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_pinjam) <= ', ''.$tgl_samp.'');
		$this->db->group_by('id');
		$this->db->order_by('tgl_pinjam', 'ASC');
		//$this->db->limit($limit, $start);
		$query = $this->db->get();

		if($query->num_rows()>0) {
			$out = $query->result();
			return $out;
		} else {
			return array();
		}
	}

	function get_jml_data_kas() {
		//$id_cab = $this->session->userdata('id_cabang');
		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_pinjam) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_pinjam) <= ', ''.$tgl_samp.'');
		//$this->db->where('id_cabang', $id_cab);
		return $this->db->count_all_results('v_hitung_pinjaman');
	}

	function get_saldo_sblm() {
		//$id_cab = $this->session->userdata('id_cabang');
		// SALDO SEBELUM NYA
		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
		}
		$this->db->select('SUM(jumlah) AS jumlah');
		$this->db->from('v_hitung_pinjaman');
		//$this->db->where('id_cabang', $id_cab);
		$this->db->where('DATE(tgl_pinjam) < ', ''.$tgl_dari.'');		
		$query_sblm = $this->db->get();
		$saldo_sblm = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$saldo_sblm = ($row_sblm->jumlah);
		}
		return $saldo_sblm;
	}

	function get_saldo_awal($limit, $start) {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('jumlah');
		$this->db->from('v_hitung_pinjaman');
		
		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_pinjam) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_pinjam) <= ', ''.$tgl_samp.'');
		//$this->db->where('id_cabang', $id_cab);
		$this->db->order_by('tgl_pinjam', 'ASC');
		$this->db->limit($start, 0);
		$query = $this->db->get();
		if($query->num_rows() > 0) {
			$res = $query->result();
			$saldo = 0;
			
			return $saldo;
		} else {
			return 0;
		}		
	}

	function get_nama_akun_id($id) {
		$this->db->select('*');
		$this->db->from('jns_akun');
		$this->db->where('id', $id);
		$query = $this->db->get();

		if($query->num_rows() > 0) {
			$out = $query->row();
			return $out;
		} else {
			$out = (object) array('nama' => '');
			return $out;
		}
	}

	function get_jml_d() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('v_hitung_pinjaman');
		
		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_pinjam) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_pinjam) <= ', ''.$tgl_samp.'');
		//$this->db->where('id_cabang', $id_cab);

		$query = $this->db->get();
		return $query->row();
	}

}