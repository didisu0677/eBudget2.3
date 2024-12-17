<?php
	$item = '';
	$data = [
		'dSum'	=> $dSum,
		'kolom'	=> $kolom,
		'anggaran'	=> $anggaran,
		'coa'		=> $coa,
	];
	foreach ($cabang[0] as $v) {
		$item .= '<tr>';
		$item .= '<td class="bg-c1">'.$v->kode_cabang.'</td>';
		$item .= '<td class="bg-c1">'.$v->nama_cabang.'</td>';
		
		$dt_loop = loop($v->id,$cabang,1,$data);
		$dtSaved = [];
		$temp_tahun = '';
		$data_core 	= [];
		foreach ($kolom as $k2 => $v2) {
			$field = 'B_' . sprintf("%02d", $v2->bulan);
			$field1= 'ori'.$v2->bulan;
			$val   = 0;
			if($dt_loop['status']):
				$val = $dt_loop['dt'][$v2->tahun][$field];
			else:
				$key = multidimensional_search($dSum, array(
					'kode_cabang'=>$v->kode_cabang,
					'data_core'	=> $v2->tahun
				));
				if(strlen($key)>0):
					$val = $dSum[$key][$field];
				elseif($v2->singkatan == arrSumberData()['real']):
					$val = 0;
					if($v2->tahun != $temp_tahun):
						$temp_tahun = $v2->tahun;
						$data_core = get_data_core([$coa],[$v2->tahun],'TOT_'.$v->kode_cabang);
					endif;
					if(isset($data_core[$v2->tahun])):
						$core_key = multidimensional_search($data_core[$v2->tahun], array(
							'glwnco'	=> $coa
						));
						if(strlen($core_key)>0):
							$kali_minus = $data_core[$v2->tahun][$core_key]['kali_minus'];
							$val 		= $data_core[$v2->tahun][$core_key][$field];
							$val 		= kali_minus($val,$kali_minus);
						endif;
					endif;
				endif;
			endif;
			$item .= '<td class="text-right bg-c1">'.custom_format(view_report($val)).'</td>';
			$dtSaved[$v2->tahun][$field1] = $val;
		}
		$where = [
			'kode_cabang' 	=> $v->kode_cabang,
			'kode_anggaran'	=> $anggaran->kode_anggaran,
			'tahun'			=> $anggaran->tahun_anggaran,
			'coa'			=> $coa,

		];
		if(!$dt_loop['status']):
			checkForSaved($dtSaved,$where);
		endif;

		$item .= '</tr>';
		$item .= $dt_loop['item'];

	}
	echo $item;

	function loop($id,$cabang,$count,$data){
		$dSum 	= $data['dSum'];
		$kolom	= $data['kolom'];
		$anggaran	= $data['anggaran'];
		$coa		= $data['coa'];

		$status = false;
		$item 	= '';
		$dt 	= [];
		if(isset($cabang[$id]) && count($cabang[$id])>0):
			$status = true;
			foreach ($cabang[$id] as $k => $v) {
				$item .= '<tr>';
				$item .= '<td class="bg-c'.($count+1).'">'.$v->kode_cabang.'</td>';
				$item .= '<td class="sub-'.$count.' bg-c'.($count+1).'">'.$v->nama_cabang.'</td>';
				
				$dt_loop = loop($v->id,$cabang,($count+1),$data);
				$dtSaved = [];
				$temp_tahun = '';
				$data_core 	= [];
				foreach ($kolom as $k2 => $v2) {
					$field = 'B_' . sprintf("%02d", $v2->bulan);
					$field1= 'ori'.$v2->bulan;
					$val   = 0;
					if($dt_loop['status']):
						$val = $dt_loop['dt'][$v2->tahun][$field];
					else:
						$key = multidimensional_search($dSum, array(
							'kode_cabang'=>$v->kode_cabang,
							'data_core'	=> $v2->tahun
						));
						if(strlen($key)>0):
							$val = $dSum[$key][$field];
						elseif($v2->singkatan == arrSumberData()['real']):
							$val = 0;
							if($v2->tahun != $temp_tahun):
								$temp_tahun = $v2->tahun;
								$data_core = get_data_core([$coa],[$v2->tahun],'TOT_'.$v->kode_cabang);
							endif;
							if(isset($data_core[$v2->tahun])):
								$core_key = multidimensional_search($data_core[$v2->tahun], array(
									'glwnco'	=> $coa
								));
								if(strlen($core_key)>0):
									$kali_minus = $data_core[$v2->tahun][$core_key]['kali_minus'];
									$val 		= $data_core[$v2->tahun][$core_key][$field];
									$val 		= kali_minus($val,$kali_minus);
								endif;
							endif;
						endif;
					endif;
					$item .= '<td class="text-right bg-c'.($count+1).'">'.custom_format(view_report($val)).'</td>';
					if(isset($dt[$v2->tahun][$field])): $dt[$v2->tahun][$field] += $val; else: $dt[$v2->tahun][$field] = $val; endif;
					$dtSaved[$v2->tahun][$field1] = $val;
				}
				$where = [
					'kode_cabang' 	=> $v->kode_cabang,
					'kode_anggaran'	=> $anggaran->kode_anggaran,
					'tahun'			=> $anggaran->tahun_anggaran,
					'coa'			=> $coa,

				];
				if(!$dt_loop['status']):
					checkForSaved($dtSaved,$where);
				endif;

				$item .= '</tr>';
				$item .= $dt_loop['item'];
			}
		endif;

		return [
			'status' => $status,
			'item'	 => $item,
			'dt'	 => $dt,
		];
	}

	function checkForSaved($data,$p1){
		foreach($data as $k => $v){
			$ck = get_data('tbl_indek_besaran',[
				'select' => 'id',
				'where'	 => [
					'kode_cabang'	=> $p1['kode_cabang'],
					'kode_anggaran'	=> $p1['kode_anggaran'],
					'coa'			=> $p1['coa'],
					'tahun_core'	=> $k
				]
			])->row();
			if($ck):
				update_data('tbl_indek_besaran',$v,'id',$ck->id);
			else:
				$v['kode_anggaran'] = $p1['kode_anggaran'];
				$v['kode_cabang'] 	= $p1['kode_cabang'];
				$v['coa']			= $p1['coa'];
				$v['tahun_core']	= $k;
				$parent_id = '0';
				if($k != $p1['tahun']):
					$parent_id = $p1['kode_cabang'];
				endif;
				$v['parent_id'] = $parent_id;
				$v['is_active']	= 1;
				insert_data('tbl_indek_besaran',$v);
			endif;
		}
	}
?>