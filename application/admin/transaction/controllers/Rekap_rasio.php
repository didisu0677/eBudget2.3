<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rekap_rasio extends BE_Controller {
    var $path = 'transaction/budget_planner/';
    var $controller = 'rekap_rasio';
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
        $data = data_cabang('rekap_rasio');
        $data['detail_tahun'] = $this->detail_tahun;
        $data['path'] = $this->path;
        $data['access_additional']  = $access['access_additional'];
        render($data,'view:'.$this->path.'rekap_rasio/index');
    }

    function data ($anggaran1="", $cabang=""){
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$anggaran1)->row();
        $thn_trakhir = $anggaran->tahun_anggaran;

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

        $selectDpk = "";
        $selectkredit = "";
        $selectLoan = "a.glwnco,";
        $selectRoa = "";
        $selectCasa = "a.coa,";
        for($a = 1;$a <= 12;$a++){
            if($a >= 10){
                $selectkredit .= "a.B_".$a.",";

                $selectDpk .= "( ((ifnull(b.bulan_".$a.",0)/".$a." * 12) /a.b_".$a.") * 100) as hasil".$a.",";
                $selectDpk .= "a.B_".$a.",";
                $selectDpk .= "ifnull(b.bulan_".$a.",0) as bulan_".$a.",";

                $selectRoa .= "((b.bulan_".$a."/".$a.") * 12) as hasil".$a.",";
                $selectRoa .= "a.B_".$a.",";
                $selectRoa .= "ifnull(b.bulan_".$a.",0) as bulan_".$a.",";

                $selectLoan .= "a.bulan_".$a.",";

                $selectCasa .= "a.B_".$a.",";

            }else {
                $selectkredit .= "a.B_0".$a.",";

                $selectDpk .= "( ((ifnull(b.bulan_".$a.",0)/".$a." * 12) /a.b_0".$a.") * 100) as hasil".$a.",";
                $selectDpk .= "a.B_0".$a.",";
                $selectDpk .= "ifnull(b.bulan_".$a.",0) as bulan_".$a.",";

                $selectRoa .= "((b.bulan_".$a."/".$a.") * 12) as hasil".$a.",";
                $selectRoa .= "a.B_0".$a.",";
                $selectRoa .= "ifnull(b.bulan_".$a.",0) as bulan_".$a.",";

                $selectLoan .= "a.bulan_".$a.",";

                $selectCasa .= "a.B_0".$a.",";
            }
            $selectDpk .= "ifnull(b.bulan_".$a.",0) as bulan_".$a.",";
           

        }

         $data['rateKredit'] = get_data('tbl_budget_plan_neraca a',[
            'select' => $selectDpk,
            'join'   => "tbl_labarugi b on a.kode_cabang = b.kode_cabang and a.kode_anggaran = b.kode_anggaran and b.glwnco = '4150000' type left",
            'where'  => "a.kode_cabang = '".$cabang."' and a.kode_anggaran = '".$anggaran->kode_anggaran."' and a.coa = '1450000'"
         ])->result_array();

         $data['rateDpk'] = get_data('tbl_budget_plan_neraca a',[
            'select' => $selectDpk,
            'join'   => "tbl_labarugi b on a.kode_cabang = b.kode_cabang and a.kode_anggaran = b.kode_anggaran and b.glwnco = '5130000' type left",
            'where'  => "a.kode_cabang = '".$cabang."' and a.kode_anggaran = '".$anggaran->kode_anggaran."' and a.coa = '602'"
         ])->result_array();

         $data['portofolioKredit'] = get_data('tbl_budget_plan_neraca a',[
            'select' => 'a.coa, '.$selectkredit,
            'where'  => "a.kode_anggaran = '".$anggaran->kode_anggaran."' and a.kode_cabang = '".$cabang."' and (a.coa = '122502' or a.coa = '122501' or a.coa = '122506')"
         ])->result_array();

         $data['kolektabilitasNpl'] = get_data('tbl_kolektibilitas_npl a',[
            'where'  => "a.kode_anggaran = '".$anggaran->kode_anggaran."' and a.kode_cabang = '".$cabang."' and a.tahun_core = '".$thn_trakhir."'"
         ])->result_array();

         // print_r($this->db->last_query()); 


          $data['kolektabilitasDetail1'] = get_data('tbl_kolektibilitas a, tbl_kolektibilitas_detail b',[
            'select' => 'b.*',
            'where'  => "a.kode_anggaran = '".$anggaran->kode_anggaran."' and a.id = b.id_kolektibilitas and coa_produk_kredit  = '122502' and a.kode_cabang = '".$cabang."'  and b.tahun_core = '".$thn_trakhir."'"
         ])->result_array();

          $data['kolektabilitasDetail2'] = get_data('tbl_kolektibilitas a, tbl_kolektibilitas_detail b',[
            'select' => 'b.*',
            'where'  => "a.kode_anggaran = '".$anggaran->kode_anggaran."' and a.id = b.id_kolektibilitas and coa_produk_kredit  = '122506' and a.kode_cabang = '".$cabang."'  and b.tahun_core = '".$thn_trakhir."' "
         ])->result_array();



          $data['loan'] = get_data('tbl_labarugi a',[
            'select' => $selectLoan,
            'where'  => "a.kode_anggaran = '".$anggaran->kode_anggaran."' and a.kode_cabang = '".$cabang."' and (a.glwnco = '4100000' or a.glwnco = '5500000' or a.glwnco = '5100000') "
         ])->result_array();


         $data['roa'] = get_data('tbl_budget_plan_neraca a',[
            'select' => $selectRoa,
            'join'   => "tbl_labarugi b on a.kode_cabang = b.kode_cabang and a.kode_anggaran = b.kode_anggaran and b.glwnco = '59999' type left",
            'where'  => "a.kode_cabang = '".$cabang."' and a.kode_anggaran = '".$anggaran->kode_anggaran."' and a.coa = '1000000'"
         ])->result_array();


         $data['nim'] = get_data('tbl_labarugi b',[
            'where'  => "b.kode_anggaran = '".$anggaran->kode_anggaran."' and b.kode_cabang = '".$cabang."' and (b.glwnco = '5100000' or b.glwnco = '4100000')"
         ])->result_array();

        // COA 1200000+COA1220000+COA1250000+COA1300000+COA1400000+COA1450000
        $data['nimAktifa'] = get_data('tbl_budget_plan_neraca a',[
            'select' => "
                sum(ifnull(B_01,0)) as B_01,
                sum(ifnull(B_02,0)) as B_02,
                sum(ifnull(B_03,0)) as B_03,
                sum(ifnull(B_04,0)) as B_04,
                sum(ifnull(B_05,0)) as B_05,
                sum(ifnull(B_06,0)) as B_06,
                sum(ifnull(B_07,0)) as B_07,
                sum(ifnull(B_08,0)) as B_08,
                sum(ifnull(B_09,0)) as B_09,
                sum(ifnull(B_10,0)) as B_10,
                sum(ifnull(B_11,0)) as B_11,
                sum(ifnull(B_12,0)) as B_12,
            ",
            'where'  => "a.kode_anggaran = '".$anggaran->kode_anggaran."' and a.kode_cabang = '".$cabang."' and a.coa in ('1200000','1220000','1250000','1300000','1400000','1450000') "
         ])->result_array();


         $data['casa'] = get_data('tbl_budget_plan_neraca a',[
            'select' => 'distinct '.$selectCasa,
            'where'  => "a.kode_cabang = '".$cabang."' and  a.kode_anggaran = '".$anggaran1."' and (a.coa = '602' or a.coa = '2130000')"
         ])->result_array();

         $data['rasiofee'] = get_data('tbl_labarugi a',[
            'select' => $selectLoan,
            'where'  => "a.kode_anggaran = '".$anggaran->kode_anggaran."' and a.kode_cabang = '".$cabang."' and (a.glwnco = '4590000' or a.glwnco = '4500000' or a.glwnco = '4100000') "
         ])->result_array();

        $response   = array(
            'status'    => true,
            'table'     => $this->load->view('transaction/budget_planner/rekap_rasio/table',$data,true),
            'access_edit' => $access_edit
        );
        render($response,'json');
    }

    function save_perubahan($anggaran="",$cabang="") {       

        $data   = json_decode(post('json'),true);

        // pengecekan save untuk cabang
        check_save_cabang($this->controller,$anggaran,$cabang,'access_edit');

        // echo post('json');
        foreach($data as $getId => $record) {
            $cekId = $getId;

            $record = insert_view_report_arr($record);
            // echo $id." - ".$cekId[1]."<br>";
            $cek  = get_data('tbl_rekap_rasio a',[
                'select'    => 'a.id',
                'where'     => [
                    'a.kode'             => $cekId,
                    'a.kode_anggaran'   => $anggaran,
                    'a.kode_cabang'   => $cabang,
                ]
            ])->result_array();
     
            if(count($cek) > 0){
                update_data('tbl_rekap_rasio', $record,'id',$cek[0]['id']);
            }else {
                    $record['kode'] = $cekId;
                    $record['kode_anggaran'] = $anggaran;
                    $record['kode_cabang'] = $cabang;
                    insert_data('tbl_rekap_rasio',$record);
            } 
         }

        $this->db->query("call stored_budget_nett('rekap_rasio','".$cabang."','".$anggaran."')");
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

        $header = $dt['#result1']['header'][1];


        $data = [];
        foreach(['#result1'] as $name){
            if(isset($dt[$name])):
                foreach($dt[$name]['data'] as $k => $v){
                    if(count($v)>5):
                        $detail = [
                            $v[0],
                            $v[1],
                        ];
                        for ($i=2; $i < count($v) ; $i++) { 
                            $detail[] = filter_money($v[$i]);
                        }
                        $data[] = $detail;
                    else:
                        $detail = [];
                        for ($i=1; $i <= 14 ; $i++) { 
                            $detail[] = '';
                        }
                        $data[] = $detail;
                    endif;
                    
                }
            endif;
        }

        $config[] = [
            'title' => 'Rekap Rasio '.' ('.get_view_report().')',
            'header' => $header,
            'data'  => $data,
        ];

        // render($config,'json'); exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'rekap_rasio_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }
} 

