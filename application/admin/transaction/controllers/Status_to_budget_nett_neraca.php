<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Status_to_budget_nett_neraca extends BE_Controller {

	var $controller = 'status_to_budget_nett_neraca';
	var $table 		= 'tbl_budget_nett_neraca';
	function __construct() {
		parent::__construct();
	}

	function index() {
		$data = [
			'tahun' => get_data('tbl_tahun_anggaran','kode_anggaran',user('kode_anggaran'))->result(),
			'controller' => $this->controller
		];
		render($data);
	}

	function data(){
		$kode_anggaran = user('kode_anggaran');
		$data['kode_anggaran'] = $kode_anggaran;
		$view = $this->load->view('transaction/'.$this->controller.'/table',$data,true);

		$CI = get_instance();
		$menu  = menu($this->controller);
		$title = $menu['title'].'<br>'.$CI->title_modal;
		render([
			'status' => true,
			'view' 	 => $view,
			'title'  => $title,
		],'json');
	}

}