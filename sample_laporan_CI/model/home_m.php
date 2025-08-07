<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home_m extends CI_Model {
	
	public function __construct() {
		parent::__construct();
	}

	//hitung jumlah anggota total
	function get_anggota_all() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('tbl_anggota');
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->num_rows();
	}

	//hitung jumlah anggota aktif
	function get_anggota_aktif() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('tbl_anggota');
		$this->db->where('aktif','Y');
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->num_rows();
	}

	//hitung jumlah anggota tdk aktif
	function get_anggota_non() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('tbl_anggota');
		$this->db->where('aktif','N');
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->num_rows();
	}

	//menghitung jumlah simpanan
	function get_jml_simpanan() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('tbl_trans_sp');
		$this->db->where('dk','D');
		//$this->db->where('id_cabang', $id_cab);

		//$thn = date('Y');			
		//$bln = date('m');			
		//$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$bln."' ";
		//$this->db->where($where);

		$query = $this->db->get();
		return $query->row();
	}

	//menghitung jumlah penarikan
	function get_jml_penarikan() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('tbl_trans_sp');
		$this->db->where('dk','K');
		//$this->db->where('id_cabang', $id_cab);

		//$thn = date('Y');			
		//$bln = date('m');			
		//$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$bln."' ";
		//$this->db->where($where);

		$query = $this->db->get();
		return $query->row();
	}

	function get_peminjam_aktif_bulan_lalu() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('count(jumlah) AS jml_peminjam_aktif_bulan_lalu');
		$this->db->from('v_hitung_pinjaman');
		$thn = date('Y');
		$bln = date('Y-m');
		$blnn = date('m', strtotime('-1 month', strtotime( $bln )));
		
		$where = "YEAR(tgl_pinjam) = '".$thn."' AND  MONTH(tgl_pinjam) = '".$blnn."' ";

		$this->db->where($where);
		$this->db->where('dk', 'K');
		//$this->db->where('id_cabang', $id_cab);
		//$this->db->where('akun', 'Setoran');

		$query_sblm = $this->db->get();
		$peminjam_aktif_bulan_lalu = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$peminjam_aktif_bulan_lalu = ($row_sblm->jml_peminjam_aktif_bulan_lalu);
		}
		return $peminjam_aktif_bulan_lalu;
	}

	//hitung jumlah peminjam aktif
	function get_peminjam_aktif_bulan_ini() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('count(jumlah) AS jml_peminjam_aktif_bulan_ini');
		$this->db->from('v_hitung_pinjaman');
		$thn = date('Y');
		$bln = date('m');
		$blnn = date('m', strtotime('-1 month', strtotime( $bln )));
		
		$where = "YEAR(tgl_pinjam) = '".$thn."' AND  MONTH(tgl_pinjam) = '".$bln."' ";

		$this->db->where($where);
		$this->db->where('dk', 'K');
		//$this->db->where('id_cabang', $id_cab);
		//$this->db->where('akun', 'Setoran');

		$query_sblm = $this->db->get();
		$peminjam_aktif_bulan_ini = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$peminjam_aktif_bulan_ini = ($row_sblm->jml_peminjam_aktif_bulan_ini);
		}
		return $peminjam_aktif_bulan_ini;
	}

	//hitung jumlah peminjam lunas
	function get_peminjam_lunas() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('v_hitung_pinjaman');
		$this->db->where('lunas','Lunas');
		//$this->db->where('id_cabang', $id_cab);

		//$tgl_dari = date('Y') . '-01-01';
		//$tgl_samp = date('Y') . '-12-31';
		
		//$this->db->where('DATE(tgl_pinjam) >= ', ''.$tgl_dari.'');
		//$this->db->where('DATE(tgl_pinjam) <= ', ''.$tgl_samp.'');

		$query = $this->db->get();
		return $query->num_rows();
	}

	//hitung jumlah peminjam belum lunas
	function get_peminjam_belum() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('v_hitung_pinjaman');
		$this->db->where('lunas','Belum');
		//$this->db->where('id_cabang', $id_cab);

		//$tgl_dari = date('Y') . '-01-01';
		//$tgl_samp = date('Y') . '-12-31';
		
		//$this->db->where('DATE(tgl_pinjam) >= ', ''.$tgl_dari.'');
		//$this->db->where('DATE(tgl_pinjam) <= ', ''.$tgl_samp.'');

		$query = $this->db->get();
		return $query->num_rows();
	}

	//menghitung jumlah pinjaman Rp
	function get_jml_pinjaman() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(tagihan) AS jml_total');
		$this->db->from('v_hitung_pinjaman');
		//$this->db->where('id_cabang', $id_cab);
		$this->db->where('lunas', 'Belum');

		//$tgl_dari = date('Y') . '-01-01';
		//$tgl_samp = date('Y') . '-12-31';
		
		//$this->db->where('DATE(tgl_pinjam) >= ', ''.$tgl_dari.'');
		//$this->db->where('DATE(tgl_pinjam) <= ', ''.$tgl_samp.'');

		$query = $this->db->get();
		return $query->row();
	}

	function get_nama_simpanan() {
		//$id = array('31','32','40','41','51','52');
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('jns_simpan');
		//$this->db->where_in('id', $id);
		$query = $this->db->get();
		if($query->num_rows()>0){
			$out = $query->result();
			return $out;
		} else {
			return FALSE;
		}
	}

	function get_belum_lunas() {
		//$id_cab = $this->session->userdata('id_cabang');
                $this->db->select('SUM(jumlah)-(select sum(jumlah) from tbl_trans_sp where jenis_id=8) AS jml_belum_lunas_debet');
		//$this->db->select('SUM(jumlah) AS jml_belum_lunas_debet');
		$this->db->from('v_tagihan');
		$thn = date('Y');
		$bln = date('m');
		$blnn = date('m', strtotime('-1 month', strtotime( $bln )));
		
		//$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$blnn."' ";
		$where = "DATE(tgl_transaksi) < '".$thn."-".$bln."-01' ";

		$this->db->where($where);
		$this->db->where('jenis_id', 8);
		//$this->db->where('id_cabang', $id_cab);

		$query_sblm = $this->db->get();
		$belum_lunas = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$belum_lunas = ($row_sblm->jml_belum_lunas_debet);
		}
		return $belum_lunas;
	}

	function get_berjangka() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(Debet) AS jml_berjangka_debet, SUM(Kredit) AS jml_berjangka_kredit');
		$this->db->from('v_rekap_simpanan');
		$thn = date('Y');
		$bln = date('m');
		$blnn = date('m', strtotime('-1 month', strtotime( $bln )));
		
		//$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$blnn."' ";
		$where = "DATE(tgl_transaksi) < '".$thn."-".$bln."-01' ";

		$this->db->where($where);
		$this->db->where('jenis_id', 31);
		//$this->db->where('id_cabang', $id_cab);

		$query_sblm = $this->db->get();
		$berjangka = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$berjangka = ($row_sblm->jml_berjangka_debet - $row_sblm->jml_berjangka_kredit);
		}
		return $berjangka;
	}

	function get_berjangka_penerimaan() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(Debet) AS jml_berjangka_debet');
		$this->db->from('v_rekap_simpanan');
		$thn = date('Y');
		$bln = date('m');
		
		$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$bln."' ";

		$this->db->where($where);
		$this->db->where('jenis_id', 31);
		//$this->db->where('id_cabang', $id_cab);

		$query_sblm = $this->db->get();
		$berjangka_penerimaan = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$berjangka_penerimaan = ($row_sblm->jml_berjangka_debet);
		}
		return $berjangka_penerimaan;
	}

	function get_berjangka_penarikan() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(Kredit) AS jml_berjangka_kredit');
		$this->db->from('v_rekap_simpanan');
		$thn = date('Y');
		$bln = date('m');
		
		$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$bln."' ";

		$this->db->where($where);
		$this->db->where('jenis_id', 31);
		//$this->db->where('id_cabang', $id_cab);

		$query_sblm = $this->db->get();
		$berjangka_penarikan = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$berjangka_penarikan = ($row_sblm->jml_berjangka_kredit);
		}
		return $berjangka_penarikan;
	}

	function get_sukarela() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(Debet) AS jml_sukarela_debet, SUM(Kredit) AS jum_sukarela_kredit');
		$this->db->from('v_rekap_simpanan');
		$thn = date('Y');
		$bln = date('m');
		$blnn = date('m', strtotime('-1 month', strtotime( $bln )));
		
		//$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$blnn."' ";
		$where = "DATE(tgl_transaksi) < '".$thn."-".$bln."-01' ";
		//$where = "DATE(tgl_transaksi) LIKE '%".$thn."-".$blnn."%' ";

		$this->db->where($where);
		$this->db->where('jenis_id', 32);
		//$this->db->where('id_cabang', $id_cab);
		//$this->db->where('akun', 'Setoran');

		$query_sblm = $this->db->get();
		$sukarela = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$sukarela = ($row_sblm->jml_sukarela_debet - $row_sblm->jum_sukarela_kredit);
		}
		return $sukarela;
	}

	function get_sukarela_penerimaan() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(Debet) AS jml_sukarela_debet');
		$this->db->from('v_rekap_simpanan');
		$thn = date('Y');
		$bln = date('m');
		
		$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$bln."' ";

		$this->db->where($where);
		$this->db->where('jenis_id', 32);
		//$this->db->where('id_cabang', $id_cab);
		//$this->db->where('akun', 'Setoran');

		$query_sblm = $this->db->get();
		$sukarela_penerimaan = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$sukarela_penerimaan = ($row_sblm->jml_sukarela_debet);
		}
		return $sukarela_penerimaan;
	}

	function get_sukarela_penarikan() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(Kredit) AS jml_sukarela_kredit');
		$this->db->from('v_rekap_simpanan');
		$thn = date('Y');
		$bln = date('m');
		
		$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$bln."' ";

		$this->db->where($where);
		$this->db->where('jenis_id', 32);
		//$this->db->where('id_cabang', $id_cab);
		//$this->db->where('akun', 'Penarikan');

		$query_sblm = $this->db->get();
		$sukarela_penarikan = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$sukarela_penarikan = ($row_sblm->jml_sukarela_kredit);
		}
		return $sukarela_penarikan;
	}

	function get_pokok() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(Debet) AS jml_pokok_debet, SUM(Kredit) AS jml_pokok_kredit');
		$this->db->from('v_rekap_simpanan');
		$thn = date('Y');
		$bln = date('m');
		$blnn = date('m', strtotime('-1 month', strtotime( $bln )));
		
		//$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$blnn."' ";
		$where = "DATE(tgl_transaksi) < '".$thn."-".$bln."-01' ";

		$this->db->where($where);
		$this->db->where('jenis_id', 40);
		//$this->db->where('id_cabang', $id_cab);

		$query_sblm = $this->db->get();
		$pokok = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$pokok = ($row_sblm->jml_pokok_debet - $row_sblm->jml_pokok_kredit);
		}
		return $pokok;
	}

	function get_pokok_penerimaan() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(Debet) AS jml_pokok_debet');
		$this->db->from('v_rekap_simpanan');
		$thn = date('Y');
		$bln = date('m');
		
		$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$bln."' ";

		$this->db->where($where);
		$this->db->where('jenis_id', 40);
		//$this->db->where('id_cabang', $id_cab);

		$query_sblm = $this->db->get();
		$pokok_penerimaan = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$pokok_penerimaan = ($row_sblm->jml_pokok_debet);
		}
		return $pokok_penerimaan;
	}

	function get_pokok_penarikan() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(Kredit) AS jml_pokok_kredit');
		$this->db->from('v_rekap_simpanan');
		$thn = date('Y');
		$bln = date('m');
		
		$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$bln."' ";

		$this->db->where($where);
		$this->db->where('jenis_id', 40);
		//$this->db->where('id_cabang', $id_cab);

		$query_sblm = $this->db->get();
		$pokok_penarikan = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$pokok_penarikan = ($row_sblm->jml_pokok_kredit);
		}
		return $pokok_penarikan;
	}

	function get_wajib() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(Debet) AS jml_wajib_debet, SUM(Kredit) AS jml_wajib_kredit');
		$this->db->from('v_rekap_simpanan');
		$thn = date('Y');
		$bln = date('m');
		$blnn = date('m', strtotime('-1 month', strtotime( $bln )));
		
		//$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$blnn."' ";
		$where = "DATE(tgl_transaksi) < '".$thn."-".$bln."-01' ";

		$this->db->where($where);
		$this->db->where('jenis_id', 41);
		//$this->db->where('id_cabang', $id_cab);

		$query_sblm = $this->db->get();
		$wajib = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$wajib = ($row_sblm->jml_wajib_debet - $row_sblm->jml_wajib_kredit);
		}
		return $wajib;
	}

	function get_khusus_1() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(Debet) AS jml_khusus_1_debet, SUM(Kredit) AS jml_khusus_1_kredit');
		$this->db->from('v_rekap_simpanan');
		$thn = date('Y');
		$bln = date('m');
		$blnn = date('m', strtotime('-1 month', strtotime( $bln )));
		
		//$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$blnn."' ";
		$where = "DATE(tgl_transaksi) < '".$thn."-".$bln."-01' ";

		$this->db->where($where);
		$this->db->where('jenis_id', 51);
		//$this->db->where('id_cabang', $id_cab);

		$query_sblm = $this->db->get();
		$khusus_1 = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$khusus_1 = ($row_sblm->jml_khusus_1_debet - $row_sblm->jml_khusus_1_kredit);
		}
		return $khusus_1;
	}

	function get_khusus_2() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(Debet) AS jml_khusus_2_debet, SUM(Kredit) AS jml_khusus_2_kredit');
		$this->db->from('v_rekap_simpanan');
		$thn = date('Y');
		$bln = date('m');
		$blnn = date('m', strtotime('-1 month', strtotime( $bln )));
		
		//$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$blnn."' ";
		$where = "DATE(tgl_transaksi) < '".$thn."-".$bln."-01' ";

		$this->db->where($where);
		$this->db->where('jenis_id', 52);
		//$this->db->where('id_cabang', $id_cab);

		$query_sblm = $this->db->get();
		$khusus_2 = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$khusus_2 = ($row_sblm->jml_khusus_2_debet - $row_sblm->jml_khusus_2_kredit);
		}
		return $khusus_2;
	}

	function get_wajib_penerimaan() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(Debet) AS jml_wajib_debet');
		$this->db->from('v_rekap_simpanan');
		$thn = date('Y');
		$bln = date('m');
		
		$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$bln."' ";

		$this->db->where($where);
		$this->db->where('jenis_id', 41);
		//$this->db->where('id_cabang', $id_cab);

		$query_sblm = $this->db->get();
		$wajib_penerimaan = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$wajib_penerimaan = ($row_sblm->jml_wajib_debet);
		}
		return $wajib_penerimaan;
	}

	function get_khusus_1_penerimaan() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(Debet) AS jml_khusus_1_debet');
		$this->db->from('v_rekap_simpanan');
		$thn = date('Y');
		$bln = date('m');
		
		$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$bln."' ";

		$this->db->where($where);
		$this->db->where('jenis_id', 51);
		//$this->db->where('id_cabang', $id_cab);

		$query_sblm = $this->db->get();
		$khusus_1_penerimaan = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$khusus_1_penerimaan = ($row_sblm->jml_khusus_1_debet);
		}
		return $khusus_1_penerimaan;
	}

	function get_khusus_2_penerimaan() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(Debet) AS jml_khusus_2_debet');
		$this->db->from('v_rekap_simpanan');
		$thn = date('Y');
		$bln = date('m');
		
		$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$bln."' ";

		$this->db->where($where);
		$this->db->where('jenis_id', 52);
		//$this->db->where('id_cabang', $id_cab);

		$query_sblm = $this->db->get();
		$khusus_2_penerimaan = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$khusus_2_penerimaan = ($row_sblm->jml_khusus_2_debet);
		}
		return $khusus_2_penerimaan;
	}

	function get_wajib_penarikan() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(Kredit) AS jml_wajib_kredit');
		$this->db->from('v_rekap_simpanan');
		$thn = date('Y');
		$bln = date('m');
		
		$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$bln."' ";

		$this->db->where($where);
		$this->db->where('jenis_id', 41);
		//$this->db->where('id_cabang', $id_cab);

		$query_sblm = $this->db->get();
		$wajib_penarikan = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$wajib_penarikan = ($row_sblm->jml_wajib_kredit);
		}
		return $wajib_penarikan;
	}

	function get_khusus_1_penarikan() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(Kredit) AS jml_khusus_1_kredit');
		$this->db->from('v_rekap_simpanan');
		$thn = date('Y');
		$bln = date('m');
		
		$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$bln."' ";

		$this->db->where($where);
		$this->db->where('jenis_id', 51);
		//$this->db->where('id_cabang', $id_cab);

		$query_sblm = $this->db->get();
		$khusus_1_penarikan = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$khusus_1_penarikan = ($row_sblm->jml_khusus_1_kredit);
		}
		return $khusus_1_penarikan;
	}

	function get_khusus_2_penarikan() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(Kredit) AS jml_khusus_2_kredit');
		$this->db->from('v_rekap_simpanan');
		$thn = date('Y');
		$bln = date('m');
		
		$where = "YEAR(tgl_transaksi) = '".$thn."' AND  MONTH(tgl_transaksi) = '".$bln."' ";

		$this->db->where($where);
		$this->db->where('jenis_id', 52);
		//$this->db->where('id_cabang', $id_cab);

		$query_sblm = $this->db->get();
		$khusus_2_penarikan = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$khusus_2_penarikan = ($row_sblm->jml_khusus_2_kredit);
		}
		return $khusus_2_penarikan;
	}

	//menghitung jumlah pinjaman bulan lalu
	function get_jml_pinjaman_bulan_lalu() {
		// SALDO SEBELUM NYA
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(tagihan) AS jml_total_bulan_lalu');
		$this->db->from('v_hitung_pinjaman');

			$thn = date('Y');
			$bln = date('Y-m');
			$blnn = date('m', strtotime('-1 month', strtotime( $bln )));
		
		//$where = "DATE(tgl_pinjam) LIKE '%".$thn."-".$blnn."%' ";
		$where = "YEAR(tgl_pinjam) = '".$thn."' AND  MONTH(tgl_pinjam) = '".$blnn."' ";

		$this->db->where($where);
		//$this->db->where('id_cabang', $id_cab);
		$this->db->where('lunas', 'Belum');

		$query_sblm = $this->db->get();
		$pinjaman_bulan_lalu = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$pinjaman_bulan_lalu = ($row_sblm->jml_total_bulan_lalu);
		}
		return $pinjaman_bulan_lalu;
	}

	//menghitung jumlah pinjaman bulan berjalan
	function get_jml_pinjaman_bulan() {
		// SALDO SEBELUM NYA
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(tagihan) AS jml_total_bulan');
		$this->db->from('v_hitung_pinjaman');

		
			$thn = date('Y');
			$bln = date('m');
		
		$where = "DATE(tgl_pinjam) LIKE '%".$thn."-".$bln."%' ";

		$this->db->where($where);
		//$this->db->where('id_cabang', $id_cab);
		$this->db->where('lunas', 'Belum');

		$query_bulan = $this->db->get();
		$pinjaman_bulan = 0;
		if($query_bulan->num_rows() > 0) {
			$row_bulan = $query_bulan->row();
			$pinjaman_bulan = ($row_bulan->jml_total_bulan);
		}
		return $pinjaman_bulan;
	}

	//menghitung jumlah bayar pokok bulan berjalan
	function get_jml_pokok_bulan() {
		// SALDO SEBELUM NYA
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah_bayar) as total_bayar');
		$this->db->from('v_rekap_angsuran');

		
			$thn = date('Y');
			$bln = date('m');
		
		$where = "DATE(tgl_bayar) LIKE '%".$thn."-".$bln."%' ";

		$this->db->where($where);
		//$this->db->where('id_cabang', $id_cab);
		//$this->db->where('lunas', 'Belum');

		$query_pokok = $this->db->get();
		$pinjaman_pokok = 0;
		if($query_pokok->num_rows() > 0) {
			$row_pokok = $query_pokok->row();
			$pinjaman_pokok = ($row_pokok->total_bayar);
		}
		return $pinjaman_pokok;
	}

	//menghitung jumlah angsuran
	function get_jml_angsuran() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah_bayar) AS jml_total');
		$this->db->from('v_rekap_angsuran');
		//$this->db->where('id_cabang', $id_cab);

		//$tgl_dari = date('Y') . '-01-01';
		//$tgl_samp = date('Y') . '-12-31';
		
		//$this->db->where('DATE(tgl_bayar) >= ', ''.$tgl_dari.'');
		//$this->db->where('DATE(tgl_bayar) <= ', ''.$tgl_samp.'');

		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_denda() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(denda_rp) AS total_denda');
		$this->db->from('tbl_pinjaman_d');
		//$this->db->where('id_cabang', $id_cab);

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_bayar) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_bayar) <= ', ''.$tgl_samp.'');

		$query = $this->db->get();
		return $query->row();
	}

	function get_peminjam_bln_ini() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('v_hitung_pinjaman');
		//$this->db->where('lunas','Belum');

		//$this->db->where('id_cabang', $id_cab);

		//$thn = date('Y');			
		//$bln = date('m');			
		//$where = "YEAR(tgl_pinjam) = '".$thn."' AND  MONTH(tgl_pinjam) = '".$bln."' ";
		//$this->db->where($where);

		$query = $this->db->get();
		return $query->num_rows();
	}

	//menghitung jumlah kas debet
	function get_jml_debet() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('tbl_trans_kas');
		//$this->db->where('id_cabang', $id_cab);
		$this->db->where('dk', 'D');
		$thn = date('Y');			
		$bln = date('m');			
		$where = "YEAR(tgl_catat) = '".$thn."' AND MONTH(tgl_catat) = '".$bln."' ";
		$this->db->where($where);
		$query = $this->db->get();
		return $query->row();
	}

	//menghitung jumlah kas kredit
	function get_jml_kredit() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('tbl_trans_kas');
		//$this->db->where('id_cabang', $id_cab);
		$this->db->where('dk', 'K');
		$thn = date('Y');			
		$bln = date('m');			
		$where = "YEAR(tgl_catat) = '".$thn."' AND MONTH(tgl_catat) = '".$bln."' ";
		$this->db->where($where);
		$query = $this->db->get();
		return $query->row();
	}

	function get_saldo_debet_sblm() {
		// SALDO SEBELUM NYA
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah) AS jum_debet');
		$this->db->from('tbl_trans_kas');

		if(isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
			$blnn = date('m', strtotime('-1 month', strtotime($bln)));
		}		
		//$where = "YEAR(tgl_catat) = '".$thn."' AND MONTH(tgl_catat) = '".$blnn."' ";
		$where = "DATE(tgl_catat) < '".$thn."-".$bln."-01' ";
		$this->db->where($where);
		//$this->db->where('id_cabang', $id_cab);
		$this->db->where('dk', 'D');
		$query_sblm = $this->db->get();
		$saldo_debet_sblm = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$saldo_debet_sblm = ($row_sblm->jum_debet);
		}
		return $saldo_debet_sblm;
	}

	function get_saldo_kredit_sblm() {
		// SALDO SEBELUM NYA
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah) AS jum_kredit');
		$this->db->from('tbl_trans_kas');

		if(isset($_REQUEST['periode'])) {
			$tgl_arr = explode('-', $_REQUEST['periode']);
			$thn = $tgl_arr[0];
			$bln = $tgl_arr[1];
		} else {
			$thn = date('Y');
			$bln = date('m');
			$blnn = date('m', strtotime('-1 month', strtotime($bln)));
		}		
		//$where = "YEAR(tgl_catat) = '".$thn."' AND MONTH(tgl_catat) = '".$blnn."' ";
		$where = "DATE(tgl_catat) < '".$thn."-".$bln."-01' ";

		$this->db->where($where);
		//$this->db->where('id_cabang', $id_cab);
		$this->db->where('dk', 'K');
		$query_sblm = $this->db->get();
		$saldo_kredit_sblm = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$saldo_kredit_sblm = ($row_sblm->jum_kredit);
		}
		return $saldo_kredit_sblm;
	}

	function get_saldo_debet() {
		$this->db->select('SUM(jumlah) AS jum_debet');
		$this->db->from('tbl_trans_kas');
		$this->db->where('dk', 'D');
		$query_sblm = $this->db->get();
		$saldo_debet_sblm = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$saldo_debet_sblm = ($row_sblm->jum_debet);
		}
		return $saldo_debet_sblm;
	}

	function get_saldo_kredit() {
		$this->db->select('SUM(jumlah) AS jum_kredit');
		$this->db->from('tbl_trans_kas');
		$this->db->where('dk', 'K');
		$query_sblm = $this->db->get();
		$saldo_kredit_sblm = 0;
		if($query_sblm->num_rows() > 0) {
			$row_sblm = $query_sblm->row();
			$saldo_kredit_sblm = ($row_sblm->jum_kredit);
		}
		return $saldo_kredit_sblm;
	}

	//hitung jumlah user aktif
	function get_user_aktif() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('tbl_user');
		$this->db->where('aktif','Y');
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->num_rows();
	}

	//hitung jumlah anggota tdk aktif
	function get_user_non() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('tbl_user');
		$this->db->where('aktif','N');
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		return $query->num_rows();
	}
}