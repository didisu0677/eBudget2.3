<?php
	$data['kode_anggaran'] = $kode_anggaran;

	$item = more(0,$kode_anggaran,0,$data);

	function more($id,$kode_anggaran,$count,$data){
		$CI = get_instance();
		$item = '';
		$dt = dt_cabang_nett($id,$kode_anggaran);
		if(count($dt)>0):
			foreach ($dt as $k => $v) {
				$title = lang('selisih').' '.lang('coa').' "'.$v->glwnco.' - '.remove_spaces($v->glwdes).'" '.lang('bulan').' '.month_lang(12);

				$item .= '<tr>';
				$item .= '<td>'.$v->kode_cabang.'</td>';
				$item .= '<td class="sb-'.$count.'">'.remove_spaces($v->nama_cabang).'</td>';
				if($v->status_group == 1):
					$item .= '<td class="text-right"></td>';
					$item .= '<td class="text-right"></td>';
				else:
					if($v->status != 1):
						$item .= '<td class="text-center">'.lang('tidak').'</td>';
					else:
						$item .= '<td class="text-center">'.lang('ok').'</td>';
					endif;
					$x = $v->n_B_012;
					$y = $v->b_B_012;
					$val = checkNumber($x) - checkNumber($y);
					$item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
				endif;
				$item .= '</tr>';
				$item .= more($v->id,$kode_anggaran,($count+1),$data);
				$CI->title_modal = $title;
			}
		endif;
		return $item;
	}

	function dt_cabang_nett($id,$kode_anggaran){
		$ls = get_data('tbl_m_cabang a',[
			'select' => '
				a.id,a.kode_cabang,a.nama_cabang,a.status_group,
				b.glwnco,b.glwdes,
				ifnull(c.bulan_12,0) as n_B_012,
				ifnull(d.B_12,0) as b_B_012,
				e.status
			',
			'join' => [
				"tbl_m_coa b on b.glwnco = '59999' and b.kode_anggaran = a.kode_anggaran and b.glwnco != '' type left",
				"tbl_labarugi c on c.glwnco = b.glwnco and c.kode_anggaran = a.kode_anggaran and c.kode_cabang = a.kode_cabang type left",
				"tbl_budget_nett_labarugi d on d.coa = b.glwnco and d.kode_anggaran = a.kode_anggaran and d.kode_cabang = a.kode_cabang type left",
				"tbl_history_to_budget_nett_labarugi e on e.kode_anggaran = a.kode_anggaran and e.kode_cabang = a.kode_cabang type left"
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
?>

<div class="table-responsive tab-pane fade active show height-window" data-height="10">
	<table class="table table-striped table-bordered table-app table-hover">
		<thead class="sticky-top">
			<tr>
				<th width="80" class="text-center"><?= lang('kode_cabang') ?></th>
				<th class="text-center"><?= lang('nama_cabang') ?></th>
				<th class="text-center" width="100"><?= lang('status_budget') ?></th>
				<th class="text-center" width="100"><?= lang('selisih').' '.lang('bulan').' '.month_lang(12) ?></th>
			</tr>
		</thead>
		<tbody>
			<?= $item ?>
		</tbody>
	</table>
</div>