<?php
	$item = '';
	$no = 0;
	foreach ($dt_coa as $k => $v) {
		$no++;
		$item .= '<tr>';
		$item .= '<td>'.$no.'</td>';
		$item .= '<td colspan="13"><b>'.$v->glwnco.' - '.remove_spaces($v->glwdes).'</b></td>';
		$item .= '</tr>';

		// renc
		$item .= '<tr>';
		$item .= '<td></td>';
		$item .= '<td>'.lang('rencana').'</td>';
		$arr_renc = [];
		$val_before = 0;
		for ($i=1; $i <= 12 ; $i++) { 
			$field 	= 'B_' . sprintf("%02d", $i);
			$val 	= 0;
			if(isset($gab['data_renc'][$field])):
				$val = $gab['data_renc'][$field];
			endif;
			$val_before += checkNumber($val);
			$item .= '<td class="text-right">'.custom_format($val_before).'</td>';
			$arr_renc[$field] = checkNumber($val_before);
		}
		$item .= '</tr>';

		// real
		$item .= '<tr>';
		$item .= '<td></td>';
		$item .= '<td>'.lang('realisasi').'</td>';
		$arr_real = [];
		for ($i=1; $i <= 12 ; $i++) { 
			$field 	= 'B_' . sprintf("%02d", $i);
			$val 	= 0;
			if($gab['data_real']):
				$val = $gab['data_real']->{$field};
			endif;
			$item .= '<td class="text-right">'.custom_format($val).'</td>';
			$arr_real[$field] = checkNumber($val);
		}
		$item .= '</tr>';

		// pencapaian
		$status_penc = false;
		$item .= '<tr>';
		$item .= '<td></td>';
		$item .= '<td>'.lang('pencapaian').'</td>';
		for ($i=1; $i <= 12 ; $i++) { 
			$field 	= 'B_' . sprintf("%02d", $i);
			
			$renc 	= $arr_renc[$field];
			$real 	= $arr_real[$field];

			$penc = 0;
			$text_color = '';
			if($renc):
				$penc = ($real/$renc)*100;
				if($penc>100 && $penc<115):
					$text_color = ' bg-yellow';
					$status_penc = true;
				elseif($penc >= 115):
					$text_color = ' bg-red white';
					$status_penc = true;
				endif;
			endif;
			$item .= '<td class="text-right'.$text_color.'">'.custom_format($penc,false,2).'%</td>';
		}
		$item .= '</tr>';

		if($status_penc):
			$item .= '<tr>';
			$item .= '<td></td>';
			$item .= '<td colspan="13" class="red"><b>'.lang('pengeluaran_lebih_100_persen').'</b></td>';
			$item .= '</tr>';
		endif;

		break;
	}
	echo $item;
?>