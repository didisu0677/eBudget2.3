<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class M_range_target_finansial extends BE_Controller {

	function __construct() {
		parent::__construct();
	}

	function index() {
		render();
	}

	function data() {
		$data = data_serverside();
		render($data,'json');
	}

	function get_data() {
		$data = get_data('tbl_m_range_target_finansial','id',post('id'))->row_array();
		render($data,'json');
	}

	function save() {
		$data = post();
		// $mulai 	= filter_money($data['mulai']);
		$sampai = filter_money($data['sampai']);

		// if($mulai>$sampai):
		// 	$nama = '> '.singkat_angka($mulai);
		// else:
		// 	$nama = singkat_angka($mulai)." - ".singkat_angka($sampai);
		// endif;
		$nama = singkat_angka($sampai);

		$data['nama'] = $nama;
		$response = save_data('tbl_m_range_target_finansial',$data,post(':validation'));
		render($response,'json');
	}

	function delete() {
		$response = destroy_data('tbl_m_range_target_finansial','id',post('id'));
		render($response,'json');
	}

	function template() {
		ini_set('memory_limit', '-1');
		// $arr = ['mulai' => 'mulai','sampai' => 'sampai','urutan' => 'urutan','is_active' => 'is_active'];
		$arr = ['sampai' => 'sampai','urutan' => 'urutan','is_active' => 'is_active'];
		$config[] = [
			'title' => 'template_import_m_range_target_finansial',
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

	function import() {
		ini_set('memory_limit', '-1');
		$file = post('fileimport');
		// $col = ['mulai','sampai','urutan','is_active'];
		$col = ['sampai','urutan','is_active'];
		$this->load->library('simpleexcel');
		$this->simpleexcel->define_column($col);
		$jml = $this->simpleexcel->read($file);
		$c = 0;
		foreach($jml as $i => $k) {
			if($i==0) {
				for($j = 2; $j <= $k; $j++) {
					$data = $this->simpleexcel->parsing($i,$j);
					// $mulai 	= filter_money($data['mulai']);
					$sampai = filter_money($data['sampai']);

					// if($mulai>$sampai):
					// 	$nama = singkat_angka($mulai).' >';
					// else:
					// 	$nama = singkat_angka($mulai).' - '.singkat_angka($sampai);
					// endif;
					$nama = singkat_angka($sampai);

					$data['nama'] = $nama;
					$data['create_at'] = date('Y-m-d H:i:s');
					$data['create_by'] = user('nama');
					$save = insert_data('tbl_m_range_target_finansial',$data);
					if($save) $c++;
				}
			}
		}
		$response = [
			'status' => 'success',
			'message' => $c.' '.lang('data_berhasil_disimpan').'.'
		];
		@unlink($file);
		render($response,'json');
	}

	function export() {
		ini_set('memory_limit', '-1');
		// $arr = ['nama' => 'Nama','mulai' => 'Mulai Dari','sampai' => 'Sampai','urutan' => 'Urutan','is_active' => 'Aktif'];
		$arr = ['nama' => 'Nama','sampai' => 'Sampai','urutan' => 'Urutan','is_active' => 'Aktif'];
		$data = get_data('tbl_m_range_target_finansial')->result_array();
		$config = [
			'title' => 'data_m_range_target_finansial',
			'data' => $data,
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

}