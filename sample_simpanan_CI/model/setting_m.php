<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Setting_m extends CI_Model {

	function get_key_val() {
		$out = array();
		//$id_cab = $this->session->userdata('id_cabang');
		$this->db->select('id,opsi_key,opsi_val');
		$this->db->from('tbl_setting');
		//$this->db->where('id_cabang', $id_cab);
		$query = $this->db->get();
		if($query->num_rows()>0){
				$result = $query->result();
				foreach($result as $value){
					$out[$value->opsi_key] = $value->opsi_val;
				}
				return $out;
		} else {
			return FALSE;
		}
	}

	function simpan() {
		//$id_cab = $this->session->userdata('id_cabang');
		$opsi_val_arr = $this->get_key_val();
		foreach ($opsi_val_arr as $key => $val) {
			if($this->input->post($key) || $this->input->post($key) == 0 ) {
				$data = array ('opsi_val'=> $this->input->post($key));
				//$this->db->where('id_cabang', $id_cab);
				$this->db->where('opsi_key',$key);
				if($this->db->update('tbl_setting',$data)) {
					// ok 
				} else {
					return FALSE;
				}
			}
		}
		return TRUE;
	}
}