<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class M_biaya_coa_keterangan extends BE_Controller {

	var $table = 'tbl_m_biaya_coa_keterangan';
	var $kode_anggaran = '';
	function __construct() {
		parent::__construct();
		$this->kode_anggaran = user('kode_anggaran');
	}

	function index() {
		$kode_anggaran 	= $this->kode_anggaran;
		$data['tahun'] 	= get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->result();
		render($data);
	}

	function data() {
		$config['where']['kode_anggaran'] = user('kode_anggaran');
		$data = data_serverside($config);
		render($data,'json');
	}

	function get_data() {
		$data = get_data('tbl_m_biaya_coa_keterangan','id',post('id'))->row_array();
		render($data,'json');
	}

	function save() {
		$this->validate();
		$data = post();
		$data['kode_anggaran'] = user('kode_anggaran');
		$response = save_data('tbl_m_biaya_coa_keterangan',$data,post(':validation'));
		render($response,'json');
	}

	private function validate(){
		$where['kode_anggaran'] = $this->kode_anggaran;
		$where['coa'] 			= post('coa');
		if(post('id')):
			$where['id !='] = post('id');
		endif;
		$ck = get_data($this->table,['where' => $where])->row();
		if($ck):
			render([
				'status' 	=> 'warning',
				'message'	=> lang('coa').' '.post('coa').' '.lang('sudah_ada'),
			],'json');
			exit();
		endif;
	}

	function delete() {
		$response = destroy_data('tbl_m_biaya_coa_keterangan','id',post('id'));
		render($response,'json');
	}

	function template() {
		ini_set('memory_limit', '-1');
		$arr = ['coa' => 'coa','nama' => 'nama','keterangan' => 'keterangan','is_active' => 'is_active'];
		$config[] = [
			'title' => 'template_import_m_biaya_coa_keterangan',
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

	function import() {
		ini_set('memory_limit', '-1');
		$file = post('fileimport');
		$col = ['coa','nama','keterangan','is_active'];
		$this->load->library('simpleexcel');
		$this->simpleexcel->define_column($col);
		$jml = $this->simpleexcel->read($file);
		$c = 0;
		foreach($jml as $i => $k) {
			if($i==0) {
				for($j = 2; $j <= $k; $j++) {
					$data = $this->simpleexcel->parsing($i,$j);
					$data['kode_anggaran'] = $this->kode_anggaran;
					$ck = get_data($this->table,[
						'where' => [
							'kode_anggaran' => $this->kode_anggaran,
							'coa'	=> $data['coa']
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
		$arr = ['kode_anggaran' => 'Kode Anggaran','coa' => 'Coa','nama' => 'Nama','keterangan' => 'Keterangan','is_active' => 'Aktif'];
		$data = get_data('tbl_m_biaya_coa_keterangan','kode_anggaran',user('kode_anggaran'))->result_array();
		$config = [
			'title' => 'data_m_biaya_coa_keterangan',
			'data' => $data,
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

}