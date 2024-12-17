<?php
$content = '';
foreach($dt as $k => $v){
	$item = '';
	$no = 0;
	$arrData = [];
	foreach($arr_bulan2 as $k2 => $v2){
		$no++;
		$item .= '<tr>';
		$item .= '<td>'.$no.'</td>';
		$item .= '<td>'.$v->nama.' '.$k2.'</td>';
		$temp_tahun = '';
		foreach($v2 as $k3 => $v3){
			$field 	= 'B_' . sprintf("%02d", $v3['bulan']);
			$tahun 	= $k2;
			if($temp_tahun != $tahun):
				$temp_tahun = $tahun;
				$key = multidimensional_search($detail,[
					'id_target_lainnya' => $v->id,
					'tahun_core'		=> $tahun
				]);
			endif;
			$val = 0;
			if(strlen($key)>0):
				$val = $detail[$key][$field];
			endif;
			$arrData[$tahun][$field] = $val;
			if($akses_ubah && $v3['singkatan'] != arrSumberData()['real']):
				$item .= '<td style="background:'.bgEdit().'"><div style="min-height: 10px; width: 100%; overflow: hidden;" contenteditable="true" class="edit-value text-right" data-name="'.$field.'" data-id="'.$v->id.'-'.$tahun.'" value="'.$val.'">'.custom_format($val).'</div></td>';
			else:
				$item .= '<td class="text-right">'.custom_format($val).'</td>';
			endif;
		}
		$item .= '</tr>';
	}

	// deviasi
	$item .= '<tr>';
	$item .= '<td></td>';
	$item .= '<td>Deviasi</td>';
	for ($i=1; $i <= 12 ; $i++) {
		$field 	= 'B_' . sprintf("%02d", $i);
		$tahun1 = (int)$anggaran->tahun_anggaran;
		$tahun2 = ($anggaran->tahun_anggaran - 1);

		$val1 = 0;
		$val2 = 0;
		if(isset($arrData[$tahun1]) && isset($arrData[$tahun1][$field])):
			$val1 = $arrData[$tahun1][$field];
		endif;
		if(isset($arrData[$tahun2]) && isset($arrData[$tahun2][$field])):
			$val2 = $arrData[$tahun2][$field];
		endif;

		$val = $val1 - $val2;
		$item .= '<td class="text-right">'.custom_format($val).'</td>';
	}
	$item .= '</tr>';
	$content .= card($v->kode.' - '.$v->nama,$item);
}
if(count($dt)<=0):
	$content .= '<div class="text-center"><h4>'.lang('data_not_found').'</h4></div>';
endif;
echo $content;
function card($title,$item){
	$header = '';
	for ($i=1; $i <= 12 ; $i++) { 
		$header .= '<th class="text-center" style="min-width:100px;width:100px">'.month_lang($i).'</th>';
	}
	$content = '
		<div class="card mt-2">
			<div class="card-header text-left">'.$title.'</div>
			<div class="card-body">
				<div class="table-responsive tab-pane fade active show">
					<table class="table table-striped table-bordered table-app table-1 table-hover">
						<thead class="sticky-top">
						<tr>
							<th width="30">'.lang('no').'</th>
							<th style="min-width:250px">'.lang('nama').'</th>'.$header.'
						</tr>
						</thead>
						<tbody>'.$item.'</tbody>
					</table>
				</div>
			</div>
		</div>';
	return $content;
}
?>