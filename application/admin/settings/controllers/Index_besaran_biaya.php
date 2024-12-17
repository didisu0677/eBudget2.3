<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class index_besaran_biaya extends BE_Controller {
	var $path = 'settings/index_besaran_biaya/';
    var $detail_tahun;
    var $kode_anggaran;
    function __construct() {
        parent::__construct();
        $this->kode_anggaran  = user('kode_anggaran');
        $this->detail_tahun   = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => [
                'a.kode_anggaran' => $this->kode_anggaran,
                'a.sumber_data'   => array(2,3)
            ],
            'order_by' => 'tahun,bulan'
        ])->result();
    }

	function index() {
		$tahun_anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$this->kode_anggaran)->result(); 
        $id_coa         = json_decode($tahun_anggaran[0]->id_coa_besaran);
        $coa            = get_data('tbl_m_coa a',[
                            'select' => 'distinct b.coa, a.glwdes, a.glwnco',
                            'join'   => 'tbl_indek_besaran b on b.coa = a.glwnco',
                        ])->result();
        // $coa            = get_data('tbl_m_coa','id', $id_coa)->result();
        $data['tahun']  = $tahun_anggaran;
        $data['coa']    = $coa;
        $data['detail_tahun']    = $this->detail_tahun;
       
        
        $page = $this->input->get('page');
        if(!$page):
        	render($data,'view:'.$this->path.'index');
        else:
        	render($data,'view:'.$this->path.$page);
        endif;
	}

	function data($anggaran="") {
		$data['coa'] = get_data('tbl_m_coa a',[
			'select'    => 'distinct a.id as getId ,a.glwnco,a.glwdes, b.*',
            'join'      => "tbl_indek_besaran_biaya b on a.glwnco = b.coa and b.kode_anggaran = '".$anggaran."'  type LEFT",
            'where'     => "(a.glwnco like '45%' or a.glwnco like '56%' or a.glwnco like '57%' or a.glwnco like '58%') and a.kode_anggaran = '$anggaran'",
            'sort_by'  => 'a.glwnco'
		])->result();

        $data['detail_tahun'] = $this->detail_tahun;

        $response   = array(
            'table'     => $this->load->view('settings/index_besaran_biaya/table',$data,true),
            'data'     => $data,
        );
        render($response,'json');
	}



    function save_perubahan($anggaran="") {       

        $data   = json_decode(post('json'),true);

        // echo post('json');

        foreach($data as $getId => $record) {
			$cekId = $getId;

			// echo $id." - ".$cekId[1]."<br>";
            $cek  = get_data('tbl_indek_besaran_biaya a',[
                'select'    => 'a.id',
                'where'     => [
                    'a.coa'             => $cekId,
                    'a.kode_anggaran'   => $anggaran,
                ]
            ])->result_array();
     
            if(count($cek) > 0){
                update_data('tbl_indek_besaran_biaya', $record,'id',$cek[0]['id']);
	        }else {
	                $record['coa'] = $cekId;
	                $record['kode_anggaran'] = $anggaran;
	                insert_data('tbl_indek_besaran_biaya',$record);
	        } 
         } 
    }

	function get_data() {
		$data = get_data('tbl_indek_besaran','id',post('id'))->row_array();
		render($data,'json');
	}

	function save() {
		$response = save_data('tbl_indek_besaran',post(),post(':validation'));
		render($response,'json');
	}

	function delete() {
		$response = destroy_data('tbl_indek_besaran','id',post('id'));
		render($response,'json');
	}

	function template() {
		ini_set('memory_limit', '-1');
		$arr = ['coa','keterangan'];
        for ($i=1; $i <= 12 ; $i++) { 
            $arr[] = month_lang($i);
        }
        $arr[] = 'is_active';
		$config[] = [
			'title' => 'template_import_index_besaran',
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}



	function export() {
        $kode_anggaran = $this->kode_anggaran;
		ini_set('memory_limit', '-1');
		$arr = ['coa' => 'Coa','glwdes' => 'Keterangan'];
        for ($i=1; $i <= 12 ; $i++) { 
            $arr['bulan'.$i] = month_lang($i);
        }
        $arr['is_active'] = 'Aktif';
		$data = get_data('tbl_indek_besaran_biaya a',[
            'select' => 'a.*,b.glwdes',
            'join'   => ["tbl_m_coa b on b.glwnco = a.coa and b.kode_anggaran = a.kode_anggaran"],
            'where' => [
                'a.kode_anggaran' => $kode_anggaran
            ]
        ])->result_array();
		$config = [
			'title' => 'indek_besaran_biaya',
			'data' => $data,
			'header' => $arr,
		];
		$this->load->library('simpleexcel',$config);
		$this->simpleexcel->export();
	}

    function import() {
        ini_set('memory_limit', '-1');
        $kode_anggaran = $this->kode_anggaran;
        $col = ['coa','keterangan'];
        for ($i=1; $i <= 12 ; $i++) { 
            $col[] = 'bulan'.$i;
        }
        $col[] = 'is_active';
        $file = post('fileimport');
        $this->load->library('simpleexcel');
        $this->simpleexcel->define_column($col);
        $jml = $this->simpleexcel->read($file);
        $c = 0;
        foreach($jml as $i => $k) {
            if($i==0) {
                for($j = 2; $j <= $k; $j++) {
                    $data = $this->simpleexcel->parsing($i,$j);
                    $data['kode_anggaran'] = $kode_anggaran;
                    $ck = get_data('tbl_indek_besaran_biaya',[
                        'select' => 'id',
                        'where' => [
                            'kode_anggaran' => $kode_anggaran,
                            'coa'           => $data['coa']
                        ]
                    ])->row();

                    $data['id'] = '';
                    if($ck):
                        $data['id'] = $ck->id;
                    endif;
                    $save = save_data('tbl_indek_besaran_biaya',$data,[],true);
                    if(isset($save['id'])) $c++;
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
}



