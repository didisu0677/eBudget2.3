<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Usulan_besaran extends BE_Controller {
	var $controller = 'usulan_besaran';
	var $kode_anggaran;
    var $anggaran;
    var $arr_tahun = array();
    var $table = 'tbl_bottom_up_form1';
	function __construct() {
		parent::__construct();
		$this->kode_anggaran  = user('kode_anggaran');
        $this->anggaran       = get_data('tbl_tahun_anggaran','kode_anggaran',$this->kode_anggaran)->row();
        $this->detail_tahun   = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => [
                'a.kode_anggaran' => $this->kode_anggaran,
            ],
            'order_by' => 'tahun,bulan'
        ])->result_array();
        $this->checkDetailTahun($this->detail_tahun);
	}

	private function checkDetailTahun($data){
		foreach ($data as $k => $v) {
			if(!in_array($v['tahun'],$this->arr_tahun)) array_push($this->arr_tahun,$v['tahun']);
		}
	}

	function index() {
		$access = get_access($this->controller);
        $data   = data_cabang();
        $data['access_additional']  = $access['access_additional'];
        $data['controller'] 		= $this->controller;
		render($data);
	}

	function data($kode_anggaran,$kode_cabang){
		$anggaran 	= get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
		$cabang 	= get_data('tbl_m_cabang','kode_cabang',$kode_cabang)->row();

		$data_finish['kode_anggaran'] 	= $kode_anggaran;
		$data_finish['kode_cabang']		= $kode_cabang;
		$access = get_access($this->controller,$data_finish);
		$access_edit = false;
		if($access['access_edit'] && $kode_cabang == user('kode_cabang')):
			$access_edit = true;
		elseif($access['access_edit'] && $access['access_additional']):
			$access_edit = true;
		endif;

		// pengecekan akses cabang
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$access);


		$coa_besaran = explode(',', str_replace(' ', '', $anggaran->coa_besaran));
		$coa_show 	 = explode(',', str_replace(' ', '', $anggaran->coa_show));

		$arr_group_giro = [];
		$arr_dpk 		= [];
		$arr_kredit 	= [];
		$arr_laba 		= [];
		$s_laba 		= false;
		$arr_other 	  	= [];
		foreach ($coa_besaran as $k => $v) {
			if(in_array($v, ['2100000','2101011','2101012'])) array_push($arr_group_giro,$v);
			if(in_array($v, ['2120011','2130000'])) array_push($arr_dpk,$v);
			if(in_array($v, ['122502','122506'])) array_push($arr_kredit,$v);
			if(in_array($v, ['59999','4570000','5580011'])) $s_laba = true;
		}

		if($s_laba):
			$arr_laba = ['59999','4570000','5580011'];
			$coa_besaran = array_merge($coa_besaran,$arr_laba);
		endif;
		foreach ($coa_besaran as $k => $v) {// get coa other
			if( !in_array($v,$arr_group_giro) && 
				!in_array($v,$arr_dpk) && 
				!in_array($v,$arr_kredit) &&
				!in_array($v,$arr_laba)):
				if(!in_array($v,$arr_other)) array_push($arr_other,$v);
			endif;
		}

		$arr_coa_show = [];
		foreach($coa_show as $v){
			if(in_array($v,$arr_group_giro) && !in_array('giro',$arr_coa_show)) array_push($arr_coa_show,'giro');
			if(in_array($v,$arr_dpk) && !in_array('dpk',$arr_coa_show)) array_push($arr_coa_show,'dpk');
			if(in_array($v,$arr_kredit) && !in_array('kredit',$arr_coa_show)) array_push($arr_coa_show,'kredit');
			if(in_array($v,$arr_laba) && !in_array('laba',$arr_coa_show)) array_push($arr_coa_show,'laba');

			array_push($arr_coa_show,$v);
		}

		$dt_coa = get_data('tbl_m_coa',[
			'where' => [
				'glwnco' => $coa_besaran,
				'kode_anggaran' => $anggaran->kode_anggaran
			]
		])->result_array();

		$tahun 			= (int) $anggaran->tahun_anggaran;
		$arr_tahun_core = [($tahun-3),($tahun-2),($tahun-1),($tahun)];
		$data_core  	= get_data_core($coa_besaran,$arr_tahun_core,'TOT_'.$cabang->kode_cabang);

		$list = get_data($this->table,[
			'where' => [
				'kode_cabang' 	=> $cabang->kode_cabang,
				'kode_anggaran'	=> $anggaran->kode_anggaran,
				'coa'			=> $coa_besaran,
				'data_core'		=> $this->arr_tahun,
			]
		])->result_array();

		$data['arr_group_giro'] = $arr_group_giro;
		$data['arr_dpk'] 		= $arr_dpk;
		$data['arr_kredit'] 	= $arr_kredit;
		$data['arr_laba'] 		= $arr_laba;
		$data['arr_other'] 		= $arr_other;
		$data['detail_tahun']	= $this->detail_tahun;
		$data['dt_coa']			= $dt_coa;
		$data['anggaran']		= $anggaran;
		$data['cabang']			= $cabang;
		$data['data_core']		= $data_core;
		$data['access_edit']	= $access_edit;
		$data['list']			= $list;
		$data['arr_tahun'] 		= $this->arr_tahun;
		$view =  $this->load->view('transaction/'.$this->controller.'/table',$data,true);

		// render($this->detail_tahun,'json');exit();

		render([
			'status' 		=> true,
			'view' 			=> $view,
			'access_edit' 	=> $access_edit,
			'coa_show' 		=> $arr_coa_show

		],'json');
	}

	function save_perubahan($kode_anggaran,$kode_cabang){
		$anggaran 	= get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
		$cabang 	= get_data('tbl_m_cabang',[
			'where' => [
				'kode_cabang' 	=> $kode_cabang,
				'kode_anggaran'	=> $kode_anggaran
			]
		])->row();

		// pengecekan save untuk cabang
        check_save_cabang($this->controller,$kode_anggaran,$kode_cabang,'access_edit');

		$data   = json_decode(post('json'),true);
		foreach($data as $k => $record) {
            $x 		= explode('-', $k);
            $coa 	= $x[0];
            $tahun 	= $x[1];

            foreach ($record as $k2 => $v2) {
                $value = filter_money($v2);
                $record[$k2] = insert_view_report($value);
            }

            $where = [
            	'kode_anggaran'	=> $anggaran->kode_anggaran,
            	'kode_cabang'	=> $cabang->kode_cabang,
            	'data_core'		=> $tahun,
            	'coa'			=> $coa
            ];

            $ck = get_data($this->table,['select' => 'id', 'where' => $where])->row();
            $dataSave = [];
            if($ck):
            	$dataSave = $record;
            	$dataSave['id'] = $ck->id;
            else:
            	$dataSave = $where;
            	$dataSave = array_merge($dataSave,$record);
            	$dataSave['keterangan_anggaran'] = $anggaran->keterangan;
            	$dataSave['tahun'] = $anggaran->tahun_anggaran;
            	$dataSave['cabang'] = $cabang->nama_cabang;
            	$dataSave['username'] = user('username');
            endif;
            save_data($this->table,$dataSave,[],true);
        }

        render([
        	'status' => true,
        	'message'=> lang('data_berhasil_diperbaharui')
        ],'json');exit();
	}

	function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_anggaran    	= post('kode_anggaran');

        $kode_cabang    	= post('kode_cabang');
        $kode_cabang_txt 	= post('kode_cabang_txt');

        $header = json_decode(post('header'),true);
        $dt     = json_decode(post('data'),true);

        // pengecekan akses cabang
        $a = get_access($this->controller);
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$a);

        $data = [];
        foreach($dt as $k => $v){
            $detail = [
                $v[0],
                $v[1],
            ];

            for ($i=2; $i <=13 ; $i++) { 
                $val = $v[$i];
                if($val && $val != '-'):
                    $val = filter_money($val);
                    $val = (float) $val;
                elseif($val == '-'):
                	$val = '';
                else:
                    $val = 0;
                endif;
                $detail[] = $val;
            }
            $data[$k] = $detail;
        }

        $config[] = [
            'title' => 'Usulan Besaran',
            'header' => $header[1],
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'Usulan_Besaran_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }
}