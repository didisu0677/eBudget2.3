<?php
$CI        	  	= get_instance();
$CI->akses_ubah = $akses_ubah;

$item = '';
$edit = 'contenteditable="true"';
if(user('id_group') == '5'){
	$edit = 'contenteditable="false"';
}
// $item = '<td><div style="min-height: 10px; width: 100%; overflow: hidden;" contenteditable="true" class="edit-value text-right" data-name="'.$v_field.'" data-id="'.$m0->glwnco.'" data-value="'.$m0->$v_field.'">'.$content.'</div></td>';
$bg = ' class="text-right bg-grey"';
$bd = ' class="border-none bg-white"';
$tr = ' class="text-right "';
foreach ($coa as $k => $v) {
	$item2 = '';
	$dt2 = [];
	$minus = $v->kali_minus;
	if(isset($detail['1'][$v->glwnco])){
		foreach ($detail['1'][$v->glwnco] as $k2 => $v2) {
			$item3 = '';
			$dt3 = [];
			$minus2 = $v2->kali_minus;
			if($akses_ubah && ($v2->glwnco == '5571000' || $v2->glwnco == '5572000') ){
				$edit2 = 'contenteditable="false"';
				$editBulan = 'contenteditable="true"';
			}else {
				$edit2 = 'contenteditable="false"';
				$editBulan = 'contenteditable="false"';
			}

			if($akses_ubah){
				$edit2 = 'contenteditable="true"';
				$editBulan = 'contenteditable="true"';
			}
			if(isset($detail['2'][$v2->glwnco])){
				foreach ($detail['2'][$v2->glwnco] as $k3 => $v3) {
					$item4 = '';
					$dt4   = [];
					$minus3 = $v3->kali_minus;
					if(($v3->glwnco == '5573012' || $v3->glwnco == '5573013') && $akses_ubah){
						$edit = 'contenteditable="true"';
					}else {
						$edit = 'contenteditable="false"';
					}
					if(($v3->glwnco == '5573014' || $v3->glwnco == '5573011') && $akses_ubah){
						$editBulan = 'contenteditable="true"';
					}else {
						$editBulan = 'contenteditable="false"';
					}

					if($akses_ubah == 1){
						$edit = 'contenteditable="true"';
						$editBulan = 'contenteditable="true"';
					}
					if(isset($detail['3'][$v3->glwnco])){
						foreach ($detail['3'][$v3->glwnco] as $k4 => $v4) {
							$item5 = '';
							$dt5   = [];
							$minus4 = $v4->kali_minus;
							$edit = 'contenteditable="false"';
							if($akses_ubah){
								$edit = 'contenteditable="true"';
							}
							if(isset($detail['4'][$v4->glwnco])){
								foreach ($detail['4'][$v4->glwnco] as $k5 => $v5) {
									$item6 	= '';
									$dt6 	= [];
									$minus5 = $v5->kali_minus;
									$edit = 'contenteditable="false"';
									if($akses_ubah){
										$edit = 'contenteditable="true"';
									}
									if(isset($detail['5'][$v5->glwnco])){
										foreach ($detail['5'][$v5->glwnco] as $k6 => $v6) {
											$s_keterangan = ck_keterangan($coa_keterangan,$v6->glwnco,$access_additional,$akses_ubah);
											
											$item6 .= '<tr>';

											$file = '';
											if(in_array($v6->glwnco,$arr_coa_file)):
												$file = '<br><button type="button" class="btn btn-info btn-file" data-id="'.$v6->glwnco.'" title="File"><i class="fa-download"></i></button>';
											endif;

											$item6 .= '<td class="button text-center">'.$v6->glwnob.'</td>';
											$item6 .= '<td class="button text-center">'.$v6->glwnco.$file.'</td>';
											$item6 .= '<td class="sb-6">'.remove_spaces($v6->glwdes).'</td>';
											$bln_trakhir = $v6->{'TOT_'.$cabang};
											$value = (float) $bln_trakhir/10;
											$minus6 = $v6->kali_minus;
											$dataSaved = ['kode_anggaran' => $anggaran->kode_anggaran,'kode_cabang' => $cabang, 'glwnco' => $v6->glwnco];
											for ($i=1; $i <=12 ; $i++) {
												$val = $value * $i;
												if(isset($dt6[$i])){ $dt6[$i] += $val; }else{ $dt6[$i] = $val; }
												$val = round_value($val);

												$dataSaved['bulan_b'.$i] = $val;
												$item6 .= '<td class="'.ck_edited($v6->cabang_edit,'bulan_b'.$i).'">'.check_data($val,$minus6).'</td>';
											}
											checkForUpdate($dataSaved);
											$item6 .= '<td'.$bd.'></td>';
											if(strlen($v6->biaya_bulan)>0){
												$valueTxt = $v6->biaya_bulan;
											}
											if(strlen($v6->biaya_tahun)>0){
												$bln_trakhir = $v6->biaya_tahun;
											}
											$valueTxt = round_value($valueTxt);

											$item6 .= '<td class="'.ck_edited($v6->cabang_edit,'biaya_bulan').'"><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$edit.' class="edit-value text-right" data-name="biaya_bulan" data-id="'.$v6->glwnco.'" data-value="'.$valueTxt.'">'.$valueTxt.'</div></td>';
											$item6 .= '<td class="'.ck_edited($v6->cabang_edit,'biaya_tahun').'"><div style="min-height: 10px; width: 100%; overflow: hidden;" contenteditable="false" class="edit-value text-right" data-name="biaya_tahun" data-id="'.$v6->glwnco.'" data-value="'.$bln_trakhir.'">'.$bln_trakhir.'</div></td>';
											$item6 .= '</tr>';
										}
										$bln_trakhir = '';
										$value = '';
										$valueTxt = '';
									}else{
										$bln_trakhir = $v5->{'TOT_'.$cabang};
										$value 		 = (float) $bln_trakhir/10;
										$valueTxt 	 = check_data($value,$minus5);
										$bln_trakhir = check_data($bln_trakhir,$minus5);
									}

									$item5 .= '<tr>';

									$file = '';
									if(in_array($v5->glwnco,$arr_coa_file)):
										$file = '<br><button type="button" class="btn btn-info btn-file" data-id="'.$v5->glwnco.'" title="File"><i class="fa-download"></i></button>';
									endif;

									$item5 .= '<td class="button text-center">'.$v5->glwnob.'</td>';
									$item5 .= '<td class="button text-center">'.$v5->glwnco.$file.'</td>';
									$item5 .= '<td class="sb-5">'.remove_spaces($v5->glwdes).'</td>';
									$dataSaved = ['kode_anggaran' => $anggaran->kode_anggaran,'kode_cabang' => $cabang, 'glwnco' => $v5->glwnco];
									for ($i=1; $i <= 12 ; $i++) { 
										if(count($dt6)>0){ $val = $dt6[$i]; }
										else{ $val = $value * $i; }
										$val = round_value($val);

										$item5 .= '<td class="'.ck_edited($v5->cabang_edit,'bulan_b'.$i).'">'.check_data($val,$minus5).'</td>';
										if(isset($dt5[$i])){ $dt5[$i] += $val; }else{ $dt5[$i] = $val; }
										$dataSaved['bulan_b'.$i] = $val;
									}
									checkForUpdate($dataSaved);
									$item5 .= '<td'.$bd.'></td>';
									if(strlen($v5->biaya_bulan)>0){
										$valueTxt = $v5->biaya_bulan;
									}
									if(strlen($v5->biaya_tahun)>0){
										$bln_trakhir = $v5->biaya_tahun;
									}
									$valueTxt = round_value($valueTxt);
									$item5 .= '<td class="'.ck_edited($v5->cabang_edit,'biaya_bulan').'"><div style="min-height: 10px; width: 100%; overflow: hidden;" contenteditable="false" class="edit-value text-right" data-name="biaya_bulan" data-id="'.$v6->glwnco.'" data-value="'.$valueTxt.'">'.$valueTxt.'</div></td>';
									$item5 .= '<td class="'.ck_edited($v5->cabang_edit,'biaya_tahun').'"><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$edit.' class="edit-value text-right" data-name="biaya_tahun" data-id="'.$v6->glwnco.'" data-value="'.$bln_trakhir.'">'.$bln_trakhir.'</div></td>';
									$item6 .= '</tr>';
									$item5 .= '</tr>';
									$item5 .= $item6;
								}
								$bln_trakhir = '';
								$value = '';
								$valueTxt = '';
							}else{
								$bln_trakhir = $v4->hasil;
								$value 		 = (float) $bln_trakhir/ $bulan_terakhir;
								$valueTxt 	 = check_data($value,$minus4);
								$getTahun	 = check_data($value*12, $minus4);
								$bln_trakhir = check_data($bln_trakhir,$minus4);
							}

							$item4 .= '<tr>';

							$file = '';
							if(in_array($v4->glwnco,$arr_coa_file)):
								$file = '<br><button type="button" class="btn btn-info btn-file" data-id="'.$v4->glwnco.'" title="File"><i class="fa-download"></i></button>';
							endif;

							$item4 .= '<td class="button text-center">'.$v4->glwnob.'</td>';
							$item4 .= '<td class="button text-center">'.$v4->glwnco.$file.'</td>';
							$item4 .= '<td class="sb-4">'.remove_spaces($v4->glwdes).'</td>';
							$dataSaved = ['kode_anggaran' => $anggaran->kode_anggaran,'kode_cabang' => $cabang, 'glwnco' => $v4->glwnco];
							for ($i=1; $i <= 12 ; $i++) {
								if(count($dt5)>0){ 
									$val = $dt5[$i];
									$bulanb = "bulan_b".$i;
									if(strlen($v4->$bulanb)>0){
										$val = $v4->$bulanb ;
									}
									$val = round_value($val);

									$item4 .= '<td class="'.ck_edited($v4->cabang_edit,'bulan_b'.$i).'" '.$tr.'><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$edit.' class="edit-value text-right" data-name="bulan_b'.$i.'" data-id="'.$v4->glwnco.'" data-value="'.$val.'">'.check_data($val,$minus4).'</div></td>';
								}
								else{ 
									$val = $value ; 

									$bulanb = "bulan_b".$i;
									if(strlen($v4->$bulanb)>0){
										$val = $v4->$bulanb ;
									}
									$val = round_value($val);

									$item4 .= '<td class="'.ck_edited($v4->cabang_edit,'bulan_b'.$i).'"><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$edit.' class="edit-value text-right" data-name="bulan_b'.$i.'" data-id="'.$v4->glwnco.'" data-value="'.$val.'">'.check_data($val,$minus4).'</div></td>'; 
								} 
								if(isset($dt4[$i])){ $dt4[$i] += $val; }else{ $dt4[$i] = $val; }
								$dataSaved['bulan_b'.$i] = $val;
							}
							checkForUpdate($dataSaved);
							$item4 .= '<td'.$bd.'></td>';
							if(strlen($v4->biaya_bulan)>0){
								$valueTxt = $v4->biaya_bulan;

								check_data($valueTxt,0);
							}
							if(strlen($v4->biaya_tahun)>0){
								$bln_trakhir = $v4->biaya_tahun;

								check_data($bln_trakhir,0);	
							}
							$valueTxt = round_value($valueTxt);
							$item4 .= '<td class="'.ck_edited($v4->cabang_edit,'biaya_bulan').'"><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$edit.' class="edit-value edit-bulan text-right" data-name="biaya_bulan" data-id="'.$v4->glwnco.'" data-value="'.$valueTxt.'">'.$valueTxt.'</div></td>';
							$item4 .= '<td class="'.ck_edited($v4->cabang_edit,'biaya_tahun').'"><div style="min-height: 10px; width: 100%; overflow: hidden;" contenteditable="false" class="edit-value text-right" data-name="biaya_tahun" data-id="'.$v4->glwnco.'" data-value="'.$bln_trakhir.'">'.$bln_trakhir.'</div></td>';
							$item4 .= '<td'.$bd.'></td>';
							$item4 .= '<td'.$bg.'>'.check_data($val,$minus4).'</td>';
							$item4 .= '<td'.$bg.'>'.$v4->hasil2.'</td>';
							$item4 .= '<td'.$bg.'>'.$v4->hasil.'</td>';
							$item4 .= '</tr>';
							$item4 .= $item5;
						}
						$bln_trakhir = '';
						$value = '';
						$valueTxt = '';
					}else{
						$bln_trakhir = $v3->hasil;
						$value 		 = (float) $bln_trakhir/$bulan_terakhir;
						if(strlen($v3->biaya_bulan)>0){
							$value = $v3->biaya_bulan;
						}
						$valueTxt 	 = check_data($value,$minus3);
						$valueTxt 	 = round_value($valueTxt);
						$getTahun	 = check_data($value*12, $minus3);
						$bln_trakhir = check_data($bln_trakhir,$minus3);
					}

					$item3 .= '<tr>';

					$file = '';
					if(in_array($v3->glwnco,$arr_coa_file)):
						$file = '<br><button type="button" class="btn btn-info btn-file" data-id="'.$v3->glwnco.'" title="File"><i class="fa-download"></i></button>';
					endif;

					$s_keterangan = ck_keterangan($coa_keterangan,$v3->glwnco,$access_additional,$akses_ubah);
					$CI->s_keterangan = $s_keterangan;

					$key_core = multidimensional_search($history_core,[
						'glwnco' => $v3->glwnco
					]);

					$item3 .= '<td class="button text-center">'.$v3->glwnob.'</td>';
					$item3 .= '<td class="button text-center">'.$v3->glwnco.$file.'</td>';
					$item3 .= '<td class="sb-3">'.remove_spaces($v3->glwdes).'</td>';
					$dataSaved = ['kode_anggaran' => $anggaran->kode_anggaran,'kode_cabang' => $cabang, 'glwnco' => $v3->glwnco];
					$biaya_tahun3 = 0;
					for ($i=1; $i <= 12 ; $i++) {
						$a = array_search($i, array_column($detail_tahun, 'bulan'));
						if(count($dt4)>0){
							$val = $dt4[$i];
							$val = round_value($val);

							// $bulanb = "bulan_b".$i;
							// if(!empty($v3->$bulanb)){
							// 	$val = $v3->$bulanb;
							// }

							// if($v3->last_edit == '1'){
							// 	$val = $v3->{'bulan_b'.$i};
							// }else {
							// 	if(!empty($v3->biaya_bulan)){
							// 		$val = $v3->biaya_bulan;
							// 	}
							// }

							// if($detail_tahun[$a]['sumber_data'] == '1'){
							// 	$val = $v3->{'core'.$i};
							// 	$b = $i-1;
							// 	if($a-1 >= 0){
							// 		if($detail_tahun[$a-1]['sumber_data'] == '1'){
							// 			$val = $val - $v3->{'core'.$b};
							// 		}
							// 	}
							// }

							$keteranganTxt = '';
							$item3 .=  '<td class="'.ck_edited($v3->cabang_edit,'bulan_b'.$i).'" '.$tr.'><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$edit.' class="edit-value text-right" data-name="bulan_b'.$i.'" data-id="'.$v3->glwnco.'" data-value="'.$val.'">'.check_data($val,$minus3).'</div></td>';
						}
						else{ 
							$val = $value ; 
							$bulanb = "bulan_b".$i;
							if(strlen($v3->$bulanb)>0){
								// $val = $v3->$bulanb ;
							}
							// history_core
							$val = check_history_core($history_core,$key_core,$i,$val);

							if($v3->last_edit == '1'){
								$val = $v3->{'bulan_b'.$i}; 
							}else {
								if(strlen($v3->biaya_bulan)>0){
									$val = $v3->biaya_bulan;
								}
							}

							if($detail_tahun[$a]['sumber_data'] == '1'){
								$val = $v3->{'core'.$i};
								$b = $i-1;
								if($a-1 >= 0){
									if($detail_tahun[$a-1]['sumber_data'] == '1'){
										$val = $val - $v3->{'core'.$b};
									}
								}
							}
							$val = round_value($val);
							if(!$s_keterangan['status'] && !$access_additional):
								$edit = 'contenteditable="false"';
							endif;
							
							$item3 .= '<td class="'.ck_edited($v3->cabang_edit,'bulan_b'.$i).'"><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$edit.' class="edit-value text-right" data-name="bulan_b'.$i.'" data-id="'.$v3->glwnco.'" data-value="'.$val.'">'.check_data($val,$minus3).'</div></td>';
							$keteranganTxt = $s_keterangan['keterangan'];
						}
						$dataSaved['bulan_b'.$i] = $val;
						$biaya_tahun3 += $val;
						
						if(isset($dt3[$i])){ $dt3[$i] += $val; }else{ $dt3[$i] = $val; }
					}
					checkForUpdate($dataSaved);
					$item3 .= '<td'.$bd.'>'.$keteranganTxt.'</td>';

					$valueTxt = $value;
					if(strlen($v3->biaya_bulan)>0){
						$valueTxt = $v3->biaya_bulan;
					}
					$valueTxt = round_value($valueTxt);

					$item3 .= '<td class="'.ck_edited($v3->cabang_edit,'biaya_bulan').'"><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$editBulan.' class="edit-value text-right edit-bulan" data-name="biaya_bulan" data-id="'.$v3->glwnco.'" data-value="'.$valueTxt.'">'.check_data($valueTxt,$minus3).'</div></td>';
					$item3 .= '<td class="'.ck_edited($v3->cabang_edit,'biaya_tahun').'"><div style="min-height: 10px; width: 100%; overflow: hidden;" contenteditable="false" class="edit-value text-right" data-name="biaya_tahun" data-id="'.$v3->glwnco.'" data-value="'.$biaya_tahun3.'">'.check_data($biaya_tahun3,$minus3).'</div></td>';
					$item3 .= '<td'.$bd.'></td>';
					$sistem 	 = (float) $v3->hasil/$bulan_terakhir;
					$item3 .= '<td'.$bg.'>'.check_data($sistem,$minus2).'</td>';
					// $item3 .= '<td'.$bg.'>'.check_data($val,$minus3).'</td>';
					$item3 .= '<td'.$bg.'>'.check_data($v3->hasil2,$minus3).'</td>';
					$item3 .= '<td'.$bg.'>'.check_data($v3->hasil,$minus3).'</td>';
					$item3 .= '</tr>';
					$item3 .= $item4;
				}
				$bln_trakhir = '';
				$value = '';
				$valueTxt = '';
			}else{
				$bln_trakhir = $v2->hasil;
				$value 		 = (float) $bln_trakhir/$bulan_terakhir;
				if(strlen($v2->biaya_bulan)>0){
					$value = $v2->biaya_bulan;
				}
				$valueTxt 	 = $value;
				$valueTxt  	 = round_value($valueTxt);
				$getTahun	 = check_data($value*12, $minus2);
				$bln_trakhir = $bln_trakhir;
			}

			$s_keterangan = ck_keterangan($coa_keterangan,$v2->glwnco,$access_additional,$akses_ubah);
			$CI->s_keterangan = $s_keterangan;
			$key_core = multidimensional_search($history_core,[
				'glwnco' => $v2->glwnco
			]);

			$item2 .= '<tr>';

			$file = '';
			if(in_array($v2->glwnco,$arr_coa_file)):
				$file = '<br><button type="button" class="btn btn-info btn-file" data-id="'.$v2->glwnco.'" title="File"><i class="fa-download"></i></button>';
			endif;

			$item2 .= '<td class="button text-center">'.$v2->glwnob.'</td>';
			$item2 .= '<td class="button text-center">'.$v2->glwnco.$file.'</td>';
			$item2 .= '<td class="sb-2">'.remove_spaces($v2->glwdes).'</td>';
			$dataSaved = ['kode_anggaran' => $anggaran->kode_anggaran,'kode_cabang' => $cabang, 'glwnco' => $v2->glwnco];
			$biaya_tahun2 = 0;
			for ($i=1; $i <=12 ; $i++) { 
				$a = array_search($i, array_column($detail_tahun, 'bulan'));
				if(count($dt3)>0){
					$val = $dt3[$i];
					$val = round_value($val);
					// $bulanb = "bulan_b".$i;
					// if(!empty($v2->$bulanb)){
					// 	$val = $v2->$bulanb;
					// } 


					// if($v2->last_edit == '1'){
					// 	$val = $val; 
					// }else {
					// 	if(!empty($v2->biaya_bulan)){
					// 		$val = $v2->biaya_bulan;
					// 	}
					// }



					// if($detail_tahun[$a]['sumber_data'] == '1'){
					// 	$val = $v2->{'core'.$i};
					// 	$b = $i-1;
					// 	if($a-1 >= 0){
					// 		if($detail_tahun[$a-1]['sumber_data'] == '1'){
					// 			$val = $val - $v2->{'core'.$b};
					// 		}
					// 	}
					// }
					$keteranganTxt = '';
					$edit2 = 'contenteditable="false"';
					$editBulan = 'contenteditable="false"';
					$item2 .= '<td class="'.ck_edited($v2->cabang_edit,'bulan_b'.$i).'" '.$tr.'><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$edit2.' class="edit-value text-right" data-name="bulan_b'.$i.'" data-id="'.$v2->glwnco.'" data-value="'.$val.'">'.check_data($val,$minus2).'</div></td>';
				}
				else{ 
					$val = $value ; 
					$bulanb = "bulan_b".$i;
					if(strlen($v2->$bulanb)>0){
						// $val = $v2->$bulanb ;
					}
					// history_core
					$val = check_history_core($history_core,$key_core,$i,$val);

					if($v2->last_edit == '1'){
						$val = $v2->{'bulan_b'.$i};
					}else {
						if(strlen($v2->biaya_bulan)>0){
							$val = $v2->biaya_bulan;
						}
					}

					if($detail_tahun[$a]['sumber_data'] == '1'){
						$val = $v2->{'core'.$i};
						$b = $i-1;
						if($a-1 >= 0){
							if($detail_tahun[$a-1]['sumber_data'] == '1'){
								$val = $val - $v2->{'core'.$b};
							}
						}
					}
					$val = round_value($val);
					if(!$s_keterangan['status'] && !$access_additional):
						$edit2 = 'contenteditable="false"';
					endif;
					$dataSaved['bulan_b'.$i] = $val;
					$item2 .= '<td class="'.ck_edited($v2->cabang_edit,'bulan_b'.$i).'"><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$edit2.' class="edit-value text-right" data-name="bulan_b'.$i.'" data-id="'.$v2->glwnco.'" data-value="'.$val.'">'.check_data($val,$minus2).'</div></td>';
					$keteranganTxt = $s_keterangan['keterangan'];
				}
				$biaya_tahun2 += $val;
				if(isset($dt2[$i])){ $dt2[$i] += $val; }else{ $dt2[$i] = $val; }
			}
			checkForUpdate($dataSaved);
			$item2 .= '<td'.$bd.'>'.$keteranganTxt.'</td>';
			$valueTxt = $biaya_tahun2 / 12;
			if(strlen($v2->biaya_bulan)>0){
				$valueTxt = $v2->biaya_bulan;
			}
			$valueTxt = round_value($valueTxt);

			$item2 .= '<td class="'.ck_edited($v2->cabang_edit,'biaya_bulan').'"><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$editBulan.' class="edit-value text-right edit-bulan" data-name="biaya_bulan" data-id="'.$v2->glwnco.'" data-value="'.$valueTxt.'">'.check_data($valueTxt,$minus2).'</div></td>';
			$item2 .= '<td class="'.ck_edited($v2->cabang_edit,'biaya_tahun').'"><div style="min-height: 10px; width: 100%; overflow: hidden;" contenteditable="false" class="edit-value text-right" data-name="biaya_tahun" data-id="'.$v2->glwnco.'" data-value="'.$biaya_tahun2.'">'.check_data($biaya_tahun2,$minus2).'</div></td>';
			$item2 .= '<td'.$bd.'></td>';
			$sistem 	 = (float) $v2->hasil/$bulan_terakhir;
			$item2 .= '<td'.$bg.'>'.check_data($sistem,$minus2).'</td>';
			$item2 .= '<td'.$bg.'>'.check_data($v2->hasil2,$minus2).'</td>';
			$item2 .= '<td'.$bg.'>'.check_data($v2->hasil,$minus2).'</td>';
			$item2 .= '</tr>';
			$item2 .= $item3;
		}
		$bln_trakhir = '';
		$value = '';
		$valueTxt = '';
	}else{
		$bln_trakhir = $v->hasil;
		$value 		 = (float) $bln_trakhir/$bulan_terakhir;
		if(strlen($v->biaya_bulan)>0){
			$value = $v->biaya_bulan;
		}
		$valueTxt 	 = check_data($value,$minus);
		$valueTxt  	 = round_value($valueTxt);
		$getTahun	 = check_data($value*12, $minus);
		$bln_trakhir = check_data($bln_trakhir,$minus);
	}

	$s_keterangan = ck_keterangan($coa_keterangan,$v->glwnco,$access_additional,$akses_ubah);
	$CI->s_keterangan = $s_keterangan;
	$key_core = multidimensional_search($history_core,[
		'glwnco' => $v->glwnco
	]);
	$item .= '<tr>';

	$file = '';
	if(in_array($v->glwnco,$arr_coa_file)):
		$file = '<br><button type="button" class="btn btn-info btn-file" data-id="'.$v->glwnco.'" title="File"><i class="fa-download"></i></button>';
	endif;

	$item .= '<td class="button text-center">'.$v->glwnob.'</td>';
	$item .= '<td class="button text-center">'.$v->glwnco.$file.'</td>';
	$item .= '<td>'.remove_spaces($v->glwdes).'</td>';
	$biaya_tahun1 = 0;
	$dataSaved = ['kode_anggaran' => $anggaran->kode_anggaran,'kode_cabang' => $cabang, 'glwnco' => $v->glwnco];
	for ($i=1; $i <= 12 ; $i++) { 
		$a = array_search($i, array_column($detail_tahun, 'bulan'));
		if(count($dt2)>0){ 
			$val = $dt2[$i]; 
			$val = round_value($val);
			// $bulanb = "bulan_b".$i;
			// if(!empty($v->$bulanb)){
			// 	$val = $v->$bulanb;
			// }

			// if($v->last_edit == '1'){
			// 	$val = $val; 
			// }else {
			// 	if(!empty($v->biaya_bulan)){
			// 		$val = $v->biaya_bulan;
			// 	}
			// }

			// if($detail_tahun[$a]['sumber_data'] == '1'){
			// 	$val = $v->{'core'.$i};
			// 	$b = $i-1;
			// 	if($a-1 >= 0){
			// 		if($detail_tahun[$a-1]['sumber_data'] == '1'){
			// 			$val = $val - $v->{'core'.$b};
			// 		}
			// 	}
				
			// }
			$edit = 'contenteditable="false"';
			$keteranganTxt = '';
		}
		else{ 
			$val = $value; 
			$bulanb = "bulan_b".$i;
			if(strlen($v->$bulanb)>0){
				// $val = $v->$bulanb;
			}
			// history_core
			$val = check_history_core($history_core,$key_core,$i,$val);

			if($v->last_edit == '1'){
				$val = $v->{'bulan_b'.$i};
			}else {
				if(strlen($v->biaya_bulan)>0){
					$val = $v->biaya_bulan;
				}
			}

			if($detail_tahun[$a]['sumber_data'] == '1'){
				$val = $v->{'core'.$i};
				$b = $i-1;
				if($a-1 >= 0){
					if($detail_tahun[$a-1]['sumber_data'] == '1'){
						$val = $val - $v->{'core'.$b};
					}
				}
			}
			$val = round_value($val);
			$keteranganTxt = $s_keterangan['keterangan'];
			
		}
		$dataSaved['bulan_b'.$i] = $val;
		$item .= '<td class="'.ck_edited($v->cabang_edit,'bulan_b'.$i).'" '.$tr.'><div style="min-height: 10px; width: 100%; overflow: hidden;" contenteditable="false" class="edit-value text-right" data-name="bulan_b'.$i.'" data-id="'.$v->glwnco.'" data-value="'.$val.'">'.check_data($val,$minus).'</div></td>';
		$biaya_tahun1 += $val;
	}
	checkForUpdate($dataSaved);
	$item .= '<td'.$bd.'>'.$keteranganTxt.'</td>';

	$biaya_bulan = $biaya_tahun1 / 12;
	if(strlen($v->biaya_bulan)>0){
		$biaya_bulan = $v->biaya_bulan;
	}
	$biaya_bulan = round_value($biaya_bulan);
	
	$item .= '<td class="'.ck_edited($v->cabang_edit,'biaya_bulan').'"><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$edit.' class="edit-value text-right edit-bulan" data-name="biaya_bulan" data-id="'.$v->glwnco.'" data-value="'.$biaya_bulan.'">'.check_data($biaya_bulan,$minus).'</div></td>';
	$item .= '<td class="'.ck_edited($v->cabang_edit,'biaya_tahun').'"><div style="min-height: 10px; width: 100%; overflow: hidden;" contenteditable="false" class="edit-value text-right" data-name="biaya_tahun" data-id="'.$v->glwnco.'" data-value="'.$biaya_tahun1.'">'.check_data($biaya_tahun1,$minus).'</div></td>';
	$item .= '<td'.$bd.'></td>';
	$sistem 	 = (float) $v->hasil/$bulan_terakhir;
	$item .= '<td'.$bg.'>'.check_data($sistem,$minus2).'</td>';
	// $item .= '<td'.$bg.'>'.check_data($val,$minus).'</td>';
	$item .= '<td'.$bg.'>'.check_data($v->hasil2,$minus).'</td>';
	$item .= '<td'.$bg.'>'.check_data($v->hasil,$minus).'</td>';
	$item .= '</tr>';
	$item .= $item2;
}
echo $item;
function check_data($v,$x){
	$val = kali_minus($v,$x);
	// $val = custom_format($val);
	$val = custom_format(view_report($val));
	return $val;
}
function checkForUpdate($data){
	$ck = get_data('tbl_biaya',[
		'select' 	=> 'id',
		'where' 	=> [
			'kode_anggaran' => $data['kode_anggaran'],
			'kode_cabang'	=> $data['kode_cabang'],
			'glwnco'		=> $data['glwnco'],
		]
	])->row();
	if($ck):
		update_data('tbl_biaya',$data,'id',$ck->id);
	else:
		insert_data('tbl_biaya',$data);
	endif;
}
function index_besaran($x){
	$x = (float) $x;
	if(!$x):
		$x = 1;
	endif;
	return $x;
}
function ck_edited($arr,$bulan){
	$CI        	  	= get_instance();
	$arr = json_decode($arr);
	$class = '';
	$s_keterangan = ['status' => false];
	if(isset($CI->s_keterangan)):
		$s_keterangan = $CI->s_keterangan;
	endif;
	// echo '<pre>'.json_encode($s_keterangan).'</pre>';
	if(($CI->akses_ubah and $bulan == 'biaya_bulan') or $s_keterangan['status']):
		$class = 'bg-edited2';
	endif;
	if(is_array($arr) and in_array($bulan,$arr)):
		$class = 'bg-edited';
	endif;
	return $class;
}
function ck_keterangan($coa_keterangan,$coa,$access_additional,$access_edit){
	$status 	= false;
	$keterangan = '';
	if(!$access_additional && $access_edit):
		$key = multidimensional_search($coa_keterangan,['coa' => $coa]);
		if(strlen($key)>0):
			$status  	= true;
			$keterangan = $coa_keterangan[$key]['keterangan'];
		endif;
	endif;
	return [
		'status' 		=> $status,
		'keterangan' 	=> $keterangan,
		'coa' 			=> $coa,
	];
}

function check_history_core($history_core,$key_core,$bulan,$val){
	$field 	= 'B_' . sprintf("%02d", $bulan);
	if(strlen($key_core)>0):
		$dt_core 	= $history_core[$key_core];
		$minus 		= $dt_core['kali_minus'];
		$val  		= $dt_core[$field];
		if($bulan != 1):
			$val = kali_minus($val,$minus);
			$field 		= 'B_' . sprintf("%02d", ($bulan-1) );
			$val_before = $dt_core[$field];
			$val = kali_minus($val_before,$minus) - $val;
		endif;
		if($val>0):
			$val = 0;
		endif;
	endif;
	return $val;
}

function check_edit($akses_ubah,$edit){
	if(!$akses_ubah){
		$edit = 'contenteditable="false"';
	}
	return $edit;
}
?>