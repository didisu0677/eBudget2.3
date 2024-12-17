<?php
	$bgedit ="";
	$contentedit ="false" ;
	$id = 'keterangan';
	if($access_edit) {
		$bgedit =bgEdit();
		$contentedit ="true" ;
		$id = 'id' ;
	}

	$item = '';
	foreach($dt_coa as $k => $v){
		$item .= '<tr>';
		$item .= '<td>'.($k+1).'</td>';
		$item .= '<td>'.$v->glwnco.' '.remove_spaces($v->glwdes).'</td>';
		foreach($detail_tahun as $k2 => $v2){
			$field 	= 'P_' . sprintf("%02d", ($v2->bulan));
			$bulan 	= sprintf("%02d", $v2->bulan);

			$val = 0;
			$key = multidimensional_search($tabungan, array(
				'coa' 			=> $v->glwnco.'_hadiah',
				'tahun_core'	=> $v2->tahun,
			));
			if(strlen($key)>0):
				$val = $tabungan[$key][$field];
			endif;
			$item .= '<td style="background: '.$bgedit.'"><div style="background:'.$bgedit.'" contenteditable="'.$contentedit.'" class="edit-value text-right" data-name="'.$v->glwnco.'_hadiah|tbl_segment|'.$v2->tahun.$bulan.'|'.$v->glwnco.'_hadiah|'.$anggaran->id.'|'.$cabang->kode_cabang.'" data-id="x0" data-value="'.view_report($val).'">'.custom_format(view_report($val)).'</div></td>';
		}
		$item .= '</tr>';
	}
	echo $item;
?>