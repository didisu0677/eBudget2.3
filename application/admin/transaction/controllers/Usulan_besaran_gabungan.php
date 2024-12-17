<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Usulan_besaran_gabungan extends BE_Controller {

	var $controller 	= 'usulan_besaran_gabungan';
	var $detail_tahun 	= [];
	var $arr_tahun = array();
	var $table = 'tbl_bottom_up_form1';
	function __construct() {
		parent::__construct();
		$this->detail_tahun   = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => [
                'a.kode_anggaran' => user('kode_anggaran'),
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
		$data['tahun'] 	= get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->result();
		$data['cabang']	= $this->cabang($access);
		$data['controller'] = $this->controller;
		render($data);
	}

	function cabang($access,$dt=[]){
		$kode_anggaran = user('kode_anggaran');
		$where = "kode_cabang like 'G%'";
		$is_divisi = '';
		if(isset($dt['kanpus'])):
			$where = "(kode_cabang like 'G%' or parent_id = 0)";
			$is_divisi = ' data-type="divisi"';
		endif;
		if(!$access['access_additional']):
			$cab = get_data('tbl_m_cabang',['where' => [
				'kode_cabang' 	=> user('kode_cabang'),
				'kode_anggaran'	=> $kode_anggaran
			]])->row();
			$where .= "and id = '".$cab->parent_id."'";
		endif;
		$cab_induk = get_data('tbl_m_cabang',[
			'select' 	=> 'id,kode_cabang,nama_cabang',
			'where' 	=> $where." and kode_cabang != 'G001' and is_active = '1' and kode_anggaran = '".user('kode_anggaran')."'",
			'order_by' 	=> 'urutan'
		])->result();

		if(isset($dt['check_cabang']) && !$access['access_additional']):
			$status = false;
			foreach($cab_induk as $v){
				if($v->id == $dt['check_cabang']->id):
					$status = true;
					break;
				endif;
			}
			if(!$status):
				render(['status' => false,'message' => lang('izin_ditolak')],'json');exit();
			endif;
		endif;

		return $cab_induk;
	}

	function data($kode_anggaran,$kode_cabang){
		$anggaran 	= get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
		$access 	= get_access($this->controller);

		$cabang 	= get_data('tbl_m_cabang',[
			'where' => [
				'kode_cabang' 	=> $kode_cabang,
				'kode_anggaran'	=> $kode_anggaran
			]
		])->row();
		if(!$anggaran):
			render(['status' => false,'message' => 'anggaran not found'],'json');exit();
		elseif(!$cabang):
			render(['status' => false,'message' => 'cabang not found'],'json');exit();
		endif;
		$this->cabang($access,['check_cabang' => $cabang]);


		$ls_cabang = get_data('tbl_m_cabang',[
			'select' => 'distinct group_concat(kode_cabang) as kode_cabang',
			'where' => [
				'kode_anggaran' => $kode_anggaran,
				'parent_id'		=> $cabang->id,
				'is_active'		=> 1
			]
		])->row();
		$arrKodeCabang = [];
		if($ls_cabang):
			$arrKodeCabang = explode(',',$ls_cabang->kode_cabang);
		endif;

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
		$data_core  	= get_data_core($coa_besaran,$arr_tahun_core,$arrKodeCabang,['sum_cabang' => 1]);

		$list = get_data($this->table,[
			'select' => '
				sum(B_01) as B_01,
				sum(B_02) as B_02,
				sum(B_03) as B_03,
				sum(B_04) as B_04,
				sum(B_05) as B_05,
				sum(B_06) as B_06,
				sum(B_07) as B_07,
				sum(B_08) as B_08,
				sum(B_09) as B_09,
				sum(B_10) as B_10,
				sum(B_11) as B_11,
				sum(B_12) as B_12,
				coa,
				data_core,
				tahun,
				kode_anggaran,
				total
			',
			'where'  => [
				'kode_cabang' 	=> $arrKodeCabang,
				'kode_anggaran'	=> $anggaran->kode_anggaran,
				'coa'			=> $coa_besaran,
				'data_core'		=> $this->arr_tahun,
			],
			'group_by' => 'coa,data_core'
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
		$data['list'] 			= $list;

		$view =  $this->load->view('transaction/'.$this->controller.'/table',$data,true);
		render([
			'status' 		=> true,
			'view' 			=> $view,
			'coa_show' 		=> $arr_coa_show

		],'json');
	}

	function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_anggaran    	= post('kode_anggaran');

        $kode_cabang    	= post('kode_cabang');
        $kode_cabang_txt 	= post('kode_cabang_txt');

        $anggaran 	= get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
		$access 	= get_access($this->controller);

		$cabang 	= get_data('tbl_m_cabang',[
			'where' => [
				'kode_cabang' 	=> $kode_cabang,
				'kode_anggaran'	=> $kode_anggaran
			]
		])->row();
		if(!$anggaran):
			render(['status' => false,'message' => 'anggaran not found'],'json');exit();
		elseif(!$cabang):
			render(['status' => false,'message' => 'cabang not found'],'json');exit();
		endif;
		$this->cabang($access,['check_cabang' => $cabang]);

        $header = json_decode(post('header'),true);
        $dt     = json_decode(post('data'),true);

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
        $filename = 'Gabungan_Usulan_Besaran_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }

}