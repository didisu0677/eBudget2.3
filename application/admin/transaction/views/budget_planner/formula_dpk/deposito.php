<?php
	$item = '';
	foreach($dt_coa as $k => $v){
		$item .= '<tr>';
		$item .= '<td>'.$v->coa.'</td>';
		$item .= '<td>'.remove_spaces($v->nama).'</td>';
		$item .= '<td class="text-right">'.custom_format($v->rate,false,2).'</td>';
		$temp_tahun = '';
		foreach($detail_tahun as $k2 => $v2){
			$field  = 'P_' . sprintf("%02d", $v2->bulan);
			$val = 0;
			if($temp_tahun != $v2->tahun):
				$key 	= multidimensional_search($dt, array(
					'coa' 	 	=> $v->coa,
					'tahun_core' => $v2->tahun,
				));
			endif;
			if(strlen($key)>0):
				$val = $dt[$key][$field];
			endif;
			$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
		}
		$item .= '</tr>';
	}

	echo $item;
?>