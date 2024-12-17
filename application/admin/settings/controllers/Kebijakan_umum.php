<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Kebijakan_umum extends BE_Controller {

	var $table = 'tbl_kebijakan_umum';
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
		$data = get_data($this->table,'id',post('id'))->row_array();
		render($data,'json');
	}

	function save() {
		$this->validate();
		$response = save_data($this->table,post(),post(':validation'));
		render($response,'json');
	}

	private function validate(){
		$where['kode'] 			= post('kode');
		if(post('id')):
			$where['id !='] = post('id');
		endif;
		$ck = get_data($this->table,['where' => $where])->row();
		if($ck):
			render([
				'status' 	=> 'warning',
				'message'	=> lang('kode').' '.post('kode').' '.lang('sudah_ada'),
			],'json');
			exit();
		endif;
	}

	function delete() {
		$response = destroy_data($this->table,'id',post('id'));
		render($response,'json');
	}

	function template() {
		ini_set('memory_limit', '-1');
		$arr = ['kode' => 'kode','nama' => 'Nama Kebijakan Umum','keterangan' => 'keterangan','is_active' => 'is_active'];
		$config[] = [
			'title' => 'template_import_kebijakan_umum',
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

	function import() {
		ini_set('memory_limit', '-1');
		$file = post('fileimport');
		$col = ['kode','nama','keterangan','is_active'];
		$this->load->library('simpleexcel');
		$this->simpleexcel->define_column($col);
		$jml = $this->simpleexcel->read($file);
		$c = 0;
		foreach($jml as $i => $k) {
			if($i==0) {
				for($j = 2; $j <= $k; $j++) {
					$data = $this->simpleexcel->parsing($i,$j);
					$ck = get_data($this->table,[
						'where' => [
							'kode'	=> $data['kode']
						]
					])->row();

					if($ck):
						$data['update_at'] = date('Y-m-d H:i:s');
						$data['update_by'] = user('nama');
						$save = update_data($this->table,$data,'id',$ck->id);
					else:
						$data['create_at'] = date('Y-m-d H:i:s');
						$data['create_by'] = user('nama');
						$save = insert_data($this->table,$data);
					endif;
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
		$arr = ['kode' => 'kode','nama' => 'Nama Kebijakan Umum','keterangan' => 'keterangan','is_active' => 'Aktif'];
		$data = get_data($this->table)->result_array();
		$config = [
			'title' => 'data_kebijakan_umum',
			'data' => $data,
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

}