<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Kolektibilitas extends BE_Controller {
    var $path = 'transaction/budget_planner/';
    var $controller = 'kolektabilitas/';
    var $controller2 = 'kolektibilitas';

    var $detail_tahun;
    var $kode_anggaran;
    var $arr_sumber_data = array();
    var $arr_tahun_core = array();
    var $history_status = false;
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
        $this->check_sumber_data();
    }

    private  function check_sumber_data($sumber_data=""){
        // $key = array_search($sumber_data, array_map(function($element){return $element->sumber_data;}, $this->detail_tahun));
        // if(strlen($key)>0):
        //     array_push($this->arr_sumber_data,$sumber_data);
        // endif;
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
        $a = get_access('kolektibilitas');
        $data = data_cabang('kolektibilitas');
        $data['path']               = $this->path;
        $data['detail_tahun']       = $this->detail_tahun;
        $data['access_additional']  = $a['access_additional'];
        render($data,'view:'.$this->path.$this->controller.'index');
    }

    private function get_coa($group){
        $ls = get_data('tbl_produk_kredit',"is_active = 1 and grup =  '$group' and kode_anggaran = '".user('kode_anggaran')."'")->result();
        $data           = '<option value=""></option>';
        foreach($ls as $e2) {
            $data       .= '<option value="'.$e2->coa.'">'.remove_spaces($e2->nama_produk_kredit).'</option>';
        }
        return $data;
    }

    function save_perubahan(){
        $data   = json_decode(post('json'),true);
        $kode_anggaran = post('kode_anggaran');
        $kode_cabang = post('kode_cabang');

        // pengecekan save untuk cabang
        check_save_cabang($this->controller2,$kode_anggaran,$kode_cabang,'access_edit');

        foreach($data as $id => $record) {
            $arr = explode("-", $id);
            $dt_id = $arr[0]; 
            $table = $arr[1];
            $arrSaved = [];
            $changed  = [];
            if($table == 'tbl_kolektibilitas_detail'):
                $get_data = get_data($table,[
                    'select' => 'changed',
                    'where'  => ['id' => $dt_id]
                ])->row_array();
                $changed = json_decode($get_data['changed']);
                if(!is_array($changed)):
                    $changed = [];
                endif;
            endif;
            foreach ($record as $k => $v) {
                $value = str_replace('.', '', $v);
                $value = str_replace(',', '.', $value);
                $arrSaved[$k] = $value;
                if($table == 'tbl_kolektibilitas_detail'):
                    $value = insert_view_report($value);
                    $arrSaved[$k] = $value;
                    if(!in_array($k,$changed)):
                        array_push($changed,$k);
                        $arrSaved['changed'] = json_encode($changed);
                    endif;
                endif;

            }
            update_data($table,$arrSaved,'id',$dt_id); 
        }

        render([
            'status' => true,
            'message'=> lang('data_berhasil_diperbaharui')
        ],'json');
    }

    function save(){
        $kode_cabang    = post('kode_cabang');
        $ckode_anggaran = user('kode_anggaran');
        $anggaran       = get_data('tbl_tahun_anggaran','kode_anggaran',$ckode_anggaran)->row();
        $tahun          = $anggaran->tahun_anggaran;
        $cabang         = get_data('tbl_m_cabang','kode_cabang',$kode_cabang)->row();

        // pengecekan save untuk cabang
        check_save_cabang($this->controller2,$ckode_anggaran,$kode_cabang,'access_edit');

        $where = [
            'kode_anggaran'         => $ckode_anggaran,
            'tahun'                 => $anggaran->tahun_anggaran,
            'kode_cabang'           => $kode_cabang,
        ];

        $c = $where;
        $c['keterangan_anggaran'] = $anggaran->keterangan;
        $c['cabang'] = $cabang->nama_cabang;

        $this->validate($where);

        $this->save_kolektibilitas(1,$c,$where);
        $this->save_kolektibilitas(2,$c,$where);

        render([
            'status'    => 'success',
            'message'   => lang('data_berhasil_disimpan')
        ],'json');
    }

    private function save_kolektibilitas($tipe,$data,$where){
        $dt_id  = post('dt_id');
        $coa    = post('coa');
        if($tipe == 2){
            $dt_id    = post('dt_id_konsumtif');
            $coa      = post('coa_konsumtif');
        }

        $arrID = array();
        if($coa){
            foreach ($coa as $k => $v) {
                $c = $data;
                $c['username'] = user('username');
                $c['coa_produk_kredit'] = $coa[$k];
                $c['tipe'] = $tipe;

                $ck_data    = $where;
                $ck_data['id'] = $dt_id[$k];
                $ck_data['tipe'] = $tipe;
                $cek        = get_data('tbl_kolektibilitas',[
                    'where'         => $ck_data,
                ])->row();
                if(!isset($cek->id)) {
                    $c['create_by'] = user('username');
                    $c['create_at'] = date("Y-m-d H:i:s");
                    $id = insert_data('tbl_kolektibilitas',$c);
                }else{
                    $id = $dt_id[$k];
                    $c['update_by'] = user('username');
                    $c['update_at'] = date("Y-m-d H:i:s");
                    update_data('tbl_kolektibilitas',$c,$ck_data);
                }
                foreach ($this->arr_tahun_core as $tahun) {
                    $c_data = [
                        'id_kolektibilitas' => $id,
                        'tipe'  => $tipe,
                        'tahun_core' => $tahun,
                    ];
                    $cek        = get_data('tbl_kolektibilitas_detail',[
                        'where'         => $c_data,
                    ])->row();
                    if(!isset($cek->id)) {
                        $c_data['create_by'] = user('username');
                        $c_data['create_at'] = date("Y-m-d H:i:s");
                        $c_data['changed'] = '[]';
                        insert_data('tbl_kolektibilitas_detail',$c_data);
                    }
                }
                array_push($arrID, $id);
            }
        }

        if(post('id') && count($arrID)>0){
            $ck_data = $where;
            $ck_data['id not'] = $arrID;
            $ck_data['tipe']   = $tipe;
            $ck_data['default']= 0;
            delete_data('tbl_kolektibilitas',$ck_data);

            // $ck_data_detail = [
            //     'tipe' => $tipe,
            //     'id_kolektibilitas not' => $arrID,
            // ];
            // delete_data('tbl_kolektibilitas_detail',$ck_data_detail);
        }elseif(post('id')){
        	$check = get_data('tbl_kolektibilitas','id',post('id'))->row();
        	if(isset($check->tipe) && $check->tipe == $tipe):
        		$ck_data = $where;
	            $ck_data['tipe']   = $tipe;
	            $ck_data['default']= 0;
	            delete_data('tbl_kolektibilitas',$ck_data);
        	endif;
        }
    }

    function data($anggaran="", $cabang="", $tipe = 'table') {
        $menu = menu();
        $ckode_anggaran = $anggaran;
        $ckode_cabang = $cabang;

        $data_finish['kode_anggaran']   = $ckode_anggaran;
        $data_finish['kode_cabang']     = $ckode_cabang;
        $access = get_access('kolektibilitas',$data_finish);
        $access_edit = false;
        if($access['access_edit'] && $ckode_cabang == user('kode_cabang')):
            $access_edit = true;
        elseif($access['access_edit'] && $access['access_additional']):
            $access_edit = true;
        endif;

        $data['akses_ubah'] = $access_edit;
        $data['access_additional']  = $access['access_additional'];
        $data['current_cabang'] = $cabang;
        $data['kode_anggaran'] = $ckode_anggaran;
        $data['title']  = ['NPL Total Kredit','NPL Kredit Produktif','NPL Kredit Konsumtif'];
        $data['title_'] = ['A. ','B. ','C. '];

        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$ckode_anggaran)->row();
        if(!$anggaran):
            render(['status' => false,'message' => 'anggaran not found'],'json');exit();
        endif;
        // pengecekan akses cabang
        check_access_cabang($this->controller2,$ckode_anggaran,$ckode_cabang,$access);

        // data core / history
        $data_core = [];
        if($this->history_status):
            $arr_coa = [];
            $dt_coa = get_data('tbl_kolektibilitas',[
                'select' => 'DISTINCT coa_produk_kredit as coa',
                'where'  => "kode_anggaran = '$ckode_anggaran' and kode_cabang = '$ckode_cabang'"
            ])->result_array();
            foreach ($dt_coa as $v) {
                $arr_coa[] = $v['coa'];
            }

            foreach ($this->arr_tahun_core as $v) {
                $tbl_history = 'tbl_history_'.$v;
                $tbl_history_status = true;
                if(!$this->db->table_exists($tbl_history)):
                    $tbl_history_status = false;
                endif;
                $column = 'TOT_'.$ckode_cabang;
                if ($tbl_history_status && !$this->db->field_exists($column, $tbl_history)):
                    $tbl_history_status = false;
                endif;
                if($tbl_history_status):
                    $data_core[$v] = get_data($tbl_history.' as a',[
                        'select' => "
                            coalesce(sum(case when bulan = '1' then ".$column." end), 0) as B_01,
                            coalesce(sum(case when bulan = '2' then ".$column." end), 0) as B_02,
                            coalesce(sum(case when bulan = '3' then ".$column." end), 0) as B_03,
                            coalesce(sum(case when bulan = '4' then ".$column." end), 0) as B_04,
                            coalesce(sum(case when bulan = '5' then ".$column." end), 0) as B_05,
                            coalesce(sum(case when bulan = '6' then ".$column." end), 0) as B_06,
                            coalesce(sum(case when bulan = '7' then ".$column." end), 0) as B_07,
                            coalesce(sum(case when bulan = '8' then ".$column." end), 0) as B_08,
                            coalesce(sum(case when bulan = '9' then ".$column." end), 0) as B_09,
                            coalesce(sum(case when bulan = '10' then ".$column." end), 0) as B_10,
                            coalesce(sum(case when bulan = '11' then ".$column." end), 0) as B_11,
                            coalesce(sum(case when bulan = '12' then ".$column." end), 0) as B_12,
                            a.account_name,
                            a.coa,
                            a.gwlsbi,
                            a.glwnco,
                            b.kali_minus,
                            ",
                        'where_in'  => ['a.glwnco' => $arr_coa],
                        'join'      => [
                            "tbl_m_coa b on a.glwnco = b.glwnco and b.kode_anggaran = '".$anggaran->kode_anggaran."'"
                        ],
                        'group_by'  => 'a.glwnco',
                    ])->result_array();
                endif;
            }
        endif;
        $data['data_core'] = $data_core;

        $select = '
                a.*,
                b.coa,
                b.nama_produk_kredit,
            ';
        $arr['select'] = $select;
        if($anggaran) {
            $arr['where']['a.kode_anggaran']  = $ckode_anggaran;
        }
        if($cabang) {
            $arr['where']['a.kode_cabang']  = $ckode_cabang;
        }

        //table npl
        $arrWhereNpl = $arr;
        $arrWhereNpl['select'] = 'a.*';
        $listNpl = get_data('tbl_kolektibilitas_npl a',$arrWhereNpl)->result_array();
        // end table npl

        // table total kredit
        $arr2 = $arr;
        $arrWhere = $arr;
        $arrWhere['where']['default'] = 2;
        $arrWhere['select'] = 'a.*';
        $arrWhere['order_by']   = 'a.id,a.tipe';
        $listTotal = get_data('tbl_kolektibilitas a',$arrWhere)->result();
        $arrWhere['select'] = 'c.*,ifnull(d.parent_id,0) as parent_index,hasil1,hasil2,hasil3,hasil4,hasil5,hasil6,hasil7,hasil8,hasil9,hasil10,hasil11,hasil12';
        $arrWhere['join'][] = 'tbl_kolektibilitas_detail c on c.id_kolektibilitas = a.id';
        $arrWhere['join'][] = 'tbl_indek_besaran d on a.coa_produk_kredit = d.coa and a.kode_anggaran = d.kode_anggaran and a.kode_cabang = d.kode_cabang type LEFT';
        $listTotalDetail    = get_data('tbl_kolektibilitas a',$arrWhere)->result_array();
        // end total kredit

        $arr['join'][]     = 'tbl_produk_kredit b on b.coa = a.coa_produk_kredit';
        $arr['order_by']   = 'a.id,a.tipe';
        $arr['where']['b.kode_anggaran'] = $ckode_anggaran;

        $listAll = get_data('tbl_kolektibilitas a',$arr)->result();

        $arrWhere = $arr;
        $arrWhere['where']['default'] = 0;
        $list        = get_data('tbl_kolektibilitas a',$arrWhere)->result();
        $arrWhere['where']['default'] = 1;
        $listDefault = get_data('tbl_kolektibilitas a',$arrWhere)->result();

        $s_join = 'd.tahun_core = c.tahun_core and d.kode_cabang = a.kode_cabang and d.kode_anggaran = a.kode_anggaran type left';
        $s_select = ',d.P_01,d.P_02,d.P_03,d.P_04,d.P_05,d.P_06,d.P_07,d.P_08,d.P_09,d.P_10,d.P_11,d.P_12';
        $select .= 'c.*';
        $arr['select'] = $select.$s_select;
        $arr['join'][]     = 'tbl_kolektibilitas_detail c on c.id_kolektibilitas = a.id';
        $arr['join'][]     = 'tbl_budget_plan_kredit d on d.coa = a.coa_produk_kredit and '.$s_join;
        $listDetail        = get_data('tbl_kolektibilitas a',$arr)->result_array();

        // table
        $data['listTotal']          = $listTotal;
        $data['listTotalDetail']    = $listTotalDetail;
        $data['listNpl']            = $listNpl;
        $data['listDefault']        = $listDefault;
        $data['listAll']            = $listAll;
        $data['list']   = $list;
        $data['detail'] = $listDetail;
        $data['detail_tahun'] = $this->detail_tahun;
        $data['anggaran'] = $anggaran;

        $data['tipe'] = 1;
        $view_detail  = $this->load->view($this->path.$this->controller.'detail',$data,true);
        $data['tipe'] = 2;
        $view_detail  .= $this->load->view($this->path.$this->controller.'detail',$data,true);

        $data['tipe'] = 1;
        $view_produktif = $this->load->view($this->path.$this->controller.'table',$data,true);
        $view_produktif_sum = $this->load->view($this->path.$this->controller.'detail_sum',$data,true);
        $data['tipe'] = 2;
        $view_konsumtif = $this->load->view($this->path.$this->controller.'table',$data,true);

        //view total kredit
        $arrWhere = $arr2;
        $arrWhere['where']['default'] = 2;
        $arrWhere['order_by']   = 'a.id,a.tipe';
        $arrWhere['select'] = 'a.coa_produk_kredit,c.*';
        $arrWhere['join'][] = 'tbl_kolektibilitas_detail c on c.id_kolektibilitas = a.id';
        $listTotalKredit    = get_data('tbl_kolektibilitas a',$arrWhere)->result();

        $data['listTotalKredit'] = $listTotalKredit;
        $view_total_kredit = $this->load->view($this->path.$this->controller.'total',$data,true);

        // chart
        $chart = $this->get_chart($data);
        $total_npl = $this->session->npl2;
        foreach ($total_npl as $k => $v) {
            $total_npl[$k] = custom_format($v,false,2);
        }

        $response   = array(
            'status'      => true,
            'produktif'   => $view_produktif,
            'produktif_sum'   => $view_produktif_sum,
            'konsumtif'   => $view_konsumtif,
            'detail'      => $view_detail,
            'total_kredit'=> $view_total_kredit,
            'chart'       => $chart,
            'table_npl'   => $total_npl,
            'edit'        => $access_edit
        );
        $response['opt_produktif'] = $this->get_coa('122502');
        $response['opt_konsumtif'] = $this->get_coa('122506');
       
        render($response,'json');
    }

    function get_data() {
        $dt = get_data('tbl_kolektibilitas','id',post('id'))->row();
        $list = get_data('tbl_kolektibilitas',[
            'where' => [
                'kode_anggaran' => $dt->kode_anggaran,    
                'tahun' => $dt->tahun,
                'kode_cabang' => $dt->kode_cabang,
                'tipe'  => $dt->tipe,
                'default'   => 0,
            ],
        ])->result_array();
        $data['detail'] = $dt;
        $data['data'] = $list;
        render($data,'json');

    }

    function get_chart($dt){
        $npl = $this->session->npl;
        foreach ($npl as $k => $v) {
            $npl[$k] = number_format($v,2,'.','');
        }
        $data = [
            'tipe_1' => [],
            'tipe_2' => [],
            'npl'    => $npl,
        ];
        foreach ($dt['listNpl'] as $k => $v) {
            $tipe = 'tipe_'.$v['tipe'];
            if($v['tahun_core'] == $dt['anggaran']->tahun_anggaran):
                for ($i=1; $i <= 12 ; $i++) { 
                    $v_field  = 'B_' . sprintf("%02d", $i);
                    $column = month_lang($i,true);
                    $data[$tipe][$column] = number_format($v[$v_field],2,'.','');
                }
            endif;
        }
        return $data;
    }

    function input_npl($anggaran="", $cabang=""){
        $menu = menu();
        $ckode_anggaran = $anggaran;
        $ckode_cabang = $cabang;

        $data_finish['kode_anggaran']   = $ckode_anggaran;
        $data_finish['kode_cabang']     = $ckode_cabang;
        $access = get_access('kolektibilitas',$data_finish);
        $access_edit = false;
        if($access['access_edit'] && $ckode_cabang == user('kode_cabang')):
            $access_edit = true;
        elseif($access['access_edit'] && $access['access_additional']):
            $access_edit = true;
        endif;

        $data['akses_ubah'] = $access_edit;
        $data['current_cabang'] = $cabang;

        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$ckode_anggaran)->row();
        $cabang         = get_data('tbl_m_cabang',[
            'where' => [
                'kode_cabang'   => $ckode_cabang,
                'kode_anggaran' => $ckode_anggaran
            ]
        ])->row();

        // pengecekan akses cabang
        if(!$anggaran):
            render(['status' => false,'message' => 'anggaran not found'],'json');exit();
        elseif(!$cabang):
            render(['status' => false,'message' => 'cabang not found'],'json');exit();
        endif;
        check_access_cabang($this->controller2,$ckode_anggaran,$ckode_cabang,$access);

        if($anggaran) {
            $arr['where']['a.kode_anggaran']  = $ckode_anggaran;
        }
        if($cabang) {
            $arr['where']['a.kode_cabang']  = $ckode_cabang;
        }


        // check table tbl_kolektibilitas_npl
        $arrTipe = [1,2];
        foreach ($arrTipe as $k => $v) {
            foreach ($this->arr_tahun_core as $tahun) {
                $arrCk = $arr;
                $arrCk['where']['tipe'] = $v;
                $arrCk['where']['tahun_core'] = $tahun;
                $ck = get_data('tbl_kolektibilitas_npl a',$arrCk)->row();
                if(!$ck):
                    $dtSaved = [
                        'kode_anggaran' => $ckode_anggaran,
                        'tahun'         => $anggaran->tahun_anggaran,
                        'tahun_core'    => $tahun,
                        'kode_cabang'   => $ckode_cabang,
                        'keterangan_anggaran'   => $anggaran->keterangan,
                        'cabang' => $cabang->nama_cabang,
                        'tipe' => $v,
                        'username'  => user('username'),
                        'create_at' => date("Y-m-d H:i:s"),
                        'create_by' => user('username'),
                    ];
                    $id_npl = insert_data('tbl_kolektibilitas_npl',$dtSaved);
                endif;
            }
        }
        //

        // check coa default
        $arrDefaultCoa = [
            '1454321-1-1',
            '1454327-1-2',
            '122502-2-1',
            '122506-2-2',
        ];
        foreach ($arrDefaultCoa as $k => $v) {
            $d_         = explode('-', $v);
            $coa        = $d_[0];
            $default    = $d_[1];
            $tipe       = $d_[2];

            $arrCk = $arr;
            $arrCk['where']['coa_produk_kredit'] = $coa;
            $arrCk['where']['default']           = $default;
            $arrCk['where']['tipe']              = $tipe;
            $ck = get_data('tbl_kolektibilitas a',$arrCk)->row();
            if(!$ck):
                $dtSaved = [
                    'kode_anggaran' => $ckode_anggaran,
                    'tahun'         => $anggaran->tahun_anggaran,
                    'kode_cabang'   => $ckode_cabang,
                    'keterangan_anggaran'   => $anggaran->keterangan,
                    'cabang' => $cabang->nama_cabang,
                    'tipe' => $tipe,
                    'default' => $default,
                    'coa_produk_kredit' => $coa,
                    'username'  => user('username'),
                    'create_at' => date("Y-m-d H:i:s"),
                    'create_by' => user('username'),
                ];
                $id_kolektibilitas = insert_data('tbl_kolektibilitas',$dtSaved);
                // insert detail
                foreach ($this->arr_tahun_core as $tahun) {
                    $dtSavedDetail = [
                        'id_kolektibilitas' => $id_kolektibilitas,
                        'tahun_core'    => $tahun,
                        'tipe'  => $tipe,
                        'changed' => '[]'
                    ];
                    insert_data('tbl_kolektibilitas_detail',$dtSavedDetail);
                }
            endif;
        }
        // end check coa default

        $arr['order_by']   = 'a.tipe';
        $arr['where']['a.tipe']     = [1,2];
        $all = get_data('tbl_kolektibilitas_npl a',$arr)->result_array();
        $arr['where']['tahun_core'] = $anggaran->tahun_anggaran;
        $list = get_data('tbl_kolektibilitas_npl a',$arr)->result();
        $data['list'] = $list;
        $data['all']  = $all;
        $data['detail_tahun'] = $this->detail_tahun;

        $view = $this->load->view($this->path.$this->controller.'table_npl',$data,true);
        $response   = array(
            'status' => true,
            'table' => $view,
            'data'  => $data,
        );
       
        render($response,'json');
        
    }

    function delete(){
        $x = post('id');
        $x = explode('_', $x);
        $status = true;

        if(!isset($x[0])) $status = false;
        if(!isset($x[1])) $status = false;
        if(!isset($x[2])) $status = false;
        if(!isset($x[3])) $status = false;

        $access = get_access($this->controller2);
        if(!$access['access_additional']) $status = false;

        if(!$status):
            render(['status' => 'failed', 'message' => lang('izin_ditolak')],'json');exit();
        endif;

        // pengecekan save untuk cabang
        $kode_anggaran  = $x[0];
        $kode_cabang    = $x[1];
        $tipe           = $x[2];
        $col            = $x[3];
        check_save_cabang($this->controller2,$kode_anggaran,$kode_cabang,'access_edit');

        $ls = get_data('tbl_kolektibilitas a',[
            'select' => 'b.id,changed',
            'join' => [
                'tbl_kolektibilitas_detail b on b.id_kolektibilitas = a.id',
            ],
            'where' => [
                'a.kode_anggaran' => $kode_anggaran,
                'a.kode_cabang'   => $kode_cabang,
                'a.tipe'          => $tipe,
                'a.default'       => 2
            ]
        ])->result();

        foreach ($ls as $k => $v) {
            $changed = json_decode($v->changed,true);
            if(!is_array($changed)) $changed = [];

            $data = [
                'update_by' => user('username'),
                'update_at' => date("Y-m-d H:i:s"),
            ];
            for ($i=1; $i <= 12 ; $i++) { 
                $field   = 'B_' . sprintf("%02d", $i);
                if (($key = array_search($field.'_'.$col,$changed)) !== false):
                    unset($changed[$key]);
                    sort($changed);
                endif;
                $data[$field.'_'.$col] = 0;
            }
            $data['changed'] = json_encode($changed);
            update_data('tbl_kolektibilitas_detail',$data,'id',$v->id);
        }

        render(['status' => 'success','message' => lang('data_berhasil_dihapus')],'json');
    }

    function validate($where){
        $dt_id  = post('dt_id');
        $coa    = post('coa');
        $status = true;
        $data   = [];
        if($coa):
            $arrCoa = [];
            foreach ($coa as $k => $v) {
                $ck_data         = $where;
                $ck_data['coa_produk_kredit'] = $coa[$k];
                if($dt_id[$k]):
                    $ck_data['id != '] = $dt_id[$k];
                endif;
                $cek        = get_data('tbl_kolektibilitas',[
                    'where'         => $ck_data,
                ])->row();

                if(in_array($coa[$k], $arrCoa)):
                    $status = false;
                else:
                    array_push($arrCoa,$coa[$k]);
                endif;

                if(isset($cek->id)):
                    $status = false;
                endif;

                if(!$status):
                    $get_coa = get_data('tbl_produk_kredit',[
                        'where' => [
                            'coa' => $coa[$k],
                            'kode_anggaran' => user('kode_anggaran')
                        ]
                    ])->row();
                    $message = 'COA "'.$coa[$k].'" ';
                    if($get_coa):
                        $message = 'COA "'.$get_coa->coa.'-'.remove_spaces($get_coa->nama_produk_kredit).'" ';
                    endif;
                    render([
                        'status'    => 'info',
                        'message'   => $message.lang('sudah_ada'),
                    ],'json');
                    exit();
                endif;

            }
        endif;

        $status   = true;
        $dt_id    = post('dt_id_konsumtif');
        $coa      = post('coa_konsumtif');
        if($coa):
            $arrCoa = [];
            foreach ($coa as $k => $v) {
                $ck_data         = $where;
                $ck_data['coa_produk_kredit'] = $coa[$k];
                if($dt_id[$k]):
                    $ck_data['id != '] = $dt_id[$k];
                endif;
                $cek        = get_data('tbl_kolektibilitas',[
                    'where'         => $ck_data,
                ])->row();

                if(in_array($coa[$k], $arrCoa)):
                    $status = false;
                else:
                    array_push($arrCoa,$coa[$k]);
                endif;

                if(isset($cek->id)):
                    $status = false;
                endif;

                if(isset($cek->id)):
                    $status = false;
                endif;

                if(!$status):
                    $get_coa = get_data('tbl_produk_kredit',[
                        'where' => [
                            'coa' => $coa[$k],
                            'kode_anggaran' => user('kode_anggaran')
                        ]
                    ])->row();
                    $message = 'COA "'.$coa[$k].'" ';
                    if($get_coa):
                        $message = 'COA "'.$get_coa->coa.'-'.remove_spaces($get_coa->nama_produk_kredit).'" ';
                    endif;
                    render([
                        'status'    => 'info',
                        'message'   => $message.lang('sudah_ada'),
                    ],'json');
                    exit();
                endif;
            }
        endif;
    }

    function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_anggaran      = post('kode_anggaran');

        $kode_cabang        = post('kode_cabang');
        $kode_cabang_txt    = post('kode_cabang_txt');

        // pengecekan akses cabang
        $access = get_access($this->controller2);
        check_access_cabang($this->controller2,$kode_anggaran,$kode_cabang,$access);

        $dt = json_decode(post('data'),true);

        $header = $dt['.tbl-produktif']['header'];
        $config[] = [
            'title'     => lang('krd_produktif'),
            'header'    => $header[0],
            'data'      => $dt['.tbl-produktif']['data']
        ];

        $header = $dt['.tbl-konsumtif']['header'];
        $config[] = [
            'title'     => lang('krd_konsumtif'),
            'header'    => $header[0],
            'data'      => $dt['.tbl-konsumtif']['data']
        ];

        $header = $dt['.tbl-input-npl']['header'];
        $title = 'Non Performing Loan (NPL)';
        $data  = [];
        foreach ($dt['.tbl-input-npl']['data'] as $k => $v) {
            $detail = [
                $v[0]
            ];
            for ($i=1; $i < count($v) ; $i++) { 
                $detail[] = filter_money($v[$i]);
            }

            $data[] = $detail;
        }
        $detail = [];
        foreach($header[1] as $i => $v) {
            $val = $v;
            if($i != 0):
                $val = filter_money($val);
            endif;
            $detail[] = $val;
        }
        $data[] = $detail;

        $config[] = [
            'title'     => $title,
            'header'    => $header[0],
            'data'      => $data
        ];

        // ['NPL Total Kredit','NPL Kredit Produktif','NPL Kredit Konsumtif'];
        $d = $dt['.d-total-kredit'];
        $header = [];
        $header[] = $d['header'][1][0];
        $header[] = $d['header'][1][1];
        $header[] = $d['header'][1][2];
        $header[] = $d['header'][1][3];
        $header[] = '';
        $header[] = '';
        $header[] = '';
        $header[] = '';
        $header[] = $d['header'][1][4];
        $header[] = $d['header'][1][5];

        $title = 'A. NPL Total Kredit ('.get_view_report().')';
        $data   = [];
        $detail = [];
        $detail[] = '';
        $detail[] = '';
        $detail[] = '';
        $detail[] = '1';
        $detail[] = '2';
        $detail[] = '3';
        $detail[] = '4';
        $detail[] = '5';
        $detail[] = '';
        $detail[] = '';
        $data[] = $detail;

        foreach($d['data'] as $k => $v){
            $detail = [
                $v[0],
                $v[1],
            ];
            for ($i=2; $i < count($v) ; $i++) { 
                $detail[] = filter_money($v[$i]);
            }
            $data[] = $detail;
        }

        $config[] = [
            'title'     => $title,
            'header'    => $header,
            'data'      => $data
        ];

        $title = 'B. NPL KRD Produktif ('.get_view_report().')';
        $data   = [];
        $detail = [];
        $detail[] = '';
        $detail[] = '';
        $detail[] = '';
        $detail[] = '1';
        $detail[] = '2';
        $detail[] = '3';
        $detail[] = '4';
        $detail[] = '5';
        $detail[] = '';
        $detail[] = '';
        $data[] = $detail;
            
        $tot_prod = post('tot_prod');
        for ($i=1; $i <= $tot_prod ; $i++) { 
            $dt2 = $dt['detail_1_'.$i];
            $detail = ['',$dt2['title']];
            for ($i2=1; $i2 <=8 ; $i2++) { 
                $detail[] = '';
            }
            $data[] = $detail;

            foreach($dt2['data'] as $k => $v){
                $detail = [
                    $v[0],
                    $v[1],
                ];
                for ($i2=2; $i2 < count($v) ; $i2++) {
                    $detail[] = filter_money($v[$i2]);
                }
                $data[] = $detail;
            }

            $detail = [];
            for ($i2=1; $i2 <=10 ; $i2++) { 
               $detail[] = '';
            }
            $data[] = $detail;
        }

        $config[] = [
            'title'     => $title,
            'header'    => $header,
            'data'      => $data
        ];

        $title = 'C. NPL KRD Konsumtif ('.get_view_report().')';
        $data   = [];
        $detail = [];
        $detail[] = '';
        $detail[] = '';
        $detail[] = '';
        $detail[] = '1';
        $detail[] = '2';
        $detail[] = '3';
        $detail[] = '4';
        $detail[] = '5';
        $detail[] = '';
        $detail[] = '';
        $data[] = $detail;
            
        $tot_kons = post('tot_kons');
        for ($i=1; $i <= $tot_kons ; $i++) { 
            $dt2 = $dt['detail_2_'.$i];
            $detail = ['',$dt2['title']];
            for ($i2=1; $i2 <=8 ; $i2++) { 
                $detail[] = '';
            }
            $data[] = $detail;

            foreach($dt2['data'] as $k => $v){
                $detail = [
                    $v[0],
                    $v[1],
                ];
                for ($i2=2; $i2 < count($v) ; $i2++) {
                    $detail[] = filter_money($v[$i2]);
                }
                $data[] = $detail;
            }

            $detail = [];
            for ($i2=1; $i2 <=10 ; $i2++) { 
               $detail[] = '';
            }
            $data[] = $detail;
        }

        $config[] = [
            'title'     => $title,
            'header'    => $header,
            'data'      => $data
        ];
        // render($config,'json'); exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'kolektabilitas_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }
}