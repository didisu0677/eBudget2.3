<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Usulan_kantor_rekap extends BE_Controller {

	function __construct() {
		parent::__construct();
	}

	function index() {
		$kode_anggaran = user('kode_anggaran');
		$cabang = get_data('tbl_m_cabang','kode_cabang',user('kode_cabang'))->row_array();
		
		$relokasi 	= '';
		$kcp 		= '';
		if(isset($cabang['id'])):
			if('cabang pembantu' == strtolower($cabang['struktur_cabang'])):
                $relokasi 	= 'relokasi';
                $kcp 		= 'kcp';
            endif;
		endif;

		$a  						= get_access('usulan_kantor_rekap');
		$data = [];
		if($a['access_additional']):
			$data['cabang'] = get_data('tbl_m_cabang a',[
	            'select'    => 'distinct a.kode_cabang,a.nama_cabang,level_cabang',
	            'where'     => "a.kode_anggaran = '".user('kode_anggaran')."' and a.is_active = 1 and (a.nama_cabang not like '%divisi%' or a.kode_cabang = '00100')",
	            'order_by' => 'a.urutan'
	        ])->result_array();
		elseif(in_array(strtolower($cabang['struktur_cabang']),['cabang induk'])):
			$kordinator = get_data('tbl_m_cabang',[
				'select' => 'kode_cabang,nama_cabang',
				'where' => [
					'kode_anggaran' => $kode_anggaran,
					'kode_cabang'	=> 'k'.$cabang['kode_cabang'],
					'is_active' 	=> 1,
				]
			])->row_array();
			$data   = data_cabang('usulan_kantor');
			if(isset($kordinator['kode_cabang'])):
				$ls_cabang = array_merge([array('kode_cabang' => $kordinator['kode_cabang'],'nama_cabang' => $kordinator['nama_cabang'])],$data['cabang']);
				$data['cabang'] = $ls_cabang;
			endif;
		else:
			$data = data_cabang('usulan_kantor');
		endif;

		$data['tahun'] 				= get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->result();
        $data['access_additional']  = $a['access_additional'];
        $data['tahapan']			= get_data('tbl_tahapan_pengembangan','is_active',1)->result_array();
        $data['jenis_kantor']		= get_data('tbl_kategori_kantor','is_active',1)->result_array();
        $data['jenis_kantor_ket']	= get_data('tbl_kategori_kantor_keterangan','is_active',1)->result_array();
        $data['status_kantor']		= get_data('tbl_status_ket_kantor','is_active',1)->result_array();
        
        // tbl_status_jaringan_kantor
		$where_renc = ['is_active' => 1];
		if($relokasi):
			$where_renc['status_jaringan like'] = $relokasi;
		endif;
		$data['rencana']		= get_data('tbl_status_jaringan_kantor',['where' => $where_renc])->result_array();

		// jenis_kantor
		$where = ['is_active' => 1];
		if($relokasi):
			$where['kategori like'] = $kcp;
		endif;
		$data['jenis_kantor']		= get_data('tbl_kategori_kantor',['where' => $where])->result_array();

		render($data);
	}

	function data(){
		$a  			= get_access('usulan_kantor_rekap');
		$kode_anggaran 	= post('kode_anggaran');
		$length_cabang 	= post('length_cabang');
		$cabang 		= post('cabang');
		$rencana 		= post('rencana');
		$tahapan 		= post('tahapan');
		$jenis_kantor 	= post('jenis_kantor');
		$keterangan 	= post('keterangan');
		$status_kantor 	= post('status_kantor');
		$export 		= post('export');

		$arr_kode_cabang = [];
		$dt_cabang = [];

		$anggaran = get_data('tbl_tahun_anggaran','kode_anggaran',$kode_anggaran)->row();

		if($cabang):
			$arr_kode_cabang = get_arr_cabang_level($kode_anggaran,$cabang);
		elseif($length_cabang<=0):
			$dt_cabang = get_data('tbl_m_cabang',[
				'where' => [
					'kode_cabang' => user('kode_cabang'),
					'kode_anggaran' => $kode_anggaran,
				]
			])->row_array();
		elseif(!$a['access_additional']):
			$dt_cabang = get_data('tbl_m_cabang',[
				'where' => [
					'kode_cabang' => user('kode_cabang'),
					'kode_anggaran' => $kode_anggaran,
				]
			])->row_array();
		endif;
		if(isset($dt_cabang['id'])):
			$arr_kode_cabang[] = $dt_cabang['kode_cabang'];
			if('cabang induk' == strtolower($dt_cabang['struktur_cabang'])):
				$kordinator = get_data('tbl_m_cabang',[
					'select' => 'kode_cabang,nama_cabang',
					'where' => [
						'kode_anggaran' => $kode_anggaran,
						'kode_cabang'	=> 'k'.$dt_cabang['kode_cabang'],
						'is_active' 	=> 1,
					]
				])->row_array();
				if(isset($kordinator['kode_cabang'])):
					$arr_kode_cabang = get_arr_cabang_level($kode_anggaran,$kordinator['kode_cabang']);
				else:
					$dt_capem = get_data('tbl_m_cabang','parent_id',$dt_cabang['parent_id'])->result();
					foreach ($dt_capem as $k => $v) {
						if(!in_array($v->kode_cabang, $arr_kode_cabang)) array_push($arr_kode_cabang, $v->kode_cabang);
					}
				endif;
			endif;

		endif;

		$where['a.kode_anggaran'] = $kode_anggaran;
		if(count($arr_kode_cabang)>0):
			$where['a.kode_cabang'] = $arr_kode_cabang;
		endif;
		if($rencana) $where['a.id_rencana'] = $rencana;
		if($tahapan) $where['a.id_tahapan'] = $tahapan;
		if($jenis_kantor) $where['a.id_kategori_kantor'] = $jenis_kantor;
		if($keterangan) $where['a.id_keterangan'] = $keterangan;
		if($status_kantor) $where['a.id_status_kantor'] = $status_kantor;

		$list = get_data('tbl_rencana_pjaringan a',[
			'select' 	=> '
				a.*,
				b.name as provinsi,c.name as kota,d.name as kecamatan,e.nama as nama_keterangan,e.warna as warna_keterangan,
				f.nama_cabang,
			',
			'where' 	=> $where,
			'join'  	=> [
				'provinsi b on b.id = a.id_provinsi type left',
				'kota c on c.id = a.id_kota type left',
				'kecamatan d on d.id = a.id_kecamatan type left',
				'tbl_kategori_kantor_keterangan e on e.id = a.id_keterangan type left',
				'tbl_m_cabang f on f.kode_cabang = a.kode_cabang and f.kode_anggaran = a.kode_anggaran type left',
			],
			'order_by' => 'f.urutan,a.id'
		])->result_array();

		if($export):
			ini_set('memory_limit', '-1');
			$header = [
				lang('no'),
				lang('rencana'),
				lang('tahapan'),
				lang('jenis_kantor'),
				lang('nama_kantor'),
				lang('cabang_induk'),
				lang('cabang'),
				lang('jadwal'),
				lang('kecamatan'),
				'Kota/Kabupaten',
				'Provinsi',
				lang('status'),
				lang('biaya_perkiraan').' ('.get_view_report().')',
				lang('penjelasan'),
				lang('keterangan')];
			$data = [];
			foreach ($list as $k => $v) {
				$h = [
					($k+1),
					$v['rencana_jarkan'],
					$v['tahapan_pengembangan'],
					$v['kategori_kantor'],
					$v['nama_kantor'],
					$v['cabang_induk'],
					$v['nama_cabang'],
					month_lang($v['jadwal']),
					$v['kecamatan'],
					$v['kota'],
					$v['provinsi'],
					$v['status_ket_kantor'],
					view_report($v['harga']),
					$v['penjelasan'],
					$v['nama_keterangan']
				];
				$data[] = $h;
			}

			$config[] = [
	            'title' => 'Rekap Jaringan Kantor',
	            'header' => $header,
	            'data'  => $data,
	        ];
	         $this->load->library('simpleexcel',$config);
	         $filename = 'rekap_jaringan_kantor_'.str_replace(' ', '_', $anggaran->keterangan).date('YmdHis');
	        $this->simpleexcel->filename($filename);
	        $this->simpleexcel->export();
		else:
			$data['data'] = $list;
			$response	= array(
	            'table'		=> $this->load->view('transaction/usulan_kantor_rekap/table',$data,true),
	        );
			render($response,'json');
		endif;
	}

}