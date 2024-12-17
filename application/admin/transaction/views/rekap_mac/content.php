<div class="main-container">
	<div class="row">
		<div class="col-sm-12">
			<div class="card">
				<div class="card-header text-center">
					MONITORING BANK JATENG <?= $tahun->keterangan ?><br>
					BERDASAR KANTOR CABANG DAN CAPEM <br>
					<red><?=  $coa->glwnco.' - '.$coa->glwdes ?></red>
				</div>
				<div class="card-body">
					<div class="table-responsive tab-pane fade active show height-window t-1" data-height="50">
						<table class="table table-striped table-bordered table-app table-hover">
							<thead class="sticky-top">
								<tr>
									<th colspan="10"><?= get_view_report() ?></th>
								</tr>
								<tr>
									<th width="30" class="text-center align-middle"><?= lang('no') ?></th>
									<th class="mw-100 text-center align-middle"><?= lang('kode_cabang') ?></th>
									<th class="mw-250 text-center align-middle"><?= lang('nama_kantor') ?></th>
									<th class="mw-150 text-center align-middle"><?= month_lang($bulan).' '.($tahun->tahun_anggaran-1) ?> <br>Real</th>
									<th class="mw-150 text-center align-middle"><?= month_lang(12).' '.($tahun->tahun_anggaran-1) ?><br>Real</th>
									<th class="mw-150 text-center align-middle"><?= month_lang($month_before).' '.($year_before) ?> <br>Real</th>
									<th class="mw-150 text-center align-middle"><?= month_lang($bulan).' '.($tahun->tahun_anggaran) ?> <br>Renc</th>
									<th class="mw-150 text-center align-middle"><?= month_lang($bulan).' '.($tahun->tahun_anggaran) ?> <br>Real</th>
									<th class="mw-150 text-center align-middle">Penc <br>(%)</th>
									<th class="mw-150 text-center align-middle">Pert <br>(%)</th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="main-container">
	<div class="row">
		<div class="col-sm-12">
			<div class="card">
				<div class="card-header text-center">
					PENCAPAIAN DARI RENCANA BERDASARKAN KANTOR CABANG <br>
					<red><?=  $coa->glwnco.' - '.$coa->glwdes ?></red>
				</div>
				<div class="card-body">
					<div class="table-responsive tab-pane fade active show t-2">
						<table class="table table-striped table-bordered table-hover tbl-total">
							<thead class="sticky-top">
								<th width="30" class="text-center align-middle"><?= lang('no') ?></th>
								<th class="mw-250 text-center align-middle"><?= lang('kantor') ?></th>
								<?php
									foreach ($keterangan as $k => $v) {
										echo '<th class="mw-150 text-center align-middle">'.$v['nama'].'</th>';
									}
								?>
							</thead>
							<tbody></tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>