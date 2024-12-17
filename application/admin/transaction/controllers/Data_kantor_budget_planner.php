<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Data_kantor_budget_planner extends BE_Controller {
    var $path       = 'transaction/budget_planner/';
    var $controller = 'data_kantor_budget_planner';
    function __construct() {
        parent::__construct();
    }

    function index($p1="") { 
        $a      = get_access($this->controller);
        $data   = data_cabang('Data_kantor_budget_planner');
        $data['detail_tahun'] = get_data('tbl_detail_tahun_anggaran a',[
            'select' => 'a.tahun,a.bulan,b.singkatan',
            'where'  => [
                'a.kode_anggaran' => user('kode_anggaran'),
            ],
            'join' => 'tbl_m_data_budget b on b.id = a.sumber_data'
        ])->result_array();

        $data['path'] = $this->path;
        $data['access_additional']  = $a['access_additional'];
        render($data,'view:'.$this->path.'data_kantor/index');
    }


    function get_data($kode_anggaran="",$kode_cabang=""){
        $data = array();

        $cabang = get_data('tbl_m_cabang',[
            'where' => [
                'kode_cabang'   => $kode_cabang,
                'kode_anggaran' => $kode_anggaran
            ]
        ])->row();
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();

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

        if(isset($cabang->kode_cabang)) {
            $data2 = array(
                'kode_anggaran' => $anggaran->kode_anggaran,
                'keterangan_anggaran' => $anggaran->keterangan,
                'kode_cabang' => $kode_cabang,
                'nama_kantor' => $cabang->nama_cabang,
            ); 
        }

        $cek = get_data('tbl_plan_berita_acara',[
            'where' => [
                'kode_cabang' => $kode_cabang,
                'kode_anggaran' => $kode_anggaran
            ]
        ])->row();
        if(!$cek) {
            insert_data('tbl_plan_berita_acara',$data2);
        }else{
            update_data('tbl_plan_berita_acara',$data2,['kode_cabang'=>$kode_cabang,'kode_anggaran' => $kode_anggaran]);

        }

        $data = get_data('tbl_plan_berita_acara',[
            'where' =>[
                'kode_anggaran' => $kode_anggaran,
                'kode_cabang'   => $kode_cabang,
            ],
        ])->row_array();

        if($data){
            $data['tgl_mulai_menjabat'] = date("d-m-Y", strtotime($data['tgl_mulai_menjabat']));
        } else{
            $data = get_data('tbl_m_data_kantor',[
                'where' => [
                    "kode_cabang"   => $kode_cabang,
                    'kode_anggaran' => $kode_anggaran
                ]
            ])->row_array();

            if($data) $data['tgl_mulai_menjabat'] = date("d-m-Y", strtotime($data['tgl_mulai_menjabat']));
            else $data = array();
            
        }
        $data['access_edit'] = $access_edit;
        $data['status']      = true;
        render($data,'json');
    }

     function data2($kode_anggaran="", $kode_cabang=""){
        $anggaran     = get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->row();
        $detail_tahun = get_data('tbl_detail_tahun_anggaran a',[
            'select' => 'a.tahun,a.bulan,b.singkatan',
            'where'  => [
                'a.kode_anggaran' => $kode_anggaran,
            ],
            'join' => 'tbl_m_data_budget b on b.id = a.sumber_data'
        ])->result_array();

        // pengecekan akses cabang
        $access = get_access($this->controller);
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$access);

        $coa = get_data('tbl_item_plan_ba a',[
            'select' => 'a.coa,a.grup,b.glwdes,b.kali_minus',
            'join'   => "tbl_m_coa b on a.coa = b.glwnco and kode_anggaran = '".$kode_anggaran."'",
            'order_by' => 'a.id',
        ])->result();

        $arr_group  = [];
        $arr_coa    = [];
        foreach ($coa as $k => $v) {
            $arr_group[$v->grup][] = $v;
            if(!in_array($v->coa,$arr_coa)) array_push($arr_coa,$v->coa);
        }

        $dt = [];
        for ($i=3; $i >= 0 ; $i--) { 
            $t      = ($anggaran->tahun_anggaran - $i);
            $table  = 'tbl_history_'.$t;
            $bulan  = [12];
            
            if(12 != $anggaran->bulan_terakhir_realisasi && $t == $anggaran->tahun_terakhir_realisasi):
                $bulan[] = $anggaran->bulan_terakhir_realisasi;
            endif;

            if($this->db->table_exists($table)):
                $column = 'TOT_'.$kode_cabang;
                if($this->db->field_exists($column, $table)):
                    $dt[$t] = get_data($table,[
                        'select' => $column.' as total,bulan,glwnco as coa',
                        'where'  => [
                            'bulan'     => $bulan,
                            'glwnco'    => $arr_coa,
                        ]
                    ])->result_array();
                endif;
            endif;
            if($t == $anggaran->tahun_anggaran):
                $dt['renc'] = get_data('tbl_indek_besaran',[
                    'select' => 'tahun_core,hasil12 as total,coa,parent_id',
                    'where'  => [
                        'coa' => $arr_coa,
                        'kode_anggaran' => $anggaran->kode_anggaran,
                        'kode_cabang'   => $kode_cabang,
                    ]
                ])->result_array();
            endif;
        }

        $data['data']       = $dt;
        $data['group']      = $arr_group;
        $data['anggaran']   = $anggaran;
        $data['kode_cabang']= $kode_cabang;
        $data['detail_tahun']      = $detail_tahun;
        $view = $this->load->view($this->path.'data_kantor/table',$data,true);
     
        $data = [
            'status'=> true,
            'data'  => $view,
        ];

        render($data,'json');
    }

    function save(){
        $data = post();
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->row();

        // pengecekan save untuk cabang
        check_save_cabang($this->controller,$anggaran->kode_anggaran,$data['kode_cabang']);

        $data['kode_anggaran'] = user('kode_anggaran');
        $data['keterangan_anggaran'] = $anggaran->keterangan;   
        $cek = get_data('tbl_plan_berita_acara',[
            'where' => [
                'kode_anggaran' => user('kode_anggaran'),
                'kode_cabang'   => $data['kode_cabang']
            ]
        ])->row();

        if(!$cek) {
            $response = insert_data('tbl_plan_berita_acara',$data,post(':validation'));
        }else{
            $data_update = $data;
            $data_update['keterangan_anggaran'] = $anggaran->keterangan;
            $data_update['kode_anggaran'] = user('kode_anggaran');
            unset($data_update['kode_cabang']);
            unset($data_update['nama_cabang']);
            unset($data_update['id']);

            $response = update_data('tbl_plan_berita_acara',$data_update,[
                'kode_anggaran'=>user('kode_anggaran'),
                'kode_cabang'=>$data['kode_cabang']
            ]);
        }

        if($response) {
            $ID = get_data('tbl_m_data_kantor',[
                'where' => [
                    'kode_cabang' => $data['kode_cabang'],
                    'kode_anggaran' => user('kode_anggaran'),
                ]
            ])->row_array();
            if($ID):
                $data['id'] = $ID['id'];
            else:
                unset($data['id']);
            endif;

            $response = save_data('tbl_m_data_kantor',$data,[],true);
        }
            
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

        $header = json_decode(post('header'),true);
        $dt     = json_decode(post('data'),true);

        $data = [];
        foreach($dt as $k => $v){
            $detail = [
                $v[0],
                $v[1],
            ];
            for ($i=2; $i < count($v) ; $i++) { 
                $detail[] = filter_money($v[$i]);
            }
            $data[] = $detail;
        }

        $config[] = $this->data_kantor($kode_anggaran,$kode_cabang);
        $config[] = [
            'title' => lang('berita_acara').' ('.get_view_report().')',
            'header' => $header[1],
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'data_kantor_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }

    private function data_kantor($kode_anggaran,$kode_cabang){
        $cab = get_data('tbl_plan_berita_acara',[
            'where' =>[
                'kode_anggaran' => $kode_anggaran,
                'kode_cabang'   => $kode_cabang,
            ],
        ])->row_array();

        $header    = ['',''];
        $data   = [];

        $data[] = [lang('kode_cabang'),$kode_cabang];
        $data[] = [lang('nama_kantor'),$this->check_value($cab,'nama_kantor')];
        $data[] = [lang('pimpinan'),$this->check_value($cab,'nama_pimpinan')];
        $data[] = [lang('no_hp_pimpinan'),$this->check_value($cab,'no_hp_pimpinan')];
        $data[] = [lang('mulai_menjabat'),$this->check_value($cab,'tgl_mulai_menjabat','date')];
        $data[] = [lang('nama_cp'),$this->check_value($cab,'nama_cp')];
        $data[] = [lang('no_hp_cp'),$this->check_value($cab,'no_hp_cp')];
        $data[] = [lang('nama_cp').' 2',$this->check_value($cab,'nama_cp2')];
        $data[] = [lang('no_hp_cp').' 2',$this->check_value($cab,'no_hp_cp2')];
        $data[] = [lang('email_kantor'),$this->check_value($cab,'email_Cp')];
        $data[] = [lang('email_kantor_lainnya'),$this->check_value($cab,'email_lainnya')];

        $config = [
            'title' => lang('data_kantor'),
            'header' => $header,
            'data'  => $data,
        ];

        return $config;
    }

    private function check_value($data,$name,$type=""){
        $val = '';
        if(isset($data[$name])):
            $val = $data[$name];
        endif;

        if($val && $type == 'date'):
            $val = c_date($val);
        endif;

        return $val;
    }
}       