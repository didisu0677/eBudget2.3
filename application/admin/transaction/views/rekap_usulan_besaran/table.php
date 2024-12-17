<?php
	function saved_data($data,$anggaran,$cabang){
		$table = 'tbl_bottom_up_form1';
		foreach($data as $k => $v){
			$x 		= explode('-', $k);
            $coa 	= $x[0];
            $tahun 	= $x[1];

            $where = [
            	'kode_anggaran'	=> $anggaran->kode_anggaran,
            	'kode_cabang'	=> $cabang->kode_cabang,
            	'data_core'		=> $tahun,
            	'coa'			=> $coa
            ];

            $ck = get_data($table,['select' => 'id', 'where' => $where])->row();
            $dataSave = [];
            if($ck):
            	$dataSave = $v;
            	$dataSave['id'] = $ck->id;
            else:
            	$dataSave = $where;
            	$dataSave = array_merge($dataSave,$v);
            	$dataSave['keterangan_anggaran'] = $anggaran->keterangan;
            	$dataSave['tahun'] = $anggaran->tahun_anggaran;
            	$dataSave['cabang'] = $cabang->nama_cabang;
            	$dataSave['username'] = user('username');
            endif;
            save_data($table,$dataSave,[],true);
		}
	}

	$item = '';
	$data['cabang'] 		= $cabang;
	$data['detail_tahun'] 	= $detail_tahun;
	$data['list'] 			= $list;
	$data['real_status']    = $real_status;
    $data['real_tahun']     = $real_tahun;
    $data['coa']     		= $coa;
    $data['anggaran']     	= $anggaran;
	foreach($cabang[0] as $k => $v){
		$dt_more = more($v->id,$data,1);

		$data_core = [];
		if($real_status and !$dt_more['status']):
			$data_core = get_data_core([$coa],$real_tahun,'TOT_'.$v->kode_cabang);
		endif;

		$item .= '<tr>';
		$item .= '<td>'.remove_spaces($v->kode_cabang).'</td>';
		$item .= '<td>'.remove_spaces($v->nama_cabang).'</td>';
		$dataSave = [];
		foreach($detail_tahun as $k2 => $v2){
			$field 	= 'B_' . sprintf("%02d", $v2->bulan);

			if($dt_more['status']):
				$val = $dt_more['dt'][$v2->tahun][$field];
			else:
				$val = 0;
				$key = multidimensional_search($list, array(
					'kode_cabang' 	=> $v->kode_cabang,
					'data_core'		=> $v2->tahun
				));
				if(strlen($key)>0):
					$val = $list[$key][$field];
				elseif($v2->singkatan == arrSumberData()['real'] && isset($data_core[$v2->tahun])):
					$core_key = multidimensional_search($data_core[$v2->tahun], array(
						'glwnco' => $coa,
					));
					if(strlen($core_key)>0):
						$kali_minus = $data_core[$v2->tahun][$core_key]['kali_minus'];
						$val 		= $data_core[$v2->tahun][$core_key][$field];
						$val 		= kali_minus($val,$kali_minus);
						$dataSave[$coa.'-'.$v2->tahun][$field] = $val;
					endif;
				endif;
			endif;
			$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
			saved_data($dataSave,$anggaran,$v);
		}
		$item .= '</tr>';
		$item .= $dt_more['item'];

	}

	echo $item;

	function more($id,$data,$count){
		$cabang 		= $data['cabang'];
		$detail_tahun 	= $data['detail_tahun'];
		$list 			= $data['list'];
		$real_status 	= $data['real_status'];
		$real_tahun 	= $data['real_tahun'];
		$coa 			= $data['coa'];
		$anggaran 		= $data['anggaran'];
		
		$item 	= '';
		$status = false;
		$dt 	= [];
		if(isset($cabang[$id]) and count($cabang[$id])>0):
			$status = true;
			foreach($cabang[$id] as $k => $v){
				$dt_more = more($v->id,$data,($count+1));

				$item .= '<tr>';
				$item .= '<td>'.remove_spaces($v->kode_cabang).'</td>';
				$item .= '<td class="sb-'.$count.'">'.remove_spaces($v->nama_cabang).'</td>';

				$data_core = [];
				if($real_status and !$dt_more['status']):
					$data_core = get_data_core([$coa],$real_tahun,'TOT_'.$v->kode_cabang);
				endif;
				
				$dataSave = [];
				foreach($detail_tahun as $k2 => $v2){
					$field 	= 'B_' . sprintf("%02d", $v2->bulan);

					if($dt_more['status']):
						$val = $dt_more['dt'][$v2->tahun][$field];
					else:
						$val = 0;
						$key = multidimensional_search($list, array(
							'kode_cabang' 	=> $v->kode_cabang,
							'data_core'		=> $v2->tahun
						));
						if(strlen($key)>0):
							$val = $list[$key][$field];
						elseif($v2->singkatan == arrSumberData()['real'] && isset($data_core[$v2->tahun])):
							$core_key = multidimensional_search($data_core[$v2->tahun], array(
								'glwnco' => $coa,
							));
							if(strlen($core_key)>0):
								$kali_minus = $data_core[$v2->tahun][$core_key]['kali_minus'];
								$val 		= $data_core[$v2->tahun][$core_key][$field];
								$val 		= kali_minus($val,$kali_minus);
								$dataSave[$coa.'-'.$v2->tahun][$field] = $val;
							endif;
						endif;
					endif;
					$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
					if(isset($dt[$v2->tahun][$field])) $dt[$v2->tahun][$field] += $val; else $dt[$v2->tahun][$field] = $val;
				}
				$item .= '</tr>';
				$item .= $dt_more['item'];
				saved_data($dataSave,$anggaran,$v);
			}
		endif;
		return [
			'item' 	=> $item,
			'status'=> $status,
			'dt'	=> $dt,
		];
	}
?>