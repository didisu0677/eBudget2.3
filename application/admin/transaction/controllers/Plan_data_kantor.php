<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Plan_data_kantor extends BE_Controller {
    var $path       = 'transaction/budget_planner/kantor_pusat/';
    var $sub_menu   = 'transaction/budget_planner/sub_menu';
    var $detail_tahun;
    var $kode_anggaran;
    var $controller = 'plan_data_kantor';
    function __construct() {
        parent::__construct();
        $this->kode_anggaran  = user('kode_anggaran');
        $this->detail_tahun   = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => [
                'a.kode_anggaran' => $this->kode_anggaran,
                'a.sumber_data'   => array(2,3)
            ],
            'order_by' => 'tahun,bulan'
        ])->result();
    }
    
    function index($p1="") {
        $data = cabang_divisi('plan_data_kantor');
        $data['path']     = $this->path;
        $data['sub_menu'] = $this->sub_menu;
        $data['detail_tahun']    = $this->detail_tahun;
        render($data,'view:'.$this->path.'data_kantor/index');
    }

    function get_data($kode_cabang){
        $status_group = post('status_group');
        // $status_group = 1;
        if($status_group == 1):
            $cabang     = get_data('tbl_m_cabang','id',$kode_cabang)->row();
            $cabang     = get_data('tbl_m_cabang',[
                'where' => [
                    'kode_cabang' => $cabang->kode_cabang,
                    'kode_anggaran' => $this->kode_anggaran
                ]
            ])->row_array();
        else:
            $cabang     = get_data('tbl_m_cabang',[
                'where' => [
                    'kode_cabang' => $kode_cabang,
                    'kode_anggaran' => $this->kode_anggaran
                ]
            ])->row_array();
        endif;

        if(!isset($cabang['id'])):
            render(['status' => false,'message' => 'cabang not found'],'json');exit();
        endif;
        $kode_cabang = $cabang['kode_cabang'];

        $data_finish['kode_anggaran']   = $this->kode_anggaran;
        $data_finish['kode_cabang']     = $kode_cabang;
        $access = get_access('plan_data_kantor',$data_finish);
        $access_edit = false;
        if($access['access_edit']):
            $access_edit = true;
        elseif($access['access_edit'] && $access['access_additional']):
            $access_edit = true;
        endif;

        // pengecekan akses cabang
        check_access_cabang($this->controller,$this->kode_anggaran,$kode_cabang,$access);

        $data = get_data('tbl_m_data_kantor',[
            'where' => "kode_cabang = '$kode_cabang' and kode_anggaran = '".$this->kode_anggaran."'"
        ])->row_array();
        if($data) $data['tgl_mulai_menjabat'] = date("d-m-Y", strtotime($data['tgl_mulai_menjabat']));
        else $data = array();
        $data['kode_cabang'] = $kode_cabang;
        $data['nama_kantor'] = $cabang['nama_cabang'];
        $data['access_edit'] = $access_edit;
        $data['status']      = true;
        render($data,'json');
    }
    function save(){
        $data = post();
        $data['kode_anggaran'] = $this->kode_anggaran;
        
        // pengecekan save divisi
        check_save_divisi($this->controller,$data['kode_anggaran'],$data['kode_cabang'],'tbl_m_data_kantor');

        $response = save_data('tbl_m_data_kantor',$data);
        render($response,'json');
    }

    function export(){
        ini_set('memory_limit', '-1');
        $status_group = post('status_group');
        $kode_cabang  = post('kode_cabang');
        // $status_group = 1;
        if($status_group == 1):
            $cabang     = get_data('tbl_m_cabang','id',$kode_cabang)->row();
            $cabang     = get_data('tbl_m_cabang',[
                'where' => [
                    'kode_cabang' => $cabang->kode_cabang,
                    'kode_anggaran' => $this->kode_anggaran
                ]
            ])->row_array();
        else:
            $cabang     = get_data('tbl_m_cabang',[
                'where' => [
                    'kode_cabang' => $kode_cabang,
                    'kode_anggaran' => $this->kode_anggaran
                ]
            ])->row_array();
        endif;

        if(!isset($cabang['id'])):
            render(['status' => false,'message' => 'cabang not found'],'json');exit();
        endif;
        $kode_cabang = $cabang['kode_cabang'];

        $access = get_access($this->controller);
        // pengecekan akses cabang
        check_access_cabang($this->controller,$this->kode_anggaran,$kode_cabang,$access);

        $header    = ['',''];
        $data   = [];

        $cab = get_data('tbl_m_data_kantor',[
            'where' => [
                'kode_cabang' => $kode_cabang,
                'kode_anggaran' => $this->kode_anggaran
            ]
        ])->row_array();
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

        // render($config,'json');exit();

        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$this->kode_anggaran)->row();

        $this->load->library('simpleexcel',$config);
        $filename = 'data_kantor_'.str_replace(' ', '_', $anggaran->keterangan).'_'.str_replace(' ', '_', $cabang['nama_cabang']).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
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