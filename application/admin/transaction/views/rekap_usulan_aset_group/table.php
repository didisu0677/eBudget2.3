<?php
	$item = '';
	$data['list'] = $list;
	$data['kode_anggaran'] = $kode_anggaran;
	foreach($cabang as $v){
		$dt_more = more($v->id,$data,1);
		$item .= '<tr>';
		$item .= '<td>'.$v->kode_cabang.'</td>';
		$item .= '<td>'.remove_spaces($v->nama_cabang).'</td>';
		for ($i=1; $i <= 12 ; $i++) {
			$val = 0;
			if($dt_more['status']):
				$val = $dt_more['dt'][$i];
			else:
				$key = multidimensional_search($list,[
					'kode_cabang' 	=> $v->kode_cabang,
					'bulan' 		=> $i
				]);
				if(strlen($key)>0):
					$val = checkNumber($list[$key]['total']);
				endif;
			endif;
			$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
		}
		$item .= '</tr>';
		$item .= $dt_more['item'];
	}
	echo $item;

	function more($id,$data,$count){
		$item 	= '';
		$status = false;
		$dt 	= [];

		$kode_anggaran 	= $data['kode_anggaran'];
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
			$item .= '<td class="bg-c'.$count.'">'.$v->kode_cabang.'</td>';
			$item .= '<td class="sb-'.$count.' bg-c'.$count.'">'.remove_spaces($v->nama_cabang).'</td>';
			for ($i=1; $i <= 12 ; $i++) { 
				$val = 0;
				if($dt_more['status']):
					$val = $dt_more['dt'][$i];
				else:
					$key = multidimensional_search($list,[
						'kode_cabang' 	=> $v->kode_cabang,
						'bulan' 		=> $i
					]);
					if(strlen($key)>0):
						$val = checkNumber($list[$key]['total']);
					endif;
				endif;
				if(isset($dt[$i])):
					$dt[$i] += $val;
				else:
					$dt[$i] = $val;
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