<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pko_legal_admin_kredit extends BE_Controller {
    var $controller = 'pko_legal_admin_kredit';
    var $path       = 'transaction/rko_pko/';
    var $sub_menu   = 'transaction/rko_pko/sub_menu';
    var $tipe       = 4;
    var $detail_tahun;
    var $kode_anggaran;
    var $tahun_anggaran;
    var $arr_sumber_data = array();
    var $arrWeekOfMonth = array();
    var $title = 'FUNGSI LEGAL DAN ADMIN KREDIT';
    function __construct() {
        parent::__construct();
        $this->kode_anggaran  = user('kode_anggaran');
        $this->tahun_anggaran = user('tahun_anggaran');
        $this->detail_tahun   = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => [
                'a.kode_anggaran' => $this->kode_anggaran,
                'a.sumber_data'   => array(2,3)
            ],
            'order_by' => 'tahun,bulan'
        ])->result();
        $this->check_sumber_data(2);
        $this->check_sumber_data(3);
        $this->arrWeekOfMonth = arrWeekOfMonth($this->tahun_anggaran);
    }
    private  function check_sumber_data($sumber_data){
        $key = array_search($sumber_data, array_map(function($element){return $element->sumber_data;}, $this->detail_tahun));
        if(strlen($key)>0):
            array_push($this->arr_sumber_data,$sumber_data);
        endif;
    }

    function index() {
        $data = data_cabang();
        $data['path']     = $this->path;
        $data['sub_menu'] = $this->sub_menu;
        $data['detail_tahun']    = $this->detail_tahun;
        $data['arrWeekOfMonth']  = $this->arrWeekOfMonth;
        $data['skala_program']   = $this->create_option('tbl_skala_program');
        $data['controller']     = $this->controller;
        $a  = get_access($this->controller);
        $data['access_additional']  = $a['access_additional'];
        render($data,'view:'.$this->path.$this->controller.'/index');
    }

    private function create_option($tbl){
        $dt = get_data($tbl,'is_active','1')->result();
        $item = '';
        foreach ($dt as $k => $v) {
            $item .= '<option value="'.$v->id.'">'.$v->nama.'</option>';
        }
        return $item;
    }

    private function option_pelaksanaan(){
        $dt = option_pelaksanaan();
        $item = '';
        foreach ($dt as $k => $v) {
            $item .= '<option value="'.$v['value'].'">'.$v['name'].'</option>';
        }
        return $item;
    }

    function get_option(){
        $item = '';
        if(post('page') == 'dt_pelaksanaan'):
            $item = $this->option_pelaksanaan();
        endif;
        render(['data' => $item],'json');
    }

    function save(){
        $kode_cabang = post('kode_cabang');
        $ckode_anggaran = user('kode_anggaran');

        // pengecekan save untuk cabang
        check_save_cabang($this->controller,$ckode_anggaran,$kode_cabang);

        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$ckode_anggaran)->row();
        $cabang   = get_data('tbl_m_cabang',[
            'where' => [
                'kode_cabang' => user('kode_cabang'),
                'kode_anggaran' => $anggaran->kode_anggaran
            ]
        ])->row();
        $tahun    = $anggaran->tahun_anggaran;

        $dt_id          = post('dt_id');
        $keterangan     = post('keterangan');
        $skala_program  = post('skala_program');
        $target         = post('target');
        $tujuan         = post('tujuan');
        $output         = post('output');
        $pelaksanaan    = post('pelaksanaan');
        $dt_key         = post('dt_key');
        $arrID = array();
        if($dt_id):
            foreach ($dt_id as $k => $v) {
                $key    = $dt_key[$k];

                $x = post('pic'.$key);
                $pic = [];
                if(isset($x[0])): if($x[0]): $pic = $x; endif; endif;

                $dt_biaya = get_data('tbl_m_range_target_finansial','id',$target[$k])->row();
                $nama_target = '';
                $target_sampai = 0;
                if($dt_biaya):
                    $nama_target     = $dt_biaya->nama;
                    $target_sampai   = $dt_biaya->sampai;
                endif;

                $c = [
                    'tipe'  => $this->tipe,
                    'kode_anggaran' => $ckode_anggaran,
                    'keterangan_anggaran' => $anggaran->keterangan,
                    'tahun'         => $anggaran->tahun_anggaran,
                    'kode_cabang'   => $kode_cabang,
                    'cabang'        => $cabang->nama_cabang,
                    'username'      => user('username'),
                    'keterangan'    => $keterangan[$k],
                    'id_skala_program'   => $skala_program[$k],
                    'tujuan'        => $tujuan[$k],
                    'output'        => $output[$k],
                    'pic'           => json_encode($pic),
                    'target'        => $target[$k],
                    'nama_target'   => $nama_target,
                    'target_sampai' => $target_sampai,
                    'pelaksanaan'   => $pelaksanaan[$k],
                ];
                $cek = get_data('tbl_rko_pko',[
                    'where'         => [
                        'kode_anggaran'   => $ckode_anggaran,
                        'kode_cabang'     => $kode_cabang,
                        'tahun'           => $tahun,
                        'tipe'            => $this->tipe,
                        'id' => $dt_id[$k],
                    ],
                ])->row();
                $c = first_checkbox_save($c,$this->arrWeekOfMonth);
                if(!isset($cek->id)) {
                    $c['create_at'] = date("Y-m-d H:i:s");
                    $c['create_by'] = user('username');
                    $dt_insert = insert_data('tbl_rko_pko',$c);
                    array_push($arrID, $dt_insert);
                }else{
                    $c['update_at'] = date("Y-m-d H:i:s");
                    $c['update_by'] = user('username');
                    update_data('tbl_rko_pko',$c,['kode_anggaran'   => $ckode_anggaran,
                        'kode_cabang'     => $kode_cabang,
                        'tahun'           => $tahun,
                        'tipe'            => $this->tipe,
                        'id' => $dt_id[$k]]);
                    array_push($arrID, $dt_id[$k]);
                }
            }
        endif;

        if(count($arrID)>0 && post('id')):
            // delete_data('tbl_rko_pko',['kode_anggaran'=>$ckode_anggaran,'id not'=>$arrID,'kode_cabang'=>$kode_cabang,'tahun'=>$tahun,'tipe' => $this->tipe]);
        elseif(post('id')):
            // delete_data('tbl_rko_pko',['kode_anggaran'=>$ckode_anggaran,'kode_cabang'=>$kode_cabang,'tahun'=>$tahun,'tipe' => $this->tipe]);
            delete_data('tbl_rko_pko',['kode_anggaran'=>$ckode_anggaran,'kode_cabang'=>$kode_cabang,'tahun'=>$tahun,'tipe' => $this->tipe,'id' => post('id')]);
        endif;

        render([
            'status'    => 'success',
            'message'   => lang('data_berhasil_disimpan'),
        ],'json');
    }

    function save_perubahan() {       
        $data   = json_decode(post('json'),true);
        foreach($data as $id => $record) {          
            update_data('tbl_rko_pko',$record,'id',$id); }
    }

    function data($anggaran="", $cabang="", $tipe = 'table'){
        $menu = menu();
        $ckode_anggaran = $anggaran;
        $ckode_cabang = $cabang;

        if(post('export') == 'export'):
            $ckode_anggaran = post('kode_anggaran');
            $ckode_cabang   = post('kode_cabang');
            $cabang         = $ckode_cabang;
        endif;
        if(!$ckode_anggaran or !$ckode_cabang):
            exit();
        endif;

        $data_finish['kode_anggaran']   = $ckode_anggaran;
        $data_finish['kode_cabang']     = $ckode_cabang;
        $a = get_access($this->controller,$data_finish);
        $access_edit    = false;
        $access_delete  = false;
        if($a['access_edit'] && $cabang == user('kode_cabang')):
            $access_edit = true;
        elseif($a['access_edit'] && $a['access_additional']):
            $access_edit = true;
        endif;
        if($a['access_delete'] && $cabang == user('kode_cabang')):
            $access_delete = true;
        elseif($a['access_delete'] && $a['access_additional']):
            $access_delete = true;
        endif;
        $data['access_edit'] = $access_edit;
        $data['access_delete'] = $access_delete;

        // pengecekan akses cabang
        check_access_cabang($this->controller,$ckode_anggaran,$ckode_cabang,$a);

        $arr = ['select'    => '
                    a.*,
                    b.nama as skala_program_name,
                ',];
        if($anggaran) {
            $arr['where']['a.kode_anggaran']  = $ckode_anggaran;
        }
        if($cabang) {
            $arr['where']['a.kode_cabang']  = $ckode_cabang;
        }

        $arr['join'][] = 'tbl_skala_program b on b.id = a.id_skala_program';
        // $arr['join'][] = 'tbl_m_range_target_finansial c on c.id = a.target';
        $arr['where']['tipe'] = $this->tipe;
        $arr['sort_by'] = 'a.id';
        $arr['sort'] = 'DESC';
        $list = get_data('tbl_rko_pko a',$arr)->result();
        $data['list']     = $list;
        $data['current_cabang'] = $cabang;
        $data['detail_tahun']    = $this->detail_tahun;
        $data['arrWeekOfMonth']  = $this->arrWeekOfMonth;
    
        if(post('export') == 'export'):
            $this->export($data);
        else:
            $response   = array(
                'status'=> true,
                'table' => $this->load->view($this->path.$this->controller.'/table',$data,true),
                'edit'  => $access_edit,
                'delete'=> $access_delete,
            );
           
            render($response,'json');
        endif;
    }

    function save_checkbox(){
        $ID     = post('ID');
        $val    = post('val');

        $a = get_access($this->controller);
        $access_edit    = false;

        $d = explode('-', $ID);
        try {
            $id     = $d[1];
            $key    = $d[2];
            $row = get_data('tbl_rko_pko',[
                'select'    => 'kode_cabang,checkbox',
                'where'     => "id = '".$d[1]."' and tipe = '".$this->tipe."'"
            ])->row();
            if($a['access_edit'] && $row->kode_cabang == user('kode_cabang')):
                $access_edit = true;
            elseif($a['access_edit'] && $a['access_additional']):
                $access_edit = true;
            endif;
            if(!$access_edit):
                render(['status' => false, 'message' => lang('cannot_edit')],'json');
                exit();
            endif;

            $x = json_decode($row->checkbox,true);
            $x[$key] = $val;
            update_data('tbl_rko_pko',['checkbox' => json_encode($x)],'id',$id);

            render(['status' => true, 'message' => lang('data_berhasil_disimpan')],'json');
        } catch (Exception $e) {
            render(['status' => false, 'message' => lang('data_not_found')],'json');
        }
    }

    function delete() {
        $response = destroy_data('tbl_rko_pko',['id' => post('id'), 'tipe' => $this->tipe]);
        render($response,'json');
    }

    function get_data(){
        $d = get_data('tbl_rko_pko',[
            'where'         => [
                'id'    => post('id'),
                'tipe'  => $this->tipe
            ],
        ])->row();

        $list = get_data('tbl_rko_pko',[
            'where'         => [
                'kode_anggaran'   => $d->kode_anggaran,
                'kode_cabang'     => $d->kode_cabang,
                'tahun'           => $d->tahun,
                'tipe'            => $this->tipe,
                'id'              => post('id'),
            ]
        ])->result();

        $arr_pic = [];
        foreach ($list as $k => $v) {
            $pic = json_decode($v->pic);
            if(!is_array($pic)) $pic = [];
            $v->pic = $pic;

            $arr_pic = array_merge($arr_pic,$v->pic);
        }
        $arr_pic = array_unique($arr_pic);
        $pic_option = data_pic_option($arr_pic);

        render([
            'status'    => 'success',
            'data'      => $list,
            'detail'    => $d,
            'pic_option' => $pic_option
        ],'json');
    }

    private function export($data){
        ini_set('memory_limit', '-1');
        $arrWeekOfMonth     = $data['arrWeekOfMonth'];
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_cabang_txt    = post('kode_cabang_txt');

        $kode_cabang        = post('kode_cabang');
        $kode_cabang_txt    = post('kode_cabang_txt');

        $header = [];
        $header[] = lang('no');
        $header[] = lang('pko');
        $header[] = lang('skala_program');
        $header[] = lang('target_financial');
        $header[] = lang('tujuan');
        $header[] = lang('output');
        $header[] = lang('pic');
        $header[] = lang('pelaksanaan');
        foreach ($arrWeekOfMonth['month'] as $k => $v) {
            for ($i=0; $i < $v ; $i++) { 
                if($i == 0):
                    $header[] = month_lang($k).' ('.get_view_report().')';
                else:
                    $header[] = '';
                endif;
            }
        }

        $data_export = [];

        // untuk header kolom ke 3
        $h = [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ];
        foreach ($arrWeekOfMonth['week'] as $k => $v) {
            $d = $arrWeekOfMonth['detail'][$v];
            $x = explode("-", $d);
            $date_string = $x[2] . 'W' . sprintf('%02d', $x[0]);
            $first_day = sprintf('%02d', date('j', strtotime($date_string)));
            $h[] = $first_day;
        }
        array_push($data_export,$h);

        $n = 0;
        $total = [];
        foreach($data['list'] as $k => $v){
            $n++;

            $checkbox = json_decode($v->checkbox,true);

            $pic = json_decode($v->pic);
            if(!is_array($pic)) $pic = [];

            $d_pic = '';
            if(count($pic)>0):
                $dt_pic = get_data('tbl_m_pegawai','id',$pic)->result();
                $no = 0;
                foreach($dt_pic as $k2 => $v2){
                    $no++;
                    $d_pic .= $no.'. '.$v2->nip.' - '.$v2->nama.PHP_EOL;
                }
            endif;

            $h = [
                $n,
                $v->keterangan,
                $v->skala_program_name,
                $v->nama_target,
                $v->tujuan,
                $v->output,
                $d_pic,
                option_pelaksanaan()[$v->pelaksanaan]['name'],
            ];
            foreach ($arrWeekOfMonth['week'] as $k2 => $v2) {
                $d = $arrWeekOfMonth['detail'][$v2];
                $x = explode("-", $d);
                $key = $x[0];
                $bln = $x[1];
                if(isset($checkbox[$key]) && $checkbox[$key] == 1):
                    if(isset($total[$bln])):
                        $total[$bln] += checkNumber($v->target_sampai);
                    else:
                        $total[$bln] = checkNumber($v->target_sampai);
                    endif;
                    $h[] = 'v';
                else:
                    $h[] = '';
                endif;
            }
            array_push($data_export,$h);
        }

        // total
        if(count($data['list'])>0):
            $h = [
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ];
            foreach ($arrWeekOfMonth['month'] as $k => $v) {
                for ($i=0; $i < $v ; $i++) { 
                    if($i == 0):
                        $val = 0;
                        if(isset($total[$k])):
                            $val = $total[$k];
                        endif;
                        $h[] = view_report($val);
                    else:
                        $h[] = '';
                    endif;
                }
            }
            array_push($data_export,$h);
        endif;

        $config[] = [
            'title' => $this->title,
            'header' => $header,
            'data'  => $data_export,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'fungsi_legal_dan_admin_kredit'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }

}