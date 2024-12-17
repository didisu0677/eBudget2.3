<?php
$item = '';

$temp_a = '';// temp id kebijakan fungsi
$temp_b = '';// temp kode divisi
$no = 0;
foreach ($list as $k => $v) {
	if(($temp_a != $v->id_kebijakan_fungsi) or ($v->nama_kebijakan == '$$SUBDIV' && $temp_b != $v->kode_div)):
		$temp_a = $v->id_kebijakan_fungsi;
		$no = 0;
		$nm_kebijakan = $v->nama_kebijakan;
		if($v->nama_kebijakan == '$$SUBDIV'):
			$temp_b = $v->kode_div;
			$nm_kebijakan = $v->nama_div;
		endif;
		$item .= '<tr><td colspan="16"><b>'.$nm_kebijakan.'</b></td></tr>';
	endif;
	$no++;
	$item .= '<tr>';
	$item .= '<td>'.$no.'</td>';
	$item .= '<td>'.remove_spaces($v->nama_div).'</td>';
	$item .= '<td>'.$v->uraian.'</td>';
	$item .= '<td>'.$v->coa.' - '.remove_spaces($v->glwdes).'</td>';
	for ($i=1; $i <= 12 ; $i++) { 
        $field = 'B_' . sprintf("%02d", $i);
        $val = $v->{$field};
        $item .= '<td class="text-right">'.custom_format(view_report($val)).'</td>';
    }
	$item .= '</tr>';
}
if(count($list)>0):
?>
<div class="card mt-3">
	<div class="card-header text-center"><?= lang('biaya_kebijakan_fungsi_kantor_pusat') ?></div>
	<div class="card-body">
		<div class="table-responsive tab-pane fade active show height-window" id="result2">
			<table class="table table-striped table-bordered table-app table-hover">
				<thead class="sticky-top">
					<tr>
						<th width="30" class="text-center align-middle"><?= lang('no') ?></th>
						<th style="min-width:200px;" class="text-center align-middle"><?= lang('divisi') ?></th>
						<th style="min-width:200px;" class="text-center align-middle"><?= lang('uraian') ?></th>
						<th style="min-width:150px;" class="text-center align-middle"><?= lang('coa') ?></th>
						<?php 
						for ($i=1; $i <= 12 ; $i++) { 
							echo '<th style="min-width:80px;" class="text-center align-middle">'.month_lang($i).'<br>('.lang('pd_bulan').')</th>';
						}
						?>
					</tr>
				</thead>
				<tbody><?= $item ?></tbody>
			</table>
		</div>
	</div>
</div>
<?php endif; ?>