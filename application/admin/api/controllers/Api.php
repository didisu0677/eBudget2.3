<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends BE_Controller {
	function coa_option(){
		$db = get_data('tbl_m_coa',[
			'select' 	=> 'glwnco,glwdes',
			'where'		=> [
				'is_active' => 1,
				'kode_anggaran' => user('kode_anggaran')
			],
		])->result();
		$data = '<option></option>';
		foreach ($db as $v) {
			$data .= '<option value="'.$v->glwnco.'">'.$v->glwnco.' - '.remove_spaces($v->glwdes).'</option>';
		}
		render(['data'=>$data],'json');
	}

	function cabang_option(){
		$parent = post('parent');
		$type	= post('type');
		if($parent && $type == 'divisi'):
			$ck_parent = get_data('tbl_m_cabang','id',$parent)->row_array();
			if(isset($ck_parent) && $ck_parent['status_group'] == 1):
				$parent = " and (parent_id = '$parent')";
			else:
				$parent = " and (parent_id = '$parent' or id = '$parent')";
			endif;
		elseif($parent):
			$parent = " and parent_id = '$parent'";
		endif;
		$db = get_data('tbl_m_cabang',[
			'select' 	=> 'kode_cabang,nama_cabang',
			'where'		=> "kode_anggaran = '".user('kode_anggaran')."' and is_active = '1'".$parent,
			'order_by'	=> 'kode_cabang',
		])->result();
		$data = '';
		foreach ($db as $v) {
			$data .= '<option value="'.$v->kode_cabang.'">'.$v->nama_cabang.'</option>';
		}
		render(['data'=>$data],'json');
	}

	function currency_option(){
		$db = get_data('tbl_m_currency',[
			'select' 	=> 'id,nama',
			'where'		=> 'is_active = 1',
		])->result();
		// $data = '<option></option>';
		$data = '';
		foreach ($db as $v) {
			$data .= '<option value="'.$v->id.'">'.$v->nama.'</option>';
		}
		render(['data'=>$data],'json');
	}

	function provinsi_option(){
		$db = get_data('provinsi',[
			'select' 	=> 'id,name',
			'where'		=> "is_active = '1'",
			'order_by'	=> 'name',
		])->result();
		$data = '<option value="">'.lang('pilih_provinsi').'</option>';
		foreach ($db as $v) {
			$data .= '<option value="'.$v->id.'">'.$v->name.'</option>';
		}
		render(['data'=>$data],'json');
	}

	function kota_option(){
		$parent = post('parent');
		if($parent):
			$parent = " and id_provinsi = '$parent'";
		endif;
		$db = get_data('kota',[
			'select' 	=> 'id,name',
			'where'		=> "is_active = '1'".$parent,
			'order_by'	=> 'name',
		])->result();
		$data = '<option value="">'.lang('pilih_kota').'</option>';
		foreach ($db as $v) {
			$data .= '<option value="'.$v->id.'">'.$v->name.'</option>';
		}
		render(['data'=>$data],'json');
	}

	function kecamatan_option(){
		$parent = post('parent');
		if($parent):
			$parent = " and id_kota = '$parent'";
		endif;
		$db = get_data('kecamatan',[
			'select' 	=> 'id,name',
			'where'		=> "is_active = '1'".$parent,
			'order_by'	=> 'name',
		])->result();
		$data = '<option value="">'.lang('pilih_kecamatan').'</option>';
		foreach ($db as $v) {
			$data .= '<option value="'.$v->id.'">'.$v->name.'</option>';
		}
		render(['data'=>$data],'json');
	}

	function divisi_option(){
		$gab = get_data('tbl_m_cabang',[
			'where' => [
				'kode_cabang' => '00100',
				'kode_anggaran' => user('kode_anggaran')
			]
		])->row();
		$data = '';
		if($gab):
			$ls = get_data('tbl_m_cabang',[
				'where'	=> [
					'parent_id' => $gab->id,
					'is_active' => 1,
					'kode_anggaran' => user('kode_anggaran')
				]
			])->result();
			foreach ($ls as $k => $v) {
				$data .= '<option value="'.$v->kode_cabang.'">'.$v->nama_cabang.'</option>';
			}
		endif;
		render(['data'=>$data],'json');
	}

	function kategori_kantor_keterangan_option(){
		$db = get_data('tbl_kategori_kantor_keterangan',[
			'select' 	=> 'id,nama',
			'where'		=> "is_active = '1'",
			'order_by'	=> 'id',
		])->result();
		$data = '';
		foreach ($db as $v) {
			$data .= '<option value="'.$v->id.'">'.$v->nama.'</option>';
		}
		render(['data'=>$data],'json');
	}

	function pegawai_option(){
		$db = get_data('tbl_m_pegawai',[
			'select' 	=> 'id,nama,nip',
			'where'		=> "is_active = '1'",
			'order_by'	=> 'nip',
		])->result();
		$data = '';
		foreach ($db as $v) {
			$data .= '<option value="'.$v->id.'">'.$v->nip.'-'.$v->nama.'</option>';
		}
		render(['data'=>$data],'json');
	}

	function pegawai_select2(){
		$term = post('term');
		$term = str_replace("'", "`", $term);
		$data = [];
		$where = '';
		if($term):
			$where = " and (nama like '%$term%' or nip like '%$term%')";
		endif;
		$db = get_data('tbl_m_pegawai',[
			'select' 	=> 'id,nama,nip',
			'where'		=> "nip != '' and is_active = '1'".$where,
			'limit' 	=> 50,
			'order_by'	=> 'nip',
		])->result();
		foreach ($db as $k => $v) {
			$data[] = [
                'id' => $v->id,
                'text' => remove_spaces($v->nip).' - '.remove_spaces($v->nama),
            ];
		}
		render($data,'json');
	}

	function delete_data_table(){
		$tables = list_tables();
		foreach ($tables as $k => $v) {
			if ($this->db->field_exists('kode_anggaran', $v)):
	            delete_data($v,'kode_anggaran !=','2020-01');
	        endif;
		}
	}

	function target_finansial_option(){
		$db = get_data('tbl_m_range_target_finansial',[
			'select' 	=> 'id,nama',
			'where'		=> "is_active = '1'",
			'order_by'	=> 'urutan',
		])->result();
		$data = '';
		foreach ($db as $v) {
			$data .= '<option value="'.$v->id.'">'.$v->nama.'</option>';
		}
		render(['data'=>$data],'json');
	}

	function redirect_wa(){
		$validate = validate_phone(post('phone'));
		if($validate['status']):
			redirect('https://wa.me/'.$validate['phone'].'/');
		else:
			render([
				'status' 	=> false,
				'message'	=> 'Invalid phone number'
			],'json');
		endif;
	}
}