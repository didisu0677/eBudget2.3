<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Target_non_finansial extends BE_Controller {
	var $path = 'transaction/budget_planner/';
	var $controller  	= 'target_non_finansial';
	var $kode_anggaran 	= '';
	var $table 			= 'tbl_target_non_finansial';
    var $arr_bulan2     = [];
	function __construct() {
		parent::__construct();
		$this->kode_anggaran = user('kode_anggaran');
	}

	function index() {
		$access = get_access($this->controller);
		$data 	= data_cabang($this->controller);
		$data['path'] 			= $this->path;
		$data['controller'] 	= $this->controller;
        $data['access_additional']  = $access['access_additional'];
        $data['arr_bulan'] 	= $this->arr_bulan($this->kode_anggaran);
		render($data,'view:'.$this->path.$this->controller.'/index');
	}

	private function arr_bulan($kode_anggaran){
		$anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
		$detail_tahun = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => [
                'a.kode_anggaran' => $this->kode_anggaran,
            ],
            'order_by' => 'tahun,bulan'
        ])->result_array();
        
		$data = [];
        for ($i=1; $i <= 12 ; $i++) { 
            $tahun = ($anggaran->tahun_anggaran - 1);
        	$key = multidimensional_search($detail_tahun,[
        		'bulan' => $i,
        		'tahun'	=> $tahun
        	]);
        	$singkatan = arrSumberData()['real'];
        	if(strlen($key)>0):
        		$singkatan = $detail_tahun[$key]['singkatan'];
        	endif;
            $h = [
                'tahun' => $tahun,
                'bulan' => $i,
                'singkatan' => $singkatan,
            ];
        	$data[] = $h;
            $this->arr_bulan2[$tahun][] = $h;
        }
        for ($i=1; $i <= 12 ; $i++) { 
            $tahun = (int) ($anggaran->tahun_anggaran);
        	$key = multidimensional_search($detail_tahun,[
        		'bulan' => $i,
        		'tahun'	=> $tahun
        	]);
        	$singkatan = arrSumberData()['real'];
        	if(strlen($key)>0):
        		$singkatan = $detail_tahun[$key]['singkatan'];
        	endif;
        	$h = [
                'tahun' => $tahun,
                'bulan' => $i,
                'singkatan' => $singkatan,
            ];
            $data[] = $h;
            $this->arr_bulan2[$tahun][] = $h;
        }
        return $data;
	}

	function data($kode_anggaran,$kode_cabang){
		$data_finish['kode_anggaran']   = $kode_anggaran;
        $data_finish['kode_cabang']     = $kode_cabang;
        $a = get_access($this->controller,$data_finish);
        $access_edit = false;
        if($a['access_edit'] && $kode_cabang == user('kode_cabang')):
            $access_edit = true;
        elseif($a['access_edit'] && $a['access_additional']):
            $access_edit = true;
        endif;
        $data['akses_ubah'] = $access_edit;
        $data['current_cabang'] = $kode_cabang;
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();

         // pengecekan akses cabang
        if(!$anggaran):
            render(['status' => false,'message' => 'anggaran not found'],'json');exit();
        endif;
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$a);

        $dt = get_data('tbl_m_target_non_finansial',[
        	'where' => [
        		'is_active' => 1,
        		'kode_anggaran' => $kode_anggaran,
        	]
        ])->result();
        $detail = get_data($this->table,[
        	'where' => [
        		'kode_anggaran' => $kode_anggaran,
        		'kode_cabang'	=> $kode_cabang
        	]
        ])->result_array();

        $data['dt'] 		= $dt;
        $data['detail'] 	= $detail;
        $data['arr_bulan']  = $this->arr_bulan($kode_anggaran);
        $data['arr_bulan2'] = $this->arr_bulan2;
        $data['anggaran']   = $anggaran;

        $response   = array(
            'status'    => true,
            'view'      => $this->load->view($this->path.$this->controller.'/table',$data,true),
        );
       
        render($response,'json');
	}

	function save_perubahan(){
		$kode_anggaran 	= post('kode_anggaran');
		$kode_cabang 	= post('kode_cabang');

        // pengecekan save untuk cabang
        check_save_cabang($this->controller,$kode_anggaran,$kode_cabang,'access_edit');

		$data   = json_decode(post('json'),true);
		foreach($data as $k => $record) {
			$x      	= explode('-', $k);
            $id_target  = $x[0];
            $tahun 		= $x[1];

            foreach($record as $k2 => $v2){
            	$record[$k2] = filter_money($v2);
            }

            $dt_target = get_data('tbl_m_target_non_finansial','id',$id_target)->row();
            $kode = '-99';
            if($dt_target):
                $kode = $dt_target->kode;
            endif;

            $ck = get_data($this->table,[
                'select'    => 'id',
                'where'     => [
                	'kode_anggaran' => $kode_anggaran,
                	'kode_cabang' 	=> $kode_cabang,
                	'kode_target' => $kode,
                	'tahun_core' 	=> $tahun
                ],
            ])->row();
            $record['id'] = '';
            if($ck):
            	$record['id'] = $ck->id;
            else:
            	$record['kode_anggaran'] 		= $kode_anggaran;
            	$record['kode_cabang'] 			= $kode_cabang;
            	$record['tahun_core'] 			= $tahun;
            	$record['kode_target'] 	        = $kode;
            endif;
            $res = save_data($this->table,$record,[],true);

		}

        render([
            'status' => true,
            'message'=> lang('data_berhasil_diperbaharui')
        ],'json');
	}

    function template(){
        ini_set('memory_limit', '-1');
        $kode_anggaran = user('kode_anggaran');
        $col = ['Kode Cabang',lang('kode_target'),lang('nama_target')];
        $arr_bulan = $this->arr_bulan($kode_anggaran);
        foreach($arr_bulan as $v){
            $col[] = month_lang($v['bulan']).' - '.$v['tahun'];
        }
        $config[] = [
            'title'     => 'template_import_transaction_target_lainnya',
            'header'    => $col,
        ];
        // render($config,'json');
        $this->load->library('simpleexcel',$config);
        $this->simpleexcel->export();
    }

    function import(){
        ini_set('memory_limit', '-1');
        $kode_anggaran = $this->kode_anggaran;
        $col = ['kode_cabang','kode','nama'];
        $arr_bulan = $this->arr_bulan($kode_anggaran);
        foreach($arr_bulan as $v){
            $col[] = $v['bulan'].' - '.$v['tahun'];
        }

        $file       = post('fileimport');
        $currency   = post('currency');

        $dt_currency = get_currency($currency);

        $this->load->library('simpleexcel');
        $this->simpleexcel->define_column($col);
        $jml = $this->simpleexcel->read($file);
        $c = 0;
        foreach($jml as $i => $k) {
            if($i==0) {
                for($j = 2; $j <= $k; $j++) {
                    $data = $this->simpleexcel->parsing($i,$j);
                    $ck_m_target = get_data('tbl_m_target_non_finansial',[
                        'select' => 'id,kode',
                        'where'  => [
                            'kode_anggaran' => $kode_anggaran,
                            'kode'          => $data['kode']
                        ]
                    ])->row();

                    if($ck_m_target):
                        $data2 = [];
                        foreach($data as $k2 => $v){
                            $name = explode(' - ', $k2);
                            if(count($name)>1):
                                $bulan = 'B_' . sprintf("%02d", $name[0]);
                                $tahun = (int) $name[1];
                                $data2[$tahun][$bulan] = (float) $v * $dt_currency['nilai'];
                            endif;
                        }
                        foreach($data2 as $k2 => $v){
                            $v['kode_target'] = $ck_m_target->kode;
                            $v['kode_anggaran']     = $kode_anggaran;
                            $v['tahun_core']        = $k2;
                            $v['kode_cabang']       = $data['kode_cabang'];

                            $ck = get_data($this->table,[
                                'select' => 'id',
                                'where' => [
                                    'kode_target' => $ck_m_target->kode,
                                    'kode_anggaran'     => $kode_anggaran,
                                    'tahun_core'        => $k2,
                                    'kode_cabang'       => $data['kode_cabang']
                                ]
                            ])->row();

                            if($ck):
                                $v['update_at'] = date('Y-m-d H:i:s');
                                $v['update_by'] = user('nama');
                                $save = update_data($this->table,$v,'id',$ck->id);
                                if($save): $c++; endif; 
                            else:
                                $v['create_at'] = date('Y-m-d H:i:s');
                                $v['create_by'] = user('nama');
                                $save = insert_data($this->table,$v);
                                if($save): $c++; endif; 
                            endif;

                        }
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

    function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_anggaran      = post('kode_anggaran');

        $kode_cabang        = post('kode_cabang');
        $kode_cabang_txt    = post('kode_cabang_txt');

        // pengecekan akses cabang
        $a = get_access($this->controller);
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$a);

        $header = json_decode(post('header'),true);
        $dt     = json_decode(post('data'),true);

        $data = [];
        foreach($dt as $k => $v){
            $detail = [
                $v[0],
                $v[1],
            ];

            for ($i=2; $i <=13 ; $i++) { 
                $val = $v[$i];
                if($val && $val != '-'):
                    $val = filter_money($val);
                    $val = (float) $val;
                elseif($val == '-'):
                    $val = '';
                else:
                    $val = 0;
                endif;
                $detail[] = $val;
            }
            $data[$k] = $detail;
        }

        $config[] = [
            'title'     => 'Targaet NON FINANSIAL',
            'header'    => $header[0],
            'data'      => $data
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'Targaet_non_finansial_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }
}