<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Neraca_nett_rekap extends BE_Controller {
	var $path = 'transaction/budget_planner/';
    var $controller = 'neraca_nett_rekap';
    var $kode_anggaran;
    var $anggaran;
    var $detail_tahun;
    function __construct() {
        parent::__construct();
        $this->kode_anggaran  = user('kode_anggaran');
        $this->anggaran       = get_data('tbl_tahun_anggaran','kode_anggaran',$this->kode_anggaran)->row();
        $this->detail_tahun   = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => [
                'a.kode_anggaran' => $this->kode_anggaran,
                'a.tahun'         => $this->anggaran->tahun_anggaran
            ],
            'order_by' => 'tahun,bulan'
        ])->result();
    }

    function index($p1="") { 
        $access         = get_access($this->controller);
        $data = data_cabang('neraca_new');
        $data['path'] = $this->path;
        $data['access_additional']  = $access['access_additional'];
        $data['detail_tahun']   = $this->detail_tahun;
        $data['controller'] 	= $this->controller;
        render($data,'view:'.$this->path.'neraca_report/index');
    }

    function data($kode_anggaran, $kode_cabang){
    	$anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
        if(!$anggaran):
            render(['status' => false,'message' => 'anggaran not found']);exit();
        endif;

        $data_finish['kode_anggaran']   = $anggaran->kode_anggaran;
        $data_finish['kode_cabang']     = $kode_cabang;
        $a = get_access($this->controller,$data_finish);
        $access_edit = false;
        if($a['access_edit'] && $kode_cabang == user('kode_cabang')):
            $access_edit = true;
        elseif($a['access_edit'] && $a['access_additional']):
            $access_edit = true;
        endif;
        $data['access_edit'] = $access_edit;

        // pengecekan akses cabang
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$a);

        $select = '';
        for ($i=1; $i <= 12 ; $i++) {
        	$field = 'B_' . sprintf("%02d", $i); 
        	$select .= "ifnull(b.".$field.",0) as ".$field.',';
        }
        $arr = [
            'select' => '
            	a.id,a.glwnco,a.glwsbi,a.glwnob,a.glwcoa,a.glwdes,a.kali_minus,
        		a.level0,a.level1,a.level2,a.level3,a.level4,a.level5,
            '.$select,
            'join' => [
            	"tbl_budget_plan_neraca b on a.glwnco = b.coa and a.kode_anggaran = b.kode_anggaran and b.kode_cabang = '".$kode_cabang."' type left"
            ],
            'where'         => [
                'a.tipe' => 1,
                'a.kode_anggaran' => $kode_anggaran,
                'a.glwnco !=' => ['602',''],
            ],
            'order_by'      => 'a.urutan'
        ];
        $neraca = get_data('tbl_m_coa a',$arr)->result();
        $neraca = coa_neraca($neraca);

        $dt_himpunan = get_data('tbl_budget_plan_neraca',[
        	'where' => [
        		'kode_anggaran' => $kode_anggaran,
        		'kode_cabang' 	=> $kode_cabang,
        		'coa' 			=> ['39999991','39999992','39999993']
        	]
        ])->result_array();
        $himpunan = [
        	['title' => 'ASSET NETTO CABANG','coa' => '39999991'],
        	['title' => 'PENGGUNAAN DANA DILUAR ANTAR KANTOR','coa' => '39999992'],
        	['title' => 'PENGHIMPUNAN DANA DILUAR ANTAR KANTOR','coa' => '39999993'],
        ];

        $data['coa'] = $neraca;
        $data['detail_tahun'] = $this->detail_tahun;
        $data['himpunan'] = $himpunan;
        $data['dt_himpunan'] = $dt_himpunan;
        $view  = $this->load->view($this->path.'neraca_report/table',$data,true);

        $response = [
            'status' => true,
            'view'   => $view,
        ];
        render($response,'json');
    }

    function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_cabang_txt    = post('kode_cabang_txt');

        $kode_cabang    = post('kode_cabang');
        $kode_cabang_txt= post('kode_cabang_txt');

        $header = json_decode(post('header'),true);
        $dt     = json_decode(post('data'),true);

        $data = [];
        foreach($dt as $k => $v){
            $detail = [
                $v[0],
                $v[1],
                $v[2],
                $v[3],
            ];
            $n = (count($this->detail_tahun)+3);
            for ($i=4; $i <=(count($this->detail_tahun)+3) ; $i++) { 
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
            'title' => 'report neraca nett ('.get_view_report().')',
            'header' => $header[1],
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'report_neraca_nett_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }
}