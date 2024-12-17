<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Konsolidasi_rekaprasio extends BE_Controller {
    var $path = 'transaction/budget_nett_konsolidasi/';
    var $controller = 'konsolidasi_rekaprasio';
    var $sub_menu   = 'transaction/budget_nett_konsolidasi/sub_menu';
    var $cabang_gab = [];
    var $detail_tahun;
    var $anggaran;
    var $kode_anggaran;
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
    }

    function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');

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
                    ];
                    foreach ($v as $k2 => $v2) {
                        if($k2>1):
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
                for($i=1;$i<=count($neraca_header[0]);$i++){
                    $data[$k][] = '';
                }
            endif;
        }
        $config[] = [
            'title' => 'Konsolidasi Rekap Rasio',
            'header' => $neraca_header[0],
            'data'  => $data,
        ];
        
        $this->load->library('simpleexcel',$config);
        $filename = 'Budget_Nett_Konsolidasi_Rekap_Rasio'.str_replace(' ', '_', $kode_anggaran_txt).'_'.'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
        // render($neraca_header,'json');
    }
    
    function index() {
        $kode_anggaran    = user('kode_anggaran');
        // $this->db->query("CALL stored_budget_nett_konsolidasi('".$kode_anggaran."')");
        $data['tahun']    = get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->result();
        $data['path']     = $this->path;
        $data['sub_menu'] = $this->sub_menu;
        $data['controller']     = $this->controller;
        render($data,'view:'.$this->path.$this->controller.'/index');
        
    }

    function create_kons(){
        $kode_anggaran    = user('kode_anggaran');
        $this->db->query("CALL stored_budget_nett_konsolidasi_neraca('".$kode_anggaran."')");
        $this->db->query("CALL stored_budget_nett_konsolidasi_labarugi('".$kode_anggaran."')");
        render(['status' => 'success','message' => lang('data_berhasil_disimpan')],'json');
    }

    private function get_cabang(){
        $cab = get_data('tbl_m_cabang',[
            'select'    => 'kode_cabang,nama_cabang',
            'where'     => "parent_id = 0 and is_active = 1 and kode_anggaran = '".user('kode_anggaran')."'",
            'order_by'  => "urutan"
        ])->result();
        return $cab;
    }

    function rekaprasio_column($kode_anggaran){
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();

        $item = $this->load->view($this->path.$this->controller.'/content',[],true);

        $cabang_list = $this->get_cabang();
        $item_cab    = '';
        $item_month  = '';
        $arr_kode_cabang = ['KONS','KONV'];

        $item_cab .= '<th class="d-head" colspan="12"><span>Konsolidasi</span></th>';
        foreach ($this->detail_tahun as $k => $v) {
            $column = month_lang($v->bulan).' '.$v->tahun;
            $column .= '<br> ('.$v->singkatan.')';
            $item_month .= '<th class="wd-150 text-center d-head"><span>'.$column.'</span></th>';
        }

        $item_cab .= '<th class="border-none bg-white d-head" style="min-width:80px;"></th>';
        $item_cab .= '<th class="d-head" colspan="12"><span>Konvensional</span></th>';
        $item_month .= '<th class="border-none bg-white d-head"></th>';
        foreach ($this->detail_tahun as $k => $v) {
            $column = month_lang($v->bulan).' '.$v->tahun;
            $column .= '<br> ('.$v->singkatan.')';
            $item_month .= '<th class="wd-150 text-center d-head"><span>'.$column.'</span></th>';
        }

        foreach ($cabang_list as $k => $v) {
            $arr_kode_cabang[] = $v->kode_cabang;
            $item_cab .= '<th class="border-none bg-white d-head" style="min-width:80px;"></th>';
            $item_cab .= '<th class="d-head" colspan="12"><span>'.$v->nama_cabang.'</span></th>';
            $item_month .= '<th class="border-none bg-white d-head"></th>';
            foreach ($this->detail_tahun as $k => $v) {
                $column = month_lang($v->bulan).' '.$v->tahun;
                $column .= '<br> ('.$v->singkatan.')';
                $item_month .= '<th class="wd-150 text-center d-head"><span>'.$column.'</span></th>';
            }
        }

        $this->session->set_userdata(['nett_konolidasi_cabang' => $arr_kode_cabang]);

        $response = [
            'status'    => true,
            'view'      => $item,
            'cabang'    => $item_cab,
            'month'     => $item_month,
        ];
        render($response,'json');
    }

    function load_more_rekap(){
        $count  = post('count');
        $cab    = $this->session->nett_konolidasi_cabang;
        if(isset($cab[$count])):
            $anggaran = $this->anggaran;
            $kode_cabang      = $cab[$count];
            $select = '';
            for($i=1;$i<=12;$i++){
                    $select .= "bulan_".$i.",";
            }
            $view = [];

             // mengambil data dari budget nett
            $arr_budget_nett = ['602','5130000','4150000','1450000','122502','122501','122506','5100000','5500000','4100000','59999','1000000','2100000','2120011','1200000','1220000','1250000','1300000','1400000','1450000','4590000','4500000'];

            $dt_budget_neraca  = get_data('tbl_budget_nett_neraca',[
                'where' => [
                    'kode_anggaran' => $anggaran->kode_anggaran,
                    'kode_cabang'   => $kode_cabang,
                    'coa'           => ['602','1450000','122502','122501','122506','1000000','2100000','2120011','1200000','1220000','1250000','1300000','1400000','1450000']
                ],
            ])->result_array();
            $dt_budget_labarugi  = get_data('tbl_budget_nett_labarugi',[
                'where' => [
                    'kode_anggaran' => $anggaran->kode_anggaran,
                    'kode_cabang'   => $kode_cabang,
                    'coa'           => ['5130000','4150000','5100000','5500000','4100000','59999','4590000','4500000']
                ],
            ])->result_array();

            $data = [];
            foreach ($arr_budget_nett as $v) {
                $key_neraca   = array_search($v, array_column($dt_budget_neraca, 'coa'));
                $key_labarugi = array_search($v, array_column($dt_budget_labarugi, 'coa'));
                if(strlen($key_neraca)>0):
                    $data[$v] = $dt_budget_neraca[$key_neraca];
                elseif(strlen($key_labarugi)>0):
                    $data[$v] = $dt_budget_labarugi[$key_labarugi];
                else:
                    $data[$v] = [
                        'B_01' => 0,
                        'B_02' => 0,
                        'B_03' => 0,
                        'B_04' => 0,
                        'B_05' => 0,
                        'B_06' => 0,
                        'B_07' => 0,
                        'B_08' => 0,
                        'B_09' => 0,
                        'B_10' => 0,
                        'B_11' => 0,
                        'B_12' => 0,
                    ];
                endif;
            }

            // mengambil data dari budget nett rekap rasio
            $arr_kode = ['A12','A13','A14','A15','A16','A17','A19','A20','A21','A22','A23','A24'];
            $dt_budget_rekaprasio  = get_data('tbl_budget_nett_rekaprasio',[
                'where' => [
                    'kode_anggaran' => $anggaran->kode_anggaran,
                    'kode_cabang'   => $kode_cabang,
                    'kode'           => $arr_kode
                ],
            ])->result_array();
            foreach ($arr_kode as $v) {
                $key = array_search($v, array_column($dt_budget_rekaprasio, 'kode'));
                if(strlen($key)>0):
                    $data[$v] = $dt_budget_rekaprasio[$key];
                else:
                    $data[$v] = [
                        'B_01' => 0,
                        'B_02' => 0,
                        'B_03' => 0,
                        'B_04' => 0,
                        'B_05' => 0,
                        'B_06' => 0,
                        'B_07' => 0,
                        'B_08' => 0,
                        'B_09' => 0,
                        'B_10' => 0,
                        'B_11' => 0,
                        'B_12' => 0,
                    ];
                endif;
            }

            // membuat varibale umum untuk kode sandi
            $arr_kode_tambahan = [32,33,34];
            for($i=1;$i<=34;$i++){
                $td_space = '';
                if($count != 0):
                    $td_space = '<td class="border-none bg-white"></td>';
                endif;

                $view['.d-rekaprasio-A'.$i] = $td_space;
                if(in_array($i, $arr_kode_tambahan)):
                    $view['.d-rekaprasio-A'.$i.'_1'] = $td_space;
                    $view['.d-rekaprasio-A'.$i.'_2'] = $td_space;
                endif;
            }

            $total100 = 0;
            $totalAktivaProduktif = 0;
            for($i=1;$i<=12;$i++){
                $filed = 'B_'.sprintf("%02d",$i);
                
                // dpk
                $pembagi = $data['602'][$filed];
                $A1 = 0;
                if($pembagi):
                    $A1 = ( (($data['5130000'][$filed]/$i) * 12)/ $pembagi) * 100;
                endif;
                $view['.d-rekaprasio-A1'] .= '<td class="text-right">'.custom_format($A1,false,2).'</td>';
                $view['.d-rekaprasio-A2'] .= '<td class="text-right">'.custom_format(view_report($data['5130000'][$filed])).'</td>';
                $view['.d-rekaprasio-A3'] .= '<td class="text-right">'.custom_format(view_report($data['602'][$filed])).'</td>';

                // kredit
                $pembagi = $data['1450000'][$filed];
                $A4 = 0;
                if($pembagi):
                    $A4 = ( (($data['4150000'][$filed]/$i) * 12)/ $pembagi) * 100;
                endif;
                $view['.d-rekaprasio-A4'] .= '<td class="text-right">'.custom_format($A4,false,2).'</td>';
                $view['.d-rekaprasio-A5'] .= '<td class="text-right">'.custom_format(view_report($data['4150000'][$filed])).'</td>';
                $view['.d-rekaprasio-A6'] .= '<td class="text-right">'.custom_format(view_report($data['1450000'][$filed])).'</td>';

                // portofolio
                $pembagi = $data['122501'][$filed];
                $A8 = 0;
                $A9 = 0;
                if($pembagi):
                    $A8 = ($data['122502'][$filed]/$pembagi) * 100;
                    $A9 = ($data['122506'][$filed]/$pembagi) * 100;
                endif;
                $view['.d-rekaprasio-A7'] .= '<td class="text-right"></td>';
                $view['.d-rekaprasio-A8'] .= '<td class="text-right">'.custom_format($A8,false,2).'</td>';
                $view['.d-rekaprasio-A9'] .= '<td class="text-right">'.custom_format($A9,false,2).'</td>';

                // kolektibilitas kredit produktif 122502
                $totalKolProduktif = $data['A15'][$filed] + $data['A16'][$filed] + $data['A17'][$filed];
                $pembagi = $data['122502'][$filed];
                $A11 = 0;
                if($pembagi):
                    $A11 = ($totalKolProduktif/$pembagi)*100;
                endif;
                $view['.d-rekaprasio-A11'] .= '<td class="text-right">'.custom_format($A11,false,2).'</td>';
                $view['.d-rekaprasio-A12'] .= '<td class="text-right">'.custom_format(view_report($data['122502'][$filed])).'</td>';
                $view['.d-rekaprasio-A13'] .= '<td class="text-right">'.custom_format(view_report($data['A13'][$filed])).'</td>';
                $view['.d-rekaprasio-A14'] .= '<td class="text-right">'.custom_format(view_report($data['A14'][$filed])).'</td>';
                $view['.d-rekaprasio-A15'] .= '<td class="text-right">'.custom_format(view_report($data['A15'][$filed])).'</td>';
                $view['.d-rekaprasio-A16'] .= '<td class="text-right">'.custom_format(view_report($data['A16'][$filed])).'</td>';
                $view['.d-rekaprasio-A17'] .= '<td class="text-right">'.custom_format(view_report($data['A17'][$filed])).'</td>';

                // kolektibilitas kredit konsumtif 122506
                $totalKolKonsumtif = $data['A22'][$filed] + $data['A23'][$filed] + $data['A24'][$filed];
                $pembagi = $data['122506'][$filed];
                $A18 = 0;
                if($pembagi):
                    $A18 = ($totalKolKonsumtif/$pembagi)*100;
                endif;
                $view['.d-rekaprasio-A18'] .= '<td class="text-right">'.custom_format($A18,false,2).'</td>';
                $view['.d-rekaprasio-A19'] .= '<td class="text-right">'.custom_format(view_report($data['122506'][$filed])).'</td>';
                $view['.d-rekaprasio-A20'] .= '<td class="text-right">'.custom_format(view_report($data['A20'][$filed])).'</td>';
                $view['.d-rekaprasio-A21'] .= '<td class="text-right">'.custom_format(view_report($data['A21'][$filed])).'</td>';
                $view['.d-rekaprasio-A22'] .= '<td class="text-right">'.custom_format(view_report($data['A22'][$filed])).'</td>';
                $view['.d-rekaprasio-A23'] .= '<td class="text-right">'.custom_format(view_report($data['A23'][$filed])).'</td>';
                $view['.d-rekaprasio-A24'] .= '<td class="text-right">'.custom_format(view_report($data['A24'][$filed])).'</td>';

                // kolektibilitas kredit npl
                $pembagi = $data['122502'][$filed] + $data['122506'][$filed];
                $A10 = 0;
                if($pembagi):
                    $A10 = (($totalKolKonsumtif+$totalKolProduktif)/$pembagi) *100;
                endif;
                $view['.d-rekaprasio-A10'] .= '<td class="text-right">'.custom_format($A10,false,2).'</td>';

                // Loan to Deposit Ratio (LDR)
                $pembagi = $data['602'][$filed];
                $A25 = 0;
                if($pembagi):
                    $A25 = ($data['1450000'][$filed]/$pembagi) * 100;
                endif;
                $view['.d-rekaprasio-A25'] .= '<td class="text-right">'.custom_format($A25,false,2).'</td>';

                // Rasio Biaya Operasional thd Pend. Operasional (BOPO)
                $A27 = $data['5100000'][$filed] + $data['5500000'][$filed];
                $A28 = $data['5100000'][$filed] + $data['4100000'][$filed];

                $pembagi = $A28;
                $A26 = 0;
                if($pembagi):
                    $A26 = ($A27/$pembagi) * 100;
                endif;
                $view['.d-rekaprasio-A26'] .= '<td class="text-right">'.custom_format($A26,false,2).'</td>';
                $view['.d-rekaprasio-A27'] .= '<td class="text-right">'.custom_format(view_report($A27)).'</td>';
                $view['.d-rekaprasio-A28'] .= '<td class="text-right">'.custom_format(view_report($A28)).'</td>';

                // Rasio ROA
                $total100 += $data['1000000'][$filed];
                $pembagi = $total100/$i;
                $A29 = 0;
                if($pembagi):
                    $A29 = ( (($data['59999'][$filed]/$i) * 12)/ $pembagi) * 100;
                endif;
                $view['.d-rekaprasio-A29'] .= '<td class="text-right">'.custom_format($A29,false,2).'</td>';
                $view['.d-rekaprasio-A30'] .= '<td class="text-right">'.custom_format(view_report($data['59999'][$filed])).'</td>';
                $view['.d-rekaprasio-A31'] .= '<td class="text-right">'.custom_format(view_report($data['1000000'][$filed])).'</td>';

                // Rasio Dana Murah (CASA)
                $A32_1 = $data['2100000'][$filed] + $data['2120011'][$filed];
                $pembagi = $data['602'][$filed];
                $A32 = 0;
                if($pembagi):
                    $A32 = ($A32_1 / $pembagi) * 100;
                endif;
                $view['.d-rekaprasio-A32'] .= '<td class="text-right">'.custom_format($A32,false,2).'</td>';
                $view['.d-rekaprasio-A32_1'] .= '<td class="text-right">'.custom_format(view_report($A32_1)).'</td>';
                $view['.d-rekaprasio-A32_2'] .= '<td class="text-right">'.custom_format(view_report($data['602'][$filed])).'</td>';

                // Net Interest Margin (NIM)
                $A33_1 = $data['4100000'][$filed] - $data['5100000'][$filed];
                $A33_2 = $data['1200000'][$filed] + $data['1220000'][$filed] + $data['1250000'][$filed] + $data['1300000'][$filed] + $data['1400000'][$filed] + $data['1450000'][$filed];
                $totalAktivaProduktif += $A33_2;
                $pembagi = $totalAktivaProduktif/$i;
                $A33 = 0;
                if($pembagi):
                    $A33 = ((($A33_1/$i) * 12)/ $pembagi) * 100;
                endif;
                $view['.d-rekaprasio-A33'] .= '<td class="text-right">'.custom_format($A33,false,2).'</td>';
                $view['.d-rekaprasio-A33_1'] .= '<td class="text-right">'.custom_format(view_report($A33_1)).'</td>';
                $view['.d-rekaprasio-A33_2'] .= '<td class="text-right">'.custom_format(view_report($A33_2)).'</td>';

                // Rasio Fee Base Income
                $A34_2   = $data['4100000'][$filed] + $data['4500000'][$filed];
                $pembagi = $A34_2;
                $A34     = 0;
                if($pembagi):
                    $A34     = ($data['4590000'][$filed]/$pembagi) * 100;
                endif;
                $view['.d-rekaprasio-A34'] .= '<td class="text-right">'.custom_format($A34,false,2).'</td>';
                $view['.d-rekaprasio-A34_1'] .= '<td class="text-right">'.custom_format(view_report($data['4590000'][$filed])).'</td>';
                $view['.d-rekaprasio-A34_2'] .= '<td class="text-right">'.custom_format(view_report($A34_2)).'</td>';


            }
            

            render([
                'status'    => true,
                'view'      => $view,
                'count'     => ($count+1),
                'classnya'  => '#rekaprasio tbody',
            ],'json');
        else:
            render(['status' => false],'json');
            
        endif;
    }
}