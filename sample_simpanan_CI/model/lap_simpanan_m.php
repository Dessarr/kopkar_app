<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lap_simpanan_m extends CI_Model {

	public function __construct() {
		parent::__construct();
	}

	//panggil data jenis kas untuk laporan
	function get_nama_kas() {
		$id = array('31','32','40','41','51','52');
		$this->db->select('*');
		$this->db->from('jns_simpan');
		$this->db->where('tampil','Y');
		$this->db->where_in('id',$id);
		$query = $this->db->get();
		if($query->num_rows()>0){
			$out = $query->result();
			return $out;
		} else {
			return array();
		}
	}

	//panggil data jenis kas untuk laporan
	function get_transaksi_kas($kas_id) {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('v_rekap_simpanan');
		
		if(isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}

		$where = "(YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$bln."')";
		$this->db->where($where);
		//$this->db->where('id_cabang', $id_cab);
		$this->db->where('jenis_id', $kas_id);
		$this->db->group_by('id');
		//$this->db->order_by('tgl_transaksi', 'ASC');
		$query = $this->db->get();

		if($query->num_rows()>0) {
			$out = $query->result();
			return $out;
		} else {
			return array();
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
		$this->db->select('SUM(Debet) AS jml_total');
		$this->db->from('v_rekap_simpanan');
		
		if(isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}
		$where = "(YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$bln."')";
		$this->db->where($where);
		//$this->db->where('id_cabang', $id_cab);

		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_k() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(Kredit) AS jml_total');
		$this->db->from('v_rekap_simpanan');
		
		if(isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}
		$where = "(YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$bln."')";
		$this->db->where($where);
		//$this->db->where('id_cabang', $id_cab);

		$query = $this->db->get();
		return $query->row();
	}

}