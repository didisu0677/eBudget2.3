<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cab_data_kantor extends BE_Controller {
    var $controller = 'cab_data_kantor';
	function __construct() {
		parent::__construct();
	}

	function index() { 
        $a      = get_access($this->controller);
        $data   = data_cabang($this->controller);
        $data['access_additional']  = $a['access_additional'];
        $data['controller'] = $this->controller;
        render($data);
    }

    function data($kode_anggaran="",$kode_cabang=""){
        $data = array();

        $cabang = get_data('tbl_m_cabang',[
            'where' => [
                'kode_cabang'   => $kode_cabang,
                'kode_anggaran' => $kode_anggaran
            ]
        ])->row();
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
        if(!$anggaran):
        	render(['status' => true,'message' => 'anggaran not found'],'json');exit();
        endif;

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
        $data['status'] 	 = true;
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

}