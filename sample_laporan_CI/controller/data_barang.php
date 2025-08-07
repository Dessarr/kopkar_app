<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Data_barang extends OperatorController {

	public function __construct() {
		parent::__construct();	
	}	
	
	public function index() {
		$id_cab = $this->session->userdata('id_cabang');
		$this->data['judul_browser'] = 'Setting';
		$this->data['judul_utama'] = 'Setting';
		$this->data['judul_sub'] = 'Data Barang';

		$this->output->set_template('gc');

		$this->load->library('grocery_CRUD');
		$crud = new grocery_CRUD();
		$crud->where('id_cabang',$id_cab);
		$crud->set_table('tbl_barang');
		$crud->set_subject('Data Barang');
	
		$crud->fields('nm_barang','type','merk','harga','jml_brg','ket','id_cabang');

		$crud->display_as('nm_barang','Nama Barang');
		$crud->display_as('jml_brg','Jumlah Barang');
		
		$crud->required_fields('nm_barang','harga','jml_brg');
		$crud->callback_add_field('id_cabang',array($this,'_set_id_cab'));
		$crud->callback_edit_field('id_cabang',array($this,'_set_id_cab'));
		$crud->unset_read();
		$output = $crud->render();

		$out['output'] = $this->data['judul_browser'];
		$this->load->section('judul_browser', 'default_v', $out);
		$out['output'] = $this->data['judul_utama'];
		$this->load->section('judul_utama', 'default_v', $out);
		$out['output'] = $this->data['judul_sub'];
		$this->load->section('judul_sub', 'default_v', $out);
		$out['output'] = $this->data['u_name'];
		$this->load->section('u_name', 'default_v', $out);

		$this->load->view('default_v', $output);
		

	}

	function _set_id_cab() {
		$id_cab = $this->session->userdata('id_cabang');
		return "<input type='text' name='id_cabang' value='$id_cab' readonly='readonly' />";
	}

}
