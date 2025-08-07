<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Piutang_m extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	function get_data_kas() {
		$this->db->select('*');
		$this->db->from('nama_kas_tbl');
		$this->db->where('aktif', 'Y');
		$this->db->where('tmpl_simpan', 'Y');
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
		if(is_array($data)) {
			$pair_arr = array();
			foreach ($data as $rows) {
				$pair = array();
				foreach ($rows as $key => $val) {
					if($key == 'A') { $pair['tgl_transaksi'] = $val; }
					if($key == 'B') { $pair['no_ktp'] = $val; }
					if($key == 'C') { $pair['jumlah_bayar'] = $val; }
					if($key == 'D') { $pair['jns_trans'] = $val; }
					$pair['dk'] 	= 'D';
					$pair['kas_id'] = '1';
					$pair['user_name'] = 'admin';
				}
				$pair_arr[] = $pair;
			}
			return $this->db->insert_batch('tbl_shu', $pair_arr);
		} else {
			return FALSE;
		}
	}

	//panggil data simpanan untuk laporan 
	function lap_data_simpanan() {
		//$id_cab = $this->session->userdata('id_cabang');
		$kode_transaksi = isset($_REQUEST['kode_transaksi']) ? $_REQUEST['kode_transaksi'] : '';
		$cari_simpanan = isset($_REQUEST['cari_simpanan']) ? $_REQUEST['cari_simpanan'] : '';
		$tgl_dari = isset($_REQUEST['tgl_dari']) ? $_REQUEST['tgl_dari'] : '';
		$tgl_sampai = isset($_REQUEST['tgl_sampai']) ? $_REQUEST['tgl_sampai'] : '';
		$sql = '';
		$sql = " SELECT * FROM v_shu WHERE dk='D' ";
		$q = array('kode_transaksi' => $kode_transaksi, 
			'cari_simpanan' => $cari_simpanan,
			'tgl_dari' => $tgl_dari, 
			'tgl_sampai' => $tgl_sampai);
		if(is_array($q)) {
			if($q['kode_transaksi'] != '') {
				$q['kode_transaksi'] = str_replace('TRD', '', $q['kode_transaksi']);
				$q['kode_transaksi'] = str_replace('AG', '', $q['kode_transaksi']);
				$q['kode_transaksi'] = $q['kode_transaksi'] * 1;
				$sql .=" AND (id LIKE '".$q['kode_transaksi']."' OR anggota_id LIKE '".$q['kode_transaksi']."') ";
			} else {
				
				if($q['cari_simpanan'] != '') {
					$sql .=" AND anggota_id = '".$q['cari_simpanan']."%' ";
				}
				if($q['tgl_dari'] != '' && $q['tgl_sampai'] != '') {
					$sql .=" AND DATE(tgl_transaksi) >= '".$q['tgl_dari']."' ";
					$sql .=" AND DATE(tgl_transaksi) <= '".$q['tgl_sampai']."' ";
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

	//panggil data anggota
	function get_data_anggota($id) {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('tbl_anggota');
		$this->db->where('id',$id);
		//$this->db->where('id_cabang',$id_cab);
		$query = $this->db->get();
		if($query->num_rows()>0){
			$out = $query->row();
			return $out;
		} else {
			return FALSE;
		}
	}

	//panggil data jenis simpan
	function get_jenis_simpan($id) {
		$this->db->select('*');
		$this->db->from('jns_simpan');
		$this->db->where('id',$id);
		$query = $this->db->get();
		if($query->num_rows()>0){
			$out = $query->row();
			return $out;
		} else {
			return FALSE;
		}
	}

	//hitung jumlah total simpanan
	function get_jml_simpanan() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('tbl_trans_sp');
		$this->db->where('dk','D');
		//$this->db->where('id_cabang',$id_cab);
		$query = $this->db->get();
		return $query->row();
	}

	//panggil data simpanan untuk esyui
	function get_data_transaksi_ajax($offset, $limit, $q='', $sort, $order) {
		//$id_cab = $this->session->userdata('id_cabang');
		$sql = "SELECT * FROM v_shu WHERE dk='D' ";
		if(is_array($q)) {
			if($q['kode_transaksi'] != '') {
				//$q['kode_transaksi'] = str_replace('TRD', '', $q['kode_transaksi']);
				//$q['kode_transaksi'] = str_replace('AG', '', $q['kode_transaksi']);
				$q['kode_transaksi'] = $q['kode_transaksi'];
				$sql .=" AND (nama LIKE '%".$q['kode_transaksi']."%' OR no_ktp LIKE '".$q['kode_transaksi']."') ";
			} else {
				if($q['cari_simpanan'] != '') {
					$sql .=" AND nama LIKE '%".$q['cari_simpanan']."%' ";
				}

				if($q['tgl_dari'] != '' && $q['tgl_sampai'] != '') {
					$sql .=" AND DATE(tgl_transaksi) >= '".$q['tgl_dari']."' ";
					$sql .=" AND DATE(tgl_transaksi) <= '".$q['tgl_sampai']."' ";
				}
			}
		}
		$result['count'] = $this->db->query($sql)->num_rows();
		$sql .=" ORDER BY id desc ";
		$sql .=" LIMIT {$offset},{$limit} ";
		$result['data'] = $this->db->query($sql)->result();
		return $result;
	}

	public function create() {
		if(str_replace(',', '', $this->input->post('jumlah')) <= 0) {
			return FALSE;
		}		
		$data = array(			
			'tgl_transaksi'		=>	$this->input->post('tgl_transaksi'),
			'no_ktp'			=>	$this->input->post('no_ktp'),
			'jumlah_bayar'		=>	str_replace(',', '', $this->input->post('jumlah')),
			'jns_trans'			=>	$this->input->post('jenis_id'),
			'dk'				=> 	'D',
			'kas_id'			=>	1,
			'update_data'		=> 	'',
			'user_name'			=> 	'admin'
			);
		return $this->db->insert('tbl_shu', $data);
	}

	public function update($id) {
		//$id_cab = $this->session->userdata('id_cabang');
		if(str_replace(',', '', $this->input->post('jumlah')) <= 0) {
			return FALSE;
		}
		$tanggal_u = date('Y-m-d H:i');
		$this->db->where('id', $id);
		return $this->db->update('tbl_shu',array(
			'tgl_transaksi'		=>	$this->input->post('tgl_transaksi'),
			'no_ktp'			=>	$this->input->post('no_ktp'),
			//'jenis_id'			=>	$this->input->post('jenis_id'),
			'jumlah_bayar'		=>	str_replace(',', '', $this->input->post('jumlah')),
			'jns_trans'			=>	$this->input->post('jenis_id'),
			'dk'				=> 	'D',
			'kas_id'			=>	1,
			'update_data'		=> 	$tanggal_u,
			'user_name'			=> 	'admin'
			//'nama_penyetor'		=> 	$this->input->post('nama_penyetor'),
			//'no_identitas'		=> 	$this->input->post('no_identitas'),
			//'alamat'			=> 	$this->input->post('alamat')
			));
	}

	public function delete($id) {
		return $this->db->delete('tbl_shu', array('id' => $id)); 
	}
}