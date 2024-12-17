<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Import_rincian_tabungan extends BE_Controller {

	function __construct() {
		parent::__construct();
	}

	function index() {
		$data['tahun'] = get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->result();
		render($data);
	}

	function data() {
		$config['where']['kode_anggaran'] = user('kode_anggaran');
		$data = data_serverside($config);
		render($data,'json');
	}

	function get_data() {
		$data = get_data('tbl_m_rincian_tabungan','id',post('id'))->row_array();
		render($data,'json');
	}

	function save() {
		$data = post();
		$data['kode_anggaran'] = user('kode_anggaran');
		$response = save_data('tbl_m_rincian_tabungan',$data,post(':validation'));
		render($response,'json');
	}

	function delete() {
		$response = destroy_data('tbl_m_rincian_tabungan','id',post('id'));
		render($response,'json');
	}

	function template() {
		ini_set('memory_limit', '-1');
		$arr = ['nama' => 'nama','coa' => 'coa','biaya_bunga' => 'biaya_bunga','is_active' => 'is_active'];
		$config[] = [
			'title' => 'template_import_import_rincian_tabungan',
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

	function import() {
		ini_set('memory_limit', '-1');
		$file = post('fileimport');
		$col = ['nama','coa','biaya_bunga','is_active'];
		$this->load->library('simpleexcel');
		$this->simpleexcel->define_column($col);
		$jml = $this->simpleexcel->read($file);
		$c = 0;
		foreach($jml as $i => $k) {
			if($i==0) {
				for($j = 2; $j <= $k; $j++) {
					$data = $this->simpleexcel->parsing($i,$j);
					$data['create_at'] = date('Y-m-d H:i:s');
					$data['create_by'] = user('nama');
					$data['kode_anggaran'] = user('kode_anggaran');
					$save = insert_data('tbl_m_rincian_tabungan',$data);
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
		$arr = ['nama' => 'Nama','coa' => 'COA','biaya_bunga' => 'Biaya Bunga','is_active' => 'Aktif'];
		$data = get_data('tbl_m_rincian_tabungan','kode_anggaran',user('kode_anggaran'))->result_array();
		$config = [
			'title' => 'data_import_rincian_tabungan',
			'data' => $data,
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

}