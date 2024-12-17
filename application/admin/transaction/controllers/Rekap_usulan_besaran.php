<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rekap_usulan_besaran extends BE_Controller {
    var $detail_tahun;
    var $kode_anggaran;
    var $arr_tahun_core     = [];
    var $arr_sumber_data    = [];
    var $real_status        = false;
    var $real_tahun         = [];
    function __construct() {
        parent::__construct();
        $this->kode_anggaran  = user('kode_anggaran');
        $this->detail_tahun   = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => [
                'a.kode_anggaran' => $this->kode_anggaran,
            ],
            'order_by' => 'tahun,bulan'
        ])->result();

        foreach ($this->detail_tahun as $k => $v) {
            if(!in_array($v->tahun,$this->arr_tahun_core)):
                array_push($this->arr_tahun_core, $v->tahun);
            endif;

            if(!in_array($v->sumber_data,$this->arr_sumber_data)):
                array_push($this->arr_sumber_data, $v->sumber_data);
            endif;

            if($v->singkatan == arrSumberData()['real']):
                $this->real_status = true;
                if(!in_array($v->tahun,$this->real_tahun)):
                    array_push($this->real_tahun, $v->tahun);
                endif;
            endif;

        }
    }
    
    function index() {
        $tahun_anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$this->kode_anggaran)->result(); 
        $id_coa         = json_decode($tahun_anggaran[0]->id_coa_besaran);
        $coa            = get_data('tbl_m_coa a',[
            'select' => 'b.*',
            'join' => [
                "tbl_m_coa b on b.glwnco = a.glwnco and b.kode_anggaran = '".$this->kode_anggaran."'"
            ],
            'where' => [
                'a.id' => $id_coa
            ],
        ])->result();
        $data['tahun']  = $tahun_anggaran;
        $data['coa']    = $coa;
        $data['detail_tahun']    = $this->detail_tahun;
        render($data);
    }

    function data($anggaran="", $coa=""){
        $data['cabang'][0] = get_data('tbl_m_cabang',array('where_array'=>array(
            'parent_id'=>0, 'is_active' => 1, 'kode_anggaran' => $anggaran
        ),'order_by' => 'urutan'))->result();
        $arrCodeCabang = array();
        foreach($data['cabang'][0] as $m0) {
            $data['cabang'][$m0->id] = get_data('tbl_m_cabang',array('where_array'=>array(
                'parent_id'=>$m0->id, 'is_active' => 1, 'kode_anggaran' => $anggaran
            ),'order_by' => 'urutan'))->result();
            foreach($data['cabang'][$m0->id] as $m1) {
                $data['cabang'][$m1->id] = get_data('tbl_m_cabang',array('where_array'=>array(
                    'parent_id'=>$m1->id, 'is_active' => 1, 'kode_anggaran' => $anggaran
                ),'order_by' => 'urutan'))->result();
                foreach($data['cabang'][$m1->id] as $m2) {
                    $dataLevel4 = get_data('tbl_m_cabang',array('where_array'=>array(
                        'parent_id'=>$m2->id, 'is_active' => 1, 'kode_anggaran' => $anggaran
                    ),'order_by' => 'urutan'))->result();
                    $data['cabang'][$m2->id] = $dataLevel4;

                    foreach ($dataLevel4 as $v) {
                        if(!in_array($v->kode_cabang,$arrCodeCabang)):
                            array_push($arrCodeCabang, $v->kode_cabang);
                        endif;
                    }
                }
            }
        }

        
        $list = get_data('tbl_bottom_up_form1 a',[
            'where' => [
                'coa'               => $coa,
                'kode_anggaran'     => $anggaran,
            ]
        ])->result_array();

        $data['detail_tahun']   = $this->detail_tahun;
        $data['list']           = $list;
        $data['real_status']    = $this->real_status;
        $data['real_tahun']     = $this->real_tahun;
        $data['coa']            = $coa;
        $data['anggaran']       = get_data('tbl_tahun_anggaran','kode_anggaran',$anggaran)->row();

        $response   = array(
            'table'     => $this->load->view('transaction/rekap_usulan_besaran/table',$data,true),

        );
        render($response,'json');
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
                $v[1],
            ];

            foreach($this->detail_tahun as $k2 => $v2){
                $val = $v[($k2+2)];
                 if($val):
                    $val = filter_money($val);
                    $val = (float) $val;
                else:
                    $val = 0;
                endif;
                $detail[] = $val;
            }
            $data[$k] = $detail;
        }

        $config[] = [
            'title' => 'Rekap Usulan Besaran',
            'header' => $header[1],
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'Rekap_Usulan_Besaran_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $coa).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }
}