<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Laba_rugi_report extends BE_Controller {
	var $path = 'transaction/budget_planner/';
    var $controller = 'laba_rugi_report';
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
        render($data,'view:'.$this->path.'laba_rugi_report/index');
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
        	$field = 'bulan_'.$i;
        	$select .= "ifnull(b.".$field.",0) as ".$field.',';
        }
        $arr = [
            'select' => '
            	a.id,a.glwnco,a.glwsbi,a.glwnob,a.glwcoa,a.glwdes,a.kali_minus,
        		a.level0,a.level1,a.level2,a.level3,a.level4,a.level5,
            '.$select,
            'join' => [
            	"tbl_labarugi b on a.glwnco = b.glwnco and a.kode_anggaran = b.kode_anggaran and b.kode_cabang = '".$kode_cabang."' type left"
            ],
            'where'         => [
                'a.tipe' => 2,
                'a.kode_anggaran' => $kode_anggaran,
                'a.glwnco !=' => [''],
            ],
            'order_by'      => 'a.urutan'
        ];
        $coa = get_data('tbl_m_coa a',$arr)->result();
        $coa = coa_labarugi($coa);

        $data['coa'] = $coa;
        $data['detail_tahun'] = $this->detail_tahun;
        $view  = $this->load->view($this->path.'laba_rugi_report/table',$data,true);

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
            'title' => 'report laba rugi nett ('.get_view_report().')',
            'header' => $header[1],
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'report_laba_rugi_nett_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }
}