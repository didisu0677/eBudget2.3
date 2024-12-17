<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Budget_nett_coa_utama extends BE_Controller {

	var $controller = 'budget_nett_coa_utama';
	var $anggaran;
    var $kode_anggaran;
    var $data_cab = [];
	function __construct() {
		parent::__construct();
		$this->kode_anggaran  = user('kode_anggaran');
        $this->anggaran       = get_data('tbl_tahun_anggaran','kode_anggaran',$this->kode_anggaran)->result();
	}

	function index() {
		$cabang = get_data('tbl_m_cabang a',[
            'select'    => 'distinct a.kode_cabang,a.nama_cabang,level_cabang',
            'where'     => "a.is_active = 1 and (status_group = 1 or a.parent_id = 0) and (a.nama_cabang not like '%divisi%' or a.kode_cabang = '00100')"
        ])->result_array();

		$kode_anggaran          = $this->kode_anggaran;
        $data['tahun']          = $this->anggaran;
        $data['controller']     = $this->controller;
        $data['cabang']         = array_merge(array(
            ['kode_cabang' => 'all','nama_cabang' => lang('all')],
            ['kode_cabang' => 'KONS','nama_cabang' => 'KONSOLIDASI'],
        ),$cabang);
        $data['coa']        = $this->arr_coa();
		render($data);
	}

	private function arr_coa(){
        $coa = get_data('tbl_item_plan_ba a',[
            'select' => 'a.coa,a.grup,b.glwdes,b.kali_minus',
            'join'   => "tbl_m_coa b on a.coa = b.glwnco and b.kode_anggaran = '".user('kode_anggaran')."'",
            'order_by' => 'a.id',
        ])->result();

        $data = ['All'];
        $arr_group  = [];
        foreach($coa as $v){
            $arr_group[$v->grup][] = $v;
        }
        foreach($arr_group as $k => $v){
            foreach($v as $coa){
                if(!in_array($coa->glwdes,$data)) array_push($data,$coa->glwdes);
            }
            if(count($v)>1):
                if(!in_array('Total '.$k,$data)) array_push($data,'Total '.$k);
            endif;
        }
        return $data;
    }

    function data($kode_anggaran,$kode_cabang){
    	$anggaran      = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
        $detail_tahun  = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => [
                'a.kode_anggaran' => $anggaran->kode_anggaran,
                'a.tahun'         => $anggaran->tahun_anggaran
            ],
            'order_by' => 'tahun,bulan'
        ])->result();

        $coa = get_data('tbl_item_plan_ba a',[
            'select' => 'a.coa,a.grup,b.glwdes,b.kali_minus',
            'join'   => "tbl_m_coa b on a.coa = b.glwnco and b.kode_anggaran = '".$anggaran->kode_anggaran."'",
            'order_by' => 'a.id',
        ])->result();

        $status 	= true;
        $message 	= '';
        $ck_coa     = post('ck_coa');
        if(!is_array($ck_coa)) $ck_coa = [];

        $arr_coa    = [];
        $arr_group  = [];
        foreach ($coa as $k => $v) {
            $arr_group[$v->grup][] = $v;

            if(in_array('All',$ck_coa)):
            	if(!in_array($v->coa,$arr_coa)): array_push($arr_coa,$v->coa); endif;
            elseif(in_array($v->glwdes,$ck_coa)):
            	if(!in_array($v->coa,$arr_coa)): array_push($arr_coa,$v->coa); endif;
            elseif(in_array('Total '.$v->grup,$ck_coa)):
                if(!in_array($v->coa,$arr_coa)): array_push($arr_coa,$v->coa); endif;
            endif;
        }
        if(count($arr_coa)<=0):
        	$status 	= false;
        	$message 	= 'Tidak ada coa yang dipilih';
        endif;


        $cabang = [];
        $view   = '';
        $data['anggaran'] = $anggaran;
        $data['group']    = $arr_group;
        $data['ck_coa']   = $ck_coa;
        $data['detail_tahun']   = $detail_tahun;
        if($kode_cabang == 'KONS' && $status):
            $this->konsolidasi_cabang($arr_coa,$anggaran);
            $cabang = $this->data_cab;
            $data['kode_cabang']= $kode_cabang;
        elseif($kode_cabang == 'all' && $status):
            $this->more_cabang('all',$arr_coa,$anggaran);
            $this->konsolidasi_cabang($arr_coa,$anggaran);
            $cabang = $this->data_cab;
            $data['kode_cabang']= 'all';
        elseif($status):
        	$this->more_cabang($kode_cabang,$arr_coa,$anggaran);
            $cabang = $this->data_cab;
            $data['kode_cabang']= $kode_cabang;
        endif;

        $data['cab']        = $cabang;
        $data['arr_coa']    = $arr_coa;
        $view   = $this->load->view('transaction/'.$this->controller.'/table',$data,true);

        render([
        	'status' 	=> $status,
        	'message'	=> $message,
        	'a'			=> $cabang,
            'view'      => $view,		
        ],'json');
    }

    private function more_cabang($kode_cabang,$arr_coa,$anggaran){
    	$field = '';
    	for ($i=1; $i <= 12 ; $i++) { 
    		$field .= 'b.B_' . sprintf("%02d", $i).',';
    	}

        $where = [
            'a.kode_cabang'     => $kode_cabang,
            'a.is_active'       => 1,
            'a.kode_anggaran'   => $anggaran->kode_anggaran
        ];
        if($kode_cabang == 'all'):
            unset($where['a.kode_cabang']);
            $where['a.parent_id'] = 0;
        endif;

        $cab = get_data('tbl_m_cabang a',[
            'select'    => 'a.parent_id,a.id,a.kode_cabang,a.nama_cabang,a.struktur_cabang',
            'where'     => $where,
            'order_by'  => "a.urutan"
        ])->result_array();

        foreach($cab as $k => $v){
        	$id = $v['id'];
        	$kode_cabang = $v['kode_cabang'];

            $x = get_data('tbl_m_cabang a',[
                'select'    => 'a.parent_id,a.id,a.kode_cabang,a.nama_cabang,b.coa,'.$field,
                'join'      => [
                    "tbl_budget_nett_neraca b on b.kode_cabang = a.kode_cabang and b.kode_anggaran = '$anggaran->kode_anggaran' and b.coa in (".implode(",", $arr_coa).") type left",
                    "tbl_m_coa c on b.coa = c.glwnco and c.kode_anggaran = a.kode_anggaran and c.tipe = 1 and c.glwnco != ''"
                ],
                'where'     => [
                    'a.kode_cabang'     => $kode_cabang,
                    'a.is_active'       => 1,
                    'a.kode_anggaran'   => $anggaran->kode_anggaran
                ],
                'order_by'  => "a.urutan"
            ])->result_array();

            $x_laba = get_data('tbl_m_cabang a',[
                'select'    => 'a.parent_id,a.id,a.kode_cabang,a.nama_cabang,b.coa,'.$field,
                'join'      => [
                    "tbl_budget_nett_labarugi b on b.kode_cabang = a.kode_cabang and b.kode_anggaran = '$anggaran->kode_anggaran' and b.coa in (".implode(",", $arr_coa).") type left",
                    "tbl_m_coa c on b.coa = c.glwnco and c.kode_anggaran = a.kode_anggaran and c.tipe = 2 and c.glwnco != ''"
                ],
                'where'     => [
                    'a.kode_cabang'     => $kode_cabang,
                    'a.is_active'       => 1,
                    'a.kode_anggaran'   => $anggaran->kode_anggaran
                ],
                'order_by'  => "a.urutan"
            ])->result_array();

            $x = array_merge($x,$x_laba);

        	$this->data_cab['cabang'][$kode_cabang]['nama_cabang'] = $v['nama_cabang'];
        	$this->data_cab['cabang'][$kode_cabang]['kode_cabang'] = $v['kode_cabang'];
        	$this->data_cab['cabang'][$kode_cabang]['id'] = $id;
        	$this->data_cab['cabang'][$kode_cabang]['data'] = $x;

            $this->more_cabang2($id,$kode_cabang,$arr_coa,$anggaran);
        }
    }

    private function more_cabang2($id,$kode_cabang,$arr_coa,$anggaran){
        $field = '';
        for ($i=1; $i <= 12 ; $i++) { 
            $field .= 'b.B_' . sprintf("%02d", $i).',';
        }

        $cabang = get_data('tbl_m_cabang',[
            'select' => 'id,kode_cabang,nama_cabang,struktur_cabang',
            'where'  => [
                'is_active' => 1,
                'parent_id' => $id,
                'kode_anggaran' => $anggaran->kode_anggaran
            ],
            'order_by'  => "urutan"
        ])->result_array();
        foreach ($cabang as $k => $v2) {
            $kode_cabang2 = $v2['kode_cabang'];
            $x = get_data('tbl_m_cabang a',[
                'select'    => 'a.parent_id,a.id,a.kode_cabang,a.nama_cabang,b.coa,'.$field,
                'join'      => [
                    "tbl_budget_nett_neraca b on b.kode_cabang = a.kode_cabang and b.kode_anggaran = '$anggaran->kode_anggaran' and b.coa in (".implode(",", $arr_coa).") type left",
                    "tbl_m_coa c on b.coa = c.glwnco and c.kode_anggaran = a.kode_anggaran and c.tipe = 1 and c.glwnco != ''"
                ],
                'where'     => [
                    'a.kode_cabang'     => $kode_cabang2,
                    'a.is_active'       => 1,
                    'a.kode_anggaran'   => $anggaran->kode_anggaran
                ],
                'order_by'  => "a.urutan"
            ])->result_array();

            $x_laba = get_data('tbl_m_cabang a',[
                'select'    => 'a.parent_id,a.id,a.kode_cabang,a.nama_cabang,b.coa,'.$field,
                'join'      => [
                    "tbl_budget_nett_labarugi b on b.kode_cabang = a.kode_cabang and b.kode_anggaran = '$anggaran->kode_anggaran' and b.coa in (".implode(",", $arr_coa).") type left",
                    "tbl_m_coa c on b.coa = c.glwnco and c.kode_anggaran = a.kode_anggaran and c.tipe = 2 and c.glwnco != ''"
                ],
                'where'     => [
                    'a.kode_cabang'     => $kode_cabang2,
                    'a.is_active'       => 1,
                    'a.kode_anggaran'   => $anggaran->kode_anggaran
                ],
                'order_by'  => "a.urutan"
            ])->result_array();

            $x = array_merge($x,$x_laba);
            
            $this->data_cab[$kode_cabang][$kode_cabang2]['nama_cabang'] = $v2['nama_cabang'];
            $this->data_cab[$kode_cabang][$kode_cabang2]['kode_cabang'] = $v2['kode_cabang'];
            $this->data_cab[$kode_cabang][$kode_cabang2]['data'] = $x;

            $id = $v2['id'];
            $this->more_cabang2($id,$kode_cabang2,$arr_coa,$anggaran);
        }
    }

    private function konsolidasi_cabang($arr_coa,$anggaran){
        $field = '';
        for ($i=1; $i <= 12 ; $i++) { 
            $field .= 'b.B_' . sprintf("%02d", $i).',';
        }

        $arr_kode_cabang = [];
        $cab = get_data('tbl_m_cabang a',[
            'select'    => 'a.parent_id,a.id,a.kode_cabang,a.nama_cabang,a.struktur_cabang',
            'where'     => [
                'a.is_active' => 1,
                'a.parent_id' => 0,
                'a.kode_anggaran' => $anggaran->kode_anggaran
            ],
            'order_by'  => "a.urutan"
        ])->result();
        foreach ($cab as $k => $v) {
            $arr_kode_cabang[$v->kode_cabang] = remove_spaces($v->nama_cabang);
        }

        $arr_kode_cabang['KONS'] = 'KONVENSIONAL';
        $arr_kode_cabang['KONV'] = 'KONSOLIDASI';
        foreach ($arr_kode_cabang as $k => $v) {
            $kode_cabang2 = $k;
            $nama_cabang  = $v;
            $x = get_data('tbl_budget_nett b',[
                'select'    => "'".$nama_cabang."'"." as nama_cabang,b.kode_cabang,b.coa,".$field,
                'where'     => [
                    'b.kode_cabang'     => $kode_cabang2,
                    'b.kode_anggaran'   => $anggaran->kode_anggaran,
                    'b.coa'             => $arr_coa
                ]
            ])->result_array();

            $this->data_cab['cabang'][$kode_cabang2]['nama_cabang'] = $nama_cabang;
            $this->data_cab['cabang'][$kode_cabang2]['kode_cabang'] = $kode_cabang2;
            $this->data_cab['cabang'][$kode_cabang2]['data'] = $x;
        }
    }

    function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_anggaran      = post('kode_anggaran');

        $kode_cabang        = post('kode_cabang');
        $kode_cabang_txt    = post('kode_cabang_txt');

        $dt = json_decode(post('data'),true);

        $h1 = $dt['#d-'.$kode_cabang]['header'][0];
        $h2 = $dt['#d-'.$kode_cabang]['header'][1];

        $header = ['','',''];
        $n = 3;
        $nn = 3;
        for ($i=3; $i < count($h2) ; $i++) { 
            $val = '';
            if($i == $nn):
                $val = $h1[$n];
                $nn += 13;
                $n += 2;
            endif;
            $header[] = $val;
        }
        $data = [];
        $data[] = $h2;
        foreach(['#d-'.$kode_cabang] as $name){
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

        $title = 'Budget Nett Coa Utama';
        $config[] = [
            'title' => $title.' ('.get_view_report().')',
            'header' => $header,
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = $title.'_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }

}