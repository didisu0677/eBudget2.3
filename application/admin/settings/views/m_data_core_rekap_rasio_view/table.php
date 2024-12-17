<?php
	$item = '';
	foreach ($list as $k => $v) {
		$item .= '<tr>';
		$item .= '<td>'.$v->kode.'</td>';
		$item .= '<td>'.$v->keterangan.'</td>';
		for ($i=1; $i <= 12 ; $i++) { 
			$field 	= 'B_' . sprintf("%02d", $i);
			$val = '';
			if($v->tipe == 1):
				$val = custom_format($v->{$field});
			elseif($v->tipe == 2):
				$val = custom_format($v->{$field},false,2);
			endif;
			$item .= '<td class="text-right">'.$val.'</td>';
		}
		$item .= '</tr>';
	}
	echo $item;
?>