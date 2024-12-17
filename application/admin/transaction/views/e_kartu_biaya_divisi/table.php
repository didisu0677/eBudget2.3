<?php
	$item = '';
	$no = 0;

	$this->arr_coa_renc = [];

	foreach ($dt_coa as $k => $v) {
		if(isset($coa_renc[$v->glwnco])):
			$val_before = 0;
			for ($i=1; $i <=12 ; $i++) { 
				$field 	= 'B_' . sprintf("%02d", $i);
				$val = 0;
				if(isset($coa_renc[$v->glwnco][$field])):
					$val = $coa_renc[$v->glwnco][$field];
				endif;
				$val_before += checkNumber($val);
				$this->arr_coa_renc[$v->glwnco][$field] = $val_before;
			}
		endif;

		$no++;
		if($k != 0):
			for ($i=1; $i <= 2 ; $i++) { 
				$item .= '<tr><td class="border-none bg-white white">.</td></tr>';
			}
		endif;
		$item .= '<tr>';
		$item .= '<td>'.$no.'</td>';
		$item .= '<td colspan="13"><b>'.$v->glwnco.' - '.remove_spaces($v->glwdes).'</b></td>';
		$item .= '</tr>';

		// renc
		$item .= '<tr>';
		$item .= '<td></td>';
		$item .= '<td>'.lang('rencana').'</td>';
		for ($i=1; $i <= 12 ; $i++) { 
			$field 	= 'B_' . sprintf("%02d", $i);
			$val 	= checkNumber($v->{$field});
			if(isset($this->arr_coa_renc[$v->glwnco][$field])):
				$val = $this->arr_coa_renc[$v->glwnco][$field];
			endif;
			$item .= '<td class="text-right">'.custom_format($val).'</td>';
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
			if($v->glwnco == $sub_coa):
				if(isset($this->arr_data['real'][$field])):
					$val = $this->arr_data['real'][$field];
				endif;
			else:
				if($data_real):
					$val = $data_real->{$field};
				endif;
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
			
			$renc 	= checkNumber($v->{$field});
			if(isset($this->arr_coa_renc[$v->glwnco][$field])):
				$renc 	= checkNumber($this->arr_coa_renc[$v->glwnco][$field]);
			endif;
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
	}

	echo $item;
?>