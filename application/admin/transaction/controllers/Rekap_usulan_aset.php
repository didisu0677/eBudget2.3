<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rekap_usulan_aset extends BE_Controller {
    var $kode_anggaran;
    var $kode_inventaris;
    var $controller = 'rekap_usulan_aset';
    function __construct() {
        parent::__construct();
        $this->kode_anggaran  = user('kode_anggaran');
        $this->kode_inventaris = get_data('tbl_rencana_aset a',[
            'select' => 'distinct a.kode_inventaris, ifnull(b.nama_inventaris,a.nama_grup) as nama_grup',
            'join'   => [
                'tbl_kode_inventaris b on b.kode_inventaris = a.kode_inventaris type left'
            ],
            'where'  => "a.kode_anggaran = '$this->kode_anggaran' and a.kode_inventaris != '' and a.nama_grup != ''",
            'order_by' => 'a.kode_inventaris'
        ])->result();
    }
    
    function index() {
        $a = get_access($this->controller);
        $tahun_anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$this->kode_anggaran)->result(); 
        $data['tahun']  = $tahun_anggaran;
        $data['kode_inventaris']  = $this->kode_inventaris;
        $data['keterangan_inventaris'] = get_data('tbl_m_keterangan_inventaris','is_active',1)->result_array();
        $data['access_edit'] = $a['access_edit'];
        render($data);
    }

    function data($anggaran="", $kode_inventaris=""){
        $kode_inventaris = str_replace('-', ' ', $kode_inventaris);
        $data['cabang'][0] = get_data('tbl_m_cabang',array('where_array'=>array(
            'parent_id'=>0, 'is_active' => 1, 'kode_anggaran' => $anggaran
        ),'order_by' => 'urutan'))->result();
        $arrCodeCabang = array();
        foreach($data['cabang'][0] as $m0) {
            $data['cabang'][$m0->id] = get_data('tbl_m_cabang',array('where_array'=>array(
                'parent_id'=>$m0->id, 'is_active' => 1, 'kode_anggaran' => $anggaran
            ),'order_by' => 'urutan'))->result();
            foreach($data['cabang'][$m0->id] as $m1) {
                $data['cabang'][$m1->id] = get_data('tbl_m_cabang',array('where_array'=>array(
                    'parent_id'=>$m1->id, 'is_active' => 1, 'kode_anggaran' => $anggaran
                ),'order_by' => 'urutan'))->result();
                foreach($data['cabang'][$m1->id] as $m2) {
                    $dataLevel4 = get_data('tbl_m_cabang',array('where_array'=>array(
                        'parent_id'=>$m2->id, 'is_active' => 1, 'kode_anggaran' => $anggaran
                    ),'order_by' => 'urutan'))->result();
                    $data['cabang'][$m2->id] = $dataLevel4;

                    foreach ($dataLevel4 as $v) {
                        if(!in_array($v->kode_cabang,$arrCodeCabang)):
                            array_push($arrCodeCabang, $v->kode_cabang);
                        endif;
                    }
                }
            }
        }

        $status_inventaris = get_data('tbl_rencana_aset_status a',[
            'select' => 'b.nama',
            'join'   => ["tbl_m_keterangan_inventaris b on b.id = a.id_keterangan_inventaris type left"],
            'where'  => [
                'kode_anggaran'     => $anggaran,
                'kode_inventaris'   => $kode_inventaris
            ]
        ])->row();

        $dSum = get_data('tbl_rencana_aset',[
            'select' => 'kode_cabang,nama_inventaris,sum(ifnull(harga,0)) as harga,jumlah,bulan',
            'where' => [
                'kode_anggaran' => $anggaran,
                'kode_inventaris'   => $kode_inventaris,
            ],
            'group_by' => 'grup,kode_inventaris,kode_cabang'
        ])->result_array();
        $dKey = get_data('tbl_rencana_aset',[
            'select' => 'DISTINCT kode_cabang,nama_inventaris',
            'where' => [
                'kode_anggaran' => $anggaran,
                'kode_inventaris'   => $kode_inventaris,
            ]
        ])->result_array();
        $data['dSum'] = $dSum;
        $data['dKey'] = $dKey;

        $txt_inventaris = '';
        $txt_status = '';
        $txt_inventaris .= '<th></th>';
        $txt_inventaris .= '<th class="txt_title">Kode Inventaris : <span></span></th>';
        for ($i=0; $i <15 ; $i++) { 
            $txt_inventaris .= '<th></th>';
        }
        if($status_inventaris):
            $txt_status .= '<th></th>';
            $txt_status = '<th>Status : '.$status_inventaris->nama.'</th>';
            for ($i=0; $i <15 ; $i++) { 
                $txt_status .= '<th></th>';
            }
        endif;

        $response   = array(
            'table'     => $this->load->view('transaction/rekap_usulan_aset/table',$data,true),
            'kode_inventaris' => $kode_inventaris,
            'dSum' => $dSum,
            'dKey' => $dKey,
            'txt_inventaris' => $txt_inventaris,
            'txt_status' => $txt_status,

        );
        render($response,'json');
    }

    function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_cabang_txt    = post('kode_cabang_txt');

        $kode_inventaris    = post('kode_inventaris');
        $kode_inventaris_txt= post('kode_inventaris_txt');

        $header = json_decode(post('header'),true);
        $dt     = json_decode(post('data'),true);

        $data = [];
        $key_header = 0;
        if(count($header) == 2):
            $key_header = 1;
            $data[0] = $header[1];
        elseif(count($header) == 3):
            $key_header = 2;
            $data[0] = $header[1];
            $data[1] = $header[2];
        endif;
        foreach($dt as $k => $v){
            $detail = [
                $v[0],
                $v[1],
                $v[2],
            ];
            for ($i=3; $i <=16 ; $i++) { 
                $val = $v[$i];
                if($val != '-'):
                    $val = filter_money($val);
                    $val = (float) $val;
                else:
                    $val = 0;
                endif;
                $detail[] = $val;
            }
            $data[($key_header+$k)] = $detail;
        }

        $config[] = [
            'title' => 'Rekap Usulan Aset',
            'header' => $header[0],
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'Rekap_Usulan_Aset_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_inventaris).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }

    function save_perubahan(){
        $access = get_access($this->controller);
        if(!$access['access_edit']):
            render(['status' => 'failed','message' => lang('cannot_edit'),'load' => ''],'json');
            exit();
        endif;
        $kode_inventaris = str_replace('-', ' ', post('kode_inventaris'));
        $kode_anggaran   = post('kode_anggaran');
        $id_keterangan_inventaris = post('id_status');

        $data['kode_anggaran']      = $kode_anggaran;
        $data['kode_inventaris']    = $kode_inventaris;
        $ck = get_data('tbl_rencana_aset_status',['select' => 'id','where' => $data])->row();
        $data['id'] = '';
        if($ck):
            $data['id'] = $ck->id;
        endif;
        $data['id_keterangan_inventaris'] = $id_keterangan_inventaris;
        $response = save_data('tbl_rencana_aset_status',$data,[],true);
        $response['load'] = '';
        if($response['status'] == 'success') $response['load'] = 'refreshData';
        render($response,'json');
    }
}