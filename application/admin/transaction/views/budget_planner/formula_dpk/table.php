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
	foreach($giro_coa as $k => $v){
		$dataSaved 		= [];
		$last_year 		= $anggaran->tahun_terakhir_realisasi;
		$temp_tahun 	= $last_year;
		$temp_core_n 	= $last_year;
		$temp_core_l 	= $last_year;
		$real 	= 0;
		$real2 	= 0;
		$core_key 	= multidimensional_search($giro_core[$last_year], array(
			'glwnco' => $v->coa,
		));
		if(strlen($core_key)>0):
			$field 		= 'B_' . sprintf("%02d", ($anggaran->bulan_terakhir_realisasi-1));
			$kali_minus = $giro_core[$last_year][$core_key]['kali_minus'];
			$val 		= $giro_core[$last_year][$core_key][$field];
			$real 		= kali_minus($val,$kali_minus);

			$field 		= 'B_' . sprintf("%02d", ($anggaran->bulan_terakhir_realisasi));
			$val 		= $giro_core[$last_year][$core_key][$field];
			$real2  	= kali_minus($val,$kali_minus);
		endif;

		$lr_real 	= 0;
		$lr_real2 	= 0;
		$core_key_b = multidimensional_search($giro_core[$last_year], array(
			'glwnco' => $v->bunga,
		));
		if(strlen($core_key_b)>0):
			$field 		= 'B_' . sprintf("%02d", ($anggaran->bulan_terakhir_realisasi-1));
			$kali_minus	= $giro_core[$last_year][$core_key_b]['kali_minus'];
			$val 		= $giro_core[$last_year][$core_key_b][$field];
			$lr_real 	= kali_minus($val,$kali_minus);

			$field 		= 'B_' . sprintf("%02d", ($anggaran->bulan_terakhir_realisasi));
			$val 		= $giro_core[$last_year][$core_key_b][$field];
			$lr_real2 	= kali_minus($val,$kali_minus);
		endif;

		$pd_bln = $real2 - $real;
		$rate 	= $v->rate;

		$item .= '<tr>';
		$item .= '<td>'.$v->coa.'</td>';
		$item .= '<td>'.remove_spaces($v->glwdes).'</td>';
		$item .= '<td class="text-right">'.custom_format(view_report($real)).'</td>';
		$item .= '<td class="text-right">'.custom_format(view_report($real2)).'</td>';
		$temp_tahun_n 	= '';
		$arrGiro = [];
		foreach($detail_tahun as $k2 => $v2){
			$field 	= 'P_' . sprintf("%02d", $v2->bulan);
			$field2 = 'B_' . sprintf("%02d", $v2->bulan);
			if($v2->singkatan != arrSumberData()['real']):
				if($temp_tahun_n != $v2->tahun):
					$temp_tahun_n = $v2->tahun;
					$key_n = multidimensional_search($giro_dt, array(
						'coa' 		=> $v->coa,
						'tahun_core'=> $v2->tahun
					));
				endif;
				$val = 0;
				if(strlen($key_n)>0):
					$val = $giro_dt[$key_n][$field];
				endif;
				$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
				$arrGiro[$v2->tahun][$v2->bulan] = $val;
				$dataSaved[$v->coa.'-'.$v2->tahun]['bulan_'.$v2->bulan] = $val;
			elseif(isset($giro_core[$v2->tahun])):
				if($temp_core_n != $v2->tahun):
					$temp_core_n = $v2->tahun;
					$core_key 	= multidimensional_search($giro_core[$v2->tahun], array(
						'glwnco' => $v->coa,
					));
				endif;
				if(strlen($core_key)>0):
					$kali_minus	= $giro_core[$v2->tahun][$core_key]['kali_minus'];
					$val 		= $giro_core[$v2->tahun][$core_key][$field2];
					$val 		= kali_minus($val,$kali_minus);
					$dataSaved[$v->coa.'-'.$v2->tahun]['bulan_'.$v2->bulan] = $val;
				endif;
			endif;
		}
		$item .= '</tr>';

		// Perhitungan
		$item_pd_bulan = '';
		$item_sd_bulan = '';
		$item_hadiah   = '';
		$bln_before    = $lr_real2;
		$temp_year_baru = '';
		$temp_year_b 	= '';
		$temp_year_hadiah= '';
		foreach($detail_tahun as $k2 => $v2){
			$field  = 'P_' . sprintf("%02d", $v2->bulan);
			$field2 = 'B_' . sprintf("%02d", $v2->bulan);
			if($v2->singkatan != arrSumberData()['real']):
				
				// PD BULAN
				$pd_bln 	= (($arrGiro[$v2->tahun][$v2->bulan] * $rate) / (100*12));
				if($temp_year_baru != $v2->tahun):
					$temp_year_baru = $v2->tahun;
					$baru_key 	= multidimensional_search($dt_bunga, array(
						'glwnco' 	 => $v->bunga.'_baru',
						'tahun_core' => $v2->tahun,
					));
				endif;
				if(strlen($baru_key)>0):
					$changed = json_decode($dt_bunga[$baru_key]['changed']);
					if(in_array('bulan_'.$v2->bulan,$changed)):
						$pd_bln = $dt_bunga[$baru_key]['bulan_'.$v2->bulan];
					endif;
				endif;
				$name 		= 'val-'.$v->bunga.'_baru-'.$v2->tahun;
				$item_pd_bulan .= '<td style="background:'.$bgedit.'"><div style="background:'.$bgedit.'" style="min-height: 10px; width: 50px; overflow: hidden;"  contenteditable="'.$contentedit.'" class="edit-value text-right" data-name="bulan_'.$v2->bulan.'" data-id="'.$name.'" data-value="'.view_report($pd_bln).'">'.custom_format(view_report($pd_bln)).'</div></td>';
				
				// cek biaya hadiah
				$hadiah 	= 0;
				if(in_array($v->coa,$arr_coa_hadiah)):
					if($temp_year_hadiah != $v2->tahun):
						$hadiah_key 	= multidimensional_search($giro_dt, array(
							'coa' 	 	=> $v->coa.'_hadiah',
							'tahun_core' => $v2->tahun,
						));
					endif;
					if(strlen($hadiah_key)>0):
						$hadiah = $giro_dt[$hadiah_key][$field];
					endif;
					$item_hadiah .= '<td class="text-right">'.custom_format(view_report($hadiah)).'</td>';
				endif;

				$val = $bln_before + $pd_bln + $hadiah;
				if($v2->bulan == 1):
					$val = $pd_bln + $hadiah;
				endif;
				$val = round(view_report($val),-2);
				$val = insert_view_report($val);

				if($temp_year_b != $v2->tahun):
					$temp_year_b = $v2->tahun;
					$b_key 	= multidimensional_search($dt_bunga, array(
						'glwnco' 	 => $v->bunga,
						'tahun_core' => $v2->tahun,
					));
				endif;
				if(strlen($b_key)>0):
					$changed = json_decode($dt_bunga[$b_key]['changed']);
					if(in_array('bulan_'.$v2->bulan,$changed)):
						$val = $dt_bunga[$b_key]['bulan_'.$v2->bulan];
					endif;
				endif;

				$bln_before = $val;
				$name 		= 'val-'.$v->bunga.'-'.$v2->tahun;
				$item_sd_bulan .='<td style="background:'.$bgedit.'"><div style="background:'.$bgedit.'" style="min-height: 10px; width: 50px; overflow: hidden;"  contenteditable="'.$contentedit.'" class="edit-value text-right" data-name="bulan_'.$v2->bulan.'" data-id="'.$name.'" data-value="'.view_report($val).'">'.custom_format(view_report($val)).'</div></td>';
				$dataSaved[$v->bunga.'-'.$v2->tahun]['bulan_'.$v2->bulan] = $val;
			elseif(isset($giro_core[$v2->tahun])):
				if($temp_core_l != $v2->tahun):
					$temp_core_l = $v2->tahun;
					$core_key_b 	= multidimensional_search($giro_core[$v2->tahun], array(
						'glwnco' => $v->bunga,
					));
				endif;
				if(strlen($core_key_b)>0):
					$kali_minus	= $giro_core[$v2->tahun][$core_key_b]['kali_minus'];
					$val 		= $giro_core[$v2->tahun][$core_key_b][$field2];
					$val 		= kali_minus($val,$kali_minus);
					$dataSaved[$v->bunga.'-'.$v2->tahun]['bulan_'.$v2->bulan] = $val;
				endif;
			endif;
		}


		$item .= '<tr>';
		$item .= '<td class="button">'.$v->bunga;
		if($access_edit):
			$item .= '<div class="text-center"><button type="button" class="btn btn-danger btn-remove" data-id="'.$v->bunga.'" title="Hapus"><i class="fa-times"></i></button><div>';
		endif;
		$item .= '</td>';

		$item .= '<td>'.remove_spaces($v->bunga_name).' S/D BLN</td>';
		$item .= '<td class="text-right">'.custom_format(view_report($lr_real)).'</td>';
		$item .= '<td class="text-right">'.custom_format(view_report($lr_real2)).'</td>';
		$item .= $item_sd_bulan;
		$item .= '</tr>';

		$item .= '<tr>';
		$item .= '<td></td>';
		$item .= '<td>RATE</td>';
		$item .= '<td></td>';
		$item .= '<td></td>';
		foreach($detail_tahun as $k2 => $v2){
			if($v2->singkatan != arrSumberData()['real']):
				$item .= '<td class="text-right">'.custom_format($rate,false,2).'</td>';
			endif;
		}
		$item .= '</tr>';

		$item .= '<tr>';
		$item .= '<td class="button">';
		if($access_edit):
			$item .= '<div class="text-center"><button type="button" class="btn btn-danger btn-remove" data-id="'.$v->bunga.'_baru" title="Hapus"><i class="fa-times"></i></button><div>';
		endif;
		$item .= '</td>';
		$item .= '<td>'.remove_spaces($v->bunga_name).' PD BLN</td>';
		$item .= '<td></td>';
		$item .= '<td></td>';
		$item .= $item_pd_bulan;
		$item .= '</tr>';

		if(in_array($v->coa,$arr_coa_hadiah)):
			$item .= '<tr>';
			$item .= '<td></td>';
			$item .= '<td>'.remove_spaces($v->glwdes).' BIAYA HADIAH'.'</td>';
			$item .= '<td></td>';
			$item .= '<td></td>';
			$item .= $item_hadiah;
			$item .= '</tr>';
		endif;

		$item .= '<tr>';
		$item .= '<td class="border-none">.</td>';
		$item .= '</tr>';

		$where = [
			'anggaran'		=> $anggaran,
			'kode_cabang'	=> $kode_cabang,
			'rate'			=> $rate,
		];
		data_saved($dataSaved,$where);

	}
	echo $item;
?>