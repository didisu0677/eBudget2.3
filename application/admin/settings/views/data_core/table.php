<?php
	$item = '';
	foreach($neraca['coa'] as $coa){
		$item .= '<tr>';
		$item .= '<td>'.$coa->glwnco.'</td>';
		$item .= '<td>'.remove_spaces($coa->glwdes).'</td>';
		for ($i=1; $i <= 12 ; $i++) { 
			$field 	= 'B_' . sprintf("%02d", $i);
			$val  	= kali_minus($coa->{$field},$coa->kali_minus);
			$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
		}
		$item .= '</tr>';

		$dt_more = more($coa->glwnco,0,$neraca);
		$item .= $dt_more['item'];
	}
	foreach($labarugi['coa'] as $coa){
		$item .= '<tr>';
		$item .= '<td>'.$coa->glwnco.'</td>';
		$item .= '<td>'.remove_spaces($coa->glwdes).'</td>';
		for ($i=1; $i <= 12 ; $i++) { 
			$field 	= 'B_' . sprintf("%02d", $i);
			$val  	= kali_minus($coa->{$field},$coa->kali_minus);
			$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
		}
		$item .= '</tr>';

		$dt_more = more($coa->glwnco,0,$labarugi);
		$item .= $dt_more['item'];
	}
	echo $item;

	function more($id,$count,$coa){
		$item 		= '';
		$status		= false;
		if(isset($coa['coa'.$count][$id])):
			$status = true;
			$count2 = $count + 1;
			foreach ($coa['coa'.$count][$id] as $k => $v) {
				$item .= '<tr>';
				$item .= '<td>'.$v->glwnco.'</td>';
				$item .= '<td class="sb-'.$count2.'">'.remove_spaces($v->glwdes).'</td>';
				for ($i=1; $i <= 12 ; $i++) { 
					$field 	= 'B_' . sprintf("%02d", $i);
					$val  	= kali_minus($v->{$field},$v->kali_minus);
					$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
				}
				$item .= '</tr>';

				$dt_more = more($v->glwnco,$count2,$coa);
				$item .= $dt_more['item'];
			}
		endif;

		return [
			'status' => $status,
			'item'	 => $item,
		];
	}
?>