<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class M_rkf_kolom extends BE_Controller {

	var $kode_anggaran = '';
	var $table = 'tbl_m_rkf_kolom';
	function __construct() {
		parent::__construct();
		$this->kode_anggaran = user('kode_anggaran');
	}

	function index() {
		render();
	}

	function data() {
		$data = data_serverside();
		foreach($data['data'] as $k => $v){
			$data['data'][$k][$this->table.'_nama'] = lang($v[$this->table.'_lang']);
		}
		$data['accessView'] = false;
		render($data,'json');
	}

	function sortable(){
		$data['list'] = get_data($this->table,[
			'order_by' => 'urutan',
		])->result();
		$response	= array(
			'content' => $this->load->view('settings/m_rkf_kolom/sortable',$data,true)
		);
		render($response,'json');
	}

	function get_data() {
		$data = get_data('tbl_m_rkf_kolom','id',post('id'))->row_array();
		render($data,'json');
	}

	function save() {
		$data = post();
		$response = save_data('tbl_m_rkf_kolom',$data,post(':validation'));
		render($response,'json');
	}

	function delete() {
		$response = destroy_data('tbl_m_rkf_kolom','id',post('id'));
		render($response,'json');
	}

	function template() {
		ini_set('memory_limit', '-1');
		$arr = ['lang' => 'Kode','urutan' => 'urutan','is_active' => 'is_active'];
		$config[] = [
			'title' => 'template_import_m_rkf_kolom',
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

	function import() {
		ini_set('memory_limit', '-1');
		$file = post('fileimport');
		$col = ['kode_anggaran','lang','urutan','is_active'];
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
							'lang'	=> $data['lang']
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
		$arr = ['lang' => 'Kode','nama' => 'Kolom','urutan' => 'Urutan','is_active' => 'Aktif'];
		$data = get_data('tbl_m_rkf_kolom')->result_array();
		$config = [
			'title' => 'data_m_rkf_kolom',
			'data' => $data,
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

	function save_sortable() {
		$data = post('menuItem');
		update_data($this->table,['urutan'=>0]);
		$urutan = 0;
		foreach($data as $id => $parent_id) {
			$urutan++;
			$save 		= update_data($this->table,['urutan'=>$urutan],'id',$id);
		}
		render([
			'status'	=> 'success',
			'data' 		=> $data,
			'message'	=> lang('data_berhasil_diperbaharui')
		],'json');
	}

}