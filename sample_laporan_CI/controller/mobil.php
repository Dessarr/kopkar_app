<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mobil extends OperatorController {

	public function __construct() {
		parent::__construct();	
	}	
	
	public function index() {
		//$id_cab = $this->session->userdata('id_cabang');
		$this->data['judul_browser'] 	= 'Data';
		$this->data['judul_utama'] 		= 'Data';
		$this->data['judul_sub'] 		= 'Mobil <a href="'.site_url('mobil/import').'" class="btn btn-sm btn-success">Import Data</a>';
		$this->output->set_template('gc');
		$this->load->library('grocery_CRUD');
		$crud = new grocery_CRUD();
		//$crud->where('id_cabang',$id_cab);
		$this->config->set_item('grocery_crud_file_upload_allow_file_types', 'gif|jpeg|jpg|png');
		$crud->set_table('tbl_mobil');
		$crud->set_subject('Data Mobil');

		$crud->columns('file_pic','id','nama','jenis','no_polisi','tgl_berlaku_stnk');
		$crud->fields('nama','jenis','merek','warna','tahun','no_polisi','no_rangka','no_mesin','no_bpkb','tgl_berlaku_stnk','file_pic');
		$crud->display_as('id','ID Mobil');
		$crud->display_as('nama','Nama');
		$crud->display_as('jenis','Jenis');
		$crud->display_as('merek','Merek');
		$crud->display_as('warna','Warna');
		$crud->display_as('tahun','Tahun');
		$crud->display_as('no_polisi','No Polisi');
		$crud->display_as('no_rangka','No Rangka');
		$crud->display_as('no_mesin','No Mesin');
		$crud->display_as('no_bpkb','No BPKB');
		$crud->display_as('tgl_berlaku_stnk','Tgl Berlaku STNK');
		$crud->display_as('file_pic','Photo');
		$crud->set_field_upload('file_pic','uploads/mobil');
		//$crud->display_as('id_cabang','ID Cabang');
		$crud->callback_after_upload(array($this,'callback_after_upload'));
		$crud->callback_column('file_pic',array($this,'callback_column_pic'));
		$crud->required_fields('nama','no_polisi','no_rangka','no_mesin','no_bpkb','tgl_berlaku_stnk');
		
		// Dropdown 
		$crud->field_type('jk','dropdown',
			array('L' => 'Laki-laki','P' => 'Perempuan'));
		$crud->display_as('jk','Jenis Kelamin');

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
		$out['level'] = $this->data['level'];
		$this->load->view('default_v', $output);
	}

	function import() {
		$this->data['judul_browser'] = 'Import Data';
		$this->data['judul_utama'] = 'Import Data';
		$this->data['judul_sub'] = 'Mobil <a href="'.site_url('mobil').'" class="btn btn-sm btn-success">Kembali</a>';
		$this->load->helper(array('form'));

		if($this->input->post('submit')) {
			$config['upload_path']   = FCPATH . 'uploads/temp/';
			$config['allowed_types'] = 'xls|xlsx';
			$this->load->library('upload', $config);

			if ( ! $this->upload->do_upload('import_mobil')) {
				$this->data['error'] = $this->upload->display_errors();
			} else {
				// ok uploaded
				$file = $this->upload->data();
				$this->data['file'] = $file;
				$this->data['lokasi_file'] = $file['full_path'];
				$this->load->library('excel');

				// baca excel
				$objPHPExcel = PHPExcel_IOFactory::load($file['full_path']);
				$no_sheet = 1;
				$header = array();
				$data_list_x = array();
				$data_list = array();
				foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
					if($no_sheet == 1) { // ambil sheet 1 saja
						$no_sheet++;
						$worksheetTitle = $worksheet->getTitle();
						$highestRow = $worksheet->getHighestRow(); // e.g. 10
						$highestColumn = $worksheet->getHighestColumn(); // e.g 'F'
						$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

						$nrColumns = ord($highestColumn) - 64;
						//echo "File ".$worksheetTitle." has ";
						//echo $nrColumns . ' columns';
						//echo ' y ' . $highestRow . ' rows.<br />';

						$data_jml_arr = array();
						//echo 'Data: <table width="100%" cellpadding="3" cellspacing="0"><tr>';
						for ($row = 1; $row <= $highestRow; ++$row) {
						   //echo '<tr>';
							for ($col = 0; $col < $highestColumnIndex; ++$col) {
								$cell = $worksheet->getCellByColumnAndRow($col, $row);
								$val = $cell->getValue();
								$kolom = PHPExcel_Cell::stringFromColumnIndex($col);
								if($row === 1) {
									if($kolom == 'A') {
										$header[$kolom] = 'no_ktp';
									} else {
										$header[$kolom] = $val;
									}
								} else {
									$data_list_x[$row][$kolom] = $val;
								}
							}
						}
					}
				}

				$no = 1;
				foreach ($data_list_x as $data_kolom) {
					if((@$data_kolom['A'] == NULL || trim(@$data_kolom['A'] == '')) ) { continue; }
					foreach ($data_kolom as $kolom => $val) {
						if(in_array($kolom, array('E', 'K', 'L')) ) {
							$val = ltrim($val, "'");
						}
						$data_list[$no][$kolom] = $val;
					}
					$no++;
				}

				//$arr_data = array();
				$this->data['header'] = $header;
				$this->data['values'] = $data_list;
				/*
				$data_import = array(
					'import_anggota_header'		=> $header,
					'import_anggota_values' 	=> $data_list
					);
				$this->session->set_userdata($data_import);
				*/
			}
		}

		$this->data['isi'] = $this->load->view('mobil_import_v', $this->data, TRUE);
		$this->load->view('themes/layout_utama_v', $this->data);
	}

	function import_db() {
		if($this->input->post('submit')) {
			$this->load->model('member_m','member', TRUE);
			$data_import = $this->input->post('val_arr');
			if($this->member->import_db($data_import)) {
				$this->session->set_flashdata('import', 'OK');
			} else {
				$this->session->set_flashdata('import', 'NO');
			}
			//hapus semua file di temp
			$files = glob('uploads/temp/*');
			foreach($files as $file){ 
				if(is_file($file)) {
					@unlink($file);
				}
			}
			redirect('mobil/import');
		} else {
			$this->session->set_flashdata('import', 'NO');
			redirect('mobil/import');
		}
	}

	function import_batal() {
		//hapus semua file di temp
		$files = glob('uploads/temp/*');
		foreach($files as $file){ 
			if(is_file($file)) {
				@unlink($file);
			}
		}
		$this->session->set_flashdata('import', 'BATAL');
		redirect('mobil/import');
	}

	function _set_password_input_to_empty() {
		return "<input type='password' name='pass_word' value='' /><br />Kosongkan password jika tidak ingin ubah/isi.";
	}

	function _set_id_cab() {
		$id_cab = $this->session->userdata('id_cabang');
		return "<input type='text' name='id_cabang' value='$id_cab' readonly='readonly' />";
	}

	function _encrypt_password_callback($post_array) {
		if(!empty($post_array['pass_word'])) {
			$post_array['pass_word'] = sha1($post_array['pass_word']);
		} else {
			unset($post_array['pass_word']);
		}
		return $post_array;
	}

	function _kolom_id_cb ($value, $row) {
		$value = '<div style="text-align:center;">AG' . sprintf('%04d', $row->id) . '</div>';
		return $value;
	}
	function _kolom_alamat($value, $row) {
		$value = wordwrap($value, 35, "<br />");
		return nl2br($value);
	}

	function callback_column_pic($value, $row) {
		if($value) {
			return '<div style="text-align: center;"><a class="image-thumbnail" href="'.base_url().'uploads/mobil/' . $value .'"><img src="'.base_url().'uploads/mobil/' . $value . '" alt="' . $value . '" width="120" height="80" /></a></div>';
		} else {
			return '<div style="text-align: center;"><img src="'.base_url().'assets/theme_admin/img/photo.jpg" alt="default" width="30" height="40" /></div>';
		}
	}

	function callback_after_upload($uploader_response,$field_info, $files_to_upload) {
		$this->load->library('image_moo');
        //Is only one file uploaded so it ok to use it with $uploader_response[0].
		$file_uploaded = $field_info->upload_path.'/'.$uploader_response[0]->name;
		$this->image_moo->load($file_uploaded)->resize(250,250)->save($file_uploaded,true);
		return true;
	}

}
