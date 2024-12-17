<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rekap_biaya_opr extends BE_Controller {

	var $controller = 'rekap_biaya_opr';
	var $table 		= 'tbl_biaya';
	function __construct() {
		parent::__construct();
	}

	function index() {
		$kode_anggaran = user('kode_anggaran');
		$data = [
			'tahun' => get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->result(),
			'controller' => $this->controller,
			'coa' 	=> get_data('tbl_m_coa',[
				'select' => 'glwnco,glwdes',
				'where' => [
					'is_active' => 1,
					'kode_anggaran' => $kode_anggaran,
					'tipe' => 2,
				],
				'order_by' => 'urutan'
			])->result()
		];
		render($data);
	}

	function data(){
		$kode_anggaran = user('kode_anggaran');
		$anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
		$data['kode_anggaran'] = $kode_anggaran;
		$data['coa'] = post('coa');
		$data['detail_tahun'] = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => "a.kode_anggaran = '".$kode_anggaran."' and a.tahun = '".$anggaran->tahun_anggaran."' ",
            'order_by' => 'tahun,bulan'
        ])->result_array();
		$view = $this->load->view('transaction/'.$this->controller.'/table',$data,true);
		render([
			'status' => true,
			'view' 	 => $view,
		],'json');
	}

	function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_anggaran      = post('kode_anggaran');

        $coa        = post('coa');
        $coa_txt    = post('coa_txt');

        $dt = json_decode(post('data'),true);

        $header = $dt['.d-content']['header'][0];


        $data = [];
        foreach(['.d-content'] as $name){
            if(isset($dt[$name])):
                $count2 = 0;
                foreach($dt[$name]['data'] as $k => $v){
                    $count2 = count($v);
                    $detail = [
                        $v[0],
                        $v[1],
                    ];
                    for ($i=2; $i <= 13 ; $i++) { 
                        $detail[] = filter_money($v[$i]);
                    }

                    $data[] = $detail;
                }
            endif;
        }

        $config[] = [
            'title' => 'Rekap Biaya'.' ('.get_view_report().')',
            'header' => $header,
            'data'  => $data,
        ];

        
        // render($config,'json'); exit();


        $this->load->library('simpleexcel',$config);
        $filename = 'rekap biaya_'.'_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $coa_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }

}