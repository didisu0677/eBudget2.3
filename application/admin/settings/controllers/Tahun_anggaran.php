<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tahun_anggaran extends BE_Controller {
	var $arr_menu = [];
	function __construct() {
		parent::__construct();
		$dt_clone_menu = get_data('tbl_clone_menu')->result();
		foreach($dt_clone_menu as $v){
			$this->arr_menu[$v->url] = $v->tabel;
		}
	}

	function index() {	
		$data['opt_data'] = get_data('tbl_m_data_budget','is_active',1)->result_array();
		$data['opt_grup'] = get_data('tbl_grup_coa','is_active',1)->result_array();
		$data['coa'] = get_data('tbl_m_coa',[
			'where' => [
				'is_active' => 1
			],
			'group_by' => 'glwnco'
		])->result_array();

		$data['grup'][0]       =get_data('tbl_m_bottomup_besaran',[
			'select' => 'distinct grup',
			'where'  => [
				'is_active' => 1,
			],
			'sort_by' => 'urutan'
		])->result();

		foreach($data['grup'][0] as $m0) {	 
		   	$arr            = [
                'select'	=> 'a.*',
                'where'     => [
                    'a.grup' => $m0->grup,
                    'a.is_active' => 1
                ],
                'sort_by' => 'urutan'
            ];

		    $data['produk'][$m0->grup] 	= get_data('tbl_m_bottomup_besaran a',$arr)->result();    
	    } 		            

		$data['detail']	= get_data('tbl_m_bottomup_besaran',[
			'where' => [
				'is_active'=> 1,
			],
			'sort_by' => 'urutan',
		])->result();  

		$data['opt_grup'] 	= get_data('tbl_grup_coa','is_active',1)->result_array();
		$data['clone_page']	= $this->arr_clone_page()['menu'];
		render($data);
	}

	function data() {
		$config['button'][]	= button_serverside('btn-primary','btn-clone',['far fa-copy','Clone Data Tahun Anggaran',true]);
		$config['button'][]	= button_serverside('btn-success','btn-coa',['far fa-desktop','Hide Show Usulan Besaran',true]);
		$data 			= data_serverside($config);
		render($data,'json');
	}

	function get_data() {
		$data = get_data('tbl_tahun_anggaran','id',post('id'))->row_array();
		$data['detail']	= get_data('tbl_detail_tahun_anggaran',[
		    'where'		=> 'id_tahun_anggaran = '.post('id'),
		    'sort_by'	=> 'tahun,bulan',
		    'sort'		=> 'ASC'
		])->result_array();

		$data['id_coa_besaran']= json_decode($data['id_coa_besaran'],true);
		render($data,'json');
	}

	function save() {
		$data = post();
		$bulan	= post('bulan');
	    $tahun	= post('tahun');
	    $sumber_data = post('sumber_data');

			$data['id_coa_besaran']			= json_encode(post('id_coa_besaran'));
			if(count(post('id_coa_besaran')) > 0) {
				$coa 				= get_data('tbl_m_coa','id',post('id_coa_besaran'))->result();
				$_v 					= [];
				foreach($coa as $b) {
					$_v[]				= $b->glwnco;
				}

				$data['coa_besaran']			= implode(', ', $_v);
				$data['coa_show']				= implode(', ', $_v);
			}

		$response = save_data('tbl_tahun_anggaran',$data,post(':validation'));
		if($response['status'] == 'success') {
			$tahun_anggaran = get_data('tbl_tahun_anggaran','id',$response['id'])->row();

		    delete_data('tbl_detail_tahun_anggaran','id_tahun_anggaran',$response['id']);
		    
		    $c = [];
		    foreach($bulan as $i => $v) {
		        $c[$i] = [
					'id_tahun_anggaran'	=> $response['id'],
					'kode_anggaran'		=> $tahun_anggaran->kode_anggaran,
		            'bulan'	=> $bulan[$i],
		            'tahun'	=> $tahun[$i],
		            'sumber_data' => $sumber_data[$i]
				];
			}
			if(count($c) > 0) insert_batch('tbl_detail_tahun_anggaran',$c);

		//    delete_data('tbl_m_bottomup_besaran','id_anggaran',$response['id']);

			if(is_array(post('id_coa_besaran'))) {
				if(count(post('id_coa_besaran')) > 0) {
					$coa 				= get_data('tbl_m_coa','id',post('id_coa_besaran'))->result();

					foreach($coa as $b) {
						$ctahun = 0;
							$ci = [
								'id_anggaran'	=> $response['id'],
								'kode_anggaran'		=> $tahun_anggaran->kode_anggaran,
					            'keterangan_anggaran' => $tahun_anggaran->keterangan,
					            'keterangan'	=> trim($b->glwdes), 
					            'coa'	=> $b->glwnco,
					            'is_active' => 1,
							];


							$cek = get_data('tbl_m_bottomup_besaran',[
								'where' => [
									'id_anggaran' => $response['id'],
									'kode_anggaran' => $tahun_anggaran->kode_anggaran,
									'coa' => $b->glwnco,
								],
							])->row();

					//	debug($response['id']);die;

						
							if(!isset($cek->id)){
									save_data('tbl_m_bottomup_besaran',$ci);
							}else{
								$ci_update = [
						            'keterangan_anggaran' => $tahun_anggaran->keterangan,
						       //     'keterangan'	=> trim($b->glwdes), 
						            'coa'	=> $b->glwnco,
						            'is_active' => 1,
								];
								update_data('tbl_m_bottomup_besaran',$ci_update,[
									'id_anggaran'=>$response['id'],'kode_anggaran'=>$tahun_anggaran->kode_anggaran,'coa'=>$b->glwnco]);
							}
						
					}

					delete_data('tbl_m_bottomup_besaran',['id_anggaran'=>$response['id'],'coa not'=>$_v]);

				}
			}

			if(!post('id')):
				// clone_rate($tahun_anggaran->kode_anggaran,'tbl_rate'); // clone rate
				// clone_rate($tahun_anggaran->kode_anggaran,'tbl_prsn_dpk'); // clone prosentase dpk

				// $last_anggaran = get_data('tbl_tahun_anggaran',[
				// 	'select' 		=> 'kode_anggaran,tahun_anggaran',
				// 	'where' 		=> "kode_anggaran != '$tahun_anggaran->kode_anggaran' and is_active = '1' ",
				// 	'order_by' 		=> 'id',
				// 	'sort'			=> 'DESC',
				// ])->row();

				// if($last_anggaran):
				// 	$v = [];
				// 	$v['keterangan_anggaran'] 	= $tahun_anggaran->keterangan;
				// 	$v['tahun']					= $tahun_anggaran->tahun_anggaran;
				// 	$v['create_by'] 			= user('username');
				// 	$v['create_at'] 			= date("Y-m-d H:i:s");
				// 	$v['update_by'] 			= null;
				// 	$v['update_at'] 			= null;
				// 	clone_value_table('tbl_m_tarif_kolektibilitas',$last_anggaran,$tahun_anggaran,$v);
				// 	clone_value_table('tbl_indek_besaran_biaya',$last_anggaran,$tahun_anggaran,$v);
				// 	if($tahun_anggaran->tahun_anggaran == $last_anggaran->tahun_anggaran):
				// 		clone_value_table('tbl_rencana_aset',$last_anggaran,$tahun_anggaran,$v);
				// 		clone_value_table('tbl_rencana_pjaringan',$last_anggaran,$tahun_anggaran,$v);
				// 	endif;

				// 	$tbl1 = "tbl_m_rincian_kredit_".str_replace('-', '_', $tahun_anggaran->kode_anggaran);
				// 	$tbl2 = "tbl_m_rincian_kredit_".str_replace('-', '_', $last_anggaran->kode_anggaran);
				// 	clone_table($tbl1,$tbl2);
				// endif;
			endif;
		}

		render($response,'json');
	}

	function save_master_besaran() {
		$data = post();
		$urutan = post('urutan');
		$coa = post('coa');
		$grup =  post('grup');
		$keterangan = post('keterangan');
		$sub_keterangan = post('sub_keterangan');
		$sub_grup = post('sub_grup');
		$sub_urutan = post('sub_urutan');
		$nomor = post('nomor');
		$sub_nomor = post('sub_nomor');
		$sub_coa = post('sub_coa');
		$core = post('core');
		$sub_core = post('sub_core');
		$s_data = post('s_data');
		$sub_sdata = post('sub_sdata');

		$anggaran = get_data('tbl_tahun_anggaran','id',$data['id_anggaran'])->row();

		$c = [];
        foreach($keterangan as $i => $v) {
        	$dt_bottomup = get_data('tbl_m_bottomup_besaran','id',$i)->row();
     		
            $c = [
                'keterangan_anggaran' => $anggaran->keterangan,
                'keterangan'  => $keterangan[$i],
                'grup' => $grup[$i],
                'coa' => $dt_bottomup->coa,
                'urutan' => $urutan[$i],
                'data_core' => $core[$i],
                'sumber_data' => $s_data[$i]
            ];

     //       debug($c);die;
  
            $cek        = get_data('tbl_m_bottomup_besaran',[
                'where'         => [
                	'id_anggaran'  => $data['id_anggaran'],
                    'coa'     => $dt_bottomup->coa,
                    'id'      => $dt_bottomup->id,
                    ],
            ])->row();

 
            if(isset($cek->id)) {

           // 	debug($c);die;
                $response = update_data('tbl_m_bottomup_besaran',$c,[
                	'id_anggaran'  => $data['id_anggaran'],
                    'coa'     => $dt_bottomup->coa,
                    'id' => $dt_bottomup->id,
                ]);              	
            }
        }    

        
        if(is_array($sub_keterangan) && count($sub_keterangan)) {
        	$nomor ='';
			foreach ($sub_keterangan as $key2 => $value2) {
	        	$dt_subbottomup = get_data('tbl_m_bottomup_besaran','id',$key2)->row();
					
				$nomor = $sub_nomor[$key2];

				if($sub_nomor[$key2] == ""){				
					$nomor = generate_code('tbl_m_bottomup_besaran','nomor');
				}


				$data 	= [
					'nomor'     => $nomor,     
					'parent_id'	=> 1,
					'keterangan'	=> $value2,
					'id_anggaran'	=> $anggaran->id,
					'kode_anggaran'	=> $anggaran->kode_anggaran,
					'keterangan_anggaran' => $anggaran->keterangan,
					'coa' => $sub_coa[$key2],
					'grup' => $sub_grup[$key2],
					'data_core' => $sub_core[$key2],
					'urutan' => $sub_urutan[$key2],
					'sumber_data' => $sub_sdata[$key2],
				];


				$cek = get_data('tbl_m_bottomup_besaran',[
					'where' => [
						'id_anggaran' => $anggaran->id,
						'nomor' => $nomor,
					]
				])->row();

				if(!isset($cek->nomor)){
					insert_data('tbl_m_bottomup_besaran',$data);
	
				}else{
					update_data('tbl_m_bottomup_besaran',$data,[
						'nomor' => $nomor,
						'id_anggaran' => $anggaran->id,
					]);
				}	
			}

			delete_data('tbl_m_bottomup_besaran',['id_anggaran'=>$response['id'],'coa not'=>$sub_coa, 'nomor not'=>$sub_nomor]);
		}

		

		render([
			'status'	=> 'success',
			'message'	=> lang('data_berhasil_disimpan')
		],'json');

	//	render($response,'json');
	}

	function delete() {
		$anggaran = get_data('tbl_tahun_anggaran','id',post('id'))->result();
		$response = destroy_data('tbl_tahun_anggaran','id',post('id'));
		$response = destroy_data('tbl_detail_tahun_anggaran','id_tahun_anggaran',post('id'));
		
		foreach($anggaran as $a){
			$tables = list_tables();
			foreach ($tables as $k => $v) {
				if ($this->db->field_exists('kode_anggaran', $v)):
		            delete_data($v,'kode_anggaran',$a->kode_anggaran);
		        endif;
			}
		}

		render($response,'json');
	}

	function get_data_usulan() {
		$__id = post('__id');
		$data = get_data('tbl_tahun_anggaran','id',$__id)->row_array();

		$data['grup']      =get_data('tbl_m_bottomup_besaran',[
			'select' => 'distinct grup',
			'where'  => [
				'is_active' => 1,
				'id_anggaran' => $__id,
			],
			'sort_by' => 'urutan'
		])->result();


		$data['detail']	= get_data('tbl_m_bottomup_besaran',[
			'where' => [
				'is_active'=>1,
				'id_anggaran' => $__id,
				'parent_id' => 0,
			],
			'sort_by' => 'urutan',
		])->result();  



		$data['sub_detail']	= get_data('tbl_m_bottomup_besaran',[
			'where' => [
				'parent_id' => 1	
			],		
			'sort_by' => 'urutan'
		])->result();

		render($data,'json');
	}

	function get_grup($type ='echo') {
        $barang             = get_data('tbl_grup_coa a',[
            'where'     => [
                'a.is_active' => 1,
                '__for' => 'Input'
            ]
        ])->result();
        $data           = '<option value=""></option>';
        foreach($barang as $e2) {
            $data       .= '<option value="'.$e2->grup.'">'.$e2->grup.'</option>';
        }

        if($type == 'echo') echo $data;
        else return $data;       
    }
	
	function get_sumber_data($type ='echo') {
        $barang             = get_data('tbl_m_data_budget a',[
            'where'     => [
                'a.is_active' => 1,
            ]
        ])->result();
        $data           = '<option value=""></option>';
        foreach($barang as $e2) {
            $data       .= '<option value="'.$e2->id.'">'.$e2->jenis_data.'</option>';
        }

        if($type == 'echo') echo $data;
        else return $data;       
    }

	function template() {
		ini_set('memory_limit', '-1');
		$arr = ['tahun' => 'tahun','is_active' => 'is_active'];
		$config[] = [
			'title' => 'template_import_tahun_anggaran',
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

	function import() {
		ini_set('memory_limit', '-1');
		$file = post('fileimport');
		$col = ['tahun','is_active'];
		$this->load->library('simpleexcel');
		$this->simpleexcel->define_column($col);
		$jml = $this->simpleexcel->read($file);
		$c = 0;
		foreach($jml as $i => $k) {
			if($i==0) {
				for($j = 2; $j <= $k; $j++) {
					$data = $this->simpleexcel->parsing($i,$j);
					$data['create_at'] = date('Y-m-d H:i:s');
					$data['create_by'] = user('nama');
					$save = insert_data('tbl_tahun_anggaran',$data);
					if($save) $c++;
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

	function export() {
		ini_set('memory_limit', '-1');
		$arr = ['tahun' => 'Tahun','is_active' => 'Aktif'];
		$data = get_data('tbl_tahun_anggaran')->result_array();
		$config = [
			'title' => 'data_tahun_anggaran',
			'data' => $data,
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

	function clone($page,$kode_anggaran){
		if($page == 'munjalindra'):
			$tahun_anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
			$last_anggaran = get_data('tbl_tahun_anggaran',[
				'select' 		=> 'kode_anggaran,tahun_anggaran',
				'where' 		=> "kode_anggaran != '$tahun_anggaran->kode_anggaran' and is_active = '1' ",
				'order_by' 		=> 'id',
				'sort'			=> 'DESC',
			])->row();

			if($last_anggaran):
				$v = [];
				$v['keterangan_anggaran'] 	= $tahun_anggaran->keterangan;
				$v['tahun']					= $tahun_anggaran->tahun_anggaran;
				$v['create_by'] 			= user('username');
				$v['create_at'] 			= date("Y-m-d H:i:s");
				$v['update_by'] 			= null;
				$v['update_at'] 			= null;
				clone_value_table('tbl_m_tarif_kolektibilitas',$last_anggaran,$tahun_anggaran,$v);
				clone_value_table('tbl_indek_besaran_biaya',$last_anggaran,$tahun_anggaran,$v);
				if($tahun_anggaran->tahun_anggaran == $last_anggaran->tahun_anggaran):
					clone_value_table('tbl_rencana_aset',$last_anggaran,$tahun_anggaran,$v);
					clone_value_table('tbl_rencana_pjaringan',$last_anggaran,$tahun_anggaran,$v);
				endif;

				$tbl1 = "tbl_m_rincian_kredit_".str_replace('-', '_', $tahun_anggaran->kode_anggaran);
				$tbl2 = "tbl_m_rincian_kredit_".str_replace('-', '_', $last_anggaran->kode_anggaran);
				clone_table($tbl1,$tbl2);
			endif;
		endif;
	}

	private function arr_clone_page(){
		$data[] = ['value' => 'all','name' => lang('all')];

		$arr_menu = [];
		foreach($this->arr_menu as $k => $v){
			$arr_menu[] = $k;
		}
		$list = get_data('tbl_menu',['select' => 'nama,target', 'where' => ['target' => $arr_menu],'order_by' => 'nama'])->result();
		foreach($list as $v){
			$data[] = ['value'=> $v->target,'name' => remove_spaces($v->nama)];
		}

		return [
			'arr_menu' 	=> $arr_menu,
			'menu'		=> $data,
		];
	}

	function clone_data_save(){
		$this->validate_clone_data_save();

		$data 			= post();
		$filter_page 	= post('filter_page');
		if(in_array('all',$filter_page)):
			$filter_page = array_keys($this->arr_menu);
		endif;
		$data['page'] 			= json_encode($filter_page);
		$data['kode_anggaran'] 	= post('kode_anggaran_to');
		$res = save_data('tbl_clone_history',$data,post(':validation'));

		$tahun_anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',post('kode_anggaran_to'))->row();
		$last_anggaran 	= get_data('tbl_tahun_anggaran','kode_anggaran',post('kode_anggaran_from'))->row();

		$v = [];
		$v['keterangan_anggaran'] 	= $tahun_anggaran->keterangan;
		$v['tahun']					= $tahun_anggaran->tahun_anggaran;
		$v['create_by'] 			= user('username');
		$v['create_at'] 			= date("Y-m-d H:i:s");
		$v['parent_id_lama'] 		= 0;
		$v['level_count'] 			= 1;
		foreach($this->arr_menu as $name =>$tbl){
			if(in_array($name,$filter_page)):
				if(in_array($tbl,$this->arr_group_menu('cabang'))):
					delete_data($tbl,'kode_anggaran',$tahun_anggaran->kode_anggaran);
					clone_cabang($tbl,$last_anggaran,$tahun_anggaran,$v);
				elseif(in_array($tbl,$this->arr_group_menu('rate'))):
					delete_data($tbl,'kode_anggaran',$tahun_anggaran->kode_anggaran);
					clone_rate($tahun_anggaran->kode_anggaran,$tbl);
				elseif(in_array($tbl,$this->arr_group_menu('value'))):
					delete_data($tbl,'kode_anggaran',$tahun_anggaran->kode_anggaran);
					clone_value_table($tbl,$last_anggaran,$tahun_anggaran,$v);
				elseif(in_array($tbl,$this->arr_group_menu('value_tahunan')) && $tahun_anggaran->tahun_anggaran == $last_anggaran->tahun_anggaran):
					delete_data($tbl,'kode_anggaran',$tahun_anggaran->kode_anggaran);
					clone_value_table($tbl,$last_anggaran,$tahun_anggaran,$v);
				elseif(in_array($tbl,$this->arr_group_menu('tabel'))):
					$tbl1 = $tbl.str_replace('-', '_', $tahun_anggaran->kode_anggaran);
					$tbl2 = $tbl.str_replace('-', '_', $last_anggaran->kode_anggaran);
					clone_table($tbl1,$tbl2);
				endif;
			endif;
		}

		render($res,'json');
	}

	private function arr_group_menu($p1){
		$ls = get_data('tbl_clone_menu',[
			'select' => 'group_concat(tabel) as tabel',
			'where' => ['grup'=>$p1]
		])->row();
		$data = [];
		if($ls):
			$data = explode(',', $ls->tabel);
		endif;
		return $data;
	}

	private function validate_clone_data_save(){
		$kode_anggaran 		= post('kode_anggaran_to');
		$kode_anggaran_from = post('kode_anggaran_from');
		$status = 'success';
		$message= '';
		if($kode_anggaran == $kode_anggaran_from):
			$status = 'error';
			$message= 'Clone Dari Anggaran tidak boleh sama';
		endif;

		$filter_page = post('filter_page');
		if(!$filter_page){
			$status = 'error';
			$message= 'Menu tidak ada yang dipilih';
		}

		if($status != 'success'){
			render([
                'status'    => $status,
                'message'   => $message,
            ],'json');
            exit();
		}
	}

	function get_clone_history(){
		$detail 	= get_data('tbl_tahun_anggaran','id',post('id'))->row_array();
		$list_opt 	= get_data('tbl_tahun_anggaran',[
			'where' => ['id !=' => post('id'),'is_active' => 1]
		])->result();
		$dt_opt 	= '<option></option>';
		foreach ($list_opt as $v) {
			$dt_opt .= '<option value="'.$v->kode_anggaran.'">'.$v->keterangan.'</option>';
		}
		$status 	= true;
		$message 	= '';

		$history = '';
		if(!$detail):
			$status 	= false;
			$message 	= lang('data_not_found');
		else:
			$dt_history = get_data('tbl_clone_history a',[
				'select' => 'a.page,a.create_at,a.kode_anggaran_from,b.keterangan',
				'join'	 => 'tbl_tahun_anggaran b on b.kode_anggaran = a.kode_anggaran_from',
				'where'	 => [
					'a.kode_anggaran' => $detail['kode_anggaran']
				],
				'sort' 		=> 'desc',
				'sort_by' 	=> 'a.create_at'
			])->result();

			if(count($dt_history)>0):
				foreach($dt_history as $k => $v){

					$menu = $v->page;
					if(!$menu) $menu = '[]';
					$menu = json_decode($menu,true);
					$dt_menu = get_data('tbl_menu',['select' => 'nama', 'where' => ['target' => $menu],'order_by' => 'nama'])->result();
					$menu = '';
					foreach($dt_menu as $v2){
						$menu .= '- '.remove_spaces($v2->nama).'</br>';
					}

					$history .= '<tr>';
					$history .= '<td>'.($k+1).'</td>';
					$history .= '<td>'.date_indo($v->create_at).'</td>';
					$history .= '<td>'.$detail['kode_anggaran'].'</td>';
					$history .= '<td>'.$detail['keterangan'].'</td>';
					$history .= '<td>'.$v->kode_anggaran_from.' - '.$v->keterangan.'</td>';
					$history .= '<td>'.$menu.'</td>';
					$history .= '</tr>';
				}
			else:
				$history .= '<tr><td colspan="6"><b>'.lang('data_not_found').'</b></td></tr>';
			endif;
		endif;

		render([
			'id' 		=> post('id'),
			'status' 	=> $status,
			'detail' 	=> $detail,
			'opt'		=> $dt_opt,
			'history'	=> $history,
			'message'	=> $message
		],'json');
	}

	function coa_show(){
		$id = post('id');
		// $id = 7;
		$data = get_data('tbl_tahun_anggaran','id',$id)->row_array();
		$data['coa_besaran'] 	= explode(',',str_replace(' ','',$data['coa_besaran']));
		$data['coa_show'] 		= explode(',',str_replace(' ','',$data['coa_show']));
		$data['ls_coa']			= get_data('tbl_m_coa',[
			'where' => [
				'kode_anggaran' => $data['kode_anggaran'],
				'glwnco' 		=> $data['coa_besaran'],
			]
		])->result_array();

		foreach ($data['ls_coa'] as $k => $v) {
			$data['ls_coa'][$k]['glwdes'] = remove_spaces($v['glwdes']);
		}

		render($data,'json');
	}

	function save_show(){
		$coa = post('coa');
		if($coa) $coa = implode(',', $coa);

		$data = post();
		$data['coa_show'] = $coa;
		$response = save_data('tbl_tahun_anggaran',$data,post(':validation'));
		render($response,'json');
	}
}