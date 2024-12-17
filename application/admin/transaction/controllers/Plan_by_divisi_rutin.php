<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Plan_by_divisi_rutin extends BE_Controller {
    var $path       = 'transaction/budget_planner/kantor_pusat/';
    var $sub_menu   = 'transaction/budget_planner/sub_menu';
    var $controller = 'plan_by_divisi_rutin';
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
        $a = get_access('plan_by_divisi_rutin');
        $data = cabang_divisi();
        $data['path']     = $this->path;
        $data['sub_menu'] = $this->sub_menu;
        $data['access_additional'] = $a['access_additional'];
        $data['access_edit'] = $a['access_edit'];
        $data['detail_tahun']= $this->detail_tahun;
        render($data,'view:'.$this->path.'by_divisi_rutin/index');
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
        $ckode_anggaran = $anggaran;
        $ckode_cabang = $cabang;

        $data_finish['kode_anggaran']   = $ckode_anggaran;
        $data_finish['kode_cabang']     = $ckode_cabang;
        $access = get_access('plan_by_divisi_rutin',$data_finish);
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
        $arr['not_like']['a.kegiatan'] = 'default__';
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
            'table'         => $this->load->view($this->path.'by_divisi_rutin/table',$data,true),
            'access_edit'   => $access_edit,
            'coa'           => $coa,
        );
       
        render($response,'json');
    }

    function save(){
        $data = post();
        $kode_cabang = post('kode_cabang');
        $ckode_anggaran = user('kode_anggaran');

        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$ckode_anggaran)->row();

        // pengecekan save divisi
        check_save_divisi($this->controller,$ckode_anggaran,$kode_cabang,'','access_edit');

        $tahun  = $anggaran->tahun_anggaran;
        $kegiatan    = post('kegiatan');
        $dt_index    = post('dt_index');

        $cabang      = get_data('tbl_m_cabang',[
            'where' => [
                'kode_cabang' => $kode_cabang,
                'kode_anggaran' => $anggaran->kode_anggaran
            ]
        ])->row();
        $status      = false;
        if($kegiatan):
            foreach ($kegiatan as $i => $h) {
                $status      = true;
                $arrID = array();
                $key = $dt_index[$i];
                $dt_id  = post('dt_id'.$key);
                $coa    = post('coa'.$key);
                $c = [];
                if(post('id')):
                    $dt = get_data('tbl_divisi_rutin','id',post('id'))->row();
                endif;
                foreach($dt_id as $k => $v) {
                    $c = [
                        'kode_anggaran' => $ckode_anggaran,
                        'keterangan_anggaran' => $anggaran->keterangan,
                        'tahun'  => $anggaran->tahun_anggaran,
                        'kode_cabang' => $kode_cabang,
                        'cabang' => $cabang->nama_cabang,
                        'username' => user('username'),
                        'coa' => $coa[$k],
                        'kegiatan' => $kegiatan[$i]

                    ];

                    $cek        = get_data('tbl_divisi_rutin',[
                        'where'         => [
                            'kode_anggaran'   => $ckode_anggaran,
                            'kode_cabang'     => $kode_cabang,
                            'tahun'           => $anggaran->tahun_anggaran,
                            'id'              => $dt_id[$k]
                            ],
                    ])->row();

                    
                    if(!isset($cek->id)) {
                        $id = insert_data('tbl_divisi_rutin',$c);
                    }else{
                        $id = $dt_id[$k];
                        update_data('tbl_divisi_rutin',$c,[
                            'kode_anggaran'   => $ckode_anggaran,
                            'keterangan_anggaran' => $anggaran->keterangan,
                            'kode_cabang'     => $kode_cabang,
                            'tahun'           => $anggaran->tahun_anggaran,
                            'id'              => $dt_id[$k]
                        ]);
                    }

                    array_push($arrID, $id);
                }

                if(count($arrID)>0 && post('id')):
                    delete_data('tbl_divisi_rutin',['kode_anggaran'=>$ckode_anggaran,'id not'=>$arrID,'kode_cabang'=>$kode_cabang,'tahun'=>$tahun, 'kegiatan' => $dt->kegiatan]);
                endif;
            }
        endif;

        if(!$status && post('id')):
            $dt = get_data('tbl_divisi_rutin','id',post('id'))->row();
            delete_data('tbl_divisi_rutin',['kode_anggaran'=>$ckode_anggaran,'kode_cabang'=>$kode_cabang,'tahun'=>$tahun, 'kegiatan' => $dt->kegiatan]);
        endif;

        render([
            'status'    => 'success',
            'message'   => lang('data_berhasil_disimpan')
        ],'json');
    }

    function save_perubahan() {       
        $data   = json_decode(post('json'),true);
        $kode_cabang = post('kode_cabang');
        $kode_anggaran = post('kode_anggaran');

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

    function get_data() {
        $dt = get_data('tbl_divisi_rutin','id',post('id'))->row();
        $list = get_data('tbl_divisi_rutin',[
            'where' => [
                'kode_anggaran' => $dt->kode_anggaran,    
                'tahun' => $dt->tahun,
                'kode_cabang' => $dt->kode_cabang,
                'kegiatan'  => $dt->kegiatan
            ],
        ])->result_array();
        $data['detail'] = $dt;
        $data['data'] = $list;
        render($data,'json');

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

    function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_anggaran      = post('kode_anggaran');

        $kode_cabang        = post('kode_cabang');
        $kode_cabang_txt    = post('kode_cabang_txt');

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
                    for ($i=4; $i < (count($v)-1) ; $i++) { 
                        $detail[] = filter_money($v[$i]);
                    }
                    $detail[] = '';
                    $data[] = $detail;
                }
            endif;
        }

        $config[] = [
            'title' => 'INPUT BY OPRS DIVISI'.' ('.get_view_report().')',
            'header' => $header,
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'input_by_oprs_divisi_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }
}