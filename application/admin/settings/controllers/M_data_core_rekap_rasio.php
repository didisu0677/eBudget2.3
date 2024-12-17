<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class M_data_core_rekap_rasio extends BE_Controller {

	var $controller = 'm_data_core_rekap_rasio';
	var $table = 'tbl_history_import_data_core_rekap_rasio';
	var $col = [];
	var $dir = 'import_datacore_rekaprasio';
	function __construct() {
		parent::__construct();
	}

	function index() {
		$data['controller'] = $this->controller;
		$data['table'] = $this->table;
		render($data);
	}

	function data() {
		$config['access_view']   = false;
        $config['access_edit']  = false;
        $config['button'][]     = button_serverside('btn-info','btn-detail',['fa-search',lang('detil'),true],'act-detil');

        $data = data_serverside($config);
		render($data,'json');
	}

	function detail($id){
        $data = get_data($this->table,'id',$id)->row_array();
        if(isset($data['id'])) {
        	$data['dir'] = $this->dir;
            render($data,'layout:false view:'.'settings/'.$this->controller.'/detail');
        } else echo lang('tidak_ada_data');
    }

	function template(){
		ini_set('memory_limit', '-1');
		$this->col = ['Date(YYYYMM)','Kode','Keterangan'];
		$this->more_cabang(0);
		$config[] = [
			'title' => 'template_import_datacore_rekap_rasio',
			'header' => $this->col,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

	private function more_cabang($id){
		$kode_anggaran = user('kode_anggaran');
		$ls = get_data('tbl_m_cabang',[
			'select' => 'id,kode_cabang,status_group',
			'where' => [
				'kode_anggaran' => $kode_anggaran,
				'is_active'	=> 1,
				'parent_id' => $id,
				'kode_cabang !=' => '00100'
			],
			'order_by' => 'urutan'
		])->result();
		foreach ($ls as $k => $v) {
			if($v->status_group == 0):
				$this->col[] = 'TOT_'.$v->kode_cabang;
			endif;
			$this->more_cabang($v->id);
		}
	}

	function import(){
		ini_set('memory_limit', '-1');
		ini_set('max_execution_time', '100000');

		$this->load->dbforge();

		$bulan 		= post('periode_import');
		$tahun 		= post('tahun_import');
		$file 		= post('fileimport');
        $currency   = post('currency');

        $dt_currency 	= get_currency($currency);
        $periode  		= $tahun . sprintf("%02d", $bulan);

        $table 	= "tbl_history_rekaprasio_".$tahun;

        if($file):
        	if(!table_exists($table)):
        		$fields = [];
	        	$fields['id'] = ['type' => 'BIGINT','null' => TRUE,'auto_increment' => TRUE];
		        $fields['tahun'] = ['type' => 'INT','constraint' => 4,'null' => TRUE];
		        $fields['bulan'] = ['type' => 'INT','constraint' => 4,'null' => TRUE];
		        $fields['periode'] = ['type' => 'VARCHAR','constraint' => 50,'null' => TRUE];
		        $fields['kode'] = ['type' => 'VARCHAR','constraint' => 50,'null' => TRUE];
		        $fields['keterangan'] = ['type' => 'VARCHAR','constraint' => 250,'null' => TRUE];
		        $this->dbforge->add_field($fields);
		        $this->dbforge->add_key('id', TRUE);
				$this->dbforge->create_table($table);
	        endif;
	       	delete_data($table,['tahun' => $tahun,'bulan' => $bulan]);
	        $arr_fields = get_field($table,'name');
	        $this->load->library('PHPExcel');
	        try {
	            $objPHPExcel = PHPExcel_IOFactory::load($file);
	        } catch(Exception $e) {
	            render(['status' => 'failed','message' => $e->getMessage(),'fields' => $arr_fields],'json');exit();
	        }

	        $sheet_count    = $objPHPExcel->getSheetCount();
	        $not_col 		= ['date(yyyymm)','date','kode','keterangan'];
	        $c = 0;
	        for($i = 0; $i < $sheet_count; $i++) {
	            if($i == 0):
	            	$list = $objPHPExcel->getSheet($i)->toArray(null,true,true,true);
	            	$arr_col = [];
	            	foreach ($list as $k => $v) {
	            		if($k == 1):
	            			$fields = [];
	            			foreach ($v as $k2 => $v2) {
	            				$name = str_replace(' ', '_', $v2);
	            				$name = str_replace('-','_',$name);
	            				$name = strtolower($name);
	            				$arr_col[$k2] = $name;
	            				if(!in_array($name,$arr_fields) && !in_array($name,$not_col)):
	            					$arr_fields[] = $name;
	            					$fields[$name] = ['type' => 'DOUBLE','null' => TRUE];
	            				endif;
	            			}
	            			if(count($fields)>0):
	            				$this->dbforge->add_column($table,$fields);
	            			endif;
	            		else:
	            			$data_save = [];
	            			$data_save['tahun'] = $tahun;
	            			$data_save['bulan'] = $bulan;
	            			$data_save['periode'] = $periode;
	            			foreach ($v as $k2 => $v2) {
	            				if(isset($arr_col[$k2])):
	            					$name = $arr_col[$k2];
	            					if(!in_array($name,$not_col)):
	            						$data_save[$name] = filter_money($v2);
	            					elseif($name != ['date(yyyymm)','date']):
	            						$data_save[$name] = $v2;
	            					endif;
	            				endif;
	            			}
	            			$save = save_data($table,$data_save,[],true);
	            			if(isset($save['id'])):
	            				$c++;
	            			endif;
	            		endif;
	            	}
	            endif;
	        }

	        $temp_file  = basename($file);
            $temp_dir   = str_replace($temp_file, '', $file);
            $e          = explode('.', $temp_file);
            $ext        = $e[count($e)-1];
            $new_name   = md5(uniqid()).'.'.$ext;
            $dest       = dir_upload($this->dir).$new_name;
            if(!@copy($file,$dest))
               $file = '';
            else {
                delete_dir(FCPATH . $temp_dir);
                $file = $new_name;
            }
            $data = [];
            $data['kode_anggaran'] 	= user('kode_anggaran');
            $data['periode'] = $periode;
			$data['tanggal'] = date('Y-m-d');
        	$data['file']      = $file;		
			$data['create_at'] = date('Y-m-d H:i:s');
			$data['create_by'] = user('nama');
			$save = insert_data($this->table,$data);
        endif;
        $response = [
			'status' => 'success',
			'message' => $c.' '.lang('data_berhasil_disimpan')
		];
		render($response,'json');
	}

}