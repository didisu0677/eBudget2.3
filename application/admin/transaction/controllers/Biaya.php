<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Biaya extends BE_Controller {
    var $path = 'transaction/budget_planner/';
    var $controller = 'biaya';
    var $detail_tahun;
    var $kode_anggaran;
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
    
    function index($p1="") { 
        $access         = get_access($this->controller);
        $data = data_cabang('biaya');
        $data['detail_tahun'] = $this->detail_tahun;
        $data['path'] = $this->path;
        $data['access_additional']  = $access['access_additional'];
        render($data,'view:'.$this->path.'biaya/index');
    }

    function data ($anggaran1="", $cabang=""){
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$anggaran1)->row();


        $data_finish['kode_anggaran']   = $anggaran1;
        $data_finish['kode_cabang']     = $cabang;
        $access = get_access($this->controller,$data_finish);
        $access_edit = false;
        if($access['access_edit'] && $cabang == user('kode_cabang')):
            $access_edit = true;
        elseif($access['access_edit'] && $access['access_additional']):
            $access_edit = true;
        endif;

        // pengecekan akses cabang
        check_access_cabang($this->controller,$anggaran1,$cabang,$access);

        $bln_trakhir = $anggaran->bulan_terakhir_realisasi;
        $getMinBulan = $anggaran->bulan_terakhir_realisasi - 1;
        $thn_trakhir = $anggaran->tahun_terakhir_realisasi;
        $tbl_history = 'tbl_history_'.$anggaran->tahun_terakhir_realisasi;

        $coa_file    = get_data('tbl_m_coa_biaya_file',[
            'where' => [
                'is_active' => 1,
                'kode_anggaran' => $anggaran->kode_anggaran
            ]
        ])->result();
        $arr_coa_file= [];
        foreach($coa_file as $v){
            if(!in_array($v->coa,$arr_coa_file)) array_push($arr_coa_file,$v->coa);
        }

        $or_neraca  = "(a.glwnco like '557%')";
        $select     = "level1,level2,level3,level4,level5,
                    a.glwsbi,a.glwnob,a.glwcoa,a.glwnco,a.glwdes,a.kali_minus";

        $select2    = "coalesce(sum(case when b.bulan = '".$bln_trakhir."' then b.TOT_".$cabang." end), 0) as hasil, coalesce(sum(case when b.bulan = '".$getMinBulan."'  then b.TOT_".$cabang." end), 0) as hasil2";

        $select3    = "coalesce(sum(case when b.bulan = '1' then b.TOT_".$cabang." end), 0) as core1,
                    coalesce(sum(case when b.bulan = '2' then b.TOT_".$cabang." end), 0) as core2,
                    coalesce(sum(case when b.bulan = '3' then b.TOT_".$cabang." end), 0) as core3,
                    coalesce(sum(case when b.bulan = '4' then b.TOT_".$cabang." end), 0) as core4,
                    coalesce(sum(case when b.bulan = '5' then b.TOT_".$cabang." end), 0) as core5,
                    coalesce(sum(case when b.bulan = '6' then b.TOT_".$cabang." end), 0) as core6,
                    coalesce(sum(case when b.bulan = '7' then b.TOT_".$cabang." end), 0) as core7,
                    coalesce(sum(case when b.bulan = '8' then b.TOT_".$cabang." end), 0) as core8,
                    coalesce(sum(case when b.bulan = '9' then b.TOT_".$cabang." end), 0) as core9,
                    coalesce(sum(case when b.bulan = '10' then b.TOT_".$cabang." end), 0) as core10,
                    coalesce(sum(case when b.bulan = '11' then b.TOT_".$cabang." end), 0) as core11,
                    coalesce(sum(case when b.bulan = '12' then b.TOT_".$cabang." end), 0) as core12,
                    coalesce(b.glwnco,0) as coaa,
                    ";

        // MW 20210426
        // sebelum diubah terdapat b.bulan not in(0) di where
        $coa = get_data('tbl_m_coa a',[
            'select' => 'd.*, '.$select.',b.TOT_'.$cabang.', c.*, '.$select2.','.$select3,
            'order_by' => 'a.urutan',
            'join' => [
                "$tbl_history b on and a.glwnco = b.glwnco and b.glwnco != '' type left",
                "tbl_indek_besaran_biaya c on c.coa = a.glwnco and c.kode_anggaran = '$anggaran1' type left",
                "tbl_biaya d on d.glwnco = a.glwnco and d.kode_anggaran = '$anggaran1' and d.kode_cabang = '".$cabang."' type left"
            ],
            'where'     => "a.kode_anggaran = '".$anggaran->kode_anggaran."' and a.is_active = '1' and a.glwnco like '557%' group by a.glwnco",
        ])->result();
        // print_r($this->db->last_query());
        $coa = $this->get_coa($coa);

        $data['coa']    = $coa['coa'];
        $data['detail'] = $coa['detail'];
        $data['cabang'] = $cabang;
        $data['bulan_terakhir'] = $bln_trakhir;
        $data['detail_tahun'] = $this->detail_tahun;
        $data['akses_ubah'] = $access_edit;
        $data['anggaran'] = $anggaran;
        $data['arr_coa_file'] = $arr_coa_file;


        $selectB     = "level1,level2,level3,level4,level5,
                    a.glwsbi,a.glwnob,a.glwcoa,a.glwnco,a.glwdes,a.kali_minus";

        $select2B    = "coalesce(sum(case when b.bulan = '".$bln_trakhir."' then b.TOT_".$cabang." end), 0) as hasil, coalesce(sum(case when b.bulan = '".$getMinBulan."'  then b.TOT_".$cabang." end), 0) as hasil2";

        $coaB = get_data('tbl_m_coa a',[
            'select' => 'd.*,'.$selectB.',b.TOT_'.$cabang.', c.*, '.$select2B.','.$select3,
            'order_by' => 'a.urutan',
            'join' => [
                "$tbl_history b on and a.glwnco = b.glwnco and b.glwnco != '' type left",
                "tbl_indek_besaran_biaya c on c.coa = a.glwnco and c.kode_anggaran = '$anggaran1' type left",
                "tbl_biaya d on d.glwnco = a.glwnco and d.kode_anggaran = '$anggaran1' and d.kode_cabang = '".$cabang."' type left"],
             'where'     => "a.kode_anggaran = '".$anggaran->kode_anggaran."' and a.is_active = '1' and a.glwnco like '567%' group by a.glwnco",
        ])->result();
        $coaB = $this->get_coa($coaB);

        $dataB['coa']    = $coaB['coa'];
        $dataB['detail'] = $coaB['detail'];
        $dataB['cabang'] = $cabang;
        $dataB['bulan_terakhir'] = $bln_trakhir;
        $dataB['detail_tahun'] = $this->detail_tahun;
        $dataB['anggaran'] = $anggaran;
        $dataB['arr_coa_file'] = $arr_coa_file;

        $dataB['akses_ubah'] = $access_edit;


        $selectC     = "level1,level2,level3,level4,level5,
                    a.glwsbi,a.glwnob,a.glwcoa,a.glwnco,a.glwdes,a.kali_minus";

        $select2C    = "coalesce(sum(case when b.bulan = '".$bln_trakhir."' then b.TOT_".$cabang." end), 0) as hasil, coalesce(sum(case when b.bulan = '".$getMinBulan."'  then b.TOT_".$cabang." end), 0) as hasil2";


        $coaC = get_data('tbl_m_coa a',[
            'select' => 'd.*,'.$selectC.',b.TOT_'.$cabang.', c.*, '.$select2C.','.$select3,
            'order_by' => 'a.urutan',
            'join' => [
                "$tbl_history b on and a.glwnco = b.glwnco and b.glwnco != '' type left",
                "tbl_indek_besaran_biaya c on c.coa = a.glwnco and c.kode_anggaran = '$anggaran1' type left",
                "tbl_biaya d on d.glwnco = a.glwnco and d.kode_anggaran = '$anggaran1' and d.kode_cabang = '".$cabang."' type left"],
             'where'     => " a.kode_anggaran = '".$anggaran->kode_anggaran."' and a.is_active = '1' and a.glwnco like '568%' group by a.glwnco",
        ])->result();
        $coaC = $this->get_coa($coaC);

        $dataC['coa']    = $coaC['coa'];
        $dataC['detail'] = $coaC['detail'];
        $dataC['cabang'] = $cabang;
        $dataC['bulan_terakhir'] = $bln_trakhir;
        $dataC['akses_ubah'] = $access_edit;
        $dataC['detail_tahun'] = $this->detail_tahun;
        $dataC['anggaran'] = $anggaran;
        $dataC['arr_coa_file'] = $arr_coa_file;


        $selectD     = "level1,level2,level3,level4,level5,
                    a.glwsbi,a.glwnob,a.glwcoa,a.glwnco,a.glwdes,a.kali_minus";

        $select2D    = "coalesce(sum(case when b.bulan = '".$bln_trakhir."' then b.TOT_".$cabang." end), 0) as hasil, coalesce(sum(case when b.bulan = '".$getMinBulan."'  then b.TOT_".$cabang." end), 0) as hasil2";

        $coaD = get_data('tbl_m_coa a',[
            'select' => 'd.*,'.$selectD.',b.TOT_'.$cabang.', c.*, '.$select2D.','.$select3,
            'order_by' => 'a.urutan',
            'join' => [
                "$tbl_history b on and a.glwnco = b.glwnco and b.glwnco != '' type left",
                "tbl_indek_besaran_biaya c on c.coa = a.glwnco and c.kode_anggaran = '$anggaran1' type left",
                "tbl_biaya d on d.glwnco = a.glwnco and d.kode_anggaran = '$anggaran1' and d.kode_cabang = '".$cabang."' type left"],
             'where'     => "a.kode_anggaran = '".$anggaran->kode_anggaran."' and a.glwnco like '57%' group by a.glwnco",
        ])->result();
        $coaD = $this->get_coa($coaD);

        $dataD['promosi'] = get_data('tbl_biaya_promosi',[
            'where' => "kode_anggaran = '".$anggaran1."' and kode_cabang = '".$cabang."'"
        ])->result_array();

        $selectSum = "";
        for($a=1;$a<=12;$a++){
            $selectSum .= "sum(bulan_".$a.") as bulan_b".$a.",";
        }

        $dataD['sumPromosi'] = get_data('tbl_biaya_promosi',[
            'select' => $selectSum,
            'where' => "kode_anggaran = '".$anggaran1."' and kode_cabang = '".$cabang."' group by kode_cabang"
        ])->result_array();

        $dataD['coa']    = $coaD['coa'];
        $dataD['detail'] = $coaD['detail'];
        $dataD['cabang'] = $cabang;
        $dataD['bulan_terakhir'] = $bln_trakhir;
        $dataD['akses_ubah'] = $access_edit;
        $dataD['anggaran'] = $anggaran;
        $dataD['arr_coa_file'] = $arr_coa_file;


         $selectE     = "level1,level2,level3,level4,level5,
                    a.glwsbi,a.glwnob,a.glwcoa,a.glwnco,a.glwdes,a.kali_minus";

        $select2E    = "coalesce(sum(case when b.bulan = '".$bln_trakhir."' then b.TOT_".$cabang." end), 0) as hasil, coalesce(sum(case when b.bulan = '".$getMinBulan."'  then b.TOT_".$cabang." end), 0) as hasil2";

        $coaE = get_data('tbl_m_coa a',[
            'select' => 'd.*, '.$selectE.',b.TOT_'.$cabang.', c.*, '.$select2E.','.$select3,
            'order_by' => 'a.urutan',
            'join' => [
                "$tbl_history b on and a.glwnco = b.glwnco and b.glwnco != '' type left",
                "tbl_indek_besaran_biaya c on c.coa = a.glwnco and c.kode_anggaran = '$anggaran1' type left",
                "tbl_biaya d on d.glwnco = a.glwnco and d.kode_anggaran = '$anggaran1' and d.kode_cabang = '".$cabang."' type left"],
             'where'     => "a.kode_anggaran = '".$anggaran->kode_anggaran."' and a.is_active = '1' and a.glwnco like '580%'",
             'group_by' => 'a.glwnco'
        ])->result();
        $coaE = $this->get_coa($coaE);

        $coaE_ket = get_data('tbl_m_coa',[
            'where' => [
                'kode_anggaran' => $anggaran->kode_anggaran,
                'glwnco'        => ['5800000'],
                'is_active'     => 1
            ]
        ])->row();

        $dataE['coa']    = $coaE['coa'];
        $dataE['detail'] = $coaE['detail'];
        $dataE['cabang'] = $cabang;
        $dataE['bulan_terakhir'] = $bln_trakhir;
        $dataE['akses_ubah'] = $access_edit;
        $dataE['anggaran'] = $anggaran;
        $dataE['arr_coa_file'] = $arr_coa_file;
        $dataE['coa_ket'] = $coaE_ket;


        $ls_coa_keterangan = get_data('tbl_m_biaya_coa_keterangan',[
            'select' => 'coa,keterangan',
            'where'  => [
                'is_active'         => 1,
                'kode_anggaran'     => $anggaran->kode_anggaran
            ]
        ])->result_array();
        $arr_coa_core = [];
        foreach($ls_coa_keterangan as $v){
            if(!in_array($v['coa'],$arr_coa_core)):
                array_push($arr_coa_core,$v['coa']);
            endif;
        }
        $history_core = [];
        if(count($arr_coa_core)>0):
            $history_core = get_data_core($arr_coa_core,[($anggaran->tahun_anggaran-1)],'TOT_'.$cabang);
            $history_core = $history_core[($anggaran->tahun_anggaran-1)];
        endif;

        $data['coa_keterangan']     = $ls_coa_keterangan;
        $data['access_additional']  = $access['access_additional'];
        $data['history_core']       = $history_core;
        $dataB['coa_keterangan']    = $ls_coa_keterangan;
        $dataB['access_additional'] = $access['access_additional'];
        $dataB['history_core']      = $history_core;
        $dataC['coa_keterangan']    = $ls_coa_keterangan;
        $dataC['access_additional'] = $access['access_additional'];
        $dataC['history_core']      = $history_core;
        $dataD['coa_keterangan']    = $ls_coa_keterangan;
        $dataD['access_additional'] = $access['access_additional'];
        $dataD['history_core']      = $history_core;
        $dataE['coa_keterangan']    = $ls_coa_keterangan;
        $dataE['access_additional'] = $access['access_additional'];
        $dataE['history_core']      = $history_core;

        $view = '';

        $view .= $this->load->view($this->path.'biaya/tableA',$data,true);

        $dataB['group'] = 1;
        $view .= $this->load->view($this->path.'biaya/tableB',$dataB,true);
        $dataC['group'] = 1;
        $view .= $this->load->view($this->path.'biaya/tableB',$dataC,true);

        $dataD['group'] = 1;
        $view .= $this->load->view($this->path.'biaya/tableB',$dataD,true);
        $dataE['group'] = 0;
        $view .= $this->load->view($this->path.'biaya/tableB',$dataE,true);

        $response   = array(
            'status'        => true,
            'table'         => $view,
            'access_edit'   => $access_edit,
            'bg_edited2'    => bgEdit()
        );
        render($response,'json');
    }


    private function get_coa($coa){
        $data = [];
        foreach ($coa as $k => $v) {
            // level 0
            if($v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
                $data['coa'][] = $v;
            endif;

            // level 1
            // if($v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
            //     $data['detail']['1'][$v->level1][] = $v;
            // endif;

            // level 2
            if(!$v->level1 && $v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
                $data['detail']['1'][$v->level2][] = $v;
            endif;

            // level 3
            if(!$v->level1 && !$v->level2 && $v->level3 && !$v->level4 && !$v->level5):
                $data['detail']['2'][$v->level3][] = $v;
            endif;

            // level 4
            if(!$v->level1 && !$v->level2 && !$v->level3 && $v->level4 && !$v->level5):
                $data['detail']['3'][$v->level4][] = $v;
            endif;

            // level 5
            // if(!$v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && $v->level5):
            //     $data['detail']['5'][$v->level5][] = $v;
            // endif;
        }
        return $data;
    }


    // function save_perubahan($anggaran="",$cabang="") {       

    //     $data   = json_decode(post('json'),true);

    //     // echo post('json');
    //     foreach($data as $getId => $record) {
    //         $cekId = $getId;

    //         $record = insert_view_report_arr($record);
    //         // echo $id." - ".$cekId[1]."<br>";
    //         $cek  = get_data('tbl_biaya a',[
    //             'select'    => 'a.id',
    //             'where'     => [
    //                 'a.glwnco'             => $cekId,
    //                 'a.kode_anggaran'   => $anggaran,
    //                 'a.kode_cabang'   => $cabang,
    //             ]
    //         ])->result_array();
     
    //         if(count($cek) > 0){
    //             update_data('tbl_biaya', $record,'id',$cek[0]['id']);
    //         }else {
    //                 $record['glwnco'] = $cekId;
    //                 $record['kode_anggaran'] = $anggaran;
    //                 $record['kode_cabang'] = $cabang;
    //                 insert_data('tbl_biaya',$record);
    //         } 
    //      } 
    // }


    function save_perubahan($anggaran="",$cabang="") {       

        // pengecekan save untuk cabang
        check_save_cabang($this->controller,$anggaran,$cabang,'access_edit');

        $data   = json_decode(post('json'),true);

        foreach($data['bulan'] as $getId => $record) {
            $cekId = $getId;

            $cekExp = explode("|", $getId);
            $cekId = $cekExp[0];


            $dataRecord = insert_view_report_arr($record);
            $dataRecord ['last_edit'] = '1';
            $cek  = get_data('tbl_biaya a',[
                'select'    => 'a.id,cabang_edit',
                'where'     => [
                    'a.glwnco'             => $cekId,
                    'a.kode_anggaran'   => $anggaran,
                    'a.kode_cabang'   => $cabang,
                ]
            ])->result_array();

            $cabang_edit = [];
            if(count($cek) > 0){
                $cabang_edit = json_decode($cek[0]['cabang_edit']);
            }

            foreach($dataRecord as $k => $v){
                if(!in_array($k,$cabang_edit)) array_push($cabang_edit,$k);
            }
            $dataRecord['cabang_edit'] = json_encode($cabang_edit);
     
            if(count($cek) > 0){
                  update_data('tbl_biaya', $dataRecord,'id',$cek[0]['id']);
            }else {
                    // echo $cekId."<br>";
                    // echo $anggaran."<br>";
                    // echo $cabang."<br>";
                    $dataRecord['glwnco'] = $cekId;
                    $dataRecord['kode_anggaran'] = $anggaran;
                    $dataRecord['kode_cabang'] = $cabang;
                    insert_data('tbl_biaya',$dataRecord);
            } 
         } 
         if(!empty($data['perbulan'])){

                // print_r($data['perbulan']);
                foreach($data['perbulan'] as $getId => $record) {
                    $cekId = $getId;

                    $cekExp = explode("|", $getId);
                    $cekId = $cekExp[0];

                    
                    $dataRecord  = insert_view_report_arr($record);

                    for($a=1;$a<=12;$a++){
                        $dataRecord['bulan_b'.$a] = $dataRecord['biaya_bulan'];
                    }

                    $cek  = get_data('tbl_biaya a',[
                        'select'    => 'a.id',
                        'where'     => [
                            'a.glwnco'             => $cekId,
                            'a.kode_anggaran'   => $anggaran,
                            'a.kode_cabang'   => $cabang,
                        ]
                    ])->result_array();
             
                    if(count($cek) > 0){
                        $dataRecord['last_edit'] = '2';
                        update_data('tbl_biaya', $dataRecord,'id',$cek[0]['id']);
                    }else {
                            // echo $cekId."<br>";
                            // echo $anggaran."<br>";
                            // echo $cabang."<br>";
                            $dataRecord['last_edit'] = '2';
                            $dataRecord['glwnco'] = $cekId;
                            $dataRecord['kode_anggaran'] = $anggaran;
                            $dataRecord['kode_cabang'] = $cabang;
                            insert_data('tbl_biaya',$dataRecord);
                    } 
                 } 
             }
       
    }

    function save_promosi($anggaran="",$cabang="") {       
        // pengecekan save untuk cabang
        check_save_cabang($this->controller,$anggaran,$cabang,'access_edit');

        $data   = json_decode(post('json'),true);

        // echo post('json');
        foreach($data as $getId => $record) {

            // if($record['keterangan'])
            // $record = insert_view_report_arr($record);
            // echo $id." - ".$cekId[1]."<br>";
            $cek  = get_data('tbl_biaya_promosi a',[
                'select'    => 'a.id',
                'where'     => [
                    'a.kode_anggaran'   => $anggaran,
                    'a.kode_cabang'   => $cabang,
                    'a.no'  => $getId,
                ]
            ])->result_array();
     
            if(count($cek) > 0){
                update_data('tbl_biaya_promosi', $record,'id',$cek[0]['id']);
            }else {
                    $record['kode_anggaran'] = $anggaran;
                    $record['kode_cabang'] = $cabang;
                    $record['no'] = $getId;
                    insert_data('tbl_biaya_promosi',$record);
            } 
         } 
    }

    private function get_list_coa($coa){
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

    function file_view(){
        $coa            = post('coa');
        $kode_cabang    = post('kode_cabang');
        $kode_anggaran  = post('kode_anggaran');

        $dt_coa = get_data('tbl_m_coa',[
            'where' => [
                'glwnco' => $coa,
                'kode_anggaran' => $kode_anggaran
            ]
        ])->row();

        $data_finish['kode_anggaran']   = $kode_anggaran;
        $data_finish['kode_cabang']     = $kode_cabang;
        $access = get_access($this->controller,$data_finish);
        $access_edit = false;
        if($access['access_edit'] && $kode_cabang == user('kode_cabang')):
            $access_edit = true;
        elseif($access['access_edit'] && $access['access_additional']):
            $access_edit = true;
        endif;
        // pengecekan akses cabang
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$access);

        $list = get_data('tbl_biaya_file',[
            'select' => 'nama,id,file',
            'where'  => [
                'coa' => $coa,
                'kode_anggaran' => $kode_anggaran,
                'kode_cabang'   => $kode_cabang,
            ]
        ])->row();
        if($list):
            $list->file = json_decode($list->file);
        endif;

        render([
            'status'=> true,
            'title' => $coa.' - '.remove_spaces($dt_coa->glwdes),
            'list'  => $list,
            'access_edit'  => $access_edit,
        ],'json');
    }

    function save_file(){
        $data = post();
        $kode_anggaran  = post('kode_anggaran');
        $kode_cabang    = post('kode_cabang');
        // pengecekan save untuk cabang
        check_save_cabang($this->controller,$kode_anggaran,$kode_cabang,'access_edit');

        $last_file = [];
        if($data['id']) {
            $dt = get_data('tbl_biaya_file','id',$data['id'])->row();
            if(isset($dt->id)) {
                if($dt->file != '') {
                    $lf     = json_decode($dt->file,true);
                    foreach($lf as $l) {
                        $last_file[$l] = $l;
                    }
                }
            }
        }

        $file                       = post('file');
        $keterangan_file            = post('keterangan_file');
        $filename                   = [];
        $dir                        = '';

        if(isset($file) && is_array($file)) {
            foreach($file as $k => $f) {
                $key = $k.'--';
                if(strpos($f,'exist:') !== false) {
                    $orig_file = str_replace('exist:','',$f);
                    if(isset($last_file[$orig_file])) {
                        unset($last_file[$orig_file]);
                        $filename[$key.$keterangan_file[$k]] = $orig_file;
                    }
                } else {
                    if(file_exists($f)) {
                        if(@copy($f, FCPATH . 'assets/uploads/biaya_file/'.basename($f))) {
                            $filename[$key.$keterangan_file[$k]] = basename($f);
                            if(!$dir) $dir = str_replace(basename($f),'',$f);
                        }
                    }
                }
            }
        }

        if($dir) {
            delete_dir(FCPATH . $dir);
        }
        foreach($last_file as $lf) {
            @unlink(FCPATH . 'assets/uploads/biaya_file/' . $lf);
        }

        $data['file'] = json_encode($filename);

        $response = save_data('tbl_biaya_file',$data,post(':validation'),true);

        render($response,'json');
    }

    function keterangan_view(){
        $kode_cabang    = post('kode_cabang');
        $kode_anggaran  = post('kode_anggaran');

        $data_finish['kode_anggaran']   = $kode_anggaran;
        $data_finish['kode_cabang']     = $kode_cabang;
        $access = get_access($this->controller,$data_finish);
        $access_edit = false;
        if($access['access_edit'] && $kode_cabang == user('kode_cabang')):
            $access_edit = true;
        elseif($access['access_edit'] && $access['access_additional']):
            $access_edit = true;
        endif;
        // pengecekan akses cabang
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$access);

        $dt_keterangan = get_data('tbl_biaya_keterangan',[
            'select' => 'keterangan,id',
            'where'  => [
                'kode_anggaran' => $kode_anggaran,
                'kode_cabang'   => $kode_cabang,
            ]
        ])->row();
        $keterangan = '';
        $id         = '';
        if($dt_keterangan):
            $id         = $dt_keterangan->id;
            $keterangan = $dt_keterangan->keterangan;
        endif;

        render([
            'status'=> true,
            'id'    => $id,
            'keterangan' => $keterangan,
            'access_edit'  => $access_edit,
        ],'json');
    }

    function save_keterangan(){
        $kode_anggaran  = post('kode_anggaran');
        $kode_cabang    = post('kode_cabang');
        $keterangan     = post('keterangan');
        // pengecekan save untuk cabang
        check_save_cabang($this->controller,$kode_anggaran,$kode_cabang,'access_edit');

        $ck = get_data('tbl_biaya_keterangan',[
            'where' => [
                'kode_cabang'   => $kode_cabang,
                'kode_anggaran' => $kode_anggaran
            ]
        ])->row();

        $data = post();
        $data['id'] = '';
        if($ck):
            $data['id'] = $ck->id;
        endif;
        $response = save_data('tbl_biaya_keterangan',$data,post(':validation'),true);
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
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$access);

        $dt = json_decode(post('data'),true);

        $header = $dt['#result1']['header'][0];


        $data = [];
        foreach(['#result1'] as $name){
            if(isset($dt[$name])):
                $count2 = 0;
                foreach($dt[$name]['data'] as $k => $v){
                    $count2 = count($v);
                    if($count2>10):
                        $detail = [
                            $v[0],
                            $v[1],
                            $v[2],
                        ];
                        if($count2<16):
                            for ($i=3; $i < $count2 ; $i++) { 
                                $detail[] = filter_money($v[$i]);
                            }
                            for ($i=1; $i <= 7 ; $i++) { 
                                $detail[] = '';
                            }
                            $data[] = $detail;
                        else:
                            for ($i=3; $i < (count($v)-7) ; $i++) { 
                                $detail[] = filter_money($v[$i]);
                            }
                            $detail[] = '';
                            $detail[] = filter_money($v[($count2-6)]);
                            $detail[] = filter_money($v[($count2-5)]);
                            $detail[] = '';
                            $detail[] = filter_money($v[($count2-3)]);
                            $detail[] = filter_money($v[($count2-2)]);
                            $detail[] = filter_money($v[($count2-1)]);
                            $data[] = $detail;
                        endif;
                    else:
                        $detail = [];
                        for ($i=1; $i <= 22 ; $i++) { 
                            $detail[] = '';
                        }
                        $data[] = $detail;
                    endif;
                }
            endif;
        }

        $config[] = [
            'title' => 'Biaya'.' ('.get_view_report().')',
            'header' => $header,
            'data'  => $data,
        ];

        // kebijakan aasumsi
        if(isset($dt['.d-kebijakan-fungsi']['header'][0])):
            $header = $dt['.d-kebijakan-fungsi']['header'][0];
            $data = [];
            foreach(['.d-kebijakan-fungsi'] as $name){
                if(isset($dt[$name])):
                    $count2 = 0;
                    foreach($dt[$name]['data'] as $k => $v){
                        $count2 = count($v);
                        if($count2<2):
                            $detail = [
                                $v[0],
                            ];
                            for ($i=1; $i <= 15 ; $i++) { 
                                $detail[] = '';
                            }
                            $data[] = $detail;
                        else:
                            $detail = [
                                $v[0],
                                $v[1],
                                $v[2],
                                $v[3],
                            ];
                            for ($i=4; $i < $count2 ; $i++) { 
                                $detail[] = filter_money($v[$i]);
                            }
                            $data[] = $detail;
                        endif;
                    }
                endif;
            }
            $config[] = [
                'title' => 'KEBIJAKAN FUNGSI KANTOR PUSAT',
                'header' => $header,
                'data'  => $data,
            ];
        endif;
        // render($config,'json'); exit();


        $this->load->library('simpleexcel',$config);
        $filename = str_replace(' ','_',lang('biaya_kebijakan_fungsi_kantor_pusat')).'_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }

    function data2($kode_anggaran,$kode_cabang){
        $data_finish['kode_anggaran']   = $kode_anggaran;
        $data_finish['kode_cabang']     = $kode_cabang;
        $access = get_access($this->controller,$data_finish);
        $access_edit = false;
        if($access['access_edit'] && $kode_cabang == user('kode_cabang')):
            $access_edit = true;
        elseif($access['access_edit'] && $access['access_additional']):
            $access_edit = true;
        endif;

        $anggaran   = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
        $cabang     = get_data('tbl_m_cabang',[
            'where' => [
                'kode_cabang'   => $kode_cabang,
                'kode_anggaran' => $kode_anggaran
            ]
        ])->row();

        // pengecekan akses cabang
        if(!$anggaran):
            render(['status' => false,'message' => 'anggaran not found'],'json');exit();
        elseif(!$cabang):
            render(['status' => false,'message' => 'anggaran not found'],'json');exit();
        endif;
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$access);

        if($access_edit):
            $this->create_kebijakan_asumsi($anggaran,$cabang);
        endif;

        $select = '';
        for ($i=1; $i <= 12 ; $i++) { 
            $field  = 'B_' . sprintf("%02d", $i);
            $select .= 'a.'.$field.',';
        }

        $list = get_data('tbl_biaya_kebijakan_asumsi a',[
            'select' => $select.'
                a.coa,
                b.uraian,
                b.id_kebijakan_fungsi,
                c.nama as nama_kebijakan,
                d.nama_cabang as nama_div,
                d.kode_cabang as kode_div,
                e.glwdes,
            ',
            'join' => [
                'tbl_kebijakan_asumsi b on b.id = a.id_kebijakan_asumsi',
                'tbl_kebijakan_fungsi c on c.id = b.id_kebijakan_fungsi',
                'tbl_m_cabang d on d.kode_cabang = b.kode_cabang and d.kode_anggaran = a.kode_anggaran',
                'tbl_m_coa e on e.glwnco = a.coa and e.kode_anggaran = a.kode_anggaran',
            ],
            'where' => [
                'a.kode_anggaran' => $anggaran->kode_anggaran,
                'a.kode_cabang' => $cabang->kode_cabang,
            ],
            'order_by' => 'c.id,d.urutan,b.id'
        ])->result();

        $data = [];
        $data['list'] = $list;
        $view  = $this->load->view($this->path.'biaya/kebijakan_asumsi',$data,true);

        render([
            'status' => true,
            'view' => $view,
        ],'json');
    }

    private function create_kebijakan_asumsi($anggaran,$cabang){
        $arr_type = "(a.type_cabang like '%".'"all"'."%'";
        if(in_array(strtolower($cabang->struktur_cabang),[strtolower('Cabang Induk')])):
            $arr_type .= " or a.type_cabang like '%".'"kc"'."%'";
        elseif(in_array(strtolower($cabang->struktur_cabang),[strtolower('Cabang Pembantu')])):
            $arr_type .= " or a.type_cabang like '%".'"kcp"'."%'";
        endif;
        $arr_type .= " or a.type_cabang like '%".'"'.$cabang->kode_cabang.'"'."%')";

        $list = get_data('tbl_kebijakan_asumsi a',[
            'where' => "a.kode_anggaran = '".$anggaran->kode_anggaran."' and coa != '' and ".$arr_type,
            'sort'  => 'DESC',
            'sort_by' => 'a.id',
        ])->result();
        $arrID = [];
        foreach ($list as $k => $v) {
            $where = [
                'kode_anggaran' => $anggaran->kode_anggaran,
                'kode_cabang'   => $cabang->kode_cabang,
                'id_kebijakan_asumsi' => $v->id,
            ];
            $ck = get_data('tbl_biaya_kebijakan_asumsi',[
                'select' => 'id',
                'where' => $where,
            ])->row();
            $data_save = $where;
            $data_save['coa'] = $v->coa;
            for ($i=1; $i <= 12 ; $i++) { 
                $field = 'B_' . sprintf("%02d", $i);
                $data_save[$field] = $v->{$field};
            }
            $data_save['id'] = '';
            if($ck):
                $data_save['id'] = $ck->id;
            endif;
            $res = save_data('tbl_biaya_kebijakan_asumsi',$data_save,[],true);
            if(isset($res['id'])) $arrID[] = $res['id'];
        }
        if(count($arrID)>0):
            delete_data('tbl_biaya_kebijakan_asumsi',[
                'kode_cabang'   => $cabang->kode_cabang,
                'kode_anggaran' => $anggaran->kode_anggaran,
                'id not' => $arrID
            ]);
        else:
            delete_data('tbl_biaya_kebijakan_asumsi',[
                'kode_cabang'   => $cabang->kode_cabang,
                'kode_anggaran' => $anggaran->kode_anggaran,
            ]);
        endif;
    }

}