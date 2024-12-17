<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class M_tabungan_biaya_hadiah extends BE_Controller {

	var $controller 	= 'M_tabungan_biaya_hadiah';
	var $table 			= 'tbl_m_tabungan_biaya_hadiah';
	var $kode_anggaran 	= '';
	var $arr_coa 		= ['412','416'];
	function __construct() {
		parent::__construct();
		$this->kode_anggaran = user('kode_anggaran');
	}

	function index() {
		$data['tahun'] 	= get_data('tbl_tahun_anggaran','kode_anggaran',$this->kode_anggaran)->result();
		$data['coa'] 	= $this->ls_coa();
		$data['struktur_cabang'] 	= get_data('tbl_m_struktur_cabang',[
			'select' => 'id,struktur_cabang',
			'where'	 => ['is_active' => 1]
		])->result_array();
		render($data);
	}

	private function ls_coa(){
		$data = get_data('tbl_m_coa',[
			'select' => 'glwnco,glwdes',
			'where'	 => [
				'is_active' 	=> 1,
				'kode_anggaran' => $this->kode_anggaran,
				'glwnco' 		=> $this->arr_coa
			],
			'order_by' => 'urutan'
		])->result_array();
		foreach($data as $k => $v){
			$data[$k]['glwdes'] = $v['glwnco'].' - '.str_replace('-','',remove_spaces($v['glwdes']));
		}
		return $data;
	}

	function data() {
		$config['where']['kode_anggaran'] = $this->kode_anggaran;
		$data = data_serverside($config);
		foreach($data['data'] as $k => $v){
			for ($i=1; $i <= 12 ; $i++) { 
				$field = $this->table.'_B_'.sprintf("%02d", $i);
				$data['data'][$k][$field] = view_report($v[$field]);
			}
		}
		$data['accessView'] = false;
		render($data,'json');
	}

	function get_data() {
		$data = get_data('tbl_m_tabungan_biaya_hadiah','id',post('id'))->row_array();
		for ($i=1; $i <= 12 ; $i++) { 
			$field = 'B_'.sprintf("%02d", $i);
			$data[$field] = "".view_report($data[$field]);
		}
		render($data,'json');
	}

	function save() {
		$this->validate();
		$data = post();
		$data['kode_anggaran'] = $this->kode_anggaran;

		for ($i=1; $i <= 12 ; $i++) { 
			$field = 'B_'.sprintf("%02d", $i);
			$nominal = insert_view_report(filter_money($data[$field]));
			$data[$field] = $nominal;
		}

		$response = save_data('tbl_m_tabungan_biaya_hadiah',$data,post(':validation'));
		render($response,'json');
	}

	private function validate(){
		$where['kode_anggaran'] = $this->kode_anggaran;
		$where['coa'] 			= post('coa');
		$where['id_struktur_cabang'] = post('id_struktur_cabang');
		if(post('id')):
			$where['id !='] = post('id');
		endif;
		$ck = get_data($this->table,['where' => $where])->row();
		if($ck):
			$message = lang('coa').' '.post('coa');
			$message .= ' dan '.lang('struktur_cabang').' '.post('struktur_cabang').' ';
			render([
				'status' 	=> 'warning',
				'message'	=> $message.lang('sudah_ada'),
			],'json');
			exit();
		endif;
	}

	function delete() {
		$response = destroy_data('tbl_m_tabungan_biaya_hadiah','id',post('id'));
		render($response,'json');
	}

	function template() {
		ini_set('memory_limit', '-1');
		$arr = ['coa' => 'coa','nama' => 'nama','struktur_cabang' => 'struktur_cabang'];
		for ($i=1; $i <= 12 ; $i++) { 
			$field = 'B_'.sprintf("%02d", $i);
			$arr[$field] = month_lang($i).' ('.get_view_report().')';
		}
		$arr['keterangan'] = 'keterangan';
		$arr['is_active'] = 'is_active';

		$config[] = [
			'title' => 'template_import_m_tabungan_biaya_hadiah',
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

	function import() {
		ini_set('memory_limit', '-1');
		$file = post('fileimport');
		$currency   = post('currency');

        $dt_currency = get_currency($currency);

		$col = ['coa','nama','struktur_cabang'];
		for ($i=1; $i <= 12 ; $i++) { 
			$field = 'B_'.sprintf("%02d", $i);
			$col[] = $field;
		}
		$col[] = 'keterangan';
		$col[] = 'is_active';

		$this->load->library('simpleexcel');
		$this->simpleexcel->define_column($col);
		$jml = $this->simpleexcel->read($file);
		$c = 0;
		foreach($jml as $i => $k) {
			if($i==0) {
				for($j = 2; $j <= $k; $j++) {
					$data = $this->simpleexcel->parsing($i,$j);
					$data['kode_anggaran'] = $this->kode_anggaran;
					$data['id'] = '';
					$ck_struktur = get_data('tbl_m_struktur_cabang',[
						'select' => 'id',
						'where'  => [
							'struktur_cabang like' => strtolower($data['struktur_cabang'])
						]
					])->row();
					if($ck_struktur and in_array($data['coa'],$this->arr_coa)):
						$data['id_struktur_cabang'] = $ck_struktur->id;
						for ($bln=1; $bln <= 12 ; $bln++) { 
							$field 	= 'B_'.sprintf("%02d", $bln);
							$data[$field] = checkNumber($data[$field]) * $dt_currency['nilai'];
						}

						$ck = get_data($this->table,[
							'select' => 'id',
							'where'	 => [
								'kode_anggaran' => $this->kode_anggaran,
								'coa' 			=> $data['coa'],
								'id_struktur_cabang' => $ck_struktur->id,
							]
						])->row();
						if($ck):
							$data['id'] = $ck->id;
						endif;
						$res = save_data($this->table,$data,[],true);
						if(isset($res['id'])):
							$c++;
						endif;
					endif;
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
		$arr = ['coa' => lang('coa'),'nama' => lang('nama'),'struktur_cabang' => lang('struktur_cabang')];
		for ($i=1; $i <= 12 ; $i++) { 
			$field = 'B_'.sprintf("%02d", $i);
			$arr[$field] = month_lang($i).' ('.get_view_report().')';
		}
		$arr['keterangan'] = lang('keterangan');
		$arr['is_active'] = lang('aktif');
		$data = get_data('tbl_m_tabungan_biaya_hadiah','kode_anggaran',$this->kode_anggaran)->result_array();
		foreach($data as $k => $v){
			for ($i=1; $i <= 12 ; $i++) { 
				$field = 'B_'.sprintf("%02d", $i);
				$data[$k][$field] = view_report($v[$field]);
			}
		}
		$config = [
			'title' => 'data_m_tabungan_biaya_hadiah',
			'data' => $data,
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

}