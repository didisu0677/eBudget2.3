<?php defined('BASEPATH') OR exit('No direct script access allowed');
// Create by MW 20201201

function check_min_value($v,$x){
	$val = kali_minus($v,$x);
	// $val = custom_format($val);
	$val = custom_format(view_report($val));
	return $val;
}
function check_value($v,$p1=false){
	// $val = kali_minus($v,$x);
	if($p1):
		$v = round(view_report($v),-2);
	else:
		$v = view_report($v);
	endif;
	$val = custom_format($v);
	return $val;
}

function round_value($val){
	$val = round(view_report($val),-2);
	$val = insert_view_report($val);
	return $val;
}

function remove_spaces($val){
	return preg_replace('/^\p{Z}+|\p{Z}+$/u', '', $val);
}

function checkRealisasiKolektibilitas($p1,$data){
	if($p1['tahun'] != $p1['tahun_core']):
		$key = multidimensional_search($data, array(
			'tahun_core' => $p1['tahun_core'],
			'id_kolektibilitas' => $p1['id'],
			'parent_index' => $p1['cabang'],
		));
		$d = $data[$key];
	else:
		$key = multidimensional_search($data, array(
			'tahun_core' => $p1['tahun_core'],
			'id_kolektibilitas' => $p1['id'],
			'parent_index' => '0'
		));
		$d = $data[$key];
	endif;
	return $d;
}

function checkMonthAnggaran($anggaran){
	$bulan 	= sprintf('%02d', $anggaran->bulan_terakhir_realisasi);
	$date 	= "01-".$bulan.'-'.$anggaran->tahun_terakhir_realisasi;
	return minusMonth($date,1);
}

function minusMonth($date,$minus){
	$date = date("m-Y", strtotime($date." -".$minus." months"));
	return $date;
}

function insert_formula_kolektibilitas($data,$anggaran){
	$d=[];
	$table = 'tbl_formula_kolektibilitas';
	foreach ($data as $k => $v) {
		$x 		= explode("-", $k);
		$coa 	= $x[0];
		$thn 	= $x[1];
		$sumber_data = $x[2];
		$cabang = $x[3];

		$h = [
			'coa' => $coa,
		];
		$h['kode_anggaran'] 		= $anggaran->kode_anggaran;
		$h['tahun_anggaran'] 		= $anggaran->tahun_anggaran;
		$h['keterangan_anggaran'] 	= $anggaran->keterangan;
		$h['kode_cabang']			= $cabang;
		$h['tahun_core'] 			= $thn;
		$h['changed'] 				= '[]';
		// $h['sumber_data'] 			= $sumber_data;
		foreach ($v as $k2 => $v2) {
			$h[$k2] 					= $v2;
		}
		$ck = get_data($table,[
			'select'	=> 'id',
			'where'		=> "kode_anggaran = '$anggaran->kode_anggaran' and kode_cabang = '$cabang' and coa = '$coa' and tahun_core = '$thn'"
		])->result();
		if(count($ck)<=0):
			insert_data($table,$h);
		endif;
		$d[] = $h;
	}
	// render($d,'json');
}

function update_formula_kolektibilitas($data,$anggaran){
	$kode_anggaran 		= $anggaran->kode_anggaran;
	$tahun_anggaran 	= $anggaran->tahun_anggaran;
	$keterangan_anggaran 	= $anggaran->keterangan;
	$table = 'tbl_formula_kolektibilitas';
	foreach ($data as $k => $v) {
		$x 		= explode('-', $k);
		$id 	= $x[0];
		$coa 	= $x[1];
		$thn 	= $x[2];
		$sumber_data = $x[3];
		$cabang = $x[4];
		if(strlen(strpos($coa,'sumkol123'))>0):
			$ck = get_data($table,[
				'select'	=> 'id',
				'where' 	=> "coa = '$coa' and kode_cabang = '$cabang' and kode_anggaran = '$kode_anggaran' and tahun_core = '$thn'",
			])->result();
			if(count($ck)>0):
				$where = [
                    'coa' => $coa,
                    'tahun_core' => $thn,
                    'kode_cabang' => $cabang,
                    'kode_anggaran' => $kode_anggaran,
                ];
				update_data($table,$v,$where);
			else:
				$h = $v;
				$h['coa'] 					= $coa;
				$h['kode_anggaran'] 		= $anggaran->kode_anggaran;
				$h['tahun_anggaran'] 		= $anggaran->tahun_anggaran;
				$h['keterangan_anggaran'] 	= $anggaran->keterangan;
				$h['kode_cabang']			= $cabang;
				$h['tahun_core'] 			= $thn;
				$h['changed'] 				= '[]';
				insert_data($table,$h);
			endif;
		elseif(strlen(strpos($coa, '_total'))>0):
			$where = [
                'coa' => $coa,
                'tahun_core' => $thn,
                'kode_cabang' => $cabang,
                'kode_anggaran' => $kode_anggaran,
            ];
			update_data($table,$v,$where);
		else:
			update_data($table,$v,'id',$id);
		endif;
	}
}

function filter_money($val){
 	$value = str_replace('.', '', $val);
    $value = str_replace(',', '.', $value);
    if(strlen(strpos($value, '('))>0):
    	$value = str_replace('(', '', $value);
    	$value = str_replace(')', '', $value);
    	$value = '-'.$value;
    endif;
    return (float) $value;
}

function parse_condition($condition){
    $val = '2 == 2 && 13 < 2';
    $condition = "return ".$val.";";
    $test = eval($condition);
    var_dump($test);
}

function arrSumberData(){
	return ['real' => 'Real','renc' => 'Renc'];
}

function bgEdit(){
	// return '#f3f088';
	if(setting('warna_inputan')):
		return setting('warna_inputan');
	else:
		return '#f3f088';
	endif;
}

function get_data_core($arr_coa,$arr_tahun_core,$column,$dt=[]){
	$CI         = get_instance();
	// data core / history
    $data_core 		= [];
    $temp_column 	= $column;
    foreach ($arr_tahun_core as $v) {
        $tbl_history = 'tbl_history_'.$v;
        $tbl_history_status = true;
        if(!$CI->db->table_exists($tbl_history)):
            $tbl_history_status = false;
        endif;
        if(isset($dt['sum_cabang'])):
        	$txt_cabang = '(';
			foreach ($temp_column as $k2 => $v2) {
				if ($tbl_history_status && $CI->db->field_exists('TOT_'.$v2, $tbl_history)):
		            $txt_cabang .= 'TOT_'.$v2;
					if(count($temp_column)>1 && ($k2+1) < count($temp_column)):
						$txt_cabang .= ' + ';
					endif;
		        endif;
			}
			$txt_cabang .= ')';
			if(strlen($txt_cabang)<3):
				$tbl_history_status = false;
			endif;
			$column = $txt_cabang;
	   	else:
	   		if ($tbl_history_status && !$CI->db->field_exists($column, $tbl_history)):
	            $tbl_history_status = false;
	        endif;
        endif;
        if($tbl_history_status):
            $data_core[$v] = get_data($tbl_history.' a',[
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
                    b.glwdes
                    ",
                'join' => "tbl_m_coa b on b.glwnco = a.glwnco and b.kode_anggaran = '".user('kode_anggaran')."'",
                'where_in' => ['a.glwnco' => $arr_coa],
                'group_by' => 'a.glwnco',
            ])->result_array();
        endif;
    }
    return $data_core;
}

function get_data_core_sum($arr_coa,$arr_tahun_core,$column){
	$CI         = get_instance();
	// data core / history
    $data_core = [];
    foreach ($arr_tahun_core as $v) {
        $tbl_history = 'tbl_history_'.$v;
        $tbl_history_status = true;
        if(!$CI->db->table_exists($tbl_history)):
            $tbl_history_status = false;
        endif;
        if ($tbl_history_status && !$CI->db->field_exists($column, $tbl_history)):
            $tbl_history_status = false;
        endif;
        if($tbl_history_status):
            $data_core[$v] = get_data($tbl_history.' a',[
                'select' => "
                    coalesce(
                    	case when b.kali_minus = 1 then
                    		sum(case when bulan = '1' then ".$column." end) * -1
                    	else
                    		sum(case when bulan = '1' then ".$column." end)
                    	end
                	,0) as B_01,
                	coalesce(
                    	case when b.kali_minus = 1 then
                    		sum(case when bulan = '2' then ".$column." end) * -1
                    	else
                    		sum(case when bulan = '2' then ".$column." end)
                    	end
                	,0) as B_02,
                	coalesce(
                    	case when b.kali_minus = 1 then
                    		sum(case when bulan = '3' then ".$column." end) * -1
                    	else
                    		sum(case when bulan = '3' then ".$column." end)
                    	end
                	,0) as B_03,
                	coalesce(
                    	case when b.kali_minus = 1 then
                    		sum(case when bulan = '4' then ".$column." end) * -1
                    	else
                    		sum(case when bulan = '4' then ".$column." end)
                    	end
                	,0) as B_04,
                	coalesce(
                    	case when b.kali_minus = 1 then
                    		sum(case when bulan = '5' then ".$column." end) * -1
                    	else
                    		sum(case when bulan = '5' then ".$column." end)
                    	end
                	,0) as B_05,
                	coalesce(
                    	case when b.kali_minus = 1 then
                    		sum(case when bulan = '6' then ".$column." end) * -1
                    	else
                    		sum(case when bulan = '6' then ".$column." end)
                    	end
                	,0) as B_06,
                	coalesce(
                    	case when b.kali_minus = 1 then
                    		sum(case when bulan = '7' then ".$column." end) * -1
                    	else
                    		sum(case when bulan = '7' then ".$column." end)
                    	end
                	,0) as B_07,
                	coalesce(
                    	case when b.kali_minus = 1 then
                    		sum(case when bulan = '8' then ".$column." end) * -1
                    	else
                    		sum(case when bulan = '8' then ".$column." end)
                    	end
                	,0) as B_08,
                	coalesce(
                    	case when b.kali_minus = 1 then
                    		sum(case when bulan = '9' then ".$column." end) * -1
                    	else
                    		sum(case when bulan = '9' then ".$column." end)
                    	end
                	,0) as B_09,
                	coalesce(
                    	case when b.kali_minus = 1 then
                    		sum(case when bulan = '10' then ".$column." end) * -1
                    	else
                    		sum(case when bulan = '10' then ".$column." end)
                    	end
                	,0) as B_10,
                	coalesce(
                    	case when b.kali_minus = 1 then
                    		sum(case when bulan = '11' then ".$column." end) * -1
                    	else
                    		sum(case when bulan = '11' then ".$column." end)
                    	end
                	,0) as B_11,
                	coalesce(
                    	case when b.kali_minus = 1 then
                    		sum(case when bulan = '12' then ".$column." end) * -1
                    	else
                    		sum(case when bulan = '12' then ".$column." end)
                    	end
                	,0) as B_12,
                    ",
                'join' => "tbl_m_coa b on b.glwnco = a.glwnco and b.kode_anggaran = '".user('kode_anggaran')."'",
                'where_in' => ['a.glwnco' => $arr_coa],
            ])->row_array();
        endif;
    }
    return $data_core;
}

function filter_cabang_admin($access_additional,$cabang,$dt=[]){
	$item = '';

	if(!$access_additional):
		$item .= '<label class="">'.lang('cabang').'  &nbsp</label>';
		$item .= '<select class="select2 custom-select" id="filter_cabang">';
		foreach($cabang as $b){
			$selected = '';
			if($b['kode_cabang'] == user('kode_cabang')) $selected = ' selected';
			$item .= '<option value="'.$b['kode_cabang'].'"'.$selected.'>'.$b['nama_cabang'].'</option>';
		}
		$item .= '</select>';
	else:
		$where = "kode_cabang like 'G%'";
		$is_divisi = '';
		if(isset($dt['kanpus'])):
			$where = "(kode_cabang like 'G%' or parent_id = 0)";
			$is_divisi = ' data-type="divisi"';
		endif;
		$cab_induk = get_data('tbl_m_cabang',[
			'select' 	=> 'id,kode_cabang,nama_cabang',
			'where' 	=> $where." and kode_cabang != 'G001' and is_active = '1' and kode_anggaran = '".user('kode_anggaran')."'",
			'order_by' 	=> 'urutan'
		])->result_array();
		$item .= '<label class="">Cabang Induk  &nbsp</label>';
		$item .= '<select class="select2 custom-select" id="filter_cabang_induk"'.$is_divisi.'>';
		foreach($cab_induk as $b){
			$selected = '';
			if($b['kode_cabang'] == user('kode_cabang')) $selected = ' selected';
			$nama_cabang = str_replace('GAB', '', $b['nama_cabang']);
			$item .= '<option value="'.$b['id'].'"'.$selected.'>'.$nama_cabang.'</option>';
		}
		$item .= '</select>';

		$item .= '<label class="">&nbsp '.lang('cabang').'  &nbsp</label>';
		$item .= '<select class="select2 custom-select" id="filter_cabang">';
		$item .= '</select>&nbsp';

		if(!isset($dt['no-align'])):
			$item .= '<style>';
			$item .= '.content-header{ height: auto !important; }';
			$item .= '.content-header .float-right{ margin-top: 1rem !important; }';
			$item .= '.content-header .header-info{ position: relative !important; }';
			if(isset($dt['rko'])):
				$item .= '.mt-6{ margin-top: 3em;}';
			else:
				$item .= '.mt-6{ margin-top: 4em;}';
			endif;
			$item .= '</style>';
		endif;

	endif;
	return $item;
}

function get_currency($currency){
	$dt_currency = get_data('tbl_m_currency','id',$currency)->row_array();
	$nama  = "Rupiah";
	$nilai = 1;
	if($dt_currency):
		$nama 	= $dt_currency['nama'];
		$nilai	= (float) $dt_currency['nilai'];
	endif;
	return [
		'nama' 	=> $nama,
		'nilai'	=> $nilai,
	];
}

function hitung_rekap_rasio($cabang,$kode,$anggaran){
	$arr_coa 		= [];
	$arr_kode 		= [];
	$status_get 	= false;
	$status_pembagi	= false;
	$status_tambah	= false;
	$status_kurang	= false;
	$s_setahun 		= false;
	$s_no_data 		= false;
	$s_avg 			= false;
	$arr_tambah 	= [];
	$arr_kurang 	= [];
	$arr_bagi 		= [];
	$coa = '';

	if($kode == 'A1'):
		$arr_coa = ['602','5130000']; $status_pembagi = true; $s_setahun = true; $arr_tambah = ['5130000']; $arr_bagi = ['602'];
	elseif($kode == 'A2'):
		$coa = '5130000'; $arr_coa = [$coa];$status_get = true;
	elseif($kode == 'A3'):
		$coa = '602'; $arr_coa = [$coa];$status_get = true;
	elseif($kode == 'A4'):
		$arr_coa = ['4150000','1450000']; $status_pembagi = true; $s_setahun = true; $arr_tambah = ['4150000']; $arr_bagi = ['1450000'];
	elseif($kode == 'A5'):
		$coa = '4150000'; $arr_coa = [$coa];$status_get = true;
	elseif($kode == 'A6'):
		$coa = '1450000'; $arr_coa = [$coa];$status_get = true;
	elseif($kode == 'A7'):
		$s_no_data = true;
	elseif($kode == 'A8'):
		$arr_coa = ['122502','122501']; $status_pembagi = true; $arr_tambah = ['122502']; $arr_bagi = ['122501'];
	elseif($kode == 'A9'):
		$arr_coa = ['122506','122501']; $status_pembagi = true; $arr_tambah = ['122506']; $arr_bagi = ['122501'];
	elseif($kode == 'A10'):
		$arr_kode = ['A15','A16','A17','A22','A23','A24'];
		$arr_coa  = ['122502','122506'];
		$status_pembagi = true; $arr_tambah = $arr_kode; $arr_bagi = $arr_coa;
	elseif($kode == 'A11'):
		$arr_kode = ['A15','A16','A17'];
		$arr_coa  = ['122502'];
		$status_pembagi = true; $arr_tambah = $arr_kode; $arr_bagi = $arr_coa;
	elseif($kode == 'A12'):
		$coa = '122502'; $arr_coa = [$coa];$status_get = true;
	elseif(in_array($kode, ['A13','A14','A15','A16','A17','A20','A21','A22','A23','A24'])):
		$coa = $kode; $arr_kode = [$coa];$status_get = true;
	elseif($kode == 'A18'):
		$arr_kode = ['A22','A23','A24'];
		$arr_coa  = ['122506'];
		$status_pembagi = true; $arr_tambah = $arr_kode; $arr_bagi = $arr_coa;
	elseif($kode == 'A19'):
		$coa = '122506'; $arr_coa = [$coa];$status_get = true;
	elseif($kode == 'A25'):
		$arr_coa = ['602','1450000']; $status_pembagi = true; $arr_tambah = ['1450000']; $arr_bagi = ['602'];
	elseif($kode == 'A26'):
		$arr_coa = ['5100000','5500000','4100000']; $status_pembagi = true; $arr_tambah = ['5100000','5500000']; $arr_bagi = ['5100000','4100000'];
	elseif($kode == 'A27'):
		$arr_coa = ['5100000','5500000']; $status_tambah = true; $arr_tambah = ['5100000','5500000'];
	elseif($kode == 'A28'):
		$arr_coa = ['5100000','4100000']; $status_tambah = true; $arr_tambah = ['5100000','4100000'];
	elseif($kode == 'A29'):
		$arr_coa = ['1000000','59999']; $status_pembagi = true; $s_setahun = true; $s_avg = true; $arr_tambah = ['59999']; $arr_bagi = ['1000000'];
	elseif($kode == 'A30'):
		$coa = '59999'; $arr_coa = [$coa];$status_get = true;
	elseif($kode == 'A31'):
		$coa = '1000000'; $arr_coa = [$coa];$status_get = true;
	elseif($kode == 'A32'):
		$arr_coa = ['2100000','2120011','602']; $status_pembagi = true; $arr_tambah = ['2100000','2120011']; $arr_bagi = ['602'];
	elseif($kode == 'A32_1'):
		$arr_coa = ['2100000','2120011']; $status_tambah = true; $arr_tambah = ['2100000','2120011'];
	elseif($kode == 'A32_2'):
		$arr_coa = ['602']; $status_tambah = true; $arr_tambah = ['602'];
	elseif($kode == 'A33'):
		$arr_coa = ['4100000','5100000','1200000','1220000','1250000','1300000','1400000','1450000']; 
		$status_pembagi = true; $s_setahun = true; $s_avg = true;
		$arr_kurang = ['4100000','5100000']; $arr_bagi = ['1200000','1220000','1250000','1300000','1400000','1450000'];
	elseif($kode == 'A33_1'):
		$arr_coa = ['4100000','5100000']; $status_kurang = true; $arr_kurang = ['4100000','5100000'];
	elseif($kode == 'A33_2'):
		$arr_coa = ['1200000','1220000','1250000','1300000','1400000','1450000']; $status_tambah = true; $arr_tambah = $arr_coa;
	elseif($kode == 'A34'):
		$arr_coa = ['4590000','4100000','4500000']; $status_pembagi = true; $arr_tambah = ['4590000']; $arr_bagi = ['4100000','4500000'];
	elseif($kode == 'A34_1'):
		$coa = '4590000'; $arr_coa = [$coa];$status_get = true;
	elseif($kode == 'A34_2'):
		$arr_coa = ['4100000','4500000']; $status_tambah = true; $arr_tambah = ['4100000','4500000'];
	endif;

	$data = [];
	if(count($arr_coa)>0):
		$dt_budget  = get_data('tbl_budget_nett_neraca',[
            'where' => [
                'kode_anggaran' => $anggaran->kode_anggaran,
                'kode_cabang'   => $cabang,
                'coa'           => $arr_coa
            ],
        ])->result_array();
        $dt_budget_labarugi  = get_data('tbl_budget_nett_labarugi',[
            'where' => [
                'kode_anggaran' => $anggaran->kode_anggaran,
                'kode_cabang'   => $cabang,
                'coa'           => $arr_coa
            ],
        ])->result_array();
        foreach ($arr_coa as $v) {
            $key = array_search($v, array_column($dt_budget, 'coa'));
            $key_labarugi = array_search($v, array_column($dt_budget_labarugi, 'coa'));
            if(strlen($key)>0):
                $data[$v] = $dt_budget[$key];
            elseif(strlen($key_labarugi)>0):
            	$data[$v] = $dt_budget_labarugi[$key_labarugi];
            else:
                $data[$v] = [
                    'B_01' => 0,
                    'B_02' => 0,
                    'B_03' => 0,
                    'B_04' => 0,
                    'B_05' => 0,
                    'B_06' => 0,
                    'B_07' => 0,
                    'B_08' => 0,
                    'B_09' => 0,
                    'B_10' => 0,
                    'B_11' => 0,
                    'B_12' => 0,
                ];
            endif;
        }
	endif;

	if(count($arr_kode)>0):
		$dt_budget_rekaprasio  = get_data('tbl_budget_nett_rekaprasio',[
            'where' => [
                'kode_anggaran' => $anggaran->kode_anggaran,
                'kode_cabang'   => $cabang,
                'kode'          => $arr_kode
            ],
        ])->result_array();
        foreach ($arr_kode as $v) {
            $key = array_search($v, array_column($dt_budget_rekaprasio, 'kode'));
            if(strlen($key)>0):
                $data[$v] = $dt_budget_rekaprasio[$key];
            else:
                $data[$v] = [
                    'B_01' => 0,
                    'B_02' => 0,
                    'B_03' => 0,
                    'B_04' => 0,
                    'B_05' => 0,
                    'B_06' => 0,
                    'B_07' => 0,
                    'B_08' => 0,
                    'B_09' => 0,
                    'B_10' => 0,
                    'B_11' => 0,
                    'B_12' => 0,
                ];
            endif;
        }
	endif;

	$res = [];
	$total = 0;
	for($i=1;$i<=12;$i++){
		$bulan = 'B_'.sprintf("%02d",$i);
		if($status_pembagi):
			$pembagi = 0; foreach ($arr_bagi as $v) {
				$pembagi += $data[$v][$bulan];
			}
			$total += $pembagi;
			if($s_avg) $pembagi = $total / $i; 
			// if(!$pembagi) $pembagi = 1;

			$tambah = 0; foreach ($arr_tambah as $v) {
				$tambah += $data[$v][$bulan];
			}
			foreach ($arr_kurang as $k => $v) {
				if($k == 0):
					$tambah = $data[$v][$bulan];
				else:
					$tambah -= $data[$v][$bulan];
				endif;
			}


			if($s_setahun) $tambah = ($tambah/$i) * 12;

       		if($pembagi):
				$A1 = ( $tambah/ $pembagi) * 100;
			else:
				$A1 = 0;
			endif;
       		$res[$bulan] = custom_format($A1,false,2);
       	elseif($status_tambah):
			$tambah = 0; foreach ($arr_tambah as $v) {
				$tambah += $data[$v][$bulan];
			}

			if($s_setahun) $tambah = ($tambah/$i) * 12;

       		$res[$bulan] = custom_format(view_report($tambah));
       	elseif($status_kurang):
			$tambah = 0; foreach ($arr_kurang as $k => $v) {
				if($k == 0):
					$tambah = $data[$v][$bulan];
				else:
					$tambah -= $data[$v][$bulan];
				endif;
			}

			if($s_setahun) $tambah = ($tambah/$i) * 12;

       		$res[$bulan] = custom_format(view_report($tambah));
       	elseif($status_get):
       		$res[$bulan] = custom_format(view_report($data[$coa][$bulan]));
       	elseif($s_no_data):
       		$res[$bulan] = '';
		endif;
	}
	return $res;
}

function cabang_not_show(){
	return ['G001'];
}

function checkFormulaAkt($where,$data,$bulan){
	$key = multidimensional_search($data, $where);
	$res = ['status' => false];
	if(strlen($key)>0):
		$changed = json_decode($data[$key]['changed']);
		if(in_array($bulan, $changed)):
			$res['status'] 	= true;
			$res['data']	= $data[$key];
		endif;
	endif;
	return $res;
}

function checkFormulaAkt2($where,$data){
	$key = multidimensional_search($data, $where);
	$res = ['status' => false];
	if(strlen($key)>0):
		$res['status'] 	= true;
		$res['data']	= $data[$key];
	endif;
	return $res;
}

function checkSavedFormulaAkt($data,$anggaran){
	foreach ($data as $k => $v) {
		$dt 		= explode('-', $k);
		$coa 		= $dt[0];
		$tahun_core = $dt[1];
		$cabang 	= $dt[2];

		$record = insert_view_report_arr($v);
		
		$ck = get_data('tbl_formula_akt',[
			'select' => 'id',
			'where'	 => "glwnco = '$coa' and kode_anggaran = '$anggaran->kode_anggaran' and tahun_core = '$tahun_core' and kode_cabang = '$cabang'"
		])->row_array();
		if($ck):
			$ID = $ck['id'];
			update_data('tbl_formula_akt',$record,['id' => $ID]);
		else:
			$record['kode_cabang'] 		= $cabang;
			$record['kode_anggaran'] 	= $anggaran->kode_anggaran;
			$record['tahun_core']	 	= $tahun_core;
			$record['glwnco']			= $coa;
			if($tahun_core != $anggaran->tahun_anggaran):
				$record['parent_id'] = $cabang;
			else:
				$record['parent_id'] = "0";
			endif;
			insert_data('tbl_formula_akt',$record);
		endif;
	}
}

function checkFomulaAktSewa($data,$bulan,$tahun){
	$res = 0;
	foreach ($data as $k => $v) {
		if($v['tahun'] == $tahun && $v['bulan'] == $bulan){
			$res += $v['harga'];
		}
	}
	return $res;
}

function searchPersentase($where,$data){
	$key = multidimensional_search($data, $where);
	$res = 0;
	if(strlen($key)>0):
		$dt = $data[$key];
		$val = (float) $dt['persen'];
		$res = $val/100;
	endif;
	return $res;
}

function cabang_divisi($access=""){
	 $segment = $cur_segment = uri_segment(2) ? uri_segment(2) : uri_segment(1);
    if($access) {
        $cur_segment        = $access;
    }
    $dt_access    = get_access($cur_segment);
    $cabang_user  = get_data('tbl_user',[
        'where' => [
            'is_active' => 1,
            'id_group'  => id_group_access($cur_segment),
            ''
        ]
    ])->result();

    $kode_cabang          = [];
    foreach($cabang_user as $c) $kode_cabang[] = $c->kode_cabang;

    $id = user('kode_cabang');
    $cab = get_data('tbl_m_cabang','kode_cabang',$id)->row_array();
    $cab = get_data('tbl_m_cabang',[
        'where' => [
            'kode_cabang'   => $cab['kode_cabang'],
            'kode_anggaran' => user('kode_anggaran')
        ]
    ])->row_array();

    $x = '';
    if(isset($cab['id'])){ 
        for ($i = 1; $i <= 4; $i++) { 
            $field = 'level' . $i ;

            if($cab['id'] == $cab[$field]) {
                $x = $field ; 
            }    
        }    
    }
    $query = [
	    'select'    => 'distinct a.id,a.kode_cabang,a.nama_cabang,a.status_group',
	    'where'     => [
	        'a.is_active' => 1,
	        'a.'.$x => $cab['id'],
	        'a.kode_cabang' => $kode_cabang,
	        'a.kode_cabang != ' => ['G001','001'],
	        'a.kode_anggaran' => user('kode_anggaran')
    	],
    	'order_by' => 'a.urutan'
    ];
    $data['status_group'] 		= $cab['status_group'];
    $data['access_additional']  = $dt_access['access_additional'];
    if($dt_access['access_additional']):
    	unset($query['where']['a.'.$x]);
    	$data['status_group'] 		= 1;
    endif;
    $data['cabang']            	= get_data('tbl_m_cabang a',$query)->result_array();


    if($data['status_group'] == 1):
    	$option_induk = '<label class="">Cabang Induk &nbsp</label>';
    	$option_induk .= '<select class="select2 custom-select" id="filter_cabang_induk" data-type="divisi">';
		foreach($data['cabang'] as $b){
			$selected = '';
			if($b['kode_cabang'] == user('kode_cabang')) $selected = ' selected';
			$nama_cabang 	= $b['nama_cabang'];
			$option_induk 	.= '<option value="'.$b['id'].'"'.$selected.'>'.$nama_cabang.'</option>';
		}
		$option_induk .= '</select>';

		$option_induk .= '<label class="l-cabang">&nbsp '.lang('cabang').'  &nbsp</label>';
		$option_induk .= '<select class="select2 custom-select" id="filter_cabang">';
		$option_induk .= '</select>&nbsp';

		$option_induk .= '<style>';
		$option_induk .= '.content-header{ height: auto !important; }';
		$option_induk .= '.content-header .float-right{ margin-top: 1rem !important; }';
		$option_induk .= '.content-header .header-info{ position: relative !important; }';
		$option_induk .= '.mt-6{ margin-top: 4em;}';
		$option_induk .= '</style>';
		$data['option'] = $option_induk;
    else:
    	$item = '<label>'.lang('cabang').'  &nbsp</label>';
		$item .= '<select class="select2 custom-select" id="filter_cabang">';
		foreach($data['cabang'] as $b){
			$selected = '';
			if($b['kode_cabang'] == user('kode_cabang')) $selected = ' selected';
			$item .= '<option value="'.$b['kode_cabang'].'"'.$selected.'>'.$b['nama_cabang'].'</option>';
		}
		$item .= '</select>';
		$data['option'] = $item;
    endif;

    $data['tahun'] = get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->result();
    return $data;
}

// clone table rate dan prosentase dpk
function clone_rate($kode_anggaran,$table){
	$anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
	$last_anggaran = get_data('tbl_tahun_anggaran',[
		'select' 		=> 'kode_anggaran',
		'where' 		=> "kode_anggaran != '$kode_anggaran' and is_active = '1' ",
		'order_by' 		=> 'id',
		'sort'			=> 'DESC',
	])->row();

	if($last_anggaran):
		$rate = get_data($table,[
			'where' => [
				'kode_anggaran' => $last_anggaran->kode_anggaran,
				'is_active'		=> 1,
			]
		])->result();
		foreach ($rate as $k => $v) {
			unset($v->id);
			$v->kode_anggaran 		= $anggaran->kode_anggaran;
			$v->id_anggaran 		= $anggaran->id;
			$v->keterangan_anggaran = $anggaran->keterangan;
			$v->create_by 			= user('username');
			$v->create_at 			= date("Y-m-d H:i:s");
		}
		if(count($rate)>0):
			insert_batch($table,$rate);
		endif;
	endif;
}

function clone_value_table($table,$last_anggaran,$anggaran,$additional = array()){
	if($last_anggaran):
		$d = get_data($table,[
			'where' => [
				'kode_anggaran' => $last_anggaran->kode_anggaran,
				'is_active'		=> 1,
			]
		])->result_array();
		$data = [];
		foreach ($d as $k => $v) {
			unset($v['id']);
			$v['kode_anggaran'] 		= $anggaran->kode_anggaran;
			foreach ($additional as $k2 => $v2) {
				if(isset($v[$k2])):
					$v[$k2] = $v2;
				endif;
			}
			$data[] = $v;
		}
		if(count($data)>0):
			// render($data,'json');
			insert_batch($table,$data);
		endif;
	endif;
}

function clone_cabang($table,$last_anggaran,$anggaran,$additional = array()){
	if($last_anggaran):
		$d = get_data($table,[
			'where' => [
				'kode_anggaran' => $last_anggaran->kode_anggaran,
				'is_active'		=> 1,
				'parent_id' 	=> $additional['parent_id_lama'],
			]
		])->result_array();
		$data = [];
		foreach ($d as $k => $v) {
			$id_lama = $v['id'];
			unset($v['id']);
			$v['kode_anggaran'] 		= $anggaran->kode_anggaran;
			foreach ($additional as $k2 => $v2) {
				if(isset($v[$k2])):
					$v[$k2] = $v2;
				endif;
			}

			$ID = insert_data($table,$v);
			$v['level'.$additional['level_count']] 	= $ID;
			update_data($table,$v,'id',$ID);
			
			$p1 = $additional;
			$p1['parent_id'] 		= $ID;
			$p1['parent_id_lama'] 	= $id_lama;
			$p1['level'.$additional['level_count']] = $ID;
			$p1['level_count'] 	= $additional['level_count'] + 1;
			clone_cabang($table,$last_anggaran,$anggaran,$p1);
		}
	endif;
}

function clone_table($table,$table_last_anggaran){
	$CI         			= get_instance();
    $status 				= $CI->db->table_exists($table);
    $status_last_anggaran 	= $CI->db->table_exists($table_last_anggaran);
    if(!$status && $status_last_anggaran):
    	$CI->db->query("CREATE TABLE ".$table." AS SELECT * FROM ".$table_last_anggaran);
    elseif($status_last_anggaran):
    	$CI->db->query("DROP TABLE ".$table);
    	$CI->db->query("CREATE TABLE ".$table." AS SELECT * FROM ".$table_last_anggaran);
    endif;
}

function coa_neraca($coa){
	$data = [];
	$arr_neraca = [];
    foreach ($coa as $k => $v) {
        if(!in_array($v->glwnco,$arr_neraca)):
        	array_push($arr_neraca,$v->glwnco);
        endif;
        // center
        if(!$v->level0 && !$v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
            $h = $v;
            $data['coa'][] = $h;
        endif;

        // level 0
        if($v->level0 && !$v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
            $h = $v;
            $data['coa0'][$v->level0][] = $h;
        endif;

        // level 1
        if(!$v->level0 && $v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
            $h = $v;
            $data['coa1'][$v->level1][] = $h;
        endif;

        // level 2
        if(!$v->level0 && !$v->level1 && $v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
            $h = $v;
            $data['coa2'][$v->level2][] = $h;
        endif;

        // level 3
        if(!$v->level0 && !$v->level1 && !$v->level2 && $v->level3 && !$v->level4 && !$v->level5):
            $h = $v;
            $data['coa3'][$v->level3][] = $h;
        endif;

        // level 4
        if(!$v->level0 && !$v->level1 && !$v->level2 && !$v->level3 && $v->level4 && !$v->level5):
            $h = $v;
            $data['coa4'][$v->level4][] = $h;
        endif;

        // level 5
        if(!$v->level0 && !$v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && $v->level5):
            $h = $v;
            $data['coa5'][$v->level5][] = $h;
        endif;
    }
    $data['arr_coa'] = $arr_neraca;
    if(!isset($data['coa'])):
    	$data['coa'] = [];
    endif;

    return $data;
}

function coa_labarugi($coa){
    $data = [];
    $arr_coa = [];
    foreach ($coa as $k => $v) {
    	if(!in_array($v->glwnco,$arr_coa)):
    		array_push($arr_coa,$v->glwnco);
    	endif;
        // center
        if(!$v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
            $h = $v;
            $data['coa'][] = $h;
        endif;

        // level 1
        if($v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
            $h = $v;
            $data['coa0'][$v->level1][] = $h;
        endif;

        // level 2
        if(!$v->level1 && $v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
            $h = $v;
            $data['coa1'][$v->level2][] = $h;
        endif;

        // level 3
        if(!$v->level1 && !$v->level2 && $v->level3 && !$v->level4 && !$v->level5):
            $h = $v;
            $data['coa2'][$v->level3][] = $h;
        endif;

        // level 4
        if(!$v->level1 && !$v->level2 && !$v->level3 && $v->level4 && !$v->level5):
            $h = $v;
            $data['coa3'][$v->level4][] = $h;
        endif;

        // level 5
        if(!$v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && $v->level5):
            $h = $v;
            $data['coa4'][$v->level5][] = $h;
        endif;
    }
    $data['arr_coa'] = $arr_coa;
    if(!isset($data['coa'])):
    	$data['coa'] = [];
    endif;
    return $data;
}

function check_first_formula($kode_anggaran,$kode_cabang,$table,$coa,$page){
	$CI         = get_instance();
	$name = $coa[0];
	$glwnco = $coa[1];
	$query = $table." where kode_anggaran = '$kode_anggaran' and kode_cabang = '$kode_cabang' and $name = '$glwnco'";
	$count = $CI->db->count_all($query);
	if($count<=0):
		create_autorun($kode_anggaran,$kode_cabang,$page);
	endif;
}

function create_autorun($kode_anggaran,$kode_cabang,$page){
	$where = [
		'kode_anggaran' => $kode_anggaran,
		'kode_cabang'	=> $kode_cabang,
		'page'			=> $page,
		'status'		=> 1,
	];
	$ck = get_data('tbl_autorun',['select' => 'id','where' => $where])->result_array();
	if(count($ck)<=0):
		save_data('tbl_autorun',$where,[],true);
	endif;
}
function call_autorun($kode_anggaran,$kode_cabang,$page){
	$where = [
		'kode_anggaran' => $kode_anggaran,
		'kode_cabang'	=> $kode_cabang,
		'page'			=> $page,
		'status'		=> 1,
	];
	$ck = get_data('tbl_autorun',['select' => 'id','where' => $where])->result_array();
	$count = count($ck);
	foreach ($ck as $k => $v) {
		$data['id'] 	= $v['id'];
		$data['status']	= 0;
		save_data('tbl_autorun',$data,[],true);
	}
	return $count;
}

function singkat_angka($n, $presisi=1) {
	if ($n < 900) {
		$format_angka = number_format($n, $presisi);
		$simbol = '';
	} else if ($n < 900000) {
		$format_angka = number_format($n / 1000, $presisi);
		$simbol = 'Rb';
	} else if ($n < 900000000) {
		$format_angka = number_format($n / 1000000, $presisi);
		$simbol = 'Jt';
	} else if ($n < 900000000000) {
		$format_angka = number_format($n / 1000000000, $presisi);
		$simbol = 'M';
	} else {
		$format_angka = number_format($n / 1000000000000, $presisi);
		$simbol = 'T';
	}
 
	if ( $presisi > 0 ) {
		$pisah = '.' . str_repeat( '0', $presisi );
		$format_angka = str_replace( $pisah, '', $format_angka );
	}
	
	return $format_angka .' '. $simbol;
}

function first_checkbox_save($c,$week){
    $bulan = '';
    $tahun = '';
    $arr = [];
    foreach ($week['week'] as $k2 => $v2) {
        $d = $week['detail'][$v2];
        $x = explode("-", $d);
        $key = $x[0];
        if($c['pelaksanaan'] == 'bulanan' && $x[1] != $bulan):
            $bulan = $x[1];
            $tahun = $x[2];
            $arr[$key] = 1;
        elseif($c['pelaksanaan'] == 'triwulan' && in_array($x[1],["01","04","07","10"]) && $x[1] != $bulan):
        	$bulan = $x[1];
            $tahun = $x[2];
            $arr[$key] = 1;
        elseif(!in_array($c['pelaksanaan'],['bulanan','triwulan'])):
        	$arr[$key] = 1;
        endif;
    }
    $c['checkbox'] = json_encode($arr);
    return $c;
}

function check_column_table($kode_anggaran,$table){
	$gab_cab = get_data('tbl_m_cabang',['select' => 'id','where' => [
		'kode_cabang like' 	=> 'G001',
		'is_active' 		=> 1,
		'kode_anggaran'		=> $kode_anggaran
	]])->row();

	$gab_divisi = get_data('tbl_m_cabang',['select' => 'id','where' => [
		'kode_cabang' 	=> '00100',
		'is_active' 		=> 1,
		'kode_anggaran'		=> $kode_anggaran
	]])->row();

	$arrCabang 	= [];
	$ls_cab 	= get_data('tbl_m_cabang',[
		'select' => 'distinct kode_cabang',
		'where' => [
			'level1' 		=> $gab_cab->id,
			'level2 !=' 	=> $gab_divisi->id,
			'level3 !=' 	=> $gab_divisi->id,
			'level4 !=' 	=> $gab_divisi->id,
			'is_active'		=> 1,
			'status_group' 	=> 0
		],
		'sort_by' 	=> 'urutan',
	])->result();
	foreach($ls_cab as $v){
		array_push($arrCabang,$v->kode_cabang);
	}

	$fields = get_field($table,'name');
	$arrColumn = [];
	foreach($arrCabang as $v){
		if(in_array('TOT_'.$v,$fields)):
			array_push($arrColumn,'TOT_'.$v);
		endif;
	}
	return $arrColumn;
}

function checkNumber($number){
    $number = (float) $number;
    if(!is_numeric($number)) $number = 0;
    return $number;
}

function option_pelaksanaan(){
	$data['harian'] 	= ['name' => lang('harian'),'value' => 'harian'];
	$data['mingguan'] 	= ['name' => lang('mingguan'),'value' => 'mingguan'];
	$data['bulanan'] 	= ['name' => lang('bulanan'),'value' => 'bulanan'];
	$data['triwulan'] 	= ['name' => lang('triwulan'),'value' => 'triwulan'];
	return $data;
}

function get_arr_cabang_level($kode_anggaran,$kode_cabang,$arr=[]){
	$data[] = $kode_cabang;
	$cab = get_data('tbl_m_cabang',[
		'select' => 'id,kode_cabang,nama_cabang',
		'where' => [
			'kode_anggaran' => $kode_anggaran,
			'kode_cabang'	=> $kode_cabang,
			'is_active' 	=> 1,
		]
	])->row_array();
	if(isset($cab['id'])):
		$id = $cab['id'];
		$where = '';
		if(isset($arr['last_level'])):
			$where = " and status_group = '0'";
		endif;
		$ls = get_data('tbl_m_cabang',[
			'select' => 'group_concat(kode_cabang) as kode_cabang',
			'where' => "kode_anggaran = '$kode_anggaran' and is_active = '1' and (level1 = '$id' or level2 = '$id' or level3 = '$id' or level4 = '$id')".$where,
			'order_by' => 'urutan'
		])->row_array();
		if(isset($ls['kode_cabang'])):
			$ls = explode(',',$ls['kode_cabang']);
			$data = $ls;
		endif;
	endif;
	return $data;
}

function check_save_cabang($controller,$kode_anggaran,$kode_cabang,$action="access_input"){
	$data_finish['kode_anggaran']   = $kode_anggaran;
    $data_finish['kode_cabang']     = $kode_cabang;
    $a = get_access($controller,$data_finish);
    $status = false;
    if(post('id')):
        $action = 'access_edit';
    endif;
    if($a[$action] && $kode_cabang == user('kode_cabang')):
        $status = true;
    elseif($a[$action] && $a['access_additional']):
        $status = true;
    endif;
    if(!$status or (!$kode_anggaran or !$kode_cabang)):
        render([
            'status'    => 'failed',
            'message'   => lang('izin_ditolak'),
        ],'json');exit();
    endif;
}

function check_access_cabang($controller,$kode_anggaran,$kode_cabang,$access){
	$data = data_cabang($controller);
	$arr_cabang = [];
	foreach($data['cabang'] as $v){
		if(!in_array($v['kode_cabang'],$arr_cabang)):
			array_push($arr_cabang,$v['kode_cabang']);
		endif;
	}
	if((!$kode_anggaran or !$kode_cabang) or (!in_array($kode_cabang,$arr_cabang) && !$access['access_additional'])):
		render([
            'status'    => false,
            'message'   => lang('izin_ditolak'),
        ],'json');exit();
	endif;
}

function check_save_divisi($controller,$kode_anggaran,$kode_cabang,$table="",$action="access_input"){
	$data = cabang_divisi($controller);
	$arr_cabang = [];
	foreach($data['cabang'] as $v){
		if(!in_array($v['kode_cabang'],$arr_cabang)):
			array_push($arr_cabang,$v['kode_cabang']);
		endif;
	}

	// ls child
	$parent = '';
	$parent_id = 'zz99';
	if(isset($data['cabang'][0])):
		$parent_id = $data['cabang'][0]['id'];
	endif;
	if($data['status_group'] == 1):
		$parent = " and (parent_id = '$parent_id')";
	else:
		$parent = " and (parent_id = '$parent_id' or id = '$parent_id')";
	endif;
	$ls_child 	= get_data('tbl_m_cabang',[
		'select' 	=> 'kode_cabang,nama_cabang',
		'where'		=> "kode_anggaran = '".$kode_anggaran."' and is_active = '1'".$parent,
		'order_by'	=> 'kode_cabang',
	])->result_array();

	foreach($ls_child as $v){
		if(!in_array($v['kode_cabang'],$arr_cabang)):
			array_push($arr_cabang,$v['kode_cabang']);
		endif;
	}

	$data_finish['kode_anggaran']   = $kode_anggaran;
    $data_finish['kode_cabang']     = $kode_cabang;
    $a = get_access($controller,$data_finish);
    $status = false;
    if(post('id')):
        $action = 'access_edit';
    endif;
    if($a[$action] && in_array($kode_cabang,$arr_cabang)):
        $status = true;
        if(post('id') && $table):
        	$dt_id = get_data($table,'id',post('id'))->row();
        	if($dt_id && $kode_cabang != $dt_id->kode_cabang):
        		$status = false;
        	endif;
        endif;
    elseif($a[$action] && $a['access_additional']):
        $status = true;
    endif;
    if(!$status or (!$kode_anggaran or !$kode_cabang)):
        render([
            'status'    => 'failed',
            'message'   => lang('izin_ditolak'),
        ],'json');exit();
    endif;
}

function check_access_divisi($controller,$kode_anggaran,$kode_cabang,$access){
	$data = cabang_divisi($controller);
	$arr_cabang = [];
	foreach($data['cabang'] as $v){
		if(!in_array($v['kode_cabang'],$arr_cabang)):
			array_push($arr_cabang,$v['kode_cabang']);
		endif;
	}

	// ls child
	$parent = '';
	$parent_id = 'zz99';
	if(isset($data['cabang'][0])):
		$parent_id = $data['cabang'][0]['id'];
	endif;
	if($data['status_group'] == 1):
		$parent = " and (parent_id = '$parent_id')";
	else:
		$parent = " and (parent_id = '$parent_id' or id = '$parent_id')";
	endif;
	$ls_child 	= get_data('tbl_m_cabang',[
		'select' 	=> 'kode_cabang,nama_cabang',
		'where'		=> "kode_anggaran = '".$kode_anggaran."' and is_active = '1'".$parent,
		'order_by'	=> 'kode_cabang',
	])->result_array();

	foreach($ls_child as $v){
		if(!in_array($v['kode_cabang'],$arr_cabang)):
			array_push($arr_cabang,$v['kode_cabang']);
		endif;
	}

	if((!$kode_anggaran or !$kode_cabang) or (!in_array($kode_cabang,$arr_cabang) && !$access['access_additional'])):
		render([
            'status'    => false,
            'message'   => lang('izin_ditolak'),
        ],'json');exit();
	endif;
}

function validate_phone($txt){
	$iso = '62';
	$phone = preg_replace('/[^0-9]/', '', $txt);
	$status = false;
	if(strlen($phone)>=10) {
		$status = true;
		if(substr($phone, 0,1) == "0"):
			$phone = substr($phone,1);
		elseif(substr($phone, 0,2) == "62"):
			$phone = substr($phone,2);
		endif;
	    $phone  = $iso.$phone;
	}
	return [
		'status' => $status,
		'phone'  => $phone
	];
}

function rkf_bulan($kode_anggaran){
	$bulan = 1;
	$row = get_data('tbl_m_rkf_bulan',[
		'where' => [
			'kode_anggaran' => $kode_anggaran,
			'is_active' 	=> 1
		]
	])->row();
	if($row):
		$bulan = $row->bulan;
	endif;
	return $bulan;
}

// pegawai
function data_pic_option($arr_id){
	$option = '';
	if(count($arr_id)>0):
		$db = get_data('tbl_m_pegawai',[
			'select' 	=> 'id,nama,nip',
			'where'		=> [
				'id' => $arr_id
			],
			'order_by'	=> 'nip',
		])->result();
		foreach ($db as $k => $v) {
			$option .= '<option value="'.$v->id.'">'.remove_spaces($v->nip.' - '.$v->nama).'<option>';
		}
	endif;
	return $option;
}

// status budget nett
function save_status_budget_nett($kode_anggaran,$kode_cabang,$table,$status=1){
	$row = get_data($table,[
		'select' => 'id',
        'where' => [
            'kode_cabang' => $kode_cabang,
            'kode_anggaran' => $kode_anggaran
        ]
    ])->row();
    $data_save = [
    	'id' => '',
    	'kode_anggaran' => $kode_anggaran,
    	'kode_cabang' => $kode_cabang,
    	'status' => $status,
    ];
    if($row):
    	$data_save['id'] = $row->id;
    endif;
    $res = save_data($table,$data_save,[],true);
    return $res;
}