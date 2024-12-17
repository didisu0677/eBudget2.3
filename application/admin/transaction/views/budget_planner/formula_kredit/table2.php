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
	foreach($dt_coa as $k => $v){
		$last_year 		= $anggaran->tahun_terakhir_realisasi;
		$temp_year_n 	= '';
		$temp_core_n 	= $last_year;
		$temp_core_b 	= $last_year;
		$rate 			= $v->rate;

		$nrc_real 	= 0;
		$nrc_real2 	= 0;
		$core_key 	= multidimensional_search($data_core[$last_year], array(
			'glwnco' => $v->coa,
		));
		if(strlen($core_key)>0):
			$field 		= 'B_' . sprintf("%02d", ($anggaran->bulan_terakhir_realisasi-1));
			$kali_minus	= $data_core[$last_year][$core_key]['kali_minus'];
			$val 		= $data_core[$last_year][$core_key][$field];
			$nrc_real 	= kali_minus($val,$kali_minus);

			$field 		= 'B_' . sprintf("%02d", ($anggaran->bulan_terakhir_realisasi));
			$val 		= $data_core[$last_year][$core_key][$field];
			$nrc_real2 	= kali_minus($val,$kali_minus);
		endif;

		$lr_real 	= 0;
		$lr_real2 	= 0;
		$core_key_b = multidimensional_search($data_core[$last_year], array(
			'glwnco' => $v->bunga_kredit,
		));
		if(strlen($core_key_b)>0):
			$field 		= 'B_' . sprintf("%02d", ($anggaran->bulan_terakhir_realisasi-1));
			$kali_minus	= $data_core[$last_year][$core_key_b]['kali_minus'];
			$val 		= $data_core[$last_year][$core_key_b][$field];
			$lr_real 	= kali_minus($val,$kali_minus);

			$field 		= 'B_' . sprintf("%02d", ($anggaran->bulan_terakhir_realisasi));
			$val 		= $data_core[$last_year][$core_key_b][$field];
			$lr_real2 	= kali_minus($val,$kali_minus);
		endif;

		// kredit
		$item .= '<tr>';
		$item .= '<td>'.$v->coa.'</td>';
		$item .= '<td>'.remove_spaces($v->glwdes).'</td>';
		$item .= '<td class="text-right">'.custom_format(view_report($nrc_real)).'</td>';
		$item .= '<td class="text-right">'.custom_format(view_report($nrc_real2)).'</td>';
		$arrKredit = [];
		$arrSaved  = [];
		foreach($detail_tahun as $k2 => $v2){
			$field 	= 'P_' . sprintf("%02d", $v2->bulan);
			$field2 = 'B_' . sprintf("%02d", $v2->bulan);
			if($v2->singkatan != arrSumberData()['real']):
				if($v2->tahun != $temp_year_n):
					$temp_year_n = $v2->tahun;
					$key_n = multidimensional_search($dt_kredit, array(
						'coa' 		=> $v->coa,
						'tahun_core'=> $v2->tahun
					));
				endif;
				$val = 0;
				if(strlen($key_n)>0):
					$val = $dt_kredit[$key_n][$field];
				endif;
				$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
				$arrKredit[$v2->tahun][$v2->bulan] = $val;
				$arrSaved[$v->coa.'-'.$v2->tahun]['bulan_'.$v2->bulan] = $val;
			elseif(isset($data_core[$v2->tahun])):
				if($temp_core_n != $v2->tahun):
					$temp_core_n = $v2->tahun;
					$core_key 	= multidimensional_search($data_core[$v2->tahun], array(
						'glwnco' => $v->coa,
					));
				endif;
				if(strlen($core_key)>0):
					$kali_minus	= $data_core[$v2->tahun][$core_key]['kali_minus'];
					$val 		= $data_core[$v2->tahun][$core_key][$field2];
					$val 		= kali_minus($val,$kali_minus);
					$arrSaved[$v->coa.'-'.$v2->tahun]['bulan_'.$v2->bulan] = $val;
				endif;
			endif;
		}
		$item .= '</tr>';

		// perhitungan pd bulan
		$item_pd_bulan 	= '';
		$item_sd_bulan 	= '';
		$bln_before 	= $lr_real2;
		$temp_year_baru = '';
		$temp_year_b 	= '';
		foreach($detail_tahun as $k2 => $v2){
			$field2 = 'B_' . sprintf("%02d", $v2->bulan);
			if($v2->singkatan != arrSumberData()['real']):
				
				// PD Bulan
				$val_pd_bln = ($arrKredit[$v2->tahun][$v2->bulan] * $rate)/1200;
				if($temp_year_baru != $v2->tahun):
					$temp_year_baru = $v2->tahun;
					$baru_key 	= multidimensional_search($dt_bunga, array(
						'glwnco' 	 => $v->bunga_kredit.'_baru',
						'tahun_core' => $v2->tahun,
					));
				endif;
				if(strlen($baru_key)>0):
					$changed = json_decode($dt_bunga[$baru_key]['changed']);
					if(in_array('bulan_'.$v2->bulan,$changed)):
						$val_pd_bln = $dt_bunga[$baru_key]['bulan_'.$v2->bulan];
					endif;
				endif;

				$name 		= 'val-'.$v->bunga_kredit.'_baru-'.$v2->tahun;
				$item_pd_bulan .= '<td style="background:'.$bgedit.'"><div style="background:'.$bgedit.'" style="min-height: 10px; width: 50px; overflow: hidden;"  contenteditable="'.$contentedit.'" class="edit-value text-right" data-name="bulan_'.$v2->bulan.'" data-id="'.$name.'" data-value="'.view_report($val_pd_bln).'">'.custom_format(view_report($val_pd_bln)).'</div></td>';

				// S/D Bulan
				$val = $bln_before + $val_pd_bln;
				if($v2->bulan == 1):
					$val = $val_pd_bln;
				endif;
				$val = round(view_report($val),-2);
				$val = insert_view_report($val);
				
				if($temp_year_b != $v2->tahun):
					$temp_year_b = $v2->tahun;
					$b_key 	= multidimensional_search($dt_bunga, array(
						'glwnco' 	 => $v->bunga_kredit,
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
				$name 		= 'val-'.$v->bunga_kredit.'-'.$v2->tahun;
				$item_sd_bulan .='<td style="background:'.$bgedit.'"><div style="background:'.$bgedit.'" style="min-height: 10px; width: 50px; overflow: hidden;"  contenteditable="'.$contentedit.'" class="edit-value text-right" data-name="bulan_'.$v2->bulan.'" data-id="'.$name.'" data-value="'.view_report($val).'">'.custom_format(view_report($val)).'</div></td>';
				$arrSaved[$v->bunga_kredit.'-'.$v2->tahun]['bulan_'.$v2->bulan] = $val;
			elseif(isset($data_core[$v2->tahun])):
				if($temp_core_b != $v2->tahun):
					$temp_core_b = $v2->tahun;
					$core_key_b 	= multidimensional_search($data_core[$v2->tahun], array(
						'glwnco' => $v->bunga_kredit,
					));
				endif;
				if(strlen($core_key_b)>0):
					$kali_minus	= $data_core[$v2->tahun][$core_key_b]['kali_minus'];
					$val 		= $data_core[$v2->tahun][$core_key_b][$field2];
					$val 		= kali_minus($val,$kali_minus);
					$arrSaved[$v->bunga_kredit.'-'.$v2->tahun]['bulan_'.$v2->bulan] = $val;
				endif;
			endif;
		}

		// bunga s/d bulan
		$item .= '<tr>';
		$item .= '<td class="button">'.$v->bunga_kredit;
		if($access_edit):
			$item .= '<div class="text-center"><button type="button" class="btn btn-danger btn-remove" data-id="'.$v->bunga_kredit.'" title="Hapus"><i class="fa-times"></i></button><div>';
		endif;
		$item .= '</td>';

		$item .= '<td>'.remove_spaces($v->bunga_name).' S/D BLN</td>';
		$item .= '<td class="text-right">'.custom_format(view_report($lr_real)).'</td>';
		$item .= '<td class="text-right">'.custom_format(view_report($lr_real2)).'</td>';
		$item .= $item_sd_bulan;
		$item .= '</tr>';

		// rate
		$item .= '<tr>';
		$item .= '<td></td>';
		$item .= '<td>RATE</td>';
		$item .= '<td class="text-right"></td>';
		$item .= '<td class="text-right"></td>';
		foreach($detail_tahun as $k2 => $v2){
			if($v2->singkatan != arrSumberData()['real']):
				$item .= '<td class="text-right">'.custom_format($rate,false,2).'</td>';
			endif;
		}
		$item .= '</tr>';

		// bunga pd bulan
		$item .= '<tr>';
		$item .= '<td class="button">';
		if($access_edit):
			$item .= '<div class="text-center"><button type="button" class="btn btn-danger btn-remove" data-id="'.$v->bunga_kredit.'_baru" title="Hapus"><i class="fa-times"></i></button><div>';
		endif;
		$item .= '</td>';
		$item .= '<td>'.remove_spaces($v->bunga_name).' PD BLN</td>';
		$item .= '<td class="text-right"></td>';
		$item .= '<td class="text-right"></td>';
		$item .= $item_pd_bulan;
		$item .= '</tr>';

		$item .= '<tr>';
		$item .= '<td class="border-none">.</td>';
		$item .= '</tr>';

		$where = [
			'anggaran'		=> $anggaran,
			'kode_cabang'	=> $kode_cabang,
			'rate'			=> $rate,
		];
		data_saved($arrSaved,$where);

	}
	echo $item;
?>