<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

class Bayar_upload_m extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	#panggil data kas
	function get_data_kas()
	{
		$this->db->select('*');
		$this->db->from('nama_kas_tbl');
		$this->db->where('aktif', 'Y');
		$this->db->where('tmpl_simpan', 'Y');
		$this->db->order_by('id', 'ASC');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$out = $query->result();
			return $out;
		} else {
			return FALSE;
		}
	}

	public function import_db($data, $tanggal)
	{
		if (is_array($data)) {
			$pair_arr = array();
			foreach ($data as $rows) {
				$pair = array();
				foreach ($rows as $key => $val) {
					$tanggal = $this->input->post('tanggal');
					if ($key == 'A') {
						$pair['tgl_transaksi'] = $tanggal;
					}
					if ($key == 'B') {
						$pair['no_ktp'] = $val;
					}
					if ($key == 'C') {
						$pair['jumlah'] = $val;
					}
				}
				$pair_arr[] = $pair;
			}
			return $this->db->insert_batch('tbl_trans_sp_temp', $pair_arr);
		} else {
			return FALSE;
		}
	}

	//panggil data simpanan untuk laporan 
	function lap_data_simpanan()
	{
		$kode_transaksi = isset($_REQUEST['kode_transaksi']) ? $_REQUEST['kode_transaksi'] : '';
		$cari_bayar_upload = isset($_REQUEST['cari_bayar_upload']) ? $_REQUEST['cari_bayar_upload'] : '';
		$tgl_dari = isset($_REQUEST['tgl_dari']) ? $_REQUEST['tgl_dari'] : '';
		$tgl_sampai = isset($_REQUEST['tgl_sampai']) ? $_REQUEST['tgl_sampai'] : '';
		$sql = '';
		$sql = " SELECT * FROM v_trans_sp WHERE dk='D' and jenis_id in (8,31,32,40,41,51,52) ";
		$q = array(
			'kode_transaksi' => $kode_transaksi,
			'cari_bayar_upload' => $cari_bayar_upload,
			'tgl_dari' => $tgl_dari,
			'tgl_sampai' => $tgl_sampai
		);
		if (is_array($q)) {
			if ($q['kode_transaksi'] != '') {
				//$q['kode_transaksi'] = str_replace('TRD', '', $q['kode_transaksi']);
				//$q['kode_transaksi'] = str_replace('AG', '', $q['kode_transaksi']);
				$q['kode_transaksi'] = $q['kode_transaksi'];
				$sql .= " AND (nama LIKE '%" . $q['kode_transaksi'] . "%' OR no_ktp LIKE '" . $q['kode_transaksi'] . "') ";
			} else {

				if ($q['cari_bayar_upload'] != '') {
					$sql .= " AND anggota_id = '" . $q['cari_bayar_upload'] . "%' ";
				}
				if ($q['tgl_dari'] != '' && $q['tgl_sampai'] != '') {
					$sql .= " AND DATE(tgl_transaksi) >= '" . $q['tgl_dari'] . "' ";
					$sql .= " AND DATE(tgl_transaksi) <= '" . $q['tgl_sampai'] . "' ";
				}
			}
		}
		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$out = $query->result();
			return $out;
		} else {
			return FALSE;
		}
	}

	//panggil data anggota
	function get_data_anggota($id)
	{
		$this->db->select('*');
		$this->db->from('tbl_anggota');
		$this->db->where('id', $id);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$out = $query->row();
			return $out;
		} else {
			return FALSE;
		}
	}

	//panggil data jenis simpan
	function get_jenis_simpan($id)
	{
		$this->db->select('*');
		$this->db->from('jns_simpan');
		$this->db->where('id', $id);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$out = $query->row();
			return $out;
		} else {
			return FALSE;
		}
	}

	//hitung jumlah total simpanan
	function get_jml_simpanan()
	{
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('tbl_trans_sp');
		$this->db->where('dk', 'D');
		$query = $this->db->get();
		return $query->row();
	}

	//panggil data simpanan untuk esyui
	function get_data_transaksi_ajax($offset, $limit, $q = '', $sort, $order)
	{
		$sql = "SELECT * FROM tbl_trans_sp_bayar_temp";
		if (is_array($q)) {
			if ($q['kode_transaksi'] != '') {
				$q['kode_transaksi'] = $q['kode_transaksi'];
				$sql .= " AND (nama LIKE '%" . $q['kode_transaksi'] . "%' OR no_ktp LIKE '" . $q['kode_transaksi'] . "') ";
			} else {

				if ($q['tgl_dari'] != '' && $q['tgl_sampai'] != '') {
					$sql .= " AND DATE(tgl_transaksi) >= '" . $q['tgl_dari'] . "' ";
					$sql .= " AND DATE(tgl_transaksi) <= '" . $q['tgl_sampai'] . "' ";
				}
			}
		}
		$result['count'] = $this->db->query($sql)->num_rows();
		$sql .= " ORDER BY id desc ";
		$sql .= " LIMIT {$offset},{$limit} ";
		$result['data'] = $this->db->query($sql)->result();
		return $result;
	}

	public function create()
	{
		if (str_replace(',', '', $this->input->post('jumlah')) <= 0) {
			return FALSE;
		}
		$data = array(
			'tgl_transaksi'		=>	$this->input->post('tgl_transaksi'),
			'no_ktp'			=>	$this->input->post('no_ktp'),
			'anggota_id'		=>	$this->input->post('anggota_id'),
			'jumlah'			=>	str_replace(',', '', $this->input->post('jumlah')),
			'keterangan'		=> 	$this->input->post('ket')
		);
		return $this->db->insert('tbl_trans_sp_bayar_temp', $data);
	}

	public function update($id)
	{
		if (str_replace(',', '', $this->input->post('jumlah')) <= 0) {
			return FALSE;
		}
		$tanggal_u = date('Y-m-d H:i');
		$this->db->where('id', $id);
		return $this->db->update('tbl_trans_sp_bayar_temp', array(
			'tgl_transaksi'		=>	$this->input->post('tgl_transaksi'),
			'no_ktp'			=>	$this->input->post('no_ktp'),
			'jumlah'			=>	str_replace(',', '', $this->input->post('jumlah')),
			'keterangan'		=> 	$this->input->post('ket')
		));
	}

	public function delete($id)
	{
		return $this->db->delete('tbl_trans_sp_bayar_temp', array('id' => $id));
	}

	public function del()
	{
		if (isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}
		$this->db->query("DELETE FROM `tbl_trans_sp_bayar_temp` WHERE YEAR(tgl_transaksi) = '$thn' and MONTH(tgl_transaksi) = '$bln'");
	}

	public function ins()
	{
		if (isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}

		$load = $this->db->query("INSERT INTO tbl_trans_sp (tgl_transaksi, no_ktp, anggota_id, jenis_id, jumlah, keterangan, akun, dk, kas_id,proces_type) 
			SELECT a.tgl_transaksi, a.no_ktp, a.anggota_id, '41', a.tagihan_simpanan_wajib, b.keterangan, 'Setoran', 'D', '4','A'
			FROM tbl_trans_sp_bayar_temp a join tbl_trans_tagihan b on a.no_ktp = b.no_ktp
			WHERE YEAR(a.tgl_transaksi) = '$thn' and MONTH(b.tgl_transaksi) = '$bln' and a.tagihan_simpanan_wajib <> 0 group by a.tgl_transaksi, a.no_ktp");

		$this->db->query("INSERT INTO tbl_trans_sp (tgl_transaksi, no_ktp, anggota_id, jenis_id, jumlah, keterangan, akun, dk, kas_id,proces_type) 
				SELECT a.tgl_transaksi, a.no_ktp, a.anggota_id, '40', a.tagihan_simpanan_pokok, b.keterangan, 'Setoran', 'D', '4','A' 
				FROM tbl_trans_sp_bayar_temp a join tbl_trans_tagihan b on a.no_ktp = b.no_ktp 
				WHERE YEAR(a.tgl_transaksi) = '$thn' and MONTH(b.tgl_transaksi) = '$bln' and a.tagihan_simpanan_pokok <> 0 group by a.tgl_transaksi, a.no_ktp");

		$this->db->query("INSERT INTO tbl_trans_sp (tgl_transaksi, no_ktp, anggota_id, jenis_id, jumlah, keterangan, akun, dk, kas_id,proces_type) 
				SELECT a.tgl_transaksi, a.no_ktp, a.anggota_id, '32', a.tagihan_simpanan_sukarela, b.keterangan, 'Setoran', 'D', '4','A' 
				FROM tbl_trans_sp_bayar_temp a join tbl_trans_tagihan b on a.no_ktp = b.no_ktp 
				WHERE YEAR(a.tgl_transaksi) = '$thn' and MONTH(b.tgl_transaksi) = '$bln' and a.tagihan_simpanan_sukarela <> 0 group by a.tgl_transaksi, a.no_ktp");

		$this->db->query("INSERT INTO tbl_trans_sp (tgl_transaksi, no_ktp, anggota_id, jenis_id, jumlah, keterangan, akun, dk, kas_id,proces_type) 
				SELECT a.tgl_transaksi, a.no_ktp, a.anggota_id, '52', a.tagihan_simpanan_khusus_2, b.keterangan, 'Setoran', 'D', '4','A' 
				FROM tbl_trans_sp_bayar_temp a join tbl_trans_tagihan b on a.no_ktp = b.no_ktp 
				WHERE YEAR(a.tgl_transaksi) = '$thn' and MONTH(b.tgl_transaksi) = '$bln' and a.tagihan_simpanan_khusus_2 <> 0 group by a.tgl_transaksi, a.no_ktp");

		$this->db->query("INSERT INTO tbl_trans_sp (tgl_transaksi, no_ktp, anggota_id, jenis_id, jumlah, keterangan, akun, dk, kas_id,proces_type) 
				SELECT a.tgl_transaksi, a.no_ktp, a.anggota_id, '155', a.tagihan_toserda, b.keterangan, 'Setoran', 'D', '4' ,'A'
				FROM tbl_trans_sp_bayar_temp a join tbl_trans_tagihan b on a.no_ktp = b.no_ktp 
				WHERE YEAR(a.tgl_transaksi) = '$thn' and MONTH(b.tgl_transaksi) = '$bln' and a.tagihan_toserda <> 0 group by a.tgl_transaksi, a.no_ktp");

		// $this->db->query("INSERT INTO tbl_trans_sp (tgl_transaksi, no_ktp, anggota_id, jenis_id, jumlah, keterangan, akun, dk, kas_id,proces_type) 
		// 		SELECT a.tgl_transaksi, a.no_ktp, a.anggota_id, b.jenis_id, a.jumlah, b.keterangan, 'Setoran', 'D', '4','A'
		// FROM tbl_trans_sp_bayar_temp a join tbl_trans_tagihan b on a.no_ktp = b.no_ktp
		// WHERE a.selisih = 0 and YEAR(a.tgl_transaksi) = '$thn' and MONTH(b.tgl_transaksi) = '$bln' group by a.tgl_transaksi, a.no_ktp");

		// $this->db->query("INSERT INTO tbl_trans_sp (tgl_transaksi, no_ktp, anggota_id, jenis_id, jumlah, keterangan, akun, dk, kas_id,proces_type) 
		// 		SELECT a.tgl_transaksi, a.no_ktp, a.anggota_id, b.jenis_id, a.jumlah, b.keterangan, 'Setoran', 'D', '4','A'
		// FROM tbl_trans_sp_bayar_temp a join tbl_trans_tagihan b on a.no_ktp = b.no_ktp
		// WHERE a.selisih > 0 and YEAR(a.tgl_transaksi) = '$thn' and MONTH(b.tgl_transaksi) = '$bln' group by a.tgl_transaksi, a.no_ktp");

		$this->db->query("INSERT INTO tbl_pinjaman_d (tgl_bayar, pinjam_id, angsuran_ke, jumlah_bayar, bunga) 
					SELECT a.tgl_transaksi, b.id, COUNT(c.angsuran_ke) + 1 as angsuran_ke, a.tagihan_pinjaman, a.tagihan_pinjaman_jasa
			FROM tbl_trans_sp_bayar_temp a join tbl_pinjaman_h b on a.no_ktp = b.no_ktp join tbl_pinjaman_d c on c.pinjam_id = b.id  
			WHERE b.lunas = 'Belum' and a.selisih = 0 and YEAR(a.tgl_transaksi) = '$thn' and MONTH(a.tgl_transaksi) = '$bln' group by a.tgl_transaksi, a.no_ktp, b.id");

		// $this->db->query("INSERT INTO tbl_trans_sp (tgl_transaksi, no_ktp, anggota_id, jenis_id, jumlah, akun, dk, kas_id,proces_type) 
		// 		SELECT a.tgl_transaksi, a.no_ktp, a.anggota_id, b.jns_trans, a.tagihan_toserda, 'Setoran', 'D', '4','A'
		// FROM tbl_trans_sp_bayar_temp a join tbl_shu b on a.no_ktp = b.no_ktp
		// WHERE a.selisih = 0 and YEAR(a.tgl_transaksi) = '$thn' and MONTH(b.tgl_transaksi) = '$bln' group by a.tgl_transaksi, a.no_ktp");

		$this->db->query("DELETE FROM `tbl_trans_sp_bayar_temp` WHERE YEAR(tgl_transaksi) = '$thn' and MONTH(tgl_transaksi) = '$bln'");
		return $load;
	}
}
