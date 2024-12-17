<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class E_kartu_biaya_rekap extends BE_Controller {

	var $controller = 'e_kartu_biaya_rekap';
    var $anggaran;
    var $kode_anggaran;
    var $table = 'tbl_monitoring_anggaran';
	function __construct() {
		parent::__construct();
		$this->kode_anggaran  = user('kode_anggaran');
        $this->anggaran       = get_data('tbl_tahun_anggaran','kode_anggaran',$this->kode_anggaran)->result();
	}

	function index() {
		$data['tahun'] 		= $this->anggaran;
		$data['controller'] = $this->controller;

		$coa = $this->option_coa($this->kode_anggaran);
        $data['coa'] 				= $coa['data'];
        $data['coa_selected'] 		= $coa['selected'];
        render($data);
	}

	private function option_coa($kode_anggaran){
		$ls = get_data('tbl_m_coa a',[
            'select' => 'a.glwnco,a.glwdes',
            'join' 	 => [
            	'tbl_m_monitoring_anggaran b on b.coa = a.glwnco and b.kode_anggaran = a.kode_anggaran',
            ],
            'where'  => [
                'b.is_active' => 1,
                'a.is_active' => 1,
                'b.kode_anggaran' => $kode_anggaran,
            ]
        ])->result();

        $data = [];
        $selected = '';
        foreach ($ls as $k => $v) {
        	if($k == 'selected'):
        		$selected = $v->glwnco;
        	endif;
        	$data[] = ['glwnco' => $v->glwnco, 'glwdes' => $v->glwnco.' - '.remove_spaces($v->glwdes)];
        }
        return [
        	'data' => $data,
        	'selected' => $selected
        ];
	}

	var $selected 	 = '';
	var $arr_sub_coa = [];
	var $dt_coa 	 = '';
	function option_sub_coa(){
		$kode_anggaran = post('kode_anggaran');
		$coa = post('coa');

		$dt = get_data('tbl_m_coa a',[
            'select' => 'a.glwnco,a.glwdes,a.tipe',
            'join' 	 => [
            	'tbl_m_monitoring_anggaran b on b.coa = a.glwnco and b.kode_anggaran = a.kode_anggaran',
            ],
            'where'  => [
                'b.is_active' => 1,
                'a.is_active' => 1,
                'b.kode_anggaran' => $kode_anggaran,
                'a.glwnco' => $coa
            ]
        ])->row();
        if(!$dt):
        	render(['status' => true, 'message' => 'coa not found'],'json');exit();
        endif;

        $option = '';
        $selected = '';
        $sub_coa = $this->sub_coa_ls($coa,$kode_anggaran);
        if($sub_coa['count'] > 0):
        	$selected = $this->selected;
        	$option = $sub_coa['option'];
        else:
        	$selected = $coa;
        	$option .= '<option value="'.$coa.'">'.$coa.' - '.remove_spaces($dt->glwdes).'</option>';
        endif;
    	
    	render([
    		'status' => true,
    		'data' => $option,
    		'selected' => $selected
    	],'json');
	}

	private function sub_coa_ls($coa,$kode_anggaran){
		$ls = get_data('tbl_m_coa',[
        	'select' => 'glwnco,glwdes',
        	'where'	=> "
        		kode_anggaran = '$kode_anggaran' and is_active = 1 and 
        		(level0  = '$coa' or level1  = '$coa' or level2  = '$coa' or level3  = '$coa' or level4  = '$coa' or level5  = '$coa')
    		",
    		'order_by' => 'urutan'
        ])->result();

		$option = '';
        foreach ($ls as $k => $v) {
        	if($v->glwnco):
        		$this->arr_sub_coa[] = $v->glwnco;
        		$sub = $this->sub_coa_ls($v->glwnco,$kode_anggaran);
	        	if($sub['count'] <= 0):
	        		if(!$this->selected):
	        			$this->selected = $v->glwnco;
	        		endif;
	        		$option .= '<option value="'.$v->glwnco.'">'.$v->glwnco.' - '.remove_spaces($v->glwdes).'</option>';
	        	endif;
	        	$option .= $sub['option'];
        	endif;
        }

        return [
        	'count' 	=> count($ls),
        	'option'	=> $option,
        ];
	}

	function data(){
		$kode_anggaran 	= post('kode_anggaran');
		$coa 			= post('coa');
		$sub_coa 		= post('sub_coa');

		$anggaran 	= get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
		// pengecekan akses cabang
		if(!$anggaran):
			render(['status' => false,'message' => 'anggaran not found'],'json');exit();
		endif;

		$this->validate($kode_anggaran,['data']);

		$p1 = date("Y-m-d",strtotime(post('start_date')));
        $p2 = date("Y-m-d",strtotime(post('end_date')));
        $loop_date = $this->loop_date($p1,$p2);

        $list = get_data($this->table,[
        	'select' => 'kode_cabang,'.$loop_date['select'],
        	'where'  => [
        		'kode_anggaran' => $kode_anggaran,
        		'tanggal >= ' => $p1,
        		'tanggal <=' => $p2,
        		'sub_coa' 	=> $sub_coa,
        	],
        	'group_by' => 'kode_cabang,coa'
        ])->result_array();

		$cabang = $this->cabang(0,[],$kode_anggaran);
		$data['cabang'] 		= $cabang;
		$data['days'] 			= $loop_date['days'];
		$data['list'] 			= $list;

		$view = $this->load->view('transaction/'.$this->controller.'/table',$data,true);
		
		$tanggal_txt = lang('periode').' '.date_lang($p1);
		if($p1 != $p2):
			$tanggal_txt .= ' - '.date_lang($p2);
		endif;

		$class = [
			'#result1 table' => $view,
			'#bln' => $tanggal_txt,
		];

		$response   = array(
        	'status' 	=> true,
            'class'     => $class,
        );
        render($response,'json');
	}

	private function loop_date($p1,$p2){
		$t1 = strtotime($p1);
    	$t2 = strtotime($p2);

    	$arr = ['select' => ''];
		while ($t1 <= $t2) {
	        $day = date('Y-m-d', $t1);
	        $day_as = str_replace('-','_',$day);
	        $t1 = strtotime('+1 day', $t1);
	        $arr['days'][] = $day;
	        $arr['select'] .= "coalesce(sum(case when DATE(tanggal) = '$day' then biaya end), 0) as $day_as,";
	    }
	    return $arr;
	}

	private function cabang($id,$data,$kode_anggaran){
		$where = [
			'a.is_active' 		=> 1,
			'a.kode_anggaran' 	=> $kode_anggaran,
			'a.parent_id' 		=> 0,
			// 'a.kode_cabang !='  => '00100'
		];
		if($id):
			$where['a.parent_id'] = $id;
		endif;

		$dt = get_data('tbl_m_cabang a',[
			'select' 	=> 'a.id,a.kode_cabang,a.nama_cabang',
			'where' 	=> $where,
			'order_by'	=> 'a.urutan',
		])->result();
		if(count($dt)>0):
			$data[$id] = $dt;
			foreach($dt as $v){
				$data = $this->cabang($v->id,$data,$kode_anggaran);
			}
		endif;
		return $data;
	}

	private function validate($kode_anggaran,$data=[]){
		$status 	= 'failed';
		if(in_array('data',$data)):
			$status = false;
		endif;

		// tanggal
		if(!post('start_date')):
        	render(['status' => $status, 'message' => lang('tanggal_mulai').' not found'],'json');exit();
        endif;
        if(!post('end_date')):
        	render(['status' => $status, 'message' => lang('tanggal_selesai').' not found'],'json');exit();
        endif;
        $start_date = date("Y-m-d",strtotime(post('start_date')));
        $end_date 	= date("Y-m-d",strtotime(post('end_date')));

        $date1 = new DateTime($start_date);
		$date2 = new DateTime($end_date);
		$days  = (int) $date2->diff($date1)->format('%a') + 1;
		if($days>31):
			render(['status' => $status, 'message' => lang('filter_tanggal_max')],'json');exit();
		endif;

		// coa
		$coa 		= post('coa');
		$sub_coa 	= post('sub_coa');
		$dt = get_data('tbl_m_coa a',[
            'select' => 'a.glwnco,a.glwdes,a.tipe',
            'join' 	 => [
            	'tbl_m_monitoring_anggaran b on b.coa = a.glwnco and b.kode_anggaran = a.kode_anggaran',
            ],
            'where'  => [
                'b.is_active' => 1,
                'a.is_active' => 1,
                'b.kode_anggaran' => $kode_anggaran,
                'a.glwnco' => $coa
            ]
        ])->row();
        if(!$dt):
        	render(['status' => $status, 'message' => 'coa not found'],'json');exit();
        endif;
        $this->dt_coa = $dt;

        $sub_coa_ls = $this->sub_coa_ls($coa,$kode_anggaran);
        if($sub_coa_ls['count'] <= 0):
        	$this->arr_sub_coa[] = $coa;
        endif;
        if(!in_array($sub_coa,$this->arr_sub_coa)):
        	render(['status' => $status, 'message' => 'sub coa not found'],'json');exit();
        endif;
	}

	function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_anggaran      = post('kode_anggaran');

        $p1 = date("Y-m-d",strtotime(post('start_date')));
        $p2 = date("Y-m-d",strtotime(post('end_date')));
		
		$tanggal_txt = lang('periode').' '.date_lang($p1);
		if($p1 != $p2):
			$tanggal_txt .= ' - '.date_lang($p2);
		endif;

        $dt = json_decode(post('data'),true);

        $header = $dt['#result1']['header'][0];

        $data = [];
        foreach(['#result1'] as $name){
            if(isset($dt[$name])):
                $count2 = 0;
                foreach($dt[$name]['data'] as $k => $v){
                    $count2 = count($v);
                    $detail = [
                        $v[0],
                        $v[1],
                    ];
                    for ($i=2; $i < $count2 ; $i++) { 
                    	$detail[] = filter_money($v[$i]);
                    }
                    $data[] = $detail;
                }
            endif;
        }

        $config[] = [
            'title' => 'REKAP E-KARTU BIAYA',
            'header' => $header,
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'Rekap E-KARTU Biaya_'.$tanggal_txt.'_'.$kode_anggaran_txt.'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }

}