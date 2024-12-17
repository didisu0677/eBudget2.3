<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Finish extends BE_Controller {

	function __construct() {
		parent::__construct();
	}

	function index() {
		$data['tahun'] 	= get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->result();
		$data['cabang']	= $this->cabang();

		$data['menu'][0] = get_data('tbl_menu',array('where_array'=>array('parent_id'=>0,'is_active'=>1),'sort_by'=>'urutan','limit' => 1))->result();
		foreach($data['menu'][0] as $m0) {
			$data['menu'][$m0->id] = get_data('tbl_menu',array('where_array'=>array('parent_id'=>$m0->id,'is_active'=>1),'sort_by'=>'urutan'))->result();
			foreach($data['menu'][$m0->id] as $m1) {
				$data['menu'][$m1->id] = get_data('tbl_menu',array('where_array'=>array('parent_id'=>$m1->id,'is_active'=>1),'sort_by'=>'urutan'))->result();
				foreach($data['menu'][$m1->id] as $m2) {
					$data['menu'][$m2->id] = get_data('tbl_menu',array('where_array'=>array('parent_id'=>$m2->id,'is_active'=>1),'sort_by'=>'urutan'))->result();
				}
			}
		}

		$data['access'] = get_access('finish');
		render($data);
	}

	private function cabang(){
		$cab[] = ['kode_cabang' => 0, 'nama_cabang' => 'All'];
		$x = get_data('tbl_m_cabang',[
			'select' => 'nama_cabang,kode_cabang',
			'where' => [
				'is_active' => 1,
				'kode_anggaran' => user('kode_anggaran')
			],
			'order_by' => 'urutan'
		])->result();
		foreach($x as $v){
			$cab[] = ['kode_cabang' => $v->kode_cabang, 'nama_cabang' => $v->nama_cabang];
		}
		return $cab;
	}

	function menu(){
		$kode_anggaran = post('kode_anggaran');
		$list = get_data('tbl_finish','kode_anggaran',$kode_anggaran)->result();

		$data = '<option value="0">All</option>';
		foreach($list as $v){
			$data .= '<option value="'.$v->id.'">'.$v->nama.'</option>';
		}
		render([
			'data' => $data
		],'json');
	}

	function save(){
		$id_menu	= post('id_menu');
		$view		= post('act_view');
		$response 	= save_data('tbl_finish',post(),post(':validation'));
		if($response['status'] == 'success' && count($id_menu) > 0):
			$arrID = [];
			foreach($id_menu as $m) {
				if(isset($view[$m])):
					$data = [
						'id_finish' => $response['id'],
						'id_menu' 	=> $m,
					];
					$check = get_data('tbl_finish_detail',array('where_array'=>array('id_menu'=>$m,'id_finish'=>$data['id_finish'])))->row();
					if($check):
						$data['id'] = $check->id;
					else:
						$data['id'] = '';
					endif;
					$res = save_data('tbl_finish_detail',$data);
					if(!in_array($res['id'],$arrID)) array_push($arrID,$res['id']);
				endif;
			}
			if(count($arrID)>0):
				delete_data('tbl_finish_detail',['id not' => $arrID, 'id_finish' => $response['id']]);
			endif;
		endif;
		render($response,'json');
	}

	function data(){
		$kode_anggaran 	= post('kode_anggaran');
		$kode_cabang 	= post('kode_cabang');
		$menu 			= post('menu');

		// cabang
		if($kode_cabang):
			$data['cabang'][0] = get_data('tbl_m_cabang',[
				'where' => [
					'kode_cabang' => $kode_cabang,
					'kode_anggaran' => $kode_anggaran
				]
			])->result();

		else:
			$data['cabang'][0] = get_data('tbl_m_cabang',array('where_array'=>array(
				'parent_id'=>0,'is_active' => 1, 'kode_anggaran' => $kode_anggaran
			),'order_by' => 'urutan'))->result();
		endif;
		foreach($data['cabang'][0] as $m0) {
			$data['cabang'][$m0->id] = get_data('tbl_m_cabang',array('where_array'=>array(
				'parent_id'=>$m0->id,'is_active' => 1, 'kode_anggaran' => $kode_anggaran
			),'order_by' => 'urutan'))->result();
			foreach($data['cabang'][$m0->id] as $m1) {
				$data['cabang'][$m1->id] = get_data('tbl_m_cabang',array('where_array'=>array(
					'parent_id'=>$m1->id,'is_active' => 1, 'kode_anggaran' => $kode_anggaran
				),'order_by' => 'urutan'))->result();
				foreach($data['cabang'][$m1->id] as $m2) {
					$data['cabang'][$m2->id] = get_data('tbl_m_cabang',array('where_array'=>array(
						'parent_id'=>$m2->id,'is_active' => 1, 'kode_anggaran' => $kode_anggaran
					),'order_by' => 'urutan'))->result();
				}
			}
		}

		// menu finish
		if($menu):
			$data['menu'] = get_data('tbl_finish','id',$menu)->result();
		else:
			$data['menu'] = get_data('tbl_finish','kode_anggaran',$kode_anggaran)->result();
		endif;

		$data['access'] = get_access('finish');
		$view = $this->load->view('transaction/finish/table',$data,true);

		render([
			'view' => $view
		],'json');
	}

	function save_perubahan($kode_anggaran,$kode_cabang="",$menu=""){
		$arr_kode_cabang = [];

		if($kode_cabang):
			$data['cabang'][0] = get_data('tbl_m_cabang',[
				'where' => [
					'kode_cabang' => $kode_cabang,
					'kode_anggaran' => $kode_anggaran
				]
			])->result();
		else:
			$data['cabang'][0] = get_data('tbl_m_cabang',array('where_array'=>array(
				'parent_id'=>0,'is_active' => 1, 'kode_anggaran' => $kode_anggaran
			),'order_by' => 'urutan'))->result();
		endif;
		foreach($data['cabang'][0] as $m0) {
			$arr_kode_cabang[] = $m0->kode_cabang;
			
			$data['cabang'][$m0->id] = get_data('tbl_m_cabang',array('where_array'=>array(
				'parent_id'=>$m0->id,'is_active' => 1, 'kode_anggaran' => $kode_anggaran
			),'order_by' => 'urutan'))->result();
			foreach($data['cabang'][$m0->id] as $m1) {
				$arr_kode_cabang[] = $m1->kode_cabang;

				$data['cabang'][$m1->id] = get_data('tbl_m_cabang',array('where_array'=>array(
					'parent_id'=>$m1->id,'is_active' => 1, 'kode_anggaran' => $kode_anggaran
				),'order_by' => 'urutan'))->result();
				foreach($data['cabang'][$m1->id] as $m2) {
					$arr_kode_cabang[] = $m2->kode_cabang;

					$data['cabang'][$m2->id] = get_data('tbl_m_cabang',array('where_array'=>array(
						'parent_id'=>$m2->id,'is_active' => 1, 'kode_anggaran' => $kode_anggaran
					),'order_by' => 'urutan'))->result();
					foreach($data['cabang'][$m2->id] as $m3) {
						$arr_kode_cabang[] = $m3->kode_cabang;
					}
				}
			}
		}

		if($menu):
			$menu = get_data('tbl_finish','id',$menu)->result();
		else:
			$menu = get_data('tbl_finish','kode_anggaran',$kode_anggaran)->result();
		endif;

		$ck = post('ck');
		if($ck):
			$ck = json_decode($ck,true);
			foreach($menu as $k => $v){
				$id = $v->id;
				$ls = get_data('tbl_finish','id',$id)->row();
				$before_cabang = [];
				if($ls->kode_cabang):
					$before_cabang = json_decode($ls->kode_cabang,true);
				endif;

				$cabang = [];
				if(isset($ck[$id])):
					$cabang = $ck[$id];
				endif;

				$arr = array_merge(array_diff($before_cabang,$arr_kode_cabang),$cabang);
				$arr = array_unique($arr);

				$data = [
					'id' 			=> $ls->id,
					'kode_cabang'	=> json_encode($arr),
				];
				save_data('tbl_finish',$data);
			}
		else:
			foreach($menu as $v){
				update_data('tbl_finish',['kode_cabang' => '[]'],'id',$v->id);
			}
		endif;
	}

	function delete() {
		$child		= array(
			'id_finish'	=> 'tbl_finish_detail'
		);
		$response 	= destroy_data('tbl_finish','id',post('id'),$child);
		render($response,'json');
	}

	function get_data(){
		$data 			= get_data('tbl_finish','id',post('id'))->row_array();
		$data['access']	= get_data('tbl_finish_detail','id_finish',post('id'))->result_array();
		render($data,'json');
	}
}