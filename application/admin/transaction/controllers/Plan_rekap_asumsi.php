<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Plan_rekap_asumsi extends BE_Controller {

	var $path       = 'transaction/budget_planner/kantor_pusat/';
    var $sub_menu   = 'transaction/budget_planner/sub_menu';
    var $controller = 'plan_rekap_asumsi';
    var $page 		= 'rekap_asumsi';
	function __construct() {
		parent::__construct();
	}

	function index() {
		$data = cabang_divisi('asumsi_kebijakan_fungsi');
        $data['path']     = $this->path;
        $data['sub_menu'] = $this->sub_menu;
        $data['controller'] = $this->controller;

        $dt_tahun = $this->detail_tahun(user('kode_anggaran'));
        $data['detail_tahun'] = $dt_tahun['detail_tahun'];
        render($data,'view:'.$this->path.$this->page.'/index');
	}

    private function detail_tahun($kode_anggaran){
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
        $detail_tahun = [];
        if($anggaran):
            $detail_tahun = get_data('tbl_detail_tahun_anggaran a',[
                'select'    => 'distinct a.bulan,a.tahun,a.sumber_data,b.singkatan',
                'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
                'where'     => [
                    'a.kode_anggaran' => $kode_anggaran,
                    'a.tahun'         => $anggaran->tahun_anggaran
                ],
                'order_by' => 'tahun,bulan'
            ])->result();
        endif;
        return [
            'anggaran'      => $anggaran,
            'detail_tahun'  => $detail_tahun,
        ];
    }

	function data($kode_anggaran,$kode_cabang){
    	$anggaran 	= get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
    	$status_group = post('status_group');
    	// $status_group = 1;
    	if($status_group == 1):
    		$cabang 	= get_data('tbl_m_cabang','id',$kode_cabang)->row();
            $cabang     = get_data('tbl_m_cabang',[
                'where' => [
                    'kode_cabang' => $cabang->kode_cabang,
                    'kode_anggaran' => $kode_anggaran
                ]
            ])->row();
    	else:
    		$cabang 	= get_data('tbl_m_cabang',[
                'where' => [
                    'kode_cabang' => $kode_cabang,
                    'kode_anggaran' => $kode_anggaran
                ]
            ])->row();
    	endif;

    	$arrKodeCabang = get_arr_cabang_level($kode_anggaran,$cabang->kode_cabang);

    	// pengecekan save untuk cabang
        $access = get_access('asumsi_kebijakan_fungsi');
        // check_save_cabang('asumsi_kebijakan_fungsi',$kode_anggaran,$cabang->kode_cabang);
        check_access_divisi('asumsi_kebijakan_fungsi',$kode_anggaran,$cabang->kode_cabang,$access);

        $arr = ['select'    => '
            a.*,
            b.nama as kebijakan_fungsi,
            c.nama_cabang,
            d.keterangan as nama_grup,
            e.glwdes,
        '];
        $arr['where']['a.kode_anggaran'] = $kode_anggaran;
        $arr['where']['a.kode_cabang']   = $arrKodeCabang;

        $arr['join'][] = 'tbl_kebijakan_fungsi b on b.id = a.id_kebijakan_fungsi';
        $arr['join'][] = 'tbl_m_cabang c on c.kode_anggaran = a.kode_anggaran and c.kode_cabang = a.kode_cabang type left';
        $arr['join'][] = "tbl_grup_asetinventaris d on d.kode = a.grup and a.grup != '' type left";
        $arr['join'][] = "tbl_m_coa e on e.glwnco = a.coa and a.coa != '' type left";
        $arr['order_by'] = 'c.urutan,a.grup,a.id';
        $arr['group_by'] = 'a.id';
        $list = get_data('tbl_kebijakan_asumsi a',$arr)->result();
        $data['list']     = $list;
        $data['kebijakan_fungsi'] = get_data('tbl_kebijakan_fungsi','is_active',1)->result();
        $data['arr_type'] = ['1' => lang('biaya'),'2' => lang('inventaris')];

        $dt_tahun = $this->detail_tahun($kode_anggaran);
        $data['detail_tahun'] = $dt_tahun['detail_tahun'];

        if(post('export') == 'export'):
        	$data['anggaran'] = $anggaran;
        	$data['cabang'] = $cabang;
        	$this->export($data);
        else:
        	$view = $this->load->view($this->path.$this->page.'/table',$data,true);

	    	render([
	    		'status' => true,
	    		'view' => $view,
	    	],'json');
        endif;
    }

    private function export($data){
    	ini_set('memory_limit', '-1');
    	$detail_tahun = $data['detail_tahun'];
        $arr_type = $data['arr_type'];

        $header = [];
        $header[] = lang('no');
        $header[] = lang('divisi');
        $header[] = lang('uraian');
        $header[] = lang('tipe');
        $header[] = lang('grup');
        $header[] = lang('kode_inventaris');
        $header[] = lang('coa');
        $header[] = lang('kantor_cabang');
        foreach ($detail_tahun as $k2 => $v2) {
            $column = month_lang($v2->bulan).' '.$v2->tahun;
            $column .= PHP_EOL.'('.$v2->singkatan.')';
            $column .= PHP_EOL.'Biaya Pd Bulan';
            $header[] = $column;
        }

        $data_export = [];
        $kebijakan_fungsi = $data['kebijakan_fungsi'];
        $list = $data['list'];
        $anggaran = $data['anggaran'];
        $cabang = $data['cabang'];
        foreach ($kebijakan_fungsi as $a) {
        	if($a->nama != '$$SUBDIV'):
        		$h = ['',$a->nama];
                for ($i=3; $i <= (8+count($detail_tahun)) ; $i++) { 
                    $h[] = '';
                }
        		array_push($data_export,$h);
        	endif;
        	$no = 0;
			$temp_cabang = '';
        	foreach ($list as $k => $v) {
        		if($a->id == $v->id_kebijakan_fungsi):
        			if($a->nama == '$$SUBDIV' and $temp_cabang != $v->nama_cabang):
						$temp_cabang = $v->nama_cabang;
						$no = 0;
						$h = [];
                        for ($i=1; $i <= (8+count($detail_tahun)) ; $i++) { 
                            $h[] = '';
                        }
    					array_push($data_export,$h);

						$h = ['',$v->nama_cabang];
                        for ($i=3; $i <= (8+count($detail_tahun)) ; $i++) { 
                            $h[] = '';
                        }
        				array_push($data_export,$h);
					endif;

                    $grup_txt = '';
                    $kode_inventaris_txt = '';
                    $coa_txt = '';
                    if($v->type == 2):
                        $grup_txt = $v->grup.' - '.remove_spaces($v->nama_grup);
                        if(in_array($v->grup,['E.4','E.5','E.6'])):
                            $kode_inventaris_txt = $v->kode_inventaris;
                        endif;
                    elseif($v->coa):
                        $coa_txt = $v->coa.' - '.remove_spaces($v->glwdes);
                    endif;

                    $type_cabang = json_decode($v->type_cabang,true);
                    if(!is_array($type_cabang)) $type_cabang = [];
                    $kantor_txt = '';
                    $no_kantor = 0;
                    if(in_array('all',$type_cabang)): $no_kantor++; $kantor_txt .= $no_kantor.'. '.lang('all').','.PHP_EOL; endif;
                    if(in_array('kc',$type_cabang)): $no_kantor++; $kantor_txt .= $no_kantor.'. KC,'.PHP_EOL; endif;
                    if(in_array('kcp',$type_cabang)): $no_kantor++; $kantor_txt .= $no_kantor.'. KCP,'.PHP_EOL; endif;
                    if(count($type_cabang)>0):
                        $ls_cabang = get_data('tbl_m_cabang',[
                            'select' => 'nama_cabang',
                            'where' => [
                                'kode_cabang' => $type_cabang,
                                'kode_anggaran' => $v->kode_anggaran,
                            ]
                        ])->result();
                        foreach ($ls_cabang as $v2) {
                            $no_kantor++; $kantor_txt .= $no_kantor.'. '.remove_spaces($v2->nama_cabang).','.PHP_EOL;
                        }
                    endif;

					$no ++;
	        		$h = [
	        			$no,
	        			$v->nama_cabang,
	        			$v->uraian,
                        $arr_type[$v->type],
                        $grup_txt,
                        $kode_inventaris_txt,
                        $coa_txt,
                        $kantor_txt,
	        		];
                    foreach ($detail_tahun as $v2) {
                        $field  = 'B_' . sprintf("%02d", $v2->bulan);
                        $val = $v->{$field};
                        $h[] = view_report($val);
                    }
	        		array_push($data_export,$h);
        		endif;
        	}

        	$h = [];
            for ($i=1; $i <= (8+count($detail_tahun)) ; $i++) { 
                $h[] = '';
            }
    		array_push($data_export,$h);
        }

        $menu = menu($this->controller);
        $title = $menu['title'];
        if(strlen($title)>26):
        	$title = substr($title,0,27).'...';
        endif;
        $config[] = [
            'title' => $title,
            'header' => $header,
            'data'  => $data_export,
        ];
        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = str_replace(' ','_',$menu['title']).'_'.str_replace(' ', '_', $anggaran->keterangan).'_'.str_replace(' ', '_', $cabang->nama_cabang).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }

}