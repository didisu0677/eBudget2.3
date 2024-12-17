<?php
	$item = '';
	$data = [];
	$data['detail_tahun'] = $detail_tahun;
	foreach($coa['coa'] as $k => $v){
		$dt_more = more($v->glwnco,0,$coa,$data);

		$item .= '<tr>';
		$item .= '<td>'.$v->glwsbi.'</td>';
        $item .= '<td>'.$v->glwnob.'</td>';
        $item .= '<td>'.$v->glwnco.'</td>';
        $item .= '<td>'.remove_spaces($v->glwdes).'</td>';
        foreach($detail_tahun as $k2 => $v2){
        	$field = 'bulan_'.$v2->bulan;
        	$val = $v->{$field};
        	$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
        }
        $item .= '</tr>';
        $item .= $dt_more['item'];
	}

	echo $item;

	function more($id,$count,$coa,$data){
		$detail_tahun = $data['detail_tahun'];
		$item 		= '';
		$status		= false;
		if(isset($coa['coa'.$count][$id]) && count($coa['coa'.$count][$id])>0):
			$status = true;
			$count2 = $count + 1;
			foreach ($coa['coa'.$count][$id] as $k => $v) {
				$dt_more = more($v->glwnco,$count2,$coa,$data);

				$item .= '<tr>';
				$item .= '<td>'.remove_spaces($v->glwsbi).'</td>';
				$item .= '<td>'.remove_spaces($v->glwnob).'</td>';
				$item .= '<td>'.remove_spaces($v->glwnco).'</td>';
				$item .= '<td class="sb-'.$count2.'">'.remove_spaces($v->glwdes).'</td>';
				foreach($detail_tahun as $k2 => $v2){
		        	$field = 'bulan_'.$v2->bulan;
		        	$val = $v->{$field};
		        	$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
		        }
				$item .= '</tr>';
				$item .= $dt_more['item'];
			}
		endif;
		return [
			'status' => $status,
			'item'	 => $item,
		];
	}
?>