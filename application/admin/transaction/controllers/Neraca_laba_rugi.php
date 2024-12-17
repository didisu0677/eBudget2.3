<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Neraca_laba_rugi extends BE_Controller {
    var $path = 'transaction/budget_planner/';
    var $controller = 'neraca_laba_rugi';
    function __construct() {
        parent::__construct();
    }

    function index($p1="") {
        $access         = get_access($this->controller);
        $data = data_cabang('Neraca_laba_rugi');
        $data['path'] = $this->path;
        $data['access_additional']  = $access['access_additional'];
        render($data,'view:'.$this->path.'neraca/index');
    }

     function data ($kode_anggaran="", $cabang=""){
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();
        if(!$anggaran):
            render(['status' => false,'message' => 'anggaran not found'],'json');exit();
        endif;
        // pengecekan akses cabang
        $a = get_access($this->controller);
        check_access_cabang($this->controller,$kode_anggaran,$cabang,$a);
         
        $tbl_history = 'tbl_history_'.($anggaran->tahun_anggaran-1);     
        $select     = 'TOT_'.$cabang;
        $or_neraca  = "(b.glwnco like '1%' or b.glwnco like '2%' or b.glwnco like '3%' or b.glwnco LIKE '41%' AND b.level1 = '2120011')";
        $dt         = get_data('tbl_m_coa as b',[
            'select'    => 
                   "coalesce(sum(case when a.bulan = '1' then a.".$select." end), 0) as b_1,
                    coalesce(sum(case when a.bulan = '2' then a.".$select." end), 0) as b_2,
                    coalesce(sum(case when a.bulan = '3' then a.".$select." end), 0) as b_3,
                    coalesce(sum(case when a.bulan = '4' then a.".$select." end), 0) as b_4,
                    coalesce(sum(case when a.bulan = '5' then a.".$select." end), 0) as b_5,
                    coalesce(sum(case when a.bulan = '6' then a.".$select." end), 0) as b_6,
                    coalesce(sum(case when a.bulan = '7' then a.".$select." end), 0) as b_7,
                    coalesce(sum(case when a.bulan = '8' then a.".$select." end), 0) as b_8,
                    coalesce(sum(case when a.bulan = '9' then a.".$select." end), 0) as b_9,
                    coalesce(sum(case when a.bulan = '10' then a.".$select." end), 0) as b_10,
                    coalesce(sum(case when a.bulan = '11' then a.".$select." end), 0) as b_11,
                    coalesce(sum(case when a.bulan = '12' then a.".$select." end), 0) as b_12,
                    b.glwdes,
                    b.glwnob,
                    b.glwsbi,
                    b.glwnco,
                    b.kali_minus,
                    b.level0,b.level1,b.level2,b.level3,b.level4,b.level5,
                    ",
            
            'join'      => [$tbl_history.' a on a.glwnco = b.glwnco type LEFT'],       
            'where'     => "b.kode_anggaran = '$kode_anggaran' and b.is_active = 1 and ".$or_neraca,
            'order_by'  => 'b.urutan',
            'group_by'  => 'b.glwnco'
        ])->result();
        $dt = $this->get_list_coa($dt);
        
        $data['coa']    = $dt['coa'];
        $data['detail'] = $dt['detail'];

        $view = $this->get_view($data);
        $response   = array(
            // 'table'     => $this->load->view('transaction/budget_planner/neraca/table',$data,true),
            'status'    => true,
            'table'     => $view,
        );
        render($response,'json');
    }

    private function get_list_coa($coa){
        $data = [];
        foreach ($coa as $k => $v) {
            // center
            if(!$v->level0 && !$v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
                $data['coa'][] = $v;
            endif;

            // level 0
            if($v->level0 && !$v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
                $data['detail']['coa0'][$v->level0][] = $v;
            endif;

            // level 1
            if(!$v->level0 && $v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
                $data['detail']['coa1'][$v->level1][] = $v;
            endif;

            // level 2
            if(!$v->level0 && !$v->level1 && $v->level2 && !$v->level3 && !$v->level4 && !$v->level5):
                $data['detail']['coa2'][$v->level2][] = $v;
            endif;

            // level 3
            if(!$v->level0 && !$v->level1 && !$v->level2 && $v->level3 && !$v->level4 && !$v->level5):
                $data['detail']['coa3'][$v->level3][] = $v;
            endif;

            // level 4
            if(!$v->level0 && !$v->level1 && !$v->level2 && !$v->level3 && $v->level4 && !$v->level5):
                $data['detail']['coa4'][$v->level4][] = $v;
            endif;

            // level 5
            if(!$v->level0 && !$v->level1 && !$v->level2 && !$v->level3 && !$v->level4 && $v->level5):
                $data['detail']['coa5'][$v->level5][] = $v;
            endif;
        }
        return $data;
    }

    private function get_view($data){
        $detail = $data['detail'];
        $item = '';
        foreach ($data['coa'] as $k => $v) {
            $item .= '<tr>';
            $item .= '<td>'.$v->glwsbi.'</td>'; 
            $item .= '<td>'.$v->glwnob.'</td>'; 
            $item .= '<td>'.$v->glwnco.'</td>'; 
            $item .= '<td>'.remove_spaces($v->glwdes).'</td>';
            for($i=1;$i<=12;$i++){
                $field = 'b_'.$i;
                $val   = kali_minus($v->{$field},$v->kali_minus);
                $item .= '<td class="text-right">'.check_value($val).'</td>';
            }
            $item .= '</tr>';
            if(isset($detail['coa0'][$v->glwnco])):
                 $dt = $this->loop_view($data,$detail['coa0'][$v->glwnco],0);
                 $item .= $dt['item'];
            endif;
        }
        return $item;
    }

    private function loop_view($data,$data2,$kk){
        $detail = $data['detail'];
        $item = '';
        $data2 = json_encode($data2);$data2 = json_decode($data2);
        foreach ($data2 as $k2 => $v2) {
            $item .= '<tr>';
            $item .= '<td>'.$v2->glwsbi.'</td>'; 
            $item .= '<td>'.$v2->glwnob.'</td>'; 
            $item .= '<td>'.$v2->glwnco.'</td>'; 
            $item .= '<td class="sb-'.($kk+1).'">'.remove_spaces($v2->glwdes).'</td>';
            for($i=1;$i<=12;$i++){
                $field = 'b_'.$i;
                $val   = kali_minus($v2->{$field},$v2->kali_minus);
                $item .= '<td class="text-right">'.check_value($val).'</td>';
            }
            $item .= '</tr>';
            if(isset($detail['coa'.($kk+1)][$v2->glwnco])):
                $dd = $detail['coa'.($kk+1)][$v2->glwnco];
                $dd = $this->loop_view($data,$dd,($kk+1));
                $item .= $dd['item'];
            endif;
        }
        return [
            'item' => $item,
        ];
    }

    function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_anggaran      = post('kode_anggaran');

        $kode_cabang        = post('kode_cabang');
        $kode_cabang_txt    = post('kode_cabang_txt');

        // pengecekan akses cabang
        $access = get_access($this->controller);
        check_access_cabang($this->controller,$kode_anggaran,$kode_cabang,$access);

        $header = json_decode(post('header'),true);
        $dt     = json_decode(post('data'),true);

        $data = [];
        foreach($dt as $k => $v){
            $detail = [
                $v[0],
                $v[1],
                $v[2],
                $v[3],
            ];
            for ($i=4; $i < count($v) ; $i++) { 
                $detail[] = filter_money($v[$i]);
            }
            $data[] = $detail;
        }

        $config[] = [
            'title' => 'neraca history'.' ('.get_view_report().')',
            'header' => $header[1],
            'data'  => $data,
        ];

        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'neraca_history_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }
}