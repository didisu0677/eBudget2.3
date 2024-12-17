<?php
	$item = '';
	$no   = 0;
	
	$data['cabang'] = $cabang;
	$data['no'] 	= $no;
	$data['list'] 	= $list;
	$data['anggaran'] 	= $anggaran;
	
	foreach($cabang[0] as $cab){
		$dt_more 	= more($cab->id,$data,1);
		$data 		= $dt_more['data'];
		$data['no']	+= 1;

		$key = null;
		if(!$dt_more['status']):
			$key = multidimensional_search($list, array(
				'kode_cabang' 	=> $cab->kode_cabang,
				'tahun_core'	=> $anggaran->tahun_anggaran,
			));
		endif;

		$item .= $dt_more['item'];
		$item .= '<tr>';
		$item .= '<td>'.$data['no'].'</td>';
		$item .= '<td>'.$cab->kode_cabang.'</td>';
		$item .= '<td>'.$cab->nama_cabang.'</td>';
		for ($i=1; $i <= 12 ; $i++) { 
			$field 	= 'B_' . sprintf("%02d", $i);
			$val = 0;
			if($dt_more['status']):
				$val = $dt_more['dt'][$field];
			elseif(strlen($key)>0):
				$val = $list[$key][$field];
			endif;
			$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
		}
		$item .= '</tr>';
	}
	echo $item;

	function more($id,$data,$count){
		$cabang = $data['cabang'];
		$list 	= $data['list'];
		$anggaran 	= $data['anggaran'];

		$item 	= '';
		$status = false;
		$dt 	= [];

		if(isset($cabang[$id]) && count($cabang[$id])>0):
			$status = true;
			foreach($cabang[$id] as $cab){

				$dt_more 	= more($cab->id, $data,($count+1));
				$data 		= $dt_more['data'];
				$data['no']	+= 1;

				$key = null;
				if(!$dt_more['status']):
					$key = multidimensional_search($list, array(
						'kode_cabang' 	=> $cab->kode_cabang,
						'tahun_core'	=> $anggaran->tahun_anggaran,
					));
				endif;

				$item .= $dt_more['item'];
				$item .= '<tr>';
				$item .= '<td>'.$data['no'].'</td>';
				$item .= '<td>'.$cab->kode_cabang.'</td>';
				$item .= '<td class="sb-'.$count.'">'.$cab->nama_cabang.'</td>';
				for ($i=1; $i <= 12 ; $i++) { 
					$field 	= 'B_' . sprintf("%02d", $i);
					$val = 0;
					if($dt_more['status']):
						$val = $dt_more['dt'][$field];
					elseif(strlen($key)>0):
						$val = $list[$key][$field];
					endif;
					$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';

					if(isset($dt[$field])) $dt[$field] += $val; else $dt[$field] = $val;
				}
				$item .= '</tr>';
			}
		endif;

		return [
			'item' 	 => $item,
			'status' => $status,
			'dt' 	 => $dt,
			'data' 	 => $data,
		];
	}
?>