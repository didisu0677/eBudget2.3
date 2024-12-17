<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rekap_target_lainnya extends BE_Controller {

	var $detail_tahun;
    var $kode_anggaran;
    var $controller = 'rekap_target_lainnya';
    var $table = 'tbl_target_lainnya';
	function __construct() {
		parent::__construct();
		$this->kode_anggaran  = user('kode_anggaran');
        $this->detail_tahun   = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => [
                'a.kode_anggaran' => $this->kode_anggaran,
            ],
            'order_by' => 'tahun,bulan'
        ])->result();
	}

	function index() {
		$tahun_anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$this->kode_anggaran)->result();
		$data['tahun']  = $tahun_anggaran;
		$data['detail_tahun']    = $this->detail_tahun;
		$data['controller'] 	 = $this->controller;
		$data['coa'] 	= get_data('tbl_m_target_lainnya',[
			'select' => 'id,kode,nama',
			'where'  => [
				'kode_anggaran' => $this->kode_anggaran,
				'is_active' 	=> 1
			]
		])->result();
		render($data);
	}

	function data($kode_anggaran,$target){
		$anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
		if(!$anggaran){
			render(['status' => false,'message' => 'anggaran not found'],'json');exit();
		}
		$list = get_data($this->table,[
			'where' => [
				'id_target_lainnya' => $target,
			]
		])->result_array();
        $cabang = $this->cabang(0,[],$kode_anggaran);

        $data['cabang'] 		= $cabang;
        $data['detail_tahun']   = $this->detail_tahun;
        $data['list']           = $list;
		$data['anggaran']       = $anggaran;

        $response   = array(
        	'status' 	=> true,
            'table'     => $this->load->view('transaction/'.$this->controller.'/table',$data,true),
        );
        render($response,'json');
	}

	private function cabang($id,$data,$kode_anggaran){
		$where = [
			'a.is_active' 		=> 1,
			'a.kode_anggaran' 	=> $kode_anggaran,
			'a.parent_id' 		=> 0,
			'a.kode_cabang !='  => '00100'
		];
		if($id):
			$where['a.parent_id'] = $id;
		endif;

		$dt = get_data('tbl_m_cabang a',[
			'select' 	=> 'a.id,a.kode_cabang,a.nama_cabang',
			'where' 	=> $where,
			'order_by'	=> 'a.urutan',
		])->result();
		if(count($dt)>0):
			$data[$id] = $dt;
			foreach($dt as $v){
				$data = $this->cabang($v->id,$data,$kode_anggaran);
			}
		endif;
		return $data;
	}

	function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_cabang_txt    = post('kode_cabang_txt');

        $coa    = post('coa');
        $coa_txt= post('coa_txt');

        $header = json_decode(post('header'),true);
        $dt     = json_decode(post('data'),true);

        $data = [];
        foreach($dt as $k => $v){
            $detail = [
                $v[0],
                $v[1],
            ];

            foreach($this->detail_tahun as $k2 => $v2){
                $val = $v[($k2+2)];
                 if($val):
                    $val = filter_money($val);
                    $val = (float) $val;
                else:
                    $val = 0;
                endif;
                $detail[] = $val;
            }
            $data[$k] = $detail;
        }

        $config[] = [
            'title' => 'Rekap Target Lainnya',
            'header' => $header[0],
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'Rekap_target_lainnya_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $coa_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }

}