<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Usulan_kegiatan extends BE_Controller {
    var $controller = 'usulan_kegiatan';
    function __construct() {
        parent::__construct();
    }

    function index() {
        $data = data_cabang('usulan_kegiatan');
        $a  = get_access('usulan_kegiatan');
        $data['access_additional']  = $a['access_additional'];
        render($data);
    }

    function data($anggaran="", $cabang="", $tipe = 'table') {
        $menu = menu();
        $ckode_anggaran = $anggaran;
        $ckode_cabang = $cabang;

        $data_finish['kode_anggaran']   = $ckode_anggaran;
        $data_finish['kode_cabang']     = $ckode_cabang;
        $a = get_access('usulan_kegiatan',$data_finish);
        $access_edit = false;
        if($a['access_edit'] && $cabang == user('kode_cabang')):
            $access_edit = true;
        elseif($a['access_edit'] && $a['access_additional']):
            $access_edit = true;
        endif;
        $data['akses_ubah'] = $access_edit;

        $data['current_cabang'] = $cabang;
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$ckode_anggaran)->row();
        if(!$anggaran):
            render(['status' => false,'message' => 'anggaran not found'],'json');exit();
        endif;

        // pengecekan akses cabang
        check_access_cabang($this->controller,$ckode_anggaran,$cabang,$a);


        $data['bulan'] = get_data('tbl_detail_tahun_anggaran a',[
            'select' => 'a.*,b.singkatan',
            'join'  => ['tbl_m_data_budget b on a.sumber_data = b.id type LEFT',
            ],
            'where' => [
                'a.kode_anggaran' => user('kode_anggaran'),
                'a.sumber_data !=' => 1
                ],
            'sort_by'   => 'a.tahun,a.bulan',
            'sort'      => 'ASC'
        ])->result();

   	    $arr            = [
            'select'	=> 'a.*',
        ];

        if($anggaran) {
            $arr['where']['a.kode_anggaran']  = $ckode_anggaran;
        }

        if($cabang) {
            $arr['where']['a.kode_cabang']  = $ckode_cabang;
        }

        $produk 	= get_data('tbl_rencana_kpromosi a',$arr)->result();
        $nama_cabang ='';
        foreach ($produk as $m1) {
            $cabang = get_data('tbl_m_cabang','kode_cabang',$ckode_cabang)->row();
            if(isset($cabang->nama_cabang)) $nama_cabang = $cabang->nama_cabang;
        	$data2 = array(
                'kode_anggaran' => $ckode_anggaran,
                'keterangan_anggaran' => $anggaran->keterangan,
                'tahun'  => $anggaran->tahun_anggaran,
                'kode_cabang'   => $ckode_cabang,
                'cabang'        => $nama_cabang,
                'username'      => user('username'),
                'nomor_kegiatan' => $m1->nomor_kegiatan,
                'nama_kegiatan' => $m1->nama_kegiatan,
            );

            $cek		= get_data('tbl_rencana_kpromosi',[
                'where'			=> [
                    'kode_anggaran'   => $ckode_anggaran,
                    'kode_cabang'	  => $ckode_cabang,
                    'tahun'           => $anggaran->tahun_anggaran,
                    'nomor_kegiatan'  => $m1->nomor_kegiatan,  
                    'nama_kegiatan'	  => $m1->nama_kegiatan,
                    ],
            ])->row();

            
            if(!isset($cek->id)) {
                $response = 			insert_data('tbl_rencana_kpromosi',$data2);
            }
        }      

    	$arr            = [
            'select'	=> 'a.*',
        ];

        if($anggaran) {
            $arr['where']['a.kode_anggaran']  = $ckode_anggaran;
        }

        if($cabang) {
            $arr['where']['a.kode_cabang']  = $ckode_cabang;
        }
        $arr['sort_by'] = 'a.id';
        $arr['sort']    = 'DESC';
        $data['produk'] 	= get_data('tbl_rencana_kpromosi a',$arr)->result();     

        $response	= array(
            'status'    => true,
            'table'		=> $this->load->view('transaction/usulan_kegiatan/table',$data,true),
            'edit'      => $access_edit,
        );

	    render($response,'json');
	}

	function get_data() {
        $dt = get_data('tbl_rencana_kpromosi','id',post('id'))->row();
		$data = get_data('tbl_rencana_kpromosi',[
            'where' => [
            'kode_anggaran' => $dt->kode_anggaran,    
            'tahun' => $dt->tahun,
            'kode_cabang' => $dt->kode_cabang
        ],
        ])->row_array();

        $data['detail_ket'] = get_data('tbl_rencana_kpromosi',[
            'where' => [
                'kode_anggaran' => $dt->kode_anggaran,     
                'tahun' => $dt->tahun,
                'kode_cabang' => $dt->kode_cabang,
            ],
            'sort_by' => 'id',
            'sort' => 'DESC'
        ])->result_array();

		render($data,'json');
	}	

    function save_perubahan() {
        $data   = json_decode(post('json'),true);
        $kode_anggaran = post('kode_anggaran');
        $kode_cabang = post('kode_cabang');

        // pengecekan save untuk cabang
        check_save_cabang($this->controller,$kode_anggaran,$kode_cabang,'access_edit');

        foreach($data as $id => $record) {
            $result = insert_view_report_arr($record);
            update_data('tbl_rencana_kpromosi', $result,'id',$id);
        }

        render([
            'status' => true,
            'message' => lang('data_berhasil_diperbaharui'),
        ],'json');
    }

    function save() {
        $data = post();
        $kode_cabang = post('kode_cabang');
        $ckode_anggaran = user('kode_anggaran');

        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$ckode_anggaran)->row();
        $tahun         = $anggaran->tahun_anggaran;
        $keterangan  = post('keterangan');
        $dt_id       = post('dt_id');
        $cabang      = get_data('tbl_m_cabang','kode_cabang',user('kode_cabang'))->row();

        // pengecekan save untuk cabang
        check_save_cabang($this->controller,$ckode_anggaran,$kode_cabang,'access_edit');

        $c = [];
        $arrID = [];
        if(is_array($keterangan) && count($keterangan)>0):
            foreach($keterangan as $i => $v) {
                $c = [
                    'kode_anggaran' => $ckode_anggaran,
                    'keterangan_anggaran' => $anggaran->keterangan,
                    'tahun'  => $anggaran->tahun_anggaran,
                    'kode_cabang' => $kode_cabang,
                    'cabang' => $cabang->nama_cabang,
                    'username' => user('username'),
                    'nomor_kegiatan' => '',
                    'nama_kegiatan' => $keterangan[$i],
                ];

                $cek        = get_data('tbl_rencana_kpromosi',[
                    'where'         => [
                        'kode_anggaran'   => $ckode_anggaran,
                        'kode_cabang'     => $kode_cabang,
                        'tahun'           => $tahun,
                        'nomor_kegiatan' => '',  
                        'id' => $dt_id[$i],
                        ],
                ])->row();

                $c['id'] = '';
                if($cek):
                    $c['id'] = $cek->id;
                endif;
                $res = save_data('tbl_rencana_kpromosi',$c);
                array_push($arrID,$res['id']);
            }
        endif;

        if(post('id')):
            if(count($arrID)>0):
                delete_data('tbl_rencana_kpromosi',[
                    'kode_anggaran' => $ckode_anggaran,
                    'kode_cabang'=>$kode_cabang,
                    'tahun'=>$anggaran->tahun_anggaran,
                    'id not' => $arrID
                ]);
            endif;   
        endif;
        render([
            'status'    => 'success',
            'message'   => lang('data_berhasil_disimpan')
        ],'json');
    }

    function delete_perubahan(){
        $response = destroy_data('tbl_rencana_kpromosi',[
            'kode_cabang'   => post('kode_cabang'),
            'kode_anggaran' => post('kode_anggaran'),
            'id'            => post('id')
        ]);
        $response['load'] = 'refreshData';
        render($response,'json');
    }

}