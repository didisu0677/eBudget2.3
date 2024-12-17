<?php
$item = '';
foreach($cabang[0] as $k => $v){
	$dt_more = more($v->id,$cabang,1);

	$item .= '<tr>';
	$item .= '<td>'.remove_spaces($v->kode_cabang).'</td>';
	$item .= '<td>'.remove_spaces($v->nama_cabang).'</td>';
	for ($i=1; $i <= 12 ; $i++) {
		$field 	= 'B_' . sprintf("%02d", $i); 
		if($dt_more['status']):
			$val = $dt_more['dt'][$field];
		else:
			$val = $v->{$field};
		endif;
		$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
	}
	$item .= '</tr>';
	$item .= $dt_more['item'];
}
echo $item;
function more($id,$cabang,$count){
	$item 	= '';
	$status = false;
	$dt 	= [];
	if(isset($cabang[$id]) and count($cabang[$id])>0):
		$status = true;
		foreach($cabang[$id] as $k => $v){
			$dt_more = more($v->id,$cabang,($count+1));

			$item .= '<tr>';
			$item .= '<td class="bg-c'.$count.'">'.remove_spaces($v->kode_cabang).'</td>';
			$item .= '<td class="sb-'.$count.' bg-c'.$count.'">'.remove_spaces($v->nama_cabang).'</td>';
			for ($i=1; $i <= 12 ; $i++) {
				$field 	= 'B_' . sprintf("%02d", $i); 
				if($dt_more['status']):
					$val = $dt_more['dt'][$field];
				else:
					$val = $v->{$field};
				endif;
				$item .= '<td class="text-right'.' bg-c'.$count.'">'.custom_format(view_report($val)).'</td>';
				if(isset($dt[$field])) $dt[$field] += checkNumber($val); else $dt[$field] = checkNumber($val);
			}
			$item .= '</tr>';
			$item .= $dt_more['item'];
		}
	endif;
	return [
		'item' 	=> $item,
		'status'=> $status,
		'dt'	=> $dt,
	];
}
?>