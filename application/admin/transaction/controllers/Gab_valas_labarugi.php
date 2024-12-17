<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Gab_valas_labarugi extends BE_Controller {

	var $path = 'transaction/gab_valas/';
    var $controller = 'gab_valas_labarugi';
    var $kode_anggaran;
    var $anggaran;
	var $detail_tahun;
	function __construct() {
		parent::__construct();
		$this->kode_anggaran  = user('kode_anggaran');
		$this->anggaran       = get_data('tbl_tahun_anggaran','kode_anggaran',$this->kode_anggaran)->result();
		$this->detail_tahun   = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => [
                'a.kode_anggaran' => $this->kode_anggaran,
                'a.tahun'         => $this->anggaran[0]->tahun_anggaran
            ],
            'order_by' => 'tahun,bulan'
        ])->result();
	}

	function index() {
		$access = get_access($this->controller);
		$data['path'] = $this->path;
        $data['access_additional']  = $access['access_additional'];
        $data['detail_tahun']   	= $this->detail_tahun;
        $data['controller']   		= $this->controller;
        $data['anggaran']   		= $this->anggaran;
        $data['coa'] 				= get_data('tbl_m_coa',[
        	'select' 	=> 'urutan,glwnco,glwdes',
        	'where'  	=> [
        		'kode_anggaran' => $this->kode_anggaran,
        		'is_active' 	=> 1,
        		'tipe' 			=> 2,
        		'glwnco !=' 	=> '',
        	],
        	'order_by' => 'urutan'
        ])->result();
		render($data,'view:'.$this->path.$this->controller.'/index');
	}

	function data($kode_anggaran,$coa=""){
		if(!$kode_anggaran or !$coa){
			exit();
		}

		$list = get_data('tbl_valas_labarugi',[
			'where' 	=> [
				'kode_anggaran' => $kode_anggaran,
				'glwnco' 		=> $coa,
			],
		])->result_array();
		$data['list'] 			= $list;
		$data['kode_anggaran']	= $kode_anggaran;
		$data['detail_tahun'] 	= $this->detail_tahun;
		$data['cabang']	= get_data('tbl_m_cabang',[
			'select' 	=> 'id,kode_cabang,nama_cabang',
			'where'		=> [
				'is_active' => 1,
				'parent_id' => 0,
				'kode_anggaran' => $kode_anggaran,
			],
			'order_by' => 'urutan'
		])->result();

		$view  = $this->load->view($this->path.$this->controller.'/table',$data,true);

		render([
			'status' => true,
			'table'	 => $view,
		],'json');
	}

	function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_cabang_txt    = post('kode_cabang_txt');

        $coa    = post('coa');
        $coa_txt= post('coa_txt');

        $header = json_decode(post('header'),true);
        $dt     = json_decode(post('data'),true);

        $data = [];
        foreach($dt as $k => $v){
            $detail = [
                $v[0],
            ];
            for ($i=1; $i <=count($this->detail_tahun) ; $i++) { 
                $val = $v[$i];
                if($val != '-'):
                    $val = filter_money($val);
                    $val = (float) $val;
                else:
                    $val = 0;
                endif;
                $detail[] = $val;
            }
            $data[($k)] = $detail;
        }

        $config[] = [
            'title' => $coa.' ('.get_view_report().')',
            'header' => $header[1],
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'Gab_valas_laba_rugi_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $coa_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }

}