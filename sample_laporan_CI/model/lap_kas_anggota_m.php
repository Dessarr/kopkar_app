<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

class Lap_kas_anggota_m extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	function get_bayar_simpanan($no_ktp)
	{
		if (isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}

		return $this->db->select('IFNULL(b.jumlah_bayar, 0) AS jumlah_bayar', false)
			->from('tbl_anggota a')
			->join('(SELECT DATE_FORMAT(tgl_transaksi, \'%Y-%m-%d\') AS tgl_transaksi, no_ktp, SUM(jumlah) AS jumlah_bayar, dk FROM tbl_trans_sp WHERE jenis_id NOT IN(155, 8, 125) AND CAST(tgl_transaksi AS DATE)!=\'2020-12-31\' AND jumlah > 0 GROUP BY no_ktp, DATE_FORMAT(tgl_transaksi, \'%Y-%m-%d\')) b', 'a.no_ktp=b.no_ktp')
			->where('YEAR(b.tgl_transaksi)', $thn)
			->where('MONTH(b.tgl_transaksi)', $bln)
			->where('a.no_ktp', $no_ktp)
			->where('b.dk', 'D')
			->get()
			->row();



		// $this->db->select('jumlah_bayar');
		// $this->db->from('v_simpanan_bayar_tanggal');
		// if (isset($_REQUEST['periode'])) {
		// 	$tgl_arr = explode('-', $_REQUEST['periode']);
		// 	$thn = $tgl_arr[0];
		// 	$bln = $tgl_arr[1];
		// } else {
		// 	$thn = date('Y');
		// 	$bln = date('m');
		// }
		// $this->db->where('YEAR(tgl_transaksi) = ', '' . $thn . '');
		// $this->db->where('MONTH(tgl_transaksi) = ', '' . $bln . '');
		// $this->db->where('no_ktp', $no_ktp);
		// $this->db->where('dk', 'D');
		// $query = $this->db->get();
		// return $query->row();
	}

	function get_bayar_simpanan_pot($no_ktp)
	{
		if (isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}
		$this->db->select('IFNULL(b.jumlah_bayar, 0) AS jumlah_bayar', false)
			->from('tbl_anggota a')
			->join('(SELECT DATE_FORMAT(tgl_transaksi, \'%Y-%m-%d\') AS tgl_transaksi, no_ktp, SUM(jumlah) AS jumlah_bayar FROM tbl_trans_sp WHERE jenis_id NOT IN(8, 125) AND CAST(tgl_transaksi AS date)!=\'2020-12-31\' AND jumlah < 0 GROUP BY no_ktp, DATE_FORMAT(tgl_transaksi, \'%Y-%m-%d\')) b', 'a.no_ktp=b.no_ktp', 'left')
			->where('YEAR(b.tgl_transaksi)', $thn)
			->where('MONTH(b.tgl_transaksi)', $bln)
			->get()
			->row();
			

		// $this->db->select('jumlah_bayar');
		// $this->db->from('v_simpanan_bayar_tanggal_pot');
		// if (isset($_REQUEST['periode'])) {
		// 	$tgl_arr = explode('-', $_REQUEST['periode']);
		// 	$thn = $tgl_arr[0];
		// 	$bln = $tgl_arr[1];
		// } else {
		// 	$thn = date('Y');
		// 	$bln = date('m');
		// }
		// $this->db->where('YEAR(tgl_transaksi) = ', '' . $thn . '');
		// $this->db->where('MONTH(tgl_transaksi) = ', '' . $bln . '');
		// $this->db->where('no_ktp', $no_ktp);
		// $query = $this->db->get();
		// return $query->row();
	}

	function get_bayar_pinjaman($no_ktp)
	{
		if (isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}

		$this->db->select('SUM(b.jumlah_bayar) AS jumlah_bayar', false)
			->from('tbl_anggota a')
			->join('(SELECT a.no_ktp, DATE_FORMAT(b.tgl_bayar, \'%Y-%m-%d\') AS tgl_bayar, SUM(b.jumlah_bayar + b.bunga) AS jumlah_bayar FROM tbl_pinjaman_h a INNER JOIN tbl_pinjaman_d b ON a.id=b.pinjam_id GROUP BY a.no_ktp, DATE_FORMAT(b.tgl_bayar, \'%Y-%m-%d\')) b', 'a.no_ktp=b.no_ktp', 'left')
			->where('a.no_ktp', $no_ktp)
			->where('YEAR(b.tgl_bayar)', $thn)
			->where('MONTH(b.tgl_bayar)', $bln)
			->get()
			->row();


		
		/*$this->db->select('sum(jumlah_bayar) as jumlah_bayar');
		$this->db->from('v_pinjaman_bayar_tanggal');
		if (isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}
		$this->db->where('YEAR(tgl_bayar) = ', '' . $thn . '');
		$this->db->where('MONTH(tgl_bayar) = ', '' . $bln . '');
		$this->db->where('no_ktp', $no_ktp);
		$query = $this->db->get();
		return $query->row();*/
	}

	function get_tak_bayar_bayar($no_ktp)
	{
		return $this->db->select('COALESCE(b.tgl_transaksi, c.tgl_transaksi, d.tgl_transaksi) AS tgl_transaksi, a.no_ktp, IFNULL(c.jumlah_bayar, 0) AS jumlah_bayar, IFNULL(d.jumlah_tagihan, 0) AS jumlah_tagihan, IFNULL(b.jumlah_bayar, 0) AS jumlah_tagihan_toserda, IF(IFNULL(c.jumlah_bayar, 0) - (IFNULL(b.jumlah_bayar, 0) + IFNULL(d.jumlah_tagihan, 0)) < 0, IFNULL(b.jumlah_bayar, 0) + IFNULL(d.jumlah_tagihan, 0), 0) AS tagihan_tak_terbayar', false)
			->from('tbl_anggota a')
			->join('(SELECT DATE_FORMAT(tgl_transaksi, \'%Y-%m-%d\') AS tgl_transaksi, no_ktp, SUM(jumlah_bayar) AS jumlah_bayar FROM tbl_shu WHERE jns_trans IN(155, 154) GROUP BY no_ktp, DATE_FORMAT(tgl_transaksi, \'%Y-%m-%d\')) b', 'a.no_ktp=b.no_ktp', 'left')
			->join('(SELECT DATE_FORMAT(tgl_transaksi, \'%Y-%m-%d\') AS tgl_transaksi, no_ktp, SUM(jumlah) AS jumlah_bayar, dk FROM tbl_trans_sp WHERE jenis_id NOT IN(155, 8, 125) AND CAST(tgl_transaksi AS date)!=\'2020-12-31\' AND jumlah>0 GROUP BY no_ktp, DATE_FORMAT(tgl_transaksi, \'%Y-%m-%d\')) c', 'a.no_ktp=c.no_ktp', 'left')
			->join('(SELECT DATE_FORMAT(tgl_transaksi, \'%Y-%m-%d\') AS tgl_transaksi, no_ktp, SUM(jumlah) AS jumlah_tagihan FROM tbl_trans_tagihan WHERE jenis_id NOT IN(8, 31) GROUP BY no_ktp, DATE_FORMAT(tgl_transaksi, \'%Y-%m-%d\')) d', 'a.no_ktp=d.no_ktp', 'left')
			->where('a.no_ktp', $no_ktp)
			->get()
			->row();

		/*$this->db->select('tagihan_tak_terbayar');
		$this->db->from('v_simpanan_gabung_tanggal');
		if (isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}
		//$this->db->where('YEAR(tgl_transaksi) = ', ''.$thn.'');
		//$this->db->where('MONTH(tgl_transaksi) = ', ''.$bln.'');
		$this->db->where('no_ktp', $no_ktp);
		$query = $this->db->get();
		return $query->row();*/
	}

	function get_jml_saldo($no_ktp)
	{
		$this->db->select('a.no_ktp, IFNULL(b.jumlah_bayar, 0) AS jumlah_bayar, IFNULL(c.jumlah_tagihan, 0) AS jumlah_tagihan, IFNULL(d.jumlah_bayar, 0) AS jumlah_tagihan_toserda, IF(IFNULL(b.jumlah_bayar, 0) - (IFNULL(d.jumlah_bayar, 0) + IFNULL(c.jumlah_tagihan, 0)) < 0, IFNULL(d.jumlah_bayar, 0) + IFNULL(c.jumlah_tagihan, 0), 0) AS tagihan_tak_terbayar', false)
			->from('tbl_anggota a')
			->join('(SELECT no_ktp, SUM(jumlah) AS jumlah_bayar FROM tbl_trans_sp WHERE jenis_id NOT IN(155, 8, 125) AND CAST(tgl_transaksi AS DATE)!=\'2020-12-31\' AND jumlah > 0 GROUP BY no_ktp) b', 'a.no_ktp=b.no_ktp', 'left')
			->join('(SELECT no_ktp, SUM(jumlah) AS jumlah_tagihan FROM tbl_trans_tagihan WHERE jenis_id NOT IN(8, 31) GROUP BY no_ktp) c', 'a.no_ktp=c.no_ktp', 'left')
			->join('(SELECT no_ktp, SUM(jumlah_bayar) AS jumlah_bayar FROM tbl_shu WHERE jns_trans IN(155, 154) GROUP BY no_ktp) d', 'a.no_ktp=d.no_ktp', 'left')
			->where('a.no_ktp', $no_ktp)
			->group_by('a.no_ktp')
			->get()->row();

		// $this->db->select('tagihan_tak_terbayar');
		// $this->db->from('v_simpanan_gabung');

		// //$this->db->where('YEAR(tgl_transaksi) = ', ''.$thn.'');
		// //$this->db->where('MONTH(tgl_transaksi) = ', ''.$bln.'');
		// $this->db->where('no_ktp', $no_ktp);
		// $query = $this->db->get();
		// return $query->row();
	}

	function get_jml_saldo_piutang($no_ktp)
	{
		$this->db->select('SUM(jumlah) as saldo_piutang');
		$this->db->from('tbl_trans_tagihan');
		$this->db->where('no_ktp', $no_ktp);
		$this->db->where('jenis_id', 8);
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_simpanan($jenis, $id)
	{
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('tbl_trans_sp');

		if (isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}
		$this->db->where('YEAR(tgl_transaksi) = ', '' . $thn . '');
		$this->db->where('MONTH(tgl_transaksi) = ', '' . $bln . '');

		$this->db->where('anggota_id', $id);
		$this->db->where('dk', 'D');
		$this->db->where('jenis_id', $jenis);
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_bulan_lalu($no_ktp)
	{
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('jumlah');
		$this->db->from('v_tagihan_bulan_lalu');
		$this->db->where('no_ktp', $no_ktp);
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_simpanan_member($jenis, $no_ktp)
	{
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('tbl_trans_sp');
		$this->db->where('no_ktp', $no_ktp);
		$this->db->where('dk', 'D');
		$this->db->where('jenis_id', $jenis);
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_simpan()
	{
		$id = array(40, 32, 41, 52);
		$this->db->select('*');
		$this->db->from('jns_simpan');
		$this->db->where_in('id', $id);
		$this->db->order_by('urut', 'ASC');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$out = $query->result();
			return $out;
		} else {
			return array();
		}
	}

	function get_jml_tagihan_simpanan($no_ktp)
	{
		$id = array(40, 32, 41, 52);
		$this->db->select('*');
		//$this->db->from('v_tagihan');
		
		$this->db->from('tbl_trans_tagihan a');
		$this->db->join('jns_simpan b', 'a.jenis_id=b.id');


		if (isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}
		$this->db->where('YEAR(a.tgl_transaksi) = ', '' . $thn . '');
		$this->db->where('MONTH(a.tgl_transaksi) = ', '' . $bln . '');
		$this->db->where_in('a.jenis_id', $id);
		$this->db->where('a.no_ktp', $no_ktp);
		$this->db->order_by('b.urut', 'ASC');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$out = $query->result();
			return $out;
		} else {
			return array();
		}
	}

	function get_jml_biasa_pinjaman($no_ktp)
	{
		$this->db->select('*');
		$this->db->from('v_hitung_pinjaman_3');
		if (isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0] . $tgl_arr[1];
		} else {

			$thn = date('Ym');
		}

		$this->db->where("DATE_FORMAT(tgl_pinjam,'%Y%m') <=", '' . $thn . '');
		$this->db->where("DATE_FORMAT(tempo,'%Y%m') >=", '' . $thn . '');
		$this->db->where('no_ktp', $no_ktp);
		$this->db->where('jenis_pinjaman', 1);
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_bank_pinjaman($no_ktp)
	{
		$this->db->select('*');
		$this->db->from('v_hitung_pinjaman_3');
		$this->db->where('no_ktp', $no_ktp);
		$this->db->where('lunas', 'Belum');
		$this->db->where('jenis_pinjaman', 2);
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_barang_pinjaman($no_ktp)
	{
		$this->db->select('*');
		$this->db->from('v_hitung_pinjaman_3');
		$this->db->where('no_ktp', $no_ktp);
		$this->db->where('lunas', 'Belum');
		$this->db->where('jenis_pinjaman', 3);
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_biasa($no_ktp)
	{
		$this->db->select('jumlah, jasa, lama_angsuran, angsuran');
		$this->db->from('v_lap_bank');
		if (isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0] . $tgl_arr[1];
		} else {

			$thn = date('Ym');
		}
		$this->db->where("DATE_FORMAT(tgl_pinjam,'%Y%m') <=", '' . $thn . '');
		$this->db->where('no_ktp', $no_ktp);
		$this->db->where('lunas', 'Belum');
		$this->db->where('jenis', 1);
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_bank($no_ktp)
	{
		$this->db->select('jumlah, jasa, lama_angsuran, angsuran');
		$this->db->from('v_lap_bank');
		$this->db->where('no_ktp', $no_ktp);
		$this->db->where('lunas', 'Belum');
		$this->db->where('jenis', 2);
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_barang($no_ktp)
	{
		$this->db->select('jumlah, jasa, lama_angsuran, angsuran');
		$this->db->from('v_lap_bank');
		$this->db->where('no_ktp', $no_ktp);
		$this->db->where('lunas', 'Belum');
		$this->db->where('jenis', 3);
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_toserda($no_ktp)
	{
		$this->db->select('jumlah_bayar');
		$this->db->from('v_lap_toserda');
		if (isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}
		$this->db->where('YEAR(tgl_transaksi) = ', '' . $thn . '');
		$this->db->where('MONTH(tgl_transaksi) = ', '' . $bln . '');

		$this->db->where('no_ktp', $no_ktp);
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_lain_lain($no_ktp)
	{
		$this->db->select('jumlah_bayar');
		$this->db->from('v_lap_lain_lain');
		if (isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}
		$this->db->where('YEAR(tgl_transaksi) = ', '' . $thn . '');
		$this->db->where('MONTH(tgl_transaksi) = ', '' . $bln . '');

		$this->db->where('no_ktp', $no_ktp);
		$query = $this->db->get();
		return $query->row();
	}

	function get_saldo($no_ktp)
	{
		$id = array(40, 32, 41, 52);
		$bu = date('m') - 1;
		$bu = date('Y' . '-' . $bu);
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('tbl_trans_sp');
		$this->db->where('no_ktp', $no_ktp);
		//$this->db->where('tgl_transaksi',$bu);
		$this->db->like('tgl_transaksi', $bu);
		$this->db->where_in('jenis_id', $id);
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_simpans($no_ktp)
	{
		$id = array(40, 32, 41, 52);

		if (isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}
		
		return $this->db->select('SUM(b.jumlah) AS jml_total')
			->from('tbl_anggota a')
			->join('(SELECT b.urut, a.no_ktp, a.tgl_transaksi, a.jumlah, a.jenis_id FROM tbl_trans_tagihan a INNER JOIN jns_simpan b ON a.jenis_id=b.id) b', 'a.no_ktp=b.no_ktp', 'left')
			->where('YEAR(b.tgl_transaksi)', $thn)
			->where('MONTH(b.tgl_transaksi)', $bln)
			->where_in('b.jenis_id', $id)
			->where('a.no_ktp', $no_ktp)
			->get()
			->row();

		// $id = array(40, 32, 41, 52);
		// $this->db->select('SUM(jumlah) AS jml_total');
		// $this->db->from('v_tagihan');
		// if (isset($_REQUEST['periode'])) {
		// 	$tgl_arr = explode('-', $_REQUEST['periode']);
		// 	$thn = $tgl_arr[0];
		// 	$bln = $tgl_arr[1];
		// } else {
		// 	$thn = date('Y');
		// 	$bln = date('m');
		// }
		// $this->db->where('YEAR(tgl_transaksi) = ', '' . $thn . '');
		// $this->db->where('MONTH(tgl_transaksi) = ', '' . $bln . '');
		// $this->db->where_in('jenis_id', $id);
		// $this->db->where('no_ktp', $no_ktp);
		// $query = $this->db->get();
		// return $query->row();
	}

	function get_jml_bayars($no_ktp)
	{
		$id = array(40, 32, 41, 52);
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('tbl_trans_sp');
		$this->db->where_in('jenis_id', $id);
		$this->db->where('no_ktp', $no_ktp);
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_simpanans($jenis, $no_ktp)
	{
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('tbl_trans_sp');
		$this->db->where('no_ktp', $no_ktp);
		$this->db->where('akun', 'Setoran');
		$this->db->where('jenis_id', $jenis);
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_cetak($no_ktp)
	{
		$id = array(40, 32, 41, 52);
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('tbl_trans_sp');

		if (isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}
		$this->db->where('YEAR(tgl_transaksi) = ', '' . $thn . '');
		$this->db->where('MONTH(tgl_transaksi) = ', '' . $bln . '');
		$this->db->where_in('jenis_id', $id);
		$this->db->where('no_ktp', $no_ktp);
		$this->db->where('dk', 'D');
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->row();
	}

	function get_bulan($no_ktp)
	{
		$this->db->select('tagihan_tak_terbayar');
		$this->db->from('v_simpanan_gabung');
		//$this->db->where('jenis_id',8);
		$this->db->where('no_ktp', $no_ktp);
		//$this->db->where('dk','D');
		$query = $this->db->get();
		return $query->row();
	}

	function get_bulan_tak($no_ktp)
	{
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('tbl_trans_tagihan');
		$this->db->where('jenis_id', 8);
		$this->db->where('no_ktp', $no_ktp);
		//$this->db->where('dk','D');
		$query = $this->db->get();
		return $query->row();
	}

	function get_tak_bayar($no_ktp)
	{

		return $this->db->select('a.no_ktp, IFNULL(jumlah_bayar, 0) AS jumlah_bayar', false)
			->from('tbl_anggota a')
			->join('(SELECT no_ktp, SUM(jumlah) AS jumlah_bayar FROM tbl_trans_sp WHERE jenis_id NOT IN(8, 125) AND CAST(tgl_transaksi AS DATE)!=\'2020-12-31\' GROUP BY no_ktp) b', 'a.no_ktp=b.no_ktp', 'left')
			->where('a.no_ktp', $no_ktp)
			->group_by('a.no_ktp')
			->get()
			->row();



		// $this->db->select('jumlah_bayar');
		// $this->db->from('v_simpanan_bayar');

		// //$this->db->where('YEAR(tgl_transaksi) = ', ''.$thn.'');
		// //$this->db->where('MONTH(tgl_transaksi) = ', ''.$bln.'');
		// //$this->db->where('jenis_id',8);
		// $this->db->where('no_ktp', $no_ktp);
		// //$this->db->where('dk','D');
		// $query = $this->db->get();
		// return $query->row();
	}

	function get_tak_bayar_toserda($no_ktp)
	{
		if (isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}

		$this->db->select('b.jumlah')
			->from('tbl_anggota a')
			->join('(SELECT no_ktp, tgl_transaksi, jumlah FROM tbl_trans_sp WHERE jenis_id IN (155, 154)) b', 'a.no_ktp=b.no_ktp', 'left')
			->where('a.no_ktp', $no_ktp)
			->where('YEAR(b.tgl_transaksi)=' . $thn, null)
			->where('MONTH(b.tgl_transaksi)=' . $bln, null)
			->get()
			->row();



		// $this->db->select('jumlah');
		// $this->db->from('v_toserda');
		// if (isset($_REQUEST['periode'])) {
		// 	$tgl_arr = explode('-', $_REQUEST['periode']);
		// 	$thn = $tgl_arr[0];
		// 	$bln = $tgl_arr[1];
		// } else {
		// 	$thn = date('Y');
		// 	$bln = date('m');
		// }
		// $this->db->where('YEAR(tgl_transaksi) = ', '' . $thn . '');
		// $this->db->where('MONTH(tgl_transaksi) = ', '' . $bln . '');
		// $this->db->where('no_ktp', $no_ktp);
		// $query = $this->db->get();
		// return $query->row();
	}

	function get_tak_bayar_lain_lain($no_ktp)
	{
		$this->db->select('jumlah');
		$this->db->from('tbl_trans_sp');
		if (isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}
		$this->db->where('YEAR(tgl_transaksi) = ', '' . $thn . '');
		$this->db->where('MONTH(tgl_transaksi) = ', '' . $bln . '');
		$this->db->where('jenis_id', 154);
		$this->db->where('no_ktp', $no_ktp);
		$query = $this->db->get();
		return $query->row();
	}

	function get_tagih($no_ktp)
	{
		$this->db->select('a.no_ktp, IFNULL(b.jumlah_tagihan, 0) AS jumlah_tagihan', false)
			->from('tbl_anggota a')
			->join('(SELECT no_ktp, SUM(jumlah) AS jumlah_tagihan FROM tbl_trans_tagihan WHERE jenis_id NOT IN(8, 31) GROUP BY no_ktp) b', 'a.no_ktp=b.no_ktp', 'left')
			->where('a.no_ktp', $no_ktp)
			->get()
			->row();


		// $this->db->select('jumlah_tagihan');
		// $this->db->from('v_simpanan_tagihan');

		// //$this->db->where('YEAR(tgl_transaksi) = ', ''.$thn.'');
		// //$this->db->where('MONTH(tgl_transaksi) = ', ''.$bln.'');
		// //$this->db->where('jenis_id',8);
		// $this->db->where('no_ktp', $no_ktp);
		// //$this->db->where('dk','D');
		// $query = $this->db->get();
		// return $query->row();
	}

	function get_tagih_toserda($no_ktp)
	{
		$this->db->select('jumlah_bayar');
		$this->db->from('tbl_shu');
		if (isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}
		$this->db->where('YEAR(tgl_transaksi) = ', '' . $thn . '');
		$this->db->where('MONTH(tgl_transaksi) = ', '' . $bln . '');
		$this->db->where('jns_trans', 155);
		$this->db->where('no_ktp', $no_ktp);
		//$this->db->where('dk','D');
		$query = $this->db->get();
		return $query->row();
	}

	function get_tagih_lain_lain($no_ktp)
	{
		$this->db->select('jumlah_bayar');
		$this->db->from('tbl_shu');
		if (isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}
		$this->db->where('YEAR(tgl_transaksi) = ', '' . $thn . '');
		$this->db->where('MONTH(tgl_transaksi) = ', '' . $bln . '');
		$this->db->where('jns_trans', 154);
		$this->db->where('no_ktp', $no_ktp);
		//$this->db->where('dk','D');
		$query = $this->db->get();
		return $query->row();
	}

	function get_bulan_sekarang($no_ktp)
	{
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('tbl_trans_sp');
		$this->db->where('jenis_id', 8);
		$this->db->where('no_ktp', $no_ktp);
		$this->db->where('dk', 'D');
		$query = $this->db->get();
		return $query->row();
	}

	//panggil data jenis simpan
	function get_jenis_simpan()
	{
		$id = array(41, 32, 52, 40, 51, 31);
		$this->db->select('*');
		$this->db->from('jns_simpan');
		$this->db->where('tampil', 'Y');
		$this->db->where_in('id', $id);
		$this->db->order_by('urut', 'ASC');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$out = $query->result();
			return $out;
		} else {
			return array();
		}
	}

	//menghitung jumlah penarikan
	function get_jml_penarikan($jenis, $no_ktp)
	{
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('tbl_trans_sp');
		$this->db->where('dk', 'K');
		$this->db->where('no_ktp', $no_ktp);
		$this->db->where('jenis_id', $jenis);
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_penarikans($jenis, $no_ktp)
	{
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('tbl_trans_sp');
		$this->db->where('dk', 'K');
		$this->db->where('no_ktp', $no_ktp);
		$this->db->where('jenis_id', $jenis);
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->row();
	}

	function get_data_anggota($limit, $start, $q = '')
	{
		//$id_cab = $this->session->userdata('id_cabang');
		$anggota_id = isset($_REQUEST['anggota_id']) ? $_REQUEST['anggota_id'] : '';
		$sql = '';
		$sql = "SELECT * FROM tbl_anggota WHERE aktif='Y' ";
		$q = array('anggota_id' => $anggota_id);
		if (is_array($q)) {
			if ($q['anggota_id'] != '') {
				$q['anggota_id'] = str_replace('AG', '', $q['anggota_id']);
				$sql .= " AND (id LIKE '" . $q['anggota_id'] . "' OR nama LIKE '" . $q['anggota_id'] . "') ";
			}
		}
		$sql .= "order by nama asc ";
		$sql .= "LIMIT " . $start . ", " . $limit . " ";

		$query = $this->db->query($sql);
		if ($query->num_rows() > 0) {
			$out = $query->result();
			return $out;
		} else {
			return array();
		}
	}

	function lap_data_anggota()
	{
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('tbl_anggota');
		$this->db->where('aktif', 'Y');
		//$this->db->where('id_cabang', $id_cab);
		$this->db->order_by('nama', 'ASC');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$out = $query->result();
			return $out;
		} else {
			return FALSE;
		}
	}

	function lap_data_anggota_limit()
	{
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('id_tagihan, nama, no_ktp');
		$this->db->from('tbl_anggota');
		$this->db->where('aktif', 'Y');
		//$this->db->where('id_cabang', $id_cab);
		$this->db->order_by('nama', 'ASC');
		$this->db->limit('25');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$out = $query->result();
			return $out;
		} else {
			return FALSE;
		}
	}

	function lap_data_potongan()
	{
		$this->db->select('*');
		$this->db->from('v_lap_gabung');

		if (isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}
		$this->db->where('YEAR(tanggal) = ', '' . $thn . '');
		$this->db->where('MONTH(tanggal) = ', '' . $bln . '');
		$this->db->order_by('nama', 'ASC');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$out = $query->result();
			return $out;
		} else {
			return FALSE;
		}
	}

	function lap_data_simpan($no_ktp)
	{
		$this->db->select('*');
		$this->db->from('v_lap_potongan');

		if (isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
		}
		$this->db->where('YEAR(tanggal) = ', '' . $thn . '');
		$this->db->where('MONTH(tanggal) = ', '' . $bln . '');
		$this->db->where('no_ktp', $no_ktp);
		$this->db->order_by('nama', 'ASC');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$out = $query->result();
			return $out;
		} else {
			return FALSE;
		}
	}

	function get_jml_data_anggota()
	{
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->where('aktif', 'Y');
		//$this->db->where('id_cabang', $id_cab);
		return $this->db->count_all_results('tbl_anggota');
	}

	//ambil data pinjaman header berdasarkan ID peminjam
	function get_data_pinjam($id)
	{
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('v_hitung_pinjaman');
		$this->db->where('anggota_id', $id);
		$this->db->where('lunas', 'Belum');
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$out = $query->row();
			return $out;
		} else {
			return array();
		}
	}

	function get_data_pinjams($no_ktp)
	{
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('v_hitung_pinjaman');
		$this->db->where('no_ktp', $no_ktp);
		$this->db->where('lunas', 'Belum');
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$out = $query->row();
			return $out;
		} else {
			return array();
		}
	}

	function get_peminjam_lunas($id)
	{
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('v_hitung_pinjaman');
		$this->db->where('lunas', 'Lunas');
		$this->db->where('anggota_id', $id);
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->num_rows();
	}

	function get_peminjam_lunass($no_ktp)
	{
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('v_hitung_pinjaman');
		$this->db->where('lunas', 'Lunas');
		$this->db->where('no_ktp', $no_ktp);
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->num_rows();
	}

	function get_peminjam_tot($id)
	{
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('v_hitung_pinjaman');
		$this->db->where('anggota_id', $id);
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->num_rows();
	}

	function get_peminjam_tots($no_ktp)
	{
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('v_hitung_pinjaman');
		$this->db->where('no_ktp', $no_ktp);
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->num_rows();
	}

	//menghitung jumlah yang sudah dibayar
	function get_jml_pinjaman($id)
	{
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah) AS total');
		$this->db->from('v_hitung_pinjaman');
		$this->db->where('anggota_id', $id);
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_pinjamans($no_ktp)
	{
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah) AS total');
		$this->db->from('v_hitung_pinjaman');
		$this->db->where('no_ktp', $no_ktp);
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->row();
	}

	//menghitung jumlah yang sudah dibayar
	function get_jml_tagihan($id)
	{
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(tagihan) AS total');
		$this->db->from('v_hitung_pinjaman');
		$this->db->where('anggota_id', $id);
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->row();
	}


	//menghitung jumlah yang sudah dibayar
	function get_jml_bayar($id)
	{
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah_bayar) AS total');
		$this->db->from('tbl_pinjaman_d');
		$this->db->where('pinjam_id', $id);
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->row();
	}

	//menghitung jumlah denda harus dibayar
	function get_jml_denda($id)
	{
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(denda_rp) AS total_denda');
		$this->db->from('tbl_pinjaman_d');
		$this->db->where('pinjam_id', $id);
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->row();
	}
}
