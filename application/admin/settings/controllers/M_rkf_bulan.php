<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class M_rkf_bulan extends BE_Controller {

	var $kode_anggaran = '';
	var $table = 'tbl_m_rkf_bulan';
	function __construct() {
		parent::__construct();
		$this->kode_anggaran = user('kode_anggaran');
	}

	function index() {
		$ck = get_data($this->table,'kode_anggaran',$this->kode_anggaran)->row();
		if(!$ck):
			$id = insert_data($this->table,['kode_anggaran' => $this->kode_anggaran,'bulan' => 1,'nama' => month_lang(1), 'is_active' => 1]);
			$this->check_rkf_bulan($id);
		endif;
		$data['tahun'] 	= get_data('tbl_tahun_anggaran','kode_anggaran',$this->kode_anggaran)->result();
		$data['bulan'] 	= $this->filter_bulan();
		render($data);
	}

	private function filter_bulan(){
		$data = [];
		for ($i=1; $i <= 12 ; $i++) { 
			array_push($data,['value' => $i, 'name' => month_lang($i)]);
		}
		return $data;
	}

	function data() {
		$config['where']['kode_anggaran'] = $this->kode_anggaran;
		$data = data_serverside($config);
		$data['accessView'] = false;
		render($data,'json');
	}

	function get_data() {
		$data = get_data($this->table,'id',post('id'))->row_array();
		render($data,'json');
	}

	function save() {
		$data = post();
		$data['kode_anggaran'] = $this->kode_anggaran;
		$data['nama'] = month_lang(post('bulan'));
		$response = save_data($this->table,$data,post(':validation'));
		if(isset($response['id'])):
			$this->check_rkf_bulan($response['id']);
		endif;
		render($response,'json');
	}

	function delete() {
		$response = destroy_data($this->table,'id',post('id'));
		render($response,'json');
	}

	function template() {
		ini_set('memory_limit', '-1');
		$arr = ['bulan' => 'bulan','is_active' => 'is_active'];
		$config[] = [
			'title' => 'template_import_m_rkf_bulan',
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

	function import() {
		ini_set('memory_limit', '-1');
		$file = post('fileimport');
		$col = ['bulan','is_active'];
		$this->load->library('simpleexcel');
		$this->simpleexcel->define_column($col);
		$jml = $this->simpleexcel->read($file);
		$c = 0;
		foreach($jml as $i => $k) {
			if($i==0) {
				for($j = 2; $j <= $k; $j++) {
					$data = $this->simpleexcel->parsing($i,$j);
					$data['create_at'] = date('Y-m-d H:i:s');
					$data['create_by'] = user('nama');
					$save = insert_data($this->table,$data);
					if($save) $c++;
				}
			}
		}
		$response = [
			'status' => 'success',
			'message' => $c.' '.lang('data_berhasil_disimpan').'.'
		];
		@unlink($file);
		render($response,'json');
	}

	function export() {
		ini_set('memory_limit', '-1');
		$arr = ['bulan' => 'Bulan','is_active' => 'Aktif'];
		$data = get_data($this->table,'kode_anggaran',$this->kode_anggaran)->result_array();
		$config = [
			'title' => 'data_m_rkf_bulan',
			'data' => $data,
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

	function check_rkf_bulan($id){
		$ck = get_data($this->table,'id',$id)->row();
		if($ck && $ck->bulan):
			$detail = get_data('tbl_input_rkf_detail a',[
                'select' => 'a.id,a.status,b.kode_anggaran,a.keterangan,a.keterangan2,a.keterangan3',
                'join'   => [
                    'tbl_input_rkf b on b.id = a.id_input_rkf'
                ],
                'where'  => [
                    'kode_anggaran' => $ck->kode_anggaran
                ]
            ])->result();
            foreach($detail as $k => $v){
            	$ck_rkf_bulan = get_data('tbl_input_rkf_detail_status',[
                    'select' => 'id',
                    'where' => [
                        'id_input_rkf_detail' => $v->id,
                        'bulan' => $ck->bulan
                    ]
                ])->row();
                $data_rkf_bulan = [
                    'id' => '',
                    'id_input_rkf_detail' => $v->id,
                    'bulan'     => $ck->bulan,
                    'status'    => $v->status,
                    'keterangan'=> $v->keterangan,
                    'keterangan2'=> $v->keterangan2,
                    'keterangan3'=> $v->keterangan3,
                ];
                if(!$ck_rkf_bulan):
                    $res2 = save_data('tbl_input_rkf_detail_status',$data_rkf_bulan,[],true);
                endif;
            }
		endif;
	}
}