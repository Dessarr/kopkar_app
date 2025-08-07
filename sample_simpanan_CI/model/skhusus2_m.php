<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Skhusus2_m extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	#panggil data kas
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
		//id_cab = $this->session->userdata('id_cabang');
		if(is_array($data)) {

			$pair_arr = array();
			foreach ($data as $rows) {
				//if(trim($rows['A']) == '') { continue; }
				// per baris
				$pair = array();
				foreach ($rows as $key => $val) {
					if($key == 'A') { $pair['tgl_transaksi'] = $val; }
					if($key == 'B') { $pair['no_ktp'] = $val; }
					//if($key == 'C') { $pair['anggota_id'] = ''; }
					if($key == 'C') { $pair['jenis_id'] = $val; }
					if($key == 'D') { $pair['jumlah'] = $val; }
					if($key == 'E') { $pair['keterangan'] = $val; }
					if($key == 'F') { $pair['akun'] = 'Tagihan'; }
					if($key == 'G') { $pair['dk'] = 'D'; }
					if($key == 'H') { $pair['kas_id'] = 1; }
					if($key == 'I') { $pair['update_data'] = ''; }
					if($key == 'J') { $pair['user_name'] = 'admin'; }
				}
				//$pair['id_cabang'] = $id_cab;
				$pair_arr[] = $pair;
			}
			//var_dump($pair_arr);
			//return 1;
			return $this->db->insert_batch('tbl_trans_tagihan', $pair_arr);
		} else {
			return FALSE;
		}
	}

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

	function get_jml_simpanan() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('tbl_trans_sp');
		$this->db->where('dk','D');
		//$this->db->where('id_cabang',$id_cab);
		$query = $this->db->get();
		return $query->row();
	}

	function get_data_transaksi_ajax($offset, $limit, $q='', $sort, $order) {
		$sql = "SELECT * FROM v_skhusus2 where jns_simpan = 'Simpanan Khusus II' ";
		if(is_array($q)) {
			if($q['kode_transaksi'] != '') {
				
				$sql .=" AND (nama LIKE '%".$q['kode_transaksi']."%' OR anggota_id LIKE '".$q['kode_transaksi']."') ";
			} else {
				if($q['cari_skhusus2'] != '') {
					$sql .=" AND jenis_id = '".$q['cari_skhusus2']."%' ";
				}

				if($q['tgl_dari'] != '' && $q['tgl_sampai'] != '') {
					$sql .=" AND DATE(tgl_transaksi) >= '".$q['tgl_dari']."' ";
					$sql .=" AND DATE(tgl_transaksi) <= '".$q['tgl_sampai']."' ";
				}
			}
		}
		$result['count'] = $this->db->query($sql)->num_rows();
		$sql .=" ORDER BY nama asc ";
		$sql .=" LIMIT {$offset},{$limit} ";
		$result['data'] = $this->db->query($sql)->result();
		return $result;
	}

	public function view() {
		if(isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');			
			$bln = date('m');	
		}
		//$periode = $this->input->post('periode');
		$view = $this->db->query("SELECT * FROM v_skhusus2 where YEAR(tgl_transaksi) = '$thn' and MONTH(tgl_transaksi) = '$bln'");
        return $view;
	}

	public function load() {
		if(isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');			
			$bln = date('m');	
		}
		$this->db->query("DELETE FROM `tbl_trans_tagihan_khusus2_temp` WHERE YEAR(tgl_transaksi) = '$thn' and MONTH(tgl_transaksi) = '$bln'");
		
		$load = $this->db->query("INSERT INTO tbl_trans_tagihan_khusus2_temp (tgl_transaksi, no_ktp, anggota_id, jenis_id, jumlah, keterangan, akun, dk) 
			SELECT '$thn-$bln-01', a.no_ktp, a.id, '52', a.simpanan_khusus_2, '', 'Tagihan', 'D'
			FROM tbl_anggota a 
			WHERE a.aktif='Y' AND a.simpanan_khusus_2 <> 0");
        return $load;
	}

	public function del() {
		if(isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');			
			$bln = date('m');	
		}
		$this->db->query("DELETE FROM `tbl_trans_tagihan_khusus2_temp` WHERE YEAR(tgl_transaksi) = '$thn' and MONTH(tgl_transaksi) = '$bln'");
	}

	public function ins() {
		if(isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');			
			$bln = date('m');	
		}
		$this->db->query("DELETE FROM tbl_trans_tagihan WHERE YEAR(tgl_transaksi) = '$thn' and MONTH(tgl_transaksi) = '$bln' and jenis_id = '52'");
		$this->db->query("INSERT INTO tbl_trans_tagihan (tgl_transaksi, no_ktp, anggota_id, jenis_id, jumlah, keterangan, akun, dk) 
			SELECT tgl_transaksi, no_ktp, anggota_id, jenis_id, jumlah, keterangan, akun, dk 
			FROM tbl_trans_tagihan_khusus2_temp 
			WHERE YEAR(tgl_transaksi) = '$thn' and MONTH(tgl_transaksi) = '$bln'");

		$load = $this->db->query("DELETE FROM `tbl_trans_tagihan_khusus2_temp` WHERE YEAR(tgl_transaksi) = '$thn' and MONTH(tgl_transaksi) = '$bln'");
        return $load;
	}

	public function create() {
		//$id_cab = $this->session->userdata('id_cabang');
		if(str_replace(',', '', $this->input->post('jumlah')) <= 0) {
			return FALSE;
		}
		$tanggal_u = date('Y-m-d H:i');		
		$data = array(			
			'tgl_transaksi'		=>	$this->input->post('tgl_transaksi'),
			'no_ktp'			=>	$this->input->post('no_ktp'),
			'anggota_id'		=>	$this->input->post('anggota_id'),
			'jenis_id'			=>	52,
			'jumlah'			=>	str_replace(',', '', $this->input->post('jumlah')),
			'keterangan'		=> 	$this->input->post('ket'),
			'akun'				=>	'Tagihan',
			'dk'				=>	'D',
			'kas_id'			=>	'1',
			'update_data'		=>	$tanggal_u,
			'user_name'			=> 	'admin'
			);
		return $this->db->insert('tbl_trans_tagihan_khusus2_temp', $data);
	}

	public function update($id) {
		if(str_replace(',', '', $this->input->post('jumlah')) <= 0) {
			return FALSE;
		}
		$tanggal_u = date('Y-m-d H:i');
		$this->db->where('id', $id);
		return $this->db->update('tbl_trans_tagihan_khusus2_temp',array(
			'tgl_transaksi'		=>	$this->input->post('tgl_transaksi'),
			'no_ktp'			=>	$this->input->post('no_ktp'),
			'jenis_id'			=>	52,
			'jumlah'			=>	str_replace(',', '', $this->input->post('jumlah')),
			'keterangan'		=> 	$this->input->post('ket'),
			'kas_id'			=>	'1',
			'update_data'		=> 	$tanggal_u,
			'user_name'			=> 	'admin'
			));
	}

	public function delete($id) {
		return $this->db->delete('tbl_trans_tagihan_khusus2_temp', array('id' => $id)); 
	}
}