<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mac2 extends BE_Controller {

	var $controller = 'mac2';
    var $arr_coa_kredit = ['122502','122506'];
    var $arr_coa_pend = ['4100000','4195011','4500000','4800000'];
    var $arr_coa_beban = ['5100000','5195011','5500000','5800000'];
	function __construct() {
		parent::__construct();
	}

	function index() {
        $kode_anggaran = user('kode_anggaran');
		$tahun = get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->result_array();
		$a     = get_access($this->controller);
        $data                   = data_cabang('mac2');
		$data['controller']     = $this->controller;
		$data['tahun']     		= $tahun;
		$data['bulan']     		= $this->month_option();
		$data['access_additional']  = $a['access_additional'];

        $data['arr_coa_kredit'] = $this->arr_coa_kredit;
        $data['arr_coa_pend']   = $this->arr_coa_pend;
        $data['arr_coa_beban']  = $this->arr_coa_beban;
        $data['arr_coa_ecl']    = $this->arr_coa_ecl($kode_anggaran);
        $data['arr_coa_other']  = $this->arr_coa_other($kode_anggaran);
        $data['arr_coa_pend_o'] = $this->arr_coa_pend_beban($kode_anggaran,1);
        $data['arr_coa_beban_o'] = $this->arr_coa_pend_beban($kode_anggaran,2);
		render($data);
	}

    private function arr_coa_ecl($kode_anggaran){
        $data = get_data('tbl_m_coa',[
            'select' => 'group_concat(distinct glwnco) as glwnco',
            'where'  => "kode_anggaran = '$kode_anggaran' and is_active = 1 and (LEVEL0 = '1552000' OR LEVEL1 = '1552000' OR LEVEL2 = '1552000' OR LEVEL4 = '1552000' OR LEVEL5 = '1552000' OR glwnco = '1552000') and glwnco not in ('1552013','1552014')",
            'order_by' => 'urutan'
        ])->row();
        $coa = [];
        if($data and strlen($data->glwnco)>0):
            $coa = explode(',', $data->glwnco);
        endif;
        return $coa;
    }

    private function arr_coa_other($kode_anggaran){
        $m_mac = get_data('tbl_m_mac',[
            'select' => 'group_concat(distinct coa) as glwnco',
            'where'  => [
                'is_active' => 1,
                'kode_anggaran' => $kode_anggaran,
            ]
        ])->row();
        $coa = [];
        if($m_mac && strlen($m_mac->glwnco)>0):
            $coa = explode(',', $m_mac->glwnco);
        endif;
        return $coa;
    }

    private function arr_coa_pend_beban($kode_anggaran,$tipe){
        $dt = get_data('tbl_m_coa_pendapatan_beban',[
            'select' => 'group_concat(distinct coa) as coa',
            'where'  => [
                'kode_anggaran' => $kode_anggaran,
                'is_active'     => 1,
                'tipe'          => $tipe
            ]
        ])->row();
        $coa = [];
        if($dt && strlen($dt->coa)>0):
            $coa = explode(',', $dt->coa);
        endif;
        return $coa;
    }

	private function month_option(){
    	$data = array();
    	for ($i=1; $i <=12 ; $i++) { 
    		$month = month_lang($i);
    		array_push($data, array('value' => $i,'name' => $month));
    	}
    	return $data;
    }

    function data($kode_anggaran,$kode_cabang,$bulan){
    	$anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
    	if(!$anggaran){
    		render(['status' => false,'message' => 'anggaran not found'],'json');exit();
    	}
        
        // pengecekan akses cabang
        $a  = get_access($this->controller);
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$a);

    	$tahun  		= $anggaran->tahun_anggaran;
    	$tahun_before  	= ($anggaran->tahun_anggaran-1);
    	$select = '';
    	$bulan_txt = 'B_' . sprintf("%02d", $bulan);
    	for ($i=1; $i <= 12 ; $i++) { 
    		$field = 'B_' . sprintf("%02d", $i);
    		$select .= "ifnull(b.".$field.",0) as ".$field.",";
    	}
        $cabang_area    = $this->cabang_area($kode_anggaran,$kode_cabang);

    	#dpk
        // giro + tabungan + simpanan berjakngka = dpk
        $coa_dpk 	= array('2100000','2120011','2130000');
        $core_dpk 	= get_data_core($coa_dpk,[$tahun,$tahun_before],'TOT_'.$kode_cabang);
        $dt_dpk 	= get_data('tbl_m_coa a',[
            'select' => "a.glwnco,a.glwdes,".$select,
            'join'   => [
                "tbl_budget_nett_neraca as b on 
                    b.coa = a.glwnco and b.kode_cabang = '$kode_cabang' and b.kode_anggaran = '$kode_anggaran' type left"
            ],
            'where'  => [
                'a.glwnco' => $coa_dpk,
                'a.kode_anggaran' => $kode_anggaran
            ],
            'order_by' => 'a.urutan'
        ])->result_array();
        $arrDPK = [
        	'dpk_renc' 	=> 0,
        	'dpk_real'	=> 0,
        	'dpk_real_before'=> 0,
        	'dpk_penc' 	=> 0,
        	'dpk_pert' 	=> 0,
        	'class'		=> [],
            'chart'     => [],
            'data'      => ['renc' => [],'real' => [],'des' => 0],
            'cabang'    => [],
        ];
        $borderColor = ['#3e95cd','#3cba9f','#ffa500'];
        $backgroundColor = ['#7bb6dd','#71d1bd','#ffc04d'];
        foreach ($dt_dpk as $k => $v) {
            $real = 0;
            $renc = checkNumber($v[$bulan_txt]);
            $real_before = 0;

        	$arrDPK['dpk_renc'] += checkNumber($v[$bulan_txt]);
        	$key_core = '';
            if(isset($core_dpk[$tahun])):
        		$key_core = multidimensional_search($core_dpk[$tahun],['glwnco' =>$v['glwnco']]);
        		if(strlen($key_core)>0):
                    $minus = $core_dpk[$tahun][$key_core]['kali_minus'];
        			$real  = checkNumber($core_dpk[$tahun][$key_core][$bulan_txt]);
                    $real  = kali_minus($real,$minus);
                    $arrDPK['dpk_real'] += $real;
                    $arrDPK[$v['glwnco'].'_real'] = $real;
        		endif;
        	endif;
            $key_core_bef = '';
        	if(isset($core_dpk[$tahun_before])):
        		$key_core_bef = multidimensional_search($core_dpk[$tahun_before],['glwnco' =>$v['glwnco']]);
        		if(strlen($key_core_bef)>0):
                    $real_before = checkNumber($core_dpk[$tahun_before][$key_core_bef][$bulan_txt]);
                    $minus       = $core_dpk[$tahun_before][$key_core_bef]['kali_minus'];
                    $real_before = kali_minus($real_before,$minus);
        			$arrDPK['dpk_real_before'] += $real_before;
        		endif;
        	endif;
        	$arrDPK['class']['.d-'.$v['glwnco'].' .div-title span'] = strtoupper(remove_spaces($v['glwdes']));

            $penc = 0;
            if($renc):
                $penc = (view_report($real)/view_report($renc))*100;
                $penc = round($penc, 2);
            endif;
            $arrDPK['class']['.d-'.$v['glwnco'].' .renc span'] = custom_format($penc,false,2).'%';

            $pert = 0;
            if($real_before):
                $pembagi = view_report($real_before);
                $pert    = ((view_report($real)-$pembagi)/$pembagi)*100;
                $pert    = round($pert, 2);
            endif;
            $img = base_url('assets\images\arrow-up.png');
            if($pert<0):
                $img = base_url('assets\images\arrow-down.png');
            endif;
            $arrDPK['class']['.d-'.$v['glwnco'].' .persent-text'] = '<img src="'.$img.'" height="25"/><span>'.custom_format($pert,false,2).'%</span>';

            $sisa = 0;
            if($penc<=100 && $penc>=0) $sisa = round((100 - $penc),2);
            if($penc<0):
                $penc = 0;  $sisa = 100;
            endif;
            $title = remove_spaces($v['glwdes']);
            $arrDPK['chart']['c_'.$v['glwnco'].'_penc'] = [
                'labels'    => [$title.' PENC',$title.' PENC'],
                'datasets'  => [
                    array(
                        'data' => [$penc,$sisa],
                        'backgroundColor' => ["rgb(255, 205, 86)"],
                        'borderColor' => ["rgb(255, 205, 86)"],
                        'borderWidth' => 1,
                        'hoverOffset' => 4
                    )
                ]
            ];

            // chart line
            $c_renc = [
                'data'  => [],
                'label' => 'RENC',
                'fill'  => false,
                'borderColor' => $borderColor[0],
                'backgroundColor' => $backgroundColor[0],
                'borderWidth' => 1
            ];
            $c_real = [
                'data'  => [],
                'label' => 'REAL',
                'fill'  => false,
                'borderColor' => $borderColor[1],
                'backgroundColor' => $backgroundColor[1],
                'borderWidth' => 1
            ];
            $c_des = [
                'data'  => [],
                'label' => 'DES '.$tahun,
                'fill'  => false,
                'borderColor' => $borderColor[2],
                'backgroundColor' => $backgroundColor[2],
                'borderWidth' => 1
            ];

            for ($i=1; $i <= $bulan ; $i++) { 
                $field  = 'B_' . sprintf("%02d", $i);
                $field2 = 'B_' . sprintf("%02d", 12);
                $c_renc['data'][] = round(view_report(checkNumber($v[$field])));
                $c_des['data'][] = round(view_report(checkNumber($v[$field2])));
                
                $val = 0;
                if(strlen($key_core)>0):
                    $val = checkNumber($core_dpk[$tahun][$key_core][$field]);
                    $minus = $core_dpk[$tahun][$key_core]['kali_minus'];
                    $val  = kali_minus($val,$minus);
                endif;
                $c_real['data'][] = round(view_report($val));

                if(isset($arrDPK['data']['renc'][$field])):
                    $arrDPK['data']['renc'][$field] += checkNumber($v[$field]);
                else:
                    $arrDPK['data']['renc'][$field] = checkNumber($v[$field]);
                endif; 
                if(isset($arrDPK['data']['real'][$field])):
                    $arrDPK['data']['real'][$field] += $val;
                else:
                    $arrDPK['data']['real'][$field] = $val;
                endif;
                if($i == 1):
                    $arrDPK['data']['des'] += checkNumber($v[$field2]);
                endif;
            }

            $labels = [];
            for ($i=1; $i <=$bulan ; $i++) { 
                $labels[] = month_lang($i,true);
            }

            $speedData = [
                'labels'    => $labels,
                'datasets'  => [$c_renc,$c_real,$c_des],
            ];
            $arrDPK['chart']['c_'.$v['glwnco'].'_line'] = $speedData;

            // chart area
            $area = $this->data_cabang_area($anggaran,$cabang_area,$v,$bulan,$arrDPK['cabang']);
            $arrDPK['cabang'] = $area['cabang'];
            $arrDPK['chart']['c_'.$v['glwnco'].'_scatter'] = $area['chart'];

        }

        $dpk_half = [
            'labels'    => [],
            'datasets'  => [
                array(
                    'data' => [],
                    'backgroundColor' => [],
                    'borderColor' => [],
                    'borderWidth' => 1,
                    'hoverOffset' => 4
                )
            ],
        ];
        $dpk_half_detail = '';
        foreach ($dt_dpk as $k => $v) {
            $real = 0;
            $dpk_real = $arrDPK['dpk_real'];
            if(isset($arrDPK[$v['glwnco'].'_real'])):
                $real = $arrDPK[$v['glwnco'].'_real'];
            endif;

            $val = 0;
            if($dpk_real):
                $val = (view_report($real)/view_report($dpk_real))*100;
                $val = round($val,2);
            endif;
            $sisa = 0;
            $val2 = $val;
            if($val<=100 && $val>=0) $sisa = round((100 - $val),2);
            if($val<0):
                $val = 0;  $sisa = 100;
            endif;

            $title = remove_spaces($v['glwdes']);
            $arrDPK['chart']['c_'.$v['glwnco'].'_half'] = [
                'labels'    => [$title.' REAL','DPK REAL'],
                'datasets'  => [
                    array(
                        'data' => [$val,$sisa],
                        'backgroundColor' => ['rgba(231, 76, 60, 1)'],
                        'borderColor' => ["rgb(231, 76, 60, 1)"],
                        'borderWidth' => 1,
                        'hoverOffset' => 4
                    )
                ]
            ];
            $arrDPK['class']['.d-'.$v['glwnco'].' .half span'] = custom_format($val2,false,2).'%';

            $bg = 'rgba(216,223,229,1)';
            $border = 'rgba(216,223,229,1)';
            if(isset($backgroundColor[$k])):
                $bg = $backgroundColor[$k];
                $border = $borderColor[$k];
            endif;

            $dpk_half['labels'][] = $title;
            $dpk_half['datasets'][0]['data'][] = $val;
            $dpk_half['datasets'][0]['backgroundColor'][] = $bg;
            $dpk_half['datasets'][0]['borderColor'][] = $border;

            $dpk_half_detail .= '<span>'.custom_format($val2,false,2).'% '.$title.'</span><br>';
        }
        $arrDPK['chart']['c_602_half'] = $dpk_half;
        $arrDPK['class']['.d-602 .det-coa'] = $dpk_half_detail;

        // pencapaian
        if($arrDPK['dpk_renc']):
        	$arrDPK['dpk_penc'] = (view_report($arrDPK['dpk_real'])/view_report($arrDPK['dpk_renc']))*100;
        	$arrDPK['dpk_penc'] = round($arrDPK['dpk_penc'], 2);
        endif;
        // pertumbuhan
        if($arrDPK['dpk_real_before']):
        	$pembagi = view_report($arrDPK['dpk_real_before']);
        	$pert 	 = ((view_report($arrDPK['dpk_real'])-$pembagi)/$pembagi)*100;
            $arrDPK['dpk_pert'] = round($pert, 2);
        endif;
        $img = base_url('assets\images\arrow-up.png');
        if($arrDPK['dpk_pert']<0):
            $img = base_url('assets\images\arrow-down.png');
        endif;
        $arrDPK['class']['.c-dpk .persent-text'] = '<img src="'.$img.'" height="25"/><span>'.custom_format($arrDPK['dpk_pert'],false,2).'%</span>';

        $title = 'DPK';
        $sisa = 0;
        $val2 = $arrDPK['dpk_penc'];
        if($arrDPK['dpk_penc']<=100 && $arrDPK['dpk_penc']>=0) $sisa = round((100 - $arrDPK['dpk_penc']),2);
        $val = $arrDPK['dpk_penc'];
        if($val<0):
            $val = 0;  $sisa = 100;
        endif;
        $arrDPK['chart']['c_602_penc'] = [
            'labels'    => [$title.' PENC',$title.' PENC'],
            'datasets'  => [
                array(
                    'data' => [$val,$sisa],
                    'backgroundColor' => ["rgb(255, 205, 86)"],
                    'borderColor' => ["rgb(255, 205, 86)"],
                    'borderWidth' => 1,
                    'hoverOffset' => 4
                )
            ]
        ];
        $arrDPK['class']['.d-602 .div-title span'] = strtoupper(lang('dana_pihak_ketiga'));
        $arrDPK['class']['.d-602 .renc span'] = custom_format($arrDPK['dpk_penc'],false,2).'%';
        $arrDPK['class']['.d-602 .persent-text'] = '<img src="'.$img.'" height="25"/><span>'.custom_format($arrDPK['dpk_pert'],false,2).'%</span>';

        $c_renc = [
            'data'  => [],
            'label' => 'RENC',
            'fill'  => false,
            'borderColor' => $borderColor[0],
            'backgroundColor' => $backgroundColor[0],
            'borderWidth' => 1
        ];
        $c_real = [
            'data'  => [],
            'label' => 'REAL',
            'fill'  => false,
            'borderColor' => $borderColor[1],
            'backgroundColor' => $backgroundColor[1],
            'borderWidth' => 1
        ];
        $c_des = [
            'data'  => [],
            'label' => 'DES '.$tahun,
            'fill'  => false,
            'borderColor' => $borderColor[2],
            'backgroundColor' => $backgroundColor[2],
            'borderWidth' => 1
        ];
        for ($i=1; $i <= $bulan ; $i++) { 
            $field  = 'B_' . sprintf("%02d", $i);
            $field2 = 'B_' . sprintf("%02d", 12);
            $c_renc['data'][] = round(view_report(checkNumber($arrDPK['data']['renc'][$field])));
            $c_des['data'][] = round(view_report(checkNumber($arrDPK['data']['des'])));
            $c_real['data'][] = round(view_report(checkNumber($arrDPK['data']['real'][$field])));
        }
        $labels = [];
        for ($i=1; $i <=$bulan ; $i++) { 
            $labels[] = month_lang($i,true);
        }

        $speedData = [
            'labels'    => $labels,
            'datasets'  => [$c_renc,$c_real,$c_des],
        ];
        $arrDPK['chart']['c_602_line'] = $speedData;
        $arrDPK['chart']['c_602_scatter'] = ['datasets' => $arrDPK['cabang']['datasets']];
        #end dpk

        unset($arrDPK['data']);
        unset($arrDPK['cabang']);
    	render([
    		'status'	=> true,
    		'dpk' 		=> $arrDPK,
    	],'json');
    }

    function data_kredit($kode_anggaran,$kode_cabang,$bulan){
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
        if(!$anggaran){
            render(['status' => false,'message' => 'anggaran not found'],'json');exit();
        }
        // pengecekan akses cabang
        $a  = get_access($this->controller);
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$a);

        $tahun          = $anggaran->tahun_anggaran;
        $tahun_before   = ($anggaran->tahun_anggaran-1);
        $select = '';
        $bulan_txt = 'B_' . sprintf("%02d", $bulan);
        for ($i=1; $i <= 12 ; $i++) { 
            $field = 'B_' . sprintf("%02d", $i);
            $select .= "ifnull(b.".$field.",0) as ".$field.",";
        }
        $cabang_area    = $this->cabang_area($kode_anggaran,$kode_cabang);

        $core_kredit   = get_data_core($this->arr_coa_kredit,[$tahun,$tahun_before],'TOT_'.$kode_cabang);
        $dt_kredit     = get_data('tbl_m_coa a',[
            'select' => "a.glwnco,a.glwdes,".$select,
            'join'   => [
                "tbl_budget_nett_neraca as b on 
                    b.coa = a.glwnco and b.kode_cabang = '$kode_cabang' and b.kode_anggaran = '$kode_anggaran' type left"
            ],
            'where'  => [
                'a.glwnco' => $this->arr_coa_kredit,
                'a.kode_anggaran' => $kode_anggaran
            ],
            'order_by' => 'a.urutan'
        ])->result_array();

        $arrKredit = [
            'kredit_renc'  => 0,
            'kredit_real'  => 0,
            'kredit_real_before'=> 0,
            'kredit_penc'  => 0,
            'kredit_pert'  => 0,
            'class'     => [],
            'chart'     => [],
            'data'      => ['renc' => [],'real' => [],'des' => 0],
            'cabang'    => [],
        ];
        $borderColor = ['#3e95cd','#3cba9f','#ffa500'];
        $backgroundColor = ['#7bb6dd','#71d1bd','#ffc04d'];
        foreach ($dt_kredit as $k => $v) {
            $real = 0;
            $renc = checkNumber($v[$bulan_txt]);
            $real_before = 0;

            $arrKredit['kredit_renc'] += checkNumber($v[$bulan_txt]);
            $key_core = '';
            if(isset($core_kredit[$tahun])):
                $key_core = multidimensional_search($core_kredit[$tahun],['glwnco' =>$v['glwnco']]);
                if(strlen($key_core)>0):
                    $real = checkNumber($core_kredit[$tahun][$key_core][$bulan_txt]);
                    $minus = $core_kredit[$tahun][$key_core]['kali_minus'];
                    $real  = kali_minus($real,$minus);
                    $arrKredit['kredit_real'] += $real;
                    $arrKredit[$v['glwnco'].'_real'] = $real;
                endif;
            endif;
            $key_core_bef = '';
            if(isset($core_kredit[$tahun_before])):
                $key_core_bef = multidimensional_search($core_kredit[$tahun_before],['glwnco' =>$v['glwnco']]);
                if(strlen($key_core_bef)>0):
                    $real_before = checkNumber($core_kredit[$tahun_before][$key_core_bef][$bulan_txt]);
                    $minus = $core_kredit[$tahun_before][$key_core_bef]['kali_minus'];
                    $real_before  = kali_minus($real_before,$minus);
                    $arrKredit['kredit_real_before'] += $real_before;
                endif;
            endif;
            $arrKredit['class']['.d-'.$v['glwnco'].' .div-title span'] = strtoupper(remove_spaces($v['glwdes']));

            $penc = 0;
            if($renc):
                $penc = (view_report($real)/view_report($renc))*100;
                $penc = round($penc, 2);
            endif;
            $arrKredit['class']['.d-'.$v['glwnco'].' .renc span'] = custom_format($penc,false,2).'%';

            $pert = 0;
            if($real_before):
                $pembagi = view_report($real_before);
                $pert    = ((view_report($real)-$pembagi)/$pembagi)*100;
                $pert    = round($pert, 2);
            endif;
            $img = base_url('assets\images\arrow-up.png');
            if($pert<0):
                $img = base_url('assets\images\arrow-down.png');
            endif;
            $arrKredit['class']['.d-'.$v['glwnco'].' .persent-text'] = '<img src="'.$img.'" height="25"/><span>'.custom_format($pert,false,2).'%</span>';

            $sisa = 0;
            if($penc<=100 && $penc>=0) $sisa = round((100 - $penc),2);
            if($penc<0):
                $penc = 0;  $sisa = 100;
            endif;
            $title = remove_spaces($v['glwdes']);
            $arrKredit['chart']['c_'.$v['glwnco'].'_penc'] = [
                'labels'    => [$title.' PENC',$title.' PENC'],
                'datasets'  => [
                    array(
                        'data' => [$penc,$sisa],
                        'backgroundColor' => ["rgb(255, 205, 86)"],
                        'borderColor' => ["rgb(255, 205, 86)"],
                        'borderWidth' => 1,
                        'hoverOffset' => 4
                    )
                ]
            ];

            // chart line
            $c_renc = [
                'data'  => [],
                'label' => 'RENC',
                'fill'  => false,
                'borderColor' => $borderColor[0],
                'backgroundColor' => $backgroundColor[0],
                'borderWidth' => 1
            ];
            $c_real = [
                'data'  => [],
                'label' => 'REAL',
                'fill'  => false,
                'borderColor' => $borderColor[1],
                'backgroundColor' => $backgroundColor[1],
                'borderWidth' => 1
            ];
            $c_des = [
                'data'  => [],
                'label' => 'DES '.$tahun,
                'fill'  => false,
                'borderColor' => $borderColor[2],
                'backgroundColor' => $backgroundColor[2],
                'borderWidth' => 1
            ];

            for ($i=1; $i <= $bulan ; $i++) { 
                $field  = 'B_' . sprintf("%02d", $i);
                $field2 = 'B_' . sprintf("%02d", 12);
                $c_renc['data'][] = round(view_report(checkNumber($v[$field])));
                $c_des['data'][] = round(view_report(checkNumber($v[$field2])));
                
                $val = 0;
                if(strlen($key_core)>0):
                    $val = checkNumber($core_kredit[$tahun][$key_core][$field]);
                    $minus = $core_kredit[$tahun][$key_core]['kali_minus'];
                    $val  = kali_minus($val,$minus);
                endif;
                $c_real['data'][] = round(view_report($val));

                if(isset($arrKredit['data']['renc'][$field])):
                    $arrKredit['data']['renc'][$field] += checkNumber($v[$field]);
                else:
                    $arrKredit['data']['renc'][$field] = checkNumber($v[$field]);
                endif; 
                if(isset($arrKredit['data']['real'][$field])):
                    $arrKredit['data']['real'][$field] += $val;
                else:
                    $arrKredit['data']['real'][$field] = $val;
                endif;
                if($i == 1):
                    $arrKredit['data']['des'] += checkNumber($v[$field2]);
                endif;
            }

            $labels = [];
            for ($i=1; $i <=$bulan ; $i++) { 
                $labels[] = month_lang($i,true);
            }

            $speedData = [
                'labels'    => $labels,
                'datasets'  => [$c_renc,$c_real,$c_des],
            ];
            $arrKredit['chart']['c_'.$v['glwnco'].'_line'] = $speedData;

            // chart area
            $area = $this->data_cabang_area($anggaran,$cabang_area,$v,$bulan,$arrKredit['cabang']);
            $arrKredit['cabang'] = $area['cabang'];
            $arrKredit['chart']['c_'.$v['glwnco'].'_scatter'] = $area['chart'];
            $arrKredit['chart']['c_'.$v['glwnco'].'_half'] = $area['chart2'];
        }

        foreach ($dt_kredit as $k => $v) {
            $real = 0;
            $kredit_real = $arrKredit['kredit_real'];
            if(isset($arrKredit[$v['glwnco'].'_real'])):
                $real = $arrKredit[$v['glwnco'].'_real'];
            endif;

            $val = 0;
            if($kredit_real):
                $val = (view_report($real)/view_report($kredit_real))*100;
                $val = round($val,2);
            endif;
            $sisa = 0;
            $val2 = $val;
            if($val<=100 && $val>=0) $sisa = round((100 - $val),2);
            if($val<0):
                $val = 0;  $sisa = 100;
            endif;
        }

        // pencapaian
        if($arrKredit['kredit_renc']):
            $arrKredit['kredit_penc'] = (view_report($arrKredit['kredit_real'])/view_report($arrKredit['kredit_renc']))*100;
            $arrKredit['kredit_penc'] = round($arrKredit['kredit_penc'], 2);
        endif;
        // pertumbuhan
        if($arrKredit['kredit_real_before']):
            $pembagi = view_report($arrKredit['kredit_real_before']);
            $pert    = ((view_report($arrKredit['kredit_real'])-$pembagi)/$pembagi)*100;
            $arrKredit['kredit_pert'] = round($pert, 2);
        endif;
        $img = base_url('assets\images\arrow-up.png');
        if($arrKredit['kredit_pert']<0):
            $img = base_url('assets\images\arrow-down.png');
        endif;
        $arrKredit['class']['.c-kredit .persent-text'] = '<img src="'.$img.'" height="25"/><span>'.custom_format($arrKredit['kredit_pert'],false,2).'%</span>';

        $title = 'KREDIT';
        $sisa = 0;
        $val2 = $arrKredit['kredit_penc'];
        if($arrKredit['kredit_penc']<=100 && $arrKredit['kredit_penc']>=0) $sisa = round((100 - $arrKredit['kredit_penc']),2);
        $val = $arrKredit['kredit_penc'];
        if($val<0):
            $val = 0;  $sisa = 100;
        endif;
        $arrKredit['chart']['c_603_penc'] = [
            'labels'    => [$title.' PENC',$title.' PENC'],
            'datasets'  => [
                array(
                    'data' => [$val,$sisa],
                    'backgroundColor' => ["rgb(255, 205, 86)"],
                    'borderColor' => ["rgb(255, 205, 86)"],
                    'borderWidth' => 1,
                    'hoverOffset' => 4
                )
            ]
        ];
        $arrKredit['class']['.d-603 .div-title span'] = strtoupper(lang('kredit'));
        $arrKredit['class']['.d-603 .renc span'] = custom_format($arrKredit['kredit_penc'],false,2).'%';
        $arrKredit['class']['.d-603 .persent-text'] = '<img src="'.$img.'" height="25"/><span>'.custom_format($arrKredit['kredit_pert'],false,2).'%</span>';

        $c_renc = [
            'data'  => [],
            'label' => 'RENC',
            'fill'  => false,
            'borderColor' => $borderColor[0],
            'backgroundColor' => $backgroundColor[0],
            'borderWidth' => 1
        ];
        $c_real = [
            'data'  => [],
            'label' => 'REAL',
            'fill'  => false,
            'borderColor' => $borderColor[1],
            'backgroundColor' => $backgroundColor[1],
            'borderWidth' => 1
        ];
        $c_des = [
            'data'  => [],
            'label' => 'DES '.$tahun,
            'fill'  => false,
            'borderColor' => $borderColor[2],
            'backgroundColor' => $backgroundColor[2],
            'borderWidth' => 1
        ];
        for ($i=1; $i <= $bulan ; $i++) { 
            $field  = 'B_' . sprintf("%02d", $i);
            $field2 = 'B_' . sprintf("%02d", 12);
            $c_renc['data'][] = round(view_report(checkNumber($arrKredit['data']['renc'][$field])));
            $c_des['data'][] = round(view_report(checkNumber($arrKredit['data']['des'])));
            $c_real['data'][] = round(view_report(checkNumber($arrKredit['data']['real'][$field])));
        }
        $labels = [];
        for ($i=1; $i <=$bulan ; $i++) { 
            $labels[] = month_lang($i,true);
        }

        $speedData = [
            'labels'    => $labels,
            'datasets'  => [$c_renc,$c_real,$c_des],
        ];
        $arrKredit['chart']['c_603_line'] = $speedData;
        $arrKredit['chart']['c_603_scatter'] = ['datasets' => $arrKredit['cabang']['datasets']];
        $arrKredit['chart']['c_603_half'] = ['labels' => [], 'datasets' => $arrKredit['cabang']['datasets2']];

        unset($arrKredit['data']);
        unset($arrKredit['cabang']);
        render([
            'status'    => true,
            'kredit'    => $arrKredit,
        ],'json');
    }

    function data_ecl($kode_anggaran,$kode_cabang,$bulan){
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
        if(!$anggaran){
            render(['status' => false,'message' => 'anggaran not found'],'json');exit();
        }
        // pengecekan akses cabang
        $a  = get_access($this->controller);
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$a);

        $tahun          = $anggaran->tahun_anggaran;
        $tahun_before   = ($anggaran->tahun_anggaran-1);
        $select = '';
        $bulan_txt = 'B_' . sprintf("%02d", $bulan);
        for ($i=1; $i <= 12 ; $i++) { 
            $field = 'B_' . sprintf("%02d", $i);
            $select .= "ifnull(b.".$field.",0) as ".$field.",";
        }
        $cabang_area    = $this->cabang_area($kode_anggaran,$kode_cabang);
        $coa_ecl        = $this->arr_coa_ecl($kode_anggaran);

        $core           = get_data_core($coa_ecl,[$tahun,$tahun_before],'TOT_'.$kode_cabang);
        $dt_ecl         = get_data('tbl_m_coa a',[
            'select' => "a.glwnco,a.glwdes,".$select,
            'join'   => [
                "tbl_budget_nett_neraca as b on 
                    b.coa = a.glwnco and b.kode_cabang = '$kode_cabang' and b.kode_anggaran = '$kode_anggaran' type left"
            ],
            'where'  => [
                'a.glwnco' => $coa_ecl,
                'a.kode_anggaran' => $kode_anggaran
            ],
            'order_by' => 'a.urutan'
        ])->result_array();

        $arrEcl = [];
        foreach ($dt_ecl as $v) {
            $title = remove_spaces($v['glwdes']);
            if($v['glwnco'] == '1552000'):
                $title = lang('total_ecl_kredit');
            endif;
            
            $real = 0;
            $renc = checkNumber($v[$bulan_txt]);
            $real_before = 0;

            $key_core = '';
            if(isset($core[$tahun])):
                $key_core = multidimensional_search($core[$tahun],['glwnco' =>$v['glwnco']]);
                if(strlen($key_core)>0):
                    $real = checkNumber($core[$tahun][$key_core][$bulan_txt]);
                    $minus = $core[$tahun][$key_core]['kali_minus'];
                    $real  = kali_minus($real,$minus);
                endif;
            endif;
            $key_core_bef = '';
            if(isset($core[$tahun_before])):
                $key_core_bef = multidimensional_search($core[$tahun_before],['glwnco' =>$v['glwnco']]);
                if(strlen($key_core_bef)>0):
                    $real_before = checkNumber($core[$tahun_before][$key_core_bef][$bulan_txt]);
                    $minus = $core[$tahun_before][$key_core_bef]['kali_minus'];
                    $real_before  = kali_minus($real_before,$minus);
                endif;
            endif;

            $arrEcl['class']['.d-'.$v['glwnco'].' .div-title span'] = strtoupper($title);
            $arrEcl['class']['.d-'.$v['glwnco'].' .renc .dashboard-main-text'] = custom_format(view_report($renc));
            $arrEcl['class']['.d-'.$v['glwnco'].' .real .dashboard-main-text'] = custom_format(view_report($real));

            $penc = 0;
            if($renc):
                $penc = (view_report($real)/view_report($renc))*100;
                $penc = round($penc, 2);
            endif;
            $arrEcl['class']['.d-'.$v['glwnco'].' .penc .dashboard-main-text'] = custom_format($penc,false,2).'%';

            $pert = 0;
            if($real_before):
                $pembagi = view_report($real_before);
                $pert    = ((view_report($real)-$pembagi)/$pembagi)*100;
                $pert    = round($pert, 2);
            endif;
            $img = base_url('assets\images\arrow-up.png');
            if($pert<0):
                $img = base_url('assets\images\arrow-down.png');
            endif;
            $arrEcl['class']['.d-'.$v['glwnco'].' .pert .dashboard-main-text'] = '<img src="'.$img.'" height="25"/><span>'.custom_format($pert,false,2).'%</span>';
        }

        render([
            'status' => true,
            'ecl'    => $arrEcl
        ],'json');
    }

    function data_laba($kode_anggaran,$kode_cabang,$bulan){
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
        if(!$anggaran){
            render(['status' => false,'message' => 'anggaran not found'],'json');exit();
        }
        // pengecekan akses cabang
        $a  = get_access($this->controller);
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$a);

        $tahun          = $anggaran->tahun_anggaran;
        $tahun_before   = ($anggaran->tahun_anggaran-1);
        $select = '';
        $bulan_txt = 'B_' . sprintf("%02d", $bulan);
        for ($i=1; $i <= 12 ; $i++) { 
            $field = 'B_' . sprintf("%02d", $i);
            $select .= "ifnull(b.".$field.",0) as ".$field.",";
        }
        $cabang_area    = $this->cabang_area($kode_anggaran,$kode_cabang);
        
        $coa_laba       = ['59999'];
        $core           = get_data_core($coa_laba,[$tahun,$tahun_before],'TOT_'.$kode_cabang);
        $dt_coa         = get_data('tbl_m_coa a',[
            'select' => "a.glwnco,a.glwdes,".$select,
            'join'   => [
                "tbl_budget_nett_labarugi as b on 
                    b.coa = a.glwnco and b.kode_cabang = '$kode_cabang' and b.kode_anggaran = '$kode_anggaran' type left"
            ],
            'where'  => [
                'a.glwnco' => $coa_laba,
                'a.kode_anggaran' => $kode_anggaran
            ],
            'order_by' => 'a.urutan'
        ])->result_array();

        $arrLaba = [
            'laba_renc'  => 0,
            'laba_real'  => 0,
            'laba_real_before'=> 0,
            'laba_penc'  => 0,
            'laba_pert'  => 0,
            'class'     => [],
            'chart'     => [],
            'data'      => ['renc' => [],'real' => [],'des' => 0],
            'cabang'    => [],
        ];
        $borderColor = ['#3e95cd','#3cba9f','#ffa500'];
        $backgroundColor = ['#7bb6dd','#71d1bd','#ffc04d'];
        foreach ($dt_coa as $k => $v) {
            $real = 0;
            $renc = checkNumber($v[$bulan_txt]);
            $real_before = 0;

            $arrLaba['laba_renc'] += checkNumber($v[$bulan_txt]);
            $key_core = '';
            if(isset($core[$tahun])):
                $key_core = multidimensional_search($core[$tahun],['glwnco' =>$v['glwnco']]);
                if(strlen($key_core)>0):
                    $real = checkNumber($core[$tahun][$key_core][$bulan_txt]);
                    $minus = $core[$tahun][$key_core]['kali_minus'];
                    $real  = kali_minus($real,$minus);
                    $arrLaba['laba_real'] += $real;
                    $arrLaba[$v['glwnco'].'_real'] = $real;
                endif;
            endif;
            $key_core_bef = '';
            if(isset($core[$tahun_before])):
                $key_core_bef = multidimensional_search($core[$tahun_before],['glwnco' =>$v['glwnco']]);
                if(strlen($key_core_bef)>0):
                    $real_before = checkNumber($core[$tahun_before][$key_core_bef][$bulan_txt]);
                    $minus = $core[$tahun_before][$key_core_bef]['kali_minus'];
                    $real_before  = kali_minus($real_before,$minus);
                    $arrLaba['laba_real_before'] += $real_before;
                endif;
            endif;
            $arrLaba['class']['.d-'.$v['glwnco'].' .div-title span'] = strtoupper(remove_spaces($v['glwdes']));

            $penc = 0;
            if($renc):
                $penc = (view_report($real)/view_report($renc))*100;
                $penc = round($penc, 2);
            endif;
            $arrLaba['class']['.d-'.$v['glwnco'].' .renc span'] = custom_format($penc,false,2).'%';
            $arrLaba['laba_penc'] = round($penc, 2);

            $pert = 0;
            if($real_before):
                $pembagi = view_report($real_before);
                $pert    = ((view_report($real)-$pembagi)/$pembagi)*100;
                $pert    = round($pert, 2);
            endif;
            $img = base_url('assets\images\arrow-up.png');
            if($pert<0):
                $img = base_url('assets\images\arrow-down.png');
            endif;
            $arrLaba['class']['.d-'.$v['glwnco'].' .persent-text'] = '<img src="'.$img.'" height="25"/><span>'.custom_format($pert,false,2).'%</span>';
            $arrLaba['laba_pert'] = round($pert, 2);
            $arrLaba['class']['.c-laba .persent-text'] = '<img src="'.$img.'" height="25"/><span>'.custom_format($pert,false,2).'%</span>';

            $sisa = 0;
            if($penc<=100 && $penc>=0) $sisa = round((100 - $penc),2);
            if($penc<0):
                $penc = 0;  $sisa = 100;
            endif;
            $title = remove_spaces($v['glwdes']);
            $arrLaba['chart']['c_'.$v['glwnco'].'_penc'] = [
                'labels'    => [$title.' PENC',$title.' PENC'],
                'datasets'  => [
                    array(
                        'data' => [$penc,$sisa],
                        'backgroundColor' => ["rgb(255, 205, 86)"],
                        'borderColor' => ["rgb(255, 205, 86)"],
                        'borderWidth' => 1,
                        'hoverOffset' => 4
                    )
                ]
            ];

            // chart line
            $c_renc = [
                'data'  => [],
                'label' => 'RENC',
                'fill'  => false,
                'borderColor' => $borderColor[0],
                'backgroundColor' => $backgroundColor[0],
                'borderWidth' => 1
            ];
            $c_real = [
                'data'  => [],
                'label' => 'REAL',
                'fill'  => false,
                'borderColor' => $borderColor[1],
                'backgroundColor' => $backgroundColor[1],
                'borderWidth' => 1
            ];
            $c_des = [
                'data'  => [],
                'label' => 'DES '.$tahun,
                'fill'  => false,
                'borderColor' => $borderColor[2],
                'backgroundColor' => $backgroundColor[2],
                'borderWidth' => 1
            ];

            for ($i=1; $i <= $bulan ; $i++) { 
                $field  = 'B_' . sprintf("%02d", $i);
                $field2 = 'B_' . sprintf("%02d", 12);
                $c_renc['data'][] = round(view_report(checkNumber($v[$field])));
                $c_des['data'][] = round(view_report(checkNumber($v[$field2])));
                
                $val = 0;
                if(strlen($key_core)>0):
                    $val = checkNumber($core[$tahun][$key_core][$field]);
                    $minus = $core[$tahun][$key_core]['kali_minus'];
                    $val  = kali_minus($val,$minus);
                endif;
                $c_real['data'][] = round(view_report($val));

                if(isset($arrLaba['data']['renc'][$field])):
                    $arrLaba['data']['renc'][$field] += checkNumber($v[$field]);
                else:
                    $arrLaba['data']['renc'][$field] = checkNumber($v[$field]);
                endif; 
                if(isset($arrLaba['data']['real'][$field])):
                    $arrLaba['data']['real'][$field] += $val;
                else:
                    $arrLaba['data']['real'][$field] = $val;
                endif;
                if($i == 1):
                    $arrLaba['data']['des'] += checkNumber($v[$field2]);
                endif;
            }

            $labels = [];
            for ($i=1; $i <=$bulan ; $i++) { 
                $labels[] = month_lang($i,true);
            }

            $speedData = [
                'labels'    => $labels,
                'datasets'  => [$c_renc,$c_real,$c_des],
            ];
            $arrLaba['chart']['c_'.$v['glwnco'].'_line'] = $speedData;

            // chart area
            $area = $this->data_cabang_area($anggaran,$cabang_area,$v,$bulan,$arrLaba['cabang']);
            $arrLaba['cabang'] = $area['cabang'];
            $arrLaba['chart']['c_'.$v['glwnco'].'_scatter'] = $area['chart'];
        }

        unset($arrLaba['data']);
        unset($arrLaba['cabang']);
        render([
            'status'    => true,
            'laba'    => $arrLaba,
        ],'json');

    }

    function data_pend_beban($kode_anggaran,$kode_cabang,$bulan){
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
        if(!$anggaran){
            render(['status' => false,'message' => 'anggaran not found'],'json');exit();
        }
        // pengecekan akses cabang
        $a  = get_access($this->controller);
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$a);

        $tahun          = $anggaran->tahun_anggaran;
        $tahun_before   = ($anggaran->tahun_anggaran-1);
        $select = '';
        $bulan_txt = 'B_' . sprintf("%02d", $bulan);
        for ($i=1; $i <= 12 ; $i++) { 
            $field = 'B_' . sprintf("%02d", $i);
            $select .= "ifnull(b.".$field.",0) as ".$field.",";
        }
        $cabang_area    = $this->cabang_area($kode_anggaran,$kode_cabang);
        
        $coa_pend       = $this->arr_coa_pend;
        $coa_beban      = $this->arr_coa_beban;
        $arr_coa        = array_merge($coa_pend,$coa_beban);
        $core           = get_data_core($arr_coa,[$tahun,$tahun_before],'TOT_'.$kode_cabang);
        $dt_coa         = get_data('tbl_m_coa a',[
            'select' => "a.glwnco,a.glwdes,".$select,
            'join'   => [
                "tbl_budget_nett_labarugi as b on 
                    b.coa = a.glwnco and b.kode_cabang = '$kode_cabang' and b.kode_anggaran = '$kode_anggaran' type left"
            ],
            'where'  => [
                'a.glwnco' => $arr_coa,
                'a.kode_anggaran' => $kode_anggaran
            ],
            'order_by' => 'a.urutan'
        ])->result_array();

        $chart_half = [
            'labels'    => [],
            'datasets'  => [
                array(
                    'data' => [],
                    'backgroundColor' => [],
                    'borderColor' => [],
                    'borderWidth' => 1,
                    'hoverOffset' => 4
                )
            ],
        ];
        $arr = [
            'pend_renc'  => 0,
            'pend_real'  => 0,
            'pend_real_before'=> 0,
            'beban_renc'  => 0,
            'beban_real'  => 0,
            'beban_real_before'=> 0,
            'class' => [],
            'chart' => [
                'c_pend_half' => $chart_half,
                'c_beban_half' => $chart_half,
            ]
        ];
        $borderColor = ['#3e95cd','#3cba9f','#ffa500'];
        $backgroundColor = ['#7bb6dd','#71d1bd','#ffc04d'];
        $no_pend = 0;
        $no_beban = 0;
        foreach($dt_coa as $k => $v){
            $real = 0;
            $renc = checkNumber($v[$bulan_txt]);
            $real_before = 0;
            $key_core = '';
            if(isset($core[$tahun])):
                $key_core = multidimensional_search($core[$tahun],['glwnco' =>$v['glwnco']]);
                if(strlen($key_core)>0):
                    $real = checkNumber($core[$tahun][$key_core][$bulan_txt]);
                    $minus = $core[$tahun][$key_core]['kali_minus'];
                    $real  = kali_minus($real,$minus);
                endif;
            endif;

            $key_core_bef = '';
            if(isset($core[$tahun_before])):
                $key_core_bef = multidimensional_search($core[$tahun_before],['glwnco' =>$v['glwnco']]);
                if(strlen($key_core_bef)>0):
                    $real_before = checkNumber($core[$tahun_before][$key_core_bef][$bulan_txt]);
                    $minus = $core[$tahun_before][$key_core_bef]['kali_minus'];
                    $real_before  = kali_minus($real_before,$minus);
                endif;
            endif;

            $penc = 0;
            if($renc):
                $penc = (view_report($real)/view_report($renc))*100;
                $penc = round($penc, 2);
            endif;

            $pert = 0;
            if($real_before):
                $pembagi = view_report($real_before);
                $pert    = ((view_report($real)-$pembagi)/$pembagi)*100;
                $pert    = round($pert, 2);
            endif;

            $item = '<tr>';
            $item .= '<td class="text-right">'.custom_format(view_report($renc)).'</td>';
            $item .= '<td class="text-right">'.custom_format(view_report($real)).'</td>';
            $item .= '<td class="text-right">'.custom_format($penc,false,2).'%</td>';
            $item .= '<td class="text-right">'.custom_format($pert,false,2).'%</td>';
            $item .= '</tr>';
            $arr['class']['.d-'.$v['glwnco'].' .div-title span'] = strtoupper(remove_spaces($v['glwdes']));
            $arr['class']['.d-'.$v['glwnco'].' table tbody'] = $item;

            $bg = 'rgba(216,223,229,1)';
            $border = 'rgba(216,223,229,1)';
            
            if(in_array($v['glwnco'],$coa_pend)):
                $arr['pend_renc'] += $renc;
                $arr['pend_real'] += $real;
                $arr['pend_real_before'] += $real_before;

                if(isset($backgroundColor[$no_pend])):
                    $bg = $backgroundColor[$no_pend];
                    $border = $borderColor[$no_pend];
                endif;

                $arr['chart']['c_pend_half']['labels'][] = remove_spaces($v['glwdes']);
                $arr['chart']['c_pend_half']['datasets'][0]['data'][] = $penc;
                $arr['chart']['c_pend_half']['datasets'][0]['backgroundColor'][] = $bg;
                $arr['chart']['c_pend_half']['datasets'][0]['borderColor'][] = $border;
                $no_pend++;
            elseif(in_array($v['glwnco'],$coa_beban)):
                $arr['beban_renc'] += $renc;
                $arr['beban_real'] += $real;
                $arr['beban_real_before'] += $real_before;

                if(isset($backgroundColor[$no_beban])):
                    $bg = $backgroundColor[$no_beban];
                    $border = $borderColor[$no_beban];
                endif;

                $arr['chart']['c_beban_half']['labels'][] = remove_spaces($v['glwdes']);
                $arr['chart']['c_beban_half']['datasets'][0]['data'][] = $penc;
                $arr['chart']['c_beban_half']['datasets'][0]['backgroundColor'][] = $bg;
                $arr['chart']['c_beban_half']['datasets'][0]['borderColor'][] = $border;
                $no_beban++;
            endif;

        }

        foreach(['pend','beban'] as $v){
            $renc = $arr[$v.'_renc'];
            $real = $arr[$v.'_real'];
            $real_before = $arr[$v.'_real_before'];

            $penc = 0;
            if($renc):
                $penc = (view_report($real)/view_report($renc))*100;
                $penc = round($penc, 2);
            endif;

            $pert = 0;
            if($real_before):
                $pembagi = view_report($real_before);
                $pert    = ((view_report($real)-$pembagi)/$pembagi)*100;
                $pert    = round($pert, 2);
            endif;

            $item = '<tr>';
            $item .= '<td class="text-right">'.custom_format(view_report($renc)).'</td>';
            $item .= '<td class="text-right">'.custom_format(view_report($real)).'</td>';
            $item .= '<td class="text-right">'.custom_format($penc,false,2).'%</td>';
            $item .= '<td class="text-right">'.custom_format($pert,false,2).'%</td>';
            $item .= '</tr>';

            $arr['class']['.tot_'.$v.' table tbody'] = $item;
        }

        render([
            'status'  => true,
            'data'    => $arr,
        ],'json');
    }

    function data_other($kode_anggaran,$kode_cabang,$bulan){
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
        if(!$anggaran){
            render(['status' => false,'message' => 'anggaran not found'],'json');exit();
        }
        // pengecekan akses cabang
        $a  = get_access($this->controller);
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$a);

        $tahun          = $anggaran->tahun_anggaran;
        $tahun_before   = ($anggaran->tahun_anggaran-1);
        $select = '';
        $bulan_txt = 'B_' . sprintf("%02d", $bulan);
        for ($i=1; $i <= 12 ; $i++) { 
            $field = 'B_' . sprintf("%02d", $i);
            $select .= "ifnull(b.".$field.",0) as ".$field.",";
        }
        $cabang_area    = $this->cabang_area($kode_anggaran,$kode_cabang);
        
        $arr_coa        = $this->arr_coa_other($kode_anggaran);
        $core           = get_data_core($arr_coa,[$tahun,$tahun_before],'TOT_'.$kode_cabang);
        $dt_coa         = get_data('tbl_m_coa a',[
            'select' => "a.glwnco,a.glwdes,".$select,
            'join'   => [
                "tbl_budget_nett_labarugi as b on 
                    b.coa = a.glwnco and b.kode_cabang = '$kode_cabang' and b.kode_anggaran = '$kode_anggaran' type left"
            ],
            'where'  => [
                'a.glwnco' => $arr_coa,
                'a.kode_anggaran' => $kode_anggaran
            ],
            'order_by' => 'a.urutan'
        ])->result_array();
        
        $arr = [
            'class' => [],
            'chart' => []
        ];
        $borderColor = ['#3e95cd','#3cba9f','#ffa500'];
        $backgroundColor = ['#7bb6dd','#71d1bd','#ffc04d'];
        foreach($dt_coa as $k => $v){
            $glwnco = $v['glwnco'];
            $title  = $glwnco.' - '.remove_spaces($v['glwdes']);

            $real = 0;
            $renc = checkNumber($v[$bulan_txt]);
            $real_before = 0;
            $key_core = '';
            if(isset($core[$tahun])):
                $key_core = multidimensional_search($core[$tahun],['glwnco' =>$v['glwnco']]);
                if(strlen($key_core)>0):
                    $real = checkNumber($core[$tahun][$key_core][$bulan_txt]);
                    $minus = $core[$tahun][$key_core]['kali_minus'];
                    $real  = kali_minus($real,$minus);
                endif;
            endif;

            $key_core_bef = '';
            if(isset($core[$tahun_before])):
                $key_core_bef = multidimensional_search($core[$tahun_before],['glwnco' =>$v['glwnco']]);
                if(strlen($key_core_bef)>0):
                    $real_before = checkNumber($core[$tahun_before][$key_core_bef][$bulan_txt]);
                    $minus = $core[$tahun_before][$key_core_bef]['kali_minus'];
                    $real_before  = kali_minus($real_before,$minus);
                endif;
            endif;

            $penc = 0;
            if($renc):
                $penc = (view_report($real)/view_report($renc))*100;
                $penc = round($penc, 2);
            endif;

            $deviasi = $renc - $real;
            $arr['class']['.v-'.$glwnco.' .card-header'] = $title;
            $arr['class']['.v-'.$glwnco.' #penc'] = custom_format($penc,false,2).'%';
            $arr['class']['.v-'.$glwnco.' #hemat'] = custom_format(view_report($deviasi));

            $chart = [
                'labels'    => [],
                'datasets'  => [
                    [
                        'label' => 'renc',
                        'data'  => [view_report($renc)],
                        'borderColor' => $borderColor[0],
                        'backgroundColor' => $backgroundColor[0],
                    ],[
                        'label' => 'Real',
                        'data'  => [view_report($real)],
                        'borderColor' => $borderColor[1],
                        'backgroundColor' => $backgroundColor[1],
                    ]
                ],
            ];
            $arr['chart']['chart_bar_'.$glwnco] = $chart;
        }

        render([
            'status' => true,
            'data'   => $arr
        ],'json');
    }

    function data_asset($kode_anggaran,$kode_cabang,$bulan){
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
        if(!$anggaran){
            render(['status' => false,'message' => 'anggaran not found'],'json');exit();
        }
        // pengecekan akses cabang
        $a  = get_access($this->controller);
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$a);

        $tahun          = $anggaran->tahun_anggaran;
        $tahun_before   = ($anggaran->tahun_anggaran-1);
        $select = '';
        $bulan_txt = 'B_' . sprintf("%02d", $bulan);
        for ($i=1; $i <= 12 ; $i++) { 
            $field = 'B_' . sprintf("%02d", $i);
            $select .= "ifnull(b.".$field.",0) as ".$field.",";
        }
        $cabang_area    = $this->cabang_area($kode_anggaran,$kode_cabang);
        
        $arr_coa        = ['2000000'];
        $core           = get_data_core($arr_coa,[$tahun,$tahun_before],'TOT_'.$kode_cabang);
        $dt_coa         = get_data('tbl_m_coa a',[
            'select' => "a.glwnco,a.glwdes,".$select,
            'join'   => [
                "tbl_budget_nett_neraca as b on 
                    b.coa = a.glwnco and b.kode_cabang = '$kode_cabang' and b.kode_anggaran = '$kode_anggaran' type left"
            ],
            'where'  => [
                'a.glwnco' => $arr_coa,
                'a.kode_anggaran' => $kode_anggaran
            ],
            'order_by' => 'a.urutan'
        ])->result_array();

        $arr = [
            'aset_renc'  => 0,
            'aset_real'  => 0,
            'aset_real_before'=> 0,
            'aset_penc'  => 0,
            'aset_pert'  => 0,
            'class'     => [],
        ];
        foreach ($dt_coa as $k => $v) {
            $real = 0;
            $renc = checkNumber($v[$bulan_txt]);
            $real_before = 0;

            $arr['aset_renc'] += checkNumber($v[$bulan_txt]);
            $key_core = '';
            if(isset($core[$tahun])):
                $key_core = multidimensional_search($core[$tahun],['glwnco' =>$v['glwnco']]);
                if(strlen($key_core)>0):
                    $real = checkNumber($core[$tahun][$key_core][$bulan_txt]);
                    $minus = $core[$tahun][$key_core]['kali_minus'];
                    $real  = kali_minus($real,$minus);
                    $arr['aset_real'] += $real;
                endif;
            endif;
            $key_core_bef = '';
            if(isset($core[$tahun_before])):
                $key_core_bef = multidimensional_search($core[$tahun_before],['glwnco' =>$v['glwnco']]);
                if(strlen($key_core_bef)>0):
                    $real_before = checkNumber($core[$tahun_before][$key_core_bef][$bulan_txt]);
                    $minus = $core[$tahun_before][$key_core_bef]['kali_minus'];
                    $real_before  = kali_minus($real_before,$minus);
                    $arr['aset_real_before'] += $real_before;
                endif;
            endif;

            $penc = 0;
            if($renc):
                $penc = (view_report($real)/view_report($renc))*100;
                $penc = round($penc, 2);
            endif;
            $arr['aset_penc'] = round($penc, 2);

            $pert = 0;
            if($real_before):
                $pembagi = view_report($real_before);
                $pert    = ((view_report($real)-$pembagi)/$pembagi)*100;
                $pert    = round($pert, 2);
            endif;
            $img = base_url('assets\images\arrow-up.png');
            if($pert<0):
                $img = base_url('assets\images\arrow-down.png');
            endif;
            $arr['class']['.c-aset .persent-text'] = '<img src="'.$img.'" height="25"/><span>'.custom_format($pert,false,2).'%</span>';
        }
        render([
            'status'    => true,
            'data'    => $arr,
        ],'json');
    }

    function data_pend_beban_o($kode_anggaran,$kode_cabang,$bulan){
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
        if(!$anggaran){
            render(['status' => false,'message' => 'anggaran not found'],'json');exit();
        }
        // pengecekan akses cabang
        $a  = get_access($this->controller);
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$a);

        $tahun          = $anggaran->tahun_anggaran;
        $tahun_before   = ($anggaran->tahun_anggaran-1);
        $select = '';
        $bulan_txt = 'B_' . sprintf("%02d", $bulan);
        for ($i=1; $i <= 12 ; $i++) { 
            $field = 'B_' . sprintf("%02d", $i);
            $select .= "ifnull(b.".$field.",0) as ".$field.",";
        }
        $cabang_area    = $this->cabang_area($kode_anggaran,$kode_cabang);
        
        $coa_pend       = $this->arr_coa_pend_beban($kode_anggaran,1);
        $coa_beban      = $this->arr_coa_pend_beban($kode_anggaran,2);
        $arr_coa        = array_merge($coa_pend,$coa_beban);
        
        $dt_coa = [];
        if(count($arr_coa)>0):
            $core           = get_data_core($arr_coa,[$tahun,$tahun_before],'TOT_'.$kode_cabang);
            $dt_coa         = get_data('tbl_m_coa a',[
                'select' => "a.glwnco,a.glwdes,".$select,
                'join'   => [
                    "tbl_budget_nett_labarugi as b on 
                        b.coa = a.glwnco and b.kode_cabang = '$kode_cabang' and b.kode_anggaran = '$kode_anggaran' type left"
                ],
                'where'  => [
                    'a.glwnco' => $arr_coa,
                    'a.kode_anggaran' => $kode_anggaran
                ],
                'order_by' => 'a.urutan'
            ])->result_array();
        endif;

        $arr = [
            'class' => [],
        ];
        foreach($dt_coa as $k => $v){
            $real = 0;
            $renc = checkNumber($v[$bulan_txt]);
            $real_before = 0;
            $key_core = '';
            if(isset($core[$tahun])):
                $key_core = multidimensional_search($core[$tahun],['glwnco' =>$v['glwnco']]);
                if(strlen($key_core)>0):
                    $real = checkNumber($core[$tahun][$key_core][$bulan_txt]);
                    $minus = $core[$tahun][$key_core]['kali_minus'];
                    $real  = kali_minus($real,$minus);
                endif;
            endif;

            $key_core_bef = '';
            if(isset($core[$tahun_before])):
                $key_core_bef = multidimensional_search($core[$tahun_before],['glwnco' =>$v['glwnco']]);
                if(strlen($key_core_bef)>0):
                    $real_before = checkNumber($core[$tahun_before][$key_core_bef][$bulan_txt]);
                    $minus = $core[$tahun_before][$key_core_bef]['kali_minus'];
                    $real_before  = kali_minus($real_before,$minus);
                endif;
            endif;

            $penc = 0;
            if($renc):
                $penc = (view_report($real)/view_report($renc))*100;
                $penc = round($penc, 2);
            endif;

            $pert = 0;
            if($real_before):
                $pembagi = view_report($real_before);
                $pert    = ((view_report($real)-$pembagi)/$pembagi)*100;
                $pert    = round($pert, 2);
            endif;

            $item = '<tr>';
            $item .= '<td class="text-right">'.custom_format(view_report($renc)).'</td>';
            $item .= '<td class="text-right">'.custom_format(view_report($real)).'</td>';
            $item .= '<td class="text-right">'.custom_format($penc,false,2).'%</td>';
            $item .= '<td class="text-right">'.custom_format($pert,false,2).'%</td>';
            $item .= '</tr>';
            $arr['class']['.d-'.$v['glwnco'].' .div-title span'] = strtoupper(remove_spaces($v['glwdes']));
            $arr['class']['.d-'.$v['glwnco'].' table tbody'] = $item;
        }

        render([
            'status'  => true,
            'data'    => $arr,
        ],'json');
    }

    private function cabang_area($kode_anggaran,$kode_cabang){
        $data = get_data('tbl_m_cabang a',[
            'select' => 'distinct b.kode_cabang,b.nama_cabang',
            'join' => [
                'tbl_m_cabang b on a.parent_id = b.parent_id'
            ],
            'where' => [
                'a.kode_anggaran'   => $kode_anggaran,
                'a.kode_cabang'     => $kode_cabang,
                'b.is_active'       => 1
            ],
            'order_by' => 'b.urutan'
        ])->result();
        return $data;
    }

    private function data_cabang_area($anggaran,$cabang,$coa,$bulan,$arr){
        $tahun          = $anggaran->tahun_anggaran;
        $tahun_before   = ($anggaran->tahun_anggaran-1);
        $bulan_txt      = 'B_' . sprintf("%02d", $bulan);
        $bulan_12       = 'B_' . sprintf("%02d", 12);
        $data = [
            'datasets' => []
        ];
        $data2 = [
            'labels'   => [""],
            'datasets' => []
        ];
        $arr['datasets']  = [];
        $arr['datasets2'] = [];
        
        $borderColor = ['#3e95cd','#3cba9f','#ffa500'];
        $backgroundColor = ['#7bb6dd','#71d1bd','#ffc04d'];
        
        foreach($cabang as $k => $v){
            $nett = get_data('tbl_budget_nett_neraca',[
                'where' => [
                    'kode_anggaran' => $anggaran->kode_anggaran,
                    'kode_cabang'   => $v->kode_cabang,
                    'coa'           => $coa['glwnco']
                ]
            ])->row();
            if(!$nett):
                $nett = get_data('tbl_budget_nett_labarugi',[
                'where' => [
                    'kode_anggaran' => $anggaran->kode_anggaran,
                    'kode_cabang'   => $v->kode_cabang,
                    'coa'           => $coa['glwnco']
                ]
            ])->row();
            endif;

            $core = get_data_core([$coa['glwnco']],[$tahun,$tahun_before],'TOT_'.$v->kode_cabang);

            $renc = 0;
            if($nett):
                $renc = checkNumber($nett->{$bulan_txt});
            endif;

            $key_core = '';
            $real = 0;
            if(isset($core[$tahun])):
                $key_core = multidimensional_search($core[$tahun],['glwnco' => $coa['glwnco']]);
                if(strlen($key_core)>0):
                    $real  = $core[$tahun][$key_core][$bulan_txt];
                    $minus = $core[$tahun][$key_core]['kali_minus'];
                    $real  = checkNumber($real);
                    $real  = kali_minus($real,$minus);
                endif;
            endif;

            $key_core_bef = '';
            $real_before = 0;
            $real_before_12 = 0;
            if(isset($core[$tahun_before])):
                $key_core_bef = multidimensional_search($core[$tahun_before],['glwnco' => $coa['glwnco']]);
                if(strlen($key_core_bef)>0):
                    $real_before  = $core[$tahun_before][$key_core_bef][$bulan_txt];
                    $minus        = $core[$tahun_before][$key_core_bef]['kali_minus'];
                    $real_before  = checkNumber($real_before);
                    $real_before  = kali_minus($real_before,$minus);

                    $real_before_12 = $core[$tahun_before][$key_core_bef][$bulan_12];
                    $real_before_12  = kali_minus(checkNumber($real_before_12),$minus);
                endif;
            endif;

            $penc = 0;
            if($renc):
                $penc = (view_report($real)/view_report($renc))*100;
                $penc = round($penc, 2);
            endif;

            $pert = 0;
            if($real_before):
                $pembagi = view_report($real_before);
                $pert    = ((view_report($real)-$pembagi)/$pembagi)*100;
                $pert    = round($pert, 2);
            endif;

            $h = [
                'label' => $v->kode_cabang,
                'data'  => [
                    ['x' => $penc, 'y' => $pert],
                ],
            ];
            $h2 = [
                'label' => $v->kode_cabang,
                'data'  =>[round(view_report($real) - view_report($real_before_12))],
            ];
            if(isset($borderColor[$k])):
                $h['borderColor'] = $borderColor[$k];
                $h['backgroundColor'] = $backgroundColor[$k];

                $h2['borderColor'] = [$borderColor[$k]];
                $h2['backgroundColor'] = [$backgroundColor[$k]];
            endif;
            $data['datasets'][] = $h;
            $data2['datasets'][] = $h2;

            if(isset($arr[$v->kode_cabang.'_renc'])):
                $arr[$v->kode_cabang.'_renc'] += $renc;
            else:
                $arr[$v->kode_cabang.'_renc'] = $renc;
            endif;

            if(isset($arr[$v->kode_cabang.'_real'])):
                $arr[$v->kode_cabang.'_real'] += $real;
            else:
                $arr[$v->kode_cabang.'_real'] = $real;
            endif;

            if(isset($arr[$v->kode_cabang.'_real_before'])):
                $arr[$v->kode_cabang.'_real_before'] += $real_before;
            else:
                $arr[$v->kode_cabang.'_real_before'] = $real_before;
            endif;

            if(isset($arr[$v->kode_cabang.'_real_before_12'])):
                $arr[$v->kode_cabang.'_real_before_12'] += $real_before_12;
            else:
                $arr[$v->kode_cabang.'_real_before_12'] = $real_before_12;
            endif;

            // TOTAL DPK
            $penc = 0;
            $renc = $arr[$v->kode_cabang.'_renc'];
            $real = $arr[$v->kode_cabang.'_real'];
            $real_before_12 = $arr[$v->kode_cabang.'_real_before_12'];
            if($renc):
                $penc = (view_report($real)/view_report($renc))*100;
                $penc = round($penc, 2);
            endif;

            $pert = 0;
            $real_before = $arr[$v->kode_cabang.'_real_before'];
            if($real_before):
                $pembagi = view_report($real_before);
                $pert    = ((view_report($real)-$pembagi)/$pembagi)*100;
                $pert    = round($pert, 2);
            endif;

            $h = [
                'label' => $v->kode_cabang,
                'data'  => [
                    ['x' => $penc, 'y' => $pert],
                ],
            ];
            if(isset($borderColor[$k])):
                $h['borderColor'] = $borderColor[$k];
                $h['backgroundColor'] = $backgroundColor[$k];
            endif;

            $arr['datasets'][] = $h;

            $h2 = [
                'label' => $v->kode_cabang,
                'data'  =>[round(view_report($real) - view_report($real_before_12))],
            ];
            if(isset($borderColor[$k])):
                $h2['borderColor'] = [$borderColor[$k]];
                $h2['backgroundColor'] = [$backgroundColor[$k]];
            endif;
            $arr['datasets2'][] = $h2;
        }
        return [
            'chart'     => $data,
            'chart2'    => $data2,
            'cabang'    => $arr
        ];
    }

    function get_arr_coa_other(){
        render($this->arr_coa_other(user('kode_anggaran')),'json');
    }

}