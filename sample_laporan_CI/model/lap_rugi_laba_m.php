<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Lap_rugi_laba_m extends CI_Model {

	public function __construct() {
		parent::__construct();
	}

	function get_data_penjualan() {
		$this->db->select('*');
		$this->db->from('v_penjualan');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_catat) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_catat) <= ', ''.$tgl_samp.'');
		//$this->db->where('id_cabang', $id_cab);
		$this->db->group_by("jns_trans");
		$query = $this->db->get();
		if($query->num_rows() > 0) {
			$out = $query->result();
			return $out;
		} else {
			return array();
		}
	}

	function get_data_pembelian() {
		$id_akun = array('117','118','119','120');
		$this->db->select('*');
		$this->db->from('v_pembelian');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_catat) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_catat) <= ', ''.$tgl_samp.'');
		$this->db->where_in('kode_trans', $id_akun);
		$this->db->group_by("jns_trans");
		$query = $this->db->get();
		if($query->num_rows() > 0) {
			$out = $query->result();
			return $out;
		} else {
			return array();
		}
	}

	function get_persediaan_awal() {
		//$id_akun = array('122','123','124','52');
		$this->db->select('*');
		$this->db->from('v_persediaan_awal');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_catat) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_catat) <= ', ''.$tgl_samp.'');
		//$this->db->where_in('kode_trans', $id_akun);
		//$this->db->group_by("jns_trans");
		$query = $this->db->get();
		if($query->num_rows() > 0) {
			$out = $query->result();
			return $out;
		} else {
			return array();
		}
	}

	function get_data_biaya_usaha() {
		//$id_akun = array('122','123','124','52');
		$this->db->select('*');
		$this->db->from('v_biaya_usaha');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_catat) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_catat) <= ', ''.$tgl_samp.'');
		//$this->db->where_in('kode_trans', $id_akun);
		$this->db->group_by("kode_trans");
		$query = $this->db->get();
		if($query->num_rows() > 0) {
			$out = $query->result();
			return $out;
		} else {
			return array();
		}
	}

	function get_jml_biaya_usaha_tahun() {
		//$id_akun = array('117','118','119','120');
		$this->db->select('
			SUM(Jan) AS jml_total_jan,
			SUM(Feb) AS jml_total_feb,
			SUM(Mar) AS jml_total_mar,
			SUM(Apr) AS jml_total_apr,
			SUM(May) AS jml_total_may,
			SUM(Jun) AS jml_total_jun,
			SUM(Jul) AS jml_total_jul,
			SUM(Aug) AS jml_total_aug,
			SUM(Sep) AS jml_total_sep,
			SUM(Oct) AS jml_total_oct,
			SUM(Nov) AS jml_total_nov,
			SUM(`Dec`) AS jml_total_dec');
		$this->db->from('v_biaya_usaha');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_catat) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_catat) <= ', ''.$tgl_samp.'');
		//$this->db->group_by("kode_trans");
		
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_penjualan() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(TOTAL) AS jml_total');
		$this->db->from('v_penjualan');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_catat) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_catat) <= ', ''.$tgl_samp.'');
		//$this->db->where('id_cabang', $id_cab);
		
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_biaya_tahun() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(TOTAL) AS jml_total');
		$this->db->from('v_biaya_usaha');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_catat) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_catat) <= ', ''.$tgl_samp.'');
		//$this->db->where('id_cabang', $id_cab);
		
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_biaya_usaha() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(TOTAL) AS jml_total');
		$this->db->from('v_biaya_usaha');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_catat) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_catat) <= ', ''.$tgl_samp.'');
		//$this->db->where('id_cabang', $id_cab);
		
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_pembelian() {
		$id_akun = array('117','118','119','120');
		$this->db->select('SUM(TOTAL) AS jml_total');
		$this->db->from('v_pembelian');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_catat) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_catat) <= ', ''.$tgl_samp.'');
		$this->db->where('kode_trans', 117);
		
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_pembelian_akhir() {
		//$id_akun = array('117','118','119','120');
		$this->db->select('SUM(TOTAL) AS jml_total');
		$this->db->from('v_pembelian');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_catat) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_catat) <= ', ''.$tgl_samp.'');
		$this->db->where('kode_trans', 121);
		
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_pembelian_akhir_tahun() {
		//$id_akun = array('117','118','119','120');
		$this->db->select('
			SUM(Jan) AS jml_total_jan,
			SUM(Feb) AS jml_total_feb,
			SUM(Mar) AS jml_total_mar,
			SUM(Apr) AS jml_total_apr,
			SUM(May) AS jml_total_may,
			SUM(Jun) AS jml_total_jun,
			SUM(Jul) AS jml_total_jul,
			SUM(Aug) AS jml_total_aug,
			SUM(Sep) AS jml_total_sep,
			SUM(Oct) AS jml_total_oct,
			SUM(Nov) AS jml_total_nov,
			SUM(`Dec`) AS jml_total_dec');
		$this->db->from('v_pembelian');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_catat) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_catat) <= ', ''.$tgl_samp.'');
		$this->db->where('kode_trans', 121);
		
		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_penjualan_tahun() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('
			SUM(Jan) AS jml_total_jan,
			SUM(Feb) AS jml_total_feb,
			SUM(Mar) AS jml_total_mar,
			SUM(Apr) AS jml_total_apr,
			SUM(May) AS jml_total_may,
			SUM(Jun) AS jml_total_jun,
			SUM(Jul) AS jml_total_jul,
			SUM(Aug) AS jml_total_aug,
			SUM(Sep) AS jml_total_sep,
			SUM(Oct) AS jml_total_oct,
			SUM(Nov) AS jml_total_nov,
			SUM(`Dec`) AS jml_total_dec');
		$this->db->from('v_penjualan');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_catat) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_catat) <= ', ''.$tgl_samp.'');
		//$this->db->where('id_cabang', $id_cab);

		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_pembelian_tahun() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('
			SUM(Jan) AS jml_total_jan,
			SUM(Feb) AS jml_total_feb,
			SUM(Mar) AS jml_total_mar,
			SUM(Apr) AS jml_total_apr,
			SUM(May) AS jml_total_may,
			SUM(Jun) AS jml_total_jun,
			SUM(Jul) AS jml_total_jul,
			SUM(Aug) AS jml_total_aug,
			SUM(Sep) AS jml_total_sep,
			SUM(Oct) AS jml_total_oct,
			SUM(Nov) AS jml_total_nov,
			SUM(`Dec`) AS jml_total_dec');
		$this->db->from('v_pembelian');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_catat) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_catat) <= ', ''.$tgl_samp.'');
		$this->db->where('kode_trans', 117);

		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_awal_pembelian_tahun() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('
			SUM(Jan) AS jml_total_jan,
			SUM(Feb) AS jml_total_feb,
			SUM(Mar) AS jml_total_mar,
			SUM(Apr) AS jml_total_apr,
			SUM(May) AS jml_total_may,
			SUM(Jun) AS jml_total_jun,
			SUM(Jul) AS jml_total_jul,
			SUM(Aug) AS jml_total_aug,
			SUM(Sep) AS jml_total_sep,
			SUM(Oct) AS jml_total_oct,
			SUM(Nov) AS jml_total_nov,
			SUM(`Dec`) AS jml_total_dec');
		$this->db->from('v_pembelian');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_catat) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_catat) <= ', ''.$tgl_samp.'');
		$this->db->where('kode_trans', 9);

		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_bus_tahun_pajak() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('
			(SUM(Jan)*2)/100 AS jml_total_jan_pajak,
			(SUM(Feb)*2)/100 AS jml_total_feb_pajak,
			(SUM(Mar)*2)/100 AS jml_total_mar_pajak,
			(SUM(Apr)*2)/100 AS jml_total_apr_pajak,
			(SUM(May)*2)/100 AS jml_total_may_pajak,
			(SUM(Jun)*2)/100 AS jml_total_jun_pajak,
			(SUM(Jul)*2)/100 AS jml_total_jul_pajak,
			(SUM(Aug)*2)/100 AS jml_total_aug_pajak,
			(SUM(Sep)*2)/100 AS jml_total_sep_pajak,
			(SUM(Oct)*2)/100 AS jml_total_oct_pajak,
			(SUM(Nov)*2)/100 AS jml_total_nov_pajak,
			(SUM(`Dec`)*2)/100 AS jml_total_dec_pajak');
		$this->db->from('v_angkutan_karyawan');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_catat) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_catat) <= ', ''.$tgl_samp.'');
		//$this->db->where('id_cabang', $id_cab);

		$query = $this->db->get();
		return $query->row();
	}

	function get_data_operasional() {
		$this->db->select('*');
		$this->db->from('v_biaya_operasional');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_catat) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_catat) <= ', ''.$tgl_samp.'');
		//$this->db->where('id_cabang', $id_cab);

		$query = $this->db->get();
		if($query->num_rows() > 0) {
			$out = $query->result();
			return $out;
		} else {
			return array();
		}
	}

	function get_jml_awal() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('v_persediaan_awal');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_catat) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_catat) <= ', ''.$tgl_samp.'');
		//$this->db->where('kode_trans', 9);

		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_awal_tahun() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('
			SUM(Jan) AS jml_total_jan,
			SUM(Feb) AS jml_total_feb,
			SUM(Mar) AS jml_total_mar,
			SUM(Apr) AS jml_total_apr,
			SUM(May) AS jml_total_may,
			SUM(Jun) AS jml_total_jun,
			SUM(Jul) AS jml_total_jul,
			SUM(Aug) AS jml_total_aug,
			SUM(Sep) AS jml_total_sep,
			SUM(Oct) AS jml_total_oct,
			SUM(Nov) AS jml_total_nov,
			SUM(`Dec`) AS jml_total_dec');
		$this->db->from('v_pembelian');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_catat) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_catat) <= ', ''.$tgl_samp.'');
		$this->db->where('kode_trans', 9);

		$query = $this->db->get();
		return $query->row();
	}

	function get_jml_admin_tahun() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('
			SUM(Jan) AS jml_total_jan,
			SUM(Feb) AS jml_total_feb,
			SUM(Mar) AS jml_total_mar,
			SUM(Apr) AS jml_total_apr,
			SUM(May) AS jml_total_may,
			SUM(Jun) AS jml_total_jun,
			SUM(Jul) AS jml_total_jul,
			SUM(Aug) AS jml_total_aug,
			SUM(Sep) AS jml_total_sep,
			SUM(Oct) AS jml_total_oct,
			SUM(Nov) AS jml_total_nov,
			SUM(`Dec`) AS jml_total_dec');
		$this->db->from('v_admin_umum');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_catat) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_catat) <= ', ''.$tgl_samp.'');
		//$this->db->where('id_cabang', $id_cab);

		$query = $this->db->get();
		return $query->row();
	}

	function get_data_admin() {
		$this->db->select('*');
		$this->db->from('v_admin_umum');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_catat) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_catat) <= ', ''.$tgl_samp.'');
		//$this->db->where('id_cabang', $id_cab);

		$query = $this->db->get();
		if($query->num_rows() > 0) {
			$out = $query->result();
			return $out;
		} else {
			return array();
		}
	}

	function get_jml_admin() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(TOTAL) AS jml_total');
		$this->db->from('v_admin_umum');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_catat) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_catat) <= ', ''.$tgl_samp.'');
		//$this->db->where('id_cabang', $id_cab);

		$query = $this->db->get();
		return $query->row();
	}

	//menghitung jumlah tagihan
	function get_jml_pinjaman() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah) AS jml_total');
		$this->db->from('v_hitung_pinjaman');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_pinjam) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_pinjam) <= ', ''.$tgl_samp.'');
		//$this->db->where('id_cabang', $id_cab);

		$query = $this->db->get();
		return $query->row();
	}

	// jumlah yg harus diangsur
	function get_jml_estimasi_angsur() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(ags_per_bulan * lama_angsuran) AS jml_total');
		$this->db->from('v_hitung_pinjaman');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_pinjam) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_pinjam) <= ', ''.$tgl_samp.'');
		//$this->db->where('id_cabang', $id_cab);

		$query = $this->db->get();
		return $query->row();
	}

	//jumlah biaya adm
	function get_jml_biaya_adm() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(biaya_adm) AS jml_total');
		$this->db->from('v_rekap_det');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_pinjam) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_pinjam) <= ', ''.$tgl_samp.'');
		//$this->db->where('id_cabang', $id_cab);

		$query = $this->db->get();
		return $query->row();
	}

	//jumlah bunga
	function get_jml_bunga() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(tbl_pinjaman_d.bunga) AS jml_total');
		$this->db->from('tbl_pinjaman_d');
		$this->db->join('tbl_pinjaman_h', 'tbl_pinjaman_h.id = tbl_pinjaman_d.pinjam_id', 'LEFT');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tbl_pinjaman_h.tgl_pinjam) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tbl_pinjaman_h.tgl_pinjam) <= ', ''.$tgl_samp.'');
		//$this->db->where('tbl_pinjaman_h.id_cabang', $id_cab);

		$query = $this->db->get();
		return $query->row();
	}


	//menghitung jumlah tagihan
	function get_jml_tagihan() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(tagihan) AS jml_total');
		$this->db->from('v_hitung_pinjaman');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_pinjam) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_pinjam) <= ', ''.$tgl_samp.'');
		//$this->db->where('id_cabang', $id_cab);

		$query = $this->db->get();
		return $query->row();
	}

	//menghitung jumlah angsuran
	function get_jml_angsuran() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah_bayar) AS jml_total');
		$this->db->from('tbl_pinjaman_d');
		$this->db->join('tbl_pinjaman_h', 'tbl_pinjaman_h.id = tbl_pinjaman_d.pinjam_id', 'LEFT');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tbl_pinjaman_h.tgl_pinjam) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tbl_pinjaman_h.tgl_pinjam) <= ', ''.$tgl_samp.'');
		//$this->db->where('tbl_pinjaman_h.id_cabang', $id_cab);

		$query = $this->db->get();
		return $query->row();
	}

	//menghitung jumlah denda harus dibayar
	function get_jml_denda() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(tbl_pinjaman_d.denda_rp) AS total_denda');
		$this->db->from('tbl_pinjaman_d');
		$this->db->join('tbl_pinjaman_h', 'tbl_pinjaman_h.id = tbl_pinjaman_d.pinjam_id', 'LEFT');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tbl_pinjaman_h.tgl_pinjam) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tbl_pinjaman_h.tgl_pinjam) <= ', ''.$tgl_samp.'');
		//$this->db->where('tbl_pinjaman_h.id_cabang', $id_cab);

		$query = $this->db->get();
		return $query->row();
	}

	//hitung jumlah peminjam aktif
	function get_peminjam_aktif() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('v_hitung_pinjaman');
		
		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_pinjam) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_pinjam) <= ', ''.$tgl_samp.'');
		//$this->db->where('id_cabang', $id_cab);

		$query = $this->db->get();
		return $query->num_rows();
	}

	//hitung jumlah peminjam lunas
	function get_peminjam_lunas() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('v_hitung_pinjaman');
		$this->db->where('lunas','Lunas');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_pinjam) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_pinjam) <= ', ''.$tgl_samp.'');
		//$this->db->where('id_cabang', $id_cab);

		$query = $this->db->get();
		return $query->num_rows();
	}

	//hitung jumlah peminjam belum lunas
	function get_peminjam_belum() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('v_hitung_pinjaman');
		$this->db->where('lunas','Belum');

		if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl_pinjam) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl_pinjam) <= ', ''.$tgl_samp.'');
		//$this->db->where('id_cabang', $id_cab);

		$query = $this->db->get();
		return $query->num_rows();
	}

	function get_data_akun_dapat() {
		$this->db->select('*');
		$this->db->from('jns_akun');
		$this->db->where('aktif', 'Y');
		$this->db->where('laba_rugi', 'PENDAPATAN');
		$this->db->where('CHAR_LENGTH(kd_aktiva) >', '1', FALSE);
		$this->db->_protect_identifiers = FALSE;
		$this->db->order_by('LPAD(kd_aktiva, 1, 0) ASC, LPAD(kd_aktiva, 5, 1)', 'ASC');
		$this->db->_protect_identifiers = TRUE;
		$query = $this->db->get();
		if($query->num_rows() > 0) {
			$out = $query->result();
			return $out;
		} else {
			return array();
		}
	}

	function get_data_akun_biaya() {
		$this->db->select('*');
		$this->db->from('jns_akun');
		$this->db->where('aktif', 'Y');
		$this->db->where('laba_rugi', 'BIAYA');
		$this->db->where('CHAR_LENGTH(kd_aktiva) >', '1', FALSE);
		$this->db->_protect_identifiers = FALSE;
		$this->db->order_by('LPAD(kd_aktiva, 1, 0) ASC, LPAD(kd_aktiva, 5, 1)', 'ASC');
		$query = $this->db->get();
		if($query->num_rows() > 0) {
			$out = $query->result();
			return $out;
		} else {
			return array();
		}
	}

	function get_jml_akun($akun) {
		$id_cab = $this->session->userdata('id_cabang');
			$this->db->select('SUM(debet) AS jum_debet, SUM(kredit) AS jum_kredit');
			$this->db->from('v_transaksi');
			$this->db->where('transaksi', $akun);

			if(isset($_REQUEST['tgl_dari']) && isset($_REQUEST['tgl_samp'])) {
			$tgl_dari = $_REQUEST['tgl_dari'];
			$tgl_samp = $_REQUEST['tgl_samp'];
		} else {
			$tgl_dari = date('Y') . '-01-01';
			$tgl_samp = date('Y') . '-12-31';
		}
		$this->db->where('DATE(tgl) >= ', ''.$tgl_dari.'');
		$this->db->where('DATE(tgl) <= ', ''.$tgl_samp.'');
		//$this->db->where('id_cabang', $id_cab);

		$query = $this->db->get();
		return $query->row();
	}
}