<?php
$CI        	  	= get_instance();
$CI->akses_ubah = $akses_ubah;

$getTahunMin = $bulan_terakhir - 1;
$table ='<tr><td class="border-none">.</td></tr><tr><td class="border-none">.</td></tr>
		<tr style="margin-top:30px" class="tr-gray">
            <th width="60" class="text-center align-middle headcol">COA</th>
            <th class="text-center align-middle headcol" style="width:auto;min-width:230px;min-height: 50px;">KETERANGAN</th>
            ';
            for ($i = 1; $i <= 12; $i++) {
				$a = array_search($i, array_column($detail_tahun, 'bulan'));
				$column = strtoupper(month_lang($i));
				$column .= '<span class="txt_title">'."<br> (".strtoupper($detail_tahun[$a]['singkatan']." PD BLN)").'</span>';
				$table .= '<th class="text-center" style="min-width:80px;">'.$column.'</th>';
			}
  $table .='<th class="border-none"></th>
            <th class="text-center" style="min-width:80px;">BIAYA PERBULAN</th>
            <th class="text-center" style="min-width:80px;">BIAYA PERTAHUN</th>
            <th class="border-none"></th>
            <th class="text-center" style="min-width:80px;">SISTEM</th>
            <th class="text-center" style="min-width:80px;">REAL '.strtoupper(month_lang($getTahunMin)).'</th>
            <th class="text-center" style="min-width:80px;">REAL '.strtoupper(month_lang($bulan_terakhir)).'</th>
        </tr>';
if($group == 0):
	$item = $table;
else:
	$item = '';
endif;
$bg = ' class="text-right  bg-grey"';
$bd = ' class="text-right  border-none bg-white"';
$tr = ' class="text-right "';
foreach ($coa as $k => $v) {

	if($group == 1):
		$item .= $table;
	endif;

	$item2 = '';
	$dt2 = [];
	$minus = $v->kali_minus;

	if(isset($detail['1'][$v->glwnco])){
		foreach ($detail['1'][$v->glwnco] as $k2 => $v2) {
			$item3 = '';
			$dt3 = [];
			$minus2 = $v2->kali_minus;

			if($v->glwnco == '5680000' || $v->glwnco == '5700000' || $v->glwnco == '5710000'  || $v->glwnco == '5720000' ){
				$editBulan2 = 'contenteditable="true"';
			}else {

				$editBulan2 = 'contenteditable="false"';
			}

			if($akses_ubah == 1){
				$editBulan2 = 'contenteditable="true"';
			}
			
			if(isset($detail['2'][$v2->glwnco])){
				foreach ($detail['2'][$v2->glwnco] as $k3 => $v3) {
					$item4 = '';
					$dt4   = [];
					$minus3 = $v3->kali_minus;
					// if($v3->glwnco == '5573012' || $v3->glwnco == '5573013' || $v3->glwnco == '5679012' || $v3->glwnco == '5679014' || $v3->glwnco == '5752030'){
					// 	$edit = 'contenteditable="true"';
					// }else {
					// 	$edit = 'contenteditable="false"';
					// }
					if($v2->glwnco == '5671000' || $v2->glwnco == '5751000' || $v2->glwnco == '5752000'){
						$editBulan3 = 'contenteditable="true"';
						$edit2 = 'contenteditable="false"';
						$editBulan = 'contenteditable="false"';
					}else {

						$edit2 = 'contenteditable="false"';
						$editBulan3 = 'contenteditable="false"';
						$editBulan = 'contenteditable="false"';
					}
					if($akses_ubah == 1){
						$editBulan3 = 'contenteditable="true"';
						$edit2 = 'contenteditable="true"';
						$editBulan = 'contenteditable="true"';
					}
					if(isset($detail['3'][$v3->glwnco])){
						foreach ($detail['3'][$v3->glwnco] as $k4 => $v4) {
							$item5 = '';
							$dt5   = [];
							$minus4 = $v4->kali_minus;
							$edit = 'contenteditable="false"';
							if($akses_ubah == 1){
								$edit = 'contenteditable="true"';
							}

							if(isset($detail['4'][$v4->glwnco])){
								foreach ($detail['4'][$v4->glwnco] as $k5 => $v5) {
									$item6 	= '';
									$dt6 	= [];
									$minus5 = $v5->kali_minus;
									$edit = 'contenteditable="false"';
									if($akses_ubah == 1){
										$edit = 'contenteditable="true"';
									}

									if(isset($detail['5'][$v5->glwnco])){
										foreach ($detail['5'][$v5->glwnco] as $k6 => $v6) {
											$item6 .= '<tr>';

											$file = '';
											if(in_array($v6->glwnco,$arr_coa_file)):
												$file = '<br><button type="button" class="btn btn-info btn-file" data-id="'.$v6->glwnco.'" title="File"><i class="fa-download"></i></button>';
											endif;

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

												$item6 .= '<td class="'.ck_edited($v6->cabang_edit,'bulan_b'.$i).'">'.check_data($val,$minus6).'</td>';
												$dataSaved['bulan_b'.$i] = $val;
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

											$item6 .= '<td class="'.ck_edited($v6->cabang_edit,'biaya_bulan').'"><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$edit.' class="edit-value text-right edit-bulan" data-name="biaya_bulan" data-id="'.$v6->glwnco.'" data-value="'.$valueTxt.'">'.$valueTxt.'</div></td>';
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

									$item5 .= '<td class="button text-center">'.$v5->glwnco.$file.'</td>';
									$item5 .= '<td class="sb-5">'.remove_spaces($v5->glwdes).'</td>';
									$dataSaved = ['kode_anggaran' => $anggaran->kode_anggaran,'kode_cabang' => $cabang, 'glwnco' => $v5->glwnco];
									for ($i=1; $i <= 12 ; $i++) { 
										if(count($dt6)>0){ 
											$val = $dt6[$i]; 
											$val = round_value($val);
											$item5 .= '<td class="'.ck_edited($v5->cabang_edit,'bulan_b'.$i).'" '.$tr.'>'.check_data($val,$minus5).'</td>';
										}
										else{ 
											
											$val = $value ; 
											if(strlen($v5->{'bulan'.$i})>0){
												$val = $val * $v5->{'bulan'.$i};
											}
											$val = round_value($val);
											$item5 .= '<td class="'.ck_edited($v5->cabang_edit,'bulan_b'.$i).'"><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$edit.' class="edit-value text-right" data-name="bulan_b'.$i.'" data-id="'.$v5->glwnco.'" data-value="'.$val.'">'.check_data($val,$minus5).'</div></td>'; 
										} 
										$dataSaved['bulan_b'.$i] = $val;
										if(isset($dt5[$i])){ $dt5[$i] += $val; }else{ $dt5[$i] = $val; }
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
									$item5 .= '<td class="'.ck_edited($v5->cabang_edit,'biaya_bulan').'"><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$edit.' class="edit-value text-right edit-bulan" data-name="biaya_bulan" data-id="'.$v6->glwnco.'" data-value="'.$valueTxt.'">'.check_data($valueTxt,0).'</div></td>';
									$item5 .= '<td class="'.ck_edited($v5->cabang_edit,'biaya_tahun').'"><div style="min-height: 10px; width: 100%; overflow: hidden;" contenteditable="false" class="edit-value text-right" data-name="biaya_tahun" data-id="'.$v6->glwnco.'" data-value="'.$bln_trakhir.'">'.check_data($bln_trakhir,0).'</div></td>';
									$item5 .= '</tr>';
									$item5 .= $item6;
								}
								$bln_trakhir = '';
								$value = '';
								$valueTxt = '';
							}else{
								// $bln_trakhir = $v4->{'TOT_'.$cabang};
								$bln_trakhir = $v4->hasil;
								if($v3->glwnob == "53303" || $v3->glwnob == "53305" || $v3->glwnob == "53306" || $v3->glwnob == "54001" || $v3->glwnob == "54101" || $v3->glwnob == "53306"){
									$value 		 = (float) $bln_trakhir - $v4->hasil2;
								}else {
									$value 		 = (float) $bln_trakhir/ $bulan_terakhir;
								}
								if(strlen($v4->biaya_bulan)>0){
									$value = $v4->biaya_bulan;
								}
								$valueTxt 	 = check_data($value,$minus4);
								$valueTxt 	 = round_value($valueTxt);
								$getTahun	 = check_data($value*12, $minus4);
								$bln_trakhir = check_data($bln_trakhir,$minus4);
							}

							$item4 .= '<tr>';

							$file = '';
							if(in_array($v4->glwnco,$arr_coa_file)):
								$file = '<br><button type="button" class="btn btn-info btn-file" data-id="'.$v4->glwnco.'" title="File"><i class="fa-download"></i></button>';
							endif;

							$item4 .= '<td class="button text-center">'.$v4->glwnco.$file.'</td>';
							$item4 .= '<td class="sb-4">'.remove_spaces($v4->glwdes).'</td>';
							$dataSaved = ['kode_anggaran' => $anggaran->kode_anggaran,'kode_cabang' => $cabang, 'glwnco' => $v4->glwnco];
							for ($i=1; $i <= 12 ; $i++) {
								if(count($dt5)>0){ 
									$val = $dt5[$i];
									$val = round_value($val); 
									// $bulanb = "bulan_b".$i;
									// if(!empty($v4->$bulanb)){
									// 	$val = $v4->$bulanb ;
									// }
									$edit= 'contenteditable="false"';
									$item4 .= '<td class="'.ck_edited($v4->cabang_edit,'bulan_b'.$i).'" '.$tr.'><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$edit.' class="edit-value text-right" data-name="bulan_b'.$i.'" data-id="'.$v4->glwnco.'" data-value="'.$val.'">'.check_data($val,$minus4).'</div></td>';
								}
								else{ 
									
									$val = $value ; 
									if(strlen($v4->{'bulan'.$i})>0){
										$val = $val * $v4->{'bulan'.$i};
									}
									$bulanb = "bulan_b".$i;
									if(strlen($v4->$bulanb)>0){
										$val = $v4->$bulanb ;
									}
									if($akses_ubah == 1){
										$edit= 'contenteditable="true"';
									}
									$val = round_value($val);
									$item4 .= '<td class="'.ck_edited($v4->cabang_edit,'bulan_b'.$i).'"><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$edit.' class="edit-value text-right" data-name="bulan_b'.$i.'" data-id="'.$v4->glwnco.'" data-value="'.$val.'">'.check_data($val,$minus4).'</div></td>'; 
								} 
								$dataSaved['bulan_b'.$i] = $val;
								if(isset($dt4[$i])){ $dt4[$i] += $val; }else{ $dt4[$i] = $val; }
							}
							checkForUpdate($dataSaved);
							$item4 .= '<td'.$bd.'></td>';
							if(strlen($v4->biaya_bulan)>0){
								$valueTxt = $v4->biaya_bulan;
							}
							if(strlen($v4->biaya_tahun)>0){
								$bln_trakhir = $v4->biaya_tahun;
							}
							if($akses_ubah == 1){
								$edit= 'contenteditable="true"';
							}
							$valueTxt = round_value($valueTxt);

							$item4 .= '<td class="'.ck_edited($v4->cabang_edit,'biaya_bulan').'"><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$edit.' class="edit-value text-right edit-bulan" data-name="biaya_bulan" data-id="'.$v4->glwnco.'" data-value="'.check_data($valueTxt,$minus4).'">'.$valueTxt.'</div></td>';
							$item4 .= '<td class="'.ck_edited($v4->cabang_edit,'biaya_tahun').'"><div style="min-height: 10px; width: 100%; overflow: hidden;" contenteditable="false" class="edit-value text-right" data-name="biaya_tahun" data-id="'.$v4->glwnco.'" data-value="'.check_data($bln_trakhir,$minus4).'">'.$bln_trakhir.'</div></td>';
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
						$editBulan3 = 'contenteditable="true"';
					}else{
						// $bln_trakhir = $v3->{'TOT_'.$cabang};

						$bln_trakhir = $v3->hasil;
						if($v3->glwnob == "53303" || $v3->glwnob == "53305" || $v3->glwnob == "53306" || $v3->glwnob == "54001" || $v3->glwnob == "54101" || $v3->glwnob == "53306"){
									$value 		 = (float) $bln_trakhir - $v3->hasil2;
									$sistem 	 = (float) $bln_trakhir - $v3->hasil2;
								}else {
									$value 		 = (float) $bln_trakhir/ $bulan_terakhir;
									$sistem 	 = (float) $bln_trakhir/ $bulan_terakhir;
								}

						for ($jum = 5671011; $jum < 5671017;$jum++){
							if($v3->glwnco == $jum || $v3->glwnco == '5679024'){
								$sistem 		 = (float) $bln_trakhir - $v3->hasil2;
								$value 			 = $sistem;
							}
						}		

						if(strlen($v3->biaya_bulan)>0){
							$value = $v3->biaya_bulan;
						}		
						$valueTxt 	 = check_data($value,$minus3);
						$valueTxt 	= round_value($valueTxt);
						$getTahun	 = check_data($value*12, $minus3);
						$bln_trakhir = check_data($bln_trakhir,$minus3);
						if($akses_ubah == 1){
							$editBulan3 = 'contenteditable="true"';
						}
					}

					$s_keterangan = ck_keterangan($coa_keterangan,$v3->glwnco,$access_additional,$akses_ubah);
					$CI->s_keterangan = $s_keterangan;
					$item3 .= '<tr>';

					$file = '';
					if(in_array($v3->glwnco,$arr_coa_file)):
						$file = '<br><button type="button" class="btn btn-info btn-file" data-id="'.$v3->glwnco.'" title="File"><i class="fa-download"></i></button>';
					endif;

					$item3 .= '<td class="button text-center">'.$v3->glwnco.$file.'</td>';
					$item3 .= '<td class="sb-3">'.remove_spaces($v3->glwdes).'</td>';
					$dataSaved = ['kode_anggaran' => $anggaran->kode_anggaran,'kode_cabang' => $cabang, 'glwnco' => $v3->glwnco];
					$biaya_tahun3 = 0;
					
					for ($i=1; $i <= 12 ; $i++) {
						$a = array_search($i, array_column($detail_tahun, 'bulan'));
						if(count($dt4)>0){ 

							$val = $dt4[$i]; 
							$val = round_value($val);
							// if($detail_tahun[$a]['sumber_data'] == '1'){
							// 	$val = $v3->{'core'.$i};
							// 	$b = $i-1;
							// 	if($detail_tahun[$a-1]['sumber_data'] == '1'){
							// 		$val = $val - $v3->{'core'.$b};
							// 	}	
							// }
							// $bulanb = "bulan_b".$i;
							// if(!empty($v3->$bulanb)){
							// 	$val = $v3->$bulanb ;
							// }


							// if($v3->last_edit == '1'){
							// 	$val = $val; 
							// }else {
							// 	if(!empty($v3->biaya_bulan)){
							// 		$val = $v3->biaya_bulan;
							// 	}
							// }


							// if($akses_ubah == 1){
								
							// }
							$edit= 'contenteditable="false"';
							$item3 .=  '<td class="'.ck_edited($v3->cabang_edit,'bulan_b'.$i).'" '.$tr.'><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$edit.' class="edit-value text-right" data-name="bulan_b'.$i.'" data-id="'.$v3->glwnco.'" data-value="'.$val.'">'.check_data($val,$minus3).'</div></td>';
							$keteranganTxt = '';
						}
						else{ 
							
							$val = $value ; 
							
							if(strlen($v3->{'bulan'.$i})>0){
								$val = $val * index_besaran($v3->{'bulan'.$i});
							}
							$bulanb = "bulan_b".$i;
							// if(strlen($v3->$bulanb)>0){
							// 	$val = $v3->$bulanb ;
							// }
							if(strlen($v3->biaya_bulan)>0){
								$val = $v3->biaya_bulan;
								if(strlen($v3->{'bulan'.$i})>0){
									$val = $val * index_besaran($v3->{'bulan'.$i});
								}
							}

							if($v3->last_edit == '1'){
								$val = $v3->{'bulan_b'.$i};
							}else {
								if(strlen($v3->biaya_bulan)>0){
									$val = $v3->biaya_bulan * index_besaran($v3->{'bulan'.$i});
								}
							}

							if($akses_ubah == 1){
								$edit= 'contenteditable="true"';
							}
							if($detail_tahun[$a]['sumber_data'] == '1'){
								$val = $v3->{'core'.$i};
								$b = $i-1;
								if($detail_tahun[$a-1]['sumber_data'] == '1'){
									$val = $val - $v3->{'core'.$b};
								}
								$edit= 'contenteditable="false"';
							}
							$val = round_value($val);

							$item3 .= '<td class="'.ck_edited($v3->cabang_edit,'bulan_b'.$i).'"><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$edit.' class="edit-value text-right" data-name="bulan_b'.$i.'" data-id="'.$v3->glwnco.'" data-value="'.$val.'">'.check_data($val,$minus3).'</div></td>';
							$dataSaved['bulan_b'.$i] = $val; 
							$keteranganTxt = $s_keterangan['keterangan'];
						}
						
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
					
					$item3 .= '<td class="'.ck_edited($v3->cabang_edit,'biaya_bulan').'"><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$editBulan3.' class="edit-value edit-bulan text-right" data-name="biaya_bulan" data-id="'.$v3->glwnco.'" data-value="'.$valueTxt.'">'.check_data($valueTxt,$minus3).'</div></td>';
					$item3 .= '<td class="'.ck_edited($v3->cabang_edit,'biaya_tahun').'"><div style="min-height: 10px; width: 100%; overflow: hidden;" contenteditable="false" class="edit-value text-right" data-name="biaya_tahun" data-id="'.$v3->glwnco.'" data-value="'.$biaya_tahun3.'">'.check_data($biaya_tahun3,$minus3).'</div></td>';
					$item3 .= '<td'.$bd.'></td>';
					// $vale = check_data($val,$minus3);
					// for ($jum = 5671011; $jum < 5671017;$jum++){
					// 	if($v3->glwnco == $jum || $v3->glwnco == '5679024'){
					// 		$vale = ($v3->hasil * -1) - ($v3->hasil2 * -1);

					// 		$vale = custom_format(view_report($vale));	
					// 	}
					// }
					
					$item3 .= '<td'.$bg.'>'.check_data($sistem,$minus3).'</td>';
					$item3 .= '<td'.$bg.'>'.check_data($v3->hasil2,$minus3).'</td>';
					$item3 .= '<td'.$bg.'>'.check_data($v3->hasil,$minus3).'</td>';
					$item3 .= '</tr>';
					$item3 .= $item4;
				}
				$bln_trakhir = '';
				$value = '';
				$valueTxt = '';

				$editBulan2 = 'contenteditable="false"';
			}else{
				
				$bln_trakhir = $v2->hasil;
				$value 		 = (float) $bln_trakhir/$bulan_terakhir;
				// $sistem 	 = (float) $bln_trakhir/$bulan_terakhir;
				if(strlen($v2->biaya_bulan)>0){
					$value = $v2->biaya_bulan;
				}

				$valueTxt 	 = $value;
				$valueTxt = round_value($valueTxt);
				$getTahun	 = $value*12;
				$bln_trakhir = $getTahun;

				if($akses_ubah == 1){
					$editBulan2 = 'contenteditable="true"';
				}
			}

			$s_keterangan = ck_keterangan($coa_keterangan,$v2->glwnco,$access_additional,$akses_ubah);
			$CI->s_keterangan = $s_keterangan;
			$item2 .= '<tr>';

			$file = '';
			if(in_array($v2->glwnco,$arr_coa_file)):
				$file = '<br><button type="button" class="btn btn-info btn-file" data-id="'.$v2->glwnco.'" title="File"><i class="fa-download"></i></button>';
			endif;

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
					// if(!empty($v2->biaya_bulan)){
					// 	$val = $v2->biaya_bulan ;
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
					// 	if($detail_tahun[$a-1]['sumber_data'] == '1'){
					// 		$val = $val - $v2->{'core'.$b};
					// 	}	
					// }

					// if($v2->glwnco == '5721012'){
					// 	if(!empty($sumPromosi)){
					// 		$val = $sumPromosi[0][$bulanb];
					// 	}else{
					// 		$val = 0;
					// 	}
					// 	$minus2 = 0;
					// }
					$edit = 'contenteditable="false"';
					$item2 .= '<td class="'.ck_edited($v2->cabang_edit,'bulan_b'.$i).'" '.$tr.'><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$edit.' class="edit-value text-right" data-name="bulan_b'.$i.'" data-id="'.$v2->glwnco.'" data-value="'.$val.'">'.check_data($val,$minus2).'</div></td>';
					$keteranganTxt = '';
				}
				else{ 

					if(strlen($v2->{'bulan'.$i})>0){
						$val = $val * index_besaran($v2->{'bulan'.$i});
					}
					$val = $value ; 
					
					$bulanb = "bulan_b".$i;
					if(strlen($v2->biaya_bulan)>0){
						$val = $v2->biaya_bulan ;
					}
					if($v2->last_edit == '1'){
						$val = $v2->{'bulan_b'.$i};
					}else {
						if(strlen($v2->biaya_bulan)>0){
							$val = $v2->biaya_bulan * index_besaran($v2->{'bulan'.$i});
						}
					}
					if($akses_ubah == 1){
						$edit = 'contenteditable="true"';
					}
					if($v2->glwnco == '5721012'){
						if(!empty($sumPromosi)){
							$val = $sumPromosi[0][$bulanb] * -1;
						}else{
							$val = 0;
						}
						$edit = 'contenteditable="false"';
						// $minus2 = 0;
					}
					
					if($detail_tahun[$a]['sumber_data'] == '1'){
						$val = $v2->{'core'.$i};
						$b = $i-1;
						if($detail_tahun[$a-1]['sumber_data'] == '1'){
							$val = $val - $v2->{'core'.$b};
						}
						$edit = 'contenteditable="false"';
					}
					$val = round_value($val);

					$item2 .= '<td class="'.ck_edited($v2->cabang_edit,'bulan_b'.$i).'" dt-d="'.$v2->{'bulan'.$i}.'"><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$edit.' class="edit-value text-right" data-name="bulan_b'.$i.'" data-id="'.$v2->glwnco.'" data-value="'.$val.'">'.check_data($val,$minus2).'</div></td>'; 
					$dataSaved['bulan_b'.$i] = $val;
					$keteranganTxt = $s_keterangan['keterangan'];
				}
				$biaya_tahun2 += $val;
				if(isset($dt2[$i])){ $dt2[$i] += $val; }else{ $dt2[$i] = $val; }
			}
			checkForUpdate($dataSaved);
			$item2 .= '<td'.$bd.'>'.$keteranganTxt.'</td>';
			$valueTxt = $value;
			if(strlen($v2->biaya_bulan)>0){
				$valueTxt = $v2->biaya_bulan;
			}
			
			$valueTxt = round_value($valueTxt);
			
			$item2 .= '<td class="'.ck_edited($v2->cabang_edit,'biaya_bulan').'"><div style="min-height: 10px; width: 100%; overflow: hidden;"bulan2 '.$editBulan2.' class="edit-value edit-bulan text-right" data-name="biaya_bulan" data-id="'.$v2->glwnco.'" data-value="'.$valueTxt.'">'.check_data($valueTxt,$minus2).'</div></td>';
			$item2 .= '<td class="'.ck_edited($v2->cabang_edit,'biaya_tahun').'"><div style="min-height: 10px; width: 100%; overflow: hidden;" contenteditable="false" class="edit-value text-right" data-name="biaya_tahun" data-id="'.$v2->glwnco.'" data-value="'.$biaya_tahun2.'">'.check_data($biaya_tahun2,$minus2).'</div></td>';
			$item2 .= '<td'.$bd.'></td>';
			$sistem 	 = (float) $v2->hasil/$bulan_terakhir;
			$item2 .= '<td'.$bg.'>'.check_data($sistem,$minus2).'</td>';
			$item2 .= '<td'.$bg.'>'.check_data($v2->hasil2,$minus2).'</td>';
			$item2 .= '<td'.$bg.'>'.check_data($v2->hasil,$minus2).'</td>';
			$item2 .= '</tr>';

			if($v2->glwnco == '5721012'){

				for($a=1;$a<=5;$a++){

					$c = $a - 1;
					$keterangan = '';
					if(isset($promosi[$c])){
						$keterangan = $promosi[$c]['keterangan'];
					}
					$bgEdit = bgEdit();
					$item2 .= '<tr style="background-color: '.$bgEdit.'">';
					$item2 .= '<td>'.$a.'</td>';
					$item2 .= '<td><div style="min-height: 10px; width: 100%; overflow: hidden;" contenteditable="true" class="promosi" data-name="keterangan" data-id="'.$a.'" data-value="">'.$keterangan.'</div></td>';
					for($i=1;$i<=12;$i++){
						$hasil = 0;
						$b = $i;
						$bulan = "bulan_".$b;
						if(isset($promosi[$c])){
							$hasil = check_data($promosi[$c][$bulan],0);
						}
						$item2 .= '<td><div style="min-height: 10px; width: 100%; overflow: hidden;" contenteditable="true" class="text-right promosi textpromosi" data-name="bulan_'.$i.'" data-id="'.$a.'" data-value="">'.$hasil.'</div></td>';
					}
					$item2 .= '</tr>';
				}
			}

			$item2 .= $item3;
		}
		$bln_trakhir = '';
		$value = '';
		$valueTxt = '';
		$editBulan1 = 'contenteditable="false"';
	}else{
		// $bln_trakhir = $v->{'TOT_'.$cabang};
		$bln_trakhir = $v->hasil;
		$value 		 = (float) $bln_trakhir/$bulan_terakhir;
		if(strlen($v->biaya_bulan)>0){
			$value = $v->biaya_bulan;
		}
		$valueTxt 	 = check_data($value,$minus);
		$valueTxt = round_value($valueTxt);
		$getTahun	 = check_data($value*12, $minus);
		$bln_trakhir = check_data($bln_trakhir,$minus);
		if($akses_ubah == 1){
			$editBulan1 = 'contenteditable="true"';
		}
	}

	$s_keterangan = ck_keterangan($coa_keterangan,$v->glwnco,$access_additional,$akses_ubah);
	$CI->s_keterangan = $s_keterangan;
	$item .= '<tr>';

	$file = '';
	if(in_array($v->glwnco,$arr_coa_file)):
		$file = '<br><button type="button" class="btn btn-info btn-file" data-id="'.$v->glwnco.'" title="File"><i class="fa-download"></i></button>';
	endif;

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
			// 	if($detail_tahun[$a-1]['sumber_data'] == '1'){
			// 		$val = $val - $v->{'core'.$b};
			// 	}	
			// }
			$edit = 'contenteditable="false"';
			$keteranganTxt = '';
		}
		else{ 
			$val = $value; 
			
			$bulanb = "bulan_b".$i;
			if(strlen($v->$bulanb)>0){
				$val = $v->$bulanb;
			}

			if($v->last_edit == '1'){
				$val = $v->{'bulan_b'.$i};
			}else {
				if(strlen($v->biaya_bulan)>0){
					$val = $v->biaya_bulan * index_besaran($v->{'bulan'.$i});
				}
			}

			if($akses_ubah == 1){
				$edit = 'contenteditable="true"';
			}
			if($detail_tahun[$a]['sumber_data'] == '1'){
				$val = $v->{'core'.$i};
				$b = $i-1;
				if($detail_tahun[$a-1]['sumber_data'] == '1'){
					$val = $val - $v->{'core'.$b};
				}
				$edit = 'contenteditable="false"';
			}
			$val = round_value($val);
			$keteranganTxt = $s_keterangan['keterangan'];
		}

		$item .= '<td class="'.ck_edited($v->cabang_edit,'bulan_b'.$i).'" '.$tr.'><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$edit.' class="edit-value text-right" data-name="bulan_b'.$i.'" data-id="'.$v->glwnco.'" data-value="'.$val.'">'.check_data($val,$minus).'</div></td>';
		$biaya_tahun1 += $val;
		
		$dataSaved['bulan_b'.$i] = $val;
	}
	checkForUpdate($dataSaved);
	$item .= '<td'.$bd.'>'.$keteranganTxt.'</td>';
	$biaya_bulan = $value;
	if(strlen($v->biaya_bulan)>0){
		$biaya_bulan = $v->biaya_bulan;
	}
	if($akses_ubah == 1){
		$edit = 'contenteditable="true"';
	}
	$biaya_bulan = round_value($biaya_bulan);

	$item .= '<td class="'.ck_edited($v->cabang_edit,'biaya_bulan').'"><div style="min-height: 10px; width: 100%; overflow: hidden;" '.$editBulan1.' class="edit-value text-right edit-bulan" data-name="biaya_bulan" data-id="'.$v->glwnco.'" data-value="'.$biaya_bulan.'">'.check_data($biaya_bulan,$minus).'</div></td>';
	$item .= '<td class="'.ck_edited($v->cabang_edit,'biaya_tahun').'"><div style="min-height: 10px; width: 100%; overflow: hidden;" contenteditable="false" class="edit-value text-right" data-name="biaya_tahun" data-id="'.$v->glwnco.'" data-value="'.$biaya_tahun1.'">'.check_data($biaya_tahun1,$minus).'</div></td>';
	$item .= '<td'.$bd.'></td>';

	$sistem 	 = (float) $v->hasil/$bulan_terakhir;
	$item .= '<td'.$bg.'>'.check_data($sistem,$minus).'</td>';
	$item .= '<td'.$bg.'>'.check_data($v->hasil2,$minus).'</td>';
	$item .= '<td'.$bg.'>'.check_data($v->hasil,$minus).'</td>';
	$item .= '</tr>';
	$item .= $item2;
}
echo $item;

?>