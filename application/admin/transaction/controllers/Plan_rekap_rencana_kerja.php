<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Plan_rekap_rencana_kerja extends BE_Controller {
    var $path       = 'transaction/budget_planner/kantor_pusat/';
    var $sub_menu   = 'transaction/budget_planner/sub_menu';
    var $controller = 'plan_rekap_rencana_kerja';
    function __construct() {
        parent::__construct();
    }
    
    function index($p1="") { 
        $data = cabang_divisi('plan_data_kantor');
        $data['path']     = $this->path;
        $data['sub_menu'] = $this->sub_menu;
        render($data,'view:'.$this->path.'rekap_rencana_kerja/index');
    }

    function data($anggaran="", $cabang="", $tipe = 'table'){
        $ckode_anggaran = $anggaran;
        $ckode_cabang = $cabang;

        // pengecekan akses divisi
        $access = get_access($this->controller);
        check_access_divisi($this->controller,$ckode_anggaran,$ckode_cabang,$access);

        $a = get_access('kebijakan_strategis');
        $data['akses_ubah'] = $a['access_edit'];

        $arr = ['select'    => '
                    a.id_kebijakan_umum,
                    a.program_kerja,
                    a.tujuan,
                    a.output,
                    a.target_finansial,
                    sum(ifnull(T_01,0)) as T_01,
                    sum(ifnull(T_02,0)) as T_02,
                    sum(ifnull(T_03,0)) as T_03,
                    sum(ifnull(T_04,0)) as T_04,
                    sum(ifnull(T_05,0)) as T_05,
                    sum(ifnull(T_06,0)) as T_06,
                    sum(ifnull(T_07,0)) as T_07,
                    sum(ifnull(T_08,0)) as T_08,
                    sum(ifnull(T_09,0)) as T_09,
                    sum(ifnull(T_10,0)) as T_10,
                    sum(ifnull(T_11,0)) as T_11,
                    sum(ifnull(T_12,0)) as T_12,
                    b.nama as kebijakan_umum,
                    c.nama as perspektif,
                    d.nama as skala_program,
                    e.glwnco,
                    e.glwdes,
                ',];
        if($anggaran) {
            $arr['where']['a.kode_anggaran']  = $ckode_anggaran;
        }
        if($cabang) {
            $arr['where']['a.kode_cabang']  = $ckode_cabang;
        }

        $arr['join'][] = 'tbl_kebijakan_umum b on b.id = a.id_kebijakan_umum';
        $arr['join'][] = 'tbl_perspektif c on c.id = a.id_perspektif';
        $arr['join'][] = 'tbl_skala_program d on d.id = a.id_skala_program';
        $arr['join'][] = "tbl_m_coa e on e.glwnco = a.coa and e.kode_anggaran = a.kode_anggaran type left";
        $arr['order_by'] = 'b.nama,b.id';
        $arr['group_by'] = 'b.id,a.coa,a.program_kerja,a.tujuan,a.output';
        $list = get_data('tbl_input_rkf a',$arr)->result();
        $data['list']     = $list;
 
        $response   = array(
            'status' => true,
            'table' => $this->load->view($this->path.'rekap_rencana_kerja/table',$data,true),
        );
        render($response,'json');
    }

    function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_anggaran      = post('kode_anggaran');

        $kode_cabang        = post('kode_cabang');
        $kode_cabang_txt    = post('kode_cabang_txt');

        // pengecekan akses cabang
        $access = get_access($this->controller);
        check_access_divisi($this->controller,$kode_anggaran,$kode_cabang,$access);

        $dt = json_decode(post('data'),true);

        $header = $dt['#result1']['header'][0];

        $data = [];
        foreach(['#result1'] as $name){
            if(isset($dt[$name])):
                $count2 = 0;
                foreach($dt[$name]['data'] as $k => $v){
                    $count2 = count($v);
                    $detail = [];
                    for ($i=0; $i < count($v) ; $i++) { 
                        $detail[] = $v[$i];
                    }
                    if($count2 < 8):
                        for ($i=$count2; $i <= (8 - $count2) ; $i++) { 
                            $detail[] = '';
                        }
                    endif;
                    $data[] = $detail;
                }
            endif;
        }

        $config[] = [
            'title' => 'rkf'.' ('.get_view_report().')',
            'header' => $header,
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'rkf_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }
}