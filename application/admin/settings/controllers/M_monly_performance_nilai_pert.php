<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class M_monly_performance_nilai_pert extends BE_Controller {

	var $controller = 'm_monly_performance_nilai_pert';
	function __construct() {
		parent::__construct();
	}

	function index() {
		$data['controller'] = $this->controller;
		$data['tahun'] 		= get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->result();
		render($data);
	}
	function data() {
		$config['where']['kode_anggaran'] = user('kode_anggaran');
		$data = data_serverside($config);
		render($data,'json');
	}

	function get_data() {
		$data = get_data('tbl_m_monly_performance_nilai_pert','id',post('id'))->row_array();
		$data['dir_upload']	= base_url().dir_upload('m_monly_performance_nilai_pert');
		render($data,'json');
	}

	function save() {
		$data 			= post();
		foreach ($data as $k => $v) {
			$data[$k] = html_entity_decode($v);
		}
		$nilai_bobot = filter_money($data['bobot']) * (filter_money($data['nilai'])/100);
		$data['nilai_bobot'] 	= $nilai_bobot;
		$data['kode_anggaran'] 	= user('kode_anggaran');
		$response = save_data('tbl_m_monly_performance_nilai_pert',$data,post(':validation'));
		render($response,'json');
	}

	function delete() {
		$response = destroy_data('tbl_m_monly_performance_nilai_pert','id',post('id'));
		render($response,'json');
	}

	function template() {
		ini_set('memory_limit', '-1');
		$arr = ['coa' => 'coa','nama' => 'nama','nilai' => 'Nilai','formula' => 'formula','bobot' => 'bobot','warna' => 'Warna','keterangan' => 'keterangan','is_active' => 'is_active'];
		$config[] = [
			'title' => 'template_import_m_monly_performance_nilai',
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

	function import() {
		ini_set('memory_limit', '-1');
		$file = post('fileimport');
		$col = ['coa','nama','nilai','formula','bobot','warna','keterangan','is_active'];
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

					$nilai_bobot = filter_money($data['bobot']) * (filter_money($data['nilai'])/100);
					$data['nilai_bobot'] = $nilai_bobot;

					$save = insert_data('tbl_m_monly_performance_nilai_pert',$data);
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
		$arr = ['coa' => 'COA','nama' => 'Nama','nilai' => 'Nilai','formula' => 'Formula','bobot' => 'Bobot','nilai_bobot' => 'Nilai Bobot','warna' => 'Warna','keterangan' => 'Keterangan','is_active' => 'Aktif'];
		$data = get_data('tbl_m_monly_performance_nilai_pert','kode_anggaran',user('kode_anggaran'))->result_array();
		$config = [
			'title' => 'monly_performance_nilai_pert',
			'data' => $data,
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

}