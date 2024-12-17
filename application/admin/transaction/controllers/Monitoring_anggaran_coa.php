<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Monitoring_anggaran_coa extends BE_Controller {

	var $controller = 'monitoring_anggaran_coa';
    var $anggaran;
    var $kode_anggaran;
    var $table = 'tbl_monitoring_anggaran';
    var $table_budget = 'tbl_budget_nett_labarugi';
	function __construct() {
		parent::__construct();
		$this->kode_anggaran  = user('kode_anggaran');
        $this->anggaran       = get_data('tbl_tahun_anggaran','kode_anggaran',$this->kode_anggaran)->result();
	}

	function index() {
		$access         = get_access($this->controller);
        $akses_ubah     = $access['access_edit'];
        $data = data_cabang($this->controller);
		$data['access_additional']  = $access['access_additional'];
        $data['akses_ubah']         = $akses_ubah;
        $data['controller'] 		= $this->controller;

        $coa = $this->option_coa($this->kode_anggaran);
        $data['coa'] 				= $coa['data'];
        $data['coa_selected'] 		= $coa['selected'];
        render($data);
	}

	private function option_coa($kode_anggaran){
		$ls = get_data('tbl_m_coa a',[
            'select' => 'a.glwnco,a.glwdes',
            'join' 	 => [
            	'tbl_m_monitoring_anggaran b on b.coa = a.glwnco and b.kode_anggaran = a.kode_anggaran',
            ],
            'where'  => [
                'b.is_active' => 1,
                'a.is_active' => 1,
                'b.kode_anggaran' => $kode_anggaran,
            ]
        ])->result();

        $data = [];
        $selected = '';
        foreach ($ls as $k => $v) {
        	if($k == 'selected'):
        		$selected = $v->glwnco;
        	endif;
        	$data[] = ['glwnco' => $v->glwnco, 'glwdes' => $v->glwnco.' - '.remove_spaces($v->glwdes)];
        }
        return [
        	'data' => $data,
        	'selected' => $selected
        ];
	}

	var $arr_data = [];
	function data(){
		$bulan = (int) date('m');
		$kode_anggaran 	= post('kode_anggaran');
		$kode_cabang 	= post('kode_cabang');
		$coa 			= post('coa');
		$sub_coa 		= post('sub_coa');

		// if(!$kode_anggaran):
		// 	$kode_anggaran 	= '2020-01';
		// 	$kode_cabang 	= '001';
		// endif;
		// $coa 			= '5750000';
		// $sub_coa 		= '5751011';

		$anggaran 	= get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
		$cabang 	= get_data('tbl_m_cabang a',[
			'select' => '
				a.parent_id,
				a.kode_cabang,a.nama_cabang,
				b.kode_cabang as kode_gab,
				b.nama_cabang as nama_gab
			',
			'join' => [
				'tbl_m_cabang b on b.id = a.parent_id and a.parent_id != 0 type left'
			],
			'where' => [
				'a.kode_anggaran' => $kode_anggaran,
				'a.kode_cabang' 	=> $kode_cabang
			]
		])->row();

		$data_finish['kode_anggaran'] 	= $kode_anggaran;
		$data_finish['kode_cabang']		= $kode_cabang;
		$access = get_access($this->controller,$data_finish);
		$access_edit  	= false;
		$access_delete 	= false;
		if($access['access_edit'] && $kode_cabang == user('kode_cabang')):
			$access_edit = true;
		elseif($access['access_edit'] && $access['access_additional']):
			$access_edit = true;
		endif;

		if($access['access_delete'] && $kode_cabang == user('kode_cabang')):
			$access_delete = true;
		elseif($access['access_delete'] && $access['access_additional']):
			$access_delete = true;
		endif;

		// pengecekan akses cabang
		if(!$anggaran):
			render(['status' => false,'message' => 'anggaran not found'],'json');exit();
		elseif(!$cabang):
			render(['status' => false,'message' => 'cabang not found'],'json');exit();
		endif;
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$access);

        $this->validate($kode_anggaran,['data']);

        $class = [];

        // gabungan
        $status_gab = false;
        if($cabang->kode_gab):
        	$kode_gab = $cabang->kode_gab;
        	$pos = strpos(strtolower($kode_gab), 'g');
        	if($pos !== false):
        		$status_gab = true;
        		$arr_nm_cab  = explode('-',remove_spaces($cabang->nama_gab));
		        $nm_cab 	 = $arr_nm_cab[0];
		        if(count($arr_nm_cab)>1):
		        	$nm_cab 	 = $arr_nm_cab[1];
		        endif;
        		$class['#gab-cab'] = $nm_cab;
        	endif;
        endif;
        // end gabungan

        $nama_cabang = remove_spaces($cabang->nama_cabang);
        $arr_nm_cab  = explode('-',$nama_cabang);
        $nm_cab 	 = $arr_nm_cab[0];
        if(count($arr_nm_cab)>1):
        	$nm_cab 	 = $arr_nm_cab[1];
        endif;
        $nm_cab = str_replace('KCP','',$nm_cab);
        $nm_cab = str_replace('KC','',$nm_cab);
        $class['.card #cab'] = remove_spaces($nm_cab);
        $class['.card .bln'] = month_lang($bulan).' - '.$anggaran->tahun_anggaran;

        $data 	= [];
        $tahun          = $anggaran->tahun_anggaran;
        $select = '';
        $bulan_txt = 'B_' . sprintf("%02d", $bulan);
        for ($i=1; $i <= 12 ; $i++) { 
            $field = 'B_' . sprintf("%02d", $i);
            $select .= "ifnull(c.".$field.",0) as ".$field.",";
        }

        $option 	= '';
        $view2 		= '';
        $view 		= '';

        $detail_coa = get_data('tbl_m_coa',[
            'select' => 'glwnco,glwdes,tipe',
            'where' => [
                'kode_anggaran' => $anggaran->kode_anggaran,
                'glwnco'        => $coa
            ]
        ])->row();

        if($detail_coa->tipe == 1):
            $this->table_budget = 'tbl_budget_nett_neraca';
        else:
            $this->table_budget = 'tbl_budget_nett_labarugi';
        endif;

        $dt_coa = get_data('tbl_m_coa a',[
        	'select' => '
        		a.glwnco,a.glwdes,'.$select
    		,
        	'join' => [
        		$this->table_budget." c on c.coa = a.glwnco and c.kode_anggaran = a.kode_anggaran and c.kode_cabang = '$kode_cabang' type left",
        	],
        	'where' => [
        		'a.is_active' => 1,
        		'a.kode_anggaran' => $kode_anggaran,
        		'a.glwnco' => [$coa,$sub_coa],
        	],
        	'order_by' => 'a.urutan'
        ])->result();

        $arr_coa = [$coa];
        if(count($this->arr_sub_coa)>0):
        	$arr_coa = $this->arr_sub_coa;
        endif;
        $data_real = $this->data_real($kode_anggaran,$kode_cabang,$arr_coa);
        $data_core = get_data_core([$coa,$sub_coa],[$tahun],'TOT_'.$kode_cabang);

        $ls_detail = get_data('tbl_m_coa a',[
        	'select' => '
        		a.glwnco,a.glwdes,b.id,b.keterangan,b.tanggal,b.biaya,b.status,'.$select
    		,
        	'join' => [
        		$this->table.' b on b.sub_coa = a.glwnco and b.kode_anggaran = a.kode_anggaran',
        		$this->table_budget.' c on c.coa = a.glwnco and c.kode_anggaran = a.kode_anggaran and c.kode_cabang = b.kode_cabang type left',
        	],
        	'where' => [
        		'a.is_active' => 1,
        		'a.kode_anggaran' => $kode_anggaran,
        		'b.sub_coa' => $sub_coa,
        		'b.kode_cabang' => $kode_cabang,
        		'MONTH(b.tanggal) <=' => $bulan
        	],
        	'order_by' => 'a.urutan,a.glwnco,b.tanggal,b.id'
        ])->result();
        
        $data['tahun'] 			= $tahun;
        $data['access_edit'] 	= $access_edit;
        $data['access_delete'] 	= $access_delete;
        $data['access_additional'] 	= $access['access_additional'];
        $data['bulan'] 			= $bulan;
        $data['bulan_txt'] 		= $bulan_txt;
        $data['ls_detail'] 		= $ls_detail;
        $data['dt_coa'] 		= $dt_coa;
        $data['data_real'] 		= $data_real;
        $data['sub_coa'] 		= $sub_coa;
        $data['data_core'] 		= $data_core;

        $view2 	= $this->load->view('transaction/'.$this->controller.'/detail',$data,true);
        $view 	= $this->load->view('transaction/'.$this->controller.'/table',$data,true);
        $view3 	= '';
        if($status_gab):
        	$arr_cab = [];
        	$ls_cab = get_data('tbl_m_cabang',[
        		'select' => 'group_concat(distinct kode_cabang) as kode_cabang',
        		'where' => [
        			'is_active' => 1,
        			'parent_id' => $cabang->parent_id,
        			'kode_anggaran' => $anggaran->kode_anggaran,
        		]
        	])->row();
        	if($ls_cab && is_array(explode(',', $ls_cab->kode_cabang))):
        		$arr_cab = explode(',', $ls_cab->kode_cabang);
        	endif;

        	$select_gab = '';
        	for ($i=1; $i <= 12 ; $i++) { 
        		$field 	= 'B_' . sprintf("%02d", $i);
        		$select_gab .= " sum(ifnull($field,0)) as $field,";
        	}
        	$data_gab['renc'][$coa] = get_data($this->table_budget,[
        		'select' => $select_gab,
        		'where'  => [
        			'kode_anggaran' => $anggaran->kode_anggaran,
        			'kode_cabang' 	=> $arr_cab,
        			'coa' 			=> $coa
        		]
        	])->row_array();
        	$data_gab['data_real'] = $this->data_real($kode_anggaran,$arr_cab,$arr_coa);
        	$data_gab['data_core'] = get_data_core([$coa,$sub_coa],[$tahun],$arr_cab,['sum_cabang' => true]);

        	$data['gab'] = $data_gab;
        	$view3 = $this->load->view('transaction/'.$this->controller.'/gab',$data,true);
        endif;
        
        $class['#result1 tbody'] 	= $view;
        $class['.d-detail'] 		= $view2;
        $class['#result2 tbody'] 	= $view3;

        render([
        	'status' => true,
        	'status_gab' => $status_gab,
        	'class'  => $class,
        	'option' => $option

        ],'json');
	}

	private function data_real($kode_anggaran,$kode_cabang,$arr_coa){
		$data_real = get_data($this->table.' a',[
            'select' => "
                coalesce(sum(case when MONTH(tanggal) = '1' then biaya end), 0) as B_01,
                coalesce(sum(case when MONTH(tanggal) = '2' then biaya end), 0) as B_02,
                coalesce(sum(case when MONTH(tanggal) = '3' then biaya end), 0) as B_03,
                coalesce(sum(case when MONTH(tanggal) = '4' then biaya end), 0) as B_04,
                coalesce(sum(case when MONTH(tanggal) = '5' then biaya end), 0) as B_05,
                coalesce(sum(case when MONTH(tanggal) = '6' then biaya end), 0) as B_06,
                coalesce(sum(case when MONTH(tanggal) = '7' then biaya end), 0) as B_07,
                coalesce(sum(case when MONTH(tanggal) = '8' then biaya end), 0) as B_08,
                coalesce(sum(case when MONTH(tanggal) = '9' then biaya end), 0) as B_09,
                coalesce(sum(case when MONTH(tanggal) = '10' then biaya end), 0) as B_10,
                coalesce(sum(case when MONTH(tanggal) = '11' then biaya end), 0) as B_11,
                coalesce(sum(case when MONTH(tanggal) = '12' then biaya end), 0) as B_12,
                ",
            'where' => [
            	'a.sub_coa' => $arr_coa,
            	'a.kode_anggaran' => $kode_anggaran,
            	'a.kode_cabang' => $kode_cabang
            ],
        ])->row();

        return $data_real;
	}

	private function arr_coa($kode_anggaran){
		$ls = get_data('tbl_m_monitoring_anggaran',[
            'select' => 'group_concat(distinct coa) as glwnco',
            'where'  => [
                'is_active' => 1,
                'kode_anggaran' => $kode_anggaran,
            ]
        ])->row();
        $coa = [];
        if($ls && strlen($ls->glwnco)>0):
            $coa = explode(',', $ls->glwnco);
        endif;
        return $coa;
	}

	function save(){
		$access 		= get_access($this->controller);
		$kode_cabang 	= post('kode_cabang');
		$kode_anggaran 	= user('kode_anggaran');

		// pengecekan save untuk cabang
        check_save_cabang($this->controller,$kode_anggaran,$kode_cabang,'access_edit');

        // validate
        $this->validate($kode_anggaran);

        $coa 			= post('coa');
		$sub_coa 		= post('sub_coa');
        $dt_id 			= post('dt_id');
        $keterangan 	= post('keterangan');
        $biaya 			= post('biaya');
        $tanggal 		= date("Y-m-d");

        $res['status']  = 'success';
        $res['message'] = lang('data_berhasil_disimpan');
        if(post('id')):
        	$res['message'] = lang('data_berhasil_diperbaharui');
        endif;
        foreach($keterangan as $k => $v){
        	$data = [
        		'kode_cabang' 	=> $kode_cabang,
        		'kode_anggaran'	=> $kode_anggaran,
        		'coa' 			=> $coa,
        		'sub_coa' 		=> $sub_coa,
        		'keterangan'	=> $keterangan[$k],
        		'biaya' 		=> filter_money($biaya[$k]),
        		'tanggal' 		=> $tanggal,
        	];
        	$ck = get_data($this->table,[
        		'select' => 'id,status',
        		'where'  => [
        			'kode_cabang' 	=> $kode_cabang,
        			'kode_anggaran'	=> $kode_anggaran,
        			'sub_coa' 		=> $sub_coa,
        			'id' 			=> $dt_id[$k]
        		]
        	])->row();
        	$data['id'] = '';
        	$status_save = true;
        	if($ck):
        		unset($data['tanggal']);
        		$data['id'] = $ck->id;
        		if(!$access['access_additional'] && $ck->status == 1):
        			$status_save = false;
        		endif;
        	endif;
        	if($status_save):
        		save_data($this->table,$data,[],true);
        	endif;
        }

        render($res,'json');
	}

	private function validate($kode_anggaran,$data=[]){
		$status 	= 'failed';
		if(in_array('data',$data)):
			$status = false;
		endif;
		$coa 		= post('coa');
		$sub_coa 	= post('sub_coa');
		$dt = get_data('tbl_m_coa a',[
            'select' => 'a.glwnco,a.glwdes,a.tipe',
            'join' 	 => [
            	'tbl_m_monitoring_anggaran b on b.coa = a.glwnco and b.kode_anggaran = a.kode_anggaran',
            ],
            'where'  => [
                'b.is_active' => 1,
                'a.is_active' => 1,
                'b.kode_anggaran' => $kode_anggaran,
                'a.glwnco' => $coa
            ]
        ])->row();
        if(!$dt):
        	render(['status' => $status, 'message' => 'coa not found'],'json');exit();
        endif;
        $this->dt_coa = $dt;

        $sub_coa_ls = $this->sub_coa_ls($coa,$kode_anggaran);
        if($sub_coa_ls['count'] <= 0):
        	$this->arr_sub_coa[] = $coa;
        endif;
        if(!in_array($sub_coa,$this->arr_sub_coa)):
        	render(['status' => $status, 'message' => 'sub coa not found'],'json');exit();
        endif;
	}

	function get_data(){
		$access = get_access($this->controller);
		$dt = get_data($this->table,[
			'where' => [
				'id' => post('id')
			]
		])->row_array();
		if(!isset($dt['id'])):
			render(['status' => 'failed','message' => lang('data_not_found')],'json');exit();
		elseif(!$access['access_additional'] && $dt['status'] == 1):
			render(['status' => 'failed','message' => lang('izin_ditolak')],'json');exit();
		endif;

        // pengecekan save untuk cabang
        check_save_cabang($this->controller,$dt['kode_anggaran'],$dt['kode_cabang'],'access_edit');

        $dt['status'] = true;
        $dt['tanggal'] = c_date($dt['tanggal']);
        render($dt,'json');
	}

	function delete(){
		$access = get_access($this->controller);
		$dt = get_data($this->table,'id',post('id'))->row_array();
		if(!isset($dt['id'])):
			render(['status' => 'failed','message' => lang('data_not_found')],'json');exit();
		elseif(!$access['access_additional'] && $dt['status'] == 1):
			render(['status' => 'failed','message' => lang('izin_ditolak')],'json');exit();
		endif;

		// pengecekan save untuk cabang
        check_save_cabang($this->controller,$dt['kode_anggaran'],$dt['kode_cabang'],'access_delete');

        $response = destroy_data($this->table,'id',post('id'));

        // delete file
        $ck_file = get_data($this->table.'_file','id_monitoring_anggaran',post('id'))->row();
        if($ck_file):
        	$file = json_decode($ck_file->file,true);
        	if(is_array($file)):
        		foreach($file as $k => $f) {
        			@unlink(FCPATH . 'assets/uploads/monitoring_anggaran/' . $f);
        		}
        	endif;
        	destroy_data($this->table.'_file','id_monitoring_anggaran',post('id'));
        endif;

		render($response,'json');
	}

	var $selected 	 = '';
	var $arr_sub_coa = [];
	var $dt_coa 	 = '';
	function option_sub_coa(){
		$kode_anggaran = post('kode_anggaran');
		$coa = post('coa');

		$dt = get_data('tbl_m_coa a',[
            'select' => 'a.glwnco,a.glwdes,a.tipe',
            'join' 	 => [
            	'tbl_m_monitoring_anggaran b on b.coa = a.glwnco and b.kode_anggaran = a.kode_anggaran',
            ],
            'where'  => [
                'b.is_active' => 1,
                'a.is_active' => 1,
                'b.kode_anggaran' => $kode_anggaran,
                'a.glwnco' => $coa
            ]
        ])->row();
        if(!$dt):
        	render(['status' => true, 'message' => 'coa not found'],'json');exit();
        endif;

        $option = '';
        $selected = '';
        $sub_coa = $this->sub_coa_ls($coa,$kode_anggaran);
        if($sub_coa['count'] > 0):
        	$selected = $this->selected;
        	$option = $sub_coa['option'];
        else:
        	$selected = $coa;
        	$option .= '<option value="'.$coa.'">'.$coa.' - '.remove_spaces($dt->glwdes).'</option>';
        endif;
    	
    	render([
    		'status' => true,
    		'data' => $option,
    		'selected' => $selected
    	],'json');
	}

	private function sub_coa_ls($coa,$kode_anggaran){
		$ls = get_data('tbl_m_coa',[
        	'select' => 'glwnco,glwdes',
        	'where'	=> "
        		kode_anggaran = '$kode_anggaran' and is_active = 1 and 
        		(level0  = '$coa' or level1  = '$coa' or level2  = '$coa' or level3  = '$coa' or level4  = '$coa' or level5  = '$coa')
    		",
    		'order_by' => 'urutan'
        ])->result();

		$option = '';
        foreach ($ls as $k => $v) {
        	if($v->glwnco):
        		$this->arr_sub_coa[] = $v->glwnco;
        		$sub = $this->sub_coa_ls($v->glwnco,$kode_anggaran);
	        	if($sub['count'] <= 0):
	        		if(!$this->selected):
	        			$this->selected = $v->glwnco;
	        		endif;
	        		$option .= '<option value="'.$v->glwnco.'">'.$v->glwnco.' - '.remove_spaces($v->glwdes).'</option>';
	        	endif;
	        	$option .= $sub['option'];
        	endif;
        }

        return [
        	'count' 	=> count($ls),
        	'option'	=> $option,
        ];
	}

	function save_status(){
		$kode_anggaran 	= post('kode_anggaran');
		$kode_cabang 	= post('kode_cabang');
		$coa 			= post('coa');
		$sub_coa 		= post('sub_coa');
		$id 			= post('id');

		$anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
		if(!$anggaran):
			render(['status' => 'failed','message' => 'anggaran not found'],'json');exit();
		endif;

		$cabang = get_data('tbl_m_cabang',[
			'select' => 'id',
			'where' => [
				'is_active' => 1,
				'kode_cabang' => $kode_cabang,
				'kode_anggaran' => $kode_anggaran
			]
		])->row();
		if(!$cabang):
			render(['status' => 'failed','message' => 'anggaran not found'],'json');exit();
		endif;
		// pengecekan save untuk cabang
        $access = get_access($this->controller);
        if(!$access['access_additional']):
        	render(['status' => 'failed','message' => lang('izin_ditolak')],'json');exit();
        endif;
        check_save_cabang($this->controller,$kode_anggaran,$kode_cabang,'access_edit');


		$this->validate($kode_anggaran);

		$ck = get_data($this->table,[
			'select' => 'id',
			'where' => [
				'kode_cabang' => $kode_cabang,
				'kode_anggaran' => $kode_anggaran,
				'id' 	=> $id,
				'sub_coa' => $sub_coa,
				'status' => 1,
			]
		])->row();
		if(!$ck):
			render(['status' => 'failed','message' => lang('data_not_found')],'json');exit();
		endif;

		update_data($this->table,['status' => 0],'id',$id);

		render([
			'status' => 'success',
			'message' => lang('data_berhasil_diperbaharui')
		],'json');
	}

	function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_anggaran      = post('kode_anggaran');

        $kode_cabang        = post('kode_cabang');
        $kode_cabang_txt    = post('kode_cabang_txt');

        // pengecekan akses cabang
        $access = get_access($this->controller);
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$access);

        $dt = json_decode(post('data'),true);

        if(post('status_gab')):
            $header = $dt['#result2']['header'][0];

            $data = [];
            foreach(['#result2'] as $name){
                if(isset($dt[$name])):
                    $count2 = 0;
                    foreach($dt[$name]['data'] as $k => $v){
                        $count2 = count($v);
                        if($v[0] == '.'):
                            $detail = [''];
                            for ($i=1; $i <= 13 ; $i++) { 
                                $detail[] = '';
                            }
                        elseif($count2 == 2):
                            $detail = [
                                $v[0],
                                $v[1],
                            ];
                            for ($i=1; $i <= 12 ; $i++) { 
                                $detail[] = '';
                            }
                        else:
                            $detail = [
                                $v[0],
                                $v[1],
                            ];
                            for ($i=2; $i < $count2 ; $i++) {
                                if(strtolower($v[1]) == 'pencapaian'):
                                    $detail[] = $v[$i];
                                else:
                                    $detail[] = filter_money($v[$i]);
                                endif;
                            }
                        endif;
                        $data[] = $detail;
                    }
                endif;
            }

            $config[] = [
                'title' => post('nama_gab'),
                'header' => $header,
                'data'  => $data,
            ];
        endif;

        $header = $dt['#result1']['header'][0];

        $data = [];
        foreach(['#result1'] as $name){
            if(isset($dt[$name])):
                $count2 = 0;
                foreach($dt[$name]['data'] as $k => $v){
                    $count2 = count($v);
                    if($v[0] == '.'):
                    	$detail = [''];
                    	for ($i=1; $i <= 13 ; $i++) { 
	                    	$detail[] = '';
	                    }
                    elseif($count2 == 2):
                    	$detail = [
	                        $v[0],
	                        $v[1],
	                    ];
	                    for ($i=1; $i <= 12 ; $i++) { 
	                    	$detail[] = '';
	                    }
                    else:
                    	$detail = [
	                        $v[0],
	                        $v[1],
	                    ];
	                    for ($i=2; $i < $count2 ; $i++) {
	                    	if(strtolower($v[1]) == 'pencapaian'):
	                    		$detail[] = $v[$i];
	                    	else:
	                    		$detail[] = filter_money($v[$i]);
	                    	endif;
	                    }
                    endif;
                    $data[] = $detail;
                }
            endif;
        }

        $config[] = [
            'title' => 'E-KARTU BIAYA',
            'header' => $header,
            'data'  => $data,
        ];

        $header = '';
        if(isset($dt['.d-detail']['header'][0])):
            $header = $dt['.d-detail']['header'][0];
        endif;

        $col = 9;
        $col_min = 2;
        if($access['access_additional']):
        	$col = 10;
        	$col_min = 3;
        endif;
        if($header):
        	$data = [];
	        foreach(['.d-detail'] as $name){
	            if(isset($dt[$name])):
	                $count2 = 0;
	                foreach($dt[$name]['data'] as $k => $v){
	                    $count2 = count($v);
	                    if($count2 == 1):
	                    	$detail = [
		                        str_replace('.','',$v[0]),
		                    ];
		                    for ($i=1; $i < $col ; $i++) { 
		                    	$detail[] = '';
		                    }
	                    else:
	                    	$detail = [
		                        $v[0],
		                        $v[1],
		                        $v[2],
		                    ];
		                    for ($i=3; $i < ($count2-$col_min) ; $i++) {
		                    	$detail[] = filter_money($v[$i]);
		                    }
		                    for ($i=($count2-$col_min); $i < $count2 ; $i++) { 
		                    	$detail[] = $v[$i];
		                    }
	                    endif;
	                    $data[] = $detail;
	                }
	            endif;
	        }

	        $config[] = [
	            'title' => 'RINCIAN E-KARTU BIAYA',
	            'header' => $header,
	            'data'  => $data,
	        ];
        endif;

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'E-KARTU_Biaya_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }

    function file_view(){
        $id_monitoring_anggaran            = post('id_monitoring_anggaran');
        $kode_cabang    = post('kode_cabang');
        $kode_anggaran  = post('kode_anggaran');

        $anggaran 	= get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
        if(!$anggaran):
			render(['status' => false,'message' => 'anggaran not found'],'json');exit();
		endif;
		$cabang 	= get_data('tbl_m_cabang',[
			'where' => [
				'kode_anggaran' => $kode_anggaran,
				'kode_cabang' 	=> $kode_cabang
			]
		])->row();
		if(!$cabang):
			render(['status' => false,'message' => 'cabang not found'],'json');exit();
		endif;
		
        $data_finish['kode_anggaran']   = $kode_anggaran;
        $data_finish['kode_cabang']     = $kode_cabang;
        $access = get_access($this->controller);
        $access_edit = false;
        if($access['access_edit'] && $kode_cabang == user('kode_cabang')):
            $access_edit = true;
        elseif($access['access_edit'] && $access['access_additional']):
            $access_edit = true;
        endif;
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$access);
        $this->validate($kode_anggaran,['data']);

        $dt = get_data($this->table,[
            'where' => [
                'id' => $id_monitoring_anggaran,
                'kode_anggaran' => $kode_anggaran
            ]
        ])->row();

        $list = get_data($this->table.'_file',[
            'select' => 'id,file',
            'where'  => [
                'id_monitoring_anggaran' => $id_monitoring_anggaran,
                'kode_anggaran' => $kode_anggaran,
                'kode_cabang'   => $kode_cabang,
            ]
        ])->row();
        if($list):
            $list->file = json_decode($list->file);
        endif;

        render([
        	'status'=> true,
            'list'  => $list,
            'title' => post('title'),
            'access_edit'  => $access_edit,
        ],'json');
    }

    function save_file(){
        $data = post();

        // pengecekan save untuk cabang
        $kode_anggaran 	= post('kode_anggaran');
        $kode_cabang 	= post('kode_cabang');
        check_save_cabang($this->controller,$kode_anggaran,$kode_cabang,'access_edit');

        // validate
        $this->validate($kode_anggaran);

        $last_file = [];
        if($data['id']) {
            $dt = get_data($this->table.'_file','id',$data['id'])->row();
            if(isset($dt->id)) {
                if($dt->file != '') {
                    $lf     = json_decode($dt->file,true);
                    foreach($lf as $l) {
                        $last_file[$l] = $l;
                    }
                }
            }
        }

        $file                       = post('file');
        $keterangan_file            = post('keterangan_file');
        $filename                   = [];
        $dir                        = '';

        if(isset($file) && is_array($file)) {
            foreach($file as $k => $f) {
                $key = $k.'--';
                if(strpos($f,'exist:') !== false) {
                    $orig_file = str_replace('exist:','',$f);
                    if(isset($last_file[$orig_file])) {
                        unset($last_file[$orig_file]);
                        $filename[$key.$keterangan_file[$k]] = $orig_file;
                    }
                } else {
                    if(file_exists($f)) {
                        if(@copy($f, FCPATH . 'assets/uploads/monitoring_anggaran_coa/'.basename($f))) {
                            $filename[$key.$keterangan_file[$k]] = basename($f);
                            if(!$dir) $dir = str_replace(basename($f),'',$f);
                        }
                    }
                }
            }
        }

        if($dir) {
            delete_dir(FCPATH . $dir);
        }
        foreach($last_file as $lf) {
            @unlink(FCPATH . 'assets/uploads/monitoring_anggaran_coa/' . $lf);
        }

        $data['file'] = json_encode($filename);

        $response = save_data($this->table.'_file',$data,post(':validation'),true);

        render($response,'json');
    }
}