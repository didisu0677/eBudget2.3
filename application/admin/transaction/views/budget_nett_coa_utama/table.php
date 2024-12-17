<?php
$CI = get_instance();
$no = 0;
$CI->arrTotalCoa = [];
$id_div = $kode_cabang;

$item_header = '<tr>';
$item_header .= '<th></th>';
$item_header .= '<th></th>';
$item_header .= '<th></th>';

$item_header2 = '<tr>';
$item_header2 .= '<th>'.lang('no').'</th>';
$item_header2 .= '<th class="text-center mw-100">'.lang('kode_cabang').'</th>';
$item_header2 .= '<th class="text-center mw-250">'.lang('nama_cabang').'</th>';

// loop header
foreach ($group as $k => $v) {
	foreach ($v as $k2 => $v2) {
		if(in_array($v2->glwdes,$ck_coa) or in_array('All',$ck_coa)):
			$no ++;
			if($no != 1) $item_header .= '<th class="border-none bg-white mw-100"></th>';
			$item_header .= '<th colspan="'.count($detail_tahun).'">'.$v2->coa.' - '.remove_spaces($v2->glwdes).'</th>';

			if($no != 1) $item_header2 .= '<th class="border-none bg-white mw-100"></th>';
			foreach($detail_tahun as $v3){
				$item_header2 .= '<th class="text-center mw-150">'.month_lang($v3->bulan).' - '.$v3->tahun.'<br>('.$v3->singkatan.')</th>';
			}
		endif;
	}
	if(count($v)>1 and (in_array('Total '.$k,$ck_coa) or in_array('All',$ck_coa))):
		$no ++;
		if($no != 1) $item_header .= '<th class="border-none bg-white mw-100"></th>';
		$item_header .= '<th colspan="'.count($detail_tahun).'">Total '.remove_spaces($k).'</th>';

		if($no != 1) $item_header2 .= '<th class="border-none bg-white mw-100"></th>';
		foreach($detail_tahun as $v3){
			$item_header2 .= '<th class="text-center mw-150">'.month_lang($v3->bulan).' - '.$v3->tahun.'<br>('.$v3->singkatan.')</th>';
		}
	endif;
}
$item_header .= '</tr>';
$item_header2 .= '</tr>';

$item = '';
// loop body
$no = 0;
$data = [
	'ck_coa' 		=> $ck_coa,
	'group' 		=> $group,
	'detail_tahun' 	=> $detail_tahun
];
foreach ($cab['cabang'] as $k => $v) {
	$kode_cabang = $v['kode_cabang'];
	$nama_cabang = $v['nama_cabang'];
	$dt_more = more($v['kode_cabang'],$cab,$no,0,$data);
	if($dt_more['status']):
		$no 	= $dt_more['no'];
		$dt 	= $dt_more['dt'];
		$item 	.= $dt_more['item'];
	endif;

	$no++;
	$item .= '<tr>';
	$item .= '<td>'.$no.'</td>';
	$item .= '<td>'.remove_spaces($kode_cabang).'</td>';
	$item .= '<td>'.remove_spaces($nama_cabang).'</td>';
	
	$no2 = 0;
	foreach ($group as $k2 => $v2) {
		foreach ($v2 as $k3 => $v3) {
			if(in_array($v3->glwdes,$ck_coa) or in_array('All',$ck_coa)):
				$no2 ++;
				if($no2 != 1) $item .= '<td class="border-none bg-white white"></td>';
			endif;

			$coa_key = multidimensional_search($v['data'], array(
				'coa' => $v3->coa,
			));
			foreach ($detail_tahun as $v4) {
				$field  = 'B_' . sprintf("%02d", $v4->bulan);
				$val = 0;
				if(strlen($coa_key)>0):
					$val = $v['data'][$coa_key][$field];
				endif;
				if(in_array($v3->glwdes,$ck_coa) or in_array('All',$ck_coa)):
					$item .= '<td class="text-right">'.check_value($val,true).'</td>';
				endif;
				
				if(isset($CI->arrTotalCoa[$kode_cabang][$k2][$field])):
					$CI->arrTotalCoa[$kode_cabang][$k2][$field] += $val;
				else:
					$CI->arrTotalCoa[$kode_cabang][$k2][$field] = $val;
				endif; 

			}
		}

		if(count($v2)>1 and (in_array('Total '.$k2,$ck_coa) or in_array('All',$ck_coa))):
			$no2 ++;
			if($no2 != 1) $item .= '<td class="border-none bg-white white"></td>';
			foreach ($detail_tahun as $v4) {
				$field  = 'B_' . sprintf("%02d", $v4->bulan);
				$val = $CI->arrTotalCoa[$kode_cabang][$k2][$field];
				$item .= '<td class="text-right">'.check_value($val,true).'</td>';
			}
		endif;
	}

	$item .= '</tr>';
}

function more($id,$cab,$no,$count,$data){
	$CI    = get_instance();
	$ck_coa= $data['ck_coa'];
	$group = $data['group'];
	$detail_tahun = $data['detail_tahun'];

	$status = false;
	$item 	= '';
	$dt 	= [];
	if(isset($cab[$id])):
		$status = true;
		$count2 = ($count+1);
		foreach ($cab[$id] as $k => $v) {
			$kode_cabang = $v['kode_cabang'];
			$nama_cabang = $v['nama_cabang'];
			$dt_more = more($kode_cabang,$cab,$no,$count2,$data);
			if($dt_more['status']):
				$no 	= $dt_more['no'];
				$dt2 	= $dt_more['dt'];
				$item 	.= $dt_more['item'];
			endif;
			$no++;
			$item .= '<tr>';
			$item .= '<td>'.$no.'</td>';
			$item .= '<td>'.remove_spaces($kode_cabang).'</td>';
			$item .= '<td class="sb-'.$count2.'">'.remove_spaces($nama_cabang).'</td>';

			$no2 = 0;
			foreach ($group as $k2 => $v2) {
				foreach ($v2 as $k3 => $v3) {
					if(in_array($v3->glwdes,$ck_coa) or in_array('All',$ck_coa)):
						$no2 ++;
						if($no2 != 1) $item .= '<td class="border-none bg-white white"></td>';
					endif;

					$coa_key = multidimensional_search($v['data'], array(
						'coa' => $v3->coa,
					));
					foreach ($detail_tahun as $v4) {
						$field  = 'B_' . sprintf("%02d", $v4->bulan);
						$val = 0;
						if(strlen($coa_key)>0):
							$val = $v['data'][$coa_key][$field];
						endif;
						if(in_array($v3->glwdes,$ck_coa) or in_array('All',$ck_coa)):
							$item .= '<td class="text-right">'.check_value($val,true).'</td>';
						endif;
						
						if(isset($CI->arrTotalCoa[$kode_cabang][$k2][$field])):
							$CI->arrTotalCoa[$kode_cabang][$k2][$field] += $val;
						else:
							$CI->arrTotalCoa[$kode_cabang][$k2][$field] = $val;
						endif; 

					}
				}

				if(count($v2)>1 and (in_array('Total '.$k2,$ck_coa) or in_array('All',$ck_coa))):
					$no2 ++;
					if($no2 != 1) $item .= '<td class="border-none bg-white white"></td>';
					foreach ($detail_tahun as $v4) {
						$field  = 'B_' . sprintf("%02d", $v4->bulan);
						$val = $CI->arrTotalCoa[$kode_cabang][$k2][$field];
						$item .= '<td class="text-right">'.check_value($val,true).'</td>';
					}
				endif;
			}

			$item .= '</tr>';
		}
	endif;
	return [
		'status' => $status,
		'item'	 => $item,
		'no'	 => $no,
		'dt'	 => $dt,
	];
}
?>

<div class="col-sm-12 col-12 d-content" id="d-<?= $id_div ?>">
	<div class="card">
		<div class="card-header text-center">
			Rekap Coa Utama <br>
			<?= $anggaran->keterangan ?><br>
			(<?= get_view_report() ?>)
		</div>
		<div class="card-body">
			<div class="table-responsive tab-pane fade active show height-window" data-height="100">
				<table class="table table-striped table-bordered table-app table-hover">
					<thead class="sticky-top">
					<?= $item_header.$item_header2 ?>
					</thead>
					<tbody><?= $item ?></tbody>
				</table>
			</div>
		</div>	
	</div>
</div>