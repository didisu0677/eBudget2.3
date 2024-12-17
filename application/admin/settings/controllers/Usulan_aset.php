<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Usulan_aset extends BE_Controller {
    var $path       = 'transaction/';
    var $controller = 'usulan_aset';
    var $detail_tahun;
    var $kode_anggaran;
    var $arrID = [];
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
    
    function index($p1="") { 
        $a = get_access('usulan_aset');
        $data = data_cabang();// nama url
        $data['access_additional'] = $a['access_additional'];
        $data['opt_grup']  = get_data('tbl_grup_asetinventaris',[
            'where' => [
                'is_active' => 1,
                'kode' => ['E.1','E.2','E.3','E.6'],
                ],
            'order_by' => 'kode',
        ])->result_array();

        $data['opt_inv1']  = get_data('tbl_kode_inventaris',[
            'where' => [
                'is_active' => 1,
                'grup'      => 'E.4'
            ],
            'order_by'  => 'kode_inventaris',
        ])->result_array();
        $data['opt_inv2']  = get_data('tbl_kode_inventaris',[
            'where' => [
                'is_active' => 1,
                'grup'      => 'E.5'
            ],
            'order_by'  => 'kode_inventaris',
        ])->result_array();
        $data['opt_inv3']  = get_data('tbl_kode_inventaris',[
            'where' => [
                'is_active' => 1,
                'grup'      => 'E.7'
            ],
            'order_by'  => 'kode_inventaris',
        ])->result_array();
        $data['path']     = $this->path;
        $data['detail_tahun']    = $this->detail_tahun;
        render($data,'view:'.$this->path.'usulan_aset/index');
    }

    function data($anggaran="", $cabang="", $tipe = 'table') {
        $ckode_anggaran = $anggaran;
        $ckode_cabang = $cabang;
        
        $data_finish['kode_anggaran']   = $ckode_anggaran;
        $data_finish['kode_cabang']     = $ckode_cabang;
        $a = get_access('usulan_aset',$data_finish);
        $access_edit = false;
        if($a['access_edit'] && $cabang == user('kode_cabang')):
            $access_edit = true;
        elseif($a['access_edit'] && $a['access_additional']):
            $access_edit = true;
        endif;
        $data['akses_ubah'] = $access_edit;

        $data['current_cabang'] = $cabang;

        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$ckode_anggaran)->row();

        $cab = get_data('tbl_m_cabang',[
            'where' => [
                'kode_cabang'   => $ckode_cabang,
                'kode_anggaran' => $anggaran->kode_anggaran
            ]
        ])->row();

        // pengecekan akses cabang
        if(!$anggaran):
            render(['status' => false,'message' => 'anggaran not found'],'json');exit();
        elseif(!$cab):
            render(['status' => false,'message' => 'cabang not found'],'json');exit();
        endif;
        check_access_cabang($this->controller,$ckode_anggaran,$ckode_cabang,$a);

        $status_capem   = false;
        $harga_relokasi = 0;
        if($cab):
            if(in_array(strtolower($cab->struktur_cabang),[strtolower('Cabang Pembantu'),strtolower('Cabang Induk')])):
                $where_relokasi = [
                    'kode_anggaran' => $anggaran->kode_anggaran,
                    'kode_cabang'   => $ckode_cabang,
                    'rencana_jarkan' => 'relokasi'
                ];
                $cabang_induk_txt = '';
                if(strtolower($cab->struktur_cabang) == strtolower('Cabang Induk')):
                    $cabang_induk_txt = " and kategori_kantor = 'kc'";
                    $where_relokasi['kategori_kantor'] = 'kc';
                endif;
                $ck_relokasi = $this->db->count_all("tbl_rencana_pjaringan where kode_anggaran = '".$anggaran->kode_anggaran."' and kode_cabang = '".$ckode_cabang."' and rencana_jarkan = 'relokasi'".$cabang_induk_txt);
                if($ck_relokasi>0):
                    $row_relokasi = get_data('tbl_rencana_pjaringan',[
                        'select'    => 'sum(ifnull(harga,0)) as harga',
                        'where'     => $where_relokasi,
                    ])->row();
                    if($row_relokasi):
                        $harga_relokasi = $row_relokasi->harga;
                    endif;
                    $status_capem = true;
                endif;
            endif;
        endif;
        $data['status_capem']   = $status_capem;
        $data['harga_relokasi'] = $harga_relokasi;
        $data['cab'] = $cab;
                  
        $arr            = [
            'select'    => 'a.*',
            'where'     => [
                'a.is_active' => 1,
            ],
            'sort_by'   => 'a.kode',
        ];
        
    
        $data['grup'][0]= get_data('tbl_grup_asetinventaris a',$arr)->result();
        

        foreach($data['grup'][0] as $m0) {         

            $arr            = [
                'select'    => 'a.*',
                'where'     => [
                    'a.grup' => $m0->kode,
                ],
            ];
            
            if($anggaran) {
                $arr['where']['a.kode_anggaran']  = $ckode_anggaran;
            }
            
            if($cabang) {
                $arr['where']['a.kode_cabang']  = $ckode_cabang;
            }

            $produk     = get_data('tbl_rencana_aset a',$arr)->result();

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
                    'kode_inventaris' => $m1->kode_inventaris,
                    'nama_inventaris' => $m1->nama_inventaris,
                    'grup'      => $m1->grup,
                    'nama_grup' => $m1->nama_grup,
                );

                $cek        = get_data('tbl_rencana_aset',[
                    'where'         => [
                        'kode_anggaran'   => $ckode_anggaran,  
                        'kode_cabang'     => $ckode_cabang,
                        'tahun'           => $anggaran->tahun_anggaran,
                        'kode_inventaris' => $m1->kode_inventaris,  
                        'grup'            => $m1->grup,
                        ],
                ])->row();
                
                if(!isset($cek->id)) {
                    $response =             insert_data('tbl_rencana_aset',$data2);
                }
            }      

            $arr            = [
                'select'    => 'a.*',
                'where'     => [
                    'a.grup' => $m0->kode,
                ],
            ];

            if($anggaran) {
                $arr['where']['a.kode_anggaran']  = $ckode_anggaran;
            }
            
            if($cabang) {
                $arr['where']['a.kode_cabang']  = $ckode_cabang;
            }

            
            $data['produk'][$m0->kode]  = get_data('tbl_rencana_aset a',$arr)->result();     
                        
        }

        $status_inventaris = get_data('tbl_rencana_aset_status a',[
            'select' => 'b.nama,a.kode_inventaris',
            'join'   => ["tbl_m_keterangan_inventaris b on b.id = a.id_keterangan_inventaris type left"],
            'where'  => [
                'kode_anggaran'     => $ckode_anggaran,
            ]
        ])->result_array();
   
        $data['cabang_user']        = user('kode_cabang');
        $data['page']               = "inv";
        $data['status_inventaris']  = $status_inventaris;

        $data2 = $data;
        $data2['page'] = 'aset_sewa';

        $response   = array(
            'status'     => true,
            'table'      => $this->load->view($this->path.'usulan_aset/table',$data,true),
            'table_sewa' => $this->load->view($this->path.'usulan_aset/table_sewa',$data2,true),
            'edit'       => $access_edit,
            'autorun'    => call_autorun($ckode_anggaran,$ckode_cabang,'inv'),
        );
       
        render($response,'json');
    }

    function getKodeInventaris(){
        $get = get_data('tbl_rencana_aset a',[
            'select' => 'a.kode_inventaris',
            'where' => [
                'kode_cabang'   => user("kode_cabang"),
                'kode_inventaris like' => 'H%'
            ],
            'order_by' => 'id',
            'sort' => 'DESC',
            'limit' => '1'
        ])->result();

        if(!empty($get)){
            $data = $get;
        }else {
            $test['kode_inventaris'] = "H-00";
            $data[] = $test;
        }

        render($data,'json');
    }


    function getKodeInventaris2(){
        $get = get_data('tbl_rencana_aset a',[
            'select' => 'a.kode_inventaris',
            'where' => [
                'kode_cabang'   => user("kode_cabang"),
                'kode_inventaris like' => 'M%'
            ],
            'order_by' => 'id',
            'sort' => 'DESC',
            'limit' => '1'
        ])->result();

        if(!empty($get)){
            $data = $get;
        }else {
            $test['kode_inventaris'] = "M 0";
            $data[] = $test;
        }

        render($data,'json');
    }


    function get_data() {
        $dt = get_data('tbl_rencana_aset','id',post('id'))->row();
        $data = get_data('tbl_rencana_aset','id',post('id'))->row_array();
        
        $data_inv = [];
        $data['class'] = '';
        if(in_array($dt->grup,['E.1','E.2','E.3','E.6'])):
            $data_inv = get_data('tbl_rencana_aset',[
                'where' => [
                'kode_anggaran' => $dt->kode_anggaran,    
                'tahun' => $dt->tahun,
                'kode_cabang' => $dt->kode_cabang,
                'grup' => $dt->grup,
                // 'grup' => ['E.1','E.2','E.3','E.6']
            ],
            ])->result_array();
            $data_inv = $this->convert_data($data_inv);
            $data['class'] = 'd-aset';
        endif;
        $data['detail_ket'] = $data_inv;

        $data_inv1 = [];
        if(in_array($dt->grup,['E.4'])):
            $data_inv1 = get_data('tbl_rencana_aset a',[
                'select' => 'a.*',
                'join' => 'tbl_kode_inventaris b on b.kode_inventaris = a.kode_inventaris',
                'where' => [
                'a.kode_anggaran' => $dt->kode_anggaran, 
                'a.tahun' => $dt->tahun,
                'a.kode_cabang' => $dt->kode_cabang,
                'a.grup' => 'E.4'
            ],
            ])->result_array();
            $data_inv1 = $this->convert_data($data_inv1);
            $data['class'] = 'd-kel1';
        endif;
        $data['detail_invk1'] =  $data_inv1;

        $data_inv2 = [];
        if(in_array($dt->grup,['E.5'])):
            $data_inv2 = get_data('tbl_rencana_aset a',[
                'select' => 'a.*',
                'join' => 'tbl_kode_inventaris b on b.kode_inventaris = a.kode_inventaris',
                'where' => [
                'a.kode_anggaran' => $dt->kode_anggaran, 
                'a.tahun' => $dt->tahun,
                'a.kode_cabang' => $dt->kode_cabang,
                'a.grup' => 'E.5'
            ],
            ])->result_array();
            $data_inv2 = $this->convert_data($data_inv2);
            $data['class'] = 'd-kel2';
        endif;
        $data['detail_invk2'] = $data_inv2;

        $data_inv3 = [];
        if(in_array($dt->grup,['E.7'])):
            $data_inv3 = get_data('tbl_rencana_aset a',[
                'select' => 'a.*',
                'join' => 'tbl_kode_inventaris b on b.kode_inventaris = a.kode_inventaris',
                'where' => [
                'a.kode_anggaran' => $dt->kode_anggaran, 
                'a.tahun' => $dt->tahun,
                'a.kode_cabang' => $dt->kode_cabang,
                'a.grup' => 'E.7'
            ],
            ])->result_array();
            $data_inv3 = $this->convert_data($data_inv3);
            $data['class'] = 'd-aset-sewa';
        endif;
        $data['detail_invk3'] = $data_inv3;

        if(in_array($dt->grup,['E.4'])):
            $data['detail_tambahan1'] = get_data('tbl_rencana_aset a',[
                'select' => 'a.*',
                'join' => 'tbl_kode_inventaris b on b.kode_inventaris = a.kode_inventaris TYPE left',
                'where' => [
                'a.kode_anggaran' => $dt->kode_anggaran, 
                'a.tahun' => $dt->tahun,
                'a.kode_cabang' => $dt->kode_cabang,
                'a.grup' => 'E.4',
                'b.kode_inventaris' => null
            ],
            ])->result_array();
            if(count($data['detail_tambahan1'])>0):
                $data['class'] = 'd-tam1';
            endif;
        endif;

        if(in_array($dt->grup,['E.5'])):
            $data['detail_tambahan2'] = get_data('tbl_rencana_aset',[
                'where' => [
                'kode_anggaran' => $dt->kode_anggaran, 
                'tahun' => $dt->tahun,
                'kode_cabang' => $dt->kode_cabang,
                'grup' => 'E.5',
                'kode_inventaris' => '' 
            ],
            ])->result_array();
            if(count($data['detail_tambahan2'])>0):
                $data['class'] = 'd-tam2';
            endif;
        endif;

        render($data,'json');
    }

    private function convert_data($p1){
        $data = [];
        foreach ($p1 as $k => $v) {
            $v['harga'] = view_report($v['harga']);
            $data[] = $v;
        }
        return $data;
    }

    function save_perubahan() {
        $kode_anggaran  = post('kode_anggaran');
        $kode_cabang    = post('kode_cabang');
        $data   = json_decode(post('json'),true);

        // pengecekan save untuk cabang
        check_save_cabang($this->controller,$kode_anggaran,$kode_cabang,'access_edit');

        foreach($data as $id => $record) {
            if(isset($record['harga'])):
                $record['harga'] = insert_view_report(filter_money($record['harga']));
            elseif(isset($record['jumlah'])):
                $record['jumlah'] = filter_money($record['jumlah']);
            endif;
            update_data('tbl_rencana_aset',$record,'id',$id); 
        }
        create_autorun($kode_anggaran,$kode_cabang,'inv');
        render([
            'status'    => true,
            'message'   => lang('data_berhasil_diperbaharui')
        ],'json');
    }

    function save() {
        $kode_cabang    = post('kode_cabang');
        $ckode_anggaran = user('kode_anggaran');

        // pengecekan save untuk cabang
        check_save_cabang($this->controller,$ckode_anggaran,$kode_cabang);

        $anggaran       = get_data('tbl_tahun_anggaran','kode_anggaran',$ckode_anggaran)->row();
        $cabang         = get_data('tbl_m_cabang',[
            'where' => [
                'kode_anggaran' => $ckode_anggaran,
                'kode_cabang'   => $kode_cabang
            ]
        ])->row();
        $tahun          = $anggaran->tahun_anggaran;

        if(post('id')):
            $dt = get_data('tbl_rencana_aset','id',post('id'))->row();
        endif;

        $dataDefault = [
            'cabang'            => $cabang,
            'kode_cabang'       => $kode_cabang,
            'anggaran'          => $anggaran,
            'tahun'             => $tahun,
        ];

        // ASET DAN INSTALASI BANGUNAN
        $data = $dataDefault;
        $data['kodeinventaris'] = post('kodeinventaris');
        $data['keterangan']     = post('keterangan');
        $data['grup_aset']      = post('grup_aset');
        $data['catatan']        = post('catatan');
        $data['bulan_aset']     = post('bulan_aset');
        $this->pengecekan($data);

        // Inventaris Kel 1
        $data = $dataDefault;
        $data['kodeinventaris'] = post('kel1');
        $data['keterangan']     = post('inv_kel1');
        $data['grup_aset']      = post('grup_aset');
        $data['catatan']        = post('catatanInvKel1');
        $data['bulan_aset']     = post('bulan_kel1');
        $this->pengecekan($data,'grouping');

        // Inventaris Kel 2
        $data = $dataDefault;
        $data['kodeinventaris'] = post('kel2');
        $data['keterangan']     = post('inv_kel2');
        $data['grup_aset']      = post('grup_aset');
        $data['catatan']        = post('catatanInvKel2');
        $data['bulan_aset']     = post('bulan_kel2');
        $this->pengecekan($data,'grouping');

        // Aset Sewa
        $data = $dataDefault;
        $data['kodeinventaris'] = post('kel3');
        $data['keterangan']     = post('inv_kel3');
        $data['grup_aset']      = post('grup_aset');
        $data['catatan']        = post('catatanInvKel3');
        $data['bulan_aset']     = post('bulan_kel3');
        $data['jumlah']         = post('jumlah3');
        $data['harga']          = post('harga3');
        $this->pengecekan($data,'grouping');

        if(post('id') && count($this->arrID)):
            delete_data('tbl_rencana_aset',[
                'kode_anggaran'=>$anggaran->kode_anggaran,
                'kode_cabang'=>$kode_cabang,
                'id not' => $this->arrID,
                'grup'  => $dt->grup,
            ]);  
        elseif(post('id')):
            delete_data('tbl_rencana_aset',[
                'kode_anggaran'=>$anggaran->kode_anggaran,
                'kode_cabang'=>$kode_cabang,
                'grup'  => $dt->grup,
            ]); 
        endif;

        create_autorun($anggaran->kode_anggaran,$kode_cabang,'inv');

        render([
            'status'    => 'success',
            'message'   => lang('data_berhasil_disimpan')
        ],'json');
    }

    private function pengecekan($data,$page=""){

        $kode_cabang    = $data['kode_cabang'];
        $cabang         = $data['cabang'];
        $anggaran       = $data['anggaran'];
        $tahun          = $data['tahun'];

        $kodeinventaris = $data['kodeinventaris'];
        $keterangan     = $data['keterangan'];
        $grup_aset      = $data['grup_aset'];
        $catatan        = $data['catatan'];
        $bulan_aset     = $data['bulan_aset'];

        foreach ($keterangan as $k => $v) {

            if($v):
                $dataWhere['kode_cabang']    = $kode_cabang;
                $dataWhere['kode_anggaran']  = $anggaran->kode_anggaran;

                $dataSave = $dataWhere;
                $dataSave['cabang']              = $cabang->nama_cabang;
                $dataSave['keterangan_anggaran'] = $anggaran->keterangan;
                $dataSave['tahun']               = $tahun;
                $dataSave['nama_inventaris']     = $v;
                $dataSave['catatan']             = $catatan[$k];
                $dataSave['bulan']               = $bulan_aset[$k];
                $dataSave['is_active']           = 1;
                $prefiks = '';

                // jika grouping hanya bisa input satu keterangan dalam satu tahun berdasarkan tbl_kode_inventaris yang terdaftar
                if($page == 'grouping'):
                    $dt_kode_inv = get_data('tbl_kode_inventaris','kode_inventaris',$v)->row_array();
                    $dataSave['nama_inventaris'] = $dt_kode_inv['nama_inventaris'];
                    $dataSave['kode_inventaris'] = $dt_kode_inv['kode_inventaris'];
                    $dataSave['grup']            = $dt_kode_inv['grup'];
                    $dataSave['nama_grup']       = $dt_kode_inv['nama_grup_aset'];
                    $dataSave['harga']           = $dt_kode_inv['harga'];

                    if(isset($data['harga'][$k])):
                        $harga  = insert_view_report(filter_money($data['harga'][$k])); if(!$harga) $harga = 0;
                        $jumlah = filter_money($data['jumlah'][$k]); if(!$jumlah) $jumlah = 0;
                        $dataSave['harga']  = $harga;
                        $dataSave['jumlah'] = $jumlah;
                        $dataSave['total']  = $harga * $jumlah;
                    endif;

                    $where = $dataWhere;
                    $where['kode_inventaris'] = $dt_kode_inv['kode_inventaris'];
                    $ck_id = get_data('tbl_rencana_aset',['select' => 'id,kode_inventaris','where' => $where])->row_array();
                    if($ck_id):
                        $dataSave['id'] = $ck_id['id'];
                    endif;

                else:

                    $dt_grup = get_data('tbl_grup_asetinventaris','kode',$grup_aset[$k])->row_array();
                    if($dt_grup):
                        $prefiks                = $dt_grup['prefiks'];
                        $dataSave['grup']       = $grup_aset[$k];
                        $dataSave['nama_grup']  = $dt_grup['keterangan'];
                    endif;

                    $where = $dataWhere;
                    $where['id'] = $kodeinventaris[$k];
                    $ck_id = get_data('tbl_rencana_aset',['select' => 'id,kode_inventaris','where' => $where])->row_array();
                    $old_kode_inventaris = '';
                    if($ck_id):
                        $old_kode_inventaris = explode(" ", $ck_id['kode_inventaris'])[0];
                    endif;


                    $where = $dataWhere;
                    $where['kode_inventaris like '] = $prefiks.' %';
                    $ck_prefiks = get_data('tbl_rencana_aset',
                        ['select' => 'kode_inventaris','where' => $where,'order_by' => 'kode_inventaris', 'sort' => 'DESC']
                    )->row_array();
                    if($prefiks != $old_kode_inventaris && $ck_prefiks):
                        $count   = explode(" ", $ck_prefiks['kode_inventaris']);
                        $dataSave['kode_inventaris'] = $prefiks.' '.($count[1]+1);
                    elseif($prefiks != $old_kode_inventaris):
                        $dataSave['kode_inventaris'] = $prefiks.' 1';
                    endif;

                    if($ck_id):
                        $dataSave['id'] = $ck_id['id'];
                    endif;
                endif;

                $ID = save_data('tbl_rencana_aset',$dataSave);
                if(!in_array($ID['id'],$this->arrID)):
                    array_push($this->arrID, $ID['id']);
                endif;
            endif;
        }
    }

    function delete(){
        $ck = get_data('tbl_rencana_aset','id',post('id'))->row();
        $access = get_access('usulan_aset');
        $edit   = false;
        if($ck && $access['access_edit'] && $ck->kode_cabang == user('kode_cabang')):
            $edit = true;
        elseif($ck && $access['access_edit'] && $access['access_additional']):
            $edit = true;
        endif;

        if(!$edit):
            render([
                'success'   => 'warning',
                'message'   => 'Maaf data tidak bisa dihapus'
            ],'json');exit();
        endif;

        $response = destroy_data('tbl_rencana_aset','id',post('id'));
        create_autorun($ck->kode_anggaran,$ck->kode_cabang,'inv');
        render($response,'json');
    }
}