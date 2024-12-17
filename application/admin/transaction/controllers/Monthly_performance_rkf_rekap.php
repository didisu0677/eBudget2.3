<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Monthly_performance_rkf_rekap extends BE_Controller {
	var $controller = 'monthly_performance_rkf_rekap';
	function __construct() {
		parent::__construct();
	}

	function index() {
		$a  						= get_access($this->controller);
		$data 						= cabang_divisi('plan_data_kantor');
		$data['tahun'] 				= get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->result();
		$data['access_additional']	= $a['access_additional'];
		
		$cabang = [['kode_cabang' => 'All','nama_cabang' => lang('all')]];
		$data['cabang'] = array_merge($cabang,$data['cabang']);
		$data['controller'] = $this->controller;
		render($data);
	}

	function data(){

		$kode_cabang 	= post('kode_cabang');
		$kode_anggaran 	= post('kode_anggaran');
		
		$anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
		if(!$anggaran):
			render(['status' => false,'message' => 'anggaran not found'],'json');exit();
		endif;

		$divisi = cabang_divisi('plan_data_kantor');
		
		$data['divisi'] 		= $divisi['cabang'];
		$data['anggaran']	 	= $anggaran;
		$data['kode_cabang'] 	= $kode_cabang;
		$view  = $this->load->view('transaction/'.$this->controller.'/table',$data,true);
		$CI = get_instance();

		render([
			'kode_cabang' => $kode_cabang,
			'status' => true,
			'view' 	 => $view,
		],'json');
	}

	function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_cabang_txt    = post('kode_cabang_txt');

        $header = json_decode(post('header'),true);
        $dt     = json_decode(post('data'),true);

        $arr_header = [
        	$header[0][0],
        	$header[0][1],
        	$header[0][2],
        	$header[0][3],
        	"",
        	"",
        	$header[0][4],
        	"",
        	"",
        ];

        $data = [];
        $data[] = [
        	"",
        	"",
        	"",
        	$header[1][0],
        	$header[1][1],
        	$header[1][2],
        	$header[1][3],
        	$header[1][4],
        	$header[1][5],
        ];

        foreach($dt as $k => $v){
        	if($v[0] == '.'):
        		$v[0] = '';
        	endif;
        	$detail = [
        		$v[0],
        		$v[1],
        	];
        	for ($i=2; $i <= 8 ; $i++) { 
        		if(strlen($v[$i])>0):
        			$detail[] = filter_money($v[$i]);
        		else:
        			$detail[] = '';
        		endif;
        	}
        	$data[] = $detail;
        }

        $config[] = [
            'title' => 'Rekap RKF',
            'header' => $arr_header,
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'Rekap_rkf_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }

}