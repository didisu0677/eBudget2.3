<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rekap_usulan_aset_group extends BE_Controller {

	var $kode_anggaran;
	var $controller = 'rekap_usulan_aset_group';
	function __construct() {
		parent::__construct();
		$this->kode_anggaran  = user('kode_anggaran');
	}

	function index() {
		$data['tahun'] = get_data('tbl_tahun_anggaran','kode_anggaran',$this->kode_anggaran)->result();
		$data['kode']  = get_data('tbl_grup_asetinventaris',[
			'select' => 'kode,keterangan',
			'where'  => ['is_active' => 1]
		])->result();
		render($data);
	}

	function data($kode_anggaran,$kode){
		if(!$kode_anggaran or !$kode){
			exit();
		}

		$list = get_data('tbl_rencana_aset',[
			'select' 	=> '
				kode_cabang,bulan,
				sum(ifnull(harga,0) * ifnull(jumlah,0)) as total,
			',
			'where' 	=> [
				'kode_anggaran' => $kode_anggaran,
				'grup' 			=> $kode,
			],
			'group_by' => 'kode_cabang,grup,bulan'
		])->result_array();

		$data['list'] 			= $list;
		$data['kode_anggaran']	= $kode_anggaran;
		$data['cabang']	= get_data('tbl_m_cabang',[
			'select' 	=> 'id,kode_cabang,nama_cabang',
			'where'		=> [
				'is_active' => 1,
				'parent_id' => 0,
				'kode_anggaran' => $kode_anggaran,
			],
			'order_by' => 'urutan'
		])->result();

		$view  = $this->load->view('transaction/'.$this->controller.'/table',$data,true);

		render([
			'status' => true,
			'table'	 => $view,
		],'json');

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
        endif;
        foreach($dt as $k => $v){
            $detail = [
                $v[0],
                $v[1],
            ];
            for ($i=2; $i <=13 ; $i++) { 
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
            'title' => $kode_inventaris,
            'header' => $header[0],
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'Rekap_Grup_Usulan_Aset_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_inventaris_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }

}