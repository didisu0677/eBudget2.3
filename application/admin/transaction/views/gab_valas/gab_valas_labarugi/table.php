<?php
	$item = '';
	$data['list'] = $list;
	$data['kode_anggaran'] 	= $kode_anggaran;
	$data['detail_tahun'] 	= $detail_tahun;
	$dt_konsolidasi = [];
	foreach($cabang as $v){
		$dt_more = more($v->id,$data,1);
		$item .= '<tr>';
		$item .= '<td>'.$v->nama_cabang.'</td>';
		
		$key = multidimensional_search($list,[
			'kode_cabang' 	=> $v->kode_cabang,
		]);
		foreach($detail_tahun as $k2 => $v2){
			$field = 'bulan_'.$v2->bulan;
			$val = 0;
			if($dt_more['status']):
				$val = $dt_more['dt'][$field];
			else:		
				if(strlen($key)>0):
					$val = checkNumber($list[$key][$field]);
				endif;
			endif;
			$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
			if(isset($dt_konsolidasi[$field])):
				$dt_konsolidasi[$field] += $val;
			else:
				$dt_konsolidasi[$field] = $val;
			endif;
		}
		$item .= '</tr>';
		$item .= $dt_more['item'];
	}

	$item .= '<tr>';
	$item .= '<td>KONSOLIDASI</td>';
	foreach($detail_tahun as $k2 => $v2){
		$field = 'bulan_'.$v2->bulan;
		$val = 0;
		if(isset($dt_konsolidasi[$field])):
			$val = $dt_konsolidasi[$field];
		endif;
		$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
	}
	$item .= '</tr>';

	echo $item;

	function more($id,$data,$count){
		$item 	= '';
		$status = false;
		$dt 	= [];

		$kode_anggaran 	= $data['kode_anggaran'];
		$detail_tahun 	= $data['detail_tahun'];
		$list 			= $data['list'];
		$cab = get_data('tbl_m_cabang',[
			'select' 	=> 'id,kode_cabang,nama_cabang',
			'where'		=> [
				'is_active' => 1,
				'parent_id' => $id,
				'kode_anggaran' => $kode_anggaran,
			],
			'order_by' => 'urutan'
		])->result();
		foreach($cab as $v){
			$status = true;
			$dt_more = more($v->id,$data,($count+1));

			$item .= '<tr>';
			$item .= '<td class="sb-'.$count.' bg-c'.$count.'">'.$v->nama_cabang.'</td>';
			$key = multidimensional_search($list,[
				'kode_cabang' 	=> $v->kode_cabang,
			]);
			foreach($detail_tahun as $k2 => $v2){
				$field = 'bulan_'.$v2->bulan;
				$val = 0;
				if($dt_more['status']):
					$val = $dt_more['dt'][$field];
				else:
					if(strlen($key)>0):
						$val = checkNumber($list[$key][$field]);
					endif;
				endif;
				if(isset($dt[$field])):
					$dt[$field] += $val;
				else:
					$dt[$field] = $val;
				endif;
				$item .= '<td class="bg-c'.$count.' text-right">'.custom_format(view_report($val)).'</td>';
			}
			$item .= '</tr>';
			$item .= $dt_more['item'];
		}
		return [
			'item' 		=> $item,
			'status'	=> $status,
			'dt' 		=> $dt
		];
	}
?>