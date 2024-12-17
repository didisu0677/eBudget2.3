<?php
$item = '';
$temp_a = '';//temp group
$no = 0;
foreach ($list as $k => $v) {
	if($v->grup != $temp_a):
		$temp_a = $v->grup;
		if($no != 0):
			$item .= '<tr>';
			$item .= '<td class="border-none bg-white white">.</td>';
			$item .= '</tr>';
		endif;
		$no++;
		$item .= '<tr>';
		$item .= '<td>'.$v->grup.'</td>';
		$item .= '<td>'.$v->nama_grup.'</td>';
		for ($i=1; $i <= 8 ; $i++) { 
			$item .= '<td></td>';
		}
		$item .= '</tr>';
	endif;

	$key = multidimensional_search($status_inventaris,['kode_inventaris' => $v->kode_inventaris]);
	$txt_status = '';
	if(strlen($key)>0):
		$txt_status = $status_inventaris[$key]['nama'];
	endif;

	$total = checkNumber($v->harga) * checkNumber($v->jumlah);
	$cabang_txt = remove_spaces($v->nama_div);

	$item .= '<tr>';
	$item .= '<td>'.$v->kode_inventaris.'</td>';
	$item .= '<td>'.$v->nama_inventaris.'</td>';
	$item .= '<td>'.$v->catatan.'</td>';
	$item .= '<td class="text-right">'.custom_format(view_report($v->harga)).'</td>';
	$item .= '<td class="text-right">'.custom_format($v->jumlah).'</td>';
	$item .= '<td class="text-right">'.$v->bulan.'</td>';
	$item .= '<td class="text-right">'.custom_format(view_report($total)).'</td>';
	$item .= '<td class="text-right"></td>';
	$item .= '<td>'.$txt_status.'</td>';
	$item .= '<td>'.$cabang_txt.'</td>';
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
						<th colspan="10" class="text-left align-middle"><?= get_view_report() ?></th>
					</tr>
					<tr>
						<th width="60" rowspan="2" class="text-center align-middle"><?= lang('kode') ?></th>
						<th rowspan="2" class="text-center align-middle wd-keterangan"><?= lang('keterangan') ?></th>
						<th rowspan="2" class="text-center align-middle wd-catatan"><?= lang('catatan') ?></th>
						<th class="text-center align-middle wd-harga"><?= lang('harga') ?></th>
						<th class="text-center align-middle wd-harga"><?= lang('jumlah').'/<br>'.lang('jangka_waktu') ?></th>
						<th rowspan="2" class="text-center align-middle wd-bln"><?= lang('bulan') ?></th>
						<th rowspan="2" class="text-center align-middle wd-harga"><?= lang('total') ?></th>
						<th rowspan="2" class="text-center align-middle wd-bln">&nbsp;</th>
						<th rowspan="2" class="text-center align-middle wd-catatan"><?= lang('status') ?></th>
						<th rowspan="2" class="text-center align-middle wd-catatan"><?= lang('divisi') ?></th>
					</tr>
					<tr>
						<th class="text-center">Di isi</th>
						<th class="text-center">Di isi</th>
					</tr>
				</thead>
				<tbody><?= $item ?></tbody>
			</table>
		</div>
	</div>
</div>
<?php endif; ?>