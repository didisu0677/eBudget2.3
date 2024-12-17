<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class M_biaya_rkf extends BE_Controller {
	var $table = 'tbl_m_biaya_rkf';
	function __construct() {
		parent::__construct();
	}

	function index() {
		$data['tahun'] = get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->result();
		render($data);
	}

	function data() {
		$config['select'] = 'b.glwdes,b.glwnob';
		$config['where']['tbl_m_biaya_rkf.kode_anggaran'] = user('kode_anggaran');
		$config['join'] = ["tbl_m_coa b on b.glwnco = tbl_m_biaya_rkf.coa and tbl_m_biaya_rkf.kode_anggaran = b.kode_anggaran type left"];
		$data = data_serverside($config);
		render($data,'json');
	}

	function get_data() {
		$data = get_data('tbl_m_biaya_rkf','id',post('id'))->row_array();
		render($data,'json');
	}

	function save() {
		$dt_id  = post('dt_id');
		$dt_key = post('dt_key');
		$coa 	= post('coa');
		$id 	= post('id');
		if(is_array($id)):
			$response = save_data($this->table,post());
			render($response,'json');exit();
		endif;
		if($coa):
			foreach ($coa as $k => $v) {
                $ck_data['coa'] = $coa[$k];
                $ck_data['kode_anggaran'] = user('kode_anggaran');
                if($dt_id[$k]):
                    $ck_data['id != '] = $dt_id[$k];
                endif;
                $cek        = get_data($this->table,[
                    'where'         => $ck_data,
                ])->row_array();
                if(isset($cek['id'])):
                    $get_coa = get_data('tbl_m_coa',[
                    	'where' => [
                    		'glwnco' => $coa[$k],
                    		'kode_anggaran' => user('kode_anggaran')
                    	]
                    ])->row();
                    $message = 'COA "'.$coa[$k].'" ';
                    if($get_coa):
                        $message = 'COA "'.$get_coa->glwnco.'-'.remove_spaces($get_coa->glwdes).'" ';
                    endif;
                    render([
                        'status'    => 'info',
                        'message'   => $message.lang('sudah_ada'),
                    ],'json');
                    exit();
                endif;
            }
		else:
			render([
                'status'    => 'info',
                'message'   => 'tidak ada data yang dipilih '.json_encode(post()),
            ],'json');exit();
		endif;

		foreach ($coa as $k => $v) {
			$key  		= $dt_key[$k];
			$is_default = 0;
            $x = post('is_default'.$key);
            if(isset($x[0])): $is_default = $x[0]; endif;

			$glwnco = $coa[$k];
			$id 	= $dt_id[$k];
			$ck_data = [];
			$ck_data['coa'] = $coa[$k];
			$ck_data['kode_anggaran'] = user('kode_anggaran');
		 	$cek        = get_data($this->table,[
                'where'         => $ck_data,
            ])->row_array();
            if(isset($cek['id'])):
            	$id = $cek['id'];
            endif;
			$data   = [
				'coa' 	=> $glwnco,
				'id'	=> $id,
				'is_default' 	=> $is_default,
				'kode_anggaran' => user('kode_anggaran')
			];
			save_data($this->table,$data);
		}
		
		render([
            'status'    => 'success',
            'message'   => lang('data_berhasil_disimpan')
        ],'json');
	}

	function delete() {
		$response = destroy_data('tbl_m_biaya_rkf','id',post('id'));
		render($response,'json');
	}

	function template() {
		ini_set('memory_limit', '-1');
		$arr = ['coa' => 'coa','is_default' => 'Default' ,'is_active' => 'is_active'];
		$config[] = [
			'title' => 'template_import_m_biaya_rkf',
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

	function import() {
		ini_set('memory_limit', '-1');
		$file = post('fileimport');
		$col = ['coa','is_default','is_active'];
		$this->load->library('simpleexcel');
		$this->simpleexcel->define_column($col);
		$jml = $this->simpleexcel->read($file);
		$c = 0;
		$u = 0;
		foreach($jml as $i => $k) {
			if($i==0) {
				for($j = 2; $j <= $k; $j++) {
					$data = $this->simpleexcel->parsing($i,$j);
					$check_coa = get_data('tbl_m_biaya_rkf',[
						'where' => [
							'coa' => $data['coa'],
							'kode_anggaran' => user('kode_anggaran')
						]
					])->row();
					if(isset($check_coa->coa)) {
						$id = $check_coa->id;
						$data['update_at'] = date('Y-m-d H:i:s');
						$data['update_by'] = user('nama');
						$data['kode_anggaran'] = user('kode_anggaran');
						$save = update_data('tbl_m_biaya_rkf',$data,'id',$id);
						if($save) $u++;
					} else {
						$data['create_at'] = date('Y-m-d H:i:s');
						$data['create_by'] = user('nama');
						$data['kode_anggaran'] = user('kode_anggaran');
						$save = insert_data('tbl_m_biaya_rkf',$data);
						if($save) $c++;
					}
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
		$arr = ['coa' => 'COA','is_default' => 'Default','is_active' => 'Aktif'];
		$data = get_data('tbl_m_biaya_rkf','kode_anggaran',user('kode_anggaran'))->result_array();
		$config = [
			'title' => 'data_m_biaya_rkf',
			'data' => $data,
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}
}