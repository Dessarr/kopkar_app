<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pengeluaran_angkutan_m extends CI_Model {

	public function __construct() {
		parent::__construct();
	}

	#panggil data kas
	function get_data_kas() {
		$this->db->select('*');
		$this->db->from('nama_kas_tbl');
		$this->db->where('aktif', 'Y');
		$this->db->where('tmpl_pengeluaran', 'Y');
		$this->db->order_by('id', 'ASC');
		$query = $this->db->get();
		if($query->num_rows()>0){
			$out = $query->result();
			return $out;
		} else {
			return FALSE;
		}
	}

	public function import_db($data) {
		//$id_cab = $this->session->userdata('id_cabang');
		if(is_array($data)) {

			$pair_arr = array();
			foreach ($data as $rows) {
				//if(trim($rows['A']) == '') { continue; }
				// per baris
				$pair = array();
				foreach ($rows as $key => $val) {
					if($key == 'A') { $pair['tgl_catat'] = $val; }
					if($key == 'B') { $pair['jumlah'] = $val; }
					if($key == 'C') { $pair['keterangan'] = $val; }
					if($key == 'D') { $pair['akun'] = $val; }
					if($key == 'E') { $pair['dari_kas_id'] = $val; }
					if($key == 'F') { $pair['untuk_kas_id'] = $val; }
					if($key == 'G') { $pair['jns_trans'] = $val; }
					if($key == 'H') { $pair['dk'] = $val; }
					if($key == 'I') { $pair['update_data'] = $val; }
					if($key == 'J') { $pair['user_name'] = 'admin'; }
				}
				//$pair['id_cabang'] = $id_cab;
				$pair_arr[] = $pair;
			}
			//var_dump($pair_arr);
			//return 1;
			return $this->db->insert_batch('tbl_trans_kas', $pair_arr);
		} else {
			return FALSE;
		}
	}

	#panggil data akun
	function get_data_akun() {
		$this->db->select('*');
		$this->db->from('jns_akun');
		$this->db->where('aktif', 'Y');
		$this->db->where('pengeluaran', 'Y');
		$this->db->order_by('id', 'ASC');
		$query = $this->db->get();
		if($query->num_rows()>0){
			$out = $query->result();
			return $out;
		} else {
			return FALSE;
		}
	}

	//panggil data simpanan untuk laporan 
	function lap_data_pengeluaran() {
		//$id_cab = $this->session->userdata('id_cabang');
		$kode_transaksi = isset($_REQUEST['kode_transaksi']) ? $_REQUEST['kode_transaksi'] : '';
		$tgl_dari = isset($_REQUEST['tgl_dari']) ? $_REQUEST['tgl_dari'] : '';
		$tgl_sampai = isset($_REQUEST['tgl_sampai']) ? $_REQUEST['tgl_sampai'] : '';
		$sql = '';
		$sql = "SELECT * FROM tbl_trans_kas WHERE akun='Pengeluaran' AND (jns_trans=55 or jns_trans=56 or jns_trans=57 or jns_trans=58 or jns_trans=59 or jns_trans=60 or jns_trans=61 or jns_trans=62 or jns_trans=63 or jns_trans=64 or jns_trans=65 or jns_trans=66 or jns_trans=67 or jns_trans=68 or jns_trans=69) ";
		$q = array('kode_transaksi' => $kode_transaksi, 
			'tgl_dari' => $tgl_dari, 
			'tgl_sampai' => $tgl_sampai);
		if(is_array($q)) {
			if($q['kode_transaksi'] != '') {
				$q['kode_transaksi'] = str_replace('TKK', '', $q['kode_transaksi']);
				$q['kode_transaksi'] = $q['kode_transaksi'] * 1;
				$sql .=" AND id LIKE '".$q['kode_transaksi']."' ";
			} else {			
				if($q['tgl_dari'] != '' && $q['tgl_sampai'] != '') {
					$sql .=" AND DATE(tgl_catat) >= '".$q['tgl_dari']."' ";
					$sql .=" AND DATE(tgl_catat) <= '".$q['tgl_sampai']."' ";
				}
			}
		}
		$query = $this->db->query($sql);
		if($query->num_rows() > 0) {
			$out = $query->result();
			return $out;
		} else {
			return FALSE;
		}
	}

	//hitung jumlah total 
	function get_jml_pengeluaran() {
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('tbl_trans_kas');
		$this->db->where('akun','Pengeluaran');
		$query = $this->db->get();
		return $query->row();
	}

	//panggil data simpanan untuk esyui
	function get_data_transaksi_ajax($offset, $limit, $q='', $sort, $order) {
		$sql = "SELECT * FROM tbl_trans_kas WHERE akun='Pengeluaran' AND (jns_trans=55 or jns_trans=56 or jns_trans=57 or jns_trans=58 or jns_trans=59 or jns_trans=60 or jns_trans=61 or jns_trans=62 or jns_trans=63 or jns_trans=64 or jns_trans=65 or jns_trans=66 or jns_trans=67 or jns_trans=68 or jns_trans=69) ";
		if(is_array($q)) {
			if($q['kode_transaksi'] != '') {
				$q['kode_transaksi'] = str_replace('TKK', '', $q['kode_transaksi']);
				$q['kode_transaksi'] = $q['kode_transaksi'] * 1;
				$sql .=" AND id LIKE '".$q['kode_transaksi']."' ";
			} else {
				if($q['tgl_dari'] != '' && $q['tgl_sampai'] != '') {
					$sql .=" AND DATE(tgl_catat) >= '".$q['tgl_dari']."' ";
					$sql .=" AND DATE(tgl_catat) <= '".$q['tgl_sampai']."' ";
				}
			}
		}
		$result['count'] = $this->db->query($sql)->num_rows();
		$sql .=" ORDER BY {$sort} {$order} ";
		$sql .=" LIMIT {$offset},{$limit} ";
		$result['data'] = $this->db->query($sql)->result();
		return $result;
	}

	public function create() {
		//$id_cab = $this->session->userdata('id_cabang');
		if(str_replace(',', '', $this->input->post('jumlah')) <= 0) {
			return FALSE;
		}		
		$data = array(			
			'tgl_catat'				=>	$this->input->post('tgl_transaksi'),
			'jumlah'					=>	str_replace(',', '', $this->input->post('jumlah')),
			'keterangan'			=>	$this->input->post('ket'),
			'dk'						=>	'K',
			'akun'					=>	'Pengeluaran',
			'dari_kas_id'			=>	$this->input->post('kas_id'),
			'jns_trans'				=>	$this->input->post('akun_id'),
			'user_name'				=> $this->data['u_name']
			);
		return $this->db->insert('tbl_trans_kas', $data);
	}

	public function update($id)
	{
		if(str_replace(',', '', $this->input->post('jumlah')) <= 0) {
			return FALSE;
		}
		//$id_cab = $this->session->userdata('id_cabang');
		$tanggal_u = date('Y-m-d H:i');
		$this->db->where('id', $id);
		//$this->db->where('id_cabang', $id_cab);
		return $this->db->update('tbl_trans_kas',array(
			'tgl_catat'				=>	$this->input->post('tgl_transaksi'),
			'jumlah'				=>	str_replace(',', '', $this->input->post('jumlah')),
			'keterangan'			=>	$this->input->post('ket'),
			'dari_kas_id'			=>	$this->input->post('kas_id'),
			'jns_trans'				=>	$this->input->post('akun_id'),
			'update_data'			=> $tanggal_u,
			'user_name'				=> $this->data['u_name']
			));
	}

	public function delete($id){
		return $this->db->delete('tbl_trans_kas', array('id' => $id)); 
	}
}