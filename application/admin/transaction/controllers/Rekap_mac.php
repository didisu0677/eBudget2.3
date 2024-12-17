<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rekap_mac extends BE_Controller {
	var $controller = 'rekap_mac';
	var $path       = 'transaction/';
    var $month_before = 0;
    var $path_file  = '';

    function __construct() {
        parent::__construct();
        $this->path_file = base_url().dir_upload('m_budget_control_keterangan');
    }
	
    function index() {
	 	$tahun = get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->result_array();

        $a     = get_access($this->controller);
        $data['controller']     = $this->controller;
        $data['coa'] 			= $this->coa_option();
        $data['tahun']     		= $tahun;
        $data['bulan']     		= $this->month_option();
        $data['keterangan']     = $this->dt_keterangan();
        $data['path_file']      = $this->path_file;
        $data['access_additional']  = $a['access_additional'];
        render($data);
    }

    private function dt_keterangan(){
        return get_data('tbl_m_budget_control_keterangan',[
            'where' => [
                'is_active' => 1,
                'kode_anggaran' => user('kode_anggaran')
            ]
        ])->result_array();
    }

    private function coa_option(){
    	$data = get_data('tbl_m_budget_control a',[
    		'select' 	=> 'a.coa,b.glwdes as name',
    		'where'		=> [
                'a.is_active' => 1,
                'a.kode_anggaran' => user('kode_anggaran')
            ],
    		'join'		=> "tbl_m_coa b on a.coa = b.glwnco and b.kode_anggaran = '".user('kode_anggaran')."'",
    		'order_by'	=> 'a.id',
    	])->result_array();
    	return $data;
    }
    private function month_option(){
    	$data = array();
    	for ($i=1; $i <=12 ; $i++) { 
    		$month = month_lang($i);
    		array_push($data, array('value' => $i,'name' => $month));
    	}
    	return $data;
    }

    function get_content(){
    	$bulan 	= post('bulan');
    	$tahun 	= post('tahun');
    	$coa 	= post('coa');

        if(!$coa):
            render([
                'success' => false,
                'message' => lang('data_not_found')
            ],'json');exit();
        endif;

    	$tahun = get_data('tbl_tahun_anggaran','kode_anggaran',$tahun)->row();
    	$coa   = get_data('tbl_m_coa',[
            'where' => [
                'glwnco' => $coa,
                'kode_anggaran' => $tahun->kode_anggaran
            ]
        ])->row();

        $date = date("Y-m",strtotime($tahun->tahun_anggaran.'-'.sprintf('%02d', $bulan)));
        $month_before = minusMonth($date,1);
        $year_before  = substr($month_before, 3);
        $month_before = (int) substr($month_before, 0,2);

    	$data['bulan'] = $bulan;
    	$data['tahun'] = $tahun;
    	$data['coa'] = $coa;
        $data['year_before']    = $year_before;
        $data['month_before']   = $month_before;
        $data['keterangan']     = $this->dt_keterangan();
    	$view 	= $this->load->view($this->path.$this->controller.'/content',$data,true);

    	render([
            'status'      => true,
    		'view'        => $view,
    	],'json');
    }

    function data(){
    	$bulan 	= post('bulan');
    	$tahun 	= post('tahun');
    	$coa 	= post('coa');

        if(!$coa):
            render([
                'success' => false,
                'message' => lang('data_not_found')
            ],'json');exit();
        endif;

    	$tahun = get_data('tbl_tahun_anggaran','kode_anggaran',$tahun)->row();

        $date = date("Y-m",strtotime($tahun->tahun_anggaran.'-'.sprintf('%02d', $bulan)));
        $month_before = minusMonth($date,1);
        $month_before = (int) substr($month_before, 0,2);

    	$status = true;
    	$tbl_history = 'tbl_history_'.($tahun->tahun_anggaran-1);
    	if(!$this->db->table_exists($tbl_history)):
    		$status = false;
    	endif;
        $status_current = true;
        $tbl_history_current = 'tbl_history_'.($tahun->tahun_anggaran);
        if(!$this->db->table_exists($tbl_history_current)):
            $status_current = false;
        endif;

        $dt_bulan           = [];
        $dt_des             = [];
        $dt_bulan_current   = [];
        if($status):
            $dt_bulan    = get_data($tbl_history.' a',[
                'select'=> 'a.*,b.kali_minus',
                'join'  => "tbl_m_coa b on b.glwnco = a.glwnco and b.kode_anggaran = '".$tahun->kode_anggaran."'",
                'where' => "a.glwnco = '$coa' and a.bulan = '$bulan'"])->row_array();
            $dt_des      = get_data($tbl_history.' a',[
                'select'=> 'a.*,b.kali_minus',
                'join'  => "tbl_m_coa b on b.glwnco = a.glwnco and b.kode_anggaran = '".$tahun->kode_anggaran."'",
                'where' => "a.glwnco = '$coa' and a.bulan = '12'"])->row_array();
        endif;
        if($status_current):
            $dt_bulan_current = get_data($tbl_history_current.' a',[
                'select'=> 'a.*,b.kali_minus',
                'join'  => "tbl_m_coa b on b.glwnco = a.glwnco and b.kode_anggaran = '".$tahun->kode_anggaran."'",
                'where' => "a.glwnco = '$coa' and a.bulan = '$bulan'"])->row_array();
        endif;

        $dt_before   = [];
        if($month_before == 12):
            $dt_before = $dt_des;
        else:
            $this->month_before = $month_before;
        endif;
        $dt_mac      = get_data('tbl_control_mac',['where' => "coa = '$coa' and kode_anggaran = '$tahun->kode_anggaran' and bulan = '$bulan'"])->row_array();

        $cabang['l1'] = $this->dt_data(0,$coa,$tahun,$bulan);
        foreach ($cabang['l1'] as $k => $v) {
            $id = $v['id'];
            $cabang['l2'][$id] = $this->dt_data($id,$coa,$tahun,$bulan);
            foreach ($cabang['l2'][$id] as $k2 => $v2) {
                $id2 = $v2['id'];
                $cabang['l3'][$id2] = $this->dt_data($id2,$coa,$tahun,$bulan,1);
                foreach ($cabang['l3'][$id2] as $k3 => $v3) {
                    $id3 = $v3['id'];
                    $cabang['l4'][$id3] = $this->dt_data($id3,$coa,$tahun,$bulan,1);
                }
            }
        }

        $keterangan = $this->dt_keterangan();
        
    	$data['cabang'] 	= $cabang;
    	$data['dt_bulan'] 	= $dt_bulan;
        $data['dt_bulan_current']   = $dt_bulan_current;
    	$data['dt_des'] 	= $dt_des;
        $data['dt_mac']     = $dt_mac;
        $data['dt_before']  = $dt_before;
        $data['month_before']  = 'B_'.sprintf("%02d", $month_before);
        $data['bulan']      = 'B_'.sprintf("%02d", $bulan);
        $data['bulanx']     = $bulan;
        $data['tahun']      = $tahun;
        $data['coa']        = $coa;
        $data['keterangan'] = $keterangan;
        $data['path_file']  = $this->path_file;
    	$view 	            = $this->load->view($this->path.$this->controller.'/table',$data,true);
        $view_total         = $this->total($keterangan);

    	render([
    		'view' 		=> $view,
    		'status' 	=> $status,
            'total'     => $view_total,
    	],'json');
    }

    private function dt_data($parentID,$coa,$tahun,$bulan, $order=""){
        $dt_column = $this->check_column();
        $tabel  = $dt_column['tabel'];
        $column = $dt_column['column'];
        $where  = $dt_column['where'];

        $select = [
            'select'    => 
                'a.id,a.kode_cabang,a.nama_cabang,a.level1,a.level2,a.level3,a.level4,a.struktur_cabang,'.
                $column,
            'where'     => "a.kode_anggaran = '".user('kode_anggaran')."' and a.is_active = '1' and a.parent_id = '$parentID'",
            'join'      => [
                "$tabel c on $where = '$coa' and c.kode_cabang = a.kode_cabang and c.kode_anggaran = '$tahun->kode_anggaran' TYPE LEFT"
            ],
            'order_by' => 'a.urutan'
        ];
        if($order):
            $select['order_by'] = 'a.kode_cabang';
        endif;

        $cabang = get_data('tbl_m_cabang a',$select)->result_array();
        return $cabang;
    }

    private function total($keterangan){
        $data = $this->session->control_total;
        unset($data['icon']);
        $item = '';
        $no   = 0;
        foreach ($data as $k => $v) {
            $no++;
            $item .= '<tr>';
            $item .= '<td>'.$no.'</td>';
            $item .= '<td>'.$k.'</td>';
            foreach ($keterangan as $k2 => $v2) {
                $id = $v2['id'];
                $val= (isset($v[$id])) ? $v[$id] : 0 ;
                $item .= '<td class="text-right">'.custom_format($val).'</td>';
            }
            $item .= '</tr>';
        }
        return $item;
    }

    private function get_tabel(){
    	$coa 	= post('coa');
    	$d 		= get_data('tbl_m_budget_control',[
            'where' => [
                'coa' => $coa,
                'kode_anggaran' => user('kode_anggaran')
            ]
        ])->row();
    }

    private function get_cabang($cabang){
    	$data   = [];
        $status = false;
        $fields = [];
    	foreach ($cabang as $k => $v) {
            $tot = 'TOT_'.$v->kode_cabang;
            $arr = [$tot.'_before',$tot.'_12',$tot.'_',$tot,$tot.'_real',$tot.'_penc',$tot.'_pert'];
    		if (!$this->db->field_exists($tot,'tbl_control_mac'))://check filed table
                // $status = true;
                // foreach ($arr as $x) {
                //     $fields[$x] = array(
                //         'type' => 'double',
                //         'null' => TRUE,
                //     );
                // }
            endif;

            if($v->level1 && !$v->level2 && !$v->level3 && !$v->level4): //level 1
    			$data['l1'][] = $v;
    		endif;

    		if($v->level1 && $v->level2 && !$v->level3 && !$v->level4): //level 2
    			$data['l2'][$v->level1][] = $v;
    		endif;

    		if($v->level1 && $v->level2 && $v->level3 && !$v->level4): //level 3
    			$data['l3'][$v->level2][] = $v;
    		endif;

    		if($v->level1 && $v->level2 && $v->level3 && $v->level4): //level 4
    			$data['l4'][$v->level3][] = $v;
    		endif;
    	}

        if($status):
            $this->load->dbforge();
            $this->dbforge->add_column('tbl_control_mac',$fields);
        endif;

    	return $data;
    }

    private function check_column(){
        $coa    = post('coa');
        $bulan  = post('bulan');
        
        $dt  = get_data('tbl_m_budget_control a',[
            'select' => 'a.tabel,b.tipe',
            'join' => [
                "tbl_m_coa b on b.glwnco = a.coa and b.kode_anggaran = a.kode_anggaran and b.glwnco != ''"
            ],
            'where'  => [
                'a.coa' => $coa,
                'a.is_active' => 1,
                'a.kode_anggaran' => user('kode_anggaran')
            ] 
        ])->row();
        $column = '';
        $tabel  = '';
        $where  = '';
        if($dt):
            $tabel = $dt->tabel;
            if($dt->tipe == 1):
                $tabel = 'tbl_budget_nett_neraca';
            elseif($dt->tipe == 2):
                $tabel = 'tbl_budget_nett_labarugi';
            endif;
            if($dt->tabel == 'tbl_budget_plan_neraca'):
                $c  = 'c.B_'.sprintf("%02d", $bulan);
                $as = 'B_'.sprintf("%02d", $bulan);
                $column .= $c.' as '.$as.', ';
                if($this->month_before):
                    $c  = 'c.B_'.sprintf("%02d", $this->month_before);
                    $as = 'B_'.sprintf("%02d", $this->month_before);
                    $column .= $c.' as '.$as.', ';
                endif;
                $where = 'c.coa';
            elseif($dt->tabel == 'tbl_budget_nett'):
                $c  = 'c.B_'.sprintf("%02d", $bulan);
                $as = 'B_'.sprintf("%02d", $bulan);
                $column .= $c.' as '.$as.', ';
                if($this->month_before):
                    $c  = 'c.B_'.sprintf("%02d", $this->month_before);
                    $as = 'B_'.sprintf("%02d", $this->month_before);
                    $column .= $c.' as '.$as.', ';
                endif;
                $where = 'c.coa';
            elseif($dt->tabel == 'tbl_labarugi'):
                $c  = 'c.bulan_'.$bulan;
                $as = 'B_'.sprintf("%02d", $bulan);
                $column .= $c.' as '.$as.', ';
                if($this->month_before):
                    $c  = 'c.bulan_'.$this->month_before;
                    $as = 'B_'.sprintf("%02d", $this->month_before);
                    $column .= $c.' as '.$as.', ';
                endif;
                $where = 'c.glwnco';
            endif;
        endif;

        $data = [
            'column'    => $column,
            'tabel'     => $tabel,
            'where'     => $where,
        ];

        return $data;
    }

    function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_anggaran      = post('kode_anggaran');

        $coa        = post('coa');
        $coa_txt    = post('coa_txt');

        $bulan      = post('bulan');
        $bulan_txt  = post('bulan_txt');

        $dt = json_decode(post('data'),true);

        $classnya = '.d-'.$bulan.'-'.$coa;
        $h1 = $dt[$classnya.' .t-1']['header'][1];

        $header = $h1;
        $data = [];
        foreach([$classnya.' .t-1'] as $name){
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

        $title = 'Rekap mac bulan '.$bulan_txt;
        $config[] = [
            'title' => $title.' ('.get_view_report().')',
            'header' => $header,
            'data'  => $data,
        ];

        $dt_keterangan = $this->dt_keterangan();

        $header = [lang('no'),lang('kantor')];
        foreach($dt_keterangan as $v){
            $header[] = $v['nama'];
        }
        $data = [];
        foreach([$classnya.' .t-2'] as $name){
            if(isset($dt[$name])):
                $count2 = 0;
                foreach($dt[$name]['data'] as $k => $v){
                    $count2 = count($v);
                    $detail = [
                        $v[0],
                        $v[1],
                    ];
                    for ($i=2; $i < $count2 ; $i++) { 
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

        $title2 = 'PENCAPAIAN DARI RENCANA BERDASARKAN KANTOR CABANG';
        $config[] = [
            'title' => $title2,
            'header' => $header,
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = $title.'_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $coa_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }
}