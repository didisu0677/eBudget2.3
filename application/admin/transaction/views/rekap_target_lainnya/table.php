<?php
	$item = '';
	$data = [
		'detail_tahun' => $detail_tahun,
		'list' => $list,
		'cabang' => $cabang,
	];
	foreach($cabang[0] as $v){
		$dt_more = more($v->id,$data,1);

		$item .= '<tr>';
		$item .= '<td>'.remove_spaces($v->kode_cabang).'</td>';
		$item .= '<td>'.remove_spaces($v->nama_cabang).'</td>';
		$temp_tahun = '';
		$key = '';
		foreach ($detail_tahun as $k2 => $v2) {
			$field 	= 'B_' . sprintf("%02d", $v2->bulan);
			if($dt_more['status']):
				$val = $dt_more['dt'][$v2->tahun][$field];
			else:
				$val = 0;
				if($temp_tahun != $v2->tahun):
					$temp_tahun = $v2->tahun;
					$key = multidimensional_search($list,[
						'kode_cabang' => $v->kode_cabang,
						'tahun_core' => $v2->tahun
					]);
				endif;
				if(strlen($key)>0):
					$val = $list[$key][$field];
				endif;
			endif;
			$item .= '<td class="text-right">'.custom_format($val).'</td>';
		}
		$item .= '</tr>';
		$item .= $dt_more['item'];
	}
	echo $item;

	function more($id,$data,$count){
		$detail_tahun = $data['detail_tahun'];
		$list = $data['list'];
		$cabang = $data['cabang'];

		$status = false;
		$dt 	= [];
		$item 	= '';
		if(isset($cabang[$id])):
			$status = true;
			foreach ($cabang[$id] as $k => $v) {
				$dt_more = more($v->id,$data,($count+1));

				$item .= '<tr>';
				$item .= '<td>'.remove_spaces($v->kode_cabang).'</td>';
				$item .= '<td class="sb-'.$count.'">'.remove_spaces($v->nama_cabang).'</td>';
				
				$temp_tahun = '';
				$key = '';
				foreach ($detail_tahun as $k2 => $v2) {
					$field 	= 'B_' . sprintf("%02d", $v2->bulan);
					if($dt_more['status']):
						$val = $dt_more['dt'][$v2->tahun][$field];
					else:
						$val = 0;
						if($temp_tahun != $v2->tahun):
							$temp_tahun = $v2->tahun;
							$key = multidimensional_search($list,[
								'kode_cabang' => $v->kode_cabang,
								'tahun_core' => $v2->tahun
							]);
						endif;
						if(strlen($key)>0):
							$val = $list[$key][$field];
						endif;
					endif;
					$item .= '<td class="text-right">'.custom_format($val).'</td>';

					if(isset($dt[$v2->tahun][$field])):
						$dt[$v2->tahun][$field] += checkNumber($val);
					else:
						$dt[$v2->tahun][$field] = checkNumber($val);
					endif;
				}
				$item .= '</tr>';
				$item .= $dt_more['item'];
			}
		endif;

		return [
			'status' => $status,
			'dt' => $dt,
			'item' => $item,
		];
	}
?>