<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class M_monly_performance_item extends BE_Controller {

	var $controller = 'm_monly_performance_item';
	function __construct() {
		parent::__construct();
	}

	function index() {
		$kode_anggaran = user('kode_anggaran');
		$data['tahun'] 	= get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->result();
		$data['coa'] 	= get_data('tbl_m_coa',[
			'where' => [
				'kode_anggaran'	=> $kode_anggaran,
				'is_active'		=> 1,
				'glwnco !=' 	=> ''
			],
			'sort_by' => 'urutan',
			'sort' => 'ASC'
		])->result_array();
		$data_clone = get_data('tbl_m_monly_performance_nilai',[
			'select' => 'distinct coa',
			'where'	 => [
				'is_active' 		=> 1,
				'kode_anggaran'		=> $kode_anggaran
			]
		])->result_array();
		foreach($data['coa'] as $k => $v){
			$data['coa'][$k]['glwdes'] = $v['glwnco'].' - '.remove_spaces($v['glwdes']);

			$key = multidimensional_search($data_clone,[
				'coa' => $v['glwnco']
			]);
			if(strlen($key)>0):
				$data_clone[$key]['glwdes'] = $v['glwnco'].' - '.remove_spaces($v['glwdes']);
			endif;
		}
		$data['data_clone'] = $data_clone;
		$data['controller'] = $this->controller;

		render($data);
	}

	function data() {
		$config['where']['tbl_m_monly_performance_item.kode_anggaran'] = user('kode_anggaran');
		$data = data_serverside($config);
		render($data,'json');
	}

	function get_data() {
		$data = get_data('tbl_m_monly_performance_item','id',post('id'))->row_array();
		render($data,'json');
	}

	function save() {
		$this->validate();
		$data = post();
		unset($data['clone_penc']);
		unset($data['clone_pert']);
		$data['kode_anggaran'] = user('kode_anggaran');
		$response = save_data('tbl_m_monly_performance_item',$data,post(':validation'));
		if(isset($response['id'])):
			$data = [];
			if(post('clone_penc') && post('coa_clone_penc')):
				$data['clone_penc'] = post('coa_clone_penc');
				$this->data_clone(post('coa'),['coa' => post('coa_clone_penc'),'table' => 'tbl_m_monly_performance_nilai']);
			endif;
			if(post('clone_pert') && post('coa_clone_pert')):
				$data['clone_pert'] = post('coa_clone_pert');
				$this->data_clone(post('coa'),['coa' => post('coa_clone_pert'),'table' => 'tbl_m_monly_performance_nilai_pert']);
			endif;
			if(count($data)>0):
				update_data('tbl_m_monly_performance_item',$data,'id',$response['id']);
			endif;
		endif;
		render($response,'json');
	}

	function validate(){
		$id 			= post('id');
		$coa 			= post('coa');
		$kode_anggaran 	= user('kode_anggaran');

		$where['kode_anggaran'] = $kode_anggaran;
		$where['coa'] 			= $coa;
		if($id):
			$where['id !='] = $id;
		endif;
		$ck = get_data('tbl_m_monly_performance_item',['where' => $where])->row();
		if($ck):
			render([
				'status' 	=> 'warning',
				'message'	=> 'COA '.$coa.' Sudah Ada',
			],'json');exit();
		endif;

	}

	function delete() {
		$response = destroy_data('tbl_m_monly_performance_item','id',post('id'));
		render($response,'json');
	}

	function template() {
		ini_set('memory_limit', '-1');
		$arr = [
			'kode_anggaran' => 'kode_anggaran',
			'coa' => 'coa',
			'nama' => 'nama',
			'grup' => 'grup',
			'clone_penc' => 'Coa Clone Penc',
			'clone_pert' => 'Coa Clone Pert',
			'urutan' => 'urutan','is_active' => 'is_active'];
		$config[] = [
			'title' => 'template_import_m_monly_performance_item',
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

	function import() {
		ini_set('memory_limit', '-1');
		$file = post('fileimport');
		$kode_anggaran = user('kode_anggaran');
		$col = ['kode_anggaran','coa','nama','grup','clone_penc','clone_pert','urutan','is_active'];
		$this->load->library('simpleexcel');
		$this->simpleexcel->define_column($col);
		$jml = $this->simpleexcel->read($file);
		$c = 0;
		$u = 0;
		foreach($jml as $i => $k) {
			if($i==0) {
				for($j = 2; $j <= $k; $j++) {
					$data = $this->simpleexcel->parsing($i,$j);
					$check_coa = get_data('tbl_m_monly_performance_item',[
						'where' => [
							'coa' => $data['coa'],
							'kode_anggaran' => $kode_anggaran
						]
					])->row();
					if(isset($check_coa->coa)) {
						$id = $check_coa->id;
						$data['update_at'] = date('Y-m-d H:i:s');
						$data['update_by'] = user('nama');
						$save = update_data('tbl_m_monly_performance_item',$data,'id',$id);
						if($save) $u++;
					} else {
						$data['create_at'] = date('Y-m-d H:i:s');
						$data['create_by'] = user('nama');
						$save = insert_data('tbl_m_monly_performance_item',$data);
						if($save) $c++;
					}
					if($data['clone_penc']):
						$this->data_clone($data['coa'],['coa' => $data['clone_penc'],'table' => 'tbl_m_monly_performance_nilai']);
					endif;
					if($data['clone_pert']):
						$this->data_clone($data['coa'],['coa' => $data['clone_pert'],'table' => 'tbl_m_monly_performance_nilai_pert']);
					endif;
				}
			}
		}
		$response = [
			'status' => 'success',
			'message' => $c.' '.lang('data_berhasil_disimpan').'. '.$u.' '.lang('data_berhasil_diperbaharui').'.'
		];
		@unlink($file);
		render($response,'json');
	}

	function export() {
		ini_set('memory_limit', '-1');
		$arr = ['kode_anggaran' => 'Kode Anggaran','coa' => 'COA','nama' => 'Nama Coa','grup' => 'Grup',
			'clone_penc' => 'Coa Clone Penc',
			'clone_pert' => 'Coa Clone Pert',
			'urutan' => 'Urutan','is_active' => 'Aktif'];
		$data = get_data('tbl_m_monly_performance_item','kode_anggaran',user('kode_anggaran'))->result_array();
		$config = [
			'title' => 'data_m_monly_performance_item',
			'data' => $data,
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

	private function data_clone($coa,$data){
		$kode_anggaran = user('kode_anggaran');
		$coa_clone 	= $data['coa'];
		$table 		= $data['table'];

		$ls = get_data($table,[
			'where' => [
				'is_active' 	=> 1,
				'kode_anggaran'	=> $kode_anggaran,
				'coa'			=> $coa_clone
			]
		])->result_array();
		foreach($ls as $k => $v){
			$v['coa'] 	= $coa;
			$v['id'] 	= '';
			$v['is_active'] = 1;
			$ck = get_data($table,[
				'select' 	=> 'id',
				'where'		=> [
					'kode_anggaran' => $kode_anggaran,
					'coa' 			=> $coa,
					'nama' 			=> $v['nama']
				]
			])->row();
			if($ck):
				$v['id'] = $ck->id;
			endif;
			save_data($table,$v,[],true);
		}

	}

}