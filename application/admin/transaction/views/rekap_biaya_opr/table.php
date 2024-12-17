<?php
	$data['kode_anggaran'] = $kode_anggaran;
	$data['coa'] = $coa;
	$data['detail_tahun'] = $detail_tahun;

	$item = '';
	$dt_item = more(0,$kode_anggaran,0,$data);
	$item .= $dt_item['item'];

	function more($id,$kode_anggaran,$count,$data){
		$coa = $data['coa'];
		$detail_tahun = $data['detail_tahun'];

		$item = '';
		$dt = dt_cabang($id,$kode_anggaran,$coa);
		
		$arr = [];
		$item = '';
		$status = false;
		if(count($dt)>0):
			$status = true;
			foreach ($dt as $k => $v) {
				$dt_item = more($v->id,$kode_anggaran,($count+1),$data);

				$item .= '<tr>';
				$item .= '<td>'.$v->kode_cabang.'</td>';
				$item .= '<td class="sb-'.$count.'">'.remove_spaces($v->nama_cabang).'</td>';
				for ($i=1; $i <= 12 ; $i++) { 
					$field = 'bulan_b'.$i;
					$val = checkNumber($v->{$field});
					$val *= -1;
					if($dt_item['status']):
						$val = $dt_item['arr'][$field];
					endif;
					$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';

					if(isset($arr[$field])):
						$arr[$field] += $val;
					else:
						$arr[$field] = $val;
					endif;
				}
				$item .= '</tr>';
				$item .= $dt_item['item'];
			}
		endif;

		return [
			'item' 		=> $item,
			'status'	=> $status,
			'arr' 		=> $arr
		];
	}

	function dt_cabang($id,$kode_anggaran,$coa){
		$ls = get_data('tbl_m_cabang a',[
			'select' => '
				b.bulan_b1,
				b.bulan_b2,
				b.bulan_b3,
				b.bulan_b4,
				b.bulan_b5,
				b.bulan_b6,
				b.bulan_b7,
				b.bulan_b8,
				b.bulan_b9,
				b.bulan_b10,
				b.bulan_b11,
				b.bulan_b12,
				a.id,a.kode_cabang,a.nama_cabang,
			',
			'join' => [
				"tbl_biaya b on b.glwnco = '$coa' and b.kode_anggaran = a.kode_anggaran and b.kode_cabang = a.kode_cabang and b.glwnco != '' type left",
			],
			'where' => [
				'a.kode_anggaran' 	=> $kode_anggaran,
				'a.parent_id'		=> $id,
				'a.kode_cabang !='  => '00100',
				'a.is_active' 		=> 1,
			],
			'order_by' => 'a.urutan'
		])->result();
		return $ls;
	}

	$thead = '';
	for ($i = 1; $i <= 12; $i++) {
		$a = array_search($i, array_column($detail_tahun, 'bulan'));
		$column = month_lang($i);
		if(strlen($a)>0):
			$column .= '<span class="txt_title">'."<br> (".$detail_tahun[$a]['singkatan']." PD BLN)".'</span>';
		else:
			$column .= '<span class="txt_title">'."<br> (".arrSumberData()['renc']." PD BLN)".'</span>';
		endif;
		$thead .= '<th class="text-center" style="min-width:100px">'.$column.'</th>';
	}
?>

<div class="table-responsive tab-pane fade active show height-window" data-height="10">
	<table class="table table-striped table-bordered table-app table-hover">
		<thead class="sticky-top">
			<tr>
				<th width="80" class="text-center align-middle"><?= lang('kode_cabang') ?></th>
				<th class="text-center align-middle" style="min-width:330px"><?= lang('nama_cabang') ?></th>
				<?= $thead ?>
			</tr>
		</thead>
		<tbody>
			<?= $item ?>
		</tbody>
	</table>
</div>