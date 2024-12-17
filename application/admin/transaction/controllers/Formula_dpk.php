<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Formula_dpk extends BE_Controller {
    var $path = 'transaction/budget_planner/';
    var $controller = 'formula_dpk';
    var $detail_tahun;
    var $kode_anggaran;
    var $real_status    = false;
    var $real_tahun     = [];
    var $arr_tahun      = [];
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
        foreach ($this->detail_tahun as $k => $v) {
            if($v->singkatan == arrSumberData()['real']):
                 $this->real_status = true;
                 if(!in_array($v->tahun,$this->real_tahun)) array_push($this->real_tahun,$v->tahun);
            endif;
            if(!in_array($v->tahun,$this->arr_tahun)) array_push($this->arr_tahun,$v->tahun);
        }
    }
    
    function index($p1="") { 
        $access = get_access($this->controller);
        $data   = data_cabang($this->controller);
        $data['path'] = $this->path;
        $data['detail_tahun'] = $this->detail_tahun;
        $data['access_additional']  = $access['access_additional'];
        $data['arr_tahun']          = $this->arr_tahun;
        $data['anggaran']           = get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->row_array();
        render($data,'view:'.$this->path.'formula_dpk/index');
    }

    function data_lama($kode_anggaran="", $kode_cabang="") {
        $nama_cabang ='';
        $cab = get_data('tbl_m_cabang','kode_cabang',$kode_cabang)->row();               
        if(isset($cab->nama_cabang)) $nama_cabang = $cab->nama_cabang;

        $anggaran = get_data('tbl_tahun_anggaran',[
            'select' => '*',
            'where'  => [
                'kode_anggaran' => $kode_anggaran,
            ],
        ])->row();

        if(isset($anggaran)) $tahun_anggaran = $anggaran->tahun_anggaran;

        $data['detail_tahun'] = $this->detail_tahun;

        $TOT_cab = 'TOT_' . $kode_cabang ;   
        $field_tabel    = get_field('tbl_rate','name');

 
        if (in_array($TOT_cab, $field_tabel)) {
            $TOT_cab = 'TOT_' . $kode_cabang ;   
        }else{
            $TOT_cab = 0 ;  
        }   

        $data['rate_kasda'] = get_data('tbl_rate',[
            'select' => 'no_coa,'.$TOT_cab.' as rate',
            'where'  => [
                'kode_anggaran' => $kode_anggaran,
                'no_coa' => '2101012'
            ],
        ])->row_array();


        $data['rate_nonkasda'] = get_data('tbl_rate',[
            'select' => 'no_coa,'.$TOT_cab.' as rate',
            'where'  => [
                'kode_anggaran' => $kode_anggaran,
                'no_coa' => '2101011'
            ],
        ])->row_array();

            
       $data['A1'] = get_data('tbl_m_coa a',[

            'select'    => 
                    "a.glwdes,
                    a.glwsbi,
                    a.glwnco",

            'where'     => " a.glwnco like '2101011%' or a.glwnco like '5131011%'  group by a.glwdes order by a.glwnco  "
        ])->result();



        $data['A1_detail'] = get_data('tbl_budget_plan_giro a',[
            'select' => 'a.*',
            'where'  => [
                    'a.kode_cabang' => $kode_cabang,
                    'a.kode_anggaran' => $kode_anggaran,
                    'a.coa' => '2101011' 
                ] 
        ])->result();


         $data['A2'] = get_data('tbl_m_coa',[

            'select'    => 
                    "glwdes,
                    glwsbi,
                    glwnco",

            'where'     => " glwnco like '2101012%' or glwnco like '5131012%' group by glwdes order by glwnco  "
        ])->result();  

        $data['A2_detail'] = get_data('tbl_budget_plan_giro a',[
            'select' => 'a.*',
            'where'  => [
                    'a.kode_cabang' => $kode_cabang,
                    'a.kode_anggaran' => $kode_anggaran,
                    'a.coa' => '2101012' 
                ] 
        ])->result();

        $data['B'] = get_data('tbl_m_rincian_tabungan a',[
            'select'    => 'a.nama as glwdes,a.coa as glwnco,a.biaya_bunga,b.glwdes as acct_bunga', 
            'join'      => 'tbl_m_coa b on a.biaya_bunga = b.glwnco type LEFT',
            'where'     => [
                'a.is_active' => 1,
                'a.kode_anggaran' => $kode_anggaran
            ],
            'sort_by' => 'a.coa',
        ])->result();  

        $TOT_cab = 'TOT_' . $kode_cabang ;   
        $field_tabel    = get_field('tbl_rate','name');
        if (in_array($TOT_cab, $field_tabel)) {
            $data['C'] = get_data('tbl_m_rincian_deposit a',[
                'select' => 'a.*,b.'.$TOT_cab.' as rate',
                'join'   => "tbl_rate b on a.coa = b.no_coa and b.kode_anggaran = '$kode_anggaran' type LEFT",
                'where' => [
                    'a.is_active' => 1,
                    'a.kode_anggaran' => $kode_anggaran
                ],
                'sort_by' => 'a.coa'
            ])->result();  
        }else{
            $data['C'] = get_data('tbl_m_rincian_deposit a',[
                'select' => 'a.*,0 as rate,0 as prsn',
                'join'   => "tbl_rate b on a.coa = b.no_coa and b.kode_anggaran = '$kode_anggaran' type LEFT",
                'where' => [
                    'a.is_active' => 1,
                    'a.kode_anggaran' => $kode_anggaran
                ],
                'sort_by' => 'a.coa'
            ])->result();  
        }

        $TOT_cab = 'TOT_' . $kode_cabang ;   
        $field_tabel    = get_field('tbl_rate','name');

        if (in_array($TOT_cab, $field_tabel)) {
            $data['rinc_tab'] = get_data('tbl_budget_plan_tabungan a',[
                'select' => 'a.*,'.$TOT_cab.' as rate',
                'join'   => "tbl_rate b on a.coa = b.no_coa and b.kode_anggaran = '$kode_anggaran' type LEFT",
                'where'  => [
                    'a.kode_cabang' => $kode_cabang,
                    'a.kode_anggaran' => $kode_anggaran,
                    'a.parent_id !=' => 0, 
                ]
            ])->result();
        }else{
            $data['rinc_tab'] = get_data('tbl_budget_plan_tabungan a',[
                'select' => 'a.*,0 as rate',
                'join'   => "tbl_rate b on a.coa = b.no_coa and b.kode_anggaran = '$kode_anggaran' type LEFT",
                'where'  => [
                    'a.kode_cabang' => $kode_cabang,
                    'a.kode_anggaran' => $kode_anggaran,
                    'a.parent_id !=' => 0, 
                ]
            ])->result();
        }

        $TOT_cab = 'TOT_' . $kode_cabang ;   
        $field_tabel    = get_field('tbl_rate','name');

        if (in_array($TOT_cab, $field_tabel)) {
            $data['rinc_dep'] = get_data('tbl_budget_plan_deposito a',[
                'select' => 'a.*,'.$TOT_cab.' as rate',
                'join'   => "tbl_rate b on a.coa = b.no_coa and b.kode_anggaran = '$kode_anggaran' type LEFT",
                'where'  => [
                    'a.kode_cabang' => $kode_cabang,
                    'a.kode_anggaran' => $kode_anggaran,
                    'a.parent_id !=' => 0, 
                ]
            ])->result();
        }else{
            $data['rinc_dep'] = get_data('tbl_budget_plan_deposito a',[
                'select' => 'a.*,0 as rate',
                'join'   => "tbl_rate b on a.coa = b.no_coa and b.kode_anggaran = '$kode_anggaran' type LEFT",
                'where'  => [
                    'a.kode_cabang' => $kode_cabang,
                    'a.kode_anggaran' => $kode_anggaran,
                    'a.parent_id !=' => 0, 
                ]
            ])->result();
        }


        $data['realisasi'] = get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->row_array();

        $select = 'TOT_'.$kode_cabang;
        $data['tahun'] = get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->result_array();

        $tahun = 'tbl_history_'.$data['tahun'][0]['tahun_terakhir_realisasi'];
        $getMinBulan = $data['tahun'][0]['bulan_terakhir_realisasi'] - 1;

        $data['Bunga'] = get_data($tahun,[

            'select'    => 
                    "glwnco,account_name,coalesce(sum(case when bulan = '".$data['tahun'][0]['bulan_terakhir_realisasi']."'  then ".$select." end), 0) as hasil10,
                    coalesce(sum(case when bulan = '".$getMinBulan."'  then ".$select." end), 0) as hasil9,
                    account_name,
                    coa,
                    gwlsbi,
                    glwnco",

            'where'     => [
            'glwnco' => '5132012',       
            ],
            'group_by' => 'glwnco',
        ])->row_array();
        $data['anggaran']       = $anggaran;
        $data['kode_cabang']    = $kode_cabang;


        $response   = array(
            'table'     => $this->load->view('transaction/budget_planner/formula_dpk/table',$data,true),
        );
        render($response,'json');
    }

    function data($kode_anggaran,$kode_cabang){
        $data_finish['kode_anggaran']   = $kode_anggaran;
        $data_finish['kode_cabang']     = $kode_cabang;
        $access = get_access('formula_dpk',$data_finish);
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
        $dataDefault['arr_tahun']   = $this->arr_tahun;
        $view = $this->load->view($this->path.$this->controller.'/table_save',$dataDefault,true);

        // GIRO
        $giro_arr       = [];
        $arr_coa_giro   = [];

        $field_tabel     = get_field('tbl_rate','name');
        if (in_array('TOT_'.$kode_cabang, $field_tabel)):
            $TOT_cab = 'TOT_' . $kode_cabang ;   
        else:
            $TOT_cab = 0 ;  
        endif;
        $giro_coa       = get_data('tbl_produk_coa a',[
            'select' => "a.coa,a.bunga,b.glwdes,c.glwdes as bunga_name,ifnull(".$TOT_cab.",0) as rate",
            'where'  => "a.is_active = 1 and a.grup = 'giro'",
            'join'   => [
                "tbl_m_coa b on b.glwnco = a.coa and b.kode_anggaran = '$anggaran->kode_anggaran'",
                "tbl_m_coa c on c.glwnco = a.bunga and c.kode_anggaran = '$anggaran->kode_anggaran'",
                "tbl_rate e on e.no_coa = a.coa and e.kode_anggaran = '$anggaran->kode_anggaran' type left"
            ]
        ])->result();
        foreach ($giro_coa as $k => $v) {
            if(!in_array($v->coa,$giro_arr)) array_push($giro_arr,$v->coa);
            if(!in_array($v->coa,$arr_coa_giro)) array_push($arr_coa_giro,$v->coa);
            if(!in_array($v->bunga,$giro_arr)) array_push($giro_arr,$v->bunga);
        }
        if($this->real_status && $anggaran->tahun_terakhir_realisasi != $anggaran->tahun_anggaran):
            $giro_core = get_data_core($giro_arr,$this->real_tahun,'TOT_'.$kode_cabang);
        else:
            $giro_core = get_data_core($giro_arr,[$anggaran->tahun_terakhir_realisasi],'TOT_'.$kode_cabang);
        endif;
        $giro_dt   = get_data('tbl_budget_plan_giro',[
            'where' => [
                'kode_anggaran' => $anggaran->kode_anggaran,
                'kode_cabang'   => $kode_cabang,
                'coa'           => $giro_arr,
                'tahun_core'    => $this->arr_tahun,
            ]
        ])->result_array();

        $dt_bunga   = get_data('tbl_formula_dpk a',[
            'where' => [
                'a.kode_anggaran' => $anggaran->kode_anggaran,
                'a.kode_cabang'   => $kode_cabang,
                'a.glwnco != '    => $arr_coa_giro,
            ]
        ])->result_array();

        $data = $dataDefault;
        $data['giro_coa']       = $giro_coa;
        $data['giro_core']      = $giro_core;
        $data['giro_dt']        = $giro_dt;
        $data['dt_bunga']       = $dt_bunga;
        $data['arr_coa_hadiah'] = [];
        $view = $this->load->view($this->path.$this->controller.'/table',$data,true);

        // Tabungan
        $giro_arr       = [];
        $arr_coa_giro   = [];
        $giro_coa       = get_data('tbl_m_rincian_tabungan a',[
            'select' => "a.coa,a.biaya_bunga as bunga,b.glwdes,c.glwdes as bunga_name,ifnull(".$TOT_cab.",0) as rate",
            'where'  => [
                'a.is_active' => 1,
                'a.kode_anggaran' => $anggaran->kode_anggaran
            ],
            'join'   => [
                "tbl_m_coa b on b.glwnco = a.coa and b.kode_anggaran = '$anggaran->kode_anggaran'",
                "tbl_m_coa c on c.glwnco = a.biaya_bunga and c.kode_anggaran = '$anggaran->kode_anggaran'",
                "tbl_rate e on e.no_coa = a.coa and e.kode_anggaran = '$anggaran->kode_anggaran' type left"
            ],
            'order_by' => 'b.urutan'
        ])->result();
        foreach ($giro_coa as $k => $v) {
            if(!in_array($v->coa,$giro_arr)) array_push($giro_arr,$v->coa);
            if(!in_array($v->coa,$arr_coa_giro)) array_push($arr_coa_giro,$v->coa);
            if(!in_array($v->bunga,$giro_arr)) array_push($giro_arr,$v->bunga);
        }
        if($this->real_status && $anggaran->tahun_terakhir_realisasi != $anggaran->tahun_anggaran):
            $giro_core = get_data_core($giro_arr,$this->real_tahun,'TOT_'.$kode_cabang);
        else:
            $giro_core = get_data_core($giro_arr,[$anggaran->tahun_terakhir_realisasi],'TOT_'.$kode_cabang);
        endif;
        $arr_coa_hadiah = ['412','416'];
        foreach($arr_coa_hadiah as $v){
            array_push($giro_arr,$v.'_hadiah');
        }
        $giro_dt   = get_data('tbl_budget_plan_tabungan',[
            'where' => [
                'kode_anggaran' => $anggaran->kode_anggaran,
                'kode_cabang'   => $kode_cabang,
                'coa'           => $giro_arr,
                'tahun_core'    => $this->arr_tahun,
            ]
        ])->result_array();

        $dt_bunga   = get_data('tbl_formula_dpk a',[
            'where' => [
                'a.kode_anggaran' => $anggaran->kode_anggaran,
                'a.kode_cabang'   => $kode_cabang,
                'a.glwnco != '    => $arr_coa_giro,
            ]
        ])->result_array();

        $data = $dataDefault;
        $data['giro_coa']       = $giro_coa;
        $data['giro_core']      = $giro_core;
        $data['giro_dt']        = $giro_dt;
        $data['dt_bunga']       = $dt_bunga;
        $data['arr_coa_hadiah'] = $arr_coa_hadiah;
        $tabungan = $this->load->view($this->path.$this->controller.'/table',$data,true);

        // Deposito / Simpanan Berjangka
        $view_deposito  = $this->deposito($dataDefault,$TOT_cab);

        $response   = array(
            'table'         => $view,
            'tab'           => $tabungan,
            'deposito'      => $view_deposito['view'],
            'deposito2'     => $view_deposito['view2'],
            'access_edit'   => $access_edit,
        );
        render($response,'json');
    }

    private function deposito($data,$TOT_cab){
        $kode_cabang = $data['kode_cabang'];
        $anggaran    = $data['anggaran'];
        $dt_coa       = get_data('tbl_m_rincian_deposit a',[
            'select' => "a.coa,a.nama,ifnull(".$TOT_cab.",0) as rate",
            'where'  => [
                'a.is_active' => 1,
                'a.kode_anggaran' => $anggaran->kode_anggaran
            ],
            'join'   => [
                "tbl_rate e on e.no_coa = a.coa and e.kode_anggaran = '$anggaran->kode_anggaran' type left"
            ],
            'order_by' => 'a.id'
        ])->result();

        $arr_coa = [];
        foreach ($dt_coa as $k => $v) {
            if(!in_array($v->coa,$arr_coa)) array_push($arr_coa,$v->coa);
        }

        $dt   = get_data('tbl_budget_plan_deposito',[
            'where' => [
                'kode_anggaran' => $anggaran->kode_anggaran,
                'kode_cabang'   => $kode_cabang,
                'coa'           => $arr_coa,
                'tahun_core'    => $this->arr_tahun,
            ]
        ])->result_array();

        if($this->real_status && $anggaran->tahun_terakhir_realisasi != $anggaran->tahun_anggaran):
            $data_core = get_data_core(['5132012'],$this->real_tahun,'TOT_'.$kode_cabang);
        else:
            $data_core = get_data_core(['5132012'],[$anggaran->tahun_terakhir_realisasi],'TOT_'.$kode_cabang);
        endif;

        $coa = get_data('tbl_m_coa',[
            'where' => [
                'glwnco' => '5132012',
                'kode_anggaran' => $anggaran->kode_anggaran
            ]
        ])->row();

        $dt_bunga = get_data('tbl_formula_dpk',[
            'where' => [
                'kode_anggaran' => $anggaran->kode_anggaran,
                'kode_cabang'   => $kode_cabang,
                'glwnco'        => '5132012_baru'
            ]
        ])->result_array();

        $data['dt_coa']     = $dt_coa;
        $data['dt']         = $dt;
        $data['data_core']  = $data_core;
        $data['coa']        = $coa;
        $data['dt_bunga']   = $dt_bunga;
        $view   = $this->load->view($this->path.$this->controller.'/deposito',$data,true);
        $view2  = $this->load->view($this->path.$this->controller.'/deposito2',$data,true);

        return [
            'view'  => $view,
            'view2' => $view2,
        ];
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
            $ck = get_data('tbl_formula_dpk',[
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
            $res = save_data('tbl_formula_dpk',$data,[],false);

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
            $ck = get_data('tbl_formula_dpk',[
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
                update_data('tbl_formula_dpk',['changed' => json_encode($edited)],'id',$v->id);
            }
            render(['status' => 'success', 'message' => lang('berhasil')],'json');
        else:
            render(['status' => false,'message' => lang('data_not_found')],'json');
        endif;
    }   

}