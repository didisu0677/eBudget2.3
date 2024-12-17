<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Import_prsn_dpk extends BE_Controller {

	var $table = 'tbl_prsn_dpk';
	function __construct() {
		parent::__construct();
	}

	function index() {
		$access = get_access('import_prsn_dpk');
		$data['access']	= $access;
		$data['tahun'] = get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->result();
		$data['cabang'] = check_column_table(user('kode_anggaran'),$this->table);
		render($data);
	}

	function data() {
		$config['where']['kode_anggaran']   = user('kode_anggaran');
		$data = data_serverside($config);
		render($data,'json');
	}

	function get_data() {
		$data = get_data('tbl_prsn_dpk','id',post('id'))->row_array();
		render($data,'json');
	}

	function save() {
		$anggaran 	= get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->row();
		$data 		= post();
		if(!post('id')):
			$data['id_anggaran']	= $anggaran->id;
			$data['kode_anggaran']	= $anggaran->kode_anggaran;
			$data['keterangan_anggaran'] = $anggaran->keterangan;
		endif;
		$response 	= save_data('tbl_prsn_dpk',post(),post(':validation'));
		render($response,'json');
	}

	function delete() {
		$response = destroy_data('tbl_prsn_dpk','id',post('id'));
		render($response,'json');
	}

	function template() {
		ini_set('memory_limit', '-1');
		$arr = ['no_coa' => 'no_coa','nama_coa' => 'nama_coa'];
		$column = check_column_table(user('kode_anggaran'),$this->table);
		foreach($column as $v){
			$arr[$v] = $v;
		}
		$arr['is_active'] = 'is_active';
		$config[] = [
			'title' => 'template_import_import_prsn_dpk',
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

	function import() {
		ini_set('memory_limit', '-1');
		$file = post('fileimport');
		$col = ['no_coa','nama_coa'];
		$column = check_column_table(user('kode_anggaran'),$this->table);
		foreach($column as $v){
			array_push($col,$v);
		}
		array_push($col,'is_active');

		$this->load->library('simpleexcel');
		$this->simpleexcel->define_column($col);
		$jml = $this->simpleexcel->read($file);
		$c = 0;
		$col = [];
		$anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->row();
		foreach($jml as $i => $k) {
			if($i==0) {
				$ls = $this->simpleexcel->ls_data($i);
				if(isset($ls[1])):
					foreach($ls[1] as $v){
						$v = str_replace(' ','_',$v);
						if(in_array(strtolower($v),['no_coa','nama_coa','is_active'])):
							$v = strtolower($v);
						endif;
						array_push($col,$v);
					}
					$this->simpleexcel->define_column($col);
				endif;

				for($j = 2; $j <= $k; $j++) {
					$data = $this->simpleexcel->parsing($i,$j);
					$data['id_anggaran']	= $anggaran->id;
					$data['kode_anggaran']	= $anggaran->kode_anggaran;
					$data['keterangan_anggaran'] = $anggaran->keterangan;
					$data['create_at'] 		= date('Y-m-d H:i:s');
					$data['create_by'] 		= user('nama');

					$ck = get_data('tbl_prsn_dpk',[
						'select' 	=> 'id',
						'where' 	=> [
							'no_coa' 		=> $data['no_coa'],
							'kode_anggaran'	=> $data['kode_anggaran']
						]
					])->row();
					$data['id'] = '';
					if($ck):
						$data['id'] = $ck->id;
					endif;

					if($data['no_coa']):
						$save = save_data('tbl_prsn_dpk',$data,false);
						if(isset($save['id'])) $c++;
					endif;
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
		$arr = ['no_coa' => 'No Coa','nama_coa' => 'Nama Coa'];
		$column = check_column_table(user('kode_anggaran'),$this->table);
		foreach($column as $v){
			$arr[$v] = $v;
		}
		$arr['is_active'] = 'Aktif';
		$data = get_data('tbl_prsn_dpk','kode_anggaran',user('kode_anggaran'))->result_array();
		$config = [
			'title' => 'data_import_prsn_dpk',
			'data' => $data,
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

	function check_cabang(){
		$kode_anggaran = user('kode_anggaran');
		if(!$kode_anggaran):
			render(['status' => 'failed'],'json');exit();
		endif;
		$gab_cab = get_data('tbl_m_cabang',['select' => 'id','where' => [
			'kode_cabang like' 	=> 'G001',
			'is_active' 		=> 1,
			'kode_anggaran'		=> $kode_anggaran
		]])->row();

		$gab_divisi = get_data('tbl_m_cabang',['select' => 'id','where' => [
			'kode_cabang' 	=> '00100',
			'is_active' 		=> 1,
			'kode_anggaran'		=> $kode_anggaran
		]])->row();

		$arrCabang 	= [];
		$ls_cab 	= get_data('tbl_m_cabang',[
			'select' => 'distinct kode_cabang',
			'where' => [
				'level1' 		=> $gab_cab->id,
				'level2 !=' 	=> $gab_divisi->id,
				'level3 !=' 	=> $gab_divisi->id,
				'level4 !=' 	=> $gab_divisi->id,
				'is_active'		=> 1,
				'status_group' 	=> 0
			],
			'sort_by' 	=> 'urutan',
		])->result();
		foreach($ls_cab as $v){
			array_push($arrCabang,$v->kode_cabang);
		}

		$fields = get_field($this->table,'name');
		$arrColumn = [];
		foreach($arrCabang as $v){
			if(!in_array('TOT_'.$v,$fields)):
				$arrColumn['TOT_'.$v] = [
					'type' => 'FLOAT',
	                'null' => TRUE,
	                'default' => 0
				];
			endif;
		}

		$message = lang('data_sudah_sesuai');
		$load 	 = '';
		if(count($arrColumn)>0):
			$message = lang('data_berhasil_diperbaharui');
			$load 	 = 'reload';
			$this->load->dbforge();
			$this->dbforge->add_column($this->table,$arrColumn);
		endif;
		render([
			'status' 	=> 'success',
			'message'	=> $message,
			'load' 		=> $load
		],'json');

	}

}