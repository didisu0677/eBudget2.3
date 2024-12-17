<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Rincian_kredit extends BE_Controller {
    var $path = 'transaction/budget_planner/';
    var $controller = 'rincian_kredit/';
    var $controller2 = 'rincian_kredit';
    var $detail_tahun;
    var $kode_anggaran;
    var $arr_sumber_data = array();
    function __construct() {
        parent::__construct();
        $this->kode_anggaran  = user('kode_anggaran');
        $this->detail_tahun   = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => [
                'a.kode_anggaran' => $this->kode_anggaran,
            ],
            'order_by' => 'tahun,bulan'
        ])->result();
    }

    private  function check_sumber_data($sumber_data){
        $key = array_search($sumber_data, array_map(function($element){return $element->sumber_data;}, $this->detail_tahun));
        if(strlen($key)>0):
            array_push($this->arr_sumber_data,$sumber_data);
        endif;
    }
    
    function index($p1="") { 
        $access         = get_access('rincian_kredit');
        $data = data_cabang('rincian_kredit');
        $data['access_additional']  = $access['access_additional'];
        $data['path'] = $this->path;
        render($data,'view:'.$this->path.$this->controller.'index');
    }

     function data($anggaran="", $cabang="") {
        $ckode_anggaran = $anggaran;
        $ckode_cabang = $cabang;
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$ckode_anggaran)->row();
        if($anggaran) {
            $arr['where']['a.kode_anggaran']  = $ckode_anggaran;
        }
        if($cabang) {
            $arr['where']['a.kode_cabang']  = $ckode_cabang;
        }

        
        // pengecekan akses cabang
        $a = get_access($this->controller2);
        if(!$anggaran):
            render(['status' => false,'message' => 'anggaran not found'],'json');exit();
        endif;
        check_access_cabang($this->controller2,$ckode_anggaran,$ckode_cabang,$a);

        $table = "tbl_m_rincian_kredit_".str_replace('-', '_', $ckode_anggaran);
        $check_tbl = $this->db->table_exists($table);
        $status = false;
        if($check_tbl):
            $status = true;
        endif;

        $column = 'TOT_'.$ckode_cabang;
        if ($status):
            if($this->db->field_exists($column, $table)):
                $status = true;
            else:
                $status = false;
            endif;
        endif;

        $arrWhere  = $arr;
        $arrWhere['select'] = 'a.id,a.tipe,b.glwnco as coa,b.glwdes as nama';
        $arrWhere['where']['a.default'] = 2;
        $arrWhere['join'][]   = "tbl_m_coa b on b.glwnco = a.coa_produk_kredit and b.kode_anggaran = '".$anggaran->kode_anggaran."'";
        $arrWhere['order_by'] = 'b.urutan';
        $listKredit = get_data('tbl_kolektibilitas a',$arrWhere)->result();
        $arrWhere['select'] = $arrWhere['select'].',c.*';
        $arrWhere['join'][] = 'tbl_kolektibilitas_detail c on c.id_kolektibilitas = a.id';
        $listDetail = get_data('tbl_kolektibilitas a',$arrWhere)->result_array();

        if($status):
            $column = 'TOT_'.$ckode_cabang;
            $arrWhere  = $arr;
            $arrWhere['select'] = 'a.coa_produk_kredit,b.grup,b.kode,b.keterangan,b.urutan,'.$column;
            $arrWhere['where']['a.default'] = 2;
            $arrWhere['join'][] = $table.' b on b.grup = a.coa_produk_kredit';
            $arrWhere['order_by'] = 'b.urutan';
            $list = get_data('tbl_kolektibilitas a',$arrWhere)->result();
        else:
            $column = 'TOT_'.$ckode_cabang;
            $arrWhere  = $arr;
            $arrWhere['select'] = 'a.coa_produk_kredit,b.grup,b.kode,b.keterangan, 0 as urutan, 0 as '.$column;
            $arrWhere['where']['a.default'] = 2;
            $arrWhere['join'][] = 'tbl_m_rincian_kredit b on b.grup = a.coa_produk_kredit';
            $list = get_data('tbl_kolektibilitas a',$arrWhere)->result();
        endif;

        $data['detail_tahun']   = $this->detail_tahun;
        $data['listKredit']     = $listKredit;
        $data['listDetail']     = $listDetail;
        $data['list']           = $list;
        $data['totTxt']         = $column;
        $view  = $this->load->view($this->path.$this->controller.'table',$data,true);

        $response   = array(
            'view'      => $view,
            'status'    => true,
        );

        render($response,'json');
     }

      function export(){
        ini_set('memory_limit', '-1');
        
        $kode_anggaran_txt  = post('kode_anggaran_txt');
        $kode_anggaran      = post('kode_anggaran');

        $kode_cabang        = post('kode_cabang');
        $kode_cabang_txt    = post('kode_cabang_txt');

        // pengecekan akses cabang
        $access = get_access($this->controller2);
        check_access_cabang($this->controller2,$kode_anggaran,$kode_cabang,$access);

        $dt = json_decode(post('data'),true);

        $header = [];
        $data   = [];

        $tot_detail = post('tot_detail');
        for ($i=0; $i <= $tot_detail ; $i++) { 
            $dt2 = $dt['detail_'.$i];
            $header = $dt2['header'][1];

            $detail = ['','',$dt2['title']];
            for ($i2=1; $i2 <= 8 ; $i2++) { 
                $detail[] = '';
            }
            $data[] = $detail;

            foreach($dt2['data'] as $k => $v){
                $detail = [
                    $v[0],
                    $v[1],
                    $v[2],
                ];
                for ($i2=3; $i2 < count($v) ; $i2++) { 
                    $detail[] = filter_money($v[$i2]);
                }
                $data[] = $detail;
            }

            $detail = [];
            for ($i2=1; $i2 <= 11 ; $i2++) { 
                $detail[] = '';
            }
            $data[] = $detail;
        }

        $config[] = [
            'title'     => 'Rincian Kredit',
            'header'    => $header,
            'data'      => $data
        ];
        // render($config,'json');exit();

        $this->load->library('simpleexcel',$config);
        $filename = 'rincian_kredit_'.str_replace(' ', '_', $kode_anggaran_txt).'_'.str_replace(' ', '_', $kode_cabang_txt).'_'.date('YmdHis');
        $this->simpleexcel->filename($filename);
        $this->simpleexcel->export();
    }
}