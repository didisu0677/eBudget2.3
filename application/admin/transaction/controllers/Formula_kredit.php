<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Formula_kredit extends BE_Controller {
    var $path = 'transaction/budget_planner/';
    var $controller = 'formula_kredit';
    var $detail_tahun;
    var $kode_anggaran;
    var $real_status = false;
    var $real_tahun = [];
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

        $this->checkReal();
    }

    private function checkReal(){
        foreach($this->detail_tahun as $k => $v){
            if($v->singkatan == arrSumberData()['real']):
                $this->real_status = true;
                if(!in_array($v->tahun,$this->real_tahun)) array_push($this->real_tahun,$v->tahun);
            endif;
        }
    }
    
    function index($p1="") { 
        $access                     = get_access($this->controller);
        $data                       = data_cabang();
        $data['path']               = $this->path;
        $data['anggaran']           = get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->row_array();
        $data['detail_tahun']       = $this->detail_tahun;
        $data['access_additional']  = $access['access_additional'];
        render($data,'view:'.$this->path.'formula_kredit/index');
    }

    function data($kode_anggaran="", $kode_cabang="") {
        $data_finish['kode_anggaran']   = $kode_anggaran;
        $data_finish['kode_cabang']     = $kode_cabang;
        $access = get_access('formula_kredit',$data_finish);
        $access_edit = false;
        if($access['access_edit'] && $kode_cabang == user('kode_cabang')):
            $access_edit = true;
        elseif($access['access_edit'] && $access['access_additional']):
            $access_edit = true;
        endif;

        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
        $dataDefault['anggaran']    = $anggaran;
        $dataDefault['kode_cabang'] = $kode_cabang;
        $dataDefault['detail_tahun']= $this->detail_tahun;
        $dataDefault['access_edit'] = $access_edit;

        // amort
        $amort = '1454399';
        $amort_coa  = get_data('tbl_m_coa',[
            'where' => [
                'glwnco' => $amort,
                'kode_anggaran' => $anggaran->kode_anggaran
            ]
        ])->row();
        $amort_dt   = get_data('tbl_formula_kredit',[
            'where' => [
                'kode_anggaran' => $anggaran->kode_anggaran,
                'kode_cabang'   => $kode_cabang,
                'glwnco'        => $amort,
            ]
        ])->result_array();
        $amort_core = [];
        if($this->real_status && $anggaran->tahun_terakhir_realisasi != $anggaran->tahun_anggaran):
            $amort_core = get_data_core([$amort],$this->real_tahun,'TOT_'.$kode_cabang);
        else:
            $amort_core = get_data_core([$amort],[$anggaran->tahun_terakhir_realisasi],'TOT_'.$kode_cabang);
        endif;
        $data = $dataDefault;
        $data['amort']      = $amort_coa;
        $data['amort_dt']   = $amort_dt;
        $data['amort_core'] = $amort_core;
        $view = $this->load->view($this->path.'formula_kredit/table',$data,true);
        
        $field_tabel     = get_field('tbl_rate','name');
        if (in_array('TOT_'.$kode_cabang, $field_tabel)):
            $TOT_cab = 'TOT_' . $kode_cabang ;   
        else:
            $TOT_cab = 0 ;  
        endif;

        // rincian kredit
        $dt_coa = get_data('tbl_produk_kredit a',[
            'select' => "a.coa,a.bunga_kredit,b.glwdes,c.glwdes as bunga_name,ifnull(".$TOT_cab.",0) as rate",
            'join'   => [
                "tbl_m_coa b on b.glwnco = a.coa and b.kode_anggaran = '$anggaran->kode_anggaran'",
                "tbl_m_coa c on c.glwnco = a.bunga_kredit and c.kode_anggaran = '$anggaran->kode_anggaran'",
                "tbl_rate e on e.no_coa = a.coa and e.kode_anggaran = '$anggaran->kode_anggaran' type left"
            ],
            'where' => [
                'a.kode_anggaran' => $anggaran->kode_anggaran
            ]
        ])->result();

        $arr_coa        = [];
        $arr_coa_kredit = [];
        foreach($dt_coa as $k => $v){
            if(!in_array($v->coa, $arr_coa)) array_push($arr_coa,$v->coa);
            if(!in_array($v->coa, $arr_coa_kredit)) array_push($arr_coa_kredit,$v->coa);
            if(!in_array($v->bunga_kredit, $arr_coa)) array_push($arr_coa,$v->bunga_kredit);
        }
        
        $dt_kredit   = get_data('tbl_budget_plan_kredit a',[
            'where' => [
                'a.kode_anggaran' => $anggaran->kode_anggaran,
                'a.kode_cabang'   => $kode_cabang,
                'a.coa'        => $arr_coa,
            ]
        ])->result_array();

        $dt_bunga   = get_data('tbl_formula_kredit a',[
            'where' => [
                'a.kode_anggaran' => $anggaran->kode_anggaran,
                'a.kode_cabang'   => $kode_cabang,
                'a.glwnco != '    => $arr_coa_kredit,
            ]
        ])->result_array();

        $data_core = [];
        if($this->real_status && $anggaran->tahun_terakhir_realisasi != $anggaran->tahun_anggaran):
            $data_core = get_data_core($arr_coa,$this->real_tahun,'TOT_'.$kode_cabang);
        else:
            $data_core = get_data_core($arr_coa,[$anggaran->tahun_terakhir_realisasi],'TOT_'.$kode_cabang);
        endif;
        $data = $dataDefault;
        $data['dt_coa']     = $dt_coa;
        $data['dt_kredit']  = $dt_kredit;
        $data['data_core']  = $data_core;
        $data['dt_bunga']   = $dt_bunga;
        $view2 = $this->load->view($this->path.'formula_kredit/table2',$data,true);

        render([
            'table'     => $view,
            'table2'    => $view2,
            'access_edit' => $access_edit
        ],'json');
    }

    function save_perubahan($anggaran="",$cabang="") {
        $anggaran = get_data('tbl_tahun_anggaran',[
            'where'  => [
                'kode_anggaran' => $anggaran,
            ],
        ])->row();

        $dt   = json_decode(post('json'),true);
        foreach($dt as $k => $v){
            $x = explode('-', $k);
            $page   = $x[0];
            $coa    = $x[1];
            $tahun  = $x[2];

            $where = [
                'kode_anggaran' => $anggaran->kode_anggaran,
                'kode_cabang'   => $cabang,
                'glwnco'        => $coa,
                'tahun_core'    => $tahun,
            ];
            $ck = get_data('tbl_formula_kredit',[
                'select'    => 'id,changed',
                'where'     => $where,
            ])->row();

            $changed = [];
            if($ck):
                $changed = json_decode($ck->changed);
            endif;

            $data = [];
            foreach($v as $k2 => $v2){
                $val = filter_money($v2);
                if($page != 'rate'):
                    $val = insert_view_report($val);
                endif;
                $data[$k2] = $val;
                if(!in_array($k2,$changed)) array_push($changed,$k2);
            }

            $data['id'] = '';
            $data['changed'] = json_encode($changed);
            if($tahun == $anggaran->tahun_anggaran):
                $data['parent_id'] = '0';
            else:
                $data['parent_id'] = $kode_cabang;
            endif;
            if($ck):
                $data['id'] = $ck->id;
            else:
                $data = array_merge($data,$where);
            endif;
            $res = save_data('tbl_formula_kredit',$data,[],false);

        }
    }

    function delete($anggaran="",$cabang=""){
        $coa = post('id');
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$anggaran)->row();
        $cabang = get_data('tbl_m_cabang',[
            'where' => [
                'kode_cabang' => $cabang,
                'kode_anggaran' => $anggaran->kode_anggaran
            ]
        ])->row();
        if($coa && $anggaran && $cabang):
            $ck = get_data('tbl_formula_kredit',[
                'select' => 'id,changed',
                'where'  => [
                    'kode_cabang'   => $cabang->kode_cabang,
                    'kode_anggaran' => $anggaran->kode_anggaran,
                    'glwnco'        => $coa,
                ]
            ])->result();
            foreach($ck as $k => $v){
                $changed = json_decode($v->changed);
                $edited = [];
                if(in_array('rate',$changed)):
                    array_push($edited,'rate');
                endif;
                update_data('tbl_formula_kredit',['changed' => json_encode($edited)],'id',$v->id);
            }
            render(['status' => 'success', 'message' => lang('berhasil')],'json');
        else:
            render(['status' => false,'message' => lang('data_not_found')],'json');
        endif;
    }
}