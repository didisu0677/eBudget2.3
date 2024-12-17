<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Valas_neraca extends BE_Controller {
    var $path = 'transaction/budget_planner/';
    var $controller = 'valas_neraca';
    var $kode_anggaran;
    var $anggaran;
    var $detail_tahun;
    var $arr_sumber_data = array();
    var $arr_tahun_core = array();
    var $history_status = false;
    var $arr_coa = array();
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
        $this->check_sumber_data();
    }

    private  function check_sumber_data($sumber_data=""){
        foreach ($this->detail_tahun as $k => $v) {
            if(!in_array($v->sumber_data,$this->arr_sumber_data)):
                array_push($this->arr_sumber_data,$v->sumber_data);
            endif;
            if(!in_array($v->tahun, $this->arr_tahun_core)):
                array_push($this->arr_tahun_core,$v->tahun);
            endif;
            if($v->singkatan == arrSumberData()['real']):
                $this->history_status = true;
            endif;
        }
    }
    
    function index($p1="") { 
        $access         = get_access($this->controller);
        $data = data_cabang('neraca_new');
        $data['path'] = $this->path;
        $data['access_additional']  = $access['access_additional'];
        $data['detail_tahun']   = $this->detail_tahun;
        $data['bulan_terakhir'] = month_lang($data['tahun'][0]->bulan_terakhir_realisasi);
        render($data,'view:'.$this->path.'valas_neraca/index');
    }

    function dataNeraca ($kode_anggaran="", $kode_cabang=""){
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

        $bln_trakhir = $anggaran->bulan_terakhir_realisasi;
        $thn_trakhir = $anggaran->tahun_terakhir_realisasi;
        $tbl_history = 'tbl_history_'.$thn_trakhir;

        $column = 'VAL_'.$kode_cabang;
        $check_tbl = $this->db->table_exists($tbl_history);
        $status_history = false;
        if($check_tbl):
            if($this->db->field_exists($column, $tbl_history)):
                $status_history = true;
            endif;
        endif;

        $select = '
        a.id,a.glwnco,a.glwsbi,a.glwnob,a.glwcoa,a.glwdes,a.kali_minus,
        a.level0,a.level1,a.level2,a.level3,a.level4,a.level5,';
        $arr = [
            'select' => $select.'0 as last_bulan',
            'where'         => [
                'a.tipe' => 1,
                'a.kode_anggaran' => $kode_anggaran,
                'a.glwnco !=' => ['602',''],
            ],
            'order_by'      => 'a.urutan'
        ];
        if($status_history):
            $arr['select'] = $select.'b.'.$column.' as last_bulan';
            $arr['join'] = [$tbl_history." b on a.glwnco = b.glwnco and b.bulan = '$bln_trakhir' type left"];
        endif;
        $neraca = get_data('tbl_m_coa a',$arr)->result();
        $neraca = coa_neraca($neraca);
        
        $list = get_data('tbl_valas_neraca',[
            'where' => "kode_cabang =  '".$kode_cabang."' and kode_anggaran = '".$kode_anggaran."'"
        ])->result_array();

        // data core / history
        $data_core = [];
        if($this->history_status && count($neraca['arr_coa'])>0): 
            $column = 'VAL_'.$kode_anggaran;
            $data_core = get_data_core($neraca['arr_coa'],$this->arr_tahun_core,$column);
        endif;

        $data['coa']        = $neraca;
        $data['list']       = $list;
        $data['data_core']  = $data_core;
        $data['detail_tahun'] = $this->detail_tahun;
        $data['anggaran']   = $anggaran;
        $data['kode_cabang']  = $kode_cabang;

        $view  = $this->load->view($this->path.$this->controller.'/table',$data,true);

        $response = [
            'status' => true,
            'view'   => $view,
            'access_edit' => $access_edit
        ];
        render($response,'json');
    }

    function save_perubahan() {
        $kode_cabang    = post('kode_cabang');
        $kode_anggaran  = post('kode_anggaran');
        // pengecekan save untuk cabang
        check_save_cabang($this->controller,$kode_anggaran,$kode_cabang,'access_edit');

        $data   = json_decode(post('json'),true);
        foreach($data as $coa => $v){
            $ck = get_data('tbl_valas_neraca',[
                'select' => 'id',
                'where' => [
                    'kode_anggaran' => $kode_anggaran,
                    'kode_cabang'   => $kode_cabang,
                    'glwnco'        => $coa
                ]
            ])->row();
            $dataSave = $v;
            foreach($dataSave as $k => $v){
                $dataSave[$k] = insert_view_report(filter_money($v));
            }
            $dataSave['last_edit']      = '1';
            if(isset($dataSave['perbulan'])):
                $dataSave['last_edit'] = '2';
            endif;
            $dataSave['kode_anggaran']  = $kode_anggaran;
            $dataSave['kode_cabang']    = $kode_cabang;
            $dataSave['glwnco']         = $coa;
            $dataSave['id']             = '';
            if($ck):
                $dataSave['id'] = $ck->id;
            endif;
            save_data('tbl_valas_neraca',$dataSave,[],true);
        }
        
        render([
            'status'    => true,
            'message'   => lang('data_berhasil_diperbaharui'),
        ],'json');
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
            $detail[] = '';
            $val = $v[($n+2)];
            if($val != '-'):
                $val = filter_money($val);
                $val = (float) $val;
            else:
                $val = 0;
            endif;
            $detail[] = $val;
            $data[($k)] = $detail;
        }

        $config[] = [
            'title' => 'valas neraca ('.get_view_report().')',
            'header' => $header[1],
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'valas_neraca_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }
}