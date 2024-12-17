<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Asumsi_kebijakan_fungsi extends BE_Controller {
    var $path       = 'transaction/budget_planner/kantor_pusat/';
    var $sub_menu   = 'transaction/budget_planner/sub_menu';
    var $controller = 'asumsi_kebijakan_fungsi';
    function __construct() {
        parent::__construct();
    }
    
    function index($p1="") {
        $a = get_access('asumsi_kebijakan_fungsi');
        $data = cabang_divisi();
        $data['path']     = $this->path;
        $data['sub_menu'] = $this->sub_menu;
        $data['access_additional'] = $a['access_additional'];
        $data['access_edit'] = $a['access_edit'];
        $data['controller'] = $this->controller;

        $dt_tahun = $this->detail_tahun(user('kode_anggaran'));
        $data['detail_tahun'] = $dt_tahun['detail_tahun'];

        render($data,'view:'.$this->path.'asumsi_kebijakan_fungsi/index');
    }

    private function detail_tahun($kode_anggaran){
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
        $detail_tahun = [];
        if($anggaran):
            $detail_tahun = get_data('tbl_detail_tahun_anggaran a',[
                'select'    => 'distinct a.bulan,a.tahun,a.sumber_data,b.singkatan',
                'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
                'where'     => [
                    'a.kode_anggaran' => $kode_anggaran,
                    'a.tahun'         => $anggaran->tahun_anggaran
                ],
                'order_by' => 'tahun,bulan'
            ])->result();
        endif;
        return [
            'anggaran'      => $anggaran,
            'detail_tahun'  => $detail_tahun,
        ];
    }

    function data($anggaran="", $cabang="", $tipe = 'table'){
        $ckode_anggaran = $anggaran;
        $ckode_cabang = $cabang;

        $data_finish['kode_anggaran']   = $ckode_anggaran;
        $data_finish['kode_cabang']     = $ckode_cabang;
        $access = get_access('asumsi_kebijakan_fungsi',$data_finish);
        $access_edit = false;
        if($access['access_edit']):
            $access_edit = true;
        elseif($access['access_edit'] && $access['access_additional']):
            $access_edit = true;
        endif;
        $data['akses_ubah'] = $access_edit;

        $access_delete = false;
        if($access['access_delete']):
            $access_delete = true;
        elseif($access['access_delete'] && $access['access_additional']):
            $access_delete = true;
        endif;
        $data['access_delete'] = $access_delete;

        // pengecekan akses divisi
        check_access_divisi($this->controller,$anggaran,$cabang,$access);

        $arr = ['select'    => '
            a.*,
            b.nama as kebijakan_fungsi,
            c.glwdes,
            d.keterangan as nama_grup,
        ',];
        if($anggaran) {
            $arr['where']['a.kode_anggaran']  = $ckode_anggaran;
        }
        if($cabang) {
            $arr['where']['a.kode_cabang']  = $ckode_cabang;
        }

        $arr['join'][] = 'tbl_kebijakan_fungsi b on b.id = a.id_kebijakan_fungsi';
        $arr['join'][] = "tbl_m_coa c on c.glwnco = a.coa and c.kode_anggaran = a.kode_anggaran and c.glwnco != '' type left";
        $arr['join'][] = "tbl_grup_asetinventaris d on d.kode = a.grup and a.grup != '' type left";
        $arr['sort'] = 'ASC';
        $arr['sort_by'] = 'a.id';
        $list = get_data('tbl_kebijakan_asumsi a',$arr)->result();
        $data['list']     = $list;
        $data['kebijakan_fungsi'] = get_data('tbl_kebijakan_fungsi','is_active',1)->result();
        $data['current_cabang'] = $cabang;

        $dt_tahun = $this->detail_tahun($ckode_anggaran);
        $data['detail_tahun'] = $dt_tahun['detail_tahun'];

        $data['arr_type'] = ['1' => lang('biaya'),'2' => lang('inventaris')];
        
        $coa = $this->get_coa('data');
        $response   = array(
            'status' => true,
            'table' => $this->load->view($this->path.'asumsi_kebijakan_fungsi/table',$data,true),
            'access_edit' => $access_edit,
            'coa' => $coa,
        );
       
        render($response,'json');
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

    function get_kebijakan_fungsi($type = 'echo'){
        $ls             = get_data('tbl_kebijakan_fungsi a',[
            'where'     => [
                'a.is_active' => 1,
            ]
        ])->result();
        $data           = '<option value=""></option>';
        foreach($ls as $e2) {
            if($e2->nama == '$$SUBDIV'):
                $e2->nama = post('cabang_txt');
            endif;
            $data       .= '<option value="'.$e2->id.'">'.$e2->nama.'</option>';
        }

        if($type == 'echo') echo $data;
        else return $data;
    }

    function cabang_option(){
        $kode_anggaran = user('kode_anggaran');
        $ls_cabang = get_data('tbl_m_cabang',[
            'select' => 'kode_cabang,nama_cabang',
            'where' => [
                'kode_anggaran' => $kode_anggaran,
                'is_active'     => 1,
                'status_group'  => 0
            ],
            'order_by' => 'urutan'
        ])->result();

        $option = '<option value="all">'.lang('all').'</option>';
        $option .= '<option value="kc">KC</option>';
        $option .= '<option value="kcp">KCP</option>';
        foreach ($ls_cabang as $k => $v) {
           $option .= '<option value="'.$v->kode_cabang.'">'.remove_spaces($v->nama_cabang).'</option>';
        }

        $option_type = '<option value="1">'.lang('biaya').'</option>';
        $option_type .= '<option value="2">'.lang('inventaris').'</option>';

        $option_group = '';
        $ls_group = get_data('tbl_grup_asetinventaris','is_active',1)->result();
        foreach ($ls_group as $v) {
            $option_group .= '<option value="'.$v->kode.'">'.$v->kode.' - '.remove_spaces($v->keterangan).'</option>';
        }

        render([
            'data' => $option,
            'type' => $option_type,
            'group' => $option_group,
        ],'json');
    }

    function save(){
        $kode_cabang = post('kode_cabang');
        $ckode_anggaran = user('kode_anggaran');

        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$ckode_anggaran)->row();
        $cabang   = get_data('tbl_m_cabang',[
            'kode_cabang' => user('kode_cabang'),
            'kode_anggaran' => $anggaran->kode_anggaran
        ])->row();
        $tahun    = $anggaran->tahun_anggaran;

        // pengecekan save divisi
        check_save_divisi($this->controller,$ckode_anggaran,$kode_cabang);

        $dt_id      = post('dt_id');
        $dt_key     = post('dt_key');
        $kebijakan_fungsi = post('kebijakan_fungsi');
        $uraian     = post('uraian');
        $coa        = post('coa');
        $type       = post('type');
        $group      = post('group');
        $kode_inventaris = post('kode_inventaris');

        $this->pengecekan($anggaran,$cabang);

        $arrID = array();
        if($kebijakan_fungsi):
            foreach ($kebijakan_fungsi as $k => $v) {
                $c = [
                    'kode_anggaran' => $ckode_anggaran,
                    'keterangan_anggaran' => $anggaran->keterangan,
                    'tahun'         => $anggaran->tahun_anggaran,
                    'kode_cabang'   => $kode_cabang,
                    'cabang'        => $cabang->nama_cabang,
                    'username'      => user('username'),
                    'id_kebijakan_fungsi'  => $kebijakan_fungsi[$k],
                    'uraian'        => $uraian[$k],
                    'type'          => $type[$k]
                ];

                if($type[$k] == 1):
                    $c['coa'] = $coa[$k];
                    $c['grup'] = '';
                    $c['kode_inventaris'] = '';
                else:
                    $c['coa'] = '';
                    $c['grup'] = $group[$k];
                    $c['kode_inventaris'] = $kode_inventaris[$k];
                    if(!in_array($group[$k],['E.4','E.5','E.7'])):
                        $c['kode_inventaris'] = '';
                    endif;
                    
                endif;

                // type cabang
                $dt_index       = $dt_key[$k];
                $type_cabang    = post('type_cabang_'.$dt_index);
                if(!$type_cabang or !is_array($type_cabang)):
                    $type_cabang = [];
                endif;
                $c['type_cabang'] = json_encode($type_cabang);

                $cek = get_data('tbl_kebijakan_asumsi',[
                    'where'         => [
                        'kode_anggaran'   => $ckode_anggaran,
                        'kode_cabang'     => $kode_cabang,
                        'tahun'           => $tahun,
                        'id' => $dt_id[$k],
                    ],
                ])->row();

                if(!isset($cek->id)) {
                    $c['create_at'] = date('Y-m-d H:i:s');
                    $c['create_by'] = user('username');
                    $dt_insert = insert_data('tbl_kebijakan_asumsi',$c);
                    array_push($arrID, $dt_insert);
                }else{
                    $c['update_at'] = date('Y-m-d H:i:s');
                    $c['update_by'] = user('username');
                    update_data('tbl_kebijakan_asumsi',$c,['kode_anggaran'   => $ckode_anggaran,
                        'kode_cabang'     => $kode_cabang,
                        'tahun'           => $tahun,
                        'id' => $dt_id[$k]]);
                    array_push($arrID, $dt_id[$k]);
                }
            }
        endif;

        if(count($arrID)>0 && post('id') || post('id')):
            // $d = get_data('tbl_kebijakan_asumsi',[
            //     'where'         => [
            //         'id' => post('id'),
            //     ],
            // ])->row();

            if(count($arrID)>0):
                // delete_data('tbl_kebijakan_asumsi',['kode_anggaran'=>$ckode_anggaran,'id not'=>$arrID,'kode_cabang'=>$kode_cabang,'tahun'=>$tahun, 'id_kebijakan_fungsi' => $d->id_kebijakan_fungsi ]);
            else:
                // delete_data('tbl_kebijakan_asumsi',['kode_anggaran'=>$ckode_anggaran,'kode_cabang'=>$kode_cabang,'tahun'=>$tahun, 'id_kebijakan_fungsi' => $d->id_kebijakan_fungsi ]);
            endif;
        endif;

        render([
            'status'    => 'success',
            'message'   => lang('data_berhasil_disimpan'),
        ],'json');
    }

    private function pengecekan($anggaran,$cabang){
        $dt_id      = post('dt_id');
        $group      = post('group');
        $kode_inventaris = post('kode_inventaris');
        $kebijakan_fungsi = post('kebijakan_fungsi');
        if(is_array($dt_id)):
            foreach ($dt_id as $k => $v) {
                if(in_array($group[$k],['E.4','E.5','E.7'])):
                    $where = "kode_anggaran = '$anggaran->kode_anggaran' and grup = '$group[$k]' and kode_inventaris = '$kode_inventaris[$k]'";
                    $where2 = $where;
                    if($dt_id[$k]):
                        $where .= " and id_kebijakan_asumsi != '$dt_id[$k]'";
                        $where2 .= " and id != '$dt_id[$k]'";
                    endif;
                    $ck = $this->db->count_all("tbl_rencana_aset where ".$where);
                    $ck2 = $this->db->count_all("tbl_kebijakan_asumsi where ".$where2);
                    if($ck>0 or $ck2>0):
                        render(['status' => 'warning', 'message' => lang('grup').' '.$group[$k].' '.lang('dengan_kode_inventaris').' "'.$kode_inventaris[$k].'" '.lang('sudah_ada')],'json');exit();
                    endif;
                endif;
            }
        endif;
    }

    function delete(){
        $ck = get_data('tbl_kebijakan_asumsi','id',post('id'))->row();
        if(!$ck):
            render(['status' => 'failed','message' => lang('data_not_found')],'json');exit();
        endif;

        // pengecekan save divisi
        check_save_divisi($this->controller,$ck->kode_anggaran,$ck->kode_cabang,'','access_delete');

        $response = destroy_data('tbl_kebijakan_asumsi','id',post('id'));
        destroy_data('tbl_biaya_kebijakan_asumsi','id_kebijakan_asumsi',post('id'));
        destroy_data('tbl_rencana_aset','id_kebijakan_asumsi',post('id'));
        render($response,'json');
    }

    function save_perubahan() {       
        $data   = json_decode(post('json'),true);
        $kode_cabang = post('kode_cabang');
        $kode_anggaran = post('kode_anggaran');

        // pengecekan save divisi
        check_save_divisi($this->controller,$kode_anggaran,$kode_cabang,'','access_edit');

        foreach($data as $id => $record) {
            $dt = insert_view_report_arr($record);
            update_data('tbl_kebijakan_asumsi',$dt,'id',$id); 
        }

        render(['status' => true,'message' => lang('data_berhasil_diperbaharui')],'json');
    }

    function get_data(){
        $d = get_data('tbl_kebijakan_asumsi',[
            'where'         => [
                'id' => post('id'),
            ],
        ])->row();

        $list = get_data('tbl_kebijakan_asumsi',[
            'where'         => [
                'kode_anggaran'   => $d->kode_anggaran,
                'kode_cabang'     => $d->kode_cabang,
                'tahun'           => $d->tahun,
                'id' => $d->id,
            ]
        ])->result_array();
        foreach($list as $k => $v){
            $type_cabang = json_decode($v['type_cabang']);
            if(!$type_cabang or !is_array($type_cabang)):
                $type_cabang = [];
            endif;
            $list[$k]['type_cabang'] = $type_cabang;
        }

        render([
            'status'    => 'success',
            'data'      => $list,
            'detail'    => $d,
        ],'json');
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

        $dt_tahun = $this->detail_tahun($kode_anggaran);
        $detail_tahun = $dt_tahun['detail_tahun'];

        $dt = json_decode(post('data'),true);

        $header = $dt['#result1']['header'][0];

        $data = [];
        foreach(['#result1'] as $name){
            if(isset($dt[$name])):
                $count2 = 0;
                foreach($dt[$name]['data'] as $k => $v){
                    $count2 = count($v);
                    if($count2<5):
                        $detail = [
                            $v[0],
                        ];
                        for ($i=2; $i < (9+count($detail_tahun)) ; $i++) { 
                            $detail[] = '';
                        }
                        $detail[] = '';
                    else:
                        $detail = [
                            $v[0],
                            $v[1],
                            $v[2],
                            $v[3],
                            $v[4],
                            $v[5],
                            $v[6],
                            $v[7],
                        ];
                        for ($i=8; $i < (count($v)-1) ; $i++) { 
                            $detail[] = filter_money($v[$i]);
                        }
                        $detail[] = '';
                    endif;
                    $data[] = $detail;
                }
            endif;
        }

        $config[] = [
            'title' => 'KEBIJAKAN FUNGSI'.' ('.get_view_report().')',
            'header' => $header,
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'asumsi_dan_keijakan_fungsi_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }
}