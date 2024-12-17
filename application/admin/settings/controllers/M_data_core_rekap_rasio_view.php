<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class M_data_core_rekap_rasio_view extends BE_Controller {

	var $table 			= 'tbl_history_import_data_core_rekap_rasio';
	var $controller 	= 'm_data_core_rekap_rasio_view';
	function __construct() {
		parent::__construct();
	}

	function index() {
		$data = data_cabang();
		$data['controller'] = $this->controller;
		$data['tahun'] = get_data($this->table,[
			'select' => 'distinct SUBSTRING(periode,1,4) as tahun',
			'order_by' => 'tahun'
		])->result();

		render($data);
	}

	function data($tahun,$cabang){
		$column 	 = 'tot_'.$cabang;
		$tbl_history = 'tbl_history_rekaprasio_'.$tahun;
		$tbl_history_status = true;
		if(!$this->db->table_exists($tbl_history)):
            $tbl_history_status = false;
        endif;
        if ($tbl_history_status && !$this->db->field_exists($column, $tbl_history)):
            $tbl_history_status = false;
        endif;
        if($tbl_history_status):
            $list = get_data('tbl_m_rekaprasio b',[
                'select' => "
                    coalesce(sum(case when bulan = '1' then ".$column." end), 0) as B_01,
                    coalesce(sum(case when bulan = '2' then ".$column." end), 0) as B_02,
                    coalesce(sum(case when bulan = '3' then ".$column." end), 0) as B_03,
                    coalesce(sum(case when bulan = '4' then ".$column." end), 0) as B_04,
                    coalesce(sum(case when bulan = '5' then ".$column." end), 0) as B_05,
                    coalesce(sum(case when bulan = '6' then ".$column." end), 0) as B_06,
                    coalesce(sum(case when bulan = '7' then ".$column." end), 0) as B_07,
                    coalesce(sum(case when bulan = '8' then ".$column." end), 0) as B_08,
                    coalesce(sum(case when bulan = '9' then ".$column." end), 0) as B_09,
                    coalesce(sum(case when bulan = '10' then ".$column." end), 0) as B_10,
                    coalesce(sum(case when bulan = '11' then ".$column." end), 0) as B_11,
                    coalesce(sum(case when bulan = '12' then ".$column." end), 0) as B_12,
                    b.kode,
                    b.keterangan,
                    b.tipe
                    ",
                'join' 		=> $tbl_history.' a on b.kode = a.kode type left',
                'where'		=> [
                	'b.is_active'	=> 1,
                	// 'b.kode_anggaran' => user('kode_anggaran')
                ],
                'group_by' 	=> 'b.kode',
                'order_by'	=> 'b.urutan'
            ])->result();
        else:
        	$list = get_data('tbl_m_rekaprasio b',[
        		'select'	=> '
        			0 as B_01,
        			0 as B_02,
        			0 as B_03,
        			0 as B_04,
        			0 as B_05,
        			0 as B_06,
        			0 as B_07,
        			0 as B_08,
        			0 as B_09,
        			0 as B_10,
        			0 as B_11,
        			0 as B_12,
        			b.kode,
        			b.keterangan,
        			b.tipe
    			',
        		'where'		=> [
                	'b.is_active'	=> 1,
                	// 'b.kode_anggaran' => user('kode_anggaran')
                ],
                'order_by' => 'urutan'
        	])->result();
        endif;
        $data['list'] = $list;
        $response	= array(
			'table'			=> $this->load->view('settings/'.$this->controller.'/table',$data,true),
		);

		render($response,'json');
	}

}