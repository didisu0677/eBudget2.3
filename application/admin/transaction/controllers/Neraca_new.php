<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Neraca_new extends BE_Controller {
    var $path = 'transaction/budget_planner/';
    var $controller = 'neraca_new';
    var $table = "tbl_budget_plan_neraca";
    var $arrAdjusment = ['1801000','2801000'];
    var $arrHeadAdjusment = ['1000000','2000000'];
    var $arrDpk = ['2130000','2120011','2100000'];
    var $arrDanaPasvia = ["2802000","2803101","2803201","2803301","2803701","2803851"];
    var $arrDanaAktiva = ["1802000","1803101","1803201","1803301","1803401","1803501","1803505","1803601","1803701","1803801"];
    var $arrAdditional = [];
    var $arrSumPerCoa  = ['1800000','2800000'];
    var $detail_tahun;
    var $kode_anggaran;
    var $anggaran;
    var $arr_sumber_data = array();
    var $arr_tahun_core = array();
    var $arr_coa = array();
    var $history_status = false;
    function __construct() {
        parent::__construct();
        $this->arrAdditional = get_data('tbl_m_neraca_nett_additional',[
            'select' => 'id,nama,coa,glwnco',
            'where'  => 'is_active = 1',
        ])->result_array();
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

    function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_cabang_txt    = post('kode_cabang_txt');

        $neraca_header = json_decode(post('neraca_header'));
        $neraca = json_decode(post('neraca'));

        $data = [];
        foreach ($neraca as $k => $v) {
            if(count($v)>2):
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
            else:
                $data[$k] = [];
                for($i=1;$i<=count($neraca_header[1]);$i++){
                    $data[$k][] = '';
                }
            endif;
        }

        unset($data[0]);
        $config[] = [
            'title' => 'Neraca Nett',
            'header' => $neraca_header[1],
            'data'  => $data,
        ];
        // render($config,'json');exit();
        $this->load->library('simpleexcel',$config);
        $filename = 'Neraca_Nett_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }
    
    function index($p1="") { 
        $access         = get_access('neraca_new');
        $akses_ubah     = $access['access_edit'];

        $data = data_cabang('neraca_new');
        $data['path'] = $this->path;
        $data['access_additional']  = $access['access_additional'];
        $data['akses_ubah']         = $akses_ubah;
        $data['detail_tahun']   = $this->detail_tahun;
        $data['bulan_terakhir'] = month_lang($data['tahun'][0]->bulan_terakhir_realisasi);
        render($data,'view:'.$this->path.'neraca_new/index');
    }

    function data ($anggaran="", $cabang=""){
        $this->session->unset_userdata(['dt_neraca','dt_anggaran','arrAktiva','arrPasiva','coa1801000','coa2801000','arrDpk','dana_pasiva','dana_aktiva','status_update_neraca_nett']);
        foreach ($this->arrAdditional as $addit) {
            $key_addit      = 'additional_'.$addit['id'];
            $this->session->unset_userdata([$key_addit]);
        }
        foreach ($this->arrSumPerCoa as $x) {
            $this->session->unset_userdata(['coa'.$x]);
        }

        $anggaran = $this->anggaran;

        // pengecekan akses cabang
        $a = get_access($this->controller);
        check_access_cabang($this->controller,$anggaran->kode_anggaran,$cabang,$a);

        $bln_trakhir = $anggaran->bulan_terakhir_realisasi;
        $thn_trakhir = $anggaran->tahun_terakhir_realisasi;
        $tbl_history = 'tbl_history_'.$thn_trakhir;

        $or_neraca  = "(a.glwnco like '1%' or a.glwnco like '2%' or a.glwnco like '3%' or a.glwnco LIKE '41%' AND a.level1 = '2120011')";
        $select     = 'level0,level1,level2,level3,level4,level5,
                    a.glwsbi,a.glwnob,a.glwcoa,a.glwnco,a.glwdes,a.kali_minus,';
        $selectJoin = 'c.id as fID,c.realisasi as frealisasi,c.changed as fchanged,';
        for ($i=1; $i <=12 ; $i++) {
            $field = sprintf("%02d", $i); 
            $selectJoin .= 'c.B_'.$field.' as fB_'.$field.', ';
        }
        $coa = get_data('tbl_m_coa a',[
            'select' => $select.$selectJoin.',b.TOT_'.$cabang,
            'where' => "
                a.kode_anggaran = '".$anggaran->kode_anggaran."' and a.is_active = '1' and $or_neraca
                ",
            'order_by' => 'a.urutan',
            'join' => [
                "$tbl_history b on b.bulan = '$bln_trakhir' and a.glwnco = b.glwnco type left",
                "tbl_budget_plan_neraca c on a.glwnco = c.coa and c.kode_anggaran = '$anggaran->kode_anggaran' and kode_cabang = '$cabang' type left",
            ]
        ])->result();

        $query_result     = $this->db->query("CALL stored_neraca_nett('$cabang','$anggaran->kode_anggaran','$anggaran->tahun_anggaran')");
        // $query_result = $this->get_data_net($anggaran->kode_anggaran,$cabang,$anggaran->tahun_anggaran);
        $detail           = $query_result->result_array();

        //add this two line 
        $query_result->next_result(); 
        $query_result->free_result(); 
        //end of new code

        $coa = $this->get_list_coa($coa,$detail);
        
        // data core / history
        $data_core = [];
        if($this->history_status && count($this->arr_coa)>0): 
            $column = 'TOT_'.$cabang;
            $data_core = get_data_core($this->arr_coa,$this->arr_tahun_core,$column);
        endif;
        
        $this->session->set_userdata(array(
            'dt_neraca'     => $coa,
            'dt_anggaran'   => $anggaran,
            'data_core'     => $data_core
        ));

        $data['coa']    = $coa['coa'];
        $data['detail'] = $coa['detail'];
        $data['cabang'] = $cabang;
        $data['kode_anggaran'] = $anggaran->kode_anggaran;
        $dt_view = $this->get_view_coa($data,0);

        $response   = $dt_view;
        $response['status'] = true;
        render($response,'json');
    }

    private function get_list_coa($coa,$detail){
        $data = [];
        foreach ($coa as $k => $v) {
            if(!in_array($v->glwnco,$this->arr_coa)):
                array_push($this->arr_coa, $v->glwnco);
            endif;

            // center
            if(!$v->level0 && !$v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
                $key = multidimensional_search($detail, array(
                    'coa' => $v->glwnco,
                ));
                $h = (array) $v;
                if(strlen($key)>0):
                    $h['kali_minus'] = 0;
                    $h = array_merge($h,$detail[$key]);
                endif;
                $data['coa'][] = $h;
            endif;

            // level 0
            if($v->level0 && !$v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
                $key = multidimensional_search($detail, array(
                    'coa' => $v->glwnco,
                ));
                $h = (array) $v;
                if(strlen($key)>0):
                    $h['kali_minus'] = 0;
                    $h = array_merge($h,$detail[$key]);
                endif;
                $data['detail']['coa0'][$v->level0][] = $h;
            endif;

            // level 1
            if(!$v->level0 && $v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
                $key = multidimensional_search($detail, array(
                    'level1' => $v->level1,
                    'coa'    => $v->glwnco,
                ));
                $h = (array) $v;
                if(strlen($key)>0):
                    $h['kali_minus'] = 0;
                    $h = array_merge($h,$detail[$key]);
                endif;
                $data['detail']['coa1'][$v->level1][] = $h;
            endif;

            // level 2
            if(!$v->level0 && !$v->level1 && $v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
                $key = multidimensional_search($detail, array(
                    'level2' => $v->level2,
                    'coa'    => $v->glwnco,
                ));
                $h = (array) $v;
                if(strlen($key)>0):
                    $h['kali_minus'] = 0;
                    $h = array_merge($h,$detail[$key]);
                endif;
                $data['detail']['coa2'][$v->level2][] = $h;
            endif;

            // level 3
            if(!$v->level0 && !$v->level1 && !$v->level2 && $v->level3 && !$v->level4 && !$v->level5):
                $key = multidimensional_search($detail, array(
                    'level3' => $v->level3,
                    'coa'    => $v->glwnco,
                ));
                $h = (array) $v;
                if(strlen($key)>0):
                    $h['kali_minus'] = 0;
                    $h = array_merge($h,$detail[$key]);
                endif;
                $data['detail']['coa3'][$v->level3][] = $h;
            endif;

            // level 4
            if(!$v->level0 && !$v->level1 && !$v->level2 && !$v->level3 && $v->level4 && !$v->level5):
                $key = multidimensional_search($detail, array(
                    'level4' => $v->level4,
                    'coa'    => $v->glwnco,
                ));
                $h = (array) $v;
                if(strlen($key)>0):
                    $h['kali_minus'] = 0;
                    $h = array_merge($h,$detail[$key]);
                endif;
                $data['detail']['coa4'][$v->level4][] = $h;
            endif;

            // level 5
            if(!$v->level0 && !$v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && $v->level5):
                $key = multidimensional_search($detail, array(
                    'level5' => $v->level5,
                    'coa'    => $v->glwnco,
                ));
                $h = (array) $v;
                if(strlen($key)>0):
                    $h['kali_minus'] = 0;
                    $h = array_merge($h,$detail[$key]);
                endif;
                $data['detail']['coa5'][$v->level5][] = $h;
            endif;
        }
        return $data;
    }

    private function get_view_coa($data,$count){
        $data_finish['kode_anggaran']   = $data['kode_anggaran'];
        $data_finish['kode_cabang']     = $data['cabang'];
        $access = get_access($this->controller,$data_finish);
        $access_edit = false;
        if($access['access_edit'] && $data['cabang'] == user('kode_cabang')):
            $access_edit = true;
        elseif($access['access_edit'] && $access['access_additional']):
            $access_edit = true;
        endif;
        $data['access_edit'] = $access_edit;

        $no = $count;
        $status = false;
        $view = '';
        for ($i=$count; $i <($count+1) ; $i++) { 
            if(isset($data['coa'][$i])){
                $status = true;
                $no += 1;
                $data['key'] = $i;
                $view .= $this->loadView($data);
            }else{
                break;
            }
        }

        $res = [
            'status'    => $status,
            'view'      => $view,
            'count'     => $no,
            'access_edit' => $access_edit
        ];
        return $res;

    }

    function loadMore($anggaran,$cabang,$count){
        $coa = $this->session->dt_neraca;
        $data['coa']    = $coa['coa'];
        $data['detail'] = $coa['detail'];
        $data['cabang'] = $cabang;
        $data['kode_anggaran'] = $anggaran;

        $dt_view = $this->get_view_coa($data,$count);

        $response   = $dt_view;
        render($response,'json');
    }

    private function loadView($data){
        $coa    = $data['coa'];
        $detail = $data['detail'];
        $key    = $data['key'];
        $cabang = $data['cabang'];

        $item = '';
        $td_transparnt = '<td class="border-none bg-white"></td>';

        $akses_ubah     = $data['access_edit'];
        $bgedit ="";
        $contentedit ="false" ;
        $id = 'keterangan';
        if($akses_ubah == 1) {
            $bgedit =bgEdit();
            $contentedit ="true" ;
            $id = 'id' ;
        }

        $v = $coa[$key];
        $v = json_encode($v);$v = json_decode($v);

        $item2  = '';
        $dt2    = [];
        $dt_status2 = true;
        $minus  = $v->kali_minus;
        $arrAktiva = [];
        if($this->session->arrAktiva){ $arrAktiva = $this->session->arrAktiva; }
        $arrPasiva = [];
        if($this->session->arrPasiva){ $arrPasiva = $this->session->arrPasiva; }

        $bln_trakhir = $v->{'TOT_'.$cabang};
        $changed = [];
        if(isset($detail['coa0'][$v->glwnco])){
            $dt = $this->loadViewLoop($data,$detail['coa0'][$v->glwnco],0);
            $item2  = $dt['item'];
            $dt2    = $dt['dt'];
            $dt_status2    = $dt['dt_status'];
            $bln_trakhir = '';
            $value = 0;
        }else{
            $bln_trakhir = $v->{'TOT_'.$cabang};
            $value = kali_minus($bln_trakhir,$minus);
            $bln_trakhir = check_value($value,true);
            $changed    = json_decode($v->fchanged,true);
        }

        $status = true;
        if($dt_status2 || isset($v->tipe)):
            $minus = 0;
            $status = true;
        endif;
        $status_update = false;
        $arrUpdate = [];
        $arrInsert = [];
        $item = '';
        if(in_array($v->glwnco, ['2000000'])):
             $item .= '<tr class="d-spaces"></tr>';
        endif;
        if(in_array($v->glwnco, $this->arrHeadAdjusment)):
            $item .= '<tr class="d-'.$v->glwnco.'">';
        else:
            $item .= '<tr>';
        endif;

        // buat variable himpunan
        foreach ($this->arrAdditional as $addit) {
            $key_addit = 'additional_'.$addit['id'];
            ${$key_addit} = [];
            $coa_addit = json_decode($addit['coa'],true);
            if(in_array($v->glwnco, $coa_addit)):
                ${$key_addit} = $this->session->{$key_addit};
            endif;
        }

        $item .= '<td class="wd-100">'.$v->glwsbi.'</td>';
        $item .= '<td class="wd-100">'.$v->glwnob.'</td>';
        $item .= '<td class="wd-100">'.$v->glwnco.'</td>';
        $item .= '<td class="wd-230">'.remove_spaces($v->glwdes).'</td>';
        $data_core = $this->session->data_core;
        foreach ($this->detail_tahun as $dt_bln_anggaran) {
            $i = $dt_bln_anggaran->bulan;
            $field  = 'B_' . sprintf("%02d", $i);
            
            if(count($dt2)>0){ $val = $dt2[$i]; }
            else{ 
                $val = $value; 
                if(isset($v->{$field})){ $val =  $v->{$field}; }
            }
            $val = kali_minus($val,$minus);
            if(isset($changed[$field]) && $changed[$field] == 1):
                $val = $v->{'f'.$field};
            endif;

            $val = round_value($val);

            if($v->glwnco == '1000000'):
                $arrAktiva[$field] = $val;
            elseif($v->glwnco == '2000000'):
                $arrPasiva[$field] = $val;
            endif;

            if(count($dt2)<=0 && $akses_ubah):
                $name = $v->glwnco.'-'.$cabang;
                $bgedit = '';
                $item .= '<td style="background:'.$bgedit.'"><div style="background:'.$bgedit.'" style="min-height: 10px; width: 50px; overflow: hidden;"  contenteditable="'.$contentedit.'" class="edit-value text-right '.$field.'" data-name="'.$field.'" data-id="'.$name.'" data-value="'.$val.'">'.check_value($val,true).'</div></td>';
            else:
                $item .= '<td class="text-right '.$field.'">'.check_value($val,true).'</td>';
            endif;

            // sum untuk coa himpunan
            foreach ($this->arrAdditional as $addit) {
                $key_addit = 'additional_'.$addit['id'];
                $coa_addit = json_decode($addit['coa'],true);
                if(in_array($v->glwnco, $coa_addit)):
                    if(isset(${$key_addit}[$field])):
                        ${$key_addit}[$field] += $val;
                    else:
                        ${$key_addit}[$field] = $val;
                    endif;
                endif;
            }

            if($v->fID):
                if($val != $v->{'f'.$field}):
                    $status_update = true;
                    $arrUpdate[$field] = $val;
                endif;
            else:
                $status_update = true;
                $arrInsert[$field] = $val;
            endif;
        }
        if($v->fID):
            if($val != $v->{'frealisasi'}):
                $status_update = true;
                $arrUpdate['realisasi'] = $value;
            endif;
        else:
            $status_update = true;
            $arrDataInsert['realisasi'] = $value;
        endif;
        if($status_update):
            $arrUpdate['status_budget_nett'] = 0;
            $this->update_data($arrUpdate,$v->fID);
            $this->insert_data($arrInsert,$v->glwnco,$cabang);
            $this->session->set_userdata(['status_update_neraca_nett' => 1]);
        endif;

        // simpan ke session himpunan dana
        foreach ($this->arrAdditional as $addit) {
            $key_addit = 'additional_'.$addit['id'];
            $coa_addit = json_decode($addit['coa'],true);
            if(in_array($v->glwnco, $coa_addit)):
                $this->session->set_userdata([$key_addit => ${$key_addit}]);
            endif;
        }

        $item .= $td_transparnt;
        $item .= '<td class="text-right">'.$bln_trakhir.'</td>';
        $item .= '</tr>';
        if(!$status):
            $item = '';
        endif;
        $item .= $item2;

        $this->session->set_userdata(array('arrAktiva' => $arrAktiva, 'arrPasiva' => $arrPasiva));

        return $item;
    }

    private function loadViewLoop($data, $data2, $kk){
        $detail = $data['detail'];
        $cabang = $data['cabang'];

        $akses_ubah     = $data['access_edit'];

        $bgedit ="";
        $contentedit ="false" ;
        $id = 'keterangan';
        if($akses_ubah == 1) {
            $bgedit =bgEdit();
            $contentedit ="true" ;
            $id = 'id' ;
        }

        $data2 = json_encode($data2);$data2 = json_decode($data2);

        $item   = '';
        $td_transparnt = '<td class="border-none bg-white"></td>';
        $dt     = [];
        $dt_status = true;
        if($kk<=5){
            foreach ($data2 as $k2 => $v2) {
                $item2      = '';
                $dt2        = [];
                $d_status2  = false;
                $minus  = $v2->kali_minus;
                $changed = [];
                if(isset($detail['coa'.($kk+1)][$v2->glwnco])){
                    $dd = $detail['coa'.($kk+1)][$v2->glwnco];
                    $dd = $this->loadViewLoop($data,$dd,($kk+1));
                    $item2  = $dd['item'];
                    $dt2    = $dd['dt'];
                    $d_status2 = $dd['dt_status'];
                    $bln_trakhir = '';
                    $value = 0;
                }else{
                    $bln_trakhir = $v2->{'TOT_'.$cabang};
                    $value = $bln_trakhir;
                    if($v2->fID)://pengecekan untuk mengambil realisasi trakhir dari table
                        $changed = json_decode($v2->fchanged,true);
                        if(isset($changed['realisasi']) && $changed['realisasi'] == 1):
                            $bln_trakhir = $v2->frealisasi;
                            $value = $v2->frealisasi;
                            $minus = 0;
                        endif;
                    endif;
                    $bln_trakhir = kali_minus($bln_trakhir,$minus);
                    $bln_trakhir = check_value($bln_trakhir,true);
                }
                $status = true;
                $item3 = '';
                if($v2->glwnco == '2150000'):// cek untuk total giro+tabungan+simpanan berjakngka
                    $item3 .= '<tr class="d-total-pasiva"></tr>';
                endif;

                // buat variable adjusment
                if(in_array($v2->glwnco, $this->arrAdjusment)):
                    $arrAdjusmentSet = [];
                    if($this->session->{'coa'.$v2->glwnco}) $arrAdjusmentSet = $this->session->{'coa'.$v2->glwnco};
                endif;
                $item3  .= '<tr class="d-'.$v2->glwnco.'">';

                // buat variable arrSumPerCoa
                if(in_array($v2->glwnco, $this->arrSumPerCoa)):
                    $arrSumPerCoa = [];
                    if($this->session->{'coa'.$v2->glwnco}) $arrSumPerCoa = $this->session->{'coa'.$v2->glwnco};
                endif;

                // buat variable himpunan
                foreach ($this->arrAdditional as $addit) {
                    $key_addit = 'additional_'.$addit['id'];
                    ${$key_addit} = [];
                    $coa_addit = json_decode($addit['coa'],true);
                    if(in_array($v2->glwnco, $coa_addit)):
                        ${$key_addit} = $this->session->{$key_addit};
                    endif;
                }
                if(in_array($v2->glwnco, $this->arrDanaPasvia)):
                    $dana_pasiva = [];
                    if($this->session->dana_pasiva): $dana_pasiva = $this->session->dana_pasiva; endif;
                endif;
                if(in_array($v2->glwnco, $this->arrDanaAktiva)):
                    $dana_aktiva = [];
                    if($this->session->dana_aktiva): $dana_aktiva = $this->session->dana_aktiva; endif;
                endif;

                //buat variable dpk
                if(in_array($v2->glwnco, $this->arrDpk)):
                    $arrDpkSet = [];
                    if($this->session->arrDpk): $arrDpkSet = $this->session->arrDpk; endif;
                endif;
                $item3 .= '<td class="wd-100">'.$v2->glwsbi.'</td>';
                $item3 .= '<td class="wd-100">'.$v2->glwnob.'</td>';
                $item3 .= '<td class="wd-100">'.$v2->glwnco.'</td>';
                $item3 .= '<td class="wd-230 sb-'.($kk+1).'">'.remove_spaces($v2->glwdes).'</td>';
                if($d_status2 || isset($v2->tipe)):
                    $minus = 0;
                    $status = true;
                endif;
                $arrUpdate = [];
                $arrInsert = [];
                $named = $v2->glwnco.'-'.$cabang;
                $status_update = false;
                $data_core = $this->session->data_core;
                foreach ($this->detail_tahun as $dt_bln_anggaran) {
                    $i = $dt_bln_anggaran->bulan;
                    $field  = 'B_' . sprintf("%02d", $i);

                    if(count($dt2)>0){ $val = $dt2[$i]; }
                    else{ 
                        $val = $value;
                        if($dt_bln_anggaran->singkatan == arrSumberData()['real']): // ambil dari core
                            $val = 0;
                            if(isset($data_core[$dt_bln_anggaran->tahun])):
                                $core_key = multidimensional_search($data_core[$dt_bln_anggaran->tahun], array(
                                    'glwnco' => $v2->glwnco,
                                ));
                                if(strlen($core_key)>0):
                                    $val = $data_core[$dt_bln_anggaran->tahun][$core_key][$field];
                                    $minus = $v2->kali_minus;
                                endif;
                            endif;
                        endif;
                        if(isset($v2->{$field})){ $val = $v2->{$field}; }
                    }
                    $val = round_value($val);

                    $val2 = $val;
                    $val = kali_minus($val,$minus);
                    if(isset($changed[$field]) && $changed[$field] == 1):
                        $val = $v2->{'f'.$field};
                    endif;
                    if(in_array($v2->glwnco, $this->arrAdjusment)):
                        $val = 0;
                        $arrAdjusmentSet[$field] = $val;
                    endif;
                    if(count($dt2)<=0 && $akses_ubah):
                        $name = $v2->glwnco.'-'.$cabang;
                        $bgedit = '';
                        $item3 .= '<td style="background:'.$bgedit.'"><div style="background:'.$bgedit.'" style="min-height: 10px; width: 50px; overflow: hidden;"  contenteditable="'.$contentedit.'" class="edit-value text-right '.$field.'" data-name="'.$field.'" data-id="'.$name.'" data-value="'.$val.'">'.check_value($val,true).'</div></td>';
                        // $item3 .= '<td class="text-right '.$field.'">'.check_value($val).'</td>';
                    else:
                        $item3 .= '<td class="text-right '.$field.'">'.check_value($val,true).'</td>';
                    endif;
                    

                    // sum buat arrSumPerCoa
                    if(in_array($v2->glwnco, $this->arrSumPerCoa)):
                        $arrSumPerCoa[$field] = $val;
                    endif;

                    // sum untuk coa himpunan
                    foreach ($this->arrAdditional as $addit) {
                        $key_addit = 'additional_'.$addit['id'];
                        $coa_addit = json_decode($addit['coa'],true);
                        if(in_array($v2->glwnco, $coa_addit)):
                            if(isset(${$key_addit}[$field])):
                                ${$key_addit}[$field] += $val;
                            else:
                                ${$key_addit}[$field] = $val;
                            endif;
                        endif;
                    }
                    if(in_array($v2->glwnco, $this->arrDanaPasvia)):
                        if(isset($dana_pasiva[$field])):
                            $dana_pasiva[$field] += $val;
                        else:
                            $dana_pasiva[$field] = $val;
                        endif;
                    endif;
                    if(in_array($v2->glwnco, $this->arrDanaAktiva)):
                        if(isset($dana_aktiva[$field])):
                            $dana_aktiva[$field] += $val;
                        else:
                            $dana_aktiva[$field] = $val;
                        endif;
                    endif;

                    // sum coa dpk
                    if(in_array($v2->glwnco, $this->arrDpk)):
                        if(isset($arrDpkSet[$field])):
                            $arrDpkSet[$field] += $val;
                        else:
                            $arrDpkSet[$field] = $val;
                        endif;
                    endif;
                    if(isset($dt[$i])){ $dt[$i] += $val; }else{ $dt[$i] = $val; }
                    if($v2->fID):
                        if($val != $v2->{'f'.$field}):
                            $status_update = true;
                            $arrUpdate[$field] = $val;
                        endif;
                    else:
                        $status_update = true;
                        $arrInsert[$field] = $val;
                    endif;
                }
                if($v2->fID)://pengecekan untuk insert atau update
                    if($val != $v2->{'frealisasi'}):
                        $status_update = true;
                        $arrUpdate['realisasi'] = $value;
                    endif;
                else:
                    $status_update = true;
                    $arrDataInsert['realisasi'] = $value;
                endif;

                // simpan ke session adjusment
                if(in_array($v2->glwnco, $this->arrAdjusment)):
                    $this->session->set_userdata(['coa'.$v2->glwnco => $arrAdjusmentSet]);
                endif;

                // simpan ke session arrSumPerCoa
                if(in_array($v2->glwnco, $this->arrSumPerCoa)):
                    $this->session->set_userdata(['coa'.$v2->glwnco => $arrSumPerCoa]);
                endif;

                // simpan ke session himpunan dana
                foreach ($this->arrAdditional as $addit) {
                    $key_addit = 'additional_'.$addit['id'];
                    $coa_addit = json_decode($addit['coa'],true);
                    if(in_array($v2->glwnco, $coa_addit)):
                        $this->session->set_userdata([$key_addit => ${$key_addit}]);
                    endif;
                }
                if(in_array($v2->glwnco, $this->arrDanaPasvia)):
                    $this->session->set_userdata(['dana_pasiva' => $dana_pasiva]);
                endif;
                if(in_array($v2->glwnco, $this->arrDanaAktiva)):
                    $this->session->set_userdata(['dana_aktiva' => $dana_aktiva]);
                endif;

                // simpan ke session dpk
                if(in_array($v2->glwnco, $this->arrDpk)):
                    $this->session->set_userdata(['arrDpk' => $arrDpkSet]);
                endif;


                if($status_update)://jika ada yg diinsert atau update
                    $arrUpdate['status_budget_nett'] = 0;
                    $this->update_data($arrUpdate,$v2->fID);
                    $this->insert_data($arrInsert,$v2->glwnco,$cabang);
                    $this->session->set_userdata(['status_update_neraca_nett' => 1]);
                endif;
                $item3 .= $td_transparnt;
                if(count($dt2)>0){
                    $item3 .= '<td class="text-right"></td>';
                }
                else{
                    $name = $v2->glwnco.'-'.$cabang;
                    $item3 .= '<td style="background:'.$bgedit.'"><div style="background:'.$bgedit.'" style="min-height: 10px; width: 50px; overflow: hidden;"  contenteditable="'.$contentedit.'" class="edit-value text-right" data-name="realisasi" data-id="'.$name.'" data-value="'.$value.'">'.$bln_trakhir.'</div></td>';
                }
                
                $item3 .= '</tr>';
                if($status):
                    $dt_status = true;
                    $item .= $item3;
                endif;
                $item .= $item2;
            }
        }
        $res = [
            'item'  => $item,
            'dt'    => $dt,
            'dt_status'    => $dt_status,
        ];
        return $res;
    }

    private function update_data($data,$ID){
        if(count($data)>0):
            $data['update_at'] = date("Y-m-d H:i:s");
            $data['update_by'] = user('username');
            update_data($this->table,$data,'id',$ID);
        endif;
    }
    private function insert_data($data,$coa,$cabang){
        $anggaran = $this->session->dt_anggaran;
        if(count($data)>0):
            $data['kode_anggaran'] = $anggaran->kode_anggaran;
            $data['keterangan_anggaran'] = $anggaran->keterangan;
            $data['tahun'] = $anggaran->tahun_anggaran;
            $data['coa'] = $coa;
            $data['kode_cabang'] = $cabang;

            $status = false;
            for ($i=1; $i <= 12 ; $i++) { 
                $field  = 'B_' . sprintf("%02d", $i);
                if(checkNumber($data[$field])):
                    $status = true;
                endif;
            }

            if($status):
                $ck = get_data($this->table,[
                    'select' => 'id',
                    'where' => [
                        'kode_anggaran' => $anggaran->kode_anggaran,
                        'coa'           => $coa,
                        'kode_cabang'   => $cabang
                    ]
                ])->row();
                if($ck):

                else:
                    $data['create_at'] = date("Y-m-d H:i:s");
                    $data['create_by'] = user('username');
                    insert_data($this->table,$data);
                endif;
            endif;
        endif;
    }

    function checkAdjusment($anggaran,$cabang){
        // Adjusment
        $arrAktiva      = $this->session->arrAktiva;
        $dana_aktiva    = $this->session->dana_aktiva;
        $arrPasiva      = $this->session->arrPasiva;
        $dana_pasiva    = $this->session->dana_pasiva;
        $anggaran       = $this->session->dt_anggaran;
        foreach ($this->arrAdjusment as $v) {
            ${'coa'.$v} = $this->session->{'coa'.$v};
        }
        foreach ($this->arrSumPerCoa as $v) {
            ${'coa'.$v} = $this->session->{'coa'.$v};
        }

        // Himpunan
        $data_himpunan['arrAdditional'] = $this->arrAdditional;
        $data_himpunan['anggaran']      = $anggaran;
        $data_himpunan['cabang']        = $cabang;
        foreach ($this->arrAdditional as $addit) {
            $key_addit      = 'additional_'.$addit['id'];
            $data_himpunan[$key_addit] = $this->session->{$key_addit};
        }
        $view_additional = $this->load->view($this->path.$this->controller.'/additional',$data_himpunan,true);

        for ($i=1; $i <=12 ; $i++) { 
            $field  = 'B_' . sprintf("%02d", $i);
            $dt_aktiva = $data_himpunan['additional_1'];
            $dt_pasiva = $data_himpunan['additional_2'];
            if(isset($dt_aktiva[$field])):
                $val = $dt_aktiva[$field];
                $val = round_value($val);

                if(isset($dana_aktiva[$field])):
                    $dana_aktiva[$field] += $val;
                else:
                    $dana_aktiva[$field] = $val;
                endif;
            endif;

            if(isset($dt_pasiva[$field])):
                $val = $dt_pasiva[$field];
                $val = round_value($val);

                if(isset($dana_pasiva[$field])):
                    $dana_pasiva[$field] += $val;
                else:
                    $dana_pasiva[$field] = $val;
                endif;
            endif;
        }

        $arrUpdate = [];
        $arrDpk = $this->session->arrDpk;
        $item = '<td></td>';
        $item .= '<td></td>';
        $item .= '<td>602</td>';
        $item .= '<td><strong>Total DPK</strong></td>';
        for ($i=1; $i <=12 ; $i++) { 
            $field  = 'B_' . sprintf("%02d", $i);
            $a = $arrAktiva[$field];
            $p = $arrPasiva[$field];
            if($p>$a):
                $selisih = $p-$a;
                $selisih = round_value($selisih);

                $coa1801000[$field] += $selisih;
                $arrAktiva[$field] += $selisih;
                $coa1800000[$field] += $selisih;
            elseif($a>$p):
                $selisih = $a-$p;
                $selisih = round_value($selisih);
                
                $coa2801000[$field] += $selisih;
                $arrPasiva[$field] += $selisih;
                $coa2800000[$field] += $selisih;
            endif;

            $item .= '<td class="text-right">'.check_value($arrDpk[$field],true).'</td>';

            $arrUpdate['1000000'][$field] = $arrAktiva[$field];
            $arrUpdate['2000000'][$field] = $arrPasiva[$field];
            $arrUpdate['1801000'][$field] = $coa1801000[$field];
            $arrUpdate['2801000'][$field] = $coa2801000[$field];
            $arrUpdate['1800000'][$field] = $coa1800000[$field];
            $arrUpdate['2800000'][$field] = $coa2800000[$field];
            $arrUpdate['602'][$field]     = $arrDpk[$field];

            $arrAktiva[$field]  = check_value($arrAktiva[$field],true);
            $arrPasiva[$field]  = check_value($arrPasiva[$field],true);
            $coa1801000[$field] = check_value($coa1801000[$field],true);
            $coa2801000[$field] = check_value($coa2801000[$field],true);
            $coa1800000[$field] = check_value($coa1800000[$field],true);
            $coa2800000[$field] = check_value($coa2800000[$field],true);
        }
        $item .= '<td class="border-none bg-white"></td>';
        $item .= '<td></td>';

        // Adjusment
        foreach ($arrUpdate as $coa => $data) {
            $ck = get_data($this->table,[
                'select'    => 'id',
                'where'     => "coa = '$coa' and kode_anggaran = '$anggaran->kode_anggaran' and tahun = '$anggaran->tahun_anggaran' and kode_cabang = '$cabang'"
            ])->row();
            if($ck):
                $data['status_budget_nett'] = 0;
                $this->session->set_userdata(['status_update_neraca_nett' => 1]);
                update_data($this->table,$data,'id',$ck->id);
            elseif($coa == '602'):
                $h = $data;
                $h['coa'] = $coa;
                $h['kode_cabang'] = $cabang;
                $h['kode_anggaran'] = $anggaran->kode_anggaran;
                $h['tahun'] = $anggaran->tahun_anggaran;
                $h['keterangan_anggaran'] = $anggaran->keterangan;
                insert_data($this->table,$h);
            endif;
        }

        $spaces = '<td><div style="color:#7fffd400">-</div></td>';
        for ($i=1; $i <=15 ; $i++) { $spaces .= '<td></td>'; }
        $spaces .= '<td class="border-none bg-white"></td>';
        $spaces .= '<td></td>';

        $res = array(
            '1000000' => $arrAktiva,
            '2000000' => $arrPasiva,
            '1801000' => ${'coa1801000'},
            '2801000' => ${'coa2801000'},
            '1800000' => ${'coa1800000'},
            '2800000' => ${'coa2800000'},
            'd-total-pasiva'     => $item,
            'd-spaces' => $spaces,
            'append_table' => $view_additional
        );

        // crone job budget nett
        if($this->session->status_update_neraca_nett == 1):
            // $this->db->query("call stored_budget_nett('neraca','".$cabang."','".$anggaran->kode_anggaran."')");
            // if($this->db->error()):

            // endif;
        endif;

        $this->session->unset_userdata(['dt_neraca','dt_anggaran','arrAktiva','arrPasiva','coa1801000','coa2801000','arrDpk','dana_pasiva','dana_aktiva','status_update_neraca_nett']);
        foreach ($this->arrAdditional as $addit) {
            $key_addit      = 'additional_'.$addit['id'];
            $this->session->unset_userdata([$key_addit]);
        }
        foreach ($this->arrSumPerCoa as $x) {
            $this->session->unset_userdata(['coa'.$x]);
        }

        render($res,'json');
    }

    function save_perubahan(){
        $kode_anggaran = post('kode_anggaran');
        $anggaran = $this->anggaran;

        // pengecekan save untuk cabang
        check_save_cabang($this->controller,$anggaran->kode_anggaran,$kode_anggaran,'access_edit');

        $data   = json_decode(post('json'),true);
        foreach($data as $k => $record) {
            $x      = explode('-', $k);
            $coa    = $x[0];
            $cabang = $x[1];

            $ck = get_data($this->table,[
                'select'    => 'id,changed',
                'where'     => "coa = '$coa' and kode_cabang = '$cabang' and  kode_anggaran = '$kode_anggaran' and tahun = '$anggaran->tahun_anggaran'",
            ])->row();
            if($ck):
                $changed = json_decode($ck->changed,true);
                foreach ($record as $k2 => $v2) {
                    $value = filter_money($v2);
                    $changed[$k2] = 1;
                    $record[$k2] = insert_view_report($value);
                }
                $record['changed'] = json_encode($changed);
                $where = [
                    'coa'           => $coa,
                    'tahun'         => $anggaran->tahun_anggaran,
                    'kode_cabang'   => $cabang,
                    'kode_anggaran' => $kode_anggaran,
                ];
                update_data($this->table,$record,$where);
            endif;
        }

        render([
            'status' => true,
            'message' => lang('data_berhasil_diperbaharui')
        ],'json');
    }

    function save_nett($kode_anggaran,$kode_cabang,$check=true){
        $access = get_access($this->controller);
        if(!$access['access_edit'] or !$access['access_additional']):
            render(['status' => 'failed', 'message' => lang('izin_ditolak')],'json');exit();
        endif;
        $check = filter_var($check, FILTER_VALIDATE_BOOLEAN);

        if(!$check):
            $this->db->query("call stored_budget_nett('neraca','".$kode_cabang."','".$kode_anggaran."')");

            // save status to budget nett
            save_status_budget_nett($kode_anggaran,$kode_cabang,'tbl_history_to_budget_nett_neraca');

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
                $select .= " ifnull(a.$field,0) as n_$field,";
                $select .= " ifnull(b.$field,0) as b_$field,";
            }
        else:
            $field  = 'B_' . sprintf("%02d", 12);
            $select .= " ifnull(a.$field,0) as n_$field,";
            $select .= " ifnull(b.$field,0) as b_$field,";
        endif;
        $ck = get_data('tbl_budget_plan_neraca a',[
            'select' => 'c.glwnco,c.glwdes,'.$select,
            'join' => [
                "tbl_budget_nett_neraca b on b.coa = a.coa and b.kode_anggaran = a.kode_anggaran and b.kode_cabang = a.kode_cabang and b.coa != '' type left",
                "tbl_m_coa c on c.glwnco = a.coa and c.kode_anggaran = a.kode_anggaran and c.glwnco != '' type left"
            ],
            'where' => [
                'a.kode_cabang'   => $kode_cabang,
                'a.kode_anggaran' => $kode_anggaran,
                'a.coa' => '1000000'
            ] 
        ])->row();
        if(!$ck):
            render(['status' => false, 'message' => lang('data_not_found')],'json');
        endif;
        return $ck;
    }
}