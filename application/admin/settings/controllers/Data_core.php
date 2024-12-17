<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Data_core extends BE_Controller {

	function __construct() {
		parent::__construct();
	}

	function index() {
		$data = data_cabang();
		$data['tahun'] = get_data('tbl_trx_import_core',[
			'select' => 'distinct SUBSTRING(periode_import,1,4) as tahun',
			'order_by' => 'tahun'
		])->result();

		render($data);
	}

	function data($tahun,$cabang){
		$column 	 = 'TOT_'.$cabang;
		$tbl_history = 'tbl_history_'.$tahun;
		$tbl_history_status = true;
		if(!$this->db->table_exists($tbl_history)):
            $tbl_history_status = false;
        endif;
        if ($tbl_history_status && !$this->db->field_exists($column, $tbl_history)):
            $tbl_history_status = false;
        endif;
        if($tbl_history_status):
            for ($i=1; $i <=2 ; $i++) { 
            	${'coa_'.$i} = get_data('tbl_m_coa b',[
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
	                    a.account_name,
	                    a.coa,
	                    a.gwlsbi,
	                    a.glwnco,
	                    b.kali_minus,
	                    b.glwdes,
	                    b.level0,
	                    b.level1,
	                    b.level2,
	                    b.level3,
	                    b.level4,
	                    b.level5,
	                    ",
	                'join' 		=> $tbl_history.' a on b.glwnco = a.glwnco type left',
	                'where'		=> [
	                	'b.tipe' 		=> $i,
	                	'b.is_active'	=> 1,
	                	'b.kode_anggaran' => user('kode_anggaran')
	                ],
	                'group_by' 	=> 'a.glwnco',
	                'order_by'	=> 'b.urutan'
	            ])->result();
            }
        else:
        	for ($i=1; $i <=2 ; $i++) { 
            	${'coa_'.$i} = get_data('tbl_m_coa b',[
            		'select'	=> '
            			b.*,
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
        			',
            		'where'		=> [
	                	'b.tipe' 		=> $i,
	                	'b.is_active'	=> 1,
	                	'b.kode_anggaran' => user('kode_anggaran')
	                ],
	                'order_by' => 'urutan'
            	])->result();
            }
        endif;

        $data['neraca'] 	= coa_neraca($coa_1);
        $data['labarugi'] 	= coa_labarugi($coa_2);

        $response	= array(
			'table'			=> $this->load->view('settings/data_core/table',$data,true),
		);

		render($response,'json');
	}

}