<?php
	$item = '';
	$data = [];
    $data['list']       = $list;
    $data['data_core']  = $data_core;
    $data['detail_tahun'] = $detail_tahun;
    $data['access_edit'] = $access_edit;
    $data['anggaran'] 	= $anggaran;
    $data['kode_cabang'] = $kode_cabang;
	
	foreach($coa['coa'] as $k => $v){
		$minus = $v->kali_minus;
		$value = checkNumber($v->last_bulan);
		$value = kali_minus($value,$minus);
		$value = round_value($value);

		$dt_more = more($v->glwnco,0,$coa,$data);

		$item .= '<tr>';
		$item .= '<td>'.$v->glwsbi.'</td>';
        $item .= '<td>'.$v->glwnob.'</td>';
        $item .= '<td>'.$v->glwnco.'</td>';
        $item .= '<td>'.remove_spaces($v->glwdes).'</td>';
        $key_list = multidimensional_search($list,[
			'glwnco' => $v->glwnco,
		]);
		if(strlen($key_list)>0 && $list[$key_list]['last_edit'] == '2'):
			$value = $list[$key_list]['perbulan'];
		endif;

		$key_core = '';
		if(isset($data_core[$anggaran->tahun_anggaran])):
			$key_core = multidimensional_search($data_core[$anggaran->tahun_anggaran], array(
                'glwnco' => $v2->glwnco,
            ));
		endif;

		$dataSave = [];
		$val_before = 0;
		foreach($detail_tahun as $k2 => $v2){
			$i = $v2->bulan;
			$field 	= 'bulan_'.$i;
			$field2 = 'B_' . sprintf("%02d", $i);
			$val 	= $value;
			if($i != 1):
				$val += $val_before;
			endif;

			if($dt_more['status']):
				$val = $dt_more['dt'][$i];
				$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
			else:
				// pengecekan data yang terdaftar
				if(strlen($key_list)>0 && $list[$key_list]['last_edit'] == 1 && strlen($list[$key_list][$field])):
					$val = $list[$key_list][$field];
					$val = round_value($val);
				endif;

				// pengecekan data core
				if($v2->singkatan == arrSumberData()['real']):
					if(strlen($key_core)>0):
						$dt_core = $data_core[$anggaran->tahun_anggaran][$key_core];
						$val = checkNumber($dt_core[$field]);
						$val = kali_minus($value,$minus);
						$val = round_value($val);
					endif;
				endif;

				if($access_edit):
					$item .= '<td class="text-right"><div style="min-height: 10px; width: 100%; overflow: hidden;" contenteditable="true" class="edit-value text-right" data-name="bulan_'.$i.'" data-id="'.$v->glwnco.'" data-value="'.view_report($val).'">'.custom_format(view_report($val)).'</div></td>';
				else:
					$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
				endif;
			endif;
			$val_before = $val;
			$dataSave[$field] = $val;
        }
        $item .= '<td class="border-none bg-white"></td>';
        if($access_edit && !$dt_more['status']):
			$item .= '<td class="text-right"><div style="min-height: 10px; width: 100%; overflow: hidden;" contenteditable="true" class="edit-value text-right" data-name="perbulan" data-id="'.$v->glwnco.'" data-value="'.view_report($value).'">'.custom_format(view_report($value)).'</div></td>';
		else:
			$item .= '<td class="text-right">'.custom_format(view_report($value)).'</td>';
		endif;
		$item .= '</tr>';
		$item .= $dt_more['item'];

		$dataSave['kode_cabang'] 	= $kode_cabang;
		$dataSave['kode_anggaran']	= $anggaran->kode_anggaran;
		$dataSave['glwnco'] 		= $v->glwnco;
		checkSaved($dataSave);
	}
	echo $item;

	function more($id,$count,$coa,$data){
		$detail_tahun = $data['detail_tahun'];
		$access_edit  = $data['access_edit'];
		$list  		  = $data['list'];
		$anggaran 	  = $data['anggaran'];
		$kode_cabang  = $data['kode_cabang'];
		$data_core 	  = $data['data_core'];

		$item 		= '';
		$status		= false;
		$dt 		= [];

		if(isset($coa['coa'.$count][$id]) && count($coa['coa'.$count][$id])>0):
			$status = true;
			$count2 = $count + 1;
			foreach ($coa['coa'.$count][$id] as $k => $v) {
				$minus = $v->kali_minus;
				$value = checkNumber($v->last_bulan);
				$value = kali_minus($value,$minus);
				$value = round_value($value);

				$dt_more = more($v->glwnco,$count2,$coa,$data);

				$item .= '<tr>';
				$item .= '<td>'.remove_spaces($v->glwsbi).'</td>';
				$item .= '<td>'.remove_spaces($v->glwnob).'</td>';
				$item .= '<td>'.remove_spaces($v->glwnco).'</td>';
				$item .= '<td class="sb-'.$count2.'">'.remove_spaces($v->glwdes).'</td>';
				
				$key_list = multidimensional_search($list,[
					'glwnco' => $v->glwnco,
				]);
				if(strlen($key_list)>0 && $list[$key_list]['last_edit'] == '2'):
					$value = $list[$key_list]['perbulan'];
				endif;

				$key_core = '';
				if(isset($data_core[$anggaran->tahun_anggaran])):
					$key_core = multidimensional_search($data_core[$anggaran->tahun_anggaran], array(
	                    'glwnco' => $v2->glwnco,
	                ));
				endif;
				$dataSave 	= [];
				$val_before = 0;
				foreach($detail_tahun as $k2 => $v2){
					$i = $v2->bulan;
					$field 	= 'bulan_'.$i;
					$field2 = 'B_' . sprintf("%02d", $i);
					$val 	= $value;
					if($i != 1):
						$val += $val_before;
					endif;

					if($dt_more['status']):
						$val = $dt_more['dt'][$i];
						$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
					else:
						// pengecekan data yang terdaftar
						if(strlen($key_list)>0 && $list[$key_list]['last_edit'] == 1 && strlen($list[$key_list][$field])):
							$val = $list[$key_list][$field];
							$val = round_value($val);
						endif;

						// pengecekan data core
						if($v2->singkatan == arrSumberData()['real']):
							if(strlen($key_core)>0):
								$dt_core = $data_core[$anggaran->tahun_anggaran][$key_core];
								$val = checkNumber($dt_core[$field]);
								$val = kali_minus($value,$minus);
								$val = round_value($val);
							endif;
						endif;

						if($access_edit):
							$item .= '<td class="text-right"><div style="min-height: 10px; width: 100%; overflow: hidden;" contenteditable="true" class="edit-value text-right" data-name="bulan_'.$i.'" data-id="'.$v->glwnco.'" data-value="'.view_report($val).'">'.custom_format(view_report($val)).'</div></td>';
						else:
							$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
						endif;
					endif;
		        	
		        	$val_before = checkNumber($val);

		        	if(isset($dt[$i])):
		        		$dt[$i] += checkNumber($val);
		        	else:
		        		$dt[$i] = checkNumber($val);
		        	endif;
		        	$dataSave[$field] = $val;
		        }
		        $item .= '<td class="border-none bg-white"></td>';
		        if($access_edit && !$dt_more['status']):
					$item .= '<td class="text-right"><div style="min-height: 10px; width: 100%; overflow: hidden;" contenteditable="true" class="edit-value text-right" data-name="perbulan" data-id="'.$v->glwnco.'" data-value="'.view_report($value).'">'.custom_format(view_report($value)).'</div></td>';
				else:
					$item .= '<td class="text-right">'.custom_format(view_report($value)).'</td>';
				endif;
				$item .= '</tr>';
				$item .= $dt_more['item'];

				$dataSave['kode_cabang'] 	= $kode_cabang;
				$dataSave['kode_anggaran']	= $anggaran->kode_anggaran;
				$dataSave['glwnco'] 		= $v->glwnco;
				checkSaved($dataSave);
			}
		endif;

		return [
			'status' => $status,
			'item'	 => $item,
			'dt'	 => $dt,
		];
	}

	function checkSaved($data){
		if($data['glwnco']):
			$ck = get_data('tbl_valas_labarugi',[
	            'select' => 'id',
	            'where' => [
	                'kode_anggaran' => $data['kode_anggaran'],
	                'kode_cabang'   => $data['kode_cabang'],
	                'glwnco'        => $data['glwnco']
	            ]
	        ])->row();
	        if($ck):
	        	update_data('tbl_valas_labarugi',$data,'id',$ck->id);
	        else:
	        	$status = false;
	        	foreach ($data as $k => $v) {
	        		if(!in_array($k,['kode_cabang','kode_anggaran','glwnco'])):
	        			if($v):
	        				$status = true;
	        			endif;
	        		endif;
	        	}
	        	if($status):
	        		insert_data('tbl_valas_labarugi',$data);
	        	endif;
	        endif;
		endif;
	}
?>