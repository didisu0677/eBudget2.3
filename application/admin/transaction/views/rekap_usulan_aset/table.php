<?php
	$item = '';

	$data['cabang'] = $cabang;
	$data['dKey'] 	= $dKey;
	$data['dSum'] 	= $dSum;
	foreach($cabang[0] as $k => $v){
		$count = 0;
		$dt_more = more($v->id,$data,($count+1));
		$keterangan = '';
		$t_harga 	= 0;
		$t_jumlah 	= 0;

		$item2 = '';
		if($dt_more['status']):
			if(isset($dt_more['dt']['harga'])):
				$t_harga 	= custom_format(view_report($dt_more['dt']['harga']));
			endif;
			if(isset($dt_more['dt']['jumlah'])):
				$t_jumlah 	= custom_format($dt_more['dt']['jumlah']);
			endif;
			for ($i=1; $i <=12 ; $i++) {
				if(isset($dt_more['dt']['B_'.$i])):
					$val = $dt_more['dt']['B_'.$i];
					$item2 	.= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
				else:
					$item2 .= '<td class="text-center">-</td>';
				endif;
			}
		else:
			$filter = array_filter($dKey, function ($var) use ($v) {
			    return ($var['kode_cabang'] == $v->kode_cabang);
			});
			foreach ($filter as $k2 => $v2) {
				$keterangan = $v2['nama_inventaris'];
				for ($i=1; $i <=12 ; $i++) { 
					$key = multidimensional_search($dSum, array(
						'kode_cabang'		=> $v->kode_cabang,
						'nama_inventaris' 	=> $v2['nama_inventaris'],
						'bulan'				=> $i,
					));
					if(strlen($key)>0):
						$jumlah  = $dSum[$key]['jumlah'];
						$harga   = $dSum[$key]['harga'];
						$total 	 = $jumlah * $harga;
						$t_harga 	+= $harga;
						$t_jumlah 	+= $jumlah;
						$item2 	.= '<td class="text-right">'.custom_format(view_report($total)).'</td>';
					else:
						$item2 .= '<td class="text-center">-</td>';
					endif;
				}
			}
			if(count($filter)<=0):
				for ($i=1; $i <=12 ; $i++) {
					$item2 .= '<td class="text-center">-</td>';
				}
			else:
				$t_harga 	= custom_format(view_report($t_harga));
				$t_jumlah 	= custom_format($t_jumlah);
			endif;
		endif;

		$item .= '<tr>';
		$item .= '<td>'.$v->kode_cabang.'</td>';
		$item .= '<td>'.remove_spaces($v->nama_cabang).'</td>';
		if($keterangan) $item .= '<td>'.$keterangan.'</td>'; else $item .= '<td class="text-center">-</td>';
		if($t_harga) $item .= '<td class="text-right">'.$t_harga.'</td>'; else $item .= '<td class="text-center">-</td>';
		if($t_jumlah) $item .= '<td class="text-right">'.$t_jumlah.'</td>'; else $item .= '<td class="text-center">-</td>';
		$item .= $item2;
		$item .= '</tr>';
		$item .= $dt_more['item'];
	}

	echo $item;

	function more($id,$data,$count){
		$cabang = $data['cabang'];
		$dKey 	= $data['dKey'];
		$dSum 	= $data['dSum'];

		$item 	= '';
		$status = false;
		$dt 	= [];

		if(isset($cabang[$id]) and count($cabang[$id])>0):
			$status = true;
			foreach($cabang[$id] as $k => $v){
				$dt_more = more($v->id,$data,($count+1));
				$keterangan = '';
				$t_harga 	= 0;
				$t_jumlah 	= 0;

				$item2 = '';
				if($dt_more['status']):
					if(isset($dt_more['dt']['harga'])):
						$t_harga 	= custom_format(view_report($dt_more['dt']['harga']));
						if(isset($dt['harga'])) $dt['harga'] += $dt_more['dt']['harga']; else $dt['harga'] = $dt_more['dt']['harga'];
					endif;
					if(isset($dt_more['dt']['jumlah'])):
						$t_jumlah 	= custom_format($dt_more['dt']['jumlah']);
						if(isset($dt['jumlah'])) $dt['jumlah'] += $dt_more['dt']['jumlah']; else $dt['jumlah'] = $dt_more['dt']['jumlah'];
					endif;
					for ($i=1; $i <=12 ; $i++) {
						if(isset($dt_more['dt']['B_'.$i])):
							$val = $dt_more['dt']['B_'.$i];
							$item2 	.= '<td class="text-right bg-c'.$count.'">'.custom_format(view_report($val)).'</td>';
							if(isset($dt['B_'.$i])) $dt['B_'.$i] += $val; else $dt['B_'.$i] = $val;
						else:
							$item2 .= '<td class="text-center bg-c'.$count.'">-</td>';
						endif;
					}
				else:
					$filter = array_filter($dKey, function ($var) use ($v) {
					    return ($var['kode_cabang'] == $v->kode_cabang);
					});
					foreach ($filter as $k2 => $v2) {
						$keterangan = $v2['nama_inventaris'];
						for ($i=1; $i <=12 ; $i++) { 
							$key = multidimensional_search($dSum, array(
								'kode_cabang'		=> $v->kode_cabang,
								'nama_inventaris' 	=> $v2['nama_inventaris'],
								'bulan'				=> $i,
							));
							if(strlen($key)>0):
								$jumlah  = $dSum[$key]['jumlah'];
								$harga   = $dSum[$key]['harga'];
								$total 	 = $jumlah * $harga;
								$t_harga 	+= $harga;
								$t_jumlah 	+= $jumlah;
								$item2 	.= '<td class="text-right bg-c'.$count.'">'.custom_format(view_report($total)).'</td>';

								if(isset($dt['harga'])) $dt['harga'] += $harga; else $dt['harga'] = $harga;
								if(isset($dt['jumlah'])) $dt['jumlah'] += $jumlah; else $dt['jumlah'] = $jumlah;
								if(isset($dt['B_'.$i])) $dt['B_'.$i] += $total; else $dt['B_'.$i] = $total;

							else:
								$item2 .= '<td class="text-center bg-c'.$count.'">-</td>';
							endif;
						}
					}
					if(count($filter)<=0):
						for ($i=1; $i <=12 ; $i++) {
							$item2 .= '<td class="text-center bg-c'.$count.'">-</td>';
						}
					else:
						$t_harga 	= custom_format(view_report($t_harga));
						$t_jumlah 	= custom_format($t_jumlah);
					endif;
				endif;

				$item .= '<tr>';
				$item .= '<td class="bg-c'.$count.'">'.$v->kode_cabang.'</td>';
				$item .= '<td class="sb-'.$count.' bg-c'.$count.'">'.remove_spaces($v->nama_cabang).'</td>';
				if($keterangan) $item .= '<td class="bg-c'.$count.'">'.$keterangan.'</td>'; else $item .= '<td class="text-center bg-c'.$count.'">-</td>';
				if($t_harga) $item .= '<td class="text-right bg-c'.$count.'">'.$t_harga.'</td>'; else $item .= '<td class="text-center bg-c'.$count.'">-</td>';
				if($t_jumlah) $item .= '<td class="text-right bg-c'.$count.'">'.$t_jumlah.'</td>'; else $item .= '<td class="text-center bg-c'.$count.'">-</td>';
				$item .= $item2;
				$item .= '</tr>';
				$item .= $dt_more['item'];
			}
		endif;

		return [
			'item' 		=> $item,
			'status'	=> $status,
			'dt'		=> $dt,
		];
	}
?>