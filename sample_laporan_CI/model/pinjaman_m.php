<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pinjaman_m extends CI_Model {

	public function __construct() {
		parent::__construct();
	}


	function get_pengajuan() {
		$this->load->helper('fungsi');
		$offset = isset($_POST['offset']) ? $_POST['offset'] : 0;
		$limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
		$search = isset($_POST['search']) ? $_POST['search'] : '';
		$fr_jenis = isset($_POST['fr_jenis']) ? $_POST['fr_jenis'] : array();
		$fr_status = isset($_POST['fr_status']) ? $_POST['fr_status'] : array();
		$fr_bulan = isset($_POST['fr_bulan']) ? $_POST['fr_bulan'] : '';
		$tgl_dari = isset($_POST['tgl_dari']) ? $_POST['tgl_dari'] : '';
		$tgl_sampai = isset($_POST['tgl_sampai']) ? $_POST['tgl_sampai'] : '';
		
		//$where = " AND anggota_id = " . $user_id;
		$where = "";
		if($fr_bulan != '') {
			$bln_dari = date("Y-m-d", strtotime($fr_bulan . "-01 -1 month"));
			$bln_dari = substr($bln_dari, 0, 7) . '-21';
			$bln_samp = $fr_bulan . '-20';
			$where .=" AND DATE(tgl_input) >= '".$bln_dari."' ";
			$where .=" AND DATE(tgl_input) <= '".$bln_samp."' ";			
		} else {
			if($tgl_dari != '' && $tgl_sampai != '') {
				$where .=" AND DATE(tgl_input) >= '".$tgl_dari."' ";
				$where .=" AND DATE(tgl_input) <= '".$tgl_sampai."' ";
			}
		}

		if($this->session->userdata('level') == 'operator') {
			$where .= " AND (a.status = '1' OR a.status = '3') ";
		}

		//
		if (! empty($fr_jenis) ) {
			$where .= " AND (";
			$no = 1;
			foreach ($fr_jenis as $fr) {
				if($no > 1) {
					$where .= " OR ";
				}
				$where .= " a.jenis = '".$fr."' ";
				$no++;
			}
			$where .= ") ";
		}

		//
		if (! empty($fr_status) ) {
			$where .= " AND (";
			$no = 1;
			foreach ($fr_status as $fr) {
				if($no > 1) {
					$where .= " OR ";
				}
				$where .= " a.status = '".$fr."' ";
				$no++;
			}
			$where .= ") ";
		}

		$order_by = " ORDER BY tgl_input DESC";
		if ( isset($_POST['sort']) && isset($_POST['order']) ) {
			$order_by = " ORDER BY ".$_POST['sort']." ".$_POST['order']." ";
		}
		$sql_limit = " LIMIT ".$offset.",".$limit." ";
		//$id_cab = $this->session->userdata('id_cabang');
		$sql_tampil = "SELECT 
			a.id AS id, a.no_ajuan AS no_ajuan, a.ajuan_id AS ajuan_id, a.anggota_id AS anggota_id, b.no_ktp AS no_ktp, a.tgl_input AS tgl_input, c.pinjaman AS jenis, a.nominal AS nominal, a.lama_ags AS lama_ags, a.keterangan AS keterangan, a.status AS status, a.alasan AS alasan, a.tgl_update AS tgl_update, a.tgl_cair AS tgl_cair,
			b.identitas AS identitas, b.nama AS nama, b.departement AS departement
			FROM tbl_pengajuan AS a
			LEFT JOIN tbl_anggota AS b ON b.id = a.anggota_id join jns_pinjaman c on a.jenis=c.id
		 	WHERE 1=1 ".$where." ".$order_by." ".$sql_limit."";
		$query = $this->db->query($sql_tampil);
		$data_list = $query->result();

		$sql_total = "SELECT id FROM tbl_pengajuan AS a WHERE 1=1 ".$where." ";
		$query = $this->db->query($sql_total);
		$total = $query->num_rows();

		// 
		$data_list_i = array();
		foreach ($data_list as $key => $val) {
			$tgl_arr = explode(' ', $val->tgl_input);
			$tgl = $tgl_arr[0];
			$val->tgl_input_txt = jin_date_ina($tgl);
			$val->tgl_update_txt = jin_date_ina($tgl);
			$val->tgl_cair_txt = jin_date_ina($val->tgl_cair);
			$val->tgl_input = substr($val->tgl_input, 0, 16);
			$val->tgl_update = substr($val->tgl_update, 0, 16);
			$val->nominal = number_format($val->nominal);
			// sisa pinjaman
			$sisa_p = $this->get_sisa_pinjaman($val->anggota_id);
			$val->sisa_jml = number_format($sisa_p['sisa_jml']);
			$val->sisa_tagihan = number_format($sisa_p['sisa_tagihan']);
			$val->sisa_ags = number_format($sisa_p['sisa_ags']);
			$data_list_i[$key] = $val;
		}
		$out = array('rows' => $data_list_i, 'total' => $total);
		return $out;
	}

	function get_pengajuan_simpanan() {
		$this->load->helper('fungsi');
		$offset = isset($_POST['offset']) ? $_POST['offset'] : 0;
		$limit = isset($_POST['limit']) ? $_POST['limit'] : 10;
		$search = isset($_POST['search']) ? $_POST['search'] : '';
		$fr_jenis = isset($_POST['fr_jenis']) ? $_POST['fr_jenis'] : array();
		$fr_status = isset($_POST['fr_status']) ? $_POST['fr_status'] : array();
		$fr_bulan = isset($_POST['fr_bulan']) ? $_POST['fr_bulan'] : '';
		$tgl_dari = isset($_POST['tgl_dari']) ? $_POST['tgl_dari'] : '';
		$tgl_sampai = isset($_POST['tgl_sampai']) ? $_POST['tgl_sampai'] : '';
		
		//$where = " AND anggota_id = " . $user_id;
		$where = "";
		if($fr_bulan != '') {
			$bln_dari = date("Y-m-d", strtotime($fr_bulan . "-01 -1 month"));
			$bln_dari = substr($bln_dari, 0, 7) . '-21';
			$bln_samp = $fr_bulan . '-20';
			$where .=" AND DATE(tgl_input) >= '".$bln_dari."' ";
			$where .=" AND DATE(tgl_input) <= '".$bln_samp."' ";			
		} else {
			if($tgl_dari != '' && $tgl_sampai != '') {
				$where .=" AND DATE(tgl_input) >= '".$tgl_dari."' ";
				$where .=" AND DATE(tgl_input) <= '".$tgl_sampai."' ";
			}
		}

		if($this->session->userdata('level') == 'operator') {
			$where .= " AND (a.status = '1' OR a.status = '3') ";
		}

		//
		if (! empty($fr_jenis) ) {
			$where .= " AND (";
			$no = 1;
			foreach ($fr_jenis as $fr) {
				if($no > 1) {
					$where .= " OR ";
				}
				$where .= " a.jenis = '".$fr."' ";
				$no++;
			}
			$where .= ") ";
		}

		//
		if (! empty($fr_status) ) {
			$where .= " AND (";
			$no = 1;
			foreach ($fr_status as $fr) {
				if($no > 1) {
					$where .= " OR ";
				}
				$where .= " a.status = '".$fr."' ";
				$no++;
			}
			$where .= ") ";
		}

		$order_by = " ORDER BY tgl_input DESC";
		if ( isset($_POST['sort']) && isset($_POST['order']) ) {
			$order_by = " ORDER BY ".$_POST['sort']." ".$_POST['order']." ";
		}
		$sql_limit = " LIMIT ".$offset.",".$limit." ";
		//$id_cab = $this->session->userdata('id_cabang');
		$sql_tampil = "SELECT 
			a.id AS id, a.no_ajuan AS no_ajuan, a.ajuan_id AS ajuan_id, a.anggota_id AS anggota_id, a.tgl_input AS tgl_input, c.jns_simpan AS jenis, a.nominal AS nominal, a.lama_ags AS lama_ags, a.keterangan AS keterangan, a.status AS status, a.alasan AS alasan, a.tgl_update AS tgl_update, a.tgl_cair AS tgl_cair,
			b.identitas AS identitas, b.nama AS nama, b.departement AS departement
			FROM tbl_pengajuan_penarikan AS a
			LEFT JOIN tbl_anggota AS b ON b.id = a.anggota_id
			LEFT JOIN jns_simpan AS c ON a.jenis = c.id
		 	WHERE 1=1 ".$where." ".$order_by." ".$sql_limit."";
		$query = $this->db->query($sql_tampil);
		$data_list = $query->result();

		$sql_total = "SELECT id FROM tbl_pengajuan_penarikan AS a WHERE 1=1 ".$where." ";
		$query = $this->db->query($sql_total);
		$total = $query->num_rows();

		// 
		$data_list_i = array();
		foreach ($data_list as $key => $val) {
			$tgl_arr = explode(' ', $val->tgl_input);
			$tgl = $tgl_arr[0];
			$val->tgl_input_txt = jin_date_ina($tgl);
			$val->tgl_update_txt = jin_date_ina($tgl);
			$val->tgl_cair_txt = jin_date_ina($val->tgl_cair);
			$val->tgl_input = substr($val->tgl_input, 0, 16);
			$val->tgl_update = substr($val->tgl_update, 0, 16);
			$val->nominal = number_format($val->nominal);

			// sisa pinjaman
			$sisa_p = $this->get_sisa_simpanans($val->anggota_id);
			$val->sisa_jml = number_format($sisa_p['sisa_jml']);
			$val->sisa_tagihan = number_format($sisa_p['sisa_tagihan']);
			$val->sisa_ags = number_format($sisa_p['sisa_ags']);
			//$val->id_cabang = $id_cab;

			$data_list_i[$key] = $val;
		}

		$out = array('rows' => $data_list_i, 'total' => $total);
		return $out;
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
					//if($key == 'A') { $pair['id'] = $val; }
					//if($key == 'B') { $pair['no_ktp'] = $val; }
					if($key == 'A') { $pair['tgl_bayar'] = $val; }
					if($key == 'B') { $pair['pinjam_id'] = $val; }
					if($key == 'C') { $pair['angsuran_ke'] = $val; }
					if($key == 'D') { $pair['jumlah_bayar'] = $val; }
					if($key == 'E') { $pair['bunga'] = $val; }
					if($key == 'F') { $pair['denda_rp'] = ''; }
					if($key == 'G') { $pair['biaya_adm'] = ''; }
					if($key == 'H') { $pair['terlambat'] = ''; }
					if($key == 'I') { $pair['ket_bayar'] = 'Angsuran'; }
					if($key == 'J') { $pair['dk'] = 'D'; }
					if($key == 'K') { $pair['kas_id'] = 1; }
					if($key == 'L') { $pair['jns_trans'] = 48; }
					//if($key == 'N') { $pair['update_data'] = $val; }
					//if($key == 'O') { $pair['user_name'] = $val; }
					//if($key == 'P') { $pair['keterangan'] = $val; }
					//if($key == 'Q') { $pair['contoh'] = $val; }
				}
				//$pair['id_cabang'] = $id_cab;
				$pair_arr[] = $pair;
			}
			//var_dump($pair_arr);
			//return 1;
			return $this->db->insert_batch('tbl_pinjaman_d', $pair_arr);
		} else {
			return FALSE;
		}
	}

	function get_pengajuan_cetak() {
		$this->load->helper('fungsi');
		$fr_jenis = isset($_REQUEST['fr_jenis']) ? explode(',', $_REQUEST['fr_jenis']) : array();
		$fr_status = isset($_REQUEST['fr_status']) ? explode(',', $_REQUEST['fr_status']) : array();
		$fr_bulan = isset($_REQUEST['fr_bulan']) ? $_REQUEST['fr_bulan'] : '';
		$tgl_dari = isset($_REQUEST['tgl_dari']) ? $_REQUEST['tgl_dari'] : '';
		$tgl_sampai = isset($_REQUEST['tgl_sampai']) ? $_REQUEST['tgl_sampai'] : '';

		$where = "";

		if($fr_bulan != '') {
			$bln_dari = date("Y-m-d", strtotime($fr_bulan . "-01 -1 month"));
			$bln_dari = substr($bln_dari, 0, 7) . '-21';
			$bln_samp = $fr_bulan . '-20';
			$where .=" AND DATE(tgl_input) >= '".$bln_dari."' ";
			$where .=" AND DATE(tgl_input) <= '".$bln_samp."' ";			
		} else {
			if($tgl_dari != '' && $tgl_sampai != '') {
				$where .=" AND DATE(tgl_input) >= '".$tgl_dari."' ";
				$where .=" AND DATE(tgl_input) <= '".$tgl_sampai."' ";
			}
		}

		if($this->session->userdata('level') == 'operator') {
			$where .= " AND (a.status = '1' OR a.status = '3') ";
		}
		$fr_jenis = array_diff($fr_jenis, array(NULL)); // NULL / FALSE / ''
		$fr_status = array_diff($fr_status, array(NULL)); // NULL / FALSE / ''
		//return $fr_jenis;
		//
		if (! empty($fr_jenis)) {
			$where .= " AND (";
			$no = 1;
			foreach ($fr_jenis as $fr) {
				if($fr != '') {
					if($no > 1) {
						$where .= " OR ";
					}
					$where .= " a.jenis = '".$fr."' ";
					$no++;
				}
			}
			$where .= ") ";
		}

		//
		if (! empty($fr_status)) {
			$where .= " AND (";
			$no = 1;
			foreach ($fr_status as $fr) {
				if($fr != '') {
					if($no > 1) {
						$where .= " OR ";
					}
					$where .= " a.status = '".$fr."' ";
					$no++;
				}
			}
			$where .= ") ";
		}		
		//return $where;
		$order_by = " ORDER BY tgl_input ASC";
		//$sql_limit = " LIMIT ".$offset.",".$limit." ";
		//$id_cab = $this->session->userdata('id_cabang');
		$sql_tampil = "SELECT 
			a.id AS id, a.no_ajuan AS no_ajuan, a.ajuan_id AS ajuan_id, a.anggota_id AS anggota_id, a.tgl_input AS tgl_input, a.jenis AS jenis, a.nominal AS nominal, a.lama_ags AS lama_ags, a.keterangan AS keterangan, a.status AS status, a.alasan AS alasan, a.tgl_update AS tgl_update, a.tgl_cair AS tgl_cair,
			b.identitas AS identitas, b.nama AS nama, b.departement AS departement
			FROM tbl_pengajuan AS a
			LEFT JOIN tbl_anggota AS b ON b.id = a.anggota_id
		 	WHERE 1=1 ".$where." ".$order_by." ";
		$query = $this->db->query($sql_tampil);
		$data_list = $query->result();

		$sql_total = "SELECT id FROM tbl_pengajuan AS a WHERE 1=1 ".$where." ";
		$query = $this->db->query($sql_total);
		$total = $query->num_rows();

		// 
		$data_list_i = array();
		foreach ($data_list as $key => $val) {
			$tgl_arr = explode(' ', $val->tgl_input);
			$tgl = $tgl_arr[0];
			$val->tgl_input_txt = jin_date_ina($tgl, 'pendek');
			$val->tgl_update_txt = jin_date_ina($tgl);
			$val->tgl_cair_txt = jin_date_ina($val->tgl_cair);
			$val->tgl_input = substr($val->tgl_input, 0, 16);
			$val->tgl_update = substr($val->tgl_update, 0, 16);
			$val->nominal = number_format($val->nominal);

			// sisa pinjaman
			$sisa_p = $this->get_sisa_pinjaman($val->anggota_id);
			$val->sisa_jml = number_format($sisa_p['sisa_jml']);
			$val->sisa_tagihan = number_format($sisa_p['sisa_tagihan']);
			$val->sisa_ags = number_format($sisa_p['sisa_ags']);

			$data_list_i[$key] = $val;
		}

		$out = array('rows' => $data_list_i, 'total' => $total);
		return $out;
		//return $where;
	}

	function get_sisa_pinjaman($anggota_id) {
		$this->db->select('*');
		$this->db->from('v_hitung_pinjaman');
		$this->db->where('lunas', 'Belum');
		$this->db->where('anggota_id', $anggota_id);
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();

		$out = array();
		$out['sisa_jml'] 		= 0;
		$out['sisa_tagihan'] 	= 0;
		$out['sisa_ags'] 		= 0;
		if($query->num_rows() > 0) {
			$result = $query->result();
			$item = 0;
			$sisa_tagihan = 0;
			$sisa_ags = 0;
			foreach ($result as $row) {
				$item++;
				$sisa_tagihan += $row->tagihan - $this->get_jml_bayar($row->id);
				$sisa_ags += $row->lama_angsuran - $this->get_sisa_ags($row->id);
			}
			$out['sisa_jml'] = $item;
			$out['sisa_tagihan'] = $sisa_tagihan;
			$out['sisa_ags'] = $sisa_ags;
			return $out;
		} else {
			return $out;
		}

	}

	function get_sisa_simpanans($anggota_id) {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('v_rekap_simpanan');
		//$this->db->where('lunas', 'Belum');
		$this->db->where('anggota_id', $anggota_id);
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();

		$out = array();
		$out['perumahan'] 		= 0;
		$out['sukarela'] 		= 0;
		$out['pokok'] 		= 0;
		$out['wajib'] 		= 0;
		$out['khusus_1'] 		= 0;
		$out['khusus_2'] 		= 0;

		$out['sisa_jml'] 		= 0;
		if($query->num_rows() > 0) {
			$result = $query->result();
			$item = 0;
			//$sisa_tagihan = 0;
			$perumahan = 0;
			$sisa_ags = 0;
			foreach ($result as $row) {
				$item++;
				$sisa_tagihan = 200;
				$perumahan = 10;
				//$sisa_ags += $row->lama_angsuran - $this->get_sisa_ags($row->id);
			}
			//$out['sisa_jml'] 		= $item;
			$out['perumahan'] 		= $perumahan;
			$out['sisa_tagihan'] 	= $sisa_tagihan;
			$out['sisa_ags'] 		= $sisa_ags;
			return $out;
		} else {
			return $out;
		}

	}

	function get_sisa_simpanan($anggota_id) {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('v_rekap_simpanan');
		//$this->db->where('lunas', 'Belum');
		$this->db->where('anggota_id', $anggota_id);
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();

		$out = array();
		$out['perumahan'] 		= 0;
		$out['sukarela'] 		= 0;
		$out['pokok'] 		= 0;
		$out['wajib'] 		= 0;
		$out['khusus_1'] 		= 0;
		$out['khusus_2'] 		= 0;

		$out['sisa_jml'] 		= 0;
		if($query->num_rows() > 0) {
			$result = $query->result();
			$item = 0;
			//$sisa_tagihan = 0;
			$perumahan = 0;
			$sisa_ags = 0;
			foreach ($result as $row) {
				$item++;
				$sisa_tagihan = 200;
				$perumahan = 10;
				$sisa_ags += $row->lama_angsuran - $this->get_sisa_ags($row->id);
			}
			//$out['sisa_jml'] 		= $item;
			$out['perumahan'] 		= $perumahan;
			$out['sisa_tagihan'] 	= $sisa_tagihan;
			$out['sisa_ags'] 		= $sisa_ags;
			return $out;
		} else {
			return $out;
		}

	}

	function get_jml_bayar($pinjam_id) {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('SUM(jumlah_bayar) AS total');
		$this->db->from('tbl_pinjaman_d');
		$this->db->where('pinjam_id', $pinjam_id);
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		$row = $query->row();
		return $row->total;
	}

	function get_sisa_ags($pinjam_id) {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('MAX(angsuran_ke) AS angsuran_ke');
		$this->db->from('tbl_pinjaman_d');
		$this->db->where('pinjam_id', $pinjam_id);
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		$row = $query->row();
		return $row->angsuran_ke;
	}

	function pengajuan_aksi() {
		$this->load->model('bunga_m');
		//$id_cab = $this->session->userdata('id_cabang');
		$status = $this->input->post('aksi');
		$id 	= $this->input->post('id');
		$alasan = $this->input->post('alasan');
		$status_txt = 0;
		$tgl_cair 	= '';
		$tanggal_u 	= date('Y-m-d H:i');

		switch ($status) {
			case 'Hapus':
				return $this->db->delete('tbl_pengajuan', array('id' => $id));
			break;
			case 'Setuju':
				$qa = $this->db->query("SELECT count(id)+2 as mid FROM `tbl_pinjaman_h`")->row();
				$id_trans = date('Ymd'.$qa->mid);
				$conf_bunga = $this->bunga_m->get_key_val();
				$biaya_admin = $conf_bunga['biaya_adm'];
				//$biaya_bunga = $conf_bunga['bg_pinjam'];

				$ps = $this->db->query("SELECT b.jenis,b.lama_ags,b.id,a.no_ktp,b.tgl_input, b.anggota_id,b.lama_ags,b.nominal FROM tbl_anggota a JOIN tbl_pengajuan b ON a.id = b.anggota_id where b.id=$id")->row();

				if($ps->jenis == 1){
					$biaya_bunga = $conf_bunga['bunga_biasa'];
				} else if($ps->jenis == 2){
					$biaya_bunga = $conf_bunga['bunga_barang'];
				} else {
					$biaya_bunga = 1;
				}
				$bunga_rp = ($biaya_bunga * $ps->nominal)/100;
				$jumlah_angsuran = $ps->nominal / $ps->lama_ags;

				$data = array(
					'id'			=>  $id_trans,
					'no_ktp'		=>	$ps->no_ktp,
					'tgl_pinjam'	=>	$ps->tgl_input,
					'anggota_id'	=>	$ps->anggota_id,
					'barang_id'		=>	4,
					'lama_angsuran'	=>	$ps->lama_ags,
					'jumlah_angsuran'	=>	$jumlah_angsuran,
					'jumlah'		=>	$ps->nominal,
					'bunga'			=>	$biaya_bunga,
					'bunga_rp'			=>	$bunga_rp,
					'biaya_adm'		=>	$biaya_admin,
					'lunas'			=>  'Belum',
					'dk'			=>	'K',
					'kas_id'			=>	2,
					'jns_trans'		=>	7,
					'status'		=>	'1',
					'jenis_pinjaman'	=>	$ps->jenis,
					'keterangan'	=> 	'',
					'user_name'		=> 	'admin'
					);
				$this->db->insert('tbl_pinjaman_h', $data);

				$status_txt = 1;
				$tgl_cair = $this->input->post('tgl_cair');
				$simpan_arr = array(			
					'status'		=>	$status_txt,
					'alasan'		=>	$alasan,
					'tgl_cair'		=>	$tgl_cair,
					'tgl_update'	=> 	$tanggal_u
				);
			break;
			case 'Tolak':
				$status_txt = 2;
				$simpan_arr = array(			
					'status'		=>	$status_txt,
					'alasan'		=>	$alasan,
					'tgl_update'	=> 	$tanggal_u
				);
			break;
			case 'Pending':
				$status_txt = 0;
				$simpan_arr = array(			
					'status'		=>	$status_txt,
					'alasan'		=>	$alasan,
					'tgl_update'	=> 	$tanggal_u
				);
			break;
			case 'Batal':
				$status_txt = 4;
				$simpan_arr = array(			
					'status'		=>	$status_txt,
					'tgl_update'	=> 	$tanggal_u
				);
			break;
			case 'Terlaksana':
				$status_txt = 3;
				$simpan_arr = array(			
					'status'		=>	$status_txt,
					'tgl_update'	=> 	$tanggal_u
				);
			break;
			case 'Belum':
				$status_txt = 1;
				$simpan_arr = array(			
					'status'		=>	$status_txt,
					'tgl_update'	=> 	$tanggal_u
				);
			break;
			default:
				return FALSE;
			break;
		}
		$this->db->where('id', $id);
		return $this->db->update('tbl_pengajuan',$simpan_arr);
	}

	function pengajuan_simpanan_aksi() {
		$this->load->model('bunga_m');
		//$id_cab = $this->session->userdata('id_cabang');
		$status = $this->input->post('aksi');
		$id 	= $this->input->post('id');
		$alasan = $this->input->post('alasan');
		$status_txt = 0;
		$tgl_cair 	= '';
		$tanggal_u 	= date('Y-m-d H:i');

		switch ($status) {
			case 'Hapus':
				return $this->db->delete('tbl_pengajuan_penarikan', array('id' => $id));
			break;
			case 'Setuju':
				$qa = $this->db->query("SELECT count(id)+1 as mid FROM `tbl_pinjaman_h`")->row();
				$id_trans = date('Ymd'.$qa->mid);

				$conf_bunga = $this->bunga_m->get_key_val();
				$biaya_admin = $conf_bunga['biaya_adm'];
				$biaya_bunga = $conf_bunga['bg_pinjam'];

				$ps = $this->db->query("SELECT b.keterangan, b.jenis, b.ajuan_id, b.id, a.no_ktp, b.tgl_input, b.anggota_id, b.nominal FROM tbl_anggota a JOIN tbl_pengajuan_penarikan b ON a.id = b.anggota_id where b.id=$id")->row();
				$data = array(
					//'no_ajuan'		=> $ps->id,
					//'ajuan_id'		=> $ps->ajuan_id,
					'tgl_transaksi'	=> $ps->tgl_input,
					'no_ktp'		=> $ps->no_ktp,
					
					'anggota_id'	=> $ps->anggota_id,
					'jenis_id'		=> $ps->jenis,
					//'lama_angsuran'	=>	$ps->lama_ags,
					'jumlah'		=> $ps->nominal,
					'keterangan'	=> $ps->keterangan,
					'akun'			=> 'Penarikan',
					//'biaya_adm'		=>	$biaya_admin,
					'dk'			=>	'K',
					//'jns_trans'		=>	'7',
					'kas_id'		=> '1',
					//'keterangan'	=> 	'',
					'user_name'		=>  'admin'
					);
				$this->db->insert('tbl_trans_sp', $data);

				$status_txt = 1;
				$tgl_cair = $this->input->post('tgl_cair');
				$simpan_arr = array(			
					'status'		=>	$status_txt,
					'alasan'		=>	$alasan,
					'tgl_cair'		=>	$tgl_cair,
					'tgl_update'	=> 	$tanggal_u
				);
			break;
			case 'Tolak':
				$status_txt = 2;
				$simpan_arr = array(			
					'status'		=>	$status_txt,
					'alasan'		=>	$alasan,
					'tgl_update'	=> 	$tanggal_u
				);
			break;
			case 'Pending':
				$status_txt = 0;
				$simpan_arr = array(			
					'status'		=>	$status_txt,
					'alasan'		=>	$alasan,
					'tgl_update'	=> 	$tanggal_u
				);
			break;
			case 'Batal':
				$status_txt = 4;
				$simpan_arr = array(			
					'status'		=>	$status_txt,
					'tgl_update'	=> 	$tanggal_u
				);
			break;
			case 'Terlaksana':
				$status_txt = 3;
				$simpan_arr = array(			
					'status'		=>	$status_txt,
					'tgl_update'	=> 	$tanggal_u
				);
			break;
			case 'Belum':
				$status_txt = 1;
				$simpan_arr = array(			
					'status'		=>	$status_txt,
					'tgl_update'	=> 	$tanggal_u
				);
			break;
			default:
				return FALSE;
			break;
		}
		
		$this->db->where('id', $id);
		//$this->db->where('id_cabang', $id_cab);
		return $this->db->update('tbl_pengajuan_penarikan',$simpan_arr);

	}

	function pengajuan_edit() {
		//$id_cab = $this->session->userdata('id_cabang');
		$out = '';
		$kolom = $this->input->post('name');
		$id = $this->input->post('pk');
		$value = $this->input->post('value');
		$value_insert = $value;
		if($kolom == 'nominal') {
			$value_insert = preg_replace("/[^0-9]/", "",$value);
		} else if($kolom == 'keterangan') {
			// ok
		} else if($kolom == 'lama_ags') {
			// ok
			$value_insert = preg_replace("/[^0-9]/", "",$value);
		} else {
			return false;
		}

		$tanggal_u = date('Y-m-d H:i');
		$simpan_arr = array(			
			$kolom			=>	$value_insert,
			'tgl_update'	=> 	$tanggal_u
		);

		$this->db->where('id', $id);
		//$this->db->where('id_cabang', $id_cab);
		if($this->db->update('tbl_pengajuan', $simpan_arr)) {
			if($kolom == 'nominal') {
				$value = number_format($value_insert * 1);
			}
			return $value;
		} else {
			return 'Error';
		}
	}

	function pengajuan_simpanan_edit() {
		//$id_cab = $this->session->userdata('id_cabang');
		$out = '';
		$kolom = $this->input->post('name');
		$id = $this->input->post('pk');
		$value = $this->input->post('value');
		$value_insert = $value;
		if($kolom == 'nominal') {
			$value_insert = preg_replace("/[^0-9]/", "",$value);
		} else if($kolom == 'keterangan') {
			// ok
		} else if($kolom == 'lama_ags') {
			// ok
			$value_insert = preg_replace("/[^0-9]/", "",$value);
		} else {
			return false;
		}

		$tanggal_u = date('Y-m-d H:i');
		$simpan_arr = array(			
			$kolom			=>	$value_insert,
			'tgl_update'	=> 	$tanggal_u
		);

		$this->db->where('id', $id);
		//$this->db->where('id_cabang', $id_cab);
		if($this->db->update('tbl_pengajuan_penarikan', $simpan_arr)) {
			if($kolom == 'nominal') {
				$value = number_format($value_insert * 1);
			}
			return $value;
		} else {
			return 'Error';
		}
	}

	function penarikan_edit() {
		//$id_cab = $this->session->userdata('id_cabang');
		$out = '';
		$kolom = $this->input->post('name');
		$id = $this->input->post('pk');
		$value = $this->input->post('value');
		$value_insert = $value;
		if($kolom == 'nominal') {
			$value_insert = preg_replace("/[^0-9]/", "",$value);
		} else if($kolom == 'keterangan') {
			// ok
		} else if($kolom == 'lama_ags') {
			// ok
			$value_insert = preg_replace("/[^0-9]/", "",$value);
		} else {
			return false;
		}

		$tanggal_u = date('Y-m-d H:i');
		$simpan_arr = array(			
			$kolom			=>	$value_insert,
			'tgl_update'	=> 	$tanggal_u
		);

		$this->db->where('id', $id);
		//$this->db->where('id_cabang', $id_cab);
		if($this->db->update('tbl_pengajuan_penarikan', $simpan_arr)) {
			if($kolom == 'nominal') {
				$value = number_format($value_insert * 1);
			}
			return $value;
		} else {
			return 'Error';
		}
	}

	//data kas
	function get_data_kas() {
		$this->db->select('*');
		$this->db->from('nama_kas_tbl');
		$this->db->where('aktif', 'Y');
		$this->db->where('tmpl_pinjaman', 'Y');
		$this->db->order_by('id', 'ASC');
		$query = $this->db->get();
		if($query->num_rows()>0){
			$out = $query->result();
			return $out;
		} else {
			return array();
		}
	}

	//data jenis angsuran
	function get_data_angsuran() {
		$this->db->select('*');
		$this->db->from('jns_angsuran');
		$this->db->where('aktif', 'Y');
		$this->db->order_by('ket', 'ASC');
		$query = $this->db->get();
		if($query->num_rows()>0){
			$out = $query->result();
			return $out;
		} else {
			return array();
		}
	}

	//data Bunga
	function get_data_bunga() {
		$this->db->select('*');
		$this->db->from('suku_bunga');
		$this->db->where('opsi_key', 'bg_pinjam');
		$this->db->order_by('id', 'ASC');
		$query = $this->db->get();
		if($query->num_rows()>0){
			$out = $query->result();
			return $out;
		} else {
			return FALSE;
		}
	}

	//data biaya adm
	function get_biaya_adm() {
		$this->db->select('*');
		$this->db->from('suku_bunga');
		$this->db->where('opsi_key', 'biaya_adm');
		$this->db->order_by('id', 'ASC');
		$query = $this->db->get();
		if($query->num_rows()>0){
			$out = $query->result();
			return $out;
		} else {
			return FALSE;
		}
	}

	//data data barang
	function get_id_barang() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('tbl_barang');
		$this->db->where('jml_brg >', 0);
		//$this->db->where('id_cabang',$id_cab);
		//$this->db->or_where('type', 'uang');
		$this->db->order_by('nm_barang', 'ASC');
		$query = $this->db->get();

		if($query->num_rows()>0){
			$out = $query->result();
			return $out;
		} else {
			return array();
		}
	}

	//data barang berdasarkan ID
	function get_data_barang($id) {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('tbl_barang');
		$this->db->where('id',$id);
		//$this->db->where('id_cabang',$id_cab);
		$query = $this->db->get();

		if($query->num_rows()>0){
			$out = $query->row();
			return $out;
		} else {
			return array();
		}
	}

	//data anggota
	function lap_data_anggota() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('tbl_anggota');
		$this->db->where('aktif', 'Y');
		//$this->db->where('id_cabang',$id_cab);
		$this->db->order_by('id', 'ASC');
		$query = $this->db->get();
		if($query->num_rows()>0){
			$out = $query->result();
			return $out;
		} else {
			return FALSE;
		}
	}

	//ambil data pinjaman header berdasarkan ID peminjam
	function get_data_pinjam_id($id) {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('v_hitung_pinjaman_3');
		$this->db->where('anggota_id',$id);
		//$this->db->where('id_cabang',$id_cab);
		$query = $this->db->get();

		if($query->num_rows() > 0){
			$out = $query->row();
			return $out;
		} else {
			return FALSE;
		}
	}

	function get_data_simpan_bank($no_ktp) {
		$this->db->select('*');
		$this->db->from('v_lap_potongan');
		$this->db->where('no_ktp',$no_ktp);
		$query = $this->db->get();

		if($query->num_rows() > 0){
			$out = $query->row();
			return $out;
		} else {
			return FALSE;
		}
	}

	//ambil data pinjaman header berdasarkan ID
	function get_data_pinjam($id) {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('*');
		$this->db->from('v_hitung_pinjaman_3');
		$this->db->where('id',$id);
		//$this->db->where('id_cabang',$id_cab);
		$query = $this->db->get();

		if($query->num_rows() > 0) {
			$out = $query->row();
			return $out;
		} else {
			return FALSE;
		}
	}

	//ambil data pengajuan berdasarkan ID
	function get_data_pengajuan($id) {
		//$id_cab = $this->session->userdata('id_cabang');
		$sql_tampil = "SELECT 
			a.id AS id, a.anggota_id AS anggota_id, a.tgl_input AS tgl_input, a.jenis AS jenis, a.nominal AS nominal, a.lama_ags AS lama_ags, a.keterangan AS keterangan, a.status AS status, a.alasan AS alasan, a.tgl_update AS tgl_update, a.tgl_cair AS tgl_cair,
			b.identitas AS identitas, b.nama AS nama, b.departement AS departement
			FROM tbl_pengajuan AS a
			LEFT JOIN tbl_anggota AS b ON b.id = a.anggota_id
		 	WHERE a.id = ".$id."";
		$query = $this->db->query($sql_tampil);
		if($query->num_rows() > 0) {
			$out = $query->row();
			return $out;
		} else {
			return FALSE;
		}
	}	


	function get_simulasi_pinjaman($pinjam_id) {
		$row = $this->get_data_pinjam($pinjam_id);
		$this->load->model('bunga_m');
		if($row) {
			$out = array();
			$conf_bunga = $this->bunga_m->get_key_val();
			$biaya_admin = $conf_bunga['biaya_adm'];
			$tgl_tempo_next = 0;
			for ($i=0; $i < $row->lama_angsuran; $i++) { 
				$odat = array();
				$odat['angsuran_pokok'] = $row->pokok_angsuran * 1;
				$odat['tgl_pinjam'] 	= substr($row->tgl_pinjam, 0, 10);
				/*
				if($conf_bunga['pinjaman_bunga_tipe'] == 'C') {
					$odat['bunga_pinjaman'] = ($row->lama_angsuran - ($i - 1)) * ($row->pokok_angsuran * $row->bunga) / 100;
					$odat['jumlah_ags'] = $row->pokok_angsuran + $odat['bunga_pinjaman'];
				} else {
					$odat['bunga_pinjaman'] = $row->bunga_pinjaman;
					$odat['jumlah_ags'] = $row->ags_per_bulan;
				}
				*/
				$odat['biaya_adm'] 		= $row->biaya_adm;
				$odat['pokok_bunga'] 	= $row->pokok_bunga;
				$odat['jumlah_ags'] 	= round($row->ags_per_bulan);
				$tgl_tempo_var 			= substr($row->tgl_pinjam, 0, 7) . '-01';
				$tgl_tempo 				= date("Y-m-d", strtotime($tgl_tempo_var . " +".$i." month"));
				$denda_hari 			= substr($row->tgl_pinjam, 8, 2);
				// $tgl_tempo 				= substr($tgl_tempo, 0, 7) . '-' . $denda_hari;
				$tgl_tempo 				= substr($tgl_tempo, 0, 7) . '-28';
				$odat['tgl_tempo'] 		= $tgl_tempo;
				$out[] 					= $odat;
			}
			return $out;
		} else {
			return FALSE;
		}
	}

	function get_data_transaksi_ajax($offset, $limit, $q='', $sort, $order) {
		//$id_cab = $this->session->userdata('id_cabang');
		$sql = "SELECT v_hitung_pinjaman_3.* FROM v_hitung_pinjaman_3 ";
		$where = " WHERE dk = 'K' and status='1' ";
		if(is_array($q)) {
			if($q['kode_transaksi'] != '') {
					$q['kode_transaksi'] = str_replace('PJ', '', $q['kode_transaksi']);
					$q['kode_transaksi'] = str_replace('AG', '', $q['kode_transaksi']);
					$q['kode_transaksi'] = $q['kode_transaksi'] * 1;
					$where .=" AND id LIKE '%".$q['kode_transaksi']."%' OR anggota_id LIKE '%".$q['kode_transaksi']."%' ";
				} else {
					if($q['cari_nama'] != '') {
						$where .=" AND v_hitung_pinjaman_3.nama LIKE '%".$q['cari_nama']."%' ";
						//$sql .= " LEFT JOIN tbl_anggota ON (v_hitung_pinjaman.anggota_id = tbl_anggota.id) ";
					}					
					if($q['cari_status'] != '') {
						$where .=" AND lunas LIKE '%".$q['cari_status']."%' ";
					}
					if($q['tgl_dari'] != '' && $q['tgl_sampai'] != '') {
						$where .=" AND DATE(tgl_pinjam) >= '".$q['tgl_dari']."' ";
						$where .=" AND DATE(tgl_pinjam) <= '".$q['tgl_sampai']."' ";
					}
			}
		}
		$sql .= $where;
		$result['count'] = $this->db->query($sql)->num_rows();
		$sql .= " ORDER BY {$sort} {$order} ";
		$sql .= " LIMIT {$offset},{$limit} ";
		$result['data'] = $this->db->query($sql)->result();
		return $result;
	}

	//panggil data simpanan untuk laporan 
	function lap_data_pinjaman() {
		//$id_cab = $this->session->userdata('id_cabang');
		$kode_transaksi = isset($_REQUEST['kode_transaksi']) ? $_REQUEST['kode_transaksi'] : '';
		$cari_status = isset($_REQUEST['cari_status']) ? $_REQUEST['cari_status'] : '';
		$tgl_dari = isset($_REQUEST['tgl_dari']) ? $_REQUEST['tgl_dari'] : '';
		$tgl_sampai = isset($_REQUEST['tgl_sampai']) ? $_REQUEST['tgl_sampai'] : '';
		$sql = '';
		$sql = " SELECT * FROM v_hitung_pinjaman WHERE dk = 'K' ";
		$q = array('kode_transaksi' => $kode_transaksi, 
			'cari_status'	=> $cari_status,
			'tgl_dari' 		=> $tgl_dari, 
			'tgl_sampai' 	=> $tgl_sampai);
		if(is_array($q)) {
			if($q['kode_transaksi'] != '') {
				$q['kode_transaksi'] = str_replace('PJ', '', $q['kode_transaksi']);
				$q['kode_transaksi'] = str_replace('AG', '', $q['kode_transaksi']);
				$q['kode_transaksi'] = $q['kode_transaksi'] * 1;
				$sql .=" AND (id LIKE '".$q['kode_transaksi']."' OR anggota_id LIKE '".$q['kode_transaksi']."') ";
			} else {
				if($q['cari_status'] != '') {
					$sql .=" AND lunas LIKE '%".$q['cari_status']."%' ";
				}

				if($q['tgl_dari'] != '' && $q['tgl_sampai'] != '') {
					$sql .=" AND DATE(tgl_pinjam) >= '".$q['tgl_dari']."' ";
					$sql .=" AND DATE(tgl_pinjam) <= '".$q['tgl_sampai']."' ";
				}
			}
		}
		$sql .=" ORDER BY tgl_pinjam ASC ";
		$query = $this->db->query($sql);
		if($query->num_rows() > 0) {
			$out = $query->result();
			return $out;
		} else {
			return FALSE;
		}
	}

	public function create() {
		if (str_replace(',', '', $this->input->post('jumlah')) <= 0) {
			return FALSE;
		}
		$q = $this->db->query("SELECT count(id)+2 as mid FROM `tbl_pinjaman_h`")->row();
		$id_trans = date('Ymd'.$q->mid);
		$this->db->trans_start();
		// update stok barang berkurang
		$this->db->where('id', $this->input->post('barang_id'));
		$this->db->where('type <>', 'uang');
		$this->db->set('jml_brg', 'jml_brg - 1', FALSE);
		$this->db->update('tbl_barang');
		$lama = $this->input->post('lama_angsuran');
		$data = array(
			'id'			=>  $id_trans,
			'no_ktp'		=>	$this->input->post('no_ktp'),
			'tgl_pinjam'	=>	$this->input->post('tgl_pinjam'),
			'anggota_id'	=>	$this->input->post('anggota_id'),
			'barang_id'		=>	4,
			'lama_angsuran'	=>	$this->input->post('lama_angsuran'),
			'jumlah_angsuran'	=>	$this->input->post('jumlah_angsuran'),
			'jumlah'		=>	str_replace(',', '', $this->input->post('jumlah')),
			'bunga_rp'			=>	$this->input->post('bunga'),
			'biaya_adm'		=>	str_replace(',', '', $this->input->post('biaya_adm')),
			'lunas' 		=>  'Belum',
			'dk'			=>	'K',
			'kas_id'		=>	$this->input->post('kas_id'),
			'jns_trans'		=>	'7',
			'status' 		=>  '1',
			'jenis_pinjaman'=>  $this->input->post('jenis_pinjaman'),
			'keterangan'	=> 	$this->input->post('ket'),
			'user_name'		=> 	$this->data['u_name']
			);
		$this->db->insert('tbl_pinjaman_h', $data);
		for ($i=1; $i <= $lama; $i++) {
			$odat = array();
			$odat['tgl_pinjam'] 	= substr($this->input->post('tgl_pinjam'), 0, 10);
			$tgl_tempo_var 			= substr($this->input->post('tgl_pinjam'), 0, 7) . '-01';
			$tgl_tempo 				= date("Y-m-d", strtotime($tgl_tempo_var . " +".$i." month"));
			$denda_hari 			= substr($this->input->post('tgl_pinjam'), 8, 2);
			$tgl_tempo 				= substr($tgl_tempo, 0, 7) . '-' . $denda_hari;
			//$odat['tgl_tempo'] 		= $tgl_tempo;
			//$out[] 					= $odat;

			$datas = array(	
				'no_urut'		=>  $i,
				'pinjam_id'		=>	$id_trans,
				'no_ktp'		=>	$this->input->post('no_ktp'),
				'tgl_pinjam'	=>	$this->input->post('tgl_pinjam'),
				'tempo'			=>  $tgl_tempo
			);
			$this->db->insert('tempo_pinjaman', $datas);
		}
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return FALSE;
		} else {
			$this->db->trans_complete();
			return TRUE;
		}
	}

	public function update($id){
		if(str_replace(',', '', $this->input->post('jumlah')) <= 0) {
			return FALSE;
		}
		$tanggal_u = date('Y-m-d H:i');
		$this->db->where('id', $id);
		return $this->db->update('tbl_pinjaman_h',array(
			'tgl_pinjam'	=>	$this->input->post('tgl_pinjam'),
			'lama_angsuran'	=>	$this->input->post('lama_angsuran'),
			'jumlah'		=>	str_replace(',', '', $this->input->post('jumlah')),
			'bunga_rp'		=>	$this->input->post('bunga'),
			'biaya_adm'		=>	str_replace(',', '', $this->input->post('biaya_adm')),
			'kas_id'		=>	$this->input->post('kas_id'),
			'update_data'	=> 	$tanggal_u,
			'keterangan'	=> 	$this->input->post('ket'),
			'user_name'		=> 	$this->data['u_name']
			));
	}

	public function delete($id) {
		$this->db->trans_start();
		// update stok barang bertambah
		$this->db->select('barang_id');
		$this->db->from('tbl_pinjaman_h');
		$this->db->where('id', $id);
		$query = $this->db->get();
		$row = $query->row();
		$barang_id = $row->barang_id;
		$this->db->where('id', $barang_id);
		$this->db->set('jml_brg', 'jml_brg + 1', FALSE);
		$this->db->update('tbl_barang');
		$this->db->delete('tbl_pinjaman_h', array('id' => $id));
		$this->db->delete('tempo_pinjaman', array('pinjam_id' => $id));
		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return FALSE;
		} else {
			$this->db->trans_complete();
			return TRUE;
		}
	}

	public function ambil_simpanan(){
        $hasil = $this->db->query("SELECT * FROM jns_simpan where id <> 8");
        return $hasil;
    }

    public function ambil_pinjaman(){
        $hasil = $this->db->query("SELECT * FROM jns_pinjaman");
        return $hasil;
    }

    function ambil_jumlah2($id_simpanan){
    	$no_ktp = $this->session->userdata('no_ktp');
        $hasil = $this->db->query("SELECT sum(jumlah) as jumlah FROM tbl_trans_sp WHERE jenis_id='$id_simpanan' and no_ktp='$no_ktp' and akun='Setoran'");
        return $hasil->result();
    }

    function ambil_jumlah($id_simpanan){
    	$no_ktp = $this->session->userdata('no_ktp');
        $hasil = $this->db->query("SELECT jumlah FROM v_saldo WHERE jenis_id='$id_simpanan' and no_ktp='$no_ktp'");
        return $hasil->result();
    }

    function ambil_persen($id_pinjaman){
        $hasil = $this->db->query("SELECT opsi_val as jumlah FROM suku_bunga WHERE opsi_key='$id_pinjaman'");
        return $hasil->result();
    }

	
}