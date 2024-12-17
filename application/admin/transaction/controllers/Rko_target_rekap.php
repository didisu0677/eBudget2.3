<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rko_target_rekap extends BE_Controller {
	var $controller = 'rko_target_rekap';
	var $path       = 'transaction/';
    var $tipe       = 1;
	var $detail_tahun;
    var $kode_anggaran;
    var $tahun_anggaran;
    var $arr_sumber_data = array();
    var $arrWeekOfMonth = array();
	function __construct() {
		parent::__construct();
		$this->kode_anggaran  = user('kode_anggaran');
        $this->tahun_anggaran = user('tahun_anggaran');
        $this->detail_tahun   = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => [
                'a.kode_anggaran' => $this->kode_anggaran,
                'a.sumber_data'   => array(2,3)
            ],
            'order_by' => 'tahun,bulan'
        ])->result();
        $this->check_sumber_data(2);
        $this->check_sumber_data(3);
        $this->arrWeekOfMonth = arrWeekOfMonth($this->tahun_anggaran);
	}
	private  function check_sumber_data($sumber_data){
        $key = array_search($sumber_data, array_map(function($element){return $element->sumber_data;}, $this->detail_tahun));
        if(strlen($key)>0):
            array_push($this->arr_sumber_data,$sumber_data);
        endif;
    }

	function index() {
        $a  = get_access($this->controller);
        $data['tahun']    = get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->result();
        $data['coa']      = get_data('tbl_history_import_target_rekap',[
            'select' => 'DISTINCT nama'
            ])->result();
        $data['path']     = $this->path;
        $data['detail_tahun']    = $this->detail_tahun;
        $data['controller']     = $this->controller;
        render($data,'view:'.$this->path.$this->controller.'/index');
	}

    function data($kode_anggaran){
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();

        $coa = post('coa');
        // $coa = "Giro Korporasi";

        $data['cabang'][0] = get_data('tbl_m_cabang',array('where_array'=>array(
            'parent_id'=>0, 'is_active' => 1, 'kode_anggaran' => $kode_anggaran
        ),'order_by' => 'urutan'))->result();
        foreach($data['cabang'][0] as $m0) {
            $data['cabang'][$m0->id] = get_data('tbl_m_cabang',array('where_array'=>array(
                'parent_id'=>$m0->id, 'is_active' => 1, 'kode_cabang !=' => '00100', 'kode_anggaran' => $kode_anggaran
            ),'order_by' => 'urutan'))->result();
            foreach($data['cabang'][$m0->id] as $m1) {
                $data['cabang'][$m1->id] = get_data('tbl_m_cabang',array('where_array'=>array(
                    'parent_id'=>$m1->id, 'is_active' => 1, 'kode_anggaran' => $kode_anggaran
                ),'order_by' => 'urutan'))->result();
                foreach($data['cabang'][$m1->id] as $m2) {
                    $dataLevel4 = get_data('tbl_m_cabang',array('where_array'=>array(
                        'parent_id'=>$m2->id, 'is_active' => 1, 'kode_anggaran' => $kode_anggaran
                    ),'order_by' => 'urutan'))->result();
                    $data['cabang'][$m2->id] = $dataLevel4;
                }
            }
        }

        $data['anggaran'] = $anggaran;
        $data['list'] = get_data('tbl_rko_target_rekap',[
            'where' => [
                'kode_anggaran' => $kode_anggaran,
                'nama'          => $coa,
            ]
        ])->result_array();

        render([
            'table' => $this->load->view('transaction/'.$this->controller.'/table',$data,true),
            'cab' => $data['cabang'][0]
        ],'json');
    }

    function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_cabang_txt    = post('kode_cabang_txt');

        $coa        = post('coa');
        $coa_txt    = post('coa_txt');

        $header = json_decode(post('header'),true);
        $dt     = json_decode(post('data'),true);

        $data = [];
        foreach($dt as $k => $v){
            if($k != 0):
                $detail = [
                    $v[0],
                    $v[1],
                    $v[2],
                ];

                for ($i=3; $i <=14 ; $i++) { 
                    $val = '-';
                    if(isset($v[$i])) $val = $v[$i];
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
            endif;
        }

        $config[] = [
            'title' => 'Usulan Besaran',
            'header' => $header[1],
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'RKO_TARGET_REKAP_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $coa_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }
}