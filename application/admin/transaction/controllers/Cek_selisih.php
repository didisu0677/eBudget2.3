<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cek_selisih extends BE_Controller {
	var $path = 'transaction/budget_planner/';
    var $controller = 'cek_selisih';
    var $detail_tahun;
    var $kode_anggaran;
    var $anggaran;
    function __construct() {
        parent::__construct();
        $this->kode_anggaran  = user('kode_anggaran');
        $this->anggaran       = get_data('tbl_tahun_anggaran','kode_anggaran',$this->kode_anggaran)->row();
        $this->detail_tahun   = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => [
                'a.kode_anggaran' => $this->kode_anggaran,
                'a.tahun'         => $this->anggaran->tahun_anggaran
            ],
            'order_by' => 'tahun,bulan'
        ])->result();
    }

    function index($p1="") { 
        $access         = get_access($this->controller);

        $data = data_cabang($this->controller);
        $data['path'] = $this->path;
        $data['access_additional']  = $access['access_additional'];
        $data['detail_tahun']   	= $this->detail_tahun;
        $data['controller'] 		= $this->controller;
        render($data,'view:'.$this->path.$this->controller.'/index');
    }

    function data($kode_anggaran, $kode_cabang){
    	$anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
    	if(!$anggaran):
    		render(['status' => false,'message' => 'anggaran not found'],'json');exit();
    	endif;
    	$data_finish['kode_anggaran']   = $kode_anggaran;
        $data_finish['kode_cabang']     = $kode_cabang;
        $a = get_access($this->controller,$data_finish);
        // pengecekan akses cabang
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$a);

        $data['biaya'] = get_data('tbl_cek_selisih',[
        	'where' => [
        		'kode_cabang' 	=> $kode_cabang,
        		'kode_anggaran'	=> $kode_anggaran,
        	]
        ])->result_array();
        $data['detail_tahun'] = $this->detail_tahun;

        // aktiva pasiva
        $data['neraca'] = get_data('tbl_budget_plan_neraca a',[
        	'where' => [
        		'coa' => ['1000000','2000000'],
        		'kode_cabang' 	=> $kode_cabang,
        		'kode_anggaran'	=> $kode_anggaran
        	]
        ])->result_array();
        $data['coa_aktiva_pasiva'] = get_data('tbl_m_coa',[
        	'where' => [
        		'glwnco' 	=> ['1000000','2000000'],
        		'is_active' => 1,
        		'kode_anggaran' => $kode_anggaran,
        	],
        	'order_by' => 'glwnco'
        ])->result();

        // selisih laba rugi 
        $data['coa_laba'] = get_data('tbl_m_coa',[
        	'where' => [
        		'glwnco' 	=> ['59999'],
        		'is_active' => 1,
        		'kode_anggaran' => $kode_anggaran,
        	],
        ])->row();
        $data['laba'] = get_data('tbl_labarugi',[
        	'where' => [
        		'glwnco' => ['59999'],
        		'kode_cabang' 	=> $kode_cabang,
        		'kode_anggaran'	=> $kode_anggaran
        	]
        ])->row();
        $data['laba_sd_bulan'] = get_data('tbl_labarugi_adj',[
        	'where' => [
        		'type' => ['sdbulan'],
        		'kode_cabang' 	=> $kode_cabang,
        		'kode_anggaran'	=> $kode_anggaran
        	]
        ])->row();

        // kolektibilitas KUP dan PLO
    	$arr_kol = [
        	'join' 	 => [
        		"tbl_kolektibilitas_detail b on b.id_kolektibilitas = a.id and b.tahun_core = '".$anggaran->tahun_anggaran."'"
        	]
        ];
        $arr_kol['where'] = [
        	'a.kode_anggaran' 	=> $kode_anggaran,
        	'a.kode_cabang' 	=> $kode_cabang,
        	'a.coa_produk_kredit' => '1454321'
        ];
        $arr_kol['select'] = 'b.*';
        $data['kol_kup'] = get_data('tbl_kolektibilitas a',$arr_kol)->row();
        $data['coa_kup'] = get_data('tbl_m_coa',[
        	'where' => [
        		'glwnco' 	=> ['1454321'],
        		'is_active' => 1,
        		'kode_anggaran' => $kode_anggaran,
        	],
        ])->row();

        $arr_kol['where']['a.coa_produk_kredit'] = '1454327';
        $data['kol_plo'] = get_data('tbl_kolektibilitas a',$arr_kol)->row();
        $data['coa_plo'] = get_data('tbl_m_coa',[
        	'where' => [
        		'glwnco' 	=> ['1454327'],
        		'is_active' => 1,
        		'kode_anggaran' => $kode_anggaran,
        	],
        ])->row();

        // semua kolektibilitas
        $select = '';
        for ($i=1; $i <= 12 ; $i++) { 
        	$field = 'B_' . sprintf("%02d", $i);
        	$select .= $field.'_3 as '.$field.',';
        }
        $arr_kol = [
        	'select' => 'c.glwnco,c.glwdes,'.$select,
        	'join' 	 => [
        		"tbl_kolektibilitas_detail b on b.id_kolektibilitas = a.id and b.tahun_core = '$anggaran->tahun_anggaran'",
        		"tbl_m_coa c on c.glwnco = a.coa_produk_kredit and c.kode_anggaran = a.kode_anggaran"
        	],
        	'where' => [
        		'a.kode_anggaran' 	=> $kode_anggaran,
        		'a.kode_cabang' 	=> $kode_cabang,
        		'a.tipe' 			=> 1,
        		'a.default' 		=> [0,1]
        	],
        	'order_by' => 'c.urutan'
        ];
        $data['kol3_produktif'] = get_data('tbl_kolektibilitas a',$arr_kol)->result();
        $arr_kol['where']['a.tipe'] = 2;
        $data['kol3_konsumtif'] = get_data('tbl_kolektibilitas a',$arr_kol)->result();

        // kredit KUP dan PLO
        $select = '';
        for ($i=1; $i <= 12 ; $i++) { 
        	$field = 'P_' . sprintf("%02d", $i);
        	$select .= 'b.'.$field.',';
        }
        $data['kredit'] = get_data('tbl_m_coa a',[
        	'select' 	=> 'a.glwnco,a.glwdes,'.$select,
        	'join' 		=> [
        		"tbl_budget_plan_kredit b on b.coa = a.glwnco and b.kode_anggaran = a.kode_anggaran and b.kode_cabang = '$kode_cabang' and b.tahun_core = '$anggaran->tahun_anggaran' type left"
        	],
        	'where' 	=> [
        		'a.kode_anggaran' 	=> $kode_anggaran,
        		'a.glwnco' 			=> ['1454321','1454327']
        	],
        	'urutan' => 'a.urutan'
        ])->result();

        // Neraca ECL
        $select = '';
        for ($i=1; $i <= 12 ; $i++) { 
        	$field = 'B_' . sprintf("%02d", $i);
        	$select .= 'b.'.$field.',';
        }
        $data['ecl'] = get_data('tbl_m_coa a',[
        	'select' 	=> 'a.glwnco,a.glwdes,'.$select,
        	'join' 		=> [
        		"tbl_budget_plan_neraca b on b.coa = a.glwnco and b.kode_anggaran = a.kode_anggaran and b.kode_cabang = '$kode_cabang' type left"
        	],
        	'where' 	=> [
        		'a.kode_anggaran' 	=> $kode_anggaran,
        		'a.glwnco' 			=> ['1552000','1552011','1552015','1552016','1552012']
        	],
        	'urutan' => 'a.urutan'
        ])->result();

        // Laba Rugi beban bunga dan CKPN
        $select = '';
        for ($i=1; $i <= 12 ; $i++) { 
        	$select .= 'b.bulan_'.$i.',';
        }
        $data['ckpn'] = get_data('tbl_m_coa a',[
        	'select' 	=> 'a.glwnco,a.glwdes,'.$select,
        	'join' 		=> [
        		"tbl_labarugi b on b.glwnco = a.glwnco and b.kode_anggaran = a.kode_anggaran and b.kode_cabang = '$kode_cabang' type left"
        	],
        	'where' 	=> [
        		'a.kode_anggaran' 	=> $kode_anggaran,
        		'a.glwnco' 			=> ['5500000','5586011']
        	],
        	'urutan' => 'a.urutan'
        ])->result();

        // Laba Rugi Pendapatan PLO
        $data['pend_plo'] = get_data('tbl_m_coa a',[
        	'select' 	=> 'a.glwnco,a.glwdes,'.$select,
        	'join' 		=> [
        		"tbl_labarugi b on b.glwnco = a.glwnco and b.kode_anggaran = a.kode_anggaran and b.kode_cabang = '$kode_cabang' type left"
        	],
        	'where' 	=> [
        		'a.kode_anggaran' 	=> $kode_anggaran,
        		'a.glwnco' 			=> ['4152128','4195011','5132012','5195011']
        	],
        	'urutan' => 'a.urutan'
        ])->result();

        // Effective Rate
        // Neraca ECL
        $select = '';
        for ($i=1; $i <= 12 ; $i++) { 
        	$field = 'B_' . sprintf("%02d", $i);
        	$select .= 'b.'.$field.',';
        }
        $data['neraca_plo'] = get_data('tbl_m_coa a',[
        	'select' 	=> 'a.glwnco,a.glwdes,'.$select,
        	'join' 		=> [
        		"tbl_budget_plan_neraca b on b.coa = a.glwnco and b.kode_anggaran = a.kode_anggaran and b.kode_cabang = '$kode_cabang' type left"
        	],
        	'where' 	=> [
        		'a.kode_anggaran' 	=> $kode_anggaran,
        		'a.glwnco' 			=> ['1454327']
        	],
        	'urutan' => 'a.urutan'
        ])->result();

        $response   = array(
            'status'=> true,
            'table' => $this->load->view($this->path.$this->controller.'/table',$data,true),
        );
       
        render($response,'json');
    }

    function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_cabang_txt    = post('kode_cabang_txt');

        $kode_cabang        = post('kode_cabang');
        $kode_anggaran      = post('kode_anggaran');

        $header = json_decode(post('header'),true);
        $dt     = json_decode(post('data'),true);

        $data = [];
        foreach($dt as $k => $v){
            $keterangan = $v[1];
            if($keterangan == '.'):
                $v[1] = '';
            endif;
            $detail = [
                $v[0],
                $v[1],
            ];
            for ($i=2; $i <= count($this->detail_tahun)+1 ; $i++) { 
                $val = $v[$i];
                if(strlen($val)<=0 or $keterangan == '.'):
                    $val = '';
                elseif($val == '#'):
                    
                elseif($val != '-'):
                    $val = filter_money($val);
                    $val = (float) $val;
                else:
                    $val = 0;
                endif;
                $detail[] = $val;
            }
            $data[($k)] = $detail;
        }

        $config[] = [
            'title' => 'Cek Selisih ('.get_view_report().')',
            'header' => $header[0],
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'Cek_selisih_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }
}