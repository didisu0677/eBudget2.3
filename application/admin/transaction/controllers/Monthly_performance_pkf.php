<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Monthly_performance_pkf extends BE_Controller {
	var $controller = 'monthly_performance_pkf';
	function __construct() {
		parent::__construct();
	}

	function index() {
		$a  						= get_access('monthly_performance_pkf');
		$data 						= cabang_divisi('plan_data_kantor');
		$data['tahun'] 				= get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->result();
		$data['access_additional']	= $a['access_additional'];
		$data['status'] 			= $this->filter_status();
		$data['bulan'] 				= $this->filter_bulan();
		$data['kode_anggaran'] 		= user('kode_anggaran');
		render($data);
	}

	private function filter_status(){
		return [
			['value' => '0', 'name' => lang('all')],
			['value' => '-0', 'name' => 'Belum dipilih'],
			['value' => '-3', 'name' => 'Proses'],
			['value' => '-1', 'name' => 'Selesai'],
			['value' => '-2', 'name' => 'Belum Selesai'],
		];
	}

	private function arr_status(){
		return [
			'0' => 'Belum dipilih',
			'3'	=> 'Proses',
			'1'	=> 'Selesai',
			'2' => 'Belum Selesai',
		];
	}

	private function filter_bulan(){
		$data = [
			['value' => '0', 'name' => lang('all')]
		];
		for ($i=1; $i <= 12 ; $i++) { 
			array_push($data,['value' => $i, 'name' => month_lang($i)]);
		}
		return $data;
	}

	function data(){
		$kode_anggaran 	= post('kode_anggaran');
		$kode_cabang 	= post('cabang');
		$status 		= explode('-',post('status'));

		$anggaran 		= get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();

		$cab = get_data('tbl_m_cabang',[
			'where' => [
				'kode_cabang' => $kode_cabang,
				'kode_anggaran' => $anggaran->kode_anggaran
			]
		])->row();
		$kode_cabang_divisi = $cab->kode_cabang;
        if($cab->level4):
            $cab = get_data('tbl_m_cabang','id',$cab->parent_id)->row();
            $kode_cabang_divisi = $cab->kode_cabang;
        endif;

        $data_finish['kode_anggaran']   = $kode_anggaran;
        $data_finish['kode_cabang']     = $kode_cabang;
        $a = get_access($this->controller,$data_finish);
        $access_edit = false;
        if($a['access_edit']):
            $access_edit = true;
        elseif($a['access_edit'] && $a['access_additional']):
            $access_edit = true;
        endif;

        $data['akses_ubah'] = $access_edit;
        $data['cabang'] 	= $kode_cabang;
        $data['arr_status'] = $this->arr_status();

        $arr = ['select'    => '
            a.*,
            b.nama as kebijakan_umum,
            c.nama as perspektif,
            d.nama as skala_program,
            e.level4,
            e.parent_id,
            f.bulan,
            f.uraian,
            f.bobot,
            f.status,
            f.id as detail_id,
            f.keterangan as detail_keterangan,
            f.keterangan2 as detail_keterangan2,
            f.keterangan3 as detail_keterangan3,
            g.glwdes
        ',];
        if(post('bulan')):
        	$arr_bulan = [];
        	for ($i=1; $i <=post('bulan') ; $i++) { 
        		$arr_bulan[] = $i;
        	}
        	$arr['where']['f.bulan'] = $arr_bulan;
        else:
        	$arr['where']['f.bulan !='] = 0;
        endif;
        if(count($status)>1):
        	$arr_list['where']['f.status']  = $status[1];
        endif;
        $arr['order_by'] = 'a.id,f.bulan';

        $arr['join'][] = 'tbl_kebijakan_umum b on b.id = a.id_kebijakan_umum';
        $arr['join'][] = 'tbl_perspektif c on c.id = a.id_perspektif';
        $arr['join'][] = 'tbl_skala_program d on d.id = a.id_skala_program';
        $arr['join'][] = 'tbl_m_cabang e on e.kode_cabang = a.kode_cabang and e.kode_anggaran = a.kode_anggaran';
        $arr['join'][] = 'tbl_input_rkf_detail f on f.id_input_rkf = a.id type left';
        $arr['join'][] = 'tbl_m_coa g on g.glwnco = a.coa and g.glwnco != "" and g.kode_anggaran = a.kode_anggaran type left';
        
        $arr_list = $arr;
        if($kode_anggaran) {
            $arr_list['where']['a.kode_anggaran']  = $kode_anggaran;
        }
        if($kode_cabang) {
            $arr_list['where']['a.kode_cabang']  = $kode_cabang;
        }
        $list = get_data('tbl_input_rkf a',$arr_list)->result();
        $data['list']     = $list;
        $data['anggaran'] = $anggaran;

        $arr_list = $arr;
        $arr_list['like']['a.divisi_terkait'] = $kode_cabang_divisi;
        if($kode_anggaran) {
            $arr_list['where_array']['a.kode_anggaran']  = $kode_anggaran;
        }
        $arr_list['where']['a.kode_cabang !=']  = $kode_cabang;
        $list2 = get_data('tbl_input_rkf a',$arr_list)->result();
        $data2 = $data;
        $data2['list'] = $list2;

        if(post('export')):
        	ini_set('memory_limit', '-1');
        	$config[] = $this->export($data);
        	$config[] = $this->export($data2,' Divisi Terkait');
        	$this->load->library('simpleexcel',$config);
	        $filename = 'monthly_performance_rkf_'.str_replace(' ', '_', $anggaran->keterangan).date('YmdHis');
	        $this->simpleexcel->filename($filename);
	        $this->simpleexcel->export();
        else:
        	$response   = array(
	            'table' => $this->load->view('transaction/monthly_performance_pkf/table',$data,true),
	            'table2' => $this->load->view('transaction/monthly_performance_pkf/table',$data2,true),
	            'access_edit' => $access_edit
	        );
	        render($response,'json');
        endif;

	}

	private function export($data,$p1=''){
		
		$arr_status 	= $data['arr_status'];
		$dt_anggaran 	= $data['anggaran'];

		$header = [
			lang('no'),
			lang('kebijakan_umum_direksi'),	
			lang('program_kerja'),	
			lang('produk_aktivitas_baru'),	
			lang('perspektif'),	
			lang('status_program'),
			lang('skala_program'),
			lang('tujuan'),
			lang('output'),
			lang('target_financial'),
			lang('anggaran'),
			lang('anggaran_perbulan'),
			lang('pos_total_anggaran'),
			lang('divisi_terkait'),
			lang('pic'),
			lang('status_program_kerja'),
			lang('bulan'),
			lang('uraian'),
			lang('bobot'),
			lang('status_progres'),
			lang('aktivitas_penjelasan'),
			lang('keterangan').' 1',
			lang('keterangan').' 2',
		];
		$dt = [];
		$temp_id = '';
		$no = 0;
		foreach($data['list'] as $k => $v){
			if($temp_id != $v->id):
				$temp_id = $v->id;
				$no 	 += 1;

				$produk = 'Tidak'; if($v->produk == 1) $produk = 'Ya';
	     		$anggaran = 'Tidak'; if($v->anggaran == 1) $anggaran = 'Ya';

	     		$anggaran_bulan = '';
				$anggaran_total = '';
				if($v->anggaran == 1):
					$total = 0;
					for ($i=1; $i <= 12 ; $i++) { 
						$field = 'T_'.sprintf("%02d", $i);
						if($v->{$field}):
							$anggaran_bulan .= month_lang($i).' = '.custom_format(view_report($v->{$field})).' | ';
							$total += $v->{$field};
						endif;
					}
					$anggaran_total .= 'POS : '.$v->coa.'-'.remove_spaces($v->glwdes).' | ';
					$anggaran_total .= 'Total : '.custom_format(view_report($total));
				endif;

	     		$divisi_terkait = $v->divisi_terkait;
	     		$divisi = '';
				if($divisi_terkait):
					$divisi_terkait = json_decode($divisi_terkait,true);
					if(count($divisi_terkait)>0):
						$s_div = true;
						$kode_cabang_divisi = $v->kode_cabang;
				        if($v->level4):
				            $dt_cabang = get_data('tbl_m_cabang','id',$v->parent_id)->row();
				            $kode_cabang_divisi = $dt_cabang->kode_cabang;
				        endif;
				        $divisi_terkait[] = $kode_cabang_divisi;

						$ls = get_data('tbl_m_cabang',[
							'where' => [
								'kode_cabang' => $divisi_terkait,
								'kode_anggaran' => $dt_anggaran->kode_anggaran
							]
						])->result();
						$divisi = "'";
						foreach ($ls as $kk => $vv) {
							$divisi .= '- '.$vv->nama_cabang.PHP_EOL;
						}
					endif;
				endif;

				$d_pic = '';
				if($v->pic):
					$pic = json_decode($v->pic,true);
					if(count($pic)>0):
						$ls = get_data('tbl_m_pegawai','id',$pic)->result();
						$d_pic = "'";
						foreach ($ls as $kk => $vv) {
							$d_pic .= '- '.$vv->nama.' ('.$vv->nip.')'.PHP_EOL;
						}
					endif;
				endif;

				$h = [
					($no),
					$v->kebijakan_umum,
					$v->program_kerja,
					$produk,
					$v->perspektif,
					$v->status_program,
					$v->skala_program,
					$v->tujuan,
					$v->output,
					$v->target_finansial,
					$anggaran,
					$anggaran_bulan,
					$anggaran_total,
					$divisi,
					$d_pic,
					$arr_status[$v->status_program_kerja],
				];
			else:
				$h = [];
				for ($i=1; $i <= 16 ; $i++) { 
					$h[] = '';
				}
			endif;

			$bulan = '';
			if($v->bulan):
				$bulan = month_lang($v->bulan);
			endif;

			$bobot = '';
			if($v->bobot):
				$bobot = custom_format($v->bobot);
			endif;

			$status = '';
			if(strlen($v->status)):
				$status = $arr_status[$v->status];
			endif;

			$h[] = $bulan;
			$h[] = $v->uraian;
			$h[] = $bobot;
			$h[] = $status;
			$h[] = $v->detail_keterangan;
			$h[] = $v->detail_keterangan2;
			$h[] = $v->detail_keterangan3;

			array_push($dt,$h);
		}

		return [
            'title' => 'Monthly RKF'.$p1,
            'header' => $header,
            'data'  => $dt,
        ];
        
	}

	function change_status(){
		$id  	 = explode('-',post('id'));
		$page 	 = post('page');
		$status  = 'warning';
		$message = lang('data_not_found');
		$url 	 = '';
		if(count($id) == 3):
			if($page == 'detail'):
				$ck = get_data('tbl_input_rkf_detail a',[
					'select' => 'a.id,a.status,b.kode_anggaran',
					'join'	 => [
						'tbl_input_rkf b on b.id = a.id_input_rkf'
					],
					'where'	 => [
						'b.kode_cabang' 	=> $id[1],
						'a.id'				=> $id[0]
					]
				])->row();
				if($ck):
                    $rkf_bulan = rkf_bulan($ck->kode_anggaran);
					update_data('tbl_input_rkf_detail',['status' => $id[2]],'id',$id[0]);
                    // pengecekan status uraian
                    if($rkf_bulan):
                        $ck_rkf_bulan = get_data('tbl_input_rkf_detail_status',[
                            'select' => 'id',
                            'where' => [
                                'id_input_rkf_detail' => $ck->id,
                                'bulan' => $rkf_bulan
                            ]
                        ])->row();
                        $data_rkf_bulan = [
                            'id' => '',
                            'id_input_rkf_detail' => $ck->id,
                            'bulan'     => $rkf_bulan,
                            'status'    => $id[2],
                        ];
                        if($ck_rkf_bulan):
                            $data_rkf_bulan['id'] = $ck_rkf_bulan->id;
                        endif;
                        $res2 = save_data('tbl_input_rkf_detail_status',$data_rkf_bulan,[],true);
                    endif;

					$status  = 'success';
					$message = 'Data berhasil diproses';
					$url 	 = 'loadData';
				endif;
			elseif($page == 'rkf'):
				$ck = get_data('tbl_input_rkf a',[
					'select' => 'a.id',
					'where'	 => [
						'a.kode_cabang' 	=> $id[1],
						'a.id'				=> $id[0]
					]
				])->row();
				if($ck):
					update_data('tbl_input_rkf',['status_program_kerja' => $id[2]],'id',$id[0]);
					$status  = 'success';
					$message = 'Data berhasil diproses';
					$url 	 = 'loadData';
				endif;
			endif;
		endif;

		render([
			'status' 	=> $status,
			'message'	=> $message,
			'url'		=> $url,
		],'json');

	}

	function save_perubahan(){
		$data   = json_decode(post('json'),true);
        foreach($data as $id => $record) {
            update_data('tbl_input_rkf_detail', $record,'id',$id);
            $ck = get_data('tbl_input_rkf_detail a',[
                'select' => 'a.id,a.status,b.kode_anggaran,a.keterangan,a.keterangan2,a.keterangan3',
                'join'   => [
                    'tbl_input_rkf b on b.id = a.id_input_rkf'
                ],
                'where'  => [
                    'a.id'              => $id
                ]
            ])->row();
            $rkf_bulan = rkf_bulan($ck->kode_anggaran);
            // pengecekan status uraian
            if($rkf_bulan):
                $ck_rkf_bulan = get_data('tbl_input_rkf_detail_status',[
                    'select' => 'id',
                    'where' => [
                        'id_input_rkf_detail' => $ck->id,
                        'bulan' => $rkf_bulan
                    ]
                ])->row();
                $data_rkf_bulan = [
                    'id' => '',
                    'id_input_rkf_detail' => $ck->id,
                    'bulan'     => $rkf_bulan,
                    'status'    => $ck->status,
                    'keterangan'=> $ck->keterangan,
                    'keterangan2'=> $ck->keterangan2,
                    'keterangan3'=> $ck->keterangan3,
                ];
                if($ck_rkf_bulan):
                    $data_rkf_bulan['id'] = $ck_rkf_bulan->id;
                endif;
                $res2 = save_data('tbl_input_rkf_detail_status',$data_rkf_bulan,[],true);
            endif;
        } 
	}

	function file_view(){
        $id_input_rkf_detail            = post('id_input_rkf_detail');

        $dt_input_rkf = get_data('tbl_input_rkf a',[
            'join'  => 'tbl_input_rkf_detail b on b.id_input_rkf = a.id',
            'where' => [
            	'b.id' => $id_input_rkf_detail,
            ]
        ])->row();

        $data_finish['kode_anggaran']   = $dt_input_rkf->kode_anggaran;
        $data_finish['kode_cabang']     = $dt_input_rkf->kode_cabang;
        $access = get_access($this->controller,$data_finish);
        $access_edit = false;
        if($access['access_edit']):
            $access_edit = true;
        elseif($access['access_edit'] && $access['access_additional']):
            $access_edit = true;
        endif;

        $list = get_data('tbl_input_rkf_detail_file',[
            'select' => 'nama,id,file',
            'where'  => [
                'id_input_rkf_detail' => $id_input_rkf_detail,
            ]
        ])->row();
        if($list):
            $list->file = json_decode($list->file);
        endif;

        render([
            'title' => 'File',
            'list'  => $list,
            'access_edit'  => $access_edit,
        ],'json');
    }

    function save_file(){
        $data = post();

        $last_file = [];
        if($data['id']) {
            $dt = get_data('tbl_input_rkf_detail_file','id',$data['id'])->row();
            if(isset($dt->id)) {
                if($dt->file != '') {
                    $lf     = json_decode($dt->file,true);
                    foreach($lf as $l) {
                        $last_file[$l] = $l;
                    }
                }
            }
        }

        $file                       = post('file');
        $keterangan_file            = post('keterangan_file');
        $filename                   = [];
        $dir                        = '';

        if(isset($file) && is_array($file)) {
            foreach($file as $k => $f) {
                if(strpos($f,'exist:') !== false) {
                    $orig_file = str_replace('exist:','',$f);
                    if(isset($last_file[$orig_file])) {
                        unset($last_file[$orig_file]);
                        $filename[$keterangan_file[$k]] = $orig_file;
                    }
                } else {
                    if(file_exists($f)) {
                        if(@copy($f, FCPATH . 'assets/uploads/input_rkf_detail_file/'.basename($f))) {
                            $filename[$keterangan_file[$k]] = basename($f);
                            if(!$dir) $dir = str_replace(basename($f),'',$f);
                        }
                    }
                }
            }
        }

        if($dir) {
            delete_dir(FCPATH . $dir);
        }
        foreach($last_file as $lf) {
            @unlink(FCPATH . 'assets/uploads/input_rkf_detail_file/' . $lf);
        }

        $data['file'] = json_encode($filename);

        $response = save_data('tbl_input_rkf_detail_file',$data,post(':validation'),true);

        render($response,'json');
    }
}