<?php
	$bgedit ="";
	$contentedit ="false" ;
	$id = 'keterangan';
	if($access_edit) {
		$bgedit =bgEdit();
		$contentedit ="true" ;
		$id = 'id' ;
	}

	$last_year 		= $anggaran->tahun_terakhir_realisasi;
	$temp_tahun 	= $last_year;
	$temp_tahun_dt 	= $last_year;
	$real 	= 0;
	$real2 	= 0;
	$core_key 	= multidimensional_search($amort_core[$last_year], array(
		'glwnco' => $amort->glwnco,
	));
	if(strlen($core_key)>0):
		$field 		= 'B_' . sprintf("%02d", ($anggaran->bulan_terakhir_realisasi-1));
		$kali_minus = $amort_core[$last_year][$core_key]['kali_minus'];
		$val 		= $amort_core[$last_year][$core_key][$field];
		$real 		= kali_minus($val,$kali_minus);

		$field 		= 'B_' . sprintf("%02d", ($anggaran->bulan_terakhir_realisasi));
		$val 		= $amort_core[$last_year][$core_key][$field];
		$real2  	= kali_minus($val,$kali_minus);
	endif;

	$pd_bln 	= $real2 - $real;
	$rate 		= 0.5;

	$key = multidimensional_search($amort_dt, array(
		'glwnco' 		=> $amort->glwnco,
		'tahun_core'	=> $temp_tahun_dt
	));
	if(strlen($key)>0):
		$changed = json_decode($amort_dt[$key]['changed']);
		if(in_array('rate',$changed)):
			$rate = $amort_dt[$key]['rate'];
		endif;
	endif;

	$total 		= $pd_bln * $rate;
	$bln_before	= $real2;

	$arrSaved = [];
	$item = '';
	$item .= '<tr>';
	
	$item .= '<td class="button">'.$amort->glwnco;
	if($access_edit):
		$item .= '<div class="text-center"><button type="button" class="btn btn-danger btn-remove" data-id="'.$amort->glwnco.'" title="Hapus"><i class="fa-times"></i></button><div>';
	endif;
	$item .= '</td>';

	$item .= '<td>'.remove_spaces($amort->glwdes).'</td>';
	$item .= '<td class="text-right">'.custom_format(view_report($real)).'</td>';
	$item .= '<td class="text-right">'.custom_format(view_report($real2)).'</td>';
	foreach($detail_tahun as $k => $v){
		$field 		= 'bulan_'.$v->bulan;
		$field2 	= 'B_' . sprintf("%02d", ($v->bulan));
		if($v->singkatan != arrSumberData()['real']):
			$val 		= $bln_before+$total;

			if($v->tahun != $temp_tahun_dt):
				$temp_tahun_dt = $v->tahun;
				$key = multidimensional_search($amort_dt, array(
					'glwnco' 		=> $amort->glwnco,
					'tahun_core'	=> $v->tahun
				));
			endif;

			if(strlen($key)>0):
				$changed = json_decode($amort_dt[$key]['changed']);
				if(in_array($field,$changed)):
					$val = $amort_dt[$key][$field];
				endif;
			endif;

			$bln_before = $val;
			$name 		= 'val-'.$amort->glwnco.'-'.$v->tahun;
			$item .= '<td style="background:'.$bgedit.'"><div style="background:'.$bgedit.'" style="min-height: 10px; width: 50px; overflow: hidden;"  contenteditable="'.$contentedit.'" class="edit-value text-right" data-name="bulan_'.$v->bulan.'" data-id="'.$name.'" data-value="'.view_report($val).'">'.custom_format(view_report($val)).'</div></td>';
			$arrSaved[$amort->glwnco.'-'.$v->tahun][$field] = $val;
		elseif(isset($amort_core[$v->tahun])):
			if($temp_tahun != $v->tahun):
				$temp_tahun = $v->tahun;
				$core_key = multidimensional_search($amort_core[$v->tahun], array(
					'glwnco' => $amort->glwnco,
				));
			endif;
			if(strlen($core_key)>0):
				$kali_minus = $amort_core[$v->tahun][$core_key]['kali_minus'];
				$val 		= $amort_core[$v->tahun][$core_key][$field2];
				$val  		= kali_minus($val,$kali_minus);
				$arrSaved[$amort->glwnco.'-'.$v->tahun][$field] = $val;
			endif;
		endif;
	}
	$item .= '</tr>';
	
	$item2 = '';
	foreach($detail_tahun as $k => $v){
		if($v->singkatan != arrSumberData()['real']):
			$item2 .= '<td class="text-right"></td>';
		endif;
	}

	$item .= '<tr>';
	$item .= '<td></td>';
	$item .= '<td>'.remove_spaces($amort->glwdes).' PD BLN</td>';
	$item .= '<td></td>';
	$item .= '<td class="text-right">'.custom_format(view_report($pd_bln)).'</td>';
	$item .= $item2;
	$item .= '</tr>';

	$item .= '<tr>';
	$item .= '<td></td>';
	$item .= '<td>RATA-RATA AKUMULASI</td>';
	$item .= '<td></td>';
	$name = 'rate-'.$amort->glwnco.'-'.$anggaran->tahun_anggaran;
	$item .= '<td style="background:'.$bgedit.'"><div style="background:'.$bgedit.'" style="min-height: 10px; width: 50px; overflow: hidden;"  contenteditable="'.$contentedit.'" class="edit-value text-right percent" data-name="rate" data-id="'.$name.'" data-value="'.$rate.'">'.custom_format($rate,false,2).'</div></td>';
	$item .= $item2;
	$item .= '</tr>';

	$item .= '<tr>';
	$item .= '<td></td>';
	$item .= '<td>'.remove_spaces($amort->glwdes).' PD BLN</td>';
	$item .= '<td></td>';
	$item .= '<td class="text-right">'.custom_format(view_report($total)).'</td>';
	$item .= $item2;
	$item .= '</tr>';

	echo $item;

	$where = [
		'anggaran'		=> $anggaran,
		'kode_cabang'	=> $kode_cabang,
		'rate'			=> $rate,
	];
	data_saved($arrSaved,$where);

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

			$ck = get_data('tbl_formula_kredit',[
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
			save_data('tbl_formula_kredit',$data,[],true);
		}
	}
?>