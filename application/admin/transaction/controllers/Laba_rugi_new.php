<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Laba_rugi_new extends BE_Controller {
    var $path = 'transaction/budget_planner/';
    var $controller = 'laba_rugi_new';
    function __construct() {
        parent::__construct();
        $this->kode_anggaran  = user('kode_anggaran');
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$this->kode_anggaran)->row();
        $this->detail_tahun   = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => "a.kode_anggaran = '".$this->kode_anggaran."' and a.tahun = '".$anggaran->tahun_anggaran."' ",
            //     'a.kode_anggaran' => $this->kode_anggaran,
            //     'a.sumber_data'   => array(2,3)
            // ],
            'order_by' => 'tahun,bulan'
        ])->result_array();
    }

    function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_cabang_txt    = post('kode_cabang_txt');

        $neraca_header = json_decode(post('neraca_header'));
        $neraca = json_decode(post('neraca'));

        $data = [];
        foreach ($neraca as $k => $v) {
            if(count($v)>2):
               if(strlen($v[3])>0 && $v[3] != "Lakukan"):
                    if($k == 0):
                        $data[$k] = $v;
                    else:
                        $detail = [
                            $v[0],
                            $v[1],
                            $v[2],
                            $v[3],
                        ];
                        foreach ($v as $k2 => $v2) {
                            if($k2>3):
                                if(strlen($v2)>0):
                                    $v2 = (float) filter_money($v2);
                                endif;
                                $detail[] = $v2;
                            endif;
                        }
                        $data[$k] = $detail;
                    endif;
                    if(count($v)<count($neraca_header[1])):
                        for($i=count($v);$i<count($neraca_header[1]);$i++){
                            $data[$k][] = "";
                        }
                    endif;
                endif;

                if($v[1] == '59999'):
                    break;
                endif;
            else:
                $data[$k] = [];
                for($i=1;$i<=count($neraca_header[1]);$i++){
                    $data[$k][] = '';
                }
            endif;
        }

        unset($data[0]);
        $config[] = [
            'title' => 'Laba Rugi Nett',
            'header' => $neraca_header[1],
            'data'  => $data,
        ];
        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'Labarugi_Nett_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }
    
    function index($p1="") { 
        $access         = get_access($this->controller);
        $data = data_cabang('laba_rugi_new');
        $data['detail_tahun'] = $this->detail_tahun;
        $data['path'] = $this->path;
        $data['access_additional']  = $access['access_additional'];
        $data['access_edit']        = $access['access_edit'];
        $data['bulan_terakhir'] = month_lang($data['tahun'][0]->bulan_terakhir_realisasi);
        render($data,'view:'.$this->path.'laba_rugi_new/index');
    }

     function data ($anggaran1="", $cabang=""){
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$anggaran1)->row();
        if(!$anggaran):
            render(['status' => false,'message' => 'anggaran not found'],'json');exit();
        endif;

        $data_finish['kode_anggaran']   = $anggaran->kode_anggaran;
        $data_finish['kode_cabang']     = $cabang;
        $a = get_access($this->controller,$data_finish);
        $access_edit = false;
        $access_edit2 = false;
        if($a['access_edit'] && $cabang == user('kode_cabang')):
            $access_edit = true;
        elseif($a['access_edit'] && $a['access_additional']):
            $access_edit = true;
            $access_edit2 = true;
        endif;
        $data['akses_ubah'] = $access_edit;
        $data['access_edit2'] = $access_edit2;
        $data['access_additional'] = $a['access_additional'];

        // pengecekan akses cabang
        check_access_cabang($this->controller,$anggaran1,$cabang,$a);

        $this->check_laba_diinginkan($anggaran,$cabang);

        $bln_trakhir = $anggaran->bulan_terakhir_realisasi;
        $thn_trakhir = $anggaran->tahun_terakhir_realisasi;
        $tbl_history = 'tbl_history_'.$thn_trakhir;

        $data['adj'] = get_data('tbl_labarugi_adj',[
            'where' => "kode_cabang =  '".$cabang."' and kode_anggaran = '".$anggaran1."'"
        ])->result_array();

        $or_neraca  = "(a.glwnco like '4%' or a.glwnco like '5%')";
        $select     = 'distinct level1,level2,level3,level4,level5,
                    a.glwsbi,a.glwnob,a.glwcoa,a.glwnco,a.glwdes,a.kali_minus';
        
        $select_labarugi = ',c.bulan_1,c.bulan_2,c.bulan_3,c.bulan_4,c.bulan_5,c.bulan_6,c.bulan_7,c.bulan_8,c.bulan_9,c.bulan_10,c.bulan_11,c.bulan_12,ifnull(c.changed,"[]") as changed,c.last_real';
        
        $coa = get_data('tbl_m_coa a',[
            'select' => $select.',b.TOT_'.$cabang.$select_labarugi,
            'where' => "
                a.kode_anggaran = '".$anggaran->kode_anggaran."' and a.is_active = '1' and $or_neraca
                ",
            'order_by' => 'a.urutan',
            'join' => [
                "$tbl_history b on b.bulan = '$bln_trakhir' and a.glwnco = b.glwnco type left",
                "tbl_labarugi c on c.glwnco = a.glwnco and kode_cabang = '$cabang' and c.kode_anggaran = '$anggaran->kode_anggaran' type left"
            ]
        ])->result();
        $coa = $this->get_coa($coa);


        $query_result     = $this->db->query("CALL stored_laba_rugi_nett('$cabang','$anggaran->kode_anggaran','$anggaran->tahun_anggaran')");
        // $query_result = $this->get_data_net($anggaran->kode_anggaran,$cabang,$anggaran->tahun_anggaran);
        $data['stored']           = $query_result->result_array();
        // print_r($this->db->last_query());    
        // $data['adj'] = get_data('tbl_labarugi_adj',[
        //     'where' => [
        //         'kode_cabang'       => $cabang,
        //         'kode_anggaran'     => $anggaran
        //     ]
        // ])->result_array();
        //add this two line 
        $query_result->next_result(); 
        $query_result->free_result(); 
        //end of new code

        // $coa = $this->get_list_coa($coa,$detail);

      

        $data['coa']    = $coa['coa'];
        $data['detail'] = $coa['detail'];
        $data['cabang'] = $cabang;
        $data['anggaran'] = $anggaran;
        $data['bulan_terakhir'] = $bln_trakhir;

        // $data['biaya'] = get_data("tbl_biaya",[
        //     'kode_cabang'   => $cabang,
        // ])->result_array();


        $index_pendapatan = get_data('tbl_m_index_pendapatan',[
            'select'    => 'index_kali,coa',
            'where'     => [
                'is_active' => 1,
                'kode_anggaran' => $anggaran->kode_anggaran
            ]
        ])->result_array();
        $data['index_pendapatan'] = $index_pendapatan;

        $detail_tahun2   = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => "a.kode_anggaran = '".$this->kode_anggaran."' and a.tahun = '".($anggaran->tahun_anggaran-1)."' ",
            'order_by' => 'tahun,bulan'
        ])->result_array();
        $data['detail_tahun2'] = $detail_tahun2;
        $data['core_laba'] = get_data_core(['59999'],[($anggaran->tahun_anggaran-1)],'TOT_'.$cabang);
        $data['usulan_laba'] = get_data('tbl_bottom_up_form1',[
            'where' => [
                'coa' => '59999',
                'kode_cabang' => $cabang,
                'kode_anggaran' => $anggaran->kode_anggaran,
                'data_core'    => ($anggaran->tahun_anggaran-1)
            ]
        ])->row();

        $view = $this->load->view($this->path.'laba_rugi_new/table',$data,true);

        $response   = array(
            'status'    => true,
            'table'     => $view,
            'data' => $data,
            'access_edit' => $access_edit
        );
        render($response,'json');
    }

     private function get_list_coa($coa,$detail){
        $data = [];
        foreach ($coa as $k => $v) {
            // level 0
            if(!$v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
                $key = multidimensional_search($detail, array(
                    'coa' => $v->glwnco,
                ));
                $h = $v;
                if(strlen($key)>0):
                    $h = array_merge($h,$detail[$key]);
                endif;
                $data['coa'][] = $h;
            endif;

            // level 1
            if($v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
                $key = multidimensional_search($detail, array(
                    'level1' => $v->level1,
                    'coa'    => $v->glwnco,
                ));
                $h = (array) $v;
                if(strlen($key)>0):
                    $h = array_merge($h,$detail[$key]);
                endif;
                $data['detail']['1'][$v->level1][] = $h;
            endif;

            // level 2
            if(!$v->level1 && $v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
                $key = multidimensional_search($detail, array(
                    'level2' => $v->level2,
                    'coa'    => $v->glwnco,
                ));
                $h = (array) $v;
                if(strlen($key)>0):
                    $h = array_merge($h,$detail[$key]);
                endif;
                $data['detail']['2'][$v->level2][] = $h;
            endif;

            // level 3
            if(!$v->level1 && !$v->level2 && $v->level3 && !$v->level4 && !$v->level5):
                $key = multidimensional_search($detail, array(
                    'level3' => $v->level3,
                    'coa'    => $v->glwnco,
                ));
                $h = (array) $v;
                if(strlen($key)>0):
                    $h = array_merge($h,$detail[$key]);
                endif;
                $data['detail']['3'][$v->level3][] = $h;
            endif;

            // level 4
            if(!$v->level1 && !$v->level2 && !$v->level3 && $v->level4 && !$v->level5):
                $key = multidimensional_search($detail, array(
                    'level4' => $v->level4,
                    'coa'    => $v->glwnco,
                ));
                $h = (array) $v;
                if(strlen($key)>0):
                    $h = array_merge($h,$detail[$key]);
                endif;
                $data['detail']['4'][$v->level4][] = $h;
            endif;

            // level 5
            if(!$v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && $v->level5):
                $key = multidimensional_search($detail, array(
                    'level5' => $v->level5,
                    'coa'    => $v->glwnco,
                ));
                $h = (array) $v;
                if(strlen($key)>0):
                    $h = array_merge($h,$detail[$key]);
                endif;
                $data['detail']['5'][$v->level5][] = $h;
            endif;
        }
        return $data;
    }

    private function get_coa($coa){
        $data = [];
        foreach ($coa as $k => $v) {
            // level 0
            if(!$v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
                $data['coa'][] = $v;
            endif;

            // level 1
            if($v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
                $data['detail']['1'][$v->level1][] = $v;
            endif;

            // level 2
            if(!$v->level1 && $v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
                $data['detail']['2'][$v->level2][] = $v;
            endif;

            // level 3
            if(!$v->level1 && !$v->level2 && $v->level3 && !$v->level4 && !$v->level5):
                $data['detail']['3'][$v->level3][] = $v;
            endif;

            // level 4
            if(!$v->level1 && !$v->level2 && !$v->level3 && $v->level4 && !$v->level5):
                $data['detail']['4'][$v->level4][] = $v;
            endif;

            // level 5
            if(!$v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && $v->level5):
                $data['detail']['5'][$v->level5][] = $v;
            endif;
        }
        return $data;
    }

    function save_perubahan($anggaran="",$cabang="") {       
        // pengecekan save untuk cabang
        check_save_cabang($this->controller,$anggaran,$cabang,'access_edit');
        $data   = json_decode(post('json'),true);

        // echo post('json');
        foreach($data as $getId => $record) {
            $cekId      = $getId;
            $changed    = [];
            $cek  = get_data('tbl_labarugi a',[
                'select'    => 'a.id,a.changed',
                'where'     => [
                    'a.glwnco'             => $cekId,
                    'a.kode_anggaran'   => $anggaran,
                    'a.kode_cabang'   => $cabang,
                ]
            ])->result_array();

            if(count($cek)>0):
                $changed = json_decode($cek[0]['changed']);
                if(!is_array($changed)) $changed = [];
            endif;

            foreach ($record as $k2 => $v2) {
                $value = filter_money($v2);
                $record[$k2] = $value;

                if(!in_array($k2,$changed)) array_push($changed, $k2);
            }

            $data = insert_view_report_arr($record);
     
            if(count($cek) > 0){
                $data['changed'] = json_encode($changed);
                update_data('tbl_labarugi', $data,'id',$cek[0]['id']);
            }else {
                    // echo $cekId."<br>";
                    // echo $anggaran."<br>";
                    // echo $cabang."<br>";
                    $data['glwnco'] = $cekId;
                    $data['kode_anggaran'] = $anggaran;
                    $data['kode_cabang'] = $cabang;
                    $data['changed'] = json_encode($changed);
                    insert_data('tbl_labarugi',$data);
            }
        }

        render(['status' => true,'message' => lang('data_berhasil_diperbaharui')],'json');
    }


    function save_adj($anggaran="",$cabang="",$glwnco="",$aksi="") {       
        check_save_cabang($this->controller,$anggaran,$cabang,'access_edit');
        $data   = json_decode(post('json'),true);

        $a = get_access($this->controller);
        $access_edit = false;
        if($a['access_edit'] && $cabang == user('kode_cabang')):
            $access_edit = true;
        elseif($a['access_edit'] && $a['access_additional']):
            $access_edit = true;
        endif;
        $access_additional = $a['access_additional'];

        foreach($data as $key => $record) {

            foreach ($record as $k2 => $v2) {
                $value = filter_money($v2);
                $record[$k2] = $value;
            }

            $data = insert_view_report_arr($record);
            // echo json_encode($key);
            $record2= [];

            if($key == 'selisih'){
                  $cek  = get_data('tbl_labarugi_adj a',[
                    'select'    => 'a.id',
                    'where'     => [
                        'a.glwnco'          => $glwnco,
                        'a.kode_anggaran'   => $anggaran,
                        'a.kode_cabang'     => $cabang,
                        'a.type'            => $key,
                    ]
                ])->result_array();
            }else {
                $cek  = get_data('tbl_labarugi_adj a',[
                    'select'    => 'a.id',
                    'where'     => [
                        'a.kode_anggaran'   => $anggaran,
                        'a.kode_cabang'     => $cabang,
                        'a.type'            => $key,
                    ]
                ])->result_array();
            }
          
            $get = get_data('tbl_labarugi',[
                'where' => [
                    'glwnco' => $glwnco,
                    'kode_anggaran'   => $anggaran,
                    'kode_cabang'     => $cabang,
                ],
            ])->result_array();

            if($key == 'selisih'){
                if(!empty($get)){
                    if($aksi == "t"){
                       for($i=1;$i<=12;$i++){
                            $bulan = "bulan_".$i;
                            if(isset($data[$bulan])):
                                $record2[$bulan] = $get[0][$bulan] + $data[$bulan];
                            endif;
                        }
                    }else {
                        for($i=1;$i<=12;$i++){
                            $bulan = "bulan_".$i;
                            if(isset($data[$bulan])):
                                $record2[$bulan] = $get[0][$bulan] - $data[$bulan];
                            endif;
                        }
                    }
                    
                }else {
                    for($i=1;$i<=12;$i++){
                        $bulan = "bulan_".$i;
                        if(isset($data[$bulan])):
                            $record2[$bulan] =  $data[$bulan];
                        endif;
                    }
                }
                $record2['glwnco'] = $glwnco;
            }else {
                for($i=1;$i<=12;$i++){
                    $bulan = "bulan_".$i;
                    if(isset($data[$bulan])):
                        $record2[$bulan] =  $data[$bulan];
                    endif;
                }
                $record2['glwnco'] = '';
            }

            if($access_additional):

            endif;
            
            
            $record2['type'] = $key;
            
            if(count($cek) > 0){
                update_data('tbl_labarugi_adj', $record2,'id',$cek[0]['id']);
            }else {
                    // $record2['glwnco'] = $glwnco;
                    $record2['kode_anggaran'] = $anggaran;
                    $record2['kode_cabang'] = $cabang;
                    insert_data('tbl_labarugi_adj',$record2);
            } 
            // echo json_encode($record2);
        }

        $load = 'loadData';
        if(!$access_additional):
            $load = '';
        else:
            // save status to budget nett
            save_status_budget_nett($anggaran,$cabang,'tbl_history_to_budget_nett_labarugi',0);
            // create budget nett
            // $this->db->query("call stored_budget_nett('labarugi','".$cabang."','".$anggaran."')");
        endif;
        render([
            'status'    => true,
            'message'   => lang('data_berhasil_diperbaharui'),
            'load'      => $load,
        ],'json');
    }

    function delete_adj(){
        $kode_cabang = post('id');
        if($kode_cabang):
            delete_data('tbl_labarugi_adj',['type' => 'selisih','kode_cabang' => $kode_cabang, 'kode_anggaran' => user('kode_anggaran')]);
            render(['status' => 'success', 'message' => lang('berhasil')],'json');
        else:
            render(['status' => false,'message' => lang('data_not_found')],'json');
        endif;
    }

    function check_laba_diinginkan($anggaran,$kode_cabang){
        $ck = get_data('tbl_labarugi_adj',[
            'select' => 'id',
            'where' => [
                'kode_cabang'   => $kode_cabang,
                'kode_anggaran' => $anggaran->kode_anggaran,
                'type'          => 'sdbulan',
            ]
        ])->row();
        if(!$ck):
            $dt_laba = get_data('tbl_indek_besaran',[
                'where' => [
                    'kode_cabang'   => $kode_cabang,
                    'kode_anggaran' => $anggaran->kode_anggaran,
                    'tahun_core'    => $anggaran->tahun_anggaran,
                    'coa'           => '59999'
                ]
            ])->row();
            $data = [
                'kode_anggaran' => $anggaran->kode_anggaran,
                'kode_cabang'   => $kode_cabang,
                'type'          => 'sdbulan',
            ];
            for ($i=1; $i <= 12 ; $i++) { 
                $val = 0;
                if($dt_laba):
                    $val = $dt_laba->{'hasil'.$i};
                endif;
                $data['bulan_'.$i] = $val;
            }

            // sampai dengan bulan
            insert_data('tbl_labarugi_adj',$data);

            // pada bulan
            $dt_sdbulan = $data;
            $data['type'] = 'pdbulan';
            for ($i=1; $i <= 12 ; $i++) { 
                $val = checkNumber($dt_sdbulan['bulan_'.$i]);
                if(isset($dt_sdbulan['bulan_'.($i-1)])):
                    $val -= checkNumber($dt_sdbulan['bulan_'.($i-1)]);
                endif;
                $data['bulan_'.$i] = $val;
            }
            insert_data('tbl_labarugi_adj',$data);

        endif;
    }

    function save_nett($kode_anggaran,$kode_cabang,$check=true){
        $access = get_access($this->controller);
        if(!$access['access_edit'] or !$access['access_additional']):
            render(['status' => 'failed', 'message' => lang('izin_ditolak')],'json');exit();
        endif;
        $check = filter_var($check, FILTER_VALIDATE_BOOLEAN);

        if(!$check):
            $this->db->query("call stored_budget_nett('labarugi','".$kode_cabang."','".$kode_anggaran."')");

            // save status to budget nett
            save_status_budget_nett($kode_anggaran,$kode_cabang,'tbl_history_to_budget_nett_labarugi');

            $ck = $this->get_data_budget_nett($kode_anggaran,$kode_cabang,$check);
            $item = '<table class="table table-striped table-bordered table-app table-hover">';
            $item .= '<thead>';
            $item .= '<tr>';
            $item .= '<td class="text-center" style="min-width:100px !important">'.lang('coa').' 7</td>';
            $item .= '<td class="text-center" style="min-width:100px !important">'.lang('nama_coa').'</td>';
            $item .= '<td class="text-center" style="min-width:100px !important">'.lang('keterangan').'</td>';
            for ($i=1; $i <= 12 ; $i++) { 
                // $field  = 'B_' . sprintf("%02d", $i);
                $item .= '<td class="text-center" style="min-width:100px !important">'.month_lang($i).'</td>';
            }
            $item .= '</tr>';
            $item .= '</thead>';
            
            $item .= '<tbody>';
            $item .= '<tr>';
            $item .= '<td>'.$ck->glwnco.'</td>';
            $item .= '<td>'.remove_spaces($ck->glwdes).'</td>';
            $item .= '<td>neraca nett</td>';
            for ($i=1; $i <= 12 ; $i++) { 
                $field  = 'B_' . sprintf("%02d", $i);
                $item .= '<td class="text-right">'.custom_format(view_report($ck->{'n_'.$field})).'</td>';
            }
            $item .= '</tr>';

            $item .= '<tr>';
            $item .= '<td>'.$ck->glwnco.'</td>';
            $item .= '<td>'.remove_spaces($ck->glwdes).'</td>';
            $item .= '<td>budget nett neraca</td>';
            for ($i=1; $i <= 12 ; $i++) { 
                $field  = 'B_' . sprintf("%02d", $i);
                $item .= '<td class="text-right">'.custom_format(view_report($ck->{'b_'.$field})).'</td>';
            }
            $item .= '</tr>';

            $item .= '<tr>';
            $item .= '<td></td>';
            $item .= '<td></td>';
            $item .= '<td>'.lang('selisih').'</td>';
            for ($i=1; $i <= 12 ; $i++) { 
                $field  = 'B_' . sprintf("%02d", $i);
                $val = checkNumber($ck->{'n_'.$field}) - checkNumber($ck->{'b_'.$field});
                $item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
            }
            $item .= '</tr>';
            $item .= '</tbody>';
            $item .= '</table>';
            render([
                'status' => 'success', 
                'message' => lang('data_berhasil_disimpan'),
                'view'  => $item,
            ],'json');
        else:
            $ck = $this->get_data_budget_nett($kode_anggaran,$kode_cabang,$check);

            $selisih = checkNumber($ck->n_B_12) - checkNumber($ck->b_B_12);
            $start = '';
            $end   = '';
            if($selisih<0):
                $start = '(';
                $end = ')';
                $selisih *= -1;
            endif;
            $selisih = $start.number_format(view_report($selisih),0,",",".").$end;

            $message = lang('selisih').' '.lang('coa').' "'.$ck->glwnco.' - '.remove_spaces($ck->glwdes).'" '.lang('bulan').' '.month_lang(12).' '.lang('adalah').' "'.$selisih.'"';
            render(['status' => true, 'message' => $message],'json');
        endif;
    }

    private function get_data_budget_nett($kode_anggaran,$kode_cabang,$check){
        $select = '';
        if(!$check):
            for ($i=1; $i <= 12 ; $i++) { 
                $field  = 'B_' . sprintf("%02d", $i);
                $select .= " ifnull(a.bulan_$i,0) as n_$field,";
                $select .= " ifnull(b.$field,0) as b_$field,";
            }
        else:
            $field  = 'B_' . sprintf("%02d", 12);
            $select .= " ifnull(a.bulan_12,0) as n_$field,";
            $select .= " ifnull(b.$field,0) as b_$field,";
        endif;
        $ck = get_data('tbl_labarugi a',[
            'select' => 'c.glwnco,c.glwdes,'.$select,
            'join' => [
                "tbl_budget_nett_labarugi b on b.coa = a.glwnco and b.kode_anggaran = a.kode_anggaran and b.kode_cabang = a.kode_cabang and b.coa != '' type left",
                "tbl_m_coa c on c.glwnco = a.glwnco and c.kode_anggaran = a.kode_anggaran and c.glwnco != '' type left"
            ],
            'where' => [
                'a.kode_cabang'   => $kode_cabang,
                'a.kode_anggaran' => $kode_anggaran,
                'a.glwnco' => '59999'
            ] 
        ])->row();
        if(!$ck):
            render(['status' => false, 'message' => lang('data_not_found')],'json');
        endif;
        return $ck;
    }

}