<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Plan_rencana_kerja_fungsi extends BE_Controller {
    var $path       = 'transaction/budget_planner/kantor_pusat/';
    var $sub_menu   = 'transaction/budget_planner/sub_menu';
    var $controller = 'plan_rencana_kerja_fungsi';
    function __construct() {
        parent::__construct();
    }
    
    function index($p1="") {
        $a = get_access('plan_rencana_kerja_fungsi');
        $data = cabang_divisi('plan_data_kantor');
        $data['path']     = $this->path;
        $data['sub_menu'] = $this->sub_menu;
        $data['access_additional']  = $a['access_additional'];
        $data['access_edit']        = $a['access_edit'];
        render($data,'view:'.$this->path.'rencana_kerja_fungsi/index');
    }

    function get_kebijakan_umum($type="echo"){
        $ls             = get_data('tbl_kebijakan_umum a',[
            'where'     => [
                'a.is_active' => 1,
            ]
        ])->result();
        $data           = '<option value=""></option>';
        foreach($ls as $e2) {
            $data       .= '<option value="'.$e2->id.'">'.$e2->nama.'</option>';
        }

        if($type == 'echo') echo $data;
        else return $data;  
    }
    function get_perspektif($type="echo"){
        $ls             = get_data('tbl_perspektif a',[
            'where'     => [
                'a.is_active' => 1,
            ]
        ])->result();
        $data           = '<option value=""></option>';
        foreach($ls as $e2) {
            $data       .= '<option value="'.$e2->id.'">'.$e2->nama.'</option>';
        }

        if($type == 'echo') echo $data;
        else return $data;  
    }
    function get_skala_program($type="echo"){
        $ls             = get_data('tbl_skala_program a',[
            'where'     => [
                'a.is_active' => 1,
            ]
        ])->result();
        $data           = '<option value=""></option>';
        foreach($ls as $e2) {
            $data       .= '<option value="'.$e2->id.'">'.$e2->nama.'</option>';
        }

        if($type == 'echo') echo $data;
        else return $data;
    }

    function save(){
        $kode_cabang = post('kode_cabang');
        $ckode_anggaran = user('kode_anggaran');

        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$ckode_anggaran)->row();
        $cabang   = get_data('tbl_m_cabang',[
            'where' => "kode_cabang = '".user('kode_cabang')."' and kode_anggaran = '$ckode_anggaran'"
        ])->row();
        $tahun    = $anggaran->tahun_anggaran;
        $rkf_bulan= rkf_bulan($anggaran->kode_anggaran);

        $dt_id      = post('dt_id');
        $dt_key     = post('dt_key');
        $kebijakan_umum = post('kebijakan_umum');
        $program_kerja  = post('program_kerja');
        $perspektif     = post('perspektif');
        $status_program = post('status_program');
        $skala_program  = post('skala_program');
        $tujuan         = post('tujuan');
        $output         = post('output');
        $target_finansial = post('target_finansial');

        $this->validate_save();

        $arrID = array();
        if($dt_key):
            foreach ($dt_key as $k => $v) {
                $key    = $v;
                $produk = 0;
                $anggaran_select = "0";
                $divisi_terkait  = [];
                $pic  = [];
                $x = post('produk'.$key);
                if(isset($x[0])): $produk = $x[0]; endif;
                $x = post('anggaran'.$key);
                if(isset($x[0])): if($x[0]): $anggaran_select = $x[0]; endif; endif;

                $x = post('divisi_terkait_'.$key);
                if(isset($x[0])): if($x[0]): $divisi_terkait = $x; endif; endif;

                $x = post('pic'.$key);
                if(isset($x[0])): if($x[0]): $pic = $x; endif; endif;
                
                $c = [
                    'kode_anggaran' => $ckode_anggaran,
                    'keterangan_anggaran' => $anggaran->keterangan,
                    'tahun'         => $anggaran->tahun_anggaran,
                    'kode_cabang'   => $kode_cabang,
                    'cabang'        => $cabang->nama_cabang,
                    'username'      => user('username'),
                    'id_kebijakan_umum'  => $kebijakan_umum[$k],
                    'id_perspektif'      => $perspektif[$k],
                    'id_skala_program'   => $skala_program[$k],
                    'program_kerja'      => $program_kerja[$k],
                    'produk'             => $produk,
                    'status_program'     => $status_program[$k],
                    'anggaran'           => $anggaran_select,
                    'tujuan'             => $tujuan[$k],
                    'output'             => $output[$k],
                    'target_finansial'   => $target_finansial[$k],
                    'divisi_terkait'   => json_encode($divisi_terkait),
                    'pic'       => json_encode($pic),
                ];
                $cek = get_data('tbl_input_rkf',[
                    'where'         => [
                        'kode_anggaran'   => $ckode_anggaran,
                        'kode_cabang'     => $kode_cabang,
                        'tahun'           => $tahun,
                        'id' => $dt_id[$k],
                    ],
                ])->row();

                if(!isset($cek->id)) {
                    $dt_insert = insert_data('tbl_input_rkf',$c);
                    $ID = $dt_insert;
                    array_push($arrID, $dt_insert);
                }else{
                    $ID = $cek->id;
                    update_data('tbl_input_rkf',$c,['kode_anggaran'   => $ckode_anggaran,
                        'kode_cabang'     => $kode_cabang,
                        'tahun'           => $tahun,
                        'id' => $dt_id[$k]]);
                    array_push($arrID, $dt_id[$k]);
                }

                // insert detail
                $arrID_detail = [];
                for ($i=1; $i <= 12 ; $i++) { 
                    $uraian = '';
                    $p_uraian = post('bulan_'.$key.'_'.$i);
                    if(isset($p_uraian) && $p_uraian[0]){
                        $uraian = $p_uraian[0];
                    }

                    $bobot = '';
                    $p_bobot = post('bobot_'.$key.'_'.$i);
                    if(isset($p_bobot) && $p_bobot[0]){
                        $bobot = $p_bobot[0];
                    }

                    if($uraian || $bobot):
                        $bobot = str_replace('.', '', $bobot);
                        $bobot = str_replace(',', '.', $bobot);
                        $dt_saved_detail = [
                            'id_input_rkf'  => $ID,
                            'bulan'         => $i,
                            'uraian'        => $uraian,
                            'bobot'         => $bobot,
                        ];
                        $ck_detail = get_data('tbl_input_rkf_detail',[
                            'select' => 'id',
                            'where'  => [
                                'id_input_rkf'  => $ID,
                                'bulan'         => $i
                            ]
                        ])->row();
                        if($ck_detail):
                            $dt_saved_detail['id'] = $ck_detail->id;
                        endif;
                        $res = save_data('tbl_input_rkf_detail',$dt_saved_detail);

                        // pengecekan status uraian
                        if(isset($res['id']) && $rkf_bulan):
                            $rkf_detail = get_data('tbl_input_rkf_detail','id',$res['id'])->row();
                            $ck_rkf_bulan = get_data('tbl_input_rkf_detail_status',[
                                'where' => [
                                    'id_input_rkf_detail' => $res['id'],
                                    'bulan' => $rkf_bulan
                                ]
                            ])->row();
                            if(!$ck_rkf_bulan):
                                $data_rkf_bulan = [
                                    'id_input_rkf_detail' => $res['id'],
                                    'bulan'     => $rkf_bulan,
                                    'status'    => $rkf_detail->status,
                                ];
                                $res2 = save_data('tbl_input_rkf_detail_status',$data_rkf_bulan,[],true);
                            endif;
                        endif;

                        array_push($arrID_detail, $res['id']);
                    endif;
                }
                if(count($arrID_detail)>0):
                    delete_data('tbl_input_rkf_detail',['id_input_rkf'=>$ID,'id not'=>$arrID_detail]);
                else:
                    delete_data('tbl_input_rkf_detail',['id_input_rkf'=>$ID]);
                endif;

            }
        endif;

        if(count($arrID)>0 && post('id')):
            // delete_data('tbl_input_rkf',['kode_anggaran'=>$ckode_anggaran,'id not'=>$arrID,'kode_cabang'=>$kode_cabang,'tahun'=>$tahun]);
        elseif(post('id')):
            delete_data('tbl_input_rkf',['kode_anggaran'=>$ckode_anggaran,'kode_cabang'=>$kode_cabang,'id' => post('id')]);
        endif;

        render([
            'status'    => 'success',
            'message'   => lang('data_berhasil_disimpan'),
        ],'json');
    }

    private function validate_save(){
        $dt_id      = post('dt_id');
        $dt_key     = post('dt_key');
        $kebijakan_umum = post('kebijakan_umum');
        
        if($dt_key):
            foreach ($dt_key as $k => $v) {
                $key    = $v;

                $total_bobot = 0;
                for ($i=1; $i <= 12 ; $i++) {
                    $uraian = '';
                    $p_uraian = post('bulan_'.$key.'_'.$i);
                    if(isset($p_uraian) && $p_uraian[0]){
                        $uraian = $p_uraian[0];
                    }

                    $bobot = '';
                    $p_bobot = post('bobot_'.$key.'_'.$i);
                    if(isset($p_bobot) && $p_bobot[0]){
                        $bobot = $p_bobot[0];
                    }

                    if($uraian || $bobot):
                        $bobot = str_replace('.', '', $bobot);
                        $bobot = str_replace(',', '.', $bobot);

                        $total_bobot += (float) $bobot;
                    endif;
                }

                if((int) $total_bobot != 100):
                    render([
                        'status'    => 'error',
                        'message'   => 'Total Bobot harus 100, Total Bobot yang Anda masukan adalah '.$total_bobot,
                    ],'json');
                    exit();
                endif;
            }
        endif;
    }

    function save_perubahan() {       
        $data   = json_decode(post('json'),true);
        foreach($data as $id => $record) {          
            update_data('tbl_input_rkf',$record,'id',$id); }
    }

    function data($anggaran="", $cabang="", $tipe = 'table'){
        $menu = menu();
        $ckode_anggaran = $anggaran;
        $ckode_cabang = $cabang;

        $dt_cabang = get_data('tbl_m_cabang',[
            'where' => [
                'kode_cabang' => $ckode_cabang,
                'kode_anggaran' => $ckode_anggaran
            ]
        ])->row();
        $kode_cabang_divisi = $dt_cabang->kode_cabang;
        if($dt_cabang->level4):
            $dt_cabang = get_data('tbl_m_cabang','id',$dt_cabang->parent_id)->row();
            $kode_cabang_divisi = $dt_cabang->kode_cabang;
        endif;

        $data_finish['kode_anggaran']   = $ckode_anggaran;
        $data_finish['kode_cabang']     = $ckode_cabang;
        $access = get_access('plan_rencana_kerja_fungsi',$data_finish);
        $access_edit = false;
        if($access['access_edit'] && $ckode_cabang):
            $access_edit = true;
        elseif($access['access_edit'] && $access['access_additional']):
            $access_edit = true;
        endif;

        $data['akses_ubah'] = $access_edit;
        $data['cabang'] = $cabang;
        $data['kode_anggaran'] = $ckode_anggaran;

        $arr = ['select'    => '
                    a.*,
                    b.nama as kebijakan_umum,
                    c.nama as perspektif,
                    d.nama as skala_program,
                    e.level4,
                    e.parent_id,
                ',];

        $arr['join'][] = 'tbl_kebijakan_umum b on b.id = a.id_kebijakan_umum';
        $arr['join'][] = 'tbl_perspektif c on c.id = a.id_perspektif';
        $arr['join'][] = 'tbl_skala_program d on d.id = a.id_skala_program';
        $arr['join'][] = "tbl_m_cabang e on e.kode_cabang = a.kode_cabang and e.kode_anggaran = '$ckode_anggaran'";
        $arr['order_by'] = 'a.id';

        $arr_list = $arr;
        if($anggaran) {
            $arr_list['where']['a.kode_anggaran']  = $ckode_anggaran;
        }
        if($cabang) {
            $arr_list['where']['a.kode_cabang']  = $ckode_cabang;
        }

        $list = get_data('tbl_input_rkf a',$arr_list)->result();
        
        $arr_list = $arr;  
        $arr_list['like']['a.divisi_terkait'] = $kode_cabang_divisi;
        if($anggaran) {
            $arr_list['where_array']['a.kode_anggaran']  = $ckode_anggaran;
        }
        if($cabang) {
            $arr_list['where']['a.kode_cabang !=']  = $ckode_cabang;
        }
        $list2 = get_data('tbl_input_rkf a',$arr_list)->result(); // divisi terkait

        $data['list']     = $list;
        $data['current_cabang'] = $cabang;

        $arr_bulan = [];
        for ($i=1; $i <= 12 ; $i++) { 
            $arr_bulan[$i] = month_lang($i);
        }

        $data2 = $data;
        $data2['list'] = $list2;
        $response   = array(
            'table' => $this->load->view($this->path.'rencana_kerja_fungsi/table',$data,true),
            'table2' => $this->load->view($this->path.'rencana_kerja_fungsi/table',$data2,true),
            'access_edit'       => $access_edit,
            'arr_bulan'         => $arr_bulan,
        );
       
        render($response,'json');
    }

    function get_data(){
        $list = get_data('tbl_input_rkf',[
            'where'         => [
                'id'   => post('id'),
            ]
        ])->result();

        $detail_rkf = [];
        foreach ($list as $k => $v) {
            $divisi_terkait = $v->divisi_terkait;
            if($divisi_terkait):
                $list[$k]->divisi_terkait = json_decode($divisi_terkait);
            else:
                $list[$k]->divisi_terkait = [];
            endif;

            $pic = $v->pic;
            if($pic):
                $list[$k]->pic = json_decode($pic);
            else:
                $list[$k]->pic = [];
            endif;
            
            $dt_rkf = get_data('tbl_input_rkf_detail','id_input_rkf',$v->id)->result();
            if(count($dt_rkf)>0):
                $detail_rkf[$v->id] = $dt_rkf;
            endif;
            $d = $v;
        }

        render([
            'status'    => 'success',
            'data'      => $list,
            'detail'    => $d,
            'detail_rkf'=> $detail_rkf,
        ],'json');
    }

    function detail($id,$cabang){
        $arr = ['select'    => '
            a.*,
            b.nama as kebijakan_umum,
            c.nama as perspektif,
            d.nama as skala_program,
            e.level4,
            e.parent_id,
            e.nama_cabang,
        ',];
        $arr['join'][] = 'tbl_kebijakan_umum b on b.id = a.id_kebijakan_umum';
        $arr['join'][] = 'tbl_perspektif c on c.id = a.id_perspektif';
        $arr['join'][] = 'tbl_skala_program d on d.id = a.id_skala_program';
        $arr['join'][] = "tbl_m_cabang e on e.kode_cabang = a.kode_cabang and e.kode_anggaran = a.kode_anggaran";
        $arr['where']['a.id'] = $id;

        $data = get_data('tbl_input_rkf a',$arr)->row_array();

        if(isset($data['id'])) {
            $detail = get_data('tbl_input_rkf_detail',[
                'where' => [
                    'id_input_rkf' => $data['id'],
                ],
                'order_by' => 'bulan',
            ])->result();

            $data['detail'] = $detail;
            render($data,'layout:false view:'.$this->path.'rencana_kerja_fungsi/detail');
        } else echo lang('tidak_ada_data');
    }

    function template(){
        ini_set('memory_limit', '-1');
        $arr = [
            'KODE KEBIJAKAN UMUM DIREKSI',
            'PROGRAM KERJA',
            'PRODUK / AKTIVITAS BARU',
            'PERSPEKTIF',
            'STATUS PROGRAM',
            'SKALA PROGRAM',
            'TUJUAN',
            'OUTPUT',
            'TARGET FINANSIAL',
            'ANGGARAN',
            'COA',
        ];
        for ($i=1; $i <= 12 ; $i++) { 
            $arr[] = month_lang($i);
        }
        $arr[] = 'Divisi Terkait';
        $arr[] = 'PIC';
        $arr[] = 'Jadwal Bulan';
        $arr[] = 'Uraian';
        $arr[] = 'Bobot';
        $arr[] = 'Divisi';
        $arr[] = 'Kode Divisi';
        $arr[] = 'Sub Divisi';
        $arr[] = 'Kode Sub Divisi';

        $config[] = [
            'title' => 'template_import_input_pkf',
            'header' => $arr,
        ];

        $arr = ['kode' => 'kode','nama' => 'Nama Kebijakan Umum','keterangan' => 'keterangan'];
        $data = get_data('tbl_kebijakan_umum')->result_array();

        $config[] = [
            'title' => 'data_kebijakan_umum',
            'data' => $data,
            'header' => $arr,
        ];

        $this->load->library('simpleexcel',$config);
        $this->simpleexcel->export();
    }

    function export(){
        ini_set('memory_limit', '-1');
        $kode_cabang    = post('kode_cabang');
        $kode_anggaran  = post('kode_anggaran');

        $config[] = $this->data_export();
        $config[] = $this->data_export(1);

        $this->load->library('simpleexcel',$config);
        $filename = 'input_rkf_'.str_replace(' ', '_', $kode_anggaran).'_'.$kode_cabang.'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }

    private function data_export($p1=0){
        $kode_cabang    = post('kode_cabang');
        $kode_anggaran  = post('kode_anggaran');

        $dt_cabang = get_data('tbl_m_cabang',[
            'where' => [
                'kode_cabang' => $kode_cabang,
                'kode_anggaran' => $kode_anggaran
            ]
        ])->row();
        $kode_cabang_divisi = $dt_cabang->kode_cabang;

        $div            = $dt_cabang->nama_cabang;
        $div_kode       = $kode_cabang;
        $sub_div        = $dt_cabang->nama_cabang;
        $sub_div_kode   = $kode_cabang;
        $level4         = $dt_cabang->level4;
        $parent_id      = $dt_cabang->parent_id;
        if($dt_cabang->level4):
            $dt_cabang = get_data('tbl_m_cabang','id',$dt_cabang->parent_id)->row();
            $kode_cabang_divisi = $dt_cabang->kode_cabang;
            $div            = $dt_cabang->nama_cabang;
            $div_kode       = $dt_cabang->kode_cabang;
        endif;

        $arr = ['select'    => '
                    a.*,
                    b.nama as kebijakan_umum,
                    c.nama as perspektif,
                    d.nama as skala_program,
                    e.level4,
                    e.parent_id,
                    e.nama_cabang,
                ',];
        if($p1==0):
            $title = "Input RKF";
            if($kode_anggaran) {
                $arr['where']['a.kode_anggaran']  = $kode_anggaran;
            }
            if($level4) {
                $arr['where']['e.parent_id']  = $parent_id;
            }else{
                $arr['where']['a.kode_cabang']  = $kode_cabang;
            }
        else:
            $arr['like']['a.divisi_terkait'] = $kode_cabang_divisi;
            if($kode_anggaran) {
                $arr['where_array']['a.kode_anggaran']  = $kode_anggaran;
            }
            if($level4) {
                $arr['where']['e.parent_id !=']  = $parent_id;
            }else{
                $arr['where']['a.kode_cabang !=']  = $kode_cabang;
            }
            $title = "Input RKF Divisi Terkait";
        endif;

        $arr['join'][] = 'tbl_kebijakan_umum b on b.id = a.id_kebijakan_umum';
        $arr['join'][] = 'tbl_perspektif c on c.id = a.id_perspektif';
        $arr['join'][] = 'tbl_skala_program d on d.id = a.id_skala_program';
        $arr['join'][] = 'tbl_m_cabang e on e.kode_cabang = a.kode_cabang and e.kode_anggaran = a.kode_anggaran';
        $arr['order_by'] = 'a.id';
        $list = get_data('tbl_input_rkf a',$arr)->result();

        $arr = [
            'KEBIJAKAN UMUM DIREKSI',
            'PROGRAM KERJA',
            'PRODUK / AKTIVITAS BARU',
            'PERSPEKTIF',
            'STATUS PROGRAM',
            'SKALA PROGRAM',
            'TUJUAN',
            'OUTPUT',
            'TARGET FINANSIAL',
            'ANGGARAN',
            'COA',
        ];
        for ($i=1; $i <= 12 ; $i++) { 
            $arr[] = month_lang($i);
        }
        $arr[] = 'Divisi Terkait';
        $arr[] = 'PIC';
        $arr[] = 'Jadwal Bulan';
        $arr[] = 'Uraian';
        $arr[] = 'Bobot';
        $arr[] = 'Divisi';
        $arr[] = 'Kode Divisi';
        $arr[] = 'Sub Divisi';
        $arr[] = 'Kode Sub Divisi';

        $data = [];
        foreach($list as $k => $v){
            $sub_div_kode = $v->kode_cabang;
            $sub_div      = remove_spaces($v->nama_cabang);

            $produk = 'Tidak'; if($v->produk == 1) $produk = 'Ya';
            $anggaran = 'Tidak'; if($v->anggaran == 1) $anggaran = 'Ya';

            $divisi_terkait = $v->divisi_terkait;
            $divisi = '';
            if($divisi_terkait):
                $divisi_terkait = json_decode($divisi_terkait,true);
                if(count($divisi_terkait)>0):
                    $kode_cabang_divisi = $v->kode_cabang;
                    if($v->level4):
                        $dt_cabang = get_data('tbl_m_cabang','id',$v->parent_id)->row();
                        $kode_cabang_divisi = $dt_cabang->kode_cabang;
                    endif;
                    $divisi_terkait[] = $kode_cabang_divisi;

                    $ls = get_data('tbl_m_cabang',[
                        'where' => [
                            'kode_cabang' => $divisi_terkait,
                            'kode_anggaran' => $kode_anggaran
                        ]
                    ])->result();
                    foreach ($ls as $kk => $vv) {
                        $divisi .= $vv->kode_cabang.' - '.remove_spaces($vv->nama_cabang).', ';
                    }
                endif;
            endif;
            
            // PIC
            $d_pic = '';
            if($v->pic):
                $pic = json_decode($v->pic,true);
                if(count($pic)>0):
                    $ls = get_data('tbl_m_pegawai','id',$pic)->result();
                    foreach ($ls as $kk => $vv) {
                        $d_pic .= $vv->nip.' - '.$vv->nama.', ';
                    }
                endif;
            endif;

            $h = [
                $v->kebijakan_umum,
                $v->program_kerja,
                $produk,
                $v->perspektif,
                $v->status_program,
                $v->skala_program,
                $v->tujuan,
                $v->output,
                $v->target_finansial,
                $anggaran,
                $v->coa,
            ];
            for ($i=1; $i <= 12 ; $i++) { 
                $filed = 'T_'.sprintf("%02d", $i);
                $h[]   = view_report($v->{$filed});
            }
            $h[] = $divisi;
            $h[] = $d_pic;
            
            // jadwal bulan
            $dt_jadwal = get_data('tbl_input_rkf_detail','id_input_rkf',$v->id)->result();
            $jadwal = '';
            $bobot  = '';
            $uraian = '';
            foreach($dt_jadwal as $v2){
                $jadwal .= $v2->bulan.', ';
                $uraian .= $v2->uraian.', ';
                $bobot  .= $v2->bobot.', ';
            }
            $h[] = $jadwal;
            $h[] = $uraian;
            $h[] = $bobot;  
            $h[] = $div;
            $h[] = $div_kode;
            $h[] = $sub_div;
            $h[] = $sub_div_kode;

            array_push($data,$h);
        }

        return [
            'title' => $title,
            'header' => $arr,
            'data'   => $data
        ];
    }

    function import(){
        ini_set('memory_limit', '-1');
        $file = post('fileimport');
        
        $kode_anggaran  = post('kode_anggaran');
        $dt_anggaran    = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
        $rkf_bulan      = rkf_bulan($kode_anggaran);

        $currency   = post('currency');
        $dt_currency = get_currency($currency);
 
        $col = [
            'id_kebijakan_umum',
            'program_kerja',
            'produk',
            'id_perspektif',
            'status_program',
            'id_skala_program',
            'tujuan',
            'output',
            'target_finansial',
            'anggaran',
            'coa',
        ];
        for ($i=1; $i <= 12 ; $i++) { 
            $filed = 'T_'.sprintf("%02d", $i);
            $col[] = $filed;
        }
        $col[] = 'divisi_terkait';
        $col[] = 'pic';
        $col[] = 'jadwal_bulan';
        $col[] = 'uraian';
        $col[] = 'bobot';
        $col[] = 'div';
        $col[] = 'div_kode';
        $col[] = 'sub_div';
        $col[] = 'sub_div_kode';

        $this->load->library('simpleexcel');
        $this->simpleexcel->define_column($col);
        $jml = $this->simpleexcel->read($file);
        $c = 0;
        $xx = [];
        foreach($jml as $i => $k) {
            if($i==0) {
                for($j = 2; $j <= $k; $j++) {
                    $data = $this->simpleexcel->parsing($i,$j);
                    $status = true;

                    $id_kebijakan_umum = $this->check_data('tbl_kebijakan_umum','kode',$data['id_kebijakan_umum']);
                    if($id_kebijakan_umum):
                        $data['id_kebijakan_umum'] = $id_kebijakan_umum;
                    else: $status = false; endif;

                    $id_perspektif = $this->check_data('tbl_perspektif','nama',$data['id_perspektif']);
                    if($id_perspektif):
                        $data['id_perspektif'] = $id_perspektif;
                    else: $status = false; endif;

                    
                    $id_skala_program = $this->check_data('tbl_skala_program','nama',$data['id_skala_program']);
                    if($id_skala_program):
                        $data['id_skala_program'] = $id_skala_program;
                    else: $status = false; endif;

                    if(remove_spaces(strtolower($data['anggaran'])) == 'ya') $data['anggaran'] = 1; else $data['anggaran'] = 0;
                    if(remove_spaces(strtolower($data['produk'])) == 'ya') $data['produk'] = 1; else $data['produk'] = 0;

                    $coa = explode('-', $data['coa']);
                    $data['coa'] = '';
                    if($data['anggaran'] == 1):
                        if(count($coa)>0) $data['coa'] = str_replace(' ','',$coa[0]);
                    endif;

                    for($a=1;$a <=12; $a++){
                        $field = 'T_'.sprintf("%02d",$a);
                        $value = 0;
                        if($data['anggaran'] == 1):
                            $value = (float) $data[$field] * $dt_currency['nilai'];
                            $data[$field] = $value;
                        endif;
                        $data[$field] = $value;
                    }

                    $divisi_terkait = explode(',',$data['divisi_terkait']);
                    $d_divisi = [];
                    foreach($divisi_terkait as $v){
                        $v = explode('-',$v);
                        if(count($v)>0 && strlen(str_replace(' ','',$v[0]))) $d_divisi[] = str_replace(' ', '', $v[0]);
                    }
                    $data['divisi_terkait'] = json_encode($d_divisi);

                    $pic = explode(',',$data['pic']);
                    $data['pic'] = [];
                    $d_pic = [];
                    foreach($pic as $v){
                        $v = explode('-',$v);
                        if(count($v)>0 && strlen(str_replace(' ','',$v[0]))) $d_pic[] = str_replace(' ', '', $v[0]);
                    }
                    if(count($d_pic)>0):
                        $ls_pic = get_data('tbl_m_pegawai','nip',$d_pic)->result();
                        foreach($ls_pic as $v){
                            $data['pic'][] = $v->id;
                        }
                    endif;
                    $data['pic'] = json_encode($data['pic']);

                    $data['kode_anggaran']       = $dt_anggaran->kode_anggaran;
                    $data['keterangan_anggaran'] = $dt_anggaran->keterangan;
                    $data['tahun']               = $dt_anggaran->tahun_anggaran;
                    $data['username']            = user('username');

                    $sub_div_kode = explode(',',$data['sub_div_kode']);
                    $d_sub_div    = [];
                    foreach($sub_div_kode as $v){
                        $v = explode('-',$v);
                        if(count($v)>0 && strlen(str_replace(' ','',$v[0]))) $d_sub_div[] = str_replace(' ', '', $v[0]);
                    }
                    if(count($d_sub_div)>0):
                        $ls_cabang = get_data('tbl_m_cabang',[
                            'where' => [
                                'kode_cabang' => $d_sub_div,
                                'kode_anggaran' => $kode_anggaran
                            ]
                        ])->result();
                        foreach($ls_cabang as $cabang){
                            $data['kode_cabang'] = '';
                            if($cabang):
                                $data['kode_cabang'] = $cabang->kode_cabang;
                                $data['cabang'] = $cabang->nama_cabang;
                            endif;

                            if($status):
                                $save = save_data('tbl_input_rkf',$data);
                                if($save):
                                    $c++;

                                    $jadwal_bulan   = explode(',',$data['jadwal_bulan']);
                                    $uraian         = explode(',',$data['uraian']);
                                    $bobot          = explode(',',$data['bobot']);

                                    $data_batch = [];
                                    foreach($jadwal_bulan as $k2 => $v){
                                        $v = str_replace(' ','',$v);
                                        $h = [];
                                        $h['id_input_rkf']  = $save['id'];
                                        $h['bulan']         = $v;
                                        if(isset($uraian[$k2])) $h['uraian'] = $uraian[$k2];
                                        if(isset($bobot[$k2])) $h['bobot'] = $bobot[$k2];

                                        $res = save_data('tbl_input_rkf_detail',$h);
                                        // pengecekan status uraian
                                        if(isset($res['id']) && $rkf_bulan):
                                            $rkf_detail = get_data('tbl_input_rkf_detail','id',$res['id'])->row();
                                            $ck_rkf_bulan = get_data('tbl_input_rkf_detail_status',[
                                                'where' => [
                                                    'id_input_rkf_detail' => $res['id'],
                                                    'bulan' => $rkf_bulan
                                                ]
                                            ])->row();
                                            if(!$ck_rkf_bulan):
                                                $data_rkf_bulan = [
                                                    'id_input_rkf_detail' => $res['id'],
                                                    'bulan'     => $rkf_bulan,
                                                    'status'    => $rkf_detail->status,
                                                ];
                                                $res2 = save_data('tbl_input_rkf_detail_status',$data_rkf_bulan,[],true);
                                            endif;
                                        endif;
                                    }
                                endif;
                            endif;
                        }
                    endif;
                }
            }
        }
        @unlink($file);
        $response = [
            'status' => 'success',
            'message' => $c.' '.lang('data_berhasil_disimpan').'.'
        ];
        render($response,'json');
    }

    private function check_data($table,$name,$value){
        $value  = strtolower(remove_spaces($value));
        $dt     = get_data($table,$name,$value)->row();
        $id     = 0;
        if($dt):
            $id = $dt->id;
        else:
            $dt = get_data($table,['limit' => 1])->row();
            if($dt) $id = $dt->id;
        endif;
        return $id;
    }

    function keterangan_view(){
        $kode_cabang    = post('kode_cabang');
        $kode_anggaran  = post('kode_anggaran');

        $data_finish['kode_anggaran']   = $kode_anggaran;
        $data_finish['kode_cabang']     = $kode_cabang;
        $access = get_access($this->controller,$data_finish);
        $access_edit = false;
        if($access['access_edit']):
            $access_edit = true;
        elseif($access['access_edit'] && $access['access_additional']):
            $access_edit = true;
        endif;
        // pengecekan akses cabang
        check_access_divisi($this->controller,$kode_anggaran,$kode_cabang,$access);

        $dt_keterangan = get_data('tbl_note',[
            'select' => 'keterangan,id',
            'where'  => [
                'page'          => $this->controller,
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
        check_save_divisi($this->controller,$kode_anggaran,$kode_cabang,'','access_edit');

        $ck = get_data('tbl_note',[
            'where' => [
                'page'          => $this->controller,
                'kode_cabang'   => $kode_cabang,
                'kode_anggaran' => $kode_anggaran
            ]
        ])->row();

        $data = post();
        $data['id'] = '';
        $data['page'] = $this->controller;
        if($ck):
            $data['id'] = $ck->id;
        endif;
        $response = save_data('tbl_note',$data,post(':validation'));
        render($response,'json');
    }
}