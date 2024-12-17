<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Usulan_kegiatan_rekap extends BE_Controller {

	var $table 		= 'tbl_rencana_kpromosi';
	var $controller = 'usulan_kegiatan_rekap';
	var $data  		= [];
	function __construct() {
		parent::__construct();
	}

	function index() {
		$data['tahun'] = get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->result();
		render($data);
	}

	function data($kode_anggaran){
		$anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();

		$select_kegiatan = "
			sum(ifnull(b.K_01,0)) as B_01,
			sum(ifnull(b.K_02,0)) as B_02,
			sum(ifnull(b.K_03,0)) as B_03,
			sum(ifnull(b.K_04,0)) as B_04,
			sum(ifnull(b.K_05,0)) as B_05,
			sum(ifnull(b.K_06,0)) as B_06,
			sum(ifnull(b.K_07,0)) as B_07,
			sum(ifnull(b.K_08,0)) as B_08,
			sum(ifnull(b.K_09,0)) as B_09,
			sum(ifnull(b.K_10,0)) as B_10,
			sum(ifnull(b.K_11,0)) as B_11,
			sum(ifnull(b.K_12,0)) as B_12,
		";

		$this->data['cabang'][0] = get_data('tbl_m_cabang a',[
			'select' => 'a.kode_anggaran,a.kode_cabang,a.nama_cabang,a.id,'.$select_kegiatan,
			'join' 	 => [
				$this->table." b on b.kode_anggaran = a.kode_anggaran and b.kode_cabang = a.kode_cabang type left"
			],
			'where'	 => [
				'a.is_active' 		=> 1,
				'a.kode_anggaran'	=> $anggaran->kode_anggaran,
				'a.parent_id' 		=> 0,
			],
			'group_by' => 'a.kode_cabang',
			'order_by' => 'a.urutan'
		])->result();
		foreach($this->data['cabang'][0] as $v) {
			$this->load_cabang($v->id,$anggaran,$select_kegiatan);
		}
		$response   = array(
            'table'     => $this->load->view('transaction/'.$this->controller.'/table',$this->data,true),

        );
        render($response,'json');
	}

	private function load_cabang($id,$anggaran,$select_kegiatan){
		$ls = get_data('tbl_m_cabang a',[
			'select' => 'a.kode_cabang,a.nama_cabang,a.id,'.$select_kegiatan,
			'join' 	 => [
				$this->table." b on b.kode_anggaran = a.kode_anggaran and b.kode_cabang = a.kode_cabang type left"
			],
			'where'	 => [
				'a.is_active' 		=> 1,
				'a.kode_anggaran'	=> $anggaran->kode_anggaran,
				'a.parent_id' 		=> $id,
				'a.kode_cabang !=' 	=> '00100'
			],
			'group_by' => 'a.kode_cabang',
			'order_by' => 'a.urutan'
		])->result();
		if(count($ls)>0):
			$this->data['cabang'][$id] = $ls;
			foreach($this->data['cabang'][$id] as $v) {
				$this->load_cabang($v->id,$anggaran,$select_kegiatan);
			}
		endif;
	}

	function export(){
		ini_set('memory_limit', '-1');
		$kode_anggaran  	= post('kode_anggaran');
		$kode_anggaran_txt  = post('kode_anggaran_txt');

		$header = json_decode(post('header'),true);
        $dt     = json_decode(post('data'),true);

        $data = [];
        $key_header = 0;
        if(count($header) == 2):
            $key_header = 1;
            $data[0] = $header[1];
            for ($i=1; $i <= 13 ; $i++) { 
            	$header[0][] = "";
            }
        elseif(count($header) == 3):
            $key_header = 2;
            $data[0] = $header[1];
            $data[1] = $header[2];
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
            'title' => 'Rekap Usulan Biaya Promosi',
            'header' => $header[0],
            'data'  => $data,
        ];
        // render($config,'json');exit();
        $this->load->library('simpleexcel',$config);
        $filename = 'Rekap_Usulan_Biaya_Promosi_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
	}

}