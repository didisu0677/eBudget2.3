<?php
function data_saved($dt,$p1){
	$anggaran 		= $p1['anggaran'];
	$kode_cabang 	= $p1['kode_cabang'];
	$rate 			= $p1['rate'];

	foreach($dt as $k => $v){
		$x 		= explode('-',$k);
		$coa 	= $x[0];
		$tahun 	= $x[1];
		$where 	= [
			'kode_anggaran'	=> $anggaran->kode_anggaran,
			'kode_cabang'	=> $kode_cabang,
			'tahun_core'	=> $tahun,
			'glwnco'		=> $coa,
		];

		$ck = get_data('tbl_formula_dpk',[
			'select' => 'id',
			'where'	 => $where
		])->row();
		
		$data = $v;
		$data['id'] 	= '';
		$data['rate']	= $rate;
		if($tahun == $anggaran->tahun_anggaran):
			$data['parent_id'] = '0';
		else:
			$data['parent_id'] = $kode_cabang;
		endif;
		if($ck):
			$data['id'] = $ck->id;
		else:
			$data = array_merge($data,$where);
		endif;
		save_data('tbl_formula_dpk',$data,[],true);
	}
}
?>