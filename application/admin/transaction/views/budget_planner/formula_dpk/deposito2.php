<?php
	$bgedit ="";
	$contentedit ="false" ;
	$id = 'keterangan';
	if($access_edit) {
		$bgedit =bgEdit();
		$contentedit ="true" ;
		$id = 'id' ;
	}

	$item = '';

	$last_year 		= $anggaran->tahun_terakhir_realisasi;
	$b1 	  		= sprintf("%02d", ($anggaran->bulan_terakhir_realisasi-1));
	$b2 	  		= sprintf("%02d", ($anggaran->bulan_terakhir_realisasi));
	$temp_tahun 	= $last_year;
	$core_key = multidimensional_search($data_core[$last_year], array(
		'glwnco' => '5132012',
	));

	$real 	= 0;
	$real2 	= 0;
	if(strlen($core_key)>0):
		$field 		= 'B_' . sprintf("%02d", ($anggaran->bulan_terakhir_realisasi-1));
		$kali_minus = $data_core[$last_year][$core_key]['kali_minus'];
		$val 		= $data_core[$last_year][$core_key][$field];
		$real 		= kali_minus($val,$kali_minus);

		$field 		= 'B_' . sprintf("%02d", ($anggaran->bulan_terakhir_realisasi));
		$val 		= $data_core[$last_year][$core_key][$field];
		$real2  	= kali_minus($val,$kali_minus);
	endif;

	$arrRincian = [];
	foreach($dt_coa as $k => $v){
		$rate = $v->rate;
		$item .= '<tr>';
		$item .= '<td>'.$v->coa.'</td>';
		$item .= '<td>'.remove_spaces($v->nama).'</td>';
		if(count($arr_tahun)>1):
			$val  = ($real * $rate)/1200;
			$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
			if(isset($arrRincian[$last_year.$b1])) $arrRincian[$last_year.$b1] += $val; else $arrRincian[$last_year.$b1] = $val;

			$val  = ($real2 * $rate)/1200;
			$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
			if(isset($arrRincian[$last_year.$b2])) $arrRincian[$last_year.$b2] += $val; else $arrRincian[$last_year.$b2] = $val;
		endif;
		$temp_tahun = '';
		foreach($detail_tahun as $k2 => $v2){
			$field  = 'P_' . sprintf("%02d", $v2->bulan);
			$bln 	= sprintf("%02d", $v2->bulan);
			if($v2->singkatan != arrSumberData()['real'] || count($arr_tahun) == 1):
				$val = 0;
				if($temp_tahun != $v2->tahun):
					$key 	= multidimensional_search($dt, array(
						'coa' 	 	=> $v->coa,
						'tahun_core' => $v2->tahun,
					));
				endif;
				if(strlen($key)>0):
					$val = $dt[$key][$field];
				endif;
				$val = ($val * $rate) / 1200;
				$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
				if(isset($arrRincian[$v2->tahun.$bln])) $arrRincian[$v2->tahun.$bln] += $val; else $arrRincian[$v2->tahun.$bln] = $val;
			endif;

		}
		$item .= '</tr>';
	}

	$item .= '<tr>';
	$item .= '<td class="button text-center">';
	if($access_edit):
		$item .= '<div class="text-center"><button type="button" class="btn btn-danger btn-remove" data-id="'.$coa->glwnco.'_baru" title="Hapus"><i class="fa-times"></i></button><div>';
	endif;
	$item .= '</td>';

	$item .= '<td>'.remove_spaces($coa->glwdes).' PD BLN</td>';
	$bln_before = 0;
	if(count($arr_tahun)>1):
		$item .= '<td></td>';
		$item .= '<td class="text-right">'.custom_format(view_report($arrRincian[$last_year.$b1])).'</td>';
		$bln_before = $arrRincian[$last_year.$b1];
	endif;
	$arrTotal 		= [];
	$temp_tahun_b 	= '';
	foreach($detail_tahun as $k2 => $v2){
		$field 	= 'bulan_'.$v2->bulan;
		$bln 	= sprintf("%02d", $v2->bulan);
		if($v2->singkatan != arrSumberData()['real'] || count($arr_tahun) == 1):
			$time = strtotime($v2->tahun.'-'.$bln.'-01');
			$final = date("Ym", strtotime("-1 month", $time));
			$val = 0;
			if(isset($arrRincian[$final])):
				$val = $arrRincian[$final];
			endif;

			if($temp_tahun_b != $v2->tahun):
				$temp_tahun_b = $v2->tahun;
				$key_b = multidimensional_search($dt_bunga, array(
					'glwnco' 	 	=> $coa->glwnco.'_baru',
					'tahun_core' => $v2->tahun,
				));
			endif;
			if(strlen($key_b)>0):
				$changed = json_decode($dt_bunga[$key_b]['changed']);
				if(in_array($field,$changed)):
					$val = $dt_bunga[$key_b][$field];
				endif;
			endif;

			$name 		= 'val-'.$coa->glwnco.'_baru-'.$v2->tahun;
			if($v2->singkatan == arrSumberData()['real']):
				$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
			else:
				$item .= '<td style="background:'.$bgedit.'"><div style="background:'.$bgedit.'" style="min-height: 10px; width: 50px; overflow: hidden;"  contenteditable="'.$contentedit.'" class="edit-value text-right" data-name="bulan_'.$v2->bulan.'" data-id="'.$name.'" data-value="'.view_report($val).'">'.custom_format(view_report($val)).'</div></td>';
			endif;
			$arrTotal[$v2->tahun.$v2->bulan] = $val;
		endif;
	}
	$item .= '</tr>';

	$item .= '<tr>';
	$item .= '<td>'.$coa->glwnco.'</td>';
	$item .= '<td>'.remove_spaces($coa->glwdes).' S/D BLN</td>';
	if(count($arr_tahun)>1):
		$item .= '<td class="text-right">'.custom_format(view_report($real)).'</td>';
		$item .= '<td class="text-right">'.custom_format(view_report($real2)).'</td>';
	endif;
	$dataSaved 		= [];
	foreach($detail_tahun as $k2 => $v2){
		$field2  = 'B_' . sprintf("%02d", $v2->bulan);
		if($v2->singkatan != arrSumberData()['real']):
			$val = $bln_before + $arrTotal[$v2->tahun.$v2->bulan];
			if($v2->bulan == 1):
				$val = $arrTotal[$v2->tahun.$v2->bulan];
			endif;
			$bln_before = $val;
			$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
			$dataSaved[$coa->glwnco.'-'.$v2->tahun]['bulan_'.$v2->bulan] = $val;
		elseif(isset($data_core[$v2->tahun])):
			if($v2->tahun != $temp_tahun):
				$core_key = multidimensional_search($data_core[$v2->tahun], array(
					'glwnco' => '5132012',
				));
			endif;
			$val = 0;
			if(strlen($core_key)>0):
				$kali_minus = $data_core[$v2->tahun][$core_key]['kali_minus'];
				$val = $data_core[$v2->tahun][$core_key][$field2];
				$val = kali_minus($val,$kali_minus);
			endif;
			$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
			$dataSaved[$coa->glwnco.'-'.$v2->tahun]['bulan_'.$v2->bulan] = $val;
			$bln_before = $val;
		endif;
	}

	$item .= '</tr>';

	$where = [
		'anggaran'		=> $anggaran,
		'kode_cabang'	=> $kode_cabang,
		'rate'			=> 1,
	];
	data_saved($dataSaved,$where);

	echo $item;
?>