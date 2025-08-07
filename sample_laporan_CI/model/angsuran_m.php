<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Angsuran_m extends CI_Model {
		public function __construct() {
		parent::__construct();
	}

	function get_data_kas() {
		$this->db->select('*');
		$this->db->from('nama_kas_tbl');
		$this->db->where('aktif', 'Y');
		$this->db->where('tmpl_bayar', 'Y');
		$this->db->order_by('id', 'ASC');
		$query = $this->db->get();
		if($query->num_rows()>0){
			$out = $query->result();
			return $out;
		} else {
			return FALSE;
		}
	}
	
	function get_data_transaksi_ajax($offset, $limit, $q='', $sort, $order, $id) {
		//$id_cab = $this->session->userdata('id_cabang');
		$sql = "SELECT * FROM tbl_pinjaman_d WHERE pinjam_id=".$id."";
		if(is_array($q)) {
			if($q['kode_transaksi'] != '') {
				$q['kode_transaksi'] = str_replace('TBY', '', $q['kode_transaksi']);
				$q['kode_transaksi'] = $q['kode_transaksi'] * 1;
				$sql .=" AND id LIKE '%".$q['kode_transaksi']."%'";
			}
			if($q['tgl_dari'] != '' && $q['tgl_sampai'] != '') {
				$sql .=" AND DATE(tgl_bayar) >= '".$q['tgl_dari']."' ";
				$sql .=" AND DATE(tgl_bayar) <= '".$q['tgl_sampai']."' ";
			}
		}
		$sql .=" AND (ket_bayar = 'Angsuran') ";
		$result['count'] = $this->db->query($sql)->num_rows();
		$sql .=" ORDER BY {$sort} {$order} ";
		$sql .=" LIMIT {$offset},{$limit} ";
		$result['data'] = $this->db->query($sql)->result();
		return $result;
	}

	//panggil data pinjaman detail berdasarkan ID
	function get_data_pembayaran_by_id($id) {
		$this->db->select('*');
		$this->db->from('tbl_pinjaman_d');
		$this->db->where('id', $id);
		$query = $this->db->get();
		if($query->num_rows() > 0){
			$out = $query->result();
			return $out;
		} else {
			return FALSE;
		}
	}

	public function create() {
		//$id_cab = $this->session->userdata('id_cabang');
		$ags_ke = $this->general_m->get_record_bayar($this->input->post('pinjam_id')) + 1;
		$jumlah = str_replace(',', '', $this->input->post('jml_bayar')) * 1;
		$denda= str_replace(',', '', $this->input->post('denda_val'))*1;
		$jumlah_bayar = $jumlah + $denda;
		$data = array(			
			'tgl_bayar'		=>	$this->input->post('tgl_transaksi'),
			'pinjam_id'		=>	$this->input->post('pinjam_id'),
			'angsuran_ke'	=>	$ags_ke,
			'jumlah_bayar'	=>	str_replace(',', '', $this->input->post('angsuran')),
			'bunga'			=>	str_replace(',', '', $this->input->post('bunga')),
			'denda_rp'		=>	$denda,
			'biaya_adm'		=>	str_replace(',', '', $this->input->post('biaya_adm')),
			'ket_bayar'		=>	'Angsuran',
			'kas_id'		=>	$this->input->post('kas_id'),
			'jns_trans'		=>	'48',
			'keterangan'	=>	$this->input->post('ket'),
			'user_name'		=> 	$this->data['u_name']
		);
		///// SQL START
		$this->db->trans_start();
		$this->db->insert('tbl_pinjaman_d', $data);
		

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			// error insert
			return FALSE;
		} else {
			$this->db->trans_complete();
			return TRUE;
		}
		///// SQL END
	}

	//panggil detail  angsuran
	function get_data_angsuran($pinjam_id) {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('tbl_pinjaman_d');
		$this->db->where('pinjam_id', $pinjam_id);
		//$this->db->where('id_cabang', $id_cab);
		$this->db->order_by('tgl_bayar', 'ASC');
		$query = $this->db->get();
		if($query->num_rows()>0){
			$out = $query->result();
			return $out;
		} else {
			return FALSE;
		}
	}


	public function update($id) {
		$id_cab = $this->session->userdata('id_cabang');
		$tanggal_u = date('Y-m-d H:i');
		$this->db->where('id', $id);
		return $this->db->update('tbl_pinjaman_d',array(
			'tgl_bayar'		=> 	$this->input->post('tgl_transaksi'),
			'jumlah_bayar'	=>	str_replace(',', '', $this->input->post('angsuran')),
			'kas_id'		=>	$this->input->post('kas_id'),
			'denda_rp'		=>	str_replace(',', '', $this->input->post('denda_val')),
			'update_data'	=> 	$tanggal_u,
			'keterangan'	=>	$this->input->post('ket'),
			'user_name'		=> 	$this->data['u_name'],
			'id_cabang'		=> 	''
		));
	}
	
	public function delete($id, $master_id) {
		// cek apakah yg dihapus adalah bukan yg terakhir
		
		$this->db->select('MAX(id) AS id_akhir');
		$this->db->where('pinjam_id', $master_id);
		$qu_akhir = $this->db->get('tbl_pinjaman_d');
		$row_akhir = $qu_akhir->row();
		if($row_akhir->id_akhir != $id) {
			return false;
		} else {
			$this->db->delete('tbl_pinjaman_d', array('id' => $id));
			$this->auto_status_lunas($master_id);
		}
		
		$this->db->delete('tbl_pinjaman_d', array('id' => $id));
		if($this->auto_status_lunas($master_id)) {
			return TRUE;
		}
	}

	function auto_status_lunas($master_id) {
		$pinjam = $this->general_m->get_data_pinjam($master_id);
		$tagihan = $pinjam->lama_angsuran * $pinjam->ags_per_bulan;
		$denda = $this->general_m->get_semua_denda_by_pinjaman($master_id);
		$total_tagihan = $tagihan + $denda;
		if($total_tagihan <= 0) {
			$status = 'Lunas';} 
		else {
			$status = 'Belum';}
		$data = array('lunas' => $status);
		$this->db->where('id', $master_id);
		$this->db->update('tbl_pinjaman_h', $data);
		return TRUE;
	}

	public function import_db($data) {
		$id_cab = 0;
		if(is_array($data)) {

			$pair_arr = array();
			foreach ($data as $rows) {
				//if(trim($rows['A']) == '') { continue; }
				// per baris
				$pair = array();
				foreach ($rows as $key => $val) {
					if($key == 'A') { $pair['tgl_bayar'] = $val; }
					if($key == 'B') { $pair['pinjam_id'] = $val; }
					if($key == 'C') { $pair['angsuran_ke'] = $val; }
					if($key == 'D') { $pair['jumlah_bayar'] = $val; }
					if($key == 'E') { $pair['bunga'] = $val; }
					if($key == 'F') { $pair['denda_rp'] = $val; }
					if($key == 'G') { $pair['biaya_adm'] = $val; }
					if($key == 'H') { $pair['terlambat'] = $val; }
					if($key == 'I') { $pair['ket_bayar'] = $val; }
					if($key == 'J') { $pair['dk'] = $val; }
					if($key == 'K') { $pair['kas_id'] = $val; }
					if($key == 'L') { $pair['jns_trans'] = $val; }
					if($key == 'M') { $pair['update_data'] = $val; }
					if($key == 'N') { $pair['user_name'] = $val; }
					if($key == 'O') { $pair['keterangan'] = $val; }
					if($key == 'P') { $pair['id_cabang'] = $id_cab; }
				}
				$pair['id_cabang'] = $id_cab;
				if($pair['ket_bayar'] == 'Pelunasan'){
					$sql = "update tbl_pinjaman_h set tbl_pinjaman_h.lunas='Lunas' where tbl_pinjaman_h.id='".$pair['pinjam_id']."'";
					$this->db->query($sql);
				}
				$pair_arr[] = $pair;
			}
			//var_dump($pair_arr);
			//return 1;
			return $this->db->insert_batch('tbl_pinjaman_d', $pair_arr);
			
		} else {
			return FALSE;
		}
	}
}