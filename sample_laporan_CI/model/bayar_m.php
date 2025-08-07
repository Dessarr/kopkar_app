<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bayar_m extends CI_Model {
	public function __construct() {
		parent::__construct();
	}

	function get_data_transaksi_ajax($offset, $limit, $q='', $sort, $order) {
		//$id_cab = $this->session->userdata('id_cabang');
		//$sql = "SELECT v_hitung_pinjaman.* FROM v_hitung_pinjaman ";
		$sql = "SELECT id,tgl_pinjam,anggota_id,nama,no_ktp,jumlah,pokok_angsuran,pokok_bunga,biaya_adm,ags_per_bulan,
		lama_angsuran,bln_sudah_angsur FROM v_hitung_pinjaman_3 ";
		$where = " WHERE lunas='Belum' ";
		if(is_array($q)) {
			if($q['kode_transaksi'] != '') {
				$q['kode_transaksi'] = str_replace('TPJ', '', $q['kode_transaksi']);
				$q['kode_transaksi'] = $q['kode_transaksi'] * 1;
				$where .=" AND (id LIKE '".$q['kode_transaksi']."' OR anggota_id LIKE '".$q['kode_transaksi']."') ";
			} else {
				if($q['cari_nama'] != '') {
					$where .=" AND nama LIKE '%".$q['cari_nama']."%' ";
					//$sql .= " LEFT JOIN tbl_anggota ON (v_hitung_pinjaman.anggota_id = tbl_anggota.id) ";
				}
				if($q['tgl_dari'] != '' && $q['tgl_sampai'] != '') {
					$where .=" AND DATE(tgl_pinjam) >= '".$q['tgl_dari']."' ";
					$where .=" AND DATE(tgl_pinjam) <= '".$q['tgl_sampai']."' ";
				}
			}
		}
		$sql .= $where;
		$result['count'] = $this->db->query($sql)->num_rows();
		$sql .=" ORDER BY {$sort} {$order} ";
		$sql .=" LIMIT {$offset},{$limit} ";
		$result['data'] = $this->db->query($sql)->result();
		return $result;
	}
}

