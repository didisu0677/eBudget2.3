<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Monthly_performance_operasional extends BE_Controller {
    var $path = 'transaction/monthly_performance_operasional/';
    var $controller = 'monthly_performance_operasional';
    var $cabang_gab = [];
    var $detail_tahun;
    var $anggaran;
    var $kode_anggaran;
    var $data_cab = [];
    var $data_item = [];
    function __construct() {
        parent::__construct();
        $this->kode_anggaran  = user('kode_anggaran');
        $this->anggaran       = get_data('tbl_tahun_anggaran','kode_anggaran',$this->kode_anggaran)->result();
        $this->detail_tahun   = get_data('tbl_detail_tahun_anggaran a',[
            'select'    => 'a.bulan,a.tahun,a.sumber_data,b.singkatan',
            'join'      => 'tbl_m_data_budget b on b.id = a.sumber_data',
            'where'     => [
                'a.kode_anggaran' => $this->kode_anggaran,
                'a.tahun'         => $this->anggaran[0]->tahun_anggaran
            ],
            'order_by' => 'tahun,bulan'
        ])->result();
    }

    function index() {
        $kode_anggaran          = $this->kode_anggaran;
        $data['tahun']          = $this->anggaran;
        $data['controller']     = $this->controller;
        $data['cabang']         = get_data('tbl_m_cabang a',[
            'select'    => 'distinct a.kode_cabang,a.nama_cabang,level_cabang',
            'where'     => "a.kode_anggaran = '".$kode_anggaran."' and a.is_active = 1 and (status_group = 1 or a.parent_id = 0) and (a.nama_cabang not like '%divisi%' or a.kode_cabang = '00100')"
        ])->result_array();
        $data['nilai']      = get_data('tbl_m_monly_performance_nilai',[
            'where' => [
                'is_active' => 1,
                'kode_anggaran' => user('kode_anggaran')
            ],
            'order_by' => 'nama'
        ])->result_array();
        $data['nilai_pert'] = get_data('tbl_m_monly_performance_nilai_pert',[
            'where' => [
                'is_active' => 1,
                'kode_anggaran' => user('kode_anggaran')
            ],
            'order_by' => 'nama'
        ])->result_array();
        $data['nilai_total'] = get_data('tbl_m_monly_performance_nilai_total',[
            'where' => [
                'is_active' => 1,
                'kode_anggaran' => user('kode_anggaran')
            ]
        ])->result_array();
        $data['bulan']      = $this->arr_bulan();
        $data['coa']        = $this->arr_coa();
        $data['item']       = $this->data_item;
        $data['arr_struktur']       = $this->arr_struktur_cabang();

        render($data);
    }

    private function arr_bulan(){
        $data = [];
        for ($i=1; $i <= 12 ; $i++) { 
            $data[] = [
                'value' => $i,
                'name'  => month_lang($i)
            ];
        }
        return $data;
    }

    private function arr_coa(){
        $coa = get_data('tbl_m_monly_performance_item a',[
            'select' => 'a.coa,a.grup,b.glwdes,b.kali_minus',
            'where'  => [
                'a.is_active'       => 1,
                'a.kode_anggaran'   => user('kode_anggaran')
            ],
            'join'   => "tbl_m_coa b on a.coa = b.glwnco and b.kode_anggaran = '".user('kode_anggaran')."'",
            'order_by' => 'a.urutan',
        ])->result();
        $this->data_item = $coa;

        $data = ['All'];
        $arr_group  = [];
        foreach($coa as $v){
            $arr_group[$v->grup][] = $v;
        }
        foreach($arr_group as $k => $v){
            foreach($v as $coa){
                if(!in_array(remove_spaces($coa->glwdes),$data)) array_push($data,remove_spaces($coa->glwdes));
            }
            if($k && count($v)>1):
                if(!in_array('Total '.$k,$data)) array_push($data,'Total '.$k);
            endif;
        }
        $data[] = 'Total Nilai';
        return $data;
    }

    function arr_struktur_cabang(){
        $ls = get_data('tbl_m_struktur_cabang',[
            'select'    => 'distinct struktur_cabang as nama',
            'where'     => [
                'is_active' => 1
            ],
            'order_by' => 'nama'
        ])->result();
        $data[] = ['nama' => 'All','value' => 'All'];
        foreach($ls as $k => $v){
            $val = str_replace(' ','',$v->nama);
            $data[] = ['nama' => $v->nama,'value' => 'd-struktur-'.$val];
        }
        return $data;
    }

    function ranking(){
        $dt = $this->session->ranking;
        $rankings = array();
        foreach($dt as $coa => $v){
            foreach($v as $cabang => $v2){
                $standings = $v2;
                arsort($standings);
                $rank = 0;
                foreach ($standings as $name => $score) {
                    $rank++;
                    $dt[$coa][$cabang][$name]['score'] = $rank;
                    $rankings['rank-'.$coa.$name] = $rank;
                }
            }
        }
        $this->session->unset_userdata(['ranking']);
        return $rankings;
    }

    function data($kode_anggaran,$kode_cabang,$bulan){
        $anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();

        $coa = get_data('tbl_m_monly_performance_item a',[
            'select' => 'a.coa,a.grup,b.glwdes,b.kali_minus',
            'where'  => [
                'a.is_active'       => 1,
                'a.kode_anggaran'   => $kode_anggaran
            ],
            'join'   => "tbl_m_coa b on a.coa = b.glwnco and b.kode_anggaran = '".$anggaran->kode_anggaran."'",
            'order_by' => 'a.urutan',
        ])->result();

        $ck_coa     = post('ck_coa');
        $arr_group  = [];
        $arr_coa    = [];
        foreach ($coa as $k => $v) {
            $arr_group[$v->grup][] = $v;
            if(!in_array($v->coa,$arr_coa)) array_push($arr_coa,$v->coa);
        }

        if(count($arr_coa)<=0):
            render([
                'success' => false,
                'message' => lang('data_not_found')
            ],'json');exit();
        endif;

        if($kode_cabang == 'konsolidasi'):
            $cabang   = [
                'kode_cabang'   => 'konsolidasi',
                'nama_cabang'   => 'KONSOLIDASI',
                'struktur_cabang' => 'Kantor Pusat',
            ];
            $cabang = json_encode($cabang); 
            $cabang = json_decode($cabang);
            $x = get_data('tbl_m_cabang',[
                'select'    => 'kode_cabang,nama_cabang,struktur_cabang',
                'where'     => "parent_id = 0 and is_active = 1 and kode_anggaran = '".$anggaran->kode_anggaran."'",
                'order_by'  => "urutan"
            ])->result_array();
            $cab = [
                array('kode_cabang' => 'KONS', 'nama_cabang' => 'KONSOLIDASI', 'struktur_cabang' => 'Kantor Pusat'),
                array('kode_cabang' => 'KONV', 'nama_cabang' => 'KONVENSIONAL', 'struktur_cabang' => 'Kantor Pusat'),
            ];
            foreach ($x as $k => $v) {
                $cab[] = $v;
            }
            foreach ($cab as $k => $v) {
                $field  = 'B_' . sprintf("%02d", $bulan);
                $x2 = get_data('tbl_budget_nett_neraca',[
                    'select' => "kode_cabang,'".$v['nama_cabang']."' as nama_cabang,coa,".$field,
                    'where'  => [
                        'coa'           => $arr_coa,
                        'kode_cabang'   => $v['kode_cabang'],
                    ]
                ])->result_array();

                $x2_laba = get_data('tbl_budget_nett_labarugi',[
                    'select' => "kode_cabang,'".$v['nama_cabang']."' as nama_cabang,coa,".$field,
                    'where'  => [
                        'coa'           => $arr_coa,
                        'kode_cabang'   => $v['kode_cabang'],
                    ]
                ])->result_array();

                $x2 = array_merge($x2,$x2_laba);

                $this->data_cab['cabang'][$v['kode_cabang']]['nama_cabang'] = $v['nama_cabang'];
                $this->data_cab['cabang'][$v['kode_cabang']]['kode_cabang'] = $v['kode_cabang'];
                $this->data_cab['cabang'][$v['kode_cabang']]['struktur_cabang'] = $v['struktur_cabang'];
                $this->data_cab['cabang'][$v['kode_cabang']]['data'][] = $x2;
            }
        else:
            $this->more_cabang(0,$kode_cabang,$arr_coa,$anggaran,$bulan,0);
            $cabang = $this->data_cab['cabang'][$kode_cabang];
            $cabang = json_encode($cabang); 
            $cabang = json_decode($cabang);
        endif;

        $tbl_history = 'tbl_history_'.($anggaran->tahun_anggaran-1);
        $history     = [];
        if($this->db->table_exists($tbl_history)):
            $history = get_data($tbl_history.' a',[
                'join'  => "tbl_m_coa b on b.glwnco = a.glwnco and b.kode_anggaran = '".$anggaran->kode_anggaran."'",
                'where' => [
                    'a.bulan'     => $bulan,
                    'a.glwnco'    => $arr_coa,
                ]
            ])->result_array();
        endif;
        $tbl_history_current = 'tbl_history_'.($anggaran->tahun_anggaran);
        $history_current     = [];
        if($this->db->table_exists($tbl_history_current)):
            $history_current = get_data($tbl_history_current.' a',[
                'join'  => "tbl_m_coa b on b.glwnco = a.glwnco and b.kode_anggaran = '".$anggaran->kode_anggaran."'",
                'where' => [
                    'a.bulan'     => $bulan,
                    'a.glwnco'    => $arr_coa,
                ]
            ])->result_array();
        endif;

        $data['cab']         = $this->data_cab;
        $data['ck_coa']      = $ck_coa;
        $data['group']       = $arr_group;
        $data['bulan']       = $bulan;
        $data['anggaran']    = $anggaran;
        $data['cabang']      = $cabang;
        $data['history_current'] = $history_current;
        $data['history']    = $history;
        $data['nilai']      = get_data('tbl_m_monly_performance_nilai',[
            'where' => [
                'is_active' => 1,
                'kode_anggaran' => $anggaran->kode_anggaran
            ]
        ])->result_array();
        $data['nilai_pert'] = get_data('tbl_m_monly_performance_nilai_pert',[
            'where' => [
                'is_active' => 1,
                'kode_anggaran' => $anggaran->kode_anggaran
            ]
        ])->result_array();
        $data['nilai_total']= get_data('tbl_m_monly_performance_nilai_total',[
            'where' => [
                'is_active' => 1,
                'kode_anggaran' => $anggaran->kode_anggaran
            ]
        ])->result_array();

        // pencapaian
        $arrNamaPenc = [];
        foreach($data['nilai'] as $v){
            if(!in_array($v['nama'],$arrNamaPenc)):
                array_push($arrNamaPenc,$v['nama']);
            endif;
        }

        // pertumbuhan
        $arrNamaPert = [];
        foreach($data['nilai_pert'] as $v){
            if(!in_array($v['nama'],$arrNamaPert)):
                array_push($arrNamaPert,$v['nama']);
            endif;
        }
        $data['arrNamaPenc'] = $arrNamaPenc;
        $data['arrNamaPert'] = $arrNamaPert;

        $view     = $this->load->view($this->path.'/content',$data,true);

        foreach($ck_coa as $k => $v){
            $string = htmlentities(remove_spaces($v), null, 'utf-8');
            $string = str_replace('&nbsp;','',$string);
            $string = str_replace(' ','',$string);

            $ck_coa[$k] = $string;
        }

        render([
            'status'    => true,
            'view'      => $view,
            'rank'      => $this->ranking(),
            'ck_coa'    => $ck_coa
        ],'json');
    }

    private function more_cabang($parent_id,$kode_cabang,$arr_coa,$anggaran,$bulan,$key){
        $field  = 'b.B_' . sprintf("%02d", $bulan);
        if($key == 0):
            $x = get_data('tbl_m_cabang a',[
                'select'    => 'a.parent_id,a.id,a.kode_cabang,a.nama_cabang,a.struktur_cabang,b.coa,'.$field,
                'join'      => [
                    "tbl_budget_nett_neraca b on b.kode_cabang = a.kode_cabang and b.kode_anggaran = '$anggaran->kode_anggaran' and b.coa in (".implode(",", $arr_coa).") type left",
                    "tbl_m_coa c on c.glwnco = b.coa and c.kode_anggaran = a.kode_anggaran and c.tipe = 1 and c.glwnco != ''"
                ],
                'where'     => [
                    'a.kode_cabang'   => $kode_cabang,
                    'a.is_active'   => 1,
                    'a.kode_anggaran' => $anggaran->kode_anggaran
                ],
                'order_by'  => "a.urutan"
            ])->result_array();

            $x_laba = get_data('tbl_m_cabang a',[
                'select'    => 'a.parent_id,a.id,a.kode_cabang,a.nama_cabang,a.struktur_cabang,b.coa,'.$field,
                'join'      => [
                    "tbl_budget_nett_labarugi b on b.kode_cabang = a.kode_cabang and b.kode_anggaran = '$anggaran->kode_anggaran' and b.coa in (".implode(",", $arr_coa).") type left",
                    "tbl_m_coa c on c.glwnco = b.coa and c.kode_anggaran = a.kode_anggaran and c.tipe = 2 and c.glwnco != ''"
                ],
                'where'     => [
                    'a.kode_cabang'   => $kode_cabang,
                    'a.is_active'   => 1,
                    'a.kode_anggaran' => $anggaran->kode_anggaran
                ],
                'order_by'  => "a.urutan"
            ])->result_array();
            $x = array_merge($x,$x_laba);
        else:
            $cabang = get_data('tbl_m_cabang',[
                'select' => 'id,kode_cabang,nama_cabang,struktur_cabang',
                'where'  => [
                    'is_active' => 1,
                    'parent_id' => $parent_id,
                    'kode_anggaran' => $anggaran->kode_anggaran
                ],
                'order_by'  => "urutan"
            ])->result_array();
        endif;

        if($key == 0):
            foreach ($x as $k => $v) {
                $kode_cabang2 = $v['kode_cabang'];
                $this->data_cab['cabang'][$kode_cabang]['nama_cabang'] = $v['nama_cabang'];
                $this->data_cab['cabang'][$kode_cabang]['kode_cabang'] = $v['kode_cabang'];
                $this->data_cab['cabang'][$kode_cabang]['struktur_cabang'] = $v['struktur_cabang'];
                $this->data_cab['cabang'][$kode_cabang]['data'] = $x;
                $x2 = $this->more_cabang($v['id'],$v['kode_cabang'],$arr_coa,$anggaran,$bulan,1);
                break;
            }
        else:
            foreach ($cabang as $k => $v) {
                $kode_cabang2 = $v['kode_cabang'];
                $x = get_data('tbl_m_cabang a',[
                    'select'    => 'a.parent_id,a.id,a.kode_cabang,a.nama_cabang,b.coa,'.$field,
                    'join'      => [
                        "tbl_budget_nett_neraca b on b.kode_cabang = a.kode_cabang and b.kode_anggaran = '$anggaran->kode_anggaran' and b.coa in (".implode(",", $arr_coa).") type left",
                        "tbl_m_coa c on c.glwnco = b.coa and c.kode_anggaran = a.kode_anggaran and c.tipe = 1 and c.glwnco != ''"
                    ],
                    'where'     => [
                        'a.kode_cabang'   => $kode_cabang2,
                        'a.is_active'   => 1,
                        'a.kode_anggaran' => $anggaran->kode_anggaran
                    ],
                    'order_by'  => "a.urutan"
                ])->result_array();

                $x_laba = get_data('tbl_m_cabang a',[
                    'select'    => 'a.parent_id,a.id,a.kode_cabang,a.nama_cabang,b.coa,'.$field,
                    'join'      => [
                        "tbl_budget_nett_labarugi b on b.kode_cabang = a.kode_cabang and b.kode_anggaran = '$anggaran->kode_anggaran' and b.coa in (".implode(",", $arr_coa).") type left",
                        "tbl_m_coa c on c.glwnco = b.coa and c.kode_anggaran = a.kode_anggaran and c.tipe = 2 and c.glwnco != ''"
                    ],
                    'where'     => [
                        'a.kode_cabang'   => $kode_cabang2,
                        'a.is_active'   => 1,
                        'a.kode_anggaran' => $anggaran->kode_anggaran
                    ],
                    'order_by'  => "a.urutan"
                ])->result_array();

                $x = array_merge($x,$x_laba);
                
                $this->data_cab[$kode_cabang][$kode_cabang2]['nama_cabang'] = $v['nama_cabang'];
                $this->data_cab[$kode_cabang][$kode_cabang2]['kode_cabang'] = $v['kode_cabang'];
                $this->data_cab[$kode_cabang][$kode_cabang2]['struktur_cabang'] = $v['struktur_cabang'];
                $this->data_cab[$kode_cabang][$kode_cabang2]['data'] = $x;
                $x2 = $this->more_cabang($v['id'],$v['kode_cabang'],$arr_coa,$anggaran,$bulan,1);
            }
        endif;
        

    }

}