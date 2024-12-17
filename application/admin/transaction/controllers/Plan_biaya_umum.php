<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Plan_biaya_umum extends BE_Controller {
    var $path       = 'transaction/budget_planner/kantor_pusat/';
    var $sub_menu   = 'transaction/budget_planner/sub_menu';
    var $controller = 'plan_biaya_umum';
    var $detail_tahun;
    var $kode_anggaran;
    var $anggaran;
    function __construct() {
        parent::__construct();
        $this->kode_anggaran  = user('kode_anggaran');
        $this->anggaran       = get_data('tbl_tahun_anggaran','kode_anggaran',$this->kode_anggaran)->row();
        $this->detail_tahun   = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'distinct a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => [
                'a.kode_anggaran' => $this->kode_anggaran,
                'a.tahun'         => $this->anggaran->tahun_anggaran
            ],
            'order_by' => 'tahun,bulan'
        ])->result();
    }
    
    function index($p1="") { 
        $a = get_access('plan_biaya_umum');
        $data = cabang_divisi();
        $data['path']     = $this->path;
        $data['sub_menu'] = $this->sub_menu;
        $data['access_additional'] = $a['access_additional'];
        $data['access_edit'] = $a['access_edit'];
        $data['detail_tahun']= $this->detail_tahun;
        $data['controller'] = $this->controller;
        render($data,'view:'.$this->path.'biaya_umum/index');
    }

    private function create_coa_default($anggaran,$kode_cabang){
        $cabang      = get_data('tbl_m_cabang',[
            'where' => [
                'kode_cabang' => $kode_cabang,
                'kode_anggaran' => $anggaran->kode_anggaran
            ]
        ])->row();

        $ls             = get_data('tbl_m_biaya_rkf a',[
            'select'    => 'a.coa as glwnco, b.glwdes',
            'where'     => [
                'a.is_active'       => 1,
                'a.kode_anggaran'   => $anggaran->kode_anggaran,
                'a.is_default'      => 1,
            ],
            'join'      => "tbl_m_coa b on a.coa = b.glwnco and b.kode_anggaran = a.kode_anggaran"
        ])->result();
            
        foreach($ls as $v){
            $nama = 'default__'.'Kegiatan '.remove_spaces($v->glwdes).' Rutin';
            $ck = get_data('tbl_divisi_rutin',[
                'select'    => 'id',
                'where'     => [
                    'kode_anggaran' => $anggaran->kode_anggaran,
                    'kode_cabang'   => $kode_cabang,
                    'coa'           => $v->glwnco,
                    'kegiatan'      => $nama,
                ]
            ])->row();
            if(!$ck):
                $data = [
                    'kode_anggaran' => $anggaran->kode_anggaran,
                    'kode_cabang'   => $kode_cabang,
                    'coa'           => $v->glwnco,
                    'kegiatan'      => $nama,
                    'keterangan_anggaran' => $anggaran->keterangan,
                    'tahun'         => $anggaran->tahun_anggaran,
                    'cabang'        => $cabang->nama_cabang,
                    'username'      => user('username'),
                    'create_by'     => user('username'),
                    'create_at'     => date("Y-m-d H:i:s"),
                    'urutan'        => 0,
                ];
                insert_data('tbl_divisi_rutin',$data);
            endif;
        }
    }

    function get_coa($type = 'echo'){
        $ls             = get_data('tbl_m_biaya_rkf a',[
            'select'    => 'a.coa as glwnco, b.glwdes',
            'where'     => "a.is_active = 1 and a.kode_anggaran = '".user('kode_anggaran')."'",
            'join'      => "tbl_m_coa b on a.coa = b.glwnco and b.kode_anggaran = '".user('kode_anggaran')."'"
        ])->result();
        $data           = '<option value=""></option>';
        foreach($ls as $e2) {
            $data       .= '<option value="'.$e2->glwnco.'">'.$e2->glwnco.' - '.remove_spaces($e2->glwdes).'</option>';
        }

        if($type == 'echo') echo $data;
        else return $data;
    }

    function data($anggaran="", $cabang="", $tipe = 'table') {
        $kode_cabang = $cabang;
        $status_group = post('status_group');
        // $status_group = 1;
        if($status_group == 1):
            $cab     = get_data('tbl_m_cabang','id',$kode_cabang)->row();
            $cab     = get_data('tbl_m_cabang',[
                'where' => [
                    'kode_cabang' => $cab->kode_cabang,
                    'kode_anggaran' => $anggaran
                ]
            ])->row_array();
        else:
            $cab     = get_data('tbl_m_cabang',[
                'where' => [
                    'kode_cabang' => $kode_cabang,
                    'kode_anggaran' => $anggaran
                ]
            ])->row_array();
        endif;

        if(!isset($cab['id'])):
            render(['status' => false,'message' => 'cabang not found'],'json');exit();
        endif;
        $cabang = $cab['kode_cabang'];
        $ckode_anggaran = $anggaran;
        $ckode_cabang = $cabang;

        $data_finish['kode_anggaran']   = $ckode_anggaran;
        $data_finish['kode_cabang']     = $ckode_cabang;
        $access = get_access('plan_biaya_umum',$data_finish);
        $access_edit = false;
        if($access['access_edit'] && $ckode_cabang):
            $access_edit = true;
        elseif($access['access_edit'] && $access['access_additional']):
            $access_edit = true;
        endif;
        $data['akses_ubah'] = $access_edit;

        $data['current_cabang'] = $cabang;

        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$ckode_anggaran)->row();
        // pengecekan akses divisi
        if(!$anggaran):
            render(['status' => false, 'message' => 'anggaran not found'],'json');exit();
        endif;
        check_access_divisi($this->controller,$ckode_anggaran,$ckode_cabang,$access);
        $this->create_coa_default($anggaran,$cabang);
              
        $arr            = [
            'select'    => '
                a.*,
                b.glwnco,
                b.glwdes,
            ',
        ];

        if($anggaran) {
            $arr['where']['a.kode_anggaran']  = $ckode_anggaran;
        }
        
        if($cabang) {
            $arr['where']['a.kode_cabang']  = $ckode_cabang;
        }
        $arr['like']['a.kegiatan'] = 'default__';
        $arr['join']     = 'tbl_m_coa b on b.glwnco = a.coa and b.kode_anggaran = a.kode_anggaran';
        $arr['order_by']  = 'a.urutan,a.id';
        $list = get_data('tbl_divisi_rutin a',$arr)->result();
        $data['list'] = $list;

        // header
        $arrHeader = array();
        foreach ($list as $k => $v) {
            $name = strtolower($v->kegiatan);
            $name = preg_replace("/[^a-z0-9]+/", "", $name);
            if(isset($data['count_'.$name])):
                $data['count_'.$name] += 1;
            else:
                $data['count_'.$name] = 1;
            endif;

            if(!in_array($v->kegiatan,$arrHeader)):
                array_push($arrHeader,$v->kegiatan);
            endif;
        }
        $data['header']     = $arrHeader;
        $coa                = $this->get_coa('data');    
        $response   = array(
            'status'        => true,
            'table'         => $this->load->view($this->path.'biaya_umum/table',$data,true),
            'access_edit'   => $access_edit,
            'coa'           => $coa,
        );
       
        render($response,'json');
    }

    function save_perubahan() {       
        $data   = json_decode(post('json'),true);
        $kode_cabang = post('kode_cabang');
        $kode_anggaran = post('kode_anggaran');

        $status_group = post('status_group');
        // $status_group = 1;
        if($status_group == 1):
            $cabang     = get_data('tbl_m_cabang','id',$kode_cabang)->row();
            $cabang     = get_data('tbl_m_cabang',[
                'where' => [
                    'kode_cabang' => $cabang->kode_cabang,
                    'kode_anggaran' => $kode_anggaran
                ]
            ])->row_array();
        else:
            $cabang     = get_data('tbl_m_cabang',[
                'where' => [
                    'kode_cabang' => $kode_cabang,
                    'kode_anggaran' => $kode_anggaran
                ]
            ])->row_array();
        endif;

        if(!isset($cabang['id'])):
            render(['status' => 'failed','message' => 'cabang not found'],'json');exit();
        endif;
        $kode_cabang = $cabang['kode_cabang'];

        // pengecekan save divisi
        check_save_divisi($this->controller,$kode_anggaran,$kode_cabang,'','access_edit');

        foreach($data as $id => $record) {
            $dt = insert_view_report_arr($record);
            if(isset($dt['pd_bulan'])):
                for ($i=1; $i <= 12 ; $i++) { 
                    $field  = 'T_' . sprintf("%02d", $i);
                    $dt[$field] = $dt['pd_bulan'];
                }
            endif;
            update_data('tbl_divisi_rutin',$dt,'id',$id); 
        }

        render(['status' => true,'message' => lang('data_berhasil_diperbaharui')],'json');
    }

    function keterangan_view(){
        $kode_cabang    = post('kode_cabang');
        $kode_anggaran  = post('kode_anggaran');

        $status_group = post('status_group');
        // $status_group = 1;
        if($status_group == 1):
            $cabang     = get_data('tbl_m_cabang','id',$kode_cabang)->row();
            $cabang     = get_data('tbl_m_cabang',[
                'where' => [
                    'kode_cabang' => $cabang->kode_cabang,
                    'kode_anggaran' => $kode_anggaran
                ]
            ])->row_array();
        else:
            $cabang     = get_data('tbl_m_cabang',[
                'where' => [
                    'kode_cabang' => $kode_cabang,
                    'kode_anggaran' => $kode_anggaran
                ]
            ])->row_array();
        endif;

        if(!isset($cabang['id'])):
            render(['status' => false,'message' => 'cabang not found'],'json');exit();
        endif;
        $kode_cabang = $cabang['kode_cabang'];

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
            'kode_cabang' => $kode_cabang,
        ],'json');
    }

    function save_keterangan(){
        $kode_anggaran  = post('kode_anggaran');
        $kode_cabang    = post('kode_cabang');
        $keterangan     = post('keterangan');

        $status_group = post('status_group');
        // $status_group = 1;
        if($status_group == 1):
            $cabang     = get_data('tbl_m_cabang','id',$kode_cabang)->row();
            $cabang     = get_data('tbl_m_cabang',[
                'where' => [
                    'kode_cabang' => $cabang->kode_cabang,
                    'kode_anggaran' => $kode_anggaran
                ]
            ])->row_array();
        else:
            $cabang     = get_data('tbl_m_cabang',[
                'where' => [
                    'kode_cabang' => $kode_cabang,
                    'kode_anggaran' => $kode_anggaran
                ]
            ])->row_array();
        endif;

        if(!isset($cabang['id'])):
            render(['status' => false,'message' => 'cabang not found'],'json');exit();
        endif;
        $kode_cabang = $cabang['kode_cabang'];

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
        $response = save_data('tbl_note',$data,post(':validation'),true);
        render($response,'json');
    }

    function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_anggaran      = post('kode_anggaran');

        $kode_cabang        = post('kode_cabang');
        $kode_cabang_txt    = post('kode_cabang_txt');

        $status_group = post('status_group');
        // $status_group = 1;
        if($status_group == 1):
            $cabang     = get_data('tbl_m_cabang','id',$kode_cabang)->row();
            $cabang     = get_data('tbl_m_cabang',[
                'where' => [
                    'kode_cabang' => $cabang->kode_cabang,
                    'kode_anggaran' => $kode_anggaran
                ]
            ])->row_array();
        else:
            $cabang     = get_data('tbl_m_cabang',[
                'where' => [
                    'kode_cabang' => $kode_cabang,
                    'kode_anggaran' => $kode_anggaran
                ]
            ])->row_array();
        endif;

        if(!isset($cabang['id'])):
            render(['status' => false,'message' => 'cabang not found'],'json');exit();
        endif;
        $kode_cabang = $cabang['kode_cabang'];

        // pengecekan akses cabang
        $access = get_access($this->controller);
        check_access_divisi($this->controller,$kode_anggaran,$kode_cabang,$access);

        $dt = json_decode(post('data'),true);

        $header = $dt['#result1']['header'][0];

        $data = [];
        foreach(['#result1'] as $name){
            if(isset($dt[$name])):
                $count2 = 0;
                foreach($dt[$name]['data'] as $k => $v){
                    $count2 = count($v);
                    $detail = [
                        $v[0],
                        $v[1],
                        $v[2],
                        $v[3],
                    ];
                    for ($i=4; $i < (count($v)) ; $i++) { 
                        $detail[] = filter_money($v[$i]);
                    }
                    $data[] = $detail;
                }
            endif;
        }

        $config[] = [
            'title' => 'Input Biaya Umum'.' ('.get_view_report().')',
            'header' => $header,
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'input_biaya_umum_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }

    private function convert_data_list(){
        $list = get_data('tbl_divisi_rutin a',[
            'select' => '
                a.*,
                c.kode_cabang as kode_div,
                c.nama_cabang as nama_div,
            ',
            'join' => [
                "tbl_m_cabang b on b.kode_cabang = a.kode_cabang and b.kode_anggaran = a.kode_anggaran",
                "tbl_m_cabang c on c.id = b.parent_id",
            ],
            'like' => [
                'a.kegiatan' => 'default__'
            ],
            'where' => [
                'b.status_group' => 0,
            ]
        ])->result();

        $data = [];
        foreach ($list as $k => $v) {
            $key = $v->kode_div.'_'.$v->kode_anggaran;
            if(!isset($data[$key])):
                $data[$key] = [];
            endif;
            if(!isset($data[$key][$v->coa])):
                $data[$key][$v->coa] = [];
            endif;
            $data[$key][$v->coa]['kode_anggaran'] = $v->kode_anggaran;
            $data[$key][$v->coa]['keterangan_anggaran'] = $v->keterangan_anggaran;
            $data[$key][$v->coa]['tahun'] = $v->tahun;
            $data[$key][$v->coa]['kode_cabang'] = $v->kode_div;
            $data[$key][$v->coa]['cabang'] = $v->nama_div;
            $data[$key][$v->coa]['username'] = $v->username;
            $data[$key][$v->coa]['kegiatan'] = $v->kegiatan;
            $data[$key][$v->coa]['coa'] = $v->coa;
            $data[$key][$v->coa]['urutan'] = $v->urutan;

            for ($i=1; $i <= 12 ; $i++) { 
                $field = 'T_' . sprintf("%02d", $i);
                if(isset($data[$key][$v->coa][$field])):
                    $data[$key][$v->coa][$field] += checkNumber($v->{$field});
                else:
                    $data[$key][$v->coa][$field] = checkNumber($v->{$field});
                endif;
            }

            delete_data('tbl_divisi_rutin','id',$v->id);
        }

        foreach ($data as $key => $v) {
            $x = explode('_', $key);
            $kode_cabang = $x[0];
            $kode_anggaran = $x[1];
            foreach ($v as $coa => $v2) {
                $dataSave = $v2;
                $ck = get_data('tbl_divisi_rutin',[
                    'select' => 'id',
                    'where'  => [
                        'kode_cabang'   => $kode_cabang,
                        'kode_anggaran' => $kode_anggaran,
                        'coa'           => $coa,
                        'kegiatan'      => $v2['kegiatan'],
                    ]
                ])->row();
                $dataSave['id'] = '';
                if($ck):
                    $dataSave['id'] = $ck->id;
                endif;
                save_data('tbl_divisi_rutin',$dataSave,[],true);
            }
        }

        render(['status' => true],'json');
    }
}