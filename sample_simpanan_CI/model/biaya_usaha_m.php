<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Biaya_usaha_m extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	#panggil data kas
	function get_data_kas() {
		$this->db->select('*');
		$this->db->from('nama_kas_tbl');
		$this->db->where('aktif', 'Y');
		$this->db->where('tmpl_pemasukan', 'Y');
		$this->db->order_by('id', 'ASC');
		$query = $this->db->get();
		if($query->num_rows()>0){
			$out = $query->result();
			return $out;
		} else {
			return array();
		}
	}

//panggil data jenis kas
	function get_jenis_kas($id) {
		$this->db->select('*');
		$this->db->from('nama_kas_tbl');
		$this->db->where('id',$id);
		$query = $this->db->get();
		if($query->num_rows()>0){
			$out = $query->row();
			return $out;
		} else {
			return FALSE;
		}
	}

	#panggil data akun
	function get_data_akun() {
		$id_akun = array('122','123','124','152');
		$this->db->select('*');
		$this->db->from('jns_akun');
		$this->db->where('aktif', 'Y');
		//$this->db->like('jns_trans', 'Penjualan');
		$this->db->where_in('id', $id_akun);
		$this->db->order_by('id', 'ASC');
		$query = $this->db->get();
		if($query->num_rows()>0){
			$out = $query->result();
			return $out;
		} else {
			return array();
		}
	}

	//panggil data jenis kas
	function get_jenis_akun($id) {
		$this->db->select('*');
		$this->db->from('jns_akun');
		$this->db->where('id',$id);
		$query = $this->db->get();

		if($query->num_rows()>0){
			$out = $query->row();
			return $out;
		} else {
			return FALSE;
		}
	}
	
	//panggil data simpanan untuk laporan 
	function lap_data_biaya_usaha() {
		//$id_cab = $this->session->userdata('id_cabang');
		$kode_transaksi = isset($_REQUEST['kode_transaksi']) ? $_REQUEST['kode_transaksi'] : '';
		$tgl_dari = isset($_REQUEST['tgl_dari']) ? $_REQUEST['tgl_dari'] : '';
		$tgl_sampai = isset($_REQUEST['tgl_sampai']) ? $_REQUEST['tgl_sampai'] : '';
		$sql = '';
		$sql = " SELECT * FROM tbl_trans_kas WHERE jns_trans in ('122','123','124','152') ";
		$q = array('kode_transaksi' => $kode_transaksi, 
			'tgl_dari' => $tgl_dari, 
			'tgl_sampai' => $tgl_sampai);
		if(is_array($q)) {
			if($q['kode_transaksi'] != '') {
				$q['kode_transaksi'] = str_replace('TKD', '', $q['kode_transaksi']);
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

	//panggil data simpanan untuk esyui
	function get_data_transaksi_ajax($offset, $limit, $q='', $sort, $order) {

		$sql = "SELECT * FROM tbl_trans_kas WHERE jns_trans in ('122','123','124','152') ";
		if(is_array($q)) {
			if($q['kode_transaksi'] != '') {
				$q['kode_transaksi'] = str_replace('TKD', '', $q['kode_transaksi']);
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
			'tgl_catat'		=>	$this->input->post('tgl_transaksi'),
			'jumlah'		=>	str_replace(',', '', $this->input->post('jumlah')),
			'keterangan'	=>	$this->input->post('ket'),
			'dk'			=>	'K',
			'akun'			=>	'Pengeluaran',
			'dari_kas_id'	=>	$this->input->post('kas_id'),
			'jns_trans'		=>	$this->input->post('akun_id'),
			'no_polisi'		=> 	'',
			'user_name'		=> 	$this->data['u_name']
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
			'tgl_catat'			=>	$this->input->post('tgl_transaksi'),
			'jumlah'			=>	str_replace(',', '', $this->input->post('jumlah')),
			'keterangan'		=>	$this->input->post('ket'),
			'dari_kas_id'		=>	$this->input->post('kas_id'),
			'jns_trans'			=>	$this->input->post('akun_id'),
			'no_polisi'			=> 	'',
			'update_data'		=> 	$tanggal_u,
			'user_name'			=> 	$this->data['u_name']
			));
	}

	public function delete($id)
	{
		return $this->db->delete('tbl_trans_kas', array('id' => $id)); 
	}

}