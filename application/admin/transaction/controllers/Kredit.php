<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Kredit extends BE_Controller {
    var $path = 'transaction/budget_planner/';
    var $controller = 'kredit';
    function __construct() {
        parent::__construct();
    }
    
    function index($p1="") { 
        $access         = get_access($this->controller);
        $data = data_cabang('kredit');
        $data['path'] = $this->path;

        $data['detail_tahun']   = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'distinct a.kode_anggaran, a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => [
                'a.kode_anggaran' => user('kode_anggaran'),
                'a.sumber_data'   => array(1,2,3)
            ],
            'order_by' => 'tahun,bulan'
        ])->result_array();
        $data['access_additional']  = $access['access_additional'];
        $data['controller']	= $this->controller;
        render($data,'view:'.$this->path.'kredit/index');

    }

    function data($kode_anggaran, $kode_cabang) {
        $access = get_access('kredit');
        $access_edit = false;
        if($access['access_edit'] && $kode_cabang == user('kode_cabang')):
            $access_edit = true;
        elseif($access['access_edit'] && $access['access_additional']):
            $access_edit = true;
        endif;

        $anggaran   = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
        $cabang     = get_data('tbl_m_cabang',[
            'where' => [
                'kode_cabang' => $kode_cabang,
                'kode_anggaran' => $kode_anggaran
            ]
        ])->row();
        // pengecekan akses cabang
        $a = get_access($this->controller);
        if(!$anggaran):
            render(['status' => false, 'message' => 'anggaran not found'],'json');exit();
        elseif(!$cabang):
            render(['status' => false, 'message' => 'cabang not found'],'json');exit();
        endif;
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$access);

        $all_coa    = ['122502','122506'];

        $dataDefault['access_edit']     = $access_edit;
        $dataDefault['anggaran']        = $anggaran;
        $dataDefault['cabang']          = $cabang;
        $dataDefault['detail_tahun']    = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'distinct a.kode_anggaran, a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => [
                'a.kode_anggaran' => $anggaran->kode_anggaran,
            ],
            'order_by' => 'tahun,bulan'
        ])->result();

        $real_status    = false;
        $arr_not_real   = [];
        $arr_detail_tahun= [];
        foreach($dataDefault['detail_tahun'] as $v){
            if($v->singkatan == arrSumberData()['real']):
                $real_status = true;
            else:
                $arr_not_real[$v->tahun][$v->bulan] = true;
            endif;
            if(!in_array($v->tahun,$arr_detail_tahun)) array_push($arr_detail_tahun,$v->tahun);
        }

        $arr_tahun  = [];
        $arr_tahun_first    = [];
        $arr_tahun_seconds  = [];
        $no = 0;
        for ($i=($anggaran->tahun_anggaran - 3); $i<=($anggaran->tahun_anggaran) ; $i++) { 
            $no++;
            array_push($arr_tahun,$i);
            if($no<=2):
                array_push($arr_tahun_first,$i);
            else:
                array_push($arr_tahun_seconds,$i);
            endif;

        }
        $data_core = get_data_core_sum($all_coa,$arr_tahun,'TOT_'.$kode_cabang);
        $data = $dataDefault;
        $data['data_core']          = $data_core;
        $data['arr_tahun_first']    = $arr_tahun_first;
        $data['arr_tahun_seconds']  = $arr_tahun_seconds;
        $data['arr_tahun']          = $arr_tahun;

        $history = $this->history($data);

        $dt_index_besaran = get_data('tbl_indek_besaran',[
            'where' => [
                'kode_cabang'   => $cabang->kode_cabang,
                'kode_anggaran' => $anggaran->kode_anggaran,
                'coa'           => $all_coa
            ]
        ])->result_array();
        $dt_coa    = get_data('tbl_m_coa a',[
            'select' => "a.glwnco as coa,a.glwdes",
            'where'  => [
                'a.is_active'   => 1,
                'a.glwnco'      => $all_coa,
                'a.kode_anggaran' => $anggaran->kode_anggaran
            ],
            'order_by' => 'a.urutan',
        ])->result();
        $data_core = get_data_core($all_coa,$arr_tahun,'TOT_'.$kode_cabang);
        
        $data['data_core_sum']  = $data['data_core'];
        $data['data_core']      = $data_core;
        $data['dt_coa']         = $dt_coa;
        $data['arr_not_real']   = $arr_not_real;
        $data['dt_index_besaran'] = $dt_index_besaran;
        $data['all_coa']          = $all_coa;
        $save   = $this->load->view('transaction/budget_planner/kredit/saved',[],true);
        $view   = $this->load->view('transaction/budget_planner/kredit/data',$data,true);
        $chart  = $this->chart($data);

        $res = [
            'status'=> true,
            'data'  => $history['view'],
            'data2' => $view,
            'chart' => $chart
        ];

        render($res,'json');
    }

    private function history($data){
        foreach($data as $k => $v){
            ${$k} = $v;
        }
        $item = '';
        $no = 0;
        foreach($arr_tahun_first as $tahun){
            $no++;
            $item .= '<tr>';
            $item .= '<td>'.$no.'</td>';
            $item .= '<td>TOTAL KREDIT '.$tahun.' ('.arrSumberData()['real'].')</td>';
            $temp_tahun = '';
            for ($bln=1; $bln <=12 ; $bln++) { 
                $field  = 'B_' . sprintf("%02d", $bln);
                $val = 0;
                if(isset($data_core[$tahun])):
                    $val = $data_core[$tahun][$field];
                endif;
                $item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
            }
            $item .= '</tr>';
        }

        return [
            'view' => $item,
        ];
    }

    private function chart($data){
        foreach($data as $k => $v){
            ${$k} = $v;
        }
        $colors = ['#0288d1','#ef6c00','#00897b','#eeff41'];
        $dt = get_data('tbl_budget_plan_kredit',[
            'select' => "
                sum(ifnull(P_01,0)) as P_01,
                sum(ifnull(P_02,0)) as P_02,
                sum(ifnull(P_03,0)) as P_03,
                sum(ifnull(P_04,0)) as P_04,
                sum(ifnull(P_05,0)) as P_05,
                sum(ifnull(P_06,0)) as P_06,
                sum(ifnull(P_07,0)) as P_07,
                sum(ifnull(P_08,0)) as P_08,
                sum(ifnull(P_09,0)) as P_09,
                sum(ifnull(P_10,0)) as P_10,
                sum(ifnull(P_11,0)) as P_11,
                sum(ifnull(P_12,0)) as P_12,
                tahun_core,
            ",
            'where' => [
                'kode_cabang'   => $cabang->kode_cabang,
                'kode_anggaran' => $anggaran->kode_anggaran,
                'coa'           => $all_coa
            ],
            'group_by' => 'tahun_core'
        ])->result_array();

        $temp_tahun = '';
        $datasets = [];
        foreach($arr_tahun as $k => $tahun){
            $title = 'TOTAL KREDIT '.$tahun;
            if($tahun == $anggaran->tahun_anggaran):
                $title .= ' ('.arrSumberData()['renc'].')';
            else:
                $title .= ' ('.arrSumberData()['real'].')';
            endif;

            $values = [];
            for ($bln=1; $bln <= 12 ; $bln++) {
                $field  = 'B_' . sprintf("%02d", $bln);
                $field2 = 'P_' . sprintf("%02d", $bln);
                $val = 0;
                if($temp_tahun != $tahun):
                    $temp_tahun = $tahun;
                    $key = multidimensional_search($dt, array(
                        'tahun_core'    => $tahun,
                    ));
                endif;
                if(strlen($key)>0):
                    $val = $dt[$key][$field2];
                elseif(isset($data_core_sum[$tahun])):
                    $val = $data_core_sum[$tahun][$field];
                endif;
                $val = round(($val / 1000000000),0);
                array_push($values,$val);
            }
            
            $h = [
                'label' => $title,
                'type'  => 'bar',
                'backgroundColor' => $colors[$k],
                'data'  => $values
            ];
            array_push($datasets,$h);
        }

        $labels = [];
        for ($i=1; $i <= 12 ; $i++) { 
            array_push($labels,month_lang($i));
        }

        return [
            'datasets'  => $datasets,
            'labels'    => $labels,
        ];
    }

    function data3($kode_anggaran, $kode_cabang){
        $data_finish['kode_anggaran']   = $kode_anggaran;
        $data_finish['kode_cabang']     = $kode_cabang;
        $access = get_access('kredit',$data_finish);
        $access_edit = false;
        if($access['access_edit'] && $kode_cabang == user('kode_cabang')):
            $access_edit = true;
        elseif($access['access_edit'] && $access['access_additional']):
            $access_edit = true;
        endif;

        $anggaran   = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
        $cabang     = get_data('tbl_m_cabang',[
            'where' => [
                'kode_cabang' => $kode_cabang,
                'kode_anggaran' => $kode_anggaran
            ]
        ])->row();

        // pengecekan akses cabang
        $a = get_access($this->controller);
        if(!$anggaran):
            render(['status' => false, 'message' => 'anggaran not found'],'json');exit();
        elseif(!$cabang):
            render(['status' => false, 'message' => 'cabang not found'],'json');exit();
        endif;
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$access);

        $dataDefault['access_edit']     = $access_edit;
        $dataDefault['anggaran']        = $anggaran;
        $dataDefault['cabang']          = $cabang;
        $dataDefault['detail_tahun']    = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'distinct a.kode_anggaran, a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => [
                'a.kode_anggaran' => $anggaran->kode_anggaran,
            ],
            'order_by' => 'tahun,bulan'
        ])->result();

        $groups = get_data('tbl_produk_kredit a',[
            'select'    => "b.glwnco as coa,b.glwdes",
            'join'      => [
                "tbl_m_coa b on b.glwnco = a.grup and b.kode_anggaran = '".$kode_anggaran."'"
            ],
            'where'     => "a.is_active = 1 and a.kode_anggaran = '".$kode_anggaran."'",
            'group_by'  => 'grup'
        ])->result();

        $real_status    = false;
        $arr_not_real   = [];
        $arr_detail_tahun= [];
        $arr_tahun      = [];
        foreach($dataDefault['detail_tahun'] as $v){
            if($v->singkatan == arrSumberData()['real']):
                $real_status = true;
            else:
                $arr_not_real[$v->tahun][$v->bulan] = true;
            endif;
            if(!in_array($v->tahun,$arr_detail_tahun)) array_push($arr_detail_tahun,$v->tahun);
            if(!in_array($v->tahun,$arr_tahun)) array_push($arr_tahun,$v->tahun);
        }
        if(!in_array($anggaran->tahun_terakhir_realisasi,$arr_detail_tahun)) array_push($arr_detail_tahun,$anggaran->tahun_terakhir_realisasi);

        $TOT_cab = 'TOT_' . $kode_cabang ;   
        $field_tabel    = get_field('tbl_rate','name');
        $select_field   = '0 as rate';
        $join_field     = '';
        if (in_array($TOT_cab, $field_tabel)):
            $select_field = 'ifnull(c.'.$TOT_cab.',0) as rate';
            $join_field   = "tbl_rate c on c.no_coa = a.coa and c.kode_anggaran = '$anggaran->kode_anggaran' type left";
        endif;

        $dt_coa  = [];
        $all_coa = [];
        foreach ($groups as $group) {
            $dt = get_data('tbl_produk_kredit a',[
                'select'    => "a.coa,b.glwdes,".$select_field,
                'join'      => [
                    "tbl_m_coa b on b.glwnco = a.coa and b.kode_anggaran = '".$anggaran->kode_anggaran."'",
                    $join_field
                ],
                'where'     => [
                    'a.is_active'   => 1,
                    'a.grup'        => $group->coa,
                    'a.kode_anggaran' => $anggaran->kode_anggaran
                ],
                'order_by' => 'b.urutan'
            ])->result();
            $dt_coa[$group->coa] = $dt;
            foreach($dt as $v){
                if(!in_array($v->coa,$all_coa)) array_push($all_coa,$v->coa);
            }
        }

        if($anggaran->tahun_terakhir_realisasi == $anggaran->tahun_anggaran):
            $data_core = get_data_core($all_coa,[$anggaran->tahun_anggaran],'TOT_'.$kode_cabang);
        else:
            $data_core = get_data_core($all_coa,$arr_detail_tahun,'TOT_'.$kode_cabang);
        endif;

        $dt_kredit = get_data('tbl_budget_plan_kredit',[
            'where' => [
                'kode_cabang'   => $cabang->kode_cabang,
                'kode_anggaran' => $anggaran->kode_anggaran,
                'tahun_core'    => $arr_tahun,
            ]
        ])->result_array();
        
        $data = $dataDefault;
        $data['groups']         = $groups;
        $data['dt_coa']         = $dt_coa;
        $data['all_coa']        = $all_coa;
        $data['data_core']      = $data_core;
        $data['arr_not_real']   = $arr_not_real;
        $data['dt_kredit']      = $dt_kredit;
        $data['buffer']         = ['1454321','1454327'];
        $save   = $this->load->view('transaction/budget_planner/kredit/saved',[],true);
        $view   = $this->load->view('transaction/budget_planner/kredit/data3',$data,true);
        $res = [
            'status'            => true,
            'data'              => $view,
            'access_edit'       => $access_edit
        ];
        render($res,'json');
    }

    function data4($kode_anggaran="", $kode_cabang="") {
        $data_finish['kode_anggaran']   = $kode_anggaran;
        $data_finish['kode_cabang']     = $kode_cabang;
        $access = get_access('kredit',$data_finish);
        $access_edit = false;
        if($access['access_edit'] && $kode_cabang == user('kode_cabang')):
            $access_edit = true;
        elseif($access['access_edit'] && $access['access_additional']):
            $access_edit = true;
        endif;

        $anggaran   = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
        $cabang     = get_data('tbl_m_cabang',[
            'where' => [
                'kode_cabang' => $kode_cabang,
                'kode_anggaran' => $kode_anggaran
            ]
        ])->row();

        // pengecekan akses cabang
        $a = get_access($this->controller);
        if(!$anggaran):
            render(['status' => false, 'message' => 'anggaran not found'],'json');exit();
        elseif(!$cabang):
            render(['status' => false, 'message' => 'cabang not found'],'json');exit();
        endif;
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$access);

        $all_coa    = ['122502','122506'];

        $dataDefault['access_edit']     = $access_edit;
        $dataDefault['anggaran']        = $anggaran;
        $dataDefault['cabang']          = $cabang;
        $dataDefault['detail_tahun']    = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'distinct a.kode_anggaran, a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => [
                'a.kode_anggaran' => $anggaran->kode_anggaran,
            ],
            'order_by' => 'tahun,bulan'
        ])->result();

        $arr_tahun      = [];
        foreach($dataDefault['detail_tahun'] as $v){
            if(!in_array($v->tahun,$arr_tahun)) array_push($arr_tahun,$v->tahun);
        }

        $TOT_cab = 'TOT_' . $kode_cabang ;   
        $field_tabel    = get_field('tbl_import_jumlah_rekening','name');
        $select_field   = '0 as total';
        $join_field     = '';
        if (in_array($TOT_cab, $field_tabel)):
            $select_field = 'ifnull(c.'.$TOT_cab.',0) as total';
            $join_field   = "tbl_import_jumlah_rekening c on c.no_coa = a.glwnco and c.kode_anggaran = '$anggaran->kode_anggaran' type left";
        endif;

        $dt_coa = get_data('tbl_m_coa a',[
            'select' => 'a.glwnco as coa, a.glwdes,'.$select_field,
            'join'   => [
                $join_field,
            ],
            'where' => [
                'a.is_active'   => 1,
                'a.glwnco'      => $all_coa,
                'a.kode_anggaran' => $anggaran->kode_anggaran
            ],
            'order_by' => 'a.urutan'
        ])->result();

        $kredit = get_data('tbl_jumlah_rekening',[
            'where' => [
                'kode_anggaran' => $anggaran->kode_anggaran,
                'kode_cabang'   => $kode_cabang,
                'tahun_core'    => $arr_tahun,
                'coa'           => $all_coa,
            ]
        ])->result_array();

        $data = $dataDefault;
        $data['dt_coa'] = $dt_coa;
        $data['kredit'] = $kredit;
        $view   = $this->load->view('transaction/budget_planner/kredit/data4',$data,true);
     
        $res = [
            'status'            => true,
            'data'              => $view,
            'autorun'           => call_autorun($kode_anggaran,$kode_cabang,'kredit'),
        ];


        render($res,'json');
    }

    function save_perubahan(){
        $kode_anggaran  = post('kode_anggaran');
        $kode_cabang    = post('kode_cabang');
        $anggaran       = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
        
        if(!$anggaran):
            render(['status' => 'failed', 'message' => 'anggaran not found'],'json');exit();
        endif;
        // pengecekan save untuk cabang
        check_save_cabang($this->controller,$kode_anggaran,$kode_cabang,'access_edit');

        if(post('json')):
            $data   = json_decode(post('json'),true);
            foreach($data as $k => $record){
                $x      = explode('-', $k);
                $page   = $x[0];
                $coa    = $x[1];
                if($x[0] == 'rate'):
                    $table = 'tbl_rate';
                    foreach($record as $name => $v){
                        $record[$name] = filter_money($v);

                        if (!$this->db->field_exists($name, 'tbl_rate')):
                            $this->load->dbforge();
                            $fields = array(
                                    $name => array(
                                        'type' => 'FLOAT',
                                    ),
                            );
                            $this->dbforge->add_column('tbl_rate',$fields);
                        endif;
                    }
                    $ck = get_data($table,[
                        'select' => 'id',
                        'where'  => [
                            'kode_anggaran' => $anggaran->kode_anggaran,
                            'no_coa'        => $coa,
                        ]
                    ])->row();
                    if($ck):
                        $record['id'] = $ck->id;
                    else:
                        $record['id'] = '';
                        $record['id_anggaran']      = $anggaran->id;
                        $record['kode_anggaran']    = $anggaran->kode_anggaran;
                        $record['keterangan_anggaran'] = $anggaran->keterangan;
                        $record['jenis_rate'] = 'KREDIT';
                        $record['no_coa']     = $coa;
                    endif;
                    save_data($table,$record,[],true);
                elseif($page == 'kredit'):
                    $tahun = $x[2];
                    $table = 'tbl_budget_plan_kredit';
                    $ck = get_data($table,[
                        'select' => 'id,is_edit',
                        'where'  => [
                            'kode_anggaran' => $anggaran->kode_anggaran,
                            'kode_cabang'   => $kode_cabang,
                            'coa'           => $coa,
                            'tahun_core'    => $tahun,
                        ]
                    ])->row();
                    $is_edit = [];
                    if($ck && $ck->is_edit):
                        $is_edit = json_decode($ck->is_edit,true);
                    endif;
                    foreach($record as $name => $v){
                        $val = filter_money($v);
                        $val = insert_view_report($val);
                        $record[$name] = $val;
                        $is_edit[$name]= $record[$name];
                    }

                    $record['is_edit'] = json_encode($is_edit);
                    if($ck):
                        $record['id'] = $ck->id;
                    else:
                        $record['id'] = '';
                        $record['kode_anggaran']        = $anggaran->kode_anggaran;
                        $record['tahun_anggaran']       = $anggaran->tahun_anggaran;
                        $record['keterangan_anggaran']  = $anggaran->keterangan;
                        $record['tahun_core']           = $tahun;
                        $record['coa']                  = $coa;
                        $record['kode_cabang']          = $kode_cabang;
                        if($anggaran->tahun_anggaran == $tahun):
                            $record['parent_id'] = '0';
                        else:
                            $record['parent_id'] = $kode_cabang;
                        endif;
                    endif;
                    $res = save_data($table,$record,[],true);
                elseif($page == 'netto'):
                    $tahun = $anggaran->tahun_anggaran;
                    $table = 'tbl_budget_plan_kredit';
                    $ck = get_data($table,[
                        'select' => 'id,is_edit',
                        'where'  => [
                            'kode_anggaran' => $anggaran->kode_anggaran,
                            'kode_cabang'   => $kode_cabang,
                            'coa'           => $coa,
                            'tahun_core'    => $tahun,
                        ]
                    ])->row();
                    foreach($record as $name => $v){
                        $val = filter_money($v);
                        $val = insert_view_report($val);
                        $record[$name] = $val;
                    }

                    if($ck):
                        $record['id'] = $ck->id;
                    else:
                        $record['id'] = '';
                        $record['kode_anggaran']        = $anggaran->kode_anggaran;
                        $record['tahun_anggaran']       = $anggaran->id;
                        $record['keterangan_anggaran']  = $anggaran->keterangan;
                        $record['tahun_core']           = $tahun;
                        $record['coa']                  = $coa;
                        $record['kode_cabang']          = $kode_cabang;
                        if($anggaran->tahun_anggaran == $tahun):
                            $record['parent_id'] = '0';
                        else:
                            $record['parent_id'] = $kode_cabang;
                        endif;
                    endif;
                    $res = save_data($table,$record,[],true);
                elseif($page == 'index_kali'):
                    $tahun = $anggaran->tahun_anggaran;
                    $table = 'tbl_jumlah_rekening';
                    $where = [
                        'kode_anggaran' => $anggaran->kode_anggaran,
                        'kode_cabang'   => $kode_cabang,
                        'coa'           => $coa,
                    ];
                    $ck = get_data($table,[
                        'select' => 'id,is_edit',
                        'where'  => $where
                    ])->row();
                    foreach($record as $name => $v){
                        $val = filter_money($v);
                        $record[$name] = $val;
                    }

                    if($ck):
                        update_data($table,$record,$where);
                    else:
                        $record['id'] = '';
                        $record['kode_anggaran']        = $anggaran->kode_anggaran;
                        $record['tahun_anggaran']       = $anggaran->id;
                        $record['keterangan_anggaran']  = $anggaran->keterangan;
                        $record['tahun_core']           = $tahun;
                        $record['coa']                  = $coa;
                        $record['kode_cabang']          = $kode_cabang;
                        $res = save_data($table,$record,[],true);
                    endif;
                elseif($page == 'rekening'):
                    $tahun = $x[2];
                    $table = 'tbl_jumlah_rekening';
                    $ck = get_data($table,[
                        'select' => 'id,is_edit',
                        'where'  => [
                            'kode_anggaran' => $anggaran->kode_anggaran,
                            'kode_cabang'   => $kode_cabang,
                            'coa'           => $coa,
                            'tahun_core'    => $tahun,
                        ]
                    ])->row();
                    $is_edit = [];
                    if($ck && $ck->is_edit):
                        $is_edit = json_decode($ck->is_edit,true);
                    endif;
                    foreach($record as $name => $v){
                        $val = filter_money($v);
                        $record[$name] = $val;
                        $is_edit[$name]= $record[$name];
                    }

                    $record['is_edit'] = json_encode($is_edit);
                    if($ck):
                        $record['id'] = $ck->id;
                    else:
                        $record['id'] = '';
                        $record['kode_anggaran']        = $anggaran->kode_anggaran;
                        $record['tahun_anggaran']       = $anggaran->tahun_anggaran;
                        $record['keterangan_anggaran']  = $anggaran->keterangan;
                        $record['tahun_core']           = $tahun;
                        $record['coa']                  = $coa;
                        $record['kode_cabang']          = $kode_cabang;
                    endif;
                    $res = save_data($table,$record,[],true);
                endif;
            }
        endif;

        create_autorun($kode_anggaran,$kode_cabang,'kredit');

        render([
            'status' => true,
            'message' => lang('data_berhasil_diperbaharui')
        ],'json');
    }

    function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_anggaran      = post('kode_anggaran');

        $kode_cabang        = post('kode_cabang');
        $kode_cabang_txt    = post('kode_cabang_txt');

        // pengecekan akses cabang
        $access = get_access($this->controller);
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$access);

        $dt = json_decode(post('data'),true);

        $header = $dt['#result1']['header'][0];
        $count  = count($header);
        if(isset($header[($count-3)])):
            unset($header[($count-3)]);
        endif;
        if(isset($header[($count-2)])):
            unset($header[($count-2)]);
        endif;
        if(isset($header[($count-1)])):
            unset($header[($count-1)]);
        endif;

        $data = [];
        foreach(['#result1','#result2'] as $name){
            if(isset($dt[$name])):
                $count2 = 0;
                foreach($dt[$name]['data'] as $k => $v){
                    $count2 = count($v);
                    $detail = [
                        $v[0],
                        $v[1],
                    ];
                    for ($i=2; $i < count($v) ; $i++) { 
                        $detail[] = filter_money($v[$i]);
                    }
                    $data[] = $detail;
                }
                $detail = [];
                for ($i=0; $i < $count2 ; $i++) { 
                    $detail[] = '';
                }
                $data[] = $detail;
            endif;
        }

        $config[] = [
            'title' => 'Kredit'.' ('.get_view_report().')',
            'header' => $header,
            'data'  => $data,
        ];

        $data = [];
        $header = $dt['#result3']['header'][0];
        $count = count($header);
        foreach(['#result3'] as $name){
            if(isset($dt[$name])):
                $count2 = 0;
                foreach($dt[$name]['data'] as $k => $v){
                    $count2 = count($v);
                    if($count2<5):
                        $detail = [];
                        foreach ($v as $k2 => $v2) {
                            $detail[] = $v2;
                        }
                        for ($i=$count2; $i < $count ; $i++) { 
                            $detail[] = '';
                        }
                        $data[] = $detail;
                    else:
                        $detail = [
                            $v[0],
                            $v[1],
                        ];
                        for ($i=2; $i < (count($v)-5) ; $i++) { 
                            $detail[] = filter_money($v[$i]);
                        }
                        $detail[] = '';
                        $detail[] = filter_money($v[$count2-4]);
                        $detail[] = '';
                        $detail[] = filter_money($v[$count2-2]);
                        $detail[] = filter_money($v[$count2-1]);
                        $data[] = $detail;
                    endif;
                }
            endif;
        }
        $config[] = [
            'title' => 'Kredit per Produk',
            'header' => $header,
            'data'  => $data,
        ];

        $title = lang('jumlah_rekening');
        $data = [];
        $header = $dt['#result4']['header'][0];
        foreach(['#result4'] as $name){
            if(isset($dt[$name])):
                $count2 = 0;
                foreach($dt[$name]['data'] as $k => $v){
                    $count2 = count($v);
                    $detail = [
                        $v[0],
                        $v[1],
                    ];
                    for ($i=2; $i < (count($v)-3) ; $i++) { 
                        $detail[] = filter_money($v[$i]);
                    }
                    $detail[] = '';
                    $detail[] = filter_money($v[$count2-2]);
                    $detail[] = filter_money($v[$count2-1]);
                    $data[] = $detail;
                }
            endif;
        }
        $config[] = [
            'title' => $title,
            'header' => $header,
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'kredit_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }
}