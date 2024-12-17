<?php
	$status = false;
	$item = '';
	$sd_bulan = 0;
	$temp_month = '';
	$no = 0;
	$colspan = 9;
	if($access_additional):
		$colspan = 10;
	endif;
	foreach ($ls_detail as $k => $v) {
		$status = true;
		$month = 'B_'.date('m',strtotime($v->tanggal));
		if($temp_month != $month):
			$temp_month = $month;
			$sd_bulan = 0;
			$no = 0;
			if($k != 0):
				$item .= '<tr><td class="border-none bg-white white">.</td></tr>';
			endif;
		endif;
		$no++;

		$sd_bulan += checkNumber($v->biaya);

		if($k == 0):
			$title = $v->glwnco.' - '.remove_spaces($v->glwdes);
			$this->arr_data['coa']['glwnco'] = $v->glwnco;
			$this->arr_data['coa']['glwdes'] = $title;
			$item .= '<tr>';
			$item .= '<td colspan="'.$colspan.'"><b>'.$title.'</b></td>';
			$item .= '</tr>';
		endif;

		$renc  = checkNumber($v->{$month});

		$penc = 0;
		if($renc):
			$penc = ($sd_bulan/$renc)*100;
		endif;

		$deviasi = $renc - $sd_bulan;

		$btn_edit = '';
		if($access_edit && ($v->status == 0 or $access_additional)):
			$btn_edit = '<button type="button" class="btn btn-warning btn-input" data-key="edit" data-id="'.$v->id.'" title="'.lang('ubah').'"><i class="fa-edit"></i></button>';
		endif;
		$btn_delete = '';
		if($access_delete && ($v->status == 0 or $access_additional)):
			$btn_delete = '<button type="button" class="btn btn-danger btn-delete" data-key="delete" data-id="'.$v->id.'" title="'.lang('hapus').'"><i class="fa-trash-alt"></i></button>';
		endif;
		$btn_active = '';
		if($access_edit && $v->status == 1 && $access_additional):
			$btn_active = '<button type="button" class="btn btn-success btn-active" data-key="active" data-id="'.$v->id.'" title="'.lang('proses').'"><i class="fa-check"></i></button>';
		endif;
		$btn_file = '<button type="button" class="btn btn-info btn-file" data-id="'.$v->id.'" title="File"><i class="fa-download"></i></button>';

		$item .= '<tr>';
		$item .= '<td>'.$no.'</td>';
		$item .= '<td>'.date_lang($v->tanggal).'</td>';
		$item .= '<td>'.$v->keterangan.'</td>';
		$item .= '<td class="text-right">'.custom_format($v->biaya).'</td>';
		$item .= '<td class="text-right">'.custom_format($sd_bulan).'</td>';
		$item .= '<td class="text-right">'.custom_format($renc).'</td>';
		$item .= '<td class="text-right">'.custom_format($deviasi).'</td>';
		$item .= '<td class="text-right">'.custom_format($penc,false,2).'%</td>';
		if($access_additional):
			$label = lang('proses'); if($v->status) $label = lang('selesai');
			$item .= '<td>'.$label.'</td>';
		endif;
		$item .= '<td class="button">'.$btn_file.$btn_edit.$btn_delete.$btn_active.'</td>';
		$item .= '</tr>';

		for ($i=1; $i <= 12 ; $i++) { 
			$field = 'B_' . sprintf("%02d", $i);
			$this->arr_data['renc'][$field] = checkNumber($v->{$field});
		}
		$this->arr_data['real'][$month] = checkNumber($sd_bulan);

	}
?>
<?php if($status): ?>
<div class="card mt-3">
	<div class="card-header text-center">
		<?= lang('rincian_biaya') ?>
	</div>
	<div class="card-body">
		<div class="table-responsive tab-pane fade active show height-window">
			<table class="table table-striped table-bordered table-app table-hover">
				<thead class="sticky-top">
					<tr>
						<th width="30" class="text-center align-middle"><?= lang('no') ?></th>
						<th class="text-center align-middle min-w-100"><?= lang('tanggal') ?></th>
						<th class="text-center align-middle min-w-ket"><?= lang('keterangan') ?></th>
						<th class="text-center align-middle min-w-80"><?= lang('biaya').'<br>'.lang('pd_bulan') ?></th>
						<th class="text-center align-middle min-w-80"><?= lang('biaya').'<br>'.lang('sd_bulan') ?></th>
						<th class="text-center align-middle min-w-80"><?= lang('anggaran') ?></th>
						<th class="text-center align-middle min-w-80"><?= lang('deviasi') ?></th>
						<th class="text-center align-middle min-w-80"><?= lang('pencapaian') ?></th>
						<?php if($access_additional): ?>
						<th class="text-center align-middle min-w-80"><?= lang('status') ?></th>
						<?php endif; ?>						
						<th class="text-center align-middle" width="30"></th>
					</tr>
				</thead>
				<tbody>
				<?= $item ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<?php endif; ?>