<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Giro extends BE_Controller {
    var $path = 'transaction/budget_planner/';
    var $controller = 'giro';
    function __construct() {
        parent::__construct();
    }
    
    function index($p1="") { 
        $access         = get_access($this->controller);
        $data = data_cabang('giro');
        $data['path'] = $this->path;

        $data['detail_tahun']   = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'distinct a.kode_anggaran, a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => [
                'a.kode_anggaran' => user('kode_anggaran'),
            ],
            'order_by' => 'tahun,bulan'
        ])->result_array();
        $data['access_additional']  = $access['access_additional'];
        render($data,'view:'.$this->path.'giro/index');
    }

    function data($kode_anggaran="", $kode_cabang="") {

        $data_finish['kode_anggaran']   = $kode_anggaran;
        $data_finish['kode_cabang']     = $kode_cabang;
        $access = get_access('giro',$data_finish);
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
        if(!$anggaran):
            render(['status' => false,'message' => 'anggaran not found'],'json');exit();
        elseif(!$cabang):
            render(['status' => false,'message' => 'cabang not found'],'json');exit();
        endif;
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$access);

        $arr_coa    = ['2100000','2101011','2101012'];

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

        $arr_tahun = [];
        for ($i=($anggaran->tahun_anggaran - 3); $i <= $anggaran->tahun_anggaran  ; $i++) { 
            array_push($arr_tahun,$i);
        }
        $data_core = get_data_core($arr_coa,$arr_tahun,'TOT_'.$kode_cabang);

        $dt_index_besaran = get_data('tbl_indek_besaran',[
            'where' => [
                'kode_cabang'   => $cabang->kode_cabang,
                'kode_anggaran' => $anggaran->kode_anggaran,
                'coa'           => $arr_coa
            ]
        ])->result_array();

        $data = $dataDefault;
        $data['arr_tahun'] = $arr_tahun;
        $data['data_core'] = $data_core;
        $view_data   = $this->view_data($data);

        $select_rate = '0 as rate';
        $join_rate   = ["tbl_m_coa b on b.glwnco = a.coa and b.kode_anggaran = '$anggaran->kode_anggaran'"];
        if ($this->db->field_exists('TOT_'.$kode_cabang, 'tbl_rate')):
            $select_rate = "ifnull(e.TOT_".$kode_cabang.",0) as rate";
            $join_rate[] = "tbl_rate e on e.no_coa = a.coa and e.kode_anggaran = '$anggaran->kode_anggaran' type left";
        endif;

        $dt_coa    = get_data('tbl_produk_coa a',[
            'select' => "a.coa,b.glwdes,".$select_rate,
            'where'  => "a.is_active = 1 and a.grup = 'giro'",
            'join'   => $join_rate,
            'order_by' => 'a.id',
            'sort'  => 'desc'
        ])->result();

        $arr_coa = array_merge($arr_coa,['211','212']);
        $dt_giro = get_data('tbl_budget_plan_giro',[
            'where' => [
                'kode_cabang'   => $cabang->kode_cabang,
                'kode_anggaran' => $anggaran->kode_anggaran,
                'coa'           => $arr_coa
            ]
        ])->result_array();

        // prosentase
        $arrPrsnDpk = [];
        if($this->db->field_exists('TOT_'.$kode_cabang, 'tbl_prsn_dpk')):
            $arrPrsnDpk = get_data('tbl_prsn_dpk',[
                'select' => "no_coa as coa,ifnull(TOT_".$kode_cabang.",0) as prsn",
                'where'  => [
                    'kode_anggaran' => $anggaran->kode_anggaran,
                    'no_coa' => ['2101012','212']
                ]
            ])->result_array();
        endif;

        $data['arrData']            = $view_data['arrData'];
        $data['dt_coa']             = $dt_coa;
        $data['arr_not_real']       = $arr_not_real;
        $data['arr_detail_tahun']   = $arr_detail_tahun;
        $data['dt_index_besaran']   = $dt_index_besaran;
        $data['dt_giro']            = $dt_giro;
        $data['arrPrsnDpk']         = $arrPrsnDpk;
        $view  = $this->load->view('transaction/budget_planner/giro/data',$data,true);
        $chart = $this->chart($data);
     
        $data = [
            'status'            => true,
            'data'              => $view_data['view'],
            'data2'             => $view,
            'check_first_data'  => 1,
            'chart'             => $chart,
            'access_edit'       => $access_edit
        ];

        render($data,'json');
    }

    function data3($kode_anggaran,$kode_cabang){
        $anggaran   = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
        $cabang     = get_data('tbl_m_cabang','kode_cabang',$kode_cabang)->row();
        
        $data_finish['kode_anggaran']   = $kode_anggaran;
        $data_finish['kode_cabang']     = $kode_cabang;
        $access = get_access('giro',$data_finish);
        $access_edit = false;
        if($access['access_edit'] && $kode_cabang == user('kode_cabang')):
            $access_edit = true;
        elseif($access['access_edit'] && $access['access_additional']):
            $access_edit = true;
        endif;

        // pengecekan akses cabang
        if(!$anggaran):
            render(['status' => false,'message' => 'anggaran not found'],'json');exit();
        elseif(!$cabang):
            render(['status' => false,'message' => 'cabang not found'],'json');exit();
        endif;
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$access);

        $detail_tahun   = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => [
                'a.kode_anggaran' => $anggaran->kode_anggaran,
            ],
            'order_by' => 'tahun,bulan'
        ])->result();
        $arr_tahun_core = [];
        foreach ($detail_tahun as $k => $v) {
            if(!in_array($v->tahun, $arr_tahun_core)) array_push($arr_tahun_core, $v->tahun);
        }

        $arr_coa = ['2101012','211','212'];
        $giro = get_data('tbl_jumlah_rekening',[
            'where' => [
                'kode_anggaran' => $anggaran->kode_anggaran,
                'kode_cabang'   => $cabang->kode_cabang,
                'coa'           => $arr_coa,
                'tahun_core'    => $arr_tahun_core,
            ]
        ])->result_array();

        $status_column = false;
        $column = 'TOT_'.$cabang->kode_cabang;
        if ($this->db->field_exists($column, 'tbl_import_jumlah_rekening')):
            $status_column = true;
        endif;
        $import_jum_rek = [];
        if($status_column):
            $import_jum_rek = get_data('tbl_import_jumlah_rekening',[
                'select' => 'no_coa as coa,'.$column.' as total',
                'where'  => [
                    'kode_anggaran' => $anggaran->kode_anggaran,
                    'no_coa'        => $arr_coa,
                ]
            ])->result_array();
        endif;

        $data['no']             = (1);
        $data['detail_tahun']   = $detail_tahun;
        $data['access_edit']    = $access_edit;
        $data['cabang']         = $cabang;
        $data['anggaran']       = $anggaran;
        $data['giro']           = $giro;
        $data['import_jum_rek'] = $import_jum_rek;
        $data['access_additional'] = $access['access_additional'];
        $view   = $this->load->view('transaction/budget_planner/giro/jum_segment',$data,true);
        $data = [
            'status'            => true,
            'data'              => $view,
            'autorun'           => call_autorun($kode_anggaran,$kode_cabang,'dpk'),
        ];

        render($data,'json');
    }

    function save_perubahan($kode_anggaran,$kode_cabang){ 
        // pengecekan save untuk cabang
        check_save_cabang($this->controller,$kode_anggaran,$kode_cabang,'access_edit');
        $res = array();
        if(post('json')):
            $data   = json_decode(post('json'),true);
            foreach($data as $id => $record) {
                $arr_id = explode('-', $id);
                if(count($arr_id)>1):
                    foreach ($record as $k => $v) {
                        $value = str_replace('.', '', $v);
                        $value = str_replace(',', '.', $value);
                        $record[$k] = $value;
                    }
                    $kode_anggaran = post('kode_anggaran');
                    $coa = $arr_id[0];
                    $tb  = $arr_id[1];
                    if($tb == 'rate'):
                        foreach ($record as $column => $v2) {
                            if (!$this->db->field_exists($column, 'tbl_rate')):
                                $this->load->dbforge();
                                $fields = array(
                                        $column => array(
                                            'type' => 'FLOAT',
                                        ),
                                );
                                $this->dbforge->add_column('tbl_rate',$fields);
                            endif;
                        }
                        $ck = get_data('tbl_rate',[
                            'select' => 'id',
                            'where'  => [
                                'kode_anggaran' => $kode_anggaran,
                                'no_coa'        => $coa,
                            ],
                        ])->row();
                        if($ck):
                            update_data('tbl_rate',$record,'id',$ck->id);
                        else:
                            insert_data('tbl_rate',$record);
                        endif;
                    endif;
                else:
                    $_v               = [];
                    foreach ($record as $k => $v) {
                        $arrkeys = explode('-', $k);
                        $nama    = $arrkeys[0];
                        $table   = $arrkeys[1];
                        $tahun   = substr($arrkeys[2],0,4);
                        $bulan   = substr($arrkeys[2],4);
                        $coa     = $arrkeys[3];
                        $id_tahun_anggaran   = $arrkeys[4];
                        $kode_cabang     = $arrkeys[5];

                        $value   = $v;
                        $value = str_replace('.', '', $v);
                        $value = str_replace(',', '.', $value);
                        $_v[$arrkeys[2]]  = $value;
                        array_push($res, array(
                            'nama' => $nama,
                            'table' => $table,
                            'tahun' => $tahun,
                            'bulan' => $bulan,
                            'coa'   => $coa,
                            'id_tahun_anggaran' => $id_tahun_anggaran,
                            'kode_cabang'   => $kode_cabang,
                            'value' => $value,
                            'is_edit'   => $_v,
                        ));
                    }
                endif;
            }
            foreach ($res as $r => $r1) {


                $field = 'P_' . sprintf("%02d", $r1['bulan']);
                $anggaran = get_data('tbl_tahun_anggaran','id',$r1['id_tahun_anggaran'])->row();

                $x = 'IP_' . sprintf("%02d", $r1['bulan']);


                $data2 = array(
                    $field => insert_view_report($r1['value']),
                );


                switch ($r1['table']) {
                  case 'table3':

                        $old_data = get_data('tbl_jumlah_rekening',[
                            'select' => 'is_edit',
                            'where'  => [
                                'kode_anggaran' => $anggaran->kode_anggaran,
                                'kode_cabang'       => $r1['kode_cabang'],
                                'coa'               => $r1['coa'],
                                'tahun_core'        => $r1['tahun'],
                            ]
                        ])->row();

                        $is_edit0 = [];
                        if(isset($old_data->is_edit)) {
                            $is_edit0 = json_decode($old_data->is_edit,true) ;
                        } 
                        foreach ($r1['is_edit'] as $k => $v) {
                            $is_edit0[$k] = $v;
                        }

                       // debug($is_edit0);die;
                        $data2_ = array(
                            $field => $r1['value'],
                            'is_edit' => json_encode($is_edit0),
                        );


                        update_data('tbl_jumlah_rekening',$data2_,['kode_anggaran'=>$anggaran->kode_anggaran,'kode_cabang'=>$r1['kode_cabang'],'coa'=>$r1['coa'],'tahun_core'=>$r1['tahun']]);
                        break;
                  case 'table2':

                        $girok = get_data('tbl_budget_plan_giro',[
                            'select' => $field,
                            'where'  => [
                                'kode_anggaran' => $anggaran->kode_anggaran,
                                'kode_cabang' => $r1['kode_cabang'],
                                'coa'   => '2100000',
                                'parent_id' => 0,
                                'tahun_core' => $r1['tahun'],                     
                            ]
                        ])->row(); 

                        
                        
                        $gironk = get_data('tbl_budget_plan_giro',[
                            'select' => $field,
                            'where'  => [
                                'kode_anggaran' => $anggaran->kode_anggaran,
                                'kode_cabang' => $r1['kode_cabang'],
                                'coa'   => '2101011',
                                'parent_id !=' => 0,
                                'tahun_core' => $r1['tahun'],                     
                            ]
                        ])->row(); 


                        $xn0 = 0 ;
                        if(isset($gironk->$field))  $xn0 = $gironk->$field ;
                        

                        $x0 = 0 ;
                        if(isset($girok->$field))  $x0 = $girok->$field ;
                

                      
                        $data3 = array(
                            $field => $x0 - insert_view_report($r1['value']),
                        );

                        $data4 = array(
                            $field => $xn0 - insert_view_report($r1['value']),
                        );



                        update_data('tbl_budget_plan_giro',$data2,['kode_anggaran'=>$anggaran->kode_anggaran,'kode_cabang'=>$r1['kode_cabang'],'coa'=>$r1['coa'],'tahun_core'=>$r1['tahun']]);
     

                        if($r1['coa'] == '2101012'){
                            update_data('tbl_budget_plan_giro',$data3,['kode_anggaran'=>$anggaran->kode_anggaran,'kode_cabang'=>$r1['kode_cabang'],'coa'=>'2101011','tahun_core'=>$r1['tahun']]);
                        }

                        
                        if($r1['coa'] == '2101011|B'){
                            update_data('tbl_budget_plan_giro',$data4,['kode_anggaran'=>$anggaran->kode_anggaran,'kode_cabang'=>$r1['kode_cabang'],'coa'=>'2101011|A','tahun_core'=>$r1['tahun']]);
                        }
                        
                        break;

                  case 'table5':
                        $data3 = array(
                            'P_akhir' => $r1['value'],
                        );

                        update_data('tbl_jumlah_rekening',$data3,['kode_anggaran'=>$anggaran->kode_anggaran,'kode_cabang'=>$r1['kode_cabang'],'coa'=>$r1['coa']]);
                        break;            
                  
                  case 'table6':
                        $data4 = array(
                            'index_kali' => $r1['value'],
                        );
     
                        update_data('tbl_jumlah_rekening',$data4,['kode_anggaran'=>$anggaran->kode_anggaran,'kode_cabang'=>$r1['kode_cabang'],'coa'=>$r1['coa']]);

                        break;
                    case 'tbl_segment':
                        $data4 = array(
                            $field => insert_view_report($r1['value']),
                        );
                        $where = ['kode_anggaran'=>$anggaran->kode_anggaran,'kode_cabang'=>$r1['kode_cabang'],'coa'=>$r1['coa'],'tahun_core'=>$r1['tahun']];

                        $ck = get_data('tbl_budget_plan_giro',[
                            'select' => 'id,changed',
                            'where'  => $where,
                        ])->row();
                        $changed = [];
                        if($ck):
                            $changed = json_decode($ck->changed);
                            if(!is_array($changed)) $changed = [];
                            $data4['id'] = $ck->id;
                        else:
                            $data4 = $where;
                            $data4['tahun_anggaran'] = $anggaran->id;
                            $data4[$field] = insert_view_report($r1['value']);
                        endif;
                        if(!in_array($field,$changed)):
                            array_push($changed,$field);
                        endif;
                        $data4['changed'] = json_encode($changed);
                        save_data('tbl_budget_plan_giro',$data4,[],true);
                        break;
                    case 'tbl_jum_segment':
                        $where = ['kode_anggaran'=>$anggaran->kode_anggaran,'kode_cabang'=>$r1['kode_cabang'],'coa'=>$r1['coa'],'tahun_core'=>$r1['tahun']];
                        $old_data = get_data('tbl_jumlah_rekening',[
                            'select' => 'is_edit',
                            'where'  => [
                                'kode_anggaran' => $anggaran->kode_anggaran,
                                'kode_cabang'       => $r1['kode_cabang'],
                                'coa'               => $r1['coa'],
                                'tahun_core'        => $r1['tahun'],
                            ]
                        ])->row();

                        $is_edit0 = [];
                        if(isset($old_data->is_edit)) {
                            $is_edit0 = json_decode($old_data->is_edit,true) ;
                        } 
                        foreach ($r1['is_edit'] as $k => $v) {
                            $is_edit0[$k] = $v;
                        }

                        $data2_ = array(
                            $field => $r1['value'],
                            'is_edit' => json_encode($is_edit0),
                        );

                        if($old_data):
                            update_data('tbl_jumlah_rekening',$data2_,$where);
                        else:
                            $data2_ = $where;
                            $data2_['tahun_anggaran'] = $anggaran->id;
                            $data2_[$field]      = $r1['value'];
                            $data2_['is_edit']   = json_encode($is_edit0);
                            insert_data('tbl_jumlah_rekening',$data2_);
                        endif;
                        break;
                    case 'tbl_index_kali':
                        $where = ['kode_anggaran'=>$anggaran->kode_anggaran,'kode_cabang'=>$r1['kode_cabang'],'coa'=>$r1['coa']];
                    
                        $ck = get_data('tbl_jumlah_rekening',[
                            'select' => 'id',
                            'where' => $where,
                        ])->row();
                        if($ck):
                            $data4 = array(
                                'index_kali' => $r1['value'],
                            );
                            update_data('tbl_jumlah_rekening',$data4,$where);
                        else:
                            $data4 = $where;
                            $data4['tahun_anggaran'] = $anggaran->id;
                            $data4['index_kali']     = $r1['value'];
                            $data4['tahun_core']     = $anggaran->tahun_anggaran;
                            insert_data('tbl_jumlah_rekening',$data4);
                        endif;
                        break;   

                }        

            }
        endif;
        
        create_autorun($kode_anggaran,$kode_cabang,'dpk');
        $res['status']  = true;
        $res['message'] = lang('data_berhasil_diperbaharui');
        render($res,'json');
    }

    // MW 20210614
    // View 2 tahun terakhir realisasi
    private function view_data($data){
        foreach($data as $k => $v){
            ${$k} = $v;
        }
        $item   = '';
        $no     = 0;
        $arrData= [];
        $temp_tahun = '';
        for ($tahun=($anggaran->tahun_anggaran - 3); $tahun <= ($anggaran->tahun_anggaran-2)  ; $tahun++) { 
            $no++;

            $item .= '<tr>';
            $item .= '<td>'.$no.'</td>';
            $item .= '<td>GIRO '.$tahun.' ('.arrSumberData()['real'].')</td>';
            $item .= '<td></td>';
            $item2 = '';
            for ($bln=1; $bln <=12 ; $bln++) { 
                $field  = 'B_' . sprintf("%02d", $bln);
                $val = 0;
                if(isset($data_core[$tahun])):
                    if($temp_tahun != $tahun):
                        $temp_tahun = $tahun;
                        $core_key = multidimensional_search($data_core[$tahun], array(
                            'glwnco' => '2100000',
                        ));
                    endif;
                    if(strlen($core_key)>0):
                        $kali_minus = $data_core[$tahun][$core_key]['kali_minus'];
                        $val        = $data_core[$tahun][$core_key][$field];
                        $val        = kali_minus($val,$kali_minus);
                    endif;
                endif;
                $item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
                $arrData[$tahun][$bln] = $val;
                if($no != 1):
                    $pertumbuhan    = 0;
                    $pembagi        = $arrData[($tahun-1)][$bln];
                    if($pembagi):
                        $pertumbuhan = (($arrData[$tahun][$bln]-$pembagi)/$pembagi)*100;
                    endif;
                    $item2 .= '<td class="text-right">'.custom_format($pertumbuhan,false,2).'</td>';
                endif;
            }
            $item .= '</tr>';
            if($no != 1):
                $item .= '<tr>';
                $item .= '<td></td>';
                $item .= '<td>Pert '.$tahun.'</td>';
                $item .= '<td></td>';
                $item .= $item2;
                $item .= '</tr>';
            endif;
        }

        return [
            'view'      => $item,
            'arrData'   => $arrData
        ];
    }

    private function chart($data){
        foreach($data as $k => $v){
            ${$k} = $v;
        }
        $colors = ['#0288d1','#ef6c00','#00897b','#eeff41'];
        $dt = get_data('tbl_budget_plan_giro',[
            'where' => [
                'kode_cabang'   => $cabang->kode_cabang,
                'kode_anggaran' => $anggaran->kode_anggaran,
                'coa'           => ['2100000']
            ]
        ])->result_array();

        $temp_tahun = '';
        $datasets = [];
        foreach($arr_tahun as $k => $tahun){
            $title = 'GIRO '.$tahun;
            if($tahun == $anggaran->tahun_anggaran):
                $title .= ' ('.arrSumberData()['renc'].')';
            else:
                $title .= ' ('.arrSumberData()['real'].')';
            endif;

            $values = [];
            for ($bln=1; $bln <= 12 ; $bln++) {
                $field2 = 'P_' . sprintf("%02d", $bln);
                $val = 0;
                if(isset($arrData[$tahun])):
                    $val = $arrData[$tahun][$bln];
                else:
                    if($temp_tahun != $tahun):
                        $temp_tahun = $tahun;
                        $key = multidimensional_search($dt, array(
                            'coa'           => '2100000',
                            'tahun_core'    => $tahun,
                        ));
                    endif;
                    if(strlen($key)>0):
                        $val = $dt[$key][$field2];
                    endif;
                endif;
                $val = $val / 1000000000;
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

    function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_anggaran      = post('kode_anggaran');

        $kode_cabang        = post('kode_cabang');
        $kode_cabang_txt    = post('kode_cabang_txt');

        // pengecekan akses cabang
        $access = get_access($this->controller);
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$access);

        // result 1
        $header = json_decode(post('header'),true);
        $dt     = json_decode(post('data'),true);

        $data   = [];
        $count  = 0;
        foreach($dt as $k => $v){
            $count = count($v);
            $detail = [
                $v[0],
                $v[1],
                $v[2],
            ];
            for ($i=3; $i < count($v) ; $i++) { 
                $detail[] = filter_money($v[$i]);
            }
            $data[] = $detail;
        }
        if(isset($header[1][($count)])):
            unset($header[1][($count)]);
        endif;
        if(isset($header[1][($count+1)])):
            unset($header[1][($count+1)]);
        endif;

        $config[] = [
            'title' => 'Giro History'.' ('.get_view_report().')',
            'header' => $header[1],
            'data'  => $data,
        ];

        // result 2
        $header = json_decode(post('header2'),true);
        $dt     = json_decode(post('data2'),true);

        $data   = [];
        $count  = 0;
        foreach($dt as $k => $v){
            $count = count($v);
            $detail = [
                $v[0],
                $v[1],
                $v[2],
            ];
            for ($i=3; $i < (count($v)-2) ; $i++) { 
                $detail[] = filter_money($v[$i]);
            }
            $detail[] = '';
            $detail[] = filter_money($v[($count-1)]);
            $data[] = $detail;
        }

        $config[] = [
            'title' => 'Giro'.' ('.get_view_report().')',
            'header' => $header[1],
            'data'  => $data,
        ];

        // result 3
        $header = json_decode(post('header3'),true);
        $dt     = json_decode(post('data3'),true);

        $data   = [];
        $count  = 0;
        foreach($dt as $k => $v){
            $count = count($v);
            $detail = [
                $v[0],
                $v[1],
                $v[2],
            ];
            for ($i=3; $i < (count($v)-3) ; $i++) { 
                $detail[] = filter_money($v[$i]);
            }
            $detail[] = '';
            $detail[] = filter_money($v[($count-2)]);
            $detail[] = filter_money($v[($count-1)]);
            $data[] = $detail;
        }

        $config[] = [
            'title' => lang('jumlah_rekening'),
            'header' => $header[0],
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'giro_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }
}