<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rekap_mac_group extends BE_Controller {
	var $controller = 'rekap_mac_group';
	var $path       = 'transaction/';
    var $month_before = 0;
    var $path_file  = '';

    function __construct() {
        parent::__construct();
        $this->path_file = base_url().dir_upload('m_budget_control_keterangan');
    }
	
    function index() {
        $p_coa =  $this->input->get('coa');
	 	$tahun = get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->result_array();

        $a     = get_access($this->controller);
	 	$data 					= data_cabang('neraca_new');
        $data['controller']     = $this->controller;
        $data['coa'] 			= $this->coa_option();
        $data['p_coa']          = $p_coa;
        $data['tahun']     		= $tahun;
        $data['bulan']     		= $this->month_option();
        $data['path_file']      = $this->path_file;
        $data['access_additional']  = $a['access_additional'];
        render($data);
    }

    private function coa_option(){
    	$data = get_data('tbl_m_budget_control_group a',[
    		'select' 	=> 'a.coa,b.glwdes as name',
    		'where'		=> [
                'a.is_active' => 1,
                'a.kode_anggaran' => user('kode_anggaran')
            ],
    		'join'		=> "tbl_m_coa b on a.coa = b.glwnco and b.kode_anggaran = '".user('kode_anggaran')."'",
    		'order_by'	=> 'a.id',
    	])->result_array();
    	return $data;
    }
    private function month_option(){
    	$data = array();
    	for ($i=1; $i <=12 ; $i++) { 
    		$month = month_lang($i);
    		array_push($data, array('value' => $i,'name' => $month));
    	}
    	return $data;
    }

    function get_content(){
    	$bulan 		= post('bulan');
    	$tahun 		= post('tahun');
    	$coa 		= post('coa');
    	$cabang 	= post('cabang');

    	$tahun  = get_data('tbl_tahun_anggaran','kode_anggaran',$tahun)->row();
    	$cabang = get_data('tbl_m_cabang',[
            'where' => [
                'kode_cabang' => $cabang,
                'kode_anggaran' => $tahun->kode_anggaran
            ]
        ])->row();
    	$coa    = get_data('tbl_m_coa',[
            'where' => [
                'glwnco' => $coa,
                'kode_anggaran' => $tahun->kode_anggaran
            ]
        ])->row();

    	$data['bulan'] = $bulan;
    	$data['tahun'] = $tahun;
    	$data['coa']   = $coa;
    	$data['cabang'] = $cabang;
    	$view 	= $this->load->view($this->path.$this->controller.'/content',$data,true);

    	render([
    		'view' => $view,
    	],'json');
    }

    function data(){
    	$bulan 		= post('bulan');
    	$tahun 		= post('tahun');
    	$coa 		= post('coa');
    	$cabang 	= post('cabang');

    	$tahun  = get_data('tbl_tahun_anggaran','kode_anggaran',$tahun)->row();
    	$coa    = get_data('tbl_m_coa',[
            'where' => [
                'glwnco' => $coa,
                'kode_anggaran' => $tahun->kode_anggaran
            ]
        ])->row();

    	$level  		= $this->coa_level($coa);
    	$index_level 	= $level;
    	
    	$arrCoa = [];
    	$dt 	= [];
    	$detail = $this->dt_data('',$coa->glwnco,$tahun,$bulan,$cabang);//-1
    	array_push($arrCoa,$detail->glwnco);

    	$level += 1;
    	if($level<=5)://0
    		$key1 = 'l'.$level;
    		$dt[$key1][$detail->glwnco] = $this->dt_data($level,$detail->glwnco,$tahun,$bulan,$cabang);
    		if(($level+1)<=5)://1
    			foreach ($dt[$key1][$detail->glwnco] as $k => $v) {
    				array_push($arrCoa,$v->glwnco);
    				$key2 = 'l'.($level+1);
    				$dt[$key2][$v->glwnco] = $this->dt_data(($level+1),$v->glwnco,$tahun,$bulan,$cabang);
    				
    				if(($level+2)<=5)://2
		    			foreach ($dt[$key2][$v->glwnco] as $k2 => $v2) {
		    				array_push($arrCoa,$v2->glwnco);
		    				$key3 = 'l'.($level+2);
		    				$dt[$key3][$v2->glwnco] = $this->dt_data(($level+2),$v2->glwnco,$tahun,$bulan,$cabang);

		    				if(($level+3)<=5)://3
				    			foreach ($dt[$key3][$v2->glwnco] as $k3 => $v3) {
				    				array_push($arrCoa,$v3->glwnco);
				    				$key4 = 'l'.($level+3);
				    				$dt[$key4][$v3->glwnco] = $this->dt_data(($level+3),$v3->glwnco,$tahun,$bulan,$cabang);

				    				if(($level+4)<=5)://4
						    			foreach ($dt[$key4][$v3->glwnco] as $k4 => $v4) {
						    				array_push($arrCoa,$v4->glwnco);
						    				$key5 = 'l'.($level+4);
						    				$dt[$key5][$v4->glwnco] = $this->dt_data(($level+4),$v4->glwnco,$tahun,$bulan,$cabang);
						    				foreach ($dt[$key5][$v4->glwnco] as $k5 => $v5) {
						    					if(($level+4)<=5)://5
						    						$key6 = 'l'.($level+5);
						    						$dt[$key5][$v4->glwnco] = $this->dt_data(($level+5),$v5->glwnco,$tahun,$bulan,$cabang);
						    						foreach ($dt[$key5][$v4->glwnco] as $k6 => $v6) {
						    							array_push($arrCoa,$v5->glwnco);
						    						}
						    					endif;
						    					array_push($arrCoa,$v5->glwnco);
						    				}
						    			}
						    		endif;
				    			}
				    		endif;
		    			}
		    		endif;
    			}
    		endif;
    	endif;

    	$status = true;
    	$tbl_history = 'tbl_history_'.($tahun->tahun_anggaran-1);
        $TOT = 'TOT_'.$cabang;
    	if(!$this->db->table_exists($tbl_history)):
    		$status = false;
        elseif(!$this->db->field_exists($TOT, $tbl_history)):
            $status = false;
    	endif;

    	$dt_bulan = [];
    	if($status):
    		$TOT = 'TOT_'.$cabang;
    		$dt_bulan = get_data($tbl_history.' a',[
    			'select'	=> 'a.glwnco,b.kali_minus,'.$TOT,
                'join'      => "tbl_m_coa b on b.glwnco = a.glwnco and b.kode_anggaran = '".$tahun->kode_anggaran."'",
    			'where' => [
    				'a.bulan' 	=> $bulan,
    				'a.glwnco'	=> $arrCoa,
    			]
    		])->result_array();
    	endif;
    	
    	$data['level']  	= $index_level;
    	$data['detail'] 	= $detail;
    	$data['dt'] 		= $dt;
    	$data['dt_bulan'] 	= $dt_bulan;
    	$data['bulan'] 		= 'B_'.sprintf("%02d", $bulan);
    	$data['tot'] 		= 'TOT_'.$cabang;
    	$view = $this->load->view($this->path.$this->controller.'/table',$data,true);

    	render([
    		'view' => $view,
    		'dt' => $dt,
    	],'json');
    }

    private function coa_level($coa){
    	$level = -1;
    	if($coa->level0) $level = 0;
    	if($coa->level1) $level = 1;
    	if($coa->level2) $level = 2;
    	if($coa->level3) $level = 3;
    	if($coa->level4) $level = 4;
    	if($coa->level5) $level = 5;

    	return $level;
    }

    private function dt_data($parentID,$coa,$tahun,$bulan, $cabang){
        $dt_column = $this->check_column();
        $tabel  = $dt_column['tabel'];
        $column = $dt_column['column'];
        $where  = $dt_column['where'];

        if(!$coa):
        	return [];
        endif;

        $where_1 = "a.kode_anggaran = '".user('kode_anggaran')."' and a.is_active = '1' and a.glwnco = '$coa'";
        if(strlen($parentID)>0):
        	$level = 'a.level'.$parentID;
        	$where_1 = "a.kode_anggaran = '".user('kode_anggaran')."' and a.is_active = '1' and $level = '$coa'";
        endif;

        $select = [
            'select'    => 
                'a.glwnco,a.glwdes,a.level0,a.level1,a.level2,a.level3,a.level4,'.
                $column,
            'where'     => $where_1,
            'join'      => [
                "$tabel c on $where = a.glwnco and c.kode_cabang = '$cabang' and c.kode_anggaran = '$tahun->kode_anggaran' TYPE LEFT"
            ]
        ];

        if(strlen($parentID)>0):
        	$data = get_data('tbl_m_coa a',$select)->result();
        else:
        	$data = get_data('tbl_m_coa a',$select)->row();
        endif;

        return $data;
    }

    private function check_column(){
        $coa    = post('coa');
        $bulan  = post('bulan');
        
        $dt  = get_data('tbl_m_budget_control_group a',[
            'select' => 'a.tabel,b.tipe',
            'join' => [
                "tbl_m_coa b on b.glwnco = a.coa and b.kode_anggaran = a.kode_anggaran and b.glwnco != ''"
            ],
            'where'  => [
                'a.coa' => $coa,
                'a.is_active' => 1,
                'a.kode_anggaran' => user('kode_anggaran') 
            ]
        ])->row();
        $column = '';
        $tabel  = '';
        $where  = '';
        if($dt):
            $tabel = $dt->tabel;
            if($dt->tipe == 1):
                $tabel = 'tbl_budget_nett_neraca';
            elseif($dt->tipe == 1):
                $tabel = 'tbl_budget_nett_labarugi';
            endif;
            if($dt->tabel == 'tbl_budget_plan_neraca'):
                $c  = 'c.B_'.sprintf("%02d", $bulan);
                $as = 'B_'.sprintf("%02d", $bulan);
                $column .= $c.' as '.$as.', ';
                if($this->month_before):
                    $c  = 'c.B_'.sprintf("%02d", $this->month_before);
                    $as = 'B_'.sprintf("%02d", $this->month_before);
                    $column .= $c.' as '.$as.', ';
                endif;
                $where = 'c.coa';
            elseif($dt->tabel == 'tbl_budget_nett'):
                $c  = 'c.B_'.sprintf("%02d", $bulan);
                $as = 'B_'.sprintf("%02d", $bulan);
                $column .= $c.' as '.$as.', ';
                if($this->month_before):
                    $c  = 'c.B_'.sprintf("%02d", $this->month_before);
                    $as = 'B_'.sprintf("%02d", $this->month_before);
                    $column .= $c.' as '.$as.', ';
                endif;
                $where = 'c.coa';
            elseif($dt->tabel == 'tbl_labarugi'):
                $c  = 'c.bulan_'.$bulan;
                $as = 'B_'.sprintf("%02d", $bulan);
                $column .= $c.' as '.$as.', ';
                if($this->month_before):
                    $c  = 'c.bulan_'.$this->month_before;
                    $as = 'B_'.sprintf("%02d", $this->month_before);
                    $column .= $c.' as '.$as.', ';
                endif;
                $where = 'c.glwnco';
            endif;
        endif;

        $data = [
            'column'    => $column,
            'tabel'     => $tabel,
            'where'     => $where,
        ];

        return $data;
    }

    function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_anggaran      = post('kode_anggaran');

        $kode_cabang_txt    = post('kode_cabang_txt');
        $kode_cabang        = post('kode_cabang');

        $coa        = post('coa');
        $coa_txt    = post('coa_txt');

        $bulan      = post('bulan');
        $bulan_txt  = post('bulan_txt');

        $dt = json_decode(post('data'),true);

        $classnya = '.d-'.$bulan.'-'.$coa.'-'.$kode_cabang;
        $h1 = $dt[$classnya]['header'][1];

        $header = $h1;
        $data = [];
        foreach([$classnya] as $name){
            if(isset($dt[$name])):
                $count2 = 0;
                foreach($dt[$name]['data'] as $k => $v){
                    $count2 = count($v);
                    $detail = [
                        $v[0],
                        $v[1],
                        $v[2],
                    ];
                    for ($i=3; $i < $count2 ; $i++) { 
                        $val = '';
                        if(strlen($v[$i])>0):
                            $val = filter_money($v[$i]);
                        endif;
                        $detail[] = $val;
                    }
                    $data[] = $detail;
                }
            endif;
        }

        $title = 'Rekap mac group';
        $config[] = [
            'title' => $title.' ('.get_view_report().')',
            'header' => $header,
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = $title.'_'.$kode_anggaran_txt.'_'.$kode_cabang_txt.'_'.$coa_txt.'_'.$bulan_txt.'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }
}