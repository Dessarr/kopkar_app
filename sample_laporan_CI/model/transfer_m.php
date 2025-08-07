<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Transfer_m extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	#panggil data kas
	function get_data_kas() {
		$this->db->select('*');
		$this->db->from('nama_kas_tbl');
		$this->db->where('aktif', 'Y');
		$this->db->where('tmpl_transfer', 'Y');
		$this->db->order_by('id', 'ASC');
		$query = $this->db->get();
		if($query->num_rows()>0){
			$out = $query->result();
			return $out;
		} else {
			return FALSE;
		}
	}


//panggil nama kas
	function get_nama_kas_id($id) {
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
	
	//panggil data simpanan untuk laporan 
	function lap_data_transfer() {
		$id_cab = $this->session->userdata('id_cabang');
		$kode_transaksi = isset($_REQUEST['kode_transaksi']) ? $_REQUEST['kode_transaksi'] : '';
		$tgl_dari = isset($_REQUEST['tgl_dari']) ? $_REQUEST['tgl_dari'] : '';
		$tgl_sampai = isset($_REQUEST['tgl_sampai']) ? $_REQUEST['tgl_sampai'] : '';
		$sql = '';
		$sql = " SELECT * FROM tbl_trans_kas WHERE akun='Transfer' and id_cabang='$id_cab' ";
		$q = array('kode_transaksi' => $kode_transaksi, 
			'tgl_dari' => $tgl_dari, 
			'tgl_sampai' => $tgl_sampai);
		if(is_array($q)) {
			if($q['kode_transaksi'] != '') {
				$q['kode_transaksi'] = str_replace('TRF', '', $q['kode_transaksi']);
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
	function get_jml_transfer() {
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('tbl_trans_kas');
		$this->db->where('akun','Transfer');
		$query = $this->db->get();
		return $query->row();
	}

	//panggil data simpanan untuk esyui
	function get_data_transaksi_ajax($id_cab, $offset, $limit, $q='', $sort, $order) {
		$sql = "SELECT * FROM tbl_trans_kas WHERE id_cabang='$id_cab' and akun='Transfer' ";
		if(is_array($q)) {
			if($q['kode_transaksi'] != '') {
				$q['kode_transaksi'] = str_replace('TRF', '', $q['kode_transaksi']);
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
		$id_cab = $this->session->userdata('id_cabang');
		if(str_replace(',', '', $this->input->post('jumlah')) <= 0) {
			return FALSE;
		}		
		$data = array(			
			'tgl_catat'		=>	$this->input->post('tgl_transaksi'),
			'jumlah'		=>	str_replace(',', '', $this->input->post('jumlah')),
			'keterangan'	=>	$this->input->post('ket'),
			'akun'			=>	'Transfer',
			'dari_kas_id'	=>	$this->input->post('dari_kas_id'),
			'untuk_kas_id'	=>	$this->input->post('untuk_kas_id'),
			'jns_trans'		=>	'110',
			'user_name'		=> 	$this->data['u_name'],
			'id_cabang'		=> 	$id_cab
			);
		return $this->db->insert('tbl_trans_kas', $data);
	}

	public function update($id)
	{
		if(str_replace(',', '', $this->input->post('jumlah')) <= 0) {
			return FALSE;
		}
		$id_cab = $this->session->userdata('id_cabang');
		$tanggal_u = date('Y-m-d H:i');
		$this->db->where('id', $id);
		return $this->db->update('tbl_trans_kas',array(
			'tgl_catat'		=>	$this->input->post('tgl_transaksi'),
			'jumlah'		=>	str_replace(',', '', $this->input->post('jumlah')),
			'keterangan'	=>	$this->input->post('ket'),
			'dari_kas_id'	=>	$this->input->post('dari_kas_id'),
			'untuk_kas_id'	=>	$this->input->post('untuk_kas_id'),
			'update_data'	=> 	$tanggal_u,
			'user_name'		=> 	$this->data['u_name'],
			'id_cabang'		=> 	$id_cab
			));
	}

	public function delete($id){
		return $this->db->delete('tbl_trans_kas', array('id' => $id)); 
	}
}