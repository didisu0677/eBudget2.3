<?php

	// header
	$item_header = '<thead class="sticky-top">';
	$item_header .= '<tr>';
	$item_header .= '<th width="30" class="text-center">'.lang('kode_cabang').'</th>';
	$item_header .= '<th class="text-center min-w-ket">'.lang('cabang').'</th>';
	foreach ($days as $v) {
		$item_header .= '<th class="text-center min-w-100">'.c_date($v).'</th>';
	}
	$item_header .= '</tr>';
	$item_header .= '</thead>';

	$item = '<tbody>';
	$data = [
		'days' => $days,
		'list' => $list,
		'cabang' => $cabang,
	];
	foreach($cabang[0] as $v){
		$dt_more = more($v->id,$data,1);

		$item .= '<tr>';
		$item .= '<td>'.remove_spaces($v->kode_cabang).'</td>';
		$item .= '<td>'.remove_spaces($v->nama_cabang).'</td>';
		$key = multidimensional_search($list,['kode_cabang' => $v->kode_cabang]);
		foreach ($days as $day) {
			$field = str_replace('-','_',$day);
			$val = 0;
			if($dt_more['status']):
				$val = $dt_more['dt'][$field];
			elseif(strlen($key)>0):
				$val = $list[$key][$field];
			endif;
			$item .= '<td class="text-right">'.custom_format($val).'</td>';
		}
		$item .= '</tr>';
		$item .= $dt_more['item'];
	}
	$item .= '</tbody>';
	echo $item_header.$item;

	function more($id,$data,$count){
		$days = $data['days'];
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
				$key = multidimensional_search($list,['kode_cabang' => $v->kode_cabang]);
				foreach ($days as $day) {
					$field = str_replace('-','_',$day);
					$val = 0;
					if($dt_more['status']):
						$val = $dt_more['dt'][$field];
					elseif(strlen($key)>0):
						$val = $list[$key][$field];
					endif;
					$item .= '<td class="text-right">'.custom_format($val).'</td>';
					if(isset($dt[$field])):
						$dt[$field] += checkNumber($val);
					else:
						$dt[$field] = checkNumber($val);
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