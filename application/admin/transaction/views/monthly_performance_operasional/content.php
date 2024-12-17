<?php
	$CI        	 = get_instance();
	$total_nilai = [];
	$arrRank 	 = [];
	$arrTotalCoa = [];
	$no = 0;
	$item_header = '<tr>';
	$item_header .= '<th></th>';
	$item_header .= '<th></th>';
	$item_header .= '<th></th>';
	
	$item_header2 = '<tr>';
	$item_header2 .= '<th>'.lang('no').'</th>';
	$item_header2 .= '<th class="text-center mw-250">'.lang('nama_cabang').'</th>';
	$item_header2 .= '<th class="text-center mw-150">'.lang('kantor').'</th>';

	$item_header3 = '<tr>';
	$item_header3 .= '<th></th>';
	$item_header3 .= '<th></th>';
	$item_header3 .= '<th></th>';

	foreach ($group as $k => $v) {
		foreach ($v as $k2 => $v2) {
			if(in_array(remove_spaces($v2->glwdes),$ck_coa) or in_array('All',$ck_coa)):
				$no ++;
				if($no != 1) $item_header .= '<th class="border-none bg-white mw-100"></th>';
				$item_header .= '<th class="text-center" colspan="10">'.$v2->coa.' - '.remove_spaces($v2->glwdes).'</th>';
				
				if($no != 1) $item_header2 .= '<th class="border-none bg-white mw-100"></th>';
				$item_header2 .= '<th class="text-center">'.month_lang($bulan).'-'.($anggaran->tahun_anggaran - 1).'</th>';
				$item_header2 .= '<th class="text-center" colspan="3">'.month_lang($bulan).'-'.($anggaran->tahun_anggaran).'</th>';
				$item_header2 .= '<th class="text-center">Pert</th>';
				$item_header2 .= '<th colspan="2" class="text-center mw-100">Nilai Huruf</th>';
				$item_header2 .= '<th colspan="2" class="text-center mw-100">Nilai Angka</th>';
				$item_header2 .= '<th class="text-center"></th>';
				
				if($no != 1) $item_header3 .= '<th class="border-none bg-white mw-100"></th>';
				$item_header3 .= '<th class="text-center mw-150">Real</th>';
				$item_header3 .= '<th class="text-center mw-150">Renc</th>';
				$item_header3 .= '<th class="text-center mw-150">Real</th>';
				$item_header3 .= '<th class="text-center mw-100">Penc (%)</th>';
				$item_header3 .= '<th class="text-center mw-100">YOY (%)</th>';
				$item_header3 .= '<th class="text-center mw-100">Penc</th>';
				$item_header3 .= '<th class="text-center mw-100">Pert</th>';
				$item_header3 .= '<th class="text-center mw-100">Penc</th>';
				$item_header3 .= '<th class="text-center mw-100">Pert</th>';
				$item_header3 .= '<th class="text-center mw-100">Rank</th>';
			endif;
		}
		if($k && count($v)>1 and (in_array('Total '.$k,$ck_coa) or in_array('All',$ck_coa))):
			$no ++;
			if($no != 1) $item_header .= '<th class="border-none bg-white mw-100"></th>';
			$item_header .= '<th class="text-center" colspan="8">Total '.$k.'</th>';

			if($no != 1) $item_header2 .= '<th class="border-none bg-white mw-100"></th>';
			$item_header2 .= '<th class="text-center">'.month_lang($bulan).'-'.($anggaran->tahun_anggaran - 1).'</th>';
			$item_header2 .= '<th class="text-center" colspan="3">'.month_lang($bulan).'-'.($anggaran->tahun_anggaran).'</th>';
			$item_header2 .= '<th class="text-center">Pert</th>';
			// dikomen karena total tidak menampilkan nilai huruf
			// $item_header2 .= '<th colspan="2" class="text-center mw-100">Nilai Huruf</th>';
			$item_header2 .= '<th colspan="2" class="text-center mw-100">Nilai Angka</th>';
			$item_header2 .= '<th class="text-center"></th>';

			if($no != 1) $item_header3 .= '<th class="border-none bg-white mw-100"></th>';
			$item_header3 .= '<th class="text-center mw-150">Real</th>';
			$item_header3 .= '<th class="text-center mw-150">Renc</th>';
			$item_header3 .= '<th class="text-center mw-150">Real</th>';
			$item_header3 .= '<th class="text-center mw-100">Penc (%)</th>';
			$item_header3 .= '<th class="text-center mw-100">YOY (%)</th>';
			// dikomen karena total tidak menampilkan nilai huruf
			// $item_header3 .= '<th class="text-center mw-100">Penc</th>';
			// $item_header3 .= '<th class="text-center mw-100">Pert</th>';
			$item_header3 .= '<th class="text-center mw-100">Penc</th>';
			$item_header3 .= '<th class="text-center mw-100">Pert</th>';
			$item_header3 .= '<th class="text-center mw-100">Rank</th>';
			$no ++;
		endif;
	}

	// total header
	if(in_array('Total Nilai',$ck_coa) or in_array('All',$ck_coa)):
		$no ++;
		if($no != 1) $item_header .= '<th class="border-none bg-white mw-100"></th>';
		$item_header 	.= '<th class="mw-100" colspan="2"></th>';

		if($no != 1) $item_header2 .= '<th class="border-none bg-white mw-100"></th>';
		$item_header2 	.= '<th class="mw-100 text-center" colspan="2">Total Nilai</th>';

		if($no != 1) $item_header3 .= '<th class="border-none bg-white mw-100"></th>';
		$item_header3 	.= '<th class="mw-100 text-center">Nilai Angka</th>';
		$item_header3 	.= '<th class="mw-150 text-center">Nilai Huruf</th>';
	endif;
	// end total header

	$item_header .= '</tr>';
	$item_header2 .= '</tr>';
	$item_header3 .= '</tr>';

	$item = '';

	$no = 0;
	$data = [
		'ck_coa'=> $ck_coa,
		'group' => $group,
		'bulan'	=> $bulan,
		'nilai'	=> $nilai,
		'nilai_pert'		=> $nilai_pert,
		'nilai_total'		=> $nilai_total,
		'history_current' 	=> $history_current,
		'history' 		 	=> $history,
	];
	foreach ($cab['cabang'] as $k => $v) {
		$kode_cabang = $v['kode_cabang'];
		$dt_more = more($v['kode_cabang'],$cab,$no,0,$data);
		if($dt_more['status']):
			$no 	= $dt_more['no'];
			$dt 	= $dt_more['dt'];
			$item 	.= $dt_more['item'];
		endif;

		$no++;

		$struktur_cabang = 'd-struktur-'.str_replace(' ','',$v['struktur_cabang']);
		$item .= '<tr class="'.$struktur_cabang.'">';
		$item .= '<td>'.$no.'</td>';
		$item .= '<td>'.$v['nama_cabang'].'</td>';
		$item .= '<td>'.$v['struktur_cabang'].'</td>';
		$no_coa = 0;
		foreach ($group as $k2 => $v2) {
			$renc_total = 0;
			$real_total = 0;
			$real2_total= 0;

			$penc_angka_total = 0;
			$pert_angka_total = 0;

			foreach ($v2 as $k3 => $v3) {
				$renc = 0;
				$real = 0;
				$real2 = 0;

				if(in_array(remove_spaces($v3->glwdes),$ck_coa) or in_array('All',$ck_coa)):
					$no_coa++;
					if($no_coa != 1) $item .= '<td class="border-none bg-white"></td>';
				endif;

				if($dt_more['status']):
					$renc = $dt[$v3->coa]['renc'];
					$real = $dt[$v3->coa]['real'];
					$real2 = $dt[$v3->coa]['real2'];
				else:
					$coa_key = multidimensional_search($v['data'], array(
						'coa' => $v3->coa,
					));
					if(strlen($coa_key)>0):
						$field  = 'B_' . sprintf("%02d", $bulan);
						$renc = $v['data'][$coa_key][$field];
					endif;

					$tot = 'TOT_'.$v['kode_cabang'];
					$real_key = multidimensional_search($history_current, array(
						'glwnco' => $v3->coa,
					));
					if(strlen($real_key)>0):
						if(isset($history_current[$real_key][$tot])){
							$minus = $history_current[$real_key]['kali_minus'];
							$real = $history_current[$real_key][$tot];
							$real = kali_minus($real,$minus);
						}
					endif;

					$real_key2 = multidimensional_search($history, array(
						'glwnco' => $v3->coa,
					));
					if(strlen($real_key2)>0):
						if(isset($history[$real_key2][$tot])){
							$minus = $history[$real_key2]['kali_minus'];
							$real2 = $history[$real_key2][$tot];
							$real2 = kali_minus($real2,$minus);
						}
					endif;
				endif;

				$renc_total 	+= checkNumber($renc);
				$real_total 	+= checkNumber($real);
				$real2_total 	+= checkNumber($real2);
				$penc = 0;
				if($real) $penc = ($renc/$real)*100;
				$pert = 0;
				if($real2) $pert = (($real-$real2)/$real2)*100;

				$dt_nilai 		= get_nilai($penc,$nilai,$v['struktur_cabang'],$v3->coa);
				$dt_nilai_pert 	= get_nilai($pert,$nilai_pert,$v['struktur_cabang'],$v3->coa,1);

				$struktur_cabang = strtolower($v['struktur_cabang']);
				// if(in_array($struktur_cabang,['cabang induk','cabang pembantu'])) $struktur_cabang = 'cabang';

				$val_rank = checkNumber($dt_nilai['angka']) + checkNumber($dt_nilai_pert['angka']);
				$CI->arrRank[$v3->coa][$struktur_cabang][$v['kode_cabang'].$bulan][] = $val_rank;

				if(isset($CI->arrTotalCoa[$kode_cabang])):
					$CI->arrTotalCoa[$kode_cabang]['nilai'] += (checkNumber($dt_nilai['angka']) + checkNumber($dt_nilai_pert['angka']));
					$CI->arrTotalCoa[$kode_cabang]['coa']  	+= 1;
				else:
					$CI->arrTotalCoa[$kode_cabang]['nilai'] = (checkNumber($dt_nilai['angka']) + checkNumber($dt_nilai_pert['angka']));
					$CI->arrTotalCoa[$kode_cabang]['coa']   = 1;
				endif;

				$temp_penc = 0;
				if(is_numeric($dt_nilai['angka'])) $temp_penc = $dt_nilai['angka'];
				$penc_angka_total += checkNumber($temp_penc);

				$temp_pert = 0;
				if(is_numeric($dt_nilai_pert['angka'])) $temp_pert = $dt_nilai_pert['angka'];
				$pert_angka_total += checkNumber($temp_pert);

				if(in_array(remove_spaces($v3->glwdes),$ck_coa) or in_array('All',$ck_coa)):
					$item .= '<td class="text-right">'.custom_format(view_report($real2)).'</td>';
					$item .= '<td class="text-right">'.custom_format(view_report($renc)).'</td>';
					$item .= '<td class="text-right">'.custom_format(view_report($real)).'</td>';
					$item .= '<td class="text-right">'.custom_format($penc,false,2).'</td>';
					$item .= '<td class="text-right">'.custom_format($pert,false,2).'</td>';
					$item .= '<td class="text-center">'.$dt_nilai['nilai'].'</td>';
					$item .= '<td class="text-center">'.$dt_nilai_pert['nilai'].'</td>';
					$item .= '<td class="text-center">'.custom_format($dt_nilai['angka'],false,2).'</td>';
					$item .= '<td class="text-center">'.custom_format($dt_nilai_pert['angka'],false,2).'</td>';
					$item .= '<td class="text-center rank-'.$v3->coa.$v['kode_cabang'].$bulan.'"></td>';
				endif;
			}
			if($k2 && count($v2)>1):
				if(in_array('Total '.$k2,$ck_coa) or in_array('All',$ck_coa)):
					$no_coa++;
					if($no_coa != 1) $item .= '<td class="border-none bg-white"></td>';
				endif;

				$penc = 0;
				if($real_total) $penc = ($renc_total/$real_total)*100;
				$pert = 0;
				if($real2_total) $pert = (($real_total-$real2_total)/$real2_total)*100;

				$dt_nilai = get_nilai($penc,$nilai,$v['struktur_cabang'],$k2);
				$dt_nilai_pert 	= get_nilai($pert,$nilai_pert,$v['struktur_cabang'],$k2,1);

				$struktur_cabang = strtolower($v['struktur_cabang']);
				// if(in_array($struktur_cabang,['cabang induk','cabang pembantu'])) $struktur_cabang = 'cabang';
				$val_rank = checkNumber($dt_nilai['angka']) + checkNumber($dt_nilai_pert['angka']);
				$CI->arrRank['total'.$v3->coa][$struktur_cabang][$v['kode_cabang'].$bulan][] = $val_rank;

				if(in_array('Total '.$k2,$ck_coa) or in_array('All',$ck_coa)):
					$item .= '<td class="text-right">'.custom_format(view_report($real2_total)).'</td>';
					$item .= '<td class="text-right">'.custom_format(view_report($renc_total)).'</td>';
					$item .= '<td class="text-right">'.custom_format(view_report($real_total)).'</td>';
					$item .= '<td class="text-right">'.custom_format($penc,false,2).'</td>';
					$item .= '<td class="text-right">'.custom_format($pert,false,2).'</td>';
					// dikomen karena total tidak menampilkan nilai huruf
					// $item .= '<td class="text-center">'.$dt_nilai['nilai'].'</td>';
					// $item .= '<td class="text-center">'.$dt_nilai_pert['nilai'].'</td>';
					$item .= '<td class="text-center">'.custom_format($penc_angka_total,false,2).'</td>';
					$item .= '<td class="text-center">'.custom_format($pert_angka_total,false,2).'</td>';
					$item .= '<td class="text-center rank-total'.$v3->coa.$v['kode_cabang'].$bulan.'"></td>';
				endif;
			endif;
		}

		// total body
		$total_angka = (float) $CI->arrTotalCoa[$kode_cabang]['nilai'];
		$total_coa 	 = (float) $CI->arrTotalCoa[$kode_cabang]['coa'];
		$total_angka = ($total_angka/$total_coa);

		$dt_nilai_total 	= get_nilai($total_angka,$nilai_total,$v['struktur_cabang'],'xx01',2);

		if(in_array('Total Nilai',$ck_coa) or in_array('All',$ck_coa)):
			$no_coa++;
			if($no_coa != 1) $item .= '<td class="border-none bg-white"></td>';
			$item .= '<td class="text-right">'.custom_format($total_angka,false,2).'</td>';
			$item .= '<td class="text-center">'.$dt_nilai_total['nilai'].'</td>';
		endif;
		// end total body

		$item .= '</tr>';
	}

	function more($id,$cab,$no,$count,$data){
		$CI    = get_instance();
		$ck_coa= $data['ck_coa'];
		$group = $data['group'];
		$bulan = $data['bulan'];
		$nilai = $data['nilai'];
		$nilai_pert 	 = $data['nilai_pert'];
		$nilai_total 	 = $data['nilai_total'];
		$history_current = $data['history_current'];
		$history 		 = $data['history'];

		$status = false;
		$item 	= '';
		$dt 	= [];

		if(isset($cab[$id])):
			$status = true;
			$count2 = ($count+1);
			foreach ($cab[$id] as $k => $v) {
				$dt_more = more($v['kode_cabang'],$cab,$no,$count2,$data);
				if($dt_more['status']):
					$no 	= $dt_more['no'];
					$dt2 	= $dt_more['dt'];
					$item 	.= $dt_more['item'];
				endif;

				$nama_cabang = $v['nama_cabang'];
				$kode_cabang = $v['kode_cabang'];
				$no++;

				$struktur_cabang = 'd-struktur-'.str_replace(' ','',$v['struktur_cabang']);
				$item .= '<tr class="'.$struktur_cabang.'">';
				$item .= '<td>'.$no.'</td>';
				$item .= '<td class="sb-'.$count2.'">'.$nama_cabang.'</td>';
				$item .= '<td>'.$v['struktur_cabang'].'</td>';
				$no_coa = 0;
				foreach ($group as $k2 => $v2) {
					$renc_total = 0;
					$real_total = 0;
					$real2_total= 0;

					$penc_angka_total = 0;
					$pert_angka_total = 0;

					foreach ($v2 as $k3 => $v3) {
						$renc = 0;
						$real = 0;
						$real2= 0;

						if(in_array(remove_spaces($v3->glwdes),$ck_coa) or in_array('All',$ck_coa)):
							$no_coa++;
							if($no_coa != 1) $item .= '<td class="border-none bg-white"></td>';
						endif;

						if($dt_more['status']):
							$renc = $dt2[$v3->coa]['renc'];
							$real = $dt2[$v3->coa]['real'];
							$real2 = $dt2[$v3->coa]['real2'];
						else:
							$coa_key = multidimensional_search($v['data'], array(
								'coa' => $v3->coa,
							));
							if(strlen($coa_key)>0):
								$field  = 'B_' . sprintf("%02d", $bulan);
								$renc = $v['data'][$coa_key][$field];
							endif;

							$tot = 'TOT_'.$v['kode_cabang'];
							$real_key = multidimensional_search($history_current, array(
								'glwnco' => $v3->coa,
							));
							if(strlen($real_key)>0):
								if(isset($history_current[$real_key][$tot])){
									$minus = $history_current[$real_key]['kali_minus'];
									$real = $history_current[$real_key][$tot];
									$real = kali_minus($real,$minus);
								}
							endif;

							$real_key2 = multidimensional_search($history, array(
								'glwnco' => $v3->coa,
							));
							if(strlen($real_key2)>0):
								if(isset($history[$real_key2][$tot])){
									$minus = $history[$real_key2]['kali_minus'];
									$real2 = $history[$real_key2][$tot];
									$real2 = kali_minus($real2,$minus);
								}
							endif;

						endif;
						if(isset($dt[$v3->coa]['renc'])) $dt[$v3->coa]['renc'] += checkNumber($renc); else $dt[$v3->coa]['renc'] = checkNumber($renc);
						if(isset($dt[$v3->coa]['real'])) $dt[$v3->coa]['real'] += checkNumber($real); else $dt[$v3->coa]['real'] = checkNumber($real);
						if(isset($dt[$v3->coa]['real2'])) $dt[$v3->coa]['real2'] += checkNumber($real2); else $dt[$v3->coa]['real2'] = checkNumber($real2);

						$renc_total 	+= checkNumber($renc);
						$real_total 	+= checkNumber($real);
						$real2_total 	+= checkNumber($real2);
						$penc = 0;
						if($real) $penc = (checkNumber($renc)/checkNumber($real))*100;
						$pert = 0;
						if($real2) $pert = (($real-$real2)/$real2)*100;

						$dt_nilai 		= get_nilai($penc,$nilai,$v['struktur_cabang'],$v3->coa);
						$dt_nilai_pert 	= get_nilai($pert,$nilai_pert,$v['struktur_cabang'],$v3->coa,1);

						$struktur_cabang = strtolower($v['struktur_cabang']);
						// if(in_array($struktur_cabang,['cabang induk','cabang pembantu'])) $struktur_cabang = 'cabang';
						$val_rank = checkNumber($dt_nilai['angka']) + checkNumber($dt_nilai_pert['angka']);
						$CI->arrRank[$v3->coa][$struktur_cabang][$v['kode_cabang'].$bulan][] = $val_rank;

						if(isset($CI->arrTotalCoa[$kode_cabang])):
							$CI->arrTotalCoa[$kode_cabang]['nilai'] += (checkNumber($dt_nilai['angka']) + checkNumber($dt_nilai_pert['angka']));
							$CI->arrTotalCoa[$kode_cabang]['coa']  	+= 1;
						else:
							$CI->arrTotalCoa[$kode_cabang]['nilai'] = (checkNumber($dt_nilai['angka']) + checkNumber($dt_nilai_pert['angka']));
							$CI->arrTotalCoa[$kode_cabang]['coa']   = 1;
						endif;

						$temp_penc = 0;
						if(is_numeric($dt_nilai['angka'])) $temp_penc = $dt_nilai['angka'];
						$penc_angka_total += checkNumber($temp_penc);

						$temp_pert = 0;
						if(is_numeric($dt_nilai_pert['angka'])) $temp_pert = $dt_nilai_pert['angka'];
						$pert_angka_total += checkNumber($temp_pert);

						if(in_array(remove_spaces($v3->glwdes),$ck_coa) or in_array('All',$ck_coa)):
							$item .= '<td class="text-right">'.custom_format(view_report($real2)).'</td>';
							$item .= '<td class="text-right">'.custom_format(view_report($renc)).'</td>';
							$item .= '<td class="text-right">'.custom_format(view_report($real)).'</td>';
							$item .= '<td class="text-right">'.custom_format($penc,false,2).'</td>';
							$item .= '<td class="text-right">'.custom_format($pert,false,2).'</td>';
							$item .= '<td class="text-center">'.$dt_nilai['nilai'].'</td>';
							$item .= '<td class="text-center">'.$dt_nilai_pert['nilai'].'</td>';
							$item .= '<td class="text-center">'.custom_format($dt_nilai['angka'],false,2).'</td>';
							$item .= '<td class="text-center">'.custom_format($dt_nilai_pert['angka'],false,2).'</td>';
							$item .= '<td class="text-center rank-'.$v3->coa.$v['kode_cabang'].$bulan.'"></td>';
						endif;
					}
					if($k2 && count($v2)>1):
						if(in_array('Total '.$k2,$ck_coa) or in_array('All',$ck_coa)):
							$no_coa++;
							if($no_coa != 1) $item .= '<td class="border-none bg-white"></td>';
						endif;

						$penc = 0;
						if($real_total) $penc = ($renc_total/$real_total)*100;
						$pert = 0;
						if($real2_total) $pert = (($real_total-$real2_total)/$real2_total)*100;

						$dt_nilai = get_nilai($penc,$nilai,$v['struktur_cabang'],$k2);
						$dt_nilai_pert 	= get_nilai($pert,$nilai_pert,$v['struktur_cabang'],$k2,1);

						$struktur_cabang = strtolower($v['struktur_cabang']);
						// if(in_array($struktur_cabang,['cabang induk','cabang pembantu'])) $struktur_cabang = 'cabang';
						$val_rank = checkNumber($dt_nilai['angka']) + checkNumber($dt_nilai_pert['angka']);
						$CI->arrRank['total'.$v3->coa][$struktur_cabang][$v['kode_cabang'].$bulan][] = $val_rank;

						if(in_array('Total '.$k2,$ck_coa) or in_array('All',$ck_coa)):
							$item .= '<td class="text-right">'.custom_format(view_report($real2_total)).'</td>';
							$item .= '<td class="text-right">'.custom_format(view_report($renc_total)).'</td>';
							$item .= '<td class="text-right">'.custom_format(view_report($real_total)).'</td>';
							$item .= '<td class="text-right">'.custom_format($penc,false,2).'</td>';
							$item .= '<td class="text-right">'.custom_format($pert,false,2).'</td>';
							// dikomen karena total tidak menampilkan nilai huruf
							// $item .= '<td class="text-center">'.$dt_nilai['nilai'].'</td>';
							// $item .= '<td class="text-center">'.$dt_nilai_pert['nilai'].'</td>';
							$item .= '<td class="text-center">'.custom_format($penc_angka_total,false,2).'</td>';
							$item .= '<td class="text-center">'.custom_format($pert_angka_total,false,2).'</td>';
							$item .= '<td class="text-center rank-total'.$v3->coa.$v['kode_cabang'].$bulan.'"></td>';
						endif;
					endif;
				}

				// total body
				$total_angka = (float) $CI->arrTotalCoa[$kode_cabang]['nilai'];
				$total_coa 	 = (float) $CI->arrTotalCoa[$kode_cabang]['coa'];
				$total_angka = ($total_angka/$total_coa);

				$dt_nilai_total 	= get_nilai($total_angka,$nilai_total,$v['struktur_cabang'],'xx01',2);

				if(in_array('Total Nilai',$ck_coa) or in_array('All',$ck_coa)):
					$no_coa++;
					if($no_coa != 1) $item .= '<td class="border-none bg-white"></td>';
					$item .= '<td class="text-right">'.custom_format($total_angka,false,2).'</td>';
					$item .= '<td class="text-center">'.$dt_nilai_total['nilai'].'</td>';
				endif;
				// end total body

				$item .= '</tr>';
			}
		endif;

		return [
			'status' => $status,
			'item'	 => $item,
			'no'	 => $no,
			'dt'	 => $dt,
		];
	}
	function get_nilai($p1,$nilai,$p2,$p3,$p4=0){
		// get_nilai($penc,$nilai,$v['struktur_cabang'],$v3->coa);
		$CI        	   = get_instance();
		$data['nilai'] = '';
		$data['warna'] = '';
		$data['angka'] = '';
		foreach ($nilai as $k => $v) {
			if(isset($v['coa'])):
				if($p3 == $v['coa']):
					$formula = $v['formula'];
					$formula = str_replace('$$value', $p1, $formula);
					$condition = "return ".$formula.";";
					$res = eval($condition);
					if($res):
						$warna = '#ccc';
						if($v['warna']) $warna = $v['warna'];
						$data['angka'] = $v['nilai_bobot'];
						$data['nilai'] = $v['nama'].' <div class="float-left"><span class="color" style="background-color:'.$warna.'"></span>'.'</div>';
						$id = $v['nama'];
						if(isset($CI->total_nilai[$p4][$p2][$p3][$id])){ $CI->total_nilai[$p4][$p2][$p3][$id] += 1; }else{ $CI->total_nilai[$p4][$p2][$p3][$id] = 1; }
					endif;
				endif;
			else:
				$formula = $v['formula'];
				$formula = str_replace('$$value', $p1, $formula);
				$condition = "return ".$formula.";";
				$res = eval($condition);
				if($res):
					$warna = '#ccc';
					if($v['warna']) $warna = $v['warna'];
					$data['angka'] = $v['nilai'];
					$data['nilai'] = $v['nama'].' <div class="float-left"><span class="color" style="background-color:'.$warna.'"></span></div>';
					$id = $v['id'];
					if(isset($CI->total_nilai[$p4][$p2][$p3][$id])){ $CI->total_nilai[$p4][$p2][$p3][$id] += 1; }else{ $CI->total_nilai[$p4][$p2][$p3][$id] = 1; }
				endif;
			endif;
		}
		return $data;
	}

	$item_header_nilai = '<tr>';
	$item_header_nilai .= '<th>'.lang('no').'</th>';
	$item_header_nilai .= '<th class="mw-150">'.lang('kantor').'</th>';

	$item_header_nilai2 = '<tr>';
	$item_header_nilai2 .= '<th></th>';
	$item_header_nilai2 .= '<th></th>';
	$no = 0;
	foreach ($group as $k => $v) {
		foreach ($v as $k2 => $v2) {
			if(in_array(remove_spaces($v2->glwdes),$ck_coa) or in_array('All',$ck_coa)):
				$no++;
				if($no != 1) $item_header_nilai .= '<th class="border-none bg-white mw-100"></th>';
				if($no != 1) $item_header_nilai2 .= '<th class="border-none bg-white mw-100"></th>';
				$item_header_nilai .= '<th class="text-center" colspan="'.count($arrNamaPenc).'">'.$v2->coa.' - '.remove_spaces($v2->glwdes).'</th>';
				foreach ($arrNamaPenc as $v3) {
					$item_header_nilai2 .= '<th class="text-center mw-100">'.$v3.'</tg>';
				}
			endif;
		}
		if($k && count($v)>1 and (in_array('Total '.$k,$ck_coa) or in_array('All',$ck_coa))):
			// $no++;
			// if($no != 1) $item_header_nilai .= '<th class="border-none bg-white mw-100"></th>';
			// if($no != 1) $item_header_nilai2 .= '<th class="border-none bg-white mw-100"></th>';
			// $item_header_nilai .= '<th class="text-center" colspan="'.count($arrNamaPert).'">Total '.$k.'</th>';
			// foreach ($arrNamaPert as $v3) {
			// 	$item_header_nilai2 .= '<th class="text-center mw-100">'.$v3.'</tg>';
			// }
		endif;
	}
	$item_header_nilai .= '</tr>';
	$item_header_nilai2 .= '</tr>';

	// Nilai Pencapaian
	$item_nilai = '';
	$no = 0;
	$total_nilai = $CI->total_nilai;
	foreach ($total_nilai[0] as $k => $v) {
		$no++;
		$struktur_cabang = 'd-struktur-'.str_replace(' ','',$k);
		$item_nilai .= '<tr class="'.$struktur_cabang.'">';
		$item_nilai .= '<td>'.$no.'</td>';
		$item_nilai .= '<td>'.$k.'</td>';
		$no_coa = 0;
		foreach ($group as $k2 => $v2) {
			foreach ($v2 as $k3 => $v3) {
				if(in_array(remove_spaces($v3->glwdes),$ck_coa) or in_array('All',$ck_coa)):
					$no_coa++;
					if($no_coa != 1) $item_nilai .= '<td class="border-none bg-white"></td>';
					
					$arrNama = [];
					foreach ($nilai as $k4 => $v4) {
						if(!in_array($v4['nama'],$arrNama)):
							array_push($arrNama,$v4['nama']);
							$n = 0;
							$coa = $v3->coa;
							if(isset($v[$coa][$v4['nama']])) $n = $v[$coa][$v4['nama']]; $item_nilai .= '<td class="text-center">'.custom_format($n).'</td>';
						endif;
					}
				endif;
			}
			// total tidak memakai nilai huruf
			// if($k2 && count($v2)>1 and (in_array('Total '.$k2,$ck_coa) or in_array('All',$ck_coa))):
			// 	$no_coa++;
			// 	if($no_coa != 1) $item_nilai .= '<td class="border-none bg-white"></td>';
			// 	$arrNama = [];
			// 	foreach ($nilai as $k4 => $v4) {
			// 		if(!in_array($v4['nama'],$arrNama)):
			// 			array_push($arrNama,$v4['nama']);
			// 			$n = 0;
			// 			$coa = $k2;
			// 			if(isset($v[$coa][$v4['nama']])) $n = $v[$coa][$v4['nama']]; $item_nilai .= '<td class="text-center">'.custom_format($n).'</td>';
			// 		endif;
			// 	}
			// endif;
		}
		$item_nilai .= '</tr>';
	}

	// Nilai Pertumbuhan
	$item_nilai_pert = '';
	$no = 0;
	$total_nilai = $CI->total_nilai;
	foreach ($total_nilai[1] as $k => $v) {
		$no++;
		$struktur_cabang = 'd-struktur-'.str_replace(' ','',$k);
		$item_nilai_pert .= '<tr class="'.$struktur_cabang.'">';
		$item_nilai_pert .= '<td>'.$no.'</td>';
		$item_nilai_pert .= '<td>'.$k.'</td>';
		$no_coa = 0;
		foreach ($group as $k2 => $v2) {
			foreach ($v2 as $k3 => $v3) {
				if(in_array(remove_spaces($v3->glwdes),$ck_coa) or in_array('All',$ck_coa)):
					$no_coa++;
					if($no_coa != 1) $item_nilai_pert .= '<td class="border-none bg-white"></td>';

					$arrNama = [];
					foreach ($nilai_pert as $k4 => $v4) {
						if(!in_array($v4['nama'],$arrNama)):
							array_push($arrNama,$v4['nama']);

							$n = 0;
							$coa = $v3->coa;
							if(isset($v[$coa][$v4['nama']])) $n = $v[$coa][$v4['nama']]; $item_nilai_pert .= '<td class="text-center">'.custom_format($n).'</td>';
						endif;	
					}
				endif;
			}
			// total tidak memakai nilai huruf
			// if($k2 && count($v2)>1 and (in_array('Total '.$k2,$ck_coa) or in_array('All',$ck_coa))):
			// 	$no_coa++;
			// 	if($no_coa != 1) $item_nilai_pert .= '<td class="border-none bg-white"></td>';

			// 	$arrNama = [];
			// 	foreach ($nilai_pert as $k4 => $v4) {
			// 		if(!in_array($v4['nama'],$arrNama)):
			// 			array_push($arrNama,$v4['nama']);

			// 			$n = 0;
			// 			$coa = $k2;
			// 			if(isset($v[$coa][$v4['nama']])) $n = $v[$coa][$v4['nama']]; $item_nilai_pert .= '<td class="text-center">'.custom_format($n).'</td>';
			// 		endif;
			// 	}
			// endif;
		}
		$item_nilai_pert .= '</tr>';
	}
?>
<div class="col-sm-12 col-12 d-content" id="d-<?= $cabang->kode_cabang.'-'.$bulan ?>">
	<div class="card">
		<div class="card-header text-center">
			KINERJA OPERASIONAL <?= $cabang->nama_cabang ?> Bank Jateng <br>
			<?= $anggaran->keterangan ?><br>
			Bulan <?= month_lang($bulan) ?><br>
			(<?= get_view_report() ?>)
		</div>
		<div class="card-body">
			<div class="table-responsive tab-pane fade active show height-window" id="tbl-data1" data-height="100">
				<table class="table table-striped table-bordered table-app table-hover">
					<thead>
					<?= $item_header.$item_header2.$item_header3 ?>
					</thead>
					<tbody><?= $item ?></tbody>
				</table>
			</div>
		</div>	
	</div>
	<div class="card mt-3">
		<div class="card-header text-center">
			PENCAPAIAN DARI RENCANA BERDASARKAN KANTOR CABANG
		</div>
		<div class="card-body">
			<div class="table-responsive tab-pane fade active show">
				<table class="table table-striped table-bordered table-app table-hover" id="tbl-data2">
					<thead>
					<?= $item_header_nilai.$item_header_nilai2 ?>
					</thead>
					<tbody><?= $item_nilai ?></tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="card mt-3">
		<div class="card-header text-center">
			PERTUMBUHAN DARI RENCANA BERDASARKAN KANTOR CABANG
		</div>
		<div class="card-body">
			<div class="table-responsive tab-pane fade active show">
				<table class="table table-striped table-bordered table-app table-hover" id="tbl-data3">
					<thead>
					<?= $item_header_nilai.$item_header_nilai2 ?>
					</thead>
					<tbody><?= $item_nilai_pert ?></tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<?php
	$this->session->set_userdata(['ranking' => $CI->arrRank]);
?>