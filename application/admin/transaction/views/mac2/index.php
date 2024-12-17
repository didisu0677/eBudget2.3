<?php
	function select_custom($label,$id,$data,$opt_key,$opt_name,$value=""){
		echo '<label>'.$label.' &nbsp</label>';
		$select = '<select class="select2 custom-select" id="'.$id.'">';
		foreach ($data as $v) {
			$selected = '';if($v[$opt_key] == $value): $selected = ' selected'; endif;
			$select .= '<option value="'.$v[$opt_key].'"'.$selected.'>'.remove_spaces($v[$opt_name]).'</option>';
		}
		$select .= '</select> &nbsp';
		echo $select;
	}
?>
<style type="text/css">
.persent-text{
	padding-bottom: 10px;
}
.persent-text span{
	font-size: 23px;
	font-weight: 700;
}
.color-red {
  stop-color: #e23131;
}

.color-yellow {
  stop-color: #fbe500;
}

.color-green {
  stop-color: #25cd6b;
}

.gradient-mask {
  visibility: hidden;
}

.gauge-container {
  padding: 20px;
  margin-top: 80px;
  display: flex;
  justify-content: space-around;
}

.gauge {
  height: 220px;
  width: 100%;
}
.gauge .dxg-range.dxg-background-range {
  fill: url(#gradientGauge);
}
.gauge .dxg-line {
  -webkit-transform: scaleX(1.04) scaleY(1.03) translate(-4px, -4px);
          transform: scaleX(1.04) scaleY(1.03) translate(-4px, -4px);
}
.gauge .dxg-line path:first-child,
.gauge .dxg-line path:last-child {
  display: none;
}
.gauge .dxg-line path:nth-child(2),
.gauge .dxg-line path:nth-child(6) {
  stroke: #ed811c;
}
.gauge .dxg-line path:nth-child(3),
.gauge .dxg-line path:nth-child(5) {
  stroke: #a7db29;
}
.gauge .dxg-line path:nth-child(4) {
  stroke: #25cd6b;
}
.gauge .dxg-elements text:first-child {
  -webkit-transform: translate(19px, 13px);
          transform: translate(19px, 13px);
}
.gauge .dxg-elements text:last-child {
  -webkit-transform: translate(-27px, 14px);
          transform: translate(-27px, 14px);
}
.gauge .dxg-value-indicator {
  fill : #000;
}
.gauge .dxg-value-indicator path {
  -webkit-transform: scale(1.2) translate(0, -5px);
          transform: scale(1.2) translate(0, -5px);
  -webkit-transform-origin: center center;
          transform-origin: center center;
}
.gauge .dxg-value-indicator .dxg-title {
  text-transform: uppercase;
}
.gauge .dxg-value-indicator .dxg-title text:first-child {
  -webkit-transform: translateY(5px);
          transform: translateY(5px);
}
.gauge .dxg-value-indicator .dxg-spindle-border:nth-child(4),
.gauge .dxg-value-indicator .dxg-spindle-hole:nth-child(5) {
  -webkit-transform: translate(0, -109px);
          transform: translate(0, -109px);
}
.gauge .dxg-value-indicator .dxg-spindle-hole {
  fill: #26323a;
}

/* chart text center */
.absolute-center {
  position:absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}
.absolute-center span{
	font-weight: 700;
	font-size: 20px;
}
.line-chart{
	width: 100% !important;
	height: 300px !important;
}
.chart_half .half-value{
	position: absolute;
  top: 92%;
  left: 50%;
  transform: translate(-50%, -50%);
}
.chart_half .half-value p{
	font-size: 20px;
  font-weight: 700;
}
.absolute-center2 {
  position:absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}
.div-title{
	margin-bottom: 20px;
}
.div-title span{
	font-size: 20px;
  font-weight: 700;
}
.mt-div{
  margin-top: 5rem;
}
.det-coa span{
	font-size: 13px;
  font-weight: 700;
}
.bg-card{
	background-position: left !important;
  background-repeat: no-repeat !important;
  background-size: cover !important;
}
.dashboard-secondary-text{
	font-size: large;
	font-weight: 700;
}
.bg-transparent2{
	background-color: #ffffff3d !important;
}
.dashboard-main-text span{
	color: #fff !important;
}
table td span{
	color: #000 !important;
}
</style>
<div class="content-header page-data" data-additional="<?= $access_additional ?>">
	<div class="main-container position-relative">
		<div class="header-info">
			<div class="content-title"><?php echo $title; ?></div>
			<?php echo breadcrumb(); ?>
		</div>
		<div class="float-right">
			<?php
			input('hidden',lang('user'),'user_cabang','',user('kode_cabang'));
			select_custom(lang('anggaran'),'filter_tahun',$tahun,'kode_anggaran','keterangan', user('kode_anggaran'));
			echo filter_cabang_admin($access_additional,$cabang);
			select_custom(lang('bulan'),'filter_bulan',$bulan,'value','name');
			?>
		</div>
		<div class="clearfix"></div>
	</div>
</div>
<div class="content-body mt-6">
	<div class="main-container mt-2">
		<div class="row">
			<div class="col-md-12">
				
				<!-- Total Aset/DPK/Kredit/Laba -->
				<div class="mt-2">
					<div class="div-title"><span><?= lang('penc_per_besaran_utama') ?></span></div>
					<div class="row col-md-12">
						<div class="col-md-3 c-aset">
							<div class="text-center persent-text"><span>0</span></div>
							<div class="gauge" id="c-aset"></div>
						</div>
						<div class="col-md-3 c-dpk">
							<div class="text-center persent-text"><span>0</span></div>
							<div class="gauge" id="c-dpk"></div>
						</div>
						<div class="col-md-3 c-kredit">
							<div class="text-center persent-text"><span>0</span></div>
							<div class="gauge" id="c-kredit"></div>
						</div>
						<div class="col-md-3 c-laba">
							<div class="text-center persent-text"><span>0</span></div>
							<div class="gauge" id="c-laba"></div>
						</div>
					</div>
				</div>
				<hr>
				<!-- End Total Aset/DPK/Kredit/Laba -->

				<!-- DPK -->
				<!-- #1 Giro -->
				<div class="d-2100000 mt-div">
					<div class="div-title"><span>.</span></div>
					<div class="row col-md-12 mt-5">
						<div class="col-md-2">
							<div>
								<div class="text-left persent-text"></div>
								<div class="absolute-center2">
									<div style="position: relative;">
										<canvas id="c_2100000_penc" width="200" height="150"></canvas>
										<div class="absolute-center renc text-center">
									    <span></span>
									  </div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-2">
							<div class="absolute-center2">
								<div style="position: relative;">
									<canvas id="c_2100000_half" width="200" height="150"></canvas>
									<div class="absolute-center half text-center">
								    <span></span>
								  </div>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<canvas id="c_2100000_line" class="line-chart"></canvas>
						</div>
						<div class="col-md-4">
							<canvas id="c_2100000_scatter" class="line-chart"></canvas>
						</div>
					</div>
				</div>
				<!-- #1 Giro -->

				<!-- #2 Tabungan -->
				<div class="d-2120011 mt-div">
					<div class="div-title"><span>.</span></div>
					<div class="row col-md-12 mt-5">
						<div class="col-md-2">
							<div>
								<div class="text-left persent-text"></div>
								<div class="absolute-center2">
									<div style="position: relative;">
										<canvas id="c_2120011_penc" width="200" height="150"></canvas>
										<div class="absolute-center renc text-center">
									    <span></span>
									  </div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-2">
							<div class="absolute-center2">
								<div style="position: relative;">
									<canvas id="c_2120011_half" width="200" height="150"></canvas>
									<div class="absolute-center half text-center">
								    <span></span>
								  </div>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<canvas id="c_2120011_line" class="line-chart"></canvas>
						</div>
						<div class="col-md-4">
							<canvas id="c_2120011_scatter" class="line-chart"></canvas>
						</div>
					</div>
				</div>
				<!-- End #2 Tabungan -->

				<!-- #3 Simpanan Berjangka -->
				<div class="d-2130000 mt-div">
					<div class="div-title"><span>.</span></div>
					<div class="row col-md-12 mt-5">
						<div class="col-md-2">
							<div>
								<div class="text-left persent-text"></div>
								<div class="absolute-center2">
									<div style="position: relative;">
										<canvas id="c_2130000_penc" width="200" height="150"></canvas>
										<div class="absolute-center renc text-center">
									    <span></span>
									  </div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-2">
							<div class="absolute-center2">
								<div style="position: relative;">
									<canvas id="c_2130000_half" width="200" height="150"></canvas>
									<div class="absolute-center half text-center">
								    <span></span>
								  </div>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<canvas id="c_2130000_line" class="line-chart"></canvas>
						</div>
						<div class="col-md-4">
							<canvas id="c_2130000_scatter" class="line-chart"></canvas>
						</div>
					</div>
				</div>
				<!-- End #3 Simpanan Berjangka -->

				<!-- #4 Total DPK -->
				<div class="d-602 mt-div">
					<div class="div-title"><span>.</span></div>
					<div class="row col-md-12 mt-5">
						<div class="col-md-2">
							<div>
								<div class="text-left persent-text"></div>
								<div class="absolute-center2">
									<div style="position: relative;">
										<canvas id="c_602_penc" width="200" height="150"></canvas>
										<div class="absolute-center renc text-center">
									    <span></span>
									  </div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-2">
							<div class="absolute-center2">
								<div style="position: relative;">
									<canvas id="c_602_half" width="200" height="150"></canvas>
								</div>
							</div>
							<div class="det-coa text-center"></div>
						</div>
						<div class="col-md-4">
							<canvas id="c_602_line" class="line-chart"></canvas>
						</div>
						<div class="col-md-4">
							<canvas id="c_602_scatter" class="line-chart"></canvas>
						</div>
					</div>
				</div>
				<hr>
				<!-- End #4 Total DPK -->
				<!-- END DPK -->

				<!-- KREDIT -->
				<!-- #1 Loop Kredit -->
				<?php foreach($arr_coa_kredit as $v): ?>
				<div class="d-<?= $v ?> mt-div">
					<div class="div-title"><span>.</span></div>
					<div class="row col-md-12 mt-5">
						<div class="col-md-2">
							<div>
								<div class="text-left persent-text"></div>
								<div class="absolute-center2">
									<div style="position: relative;">
										<canvas id="c_<?= $v ?>_penc" width="200" height="150"></canvas>
										<div class="absolute-center renc text-center">
									    <span></span>
									  </div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-2">
							<div class="absolute-center2">
								<div style="position: relative;">
									<canvas id="c_<?= $v ?>_half" width="200" height="150"></canvas>
									<div class="absolute-center half text-center">
								    <span></span>
								  </div>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<canvas id="c_<?= $v ?>_line" class="line-chart"></canvas>
						</div>
						<div class="col-md-4">
							<canvas id="c_<?= $v ?>_scatter" class="line-chart"></canvas>
						</div>
					</div>
				</div>
				<?php endforeach; ?>
				<!-- End #1 Loop Kredit -->

				<!-- #2 Total Kredit -->
				<div class="d-603 mt-div">
					<div class="div-title"><span>.</span></div>
					<div class="row col-md-12 mt-5">
						<div class="col-md-2">
							<div>
								<div class="text-left persent-text"></div>
								<div class="absolute-center2">
									<div style="position: relative;">
										<canvas id="c_603_penc" width="200" height="150"></canvas>
										<div class="absolute-center renc text-center">
									    <span></span>
									  </div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-2">
							<div class="absolute-center2">
								<div style="position: relative;">
									<canvas id="c_603_half" width="200" height="150"></canvas>
								</div>
							</div>
							<div class="det-coa text-center"></div>
						</div>
						<div class="col-md-4">
							<canvas id="c_603_line" class="line-chart"></canvas>
						</div>
						<div class="col-md-4">
							<canvas id="c_603_scatter" class="line-chart"></canvas>
						</div>
					</div>
				</div>
				<!-- End #2 Total Kredit -->
				
				<!-- #3 loop ECL -->
				<div class="mt-5"></div>
				<?php foreach($arr_coa_ecl as $v): ?>
				<div class="d-<?= $v ?> mt-3">
					<div class="div-title"><span></span></div>
					<div class="row">
						<div class="col-md-3">
							<div class="card">
								<div class="card-body bg-card" style="background: url(<?= base_url('assets/images/bg-blue.jpg') ?>);">
									<div class="dashboard-avatar">
										<div class="icon-avatar bg-transparent2"><i class="fa-paper-plane"></i></div>
									</div>
									<div class="dashboard-content renc">
										<div class="row">
											<div class="col-12">
												<div class="single-line dashboard-secondary-text white"><?= strtoupper(lang('rencana')) ?></div>
												<div class="dashboard-main-text single-line mb-1 white">0</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="card">
								<div class="card-body bg-card" style="background: url(<?= base_url('assets/images/bg-blue2.jpg') ?>);">
									<div class="dashboard-avatar">
										<div class="icon-avatar bg-transparent2"><i class="fa-money-bill"></i></div>
									</div>
									<div class="dashboard-content real">
										<div class="row">
											<div class="col-12">
												<div class="single-line dashboard-secondary-text white"><?= strtoupper(lang('realisasi')) ?></div>
												<div class="dashboard-main-text single-line mb-1 white">0</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="card">
								<div class="card-body bg-card" style="background: url(<?= base_url('assets/images/bg-pink.jpg') ?>);">
									<div class="dashboard-avatar">
										<div class="icon-avatar bg-transparent2"><i class="fa-chart-line"></i></div>
									</div>
									<div class="dashboard-content penc">
										<div class="row">
											<div class="col-12">
												<div class="single-line dashboard-secondary-text white"><?= strtoupper(lang('penc')) ?></div>
												<div class="dashboard-main-text single-line mb-1 white">0</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-3">
							<div class="card">
								<div class="card-body bg-card" style="background: url(<?= base_url('assets/images/bg-red.jpg') ?>);">
									<div class="dashboard-avatar">
										<div class="icon-avatar bg-transparent2"><i class="fa-chart-line"></i></div>
									</div>
									<div class="dashboard-content pert">
										<div class="row">
											<div class="col-12">
												<div class="single-line dashboard-secondary-text white"><?= strtoupper(lang('pert')) ?></div>
												<div class="dashboard-main-text single-line mb-1 white">0</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<?php endforeach; ?>
				<!-- End #3 loop ECL -->

				<hr>
				<!-- END KREDIT -->

				<!-- LABA -->
				<!-- #1 Total Laba -->
				<div class="d-59999 mt-div">
					<div class="div-title"><span>.</span></div>
					<div class="row col-md-12 mt-5">
						<div class="col-md-4">
							<div>
								<div class="text-left persent-text"></div>
								<div class="absolute-center2">
									<div style="position: relative;">
										<canvas id="c_59999_penc" width="200" height="150"></canvas>
										<div class="absolute-center renc text-center">
									    <span></span>
									  </div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<canvas id="c_59999_line" class="line-chart"></canvas>
						</div>
						<div class="col-md-4">
							<canvas id="c_59999_scatter" class="line-chart"></canvas>
						</div>
					</div>
				</div>
				<!-- End #1 Total Laba -->
				<hr>
				<!-- END LABA -->
				
				<!-- Pendapatan & Beban -->
				<div class="mt-div">
					<div class="row">
						<div class="col-md-4">
							<div class="tot_pend">
								<div class="div-title"><span><?= lang('total_pendapatan') ?></span></div>
								<table class="table table-striped table-bordered table-app">
									<thead>
										<tr>
											<th class="text-center renc" width="30%"><?= lang('rencana') ?></th>
											<th class="text-center real" width="30%"><?= lang('realisasi') ?></th>
											<th class="text-center penc" width="20%"><?= lang('penc') ?></th>
											<th class="text-center pert" width="20%"><?= lang('pert') ?></th>
										</tr>
									</thead>
									<tbody></tbody>
								</table>
							</div>
							<?php foreach($arr_coa_pend as $v): ?>
							<div class="d-<?= $v ?> mt-5">
								<div class="div-title"><span></span></div>
								<table class="table table-striped table-bordered table-app">
									<thead>
										<tr>
											<th class="text-center renc" width="30%"><?= lang('rencana') ?></th>
											<th class="text-center real" width="30%"><?= lang('realisasi') ?></th>
											<th class="text-center penc" width="20%"><?= lang('penc') ?></th>
											<th class="text-center pert" width="20%"><?= lang('pert') ?></th>
										</tr>
									</thead>
									<tbody></tbody>
								</table>
							</div>
							<?php endforeach; ?>
							<?php foreach($arr_coa_pend_o as $v): ?>
							<div class="d-<?= $v ?> mt-5">
								<div class="div-title"><span></span></div>
								<table class="table table-striped table-bordered table-app">
									<thead>
										<tr>
											<th class="text-center renc" width="30%"><?= lang('rencana') ?></th>
											<th class="text-center real" width="30%"><?= lang('realisasi') ?></th>
											<th class="text-center penc" width="20%"><?= lang('penc') ?></th>
											<th class="text-center pert" width="20%"><?= lang('pert') ?></th>
										</tr>
									</thead>
									<tbody></tbody>
								</table>
							</div>
							<?php endforeach; ?>
						</div>
						<div class="col-md-4">
							<div class="tot_beban">
								<div class="div-title"><span><?= lang('total_beban') ?></span></div>
								<table class="table table-striped table-bordered table-app">
									<thead>
										<tr>
											<th class="text-center renc" width="30%"><?= lang('rencana') ?></th>
											<th class="text-center real" width="30%"><?= lang('realisasi') ?></th>
											<th class="text-center penc" width="20%"><?= lang('penc') ?></th>
											<th class="text-center pert" width="20%"><?= lang('pert') ?></th>
										</tr>
									</thead>
									<tbody></tbody>
								</table>
							</div>
							<?php foreach($arr_coa_beban as $v): ?>
							<div class="d-<?= $v ?> mt-5">
								<div class="div-title"><span></span></div>
								<table class="table table-striped table-bordered table-app">
									<thead>
										<tr>
											<th class="text-center renc" width="30%"><?= lang('rencana') ?></th>
											<th class="text-center real" width="30%"><?= lang('realisasi') ?></th>
											<th class="text-center penc" width="20%"><?= lang('penc') ?></th>
											<th class="text-center pert" width="20%"><?= lang('pert') ?></th>
										</tr>
									</thead>
									<tbody></tbody>
								</table>
							</div>
							<?php endforeach; ?>
							<?php foreach($arr_coa_beban_o as $v): ?>
							<div class="d-<?= $v ?> mt-5">
								<div class="div-title"><span></span></div>
								<table class="table table-striped table-bordered table-app">
									<thead>
										<tr>
											<th class="text-center renc" width="30%"><?= lang('rencana') ?></th>
											<th class="text-center real" width="30%"><?= lang('realisasi') ?></th>
											<th class="text-center penc" width="20%"><?= lang('penc') ?></th>
											<th class="text-center pert" width="20%"><?= lang('pert') ?></th>
										</tr>
									</thead>
									<tbody></tbody>
								</table>
							</div>
							<?php endforeach; ?>
						</div>
						<div class="col-md-3">
							<div>
								<div class="div-title text-center"><span><?= lang('komposisi_pendapatan') ?></span></div>
								<canvas id="c_pend_half" width="200" height="150"></canvas>
							</div>
							<div class="mt-5">
								<div class="div-title text-center"><span><?= lang('komposisi_beban') ?></span></div>
								<canvas id="c_beban_half" width="200" height="150"></canvas>
							</div>
						</div>
					</div>
				</div>
				<hr>
				<!-- END Pendapatan & Beban -->

				<!-- COA Other -->
				<div class="mt-div">
					<div class="row">
						<?php
							foreach ($arr_coa_other as $k=> $v) {
								$right = '';
								$left  = '';

								$table = '<div class="col-sm-5">';
								$table .= '<table class="table table-bordered table-app table-detail table-normal">
							                <tr>
							                    <th class="text-center" width="100">'.lang('pencapaian').'</th>
							                    <th class="text-center" id="penc"></th>
							                </tr>
							                <tr>
							                    <th class="text-center" width="100">'.lang('deviasi').'</th>
							                    <th class="text-center" id="hemat"></th>
							                </tr>
							            </table></div>';
							    $chart = '<div class="col-sm-7">';
							    $chart .= '<canvas class="pointer" id="chart_bar_'.$v.'" height="100"></canvas></div>';
								if($k % 2 == 0):
									$left 	= $table;
									$right 	= $chart;
								else:
									$left 	= $chart;
									$right 	= $table;
								endif;

								$item = '<div class="mt-3 col-sm-6 v-'.$v.'">';
								$item .= '<div class="card">';
								$item .= '<div class="card-header text-center"></div>';
								$item .= '<div class="card-body row">';
									$item .= $left;
									$item .= $right;
								$item .= '</div></div></div>';
								echo $item;
							}
						?>
					</div>
				</div>
				<!-- EndCOA Other -->

				<div class="mt-div"></div>
			</div>
		</div>
	</div>
</div>
<svg width="0" height="0" version="1.1" class="gradient-mask" xmlns="http://www.w3.org/2000/svg">
  <defs>
      <linearGradient id="gradientGauge">
        <stop class="color-red" offset="0%"/>
        <stop class="color-red" offset="17%"/>
        <stop class="color-yellow" offset="40%"/>
        <stop class="color-yellow" offset="87%"/>
        <stop class="color-green" offset="100%"/>
      </linearGradient>
  </defs>  
</svg>
<script type="text/javascript" src="<?php echo base_url('assets/js/dx.all.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/js/Chart.bundle.min.js'); ?>"></script>
<script type="text/javascript">

//GAUGRECHART
$(function () {

  class GaugeChart {
    constructor(element, params) {
      this._element = element;
      this._initialValue = params.initialValue;
      this._higherValue = params.higherValue;
      this._title = params.title;
      this._subtitle = params.subtitle;
    }

    _buildConfig() {
      let element = this._element;

      return {
        value: this._initialValue,
        valueIndicator: {
          color: '#fff' },

        geometry: {
          startAngle: 180,
          endAngle: 360 },

        scale: {
          startValue: 0,
          endValue: this._higherValue,
          customTicks: [0,25, 50, 75,100,125,150,175,200],
          tick: {
            length: 8 },

          label: {
            font: {
              color: '#87959f',
              size: 9,
              family: '"Open Sans", sans-serif' } } },



        title: {
          verticalAlignment: 'bottom',
          text: this._title,
          font: {
            family: '"Open Sans", sans-serif',
            color: '#000',
            weight: 700,
            size: 22 },

          subtitle: {
            text: this._subtitle,
            font: {
              family: '"Open Sans", sans-serif',
              color: '#fff',
              weight: 700,
              size: 18 } } },



        onInitialized: function () {
          let currentGauge = $(element);
          let circle = currentGauge.find('.dxg-spindle-hole');
          let border = currentGauge.find('.dxg-spindle-border');

          currentGauge.find('.dxg-title text').first().attr('y', 48);
          currentGauge.find('.dxg-title text').last().attr('y', 28);
          currentGauge.find('.dxg-value-indicator').append(border, circle);
        } };


    }

    init() {
      $(this._element).dxCircularGauge(this._buildConfig());
    }}

    
  	$(document).ready(function () {
	  	// chart total dpk
	  	let params = {
			    initialValue: 0,
			    higherValue: 200,
			    subtitle: '0' 
			};
			params.title = 'ASET';
	  	let c_aset = new GaugeChart($('#c-aset'), params);
	    c_aset.init();

	    params.title = 'DPK';
	  	let c_dpk = new GaugeChart($('#c-dpk'), params);
	    c_dpk.init();

	    params.title = 'KREDIT';
	  	let c_kredit = new GaugeChart($('#c-kredit'), params);
	    c_kredit.init();

	    params.title = 'LABA';
	  	let c_laba = new GaugeChart($('#c-laba'), params);
	    c_laba.init();

	    get_arr_coa_other();
  	});
});

// CHART JS
// dpk
var c_2100000_penc,c_2120011_penc,c_2130000_penc,c_602_penc;
var c_2100000_line,c_2120011_line,c_2130000_line,c_602_line;
var c_2100000_scatter,c_2120011_scatter,c_2130000_scatter,c_602_scatter;
var c_2100000_half,c_2120011_half,c_2130000_half,c_602_half;

// kredit
var c_122502_penc,c_122506_penc,c_603_penc;
var c_122502_line,c_122506_line,c_603_line;
var c_122502_scatter,c_122506_scatter,c_603_scatter;
var c_122502_half,c_122506_half,c_603_half;

// laba
var c_59999_penc;
var c_59999_line;
var c_59999_scatter;

// pend & beban
var c_pend_half,c_beban_half;

var option_pie = {
  responsive: true,
  aspectRatio: 1,
  cutoutPercentage: 80,
  legend: {
    display: false
  },
  plugins: {
    title: {
      display: true,
      text: 'Chart.js Doughnut Chart'
    },
  },
}
var option_pie2 = {
  responsive: true,
  legend: {
    display: false
  }
}
var option_line = {
  responsive: true,
  elements: {
    point:{
      radius: 1
    }
  },
  tooltips: {
    "enabled": true,
    callbacks: {
      label: function(tooltipItem, data) {
        var label = data.datasets[tooltipItem.datasetIndex].label || '';

        if (label) {
            label += ': ';
        }
        label += customFormat(tooltipItem.yLabel / 1,0);
        return label;
      }
    }
	},
  stacked: false,
  plugins: {
    title: {
      display: true,
    },
  },
  legend: {
    position: 'bottom',
    labels: {
     	fontSize: 9,
    }
  },
  scales: {
  	xAxes: [{
  		gridLines: {
        display:false
      },
    	beginAtZero: true,
    	ticks: {
       	autoSkip: true,
       	display : true,
    	}
  	}],
    yAxes: [{
    	display:false,
    	gridLines: {
        display:false
      },
      ticks: {
       	display : false,
    	},
    }],
    },
    
};

var option_bar = {
  responsive: true,
  elements: {
    point:{
      radius: 1
    }
  },
  tooltips: {
    "enabled": true,
    callbacks: {
      label: function(tooltipItem, data) {
        var label = data.datasets[tooltipItem.datasetIndex].label || '';

        if (label) {
            label += ': ';
        }
        label += customFormat(tooltipItem.xLabel / 1,0);
        return label;
      }
    }
	},
  stacked: false,
  plugins: {
    title: {
      display: true,
    },
  },
  legend: {
  	display: false,
    position: 'bottom',
    labels: {
     	fontSize: 9,
    }
  },
  scales: {
  	xAxes: [{
  		display: false,
  	}],
    yAxes: [{
    	display:false,
    }],
    },
    
};

var option_bar2 = {
	tooltips: {
	    "enabled": true,
	    callbacks: {
	        label: function(tooltipItem, data) {
	            var label = data.datasets[tooltipItem.datasetIndex].label || '';

	            if (label) {
	                label += ': ';
	            }
	            label += numberFormat(tooltipItem.yLabel / 1,0);
	            return label;
	        }
	    }
	},
	maintainAspectRatio: false,
	responsive: true,
    scales: {
		xAxes: [{
			display: false,
	    beginAtZero: true,
	    ticks: {
	        autoSkip: false
	    },
	    gridLines: {
          display:false
      },
		}],
		yAxes: [{
      display: false,
      gridLines: {
            display:false
        },
      ticks: {
        	beginAtZero: true,
    	// Abbreviate the millions
    		callback: function(value, index, values) {
        	return numberFormat(value / 1,0);
    		}
  		}
    }],
  },

	legend: {
		display: true,
		position: 'bottom',
			labels: {
			boxWidth: 15,
		}
	}
};

var option_scatter = {
	responsive : true,
	tooltips: {
    callbacks: {
      label: function(tooltipItem, data) {
        var datasetLabel = data.datasets[tooltipItem.datasetIndex].label || 'Other';
   			return datasetLabel + ': (' + tooltipItem.xLabel + ', ' + tooltipItem.yLabel + ')';
      }
    }
  },
	plugins: {
    title: {
      display: true,
    },
  },
  legend: {
    display: false
  },
  scales: {
    xAxes: [{
      ticks: {
        // min: 0,
        // max: 160,
        fontSize: 7
      },
      gridLines: {
        display:false
      },
    }],
  	yAxes: [{
    	ticks: {
      	// min: -20,
      	// max: 20,
      	fontSize: 7,
    	},
    	gridLines: {
        display:false
      },
  	}],
	}
};

var option_pie_half = {
	responsive:true,
  circumference: 1 * Math.PI,
  rotation: 1 * Math.PI,
  legend: {
      display: false
  },
  tooltip: {
      enabled: false
  },
  cutoutPercentage: 80
}

$(function(){
	var ctx = document.getElementById('c_2100000_penc').getContext('2d');
	c_2100000_penc = new Chart(ctx, {
		type: 'doughnut',
		options : option_pie,
	});

	ctx = document.getElementById('c_2120011_penc').getContext('2d');
	c_2120011_penc = new Chart(ctx, {
		type: 'doughnut',
		options : option_pie,
	});

	ctx = document.getElementById('c_2130000_penc').getContext('2d');
	c_2130000_penc = new Chart(ctx, {
		type: 'doughnut',
		options : option_pie,
	});

	ctx = document.getElementById('c_602_penc').getContext('2d');
	c_602_penc = new Chart(ctx, {
		type: 'doughnut',
		options : option_pie,
	});

	// line
	ctx = document.getElementById('c_2100000_line').getContext('2d');
	c_2100000_line = new Chart(ctx, {
		type: 'line',
		options : option_line,
	});

	ctx = document.getElementById('c_2120011_line').getContext('2d');
	c_2120011_line = new Chart(ctx, {
		type: 'line',
		options : option_line,
	});

	ctx = document.getElementById('c_2130000_line').getContext('2d');
	c_2130000_line = new Chart(ctx, {
		type: 'line',
		options : option_line,
	});

	ctx = document.getElementById('c_602_line').getContext('2d');
	c_602_line = new Chart(ctx, {
		type: 'line',
		options : option_line,
	});

	ctx = document.getElementById('c_2100000_scatter').getContext('2d');
	c_2100000_scatter = new Chart(ctx, {
		type: 'scatter',
		options : option_scatter,
	});

	ctx = document.getElementById('c_2120011_scatter').getContext('2d');
	c_2120011_scatter = new Chart(ctx, {
		type: 'scatter',
		options : option_scatter,
	});

	ctx = document.getElementById('c_2130000_scatter').getContext('2d');
	c_2130000_scatter = new Chart(ctx, {
		type: 'scatter',
		options : option_scatter,
	});

	ctx = document.getElementById('c_602_scatter').getContext('2d');
	c_602_scatter = new Chart(ctx, {
		type: 'scatter',
		options : option_scatter,
	});

	ctx = document.getElementById("c_2100000_half");
  c_2100000_half = new Chart(ctx, {
    type: 'doughnut',
    options: option_pie
  });

  ctx = document.getElementById("c_2120011_half");
  c_2120011_half = new Chart(ctx, {
    type: 'doughnut',
    options: option_pie
  });

  ctx = document.getElementById("c_2130000_half");
  c_2130000_half = new Chart(ctx, {
    type: 'doughnut',
    options: option_pie
  });

  ctx = document.getElementById("c_602_half");
  c_602_half = new Chart(ctx, {
    type: 'pie',
    options: option_pie2
  });

  // kr
  ctx = document.getElementById('c_122502_penc').getContext('2d');
	c_122502_penc = new Chart(ctx, {
		type: 'doughnut',
		options : option_pie,
	});

	ctx = document.getElementById('c_122506_penc').getContext('2d');
	c_122506_penc = new Chart(ctx, {
		type: 'doughnut',
		options : option_pie,
	});

	ctx = document.getElementById('c_603_penc').getContext('2d');
	c_603_penc = new Chart(ctx, {
		type: 'doughnut',
		options : option_pie,
	});

	ctx = document.getElementById('c_122502_line').getContext('2d');
	c_122502_line = new Chart(ctx, {
		type: 'line',
		options : option_line,
	});

	ctx = document.getElementById('c_122506_line').getContext('2d');
	c_122506_line = new Chart(ctx, {
		type: 'line',
		options : option_line,
	});

	ctx = document.getElementById('c_603_line').getContext('2d');
	c_603_line = new Chart(ctx, {
		type: 'line',
		options : option_line,
	});

	ctx = document.getElementById('c_122502_scatter').getContext('2d');
	c_122502_scatter = new Chart(ctx, {
		type: 'scatter',
		options : option_scatter,
	});

	ctx = document.getElementById('c_122506_scatter').getContext('2d');
	c_122506_scatter = new Chart(ctx, {
		type: 'scatter',
		options : option_scatter,
	});

	ctx = document.getElementById('c_603_scatter').getContext('2d');
	c_603_scatter = new Chart(ctx, {
		type: 'scatter',
		options : option_scatter,
	});

	ctx = document.getElementById('c_122502_half').getContext('2d');
	c_122502_half = new Chart(ctx, {
		type: 'horizontalBar',
		options : option_bar,
	});

	ctx = document.getElementById('c_122506_half').getContext('2d');
	c_122506_half = new Chart(ctx, {
		type: 'horizontalBar',
		options : option_bar,
	});

	ctx = document.getElementById('c_603_half').getContext('2d');
	c_603_half = new Chart(ctx, {
		type: 'horizontalBar',
		options : option_bar,
	});
	
	// lb
	ctx = document.getElementById('c_59999_penc').getContext('2d');
	c_59999_penc = new Chart(ctx, {
		type: 'doughnut',
		options : option_pie,
	});
	ctx = document.getElementById('c_59999_line').getContext('2d');
	c_59999_line = new Chart(ctx, {
		type: 'line',
		options : option_line,
	});
	ctx = document.getElementById('c_59999_scatter').getContext('2d');
	c_59999_scatter = new Chart(ctx, {
		type: 'scatter',
		options : option_scatter,
	});

	// pend & beban
	ctx = document.getElementById("c_pend_half");
  c_pend_half = new Chart(ctx, {
    type: 'pie',
    options: option_pie2
  });
  ctx = document.getElementById("c_beban_half");
  c_beban_half = new Chart(ctx, {
    type: 'pie',
    options: option_pie2
  });

})

$('#filter_tahun').change(function(){loadData();});
$('#filter_cabang').change(function(){loadData();});
$('#filter_bulan').change(function(){loadData();});
var controller = '<?= $controller ?>';

var content_status = false;
function get_arr_coa_other(){
	var page = base_url +'transaction/'+controller+'/get_arr_coa_other';
	$.ajax({
			url 	: page,
			type	: 'post',
			dataType: 'json',
			success	: function(response) {
				$.each(response,function(k,v){
					var val = 'chart_bar_'+v;
					var ctx = document.getElementById(val).getContext('2d');
					window[val] = new Chart(ctx, {
						type: 'bar',
						options : option_bar2,
					});

					$("#"+val).click(function(evt){
					   	var coa = v;
					   	var activePoints = window[val].getElementsAtEvent(evt);
					   	if(activePoints.length>0){
					   		window.open(base_url+'transaction/rekap_mac_group?coa='+coa, '_blank');
					   	}
				    });
				});
				content_status = true;
				loadData();
			}
		});
}

var xhr_ajax = null;
function loadData(){
	if(!content_status){
		return false;
	}

	var cabang = $('#filter_cabang option:selected').val();
	if(!cabang){return false;}
	cLoader.open(lang.memuat_data + '...');
    if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }

    var page = base_url + 'transaction/'+controller+'/data';
    page += '/'+ $('#filter_tahun').val();
    page += '/'+ $('#filter_cabang').val();
    page += '/'+ $('#filter_bulan').val();

  	xhr_ajax = $.ajax({
      url: page,
      type: 'post',
			data : {},
      dataType: 'json',
      success: function(res){
      	xhr_ajax = null;
      	
      	if(!res.status){
      		cLoader.close();
      		cAlert.open(res.message,'failed');
      		return false;
      	}

      	// # dpk
      	let c_dpk = $('#c-dpk').dxCircularGauge('instance');
        let gaugeElement = $(c_dpk._$element[0]);
        let val = res.dpk.dpk_penc;
        gaugeElement.find('.dxg-title text').last().html(`${val}%`);
        c_dpk.value(val);

      	$.each(res.dpk.class,function(k,v){
      		$(k).html(v);
      	})
      	$.each(res.dpk.chart,function(k,v){
      		window[k].data = v;
      		window[k].update();
      	})
      	cLoader.close();
      	loadKredit();
      }
    });
}

var xhr_ajax_kre = null;
function loadKredit(){
	var cabang = $('#filter_cabang option:selected').val();
	if(!cabang){return false;}
	cLoader.open(lang.memuat_data + '...');
    if( xhr_ajax_kre != null ) {
        xhr_ajax_kre.abort();
        xhr_ajax_kre = null;
    }

  var page = base_url + 'transaction/'+controller+'/data_kredit';
  page += '/'+ $('#filter_tahun').val();
  page += '/'+ $('#filter_cabang').val();
  page += '/'+ $('#filter_bulan').val();

	xhr_ajax_kre = $.ajax({
    url: page,
    type: 'post',
		data : {},
    dataType: 'json',
    success: function(res){
    	xhr_ajax_kre = null;
    	
    	if(!res.status){
    		cLoader.close();
    		cAlert.open(res.message,'failed');
    		return false;
    	}

    	// # kredit
    	let c_kredit = $('#c-kredit').dxCircularGauge('instance');
      let gaugeElement = $(c_kredit._$element[0]);
      let val = res.kredit.kredit_penc;
      gaugeElement.find('.dxg-title text').last().html(`${val}%`);
      c_kredit.value(val);

    	$.each(res.kredit.class,function(k,v){
    		$(k).html(v);
    	})
    	$.each(res.kredit.chart,function(k,v){
    		window[k].data = v;
    		window[k].update();
    	})
    	cLoader.close();
    	loadEcl();
    }
  });
}

var xhr_ajax_ecl = null;
function loadEcl(){
	var cabang = $('#filter_cabang option:selected').val();
	if(!cabang){return false;}
	cLoader.open(lang.memuat_data + '...');
    if( xhr_ajax_ecl != null ) {
        xhr_ajax_ecl.abort();
        xhr_ajax_ecl = null;
    }

  var page = base_url + 'transaction/'+controller+'/data_ecl';
  page += '/'+ $('#filter_tahun').val();
  page += '/'+ $('#filter_cabang').val();
  page += '/'+ $('#filter_bulan').val();

	xhr_ajax_ecl = $.ajax({
    url: page,
    type: 'post',
		data : {},
    dataType: 'json',
    success: function(res){
    	xhr_ajax_ecl = null;
    	
    	if(!res.status){
    		cLoader.close();
    		cAlert.open(res.message,'failed');
    		return false;
    	}
    	$.each(res.ecl.class,function(k,v){
    		$(k).html(v);
    	})
    	cLoader.close();
    	loadLaba();
    }
  });
}

var xhr_ajax_laba = null;
function loadLaba(){
	var cabang = $('#filter_cabang option:selected').val();
	if(!cabang){return false;}
	cLoader.open(lang.memuat_data + '...');
    if( xhr_ajax_laba != null ) {
        xhr_ajax_laba.abort();
        xhr_ajax_laba = null;
    }

  var page = base_url + 'transaction/'+controller+'/data_laba';
  page += '/'+ $('#filter_tahun').val();
  page += '/'+ $('#filter_cabang').val();
  page += '/'+ $('#filter_bulan').val();

	xhr_ajax_laba = $.ajax({
    url: page,
    type: 'post',
		data : {},
    dataType: 'json',
    success: function(res){
    	xhr_ajax_laba = null;
    	
    	if(!res.status){
    		cLoader.close();
    		cAlert.open(res.message,'failed');
    		return false;
    	}

    	let c_laba = $('#c-laba').dxCircularGauge('instance');
      let gaugeElement = $(c_laba._$element[0]);
      let val = res.laba.laba_penc;
      gaugeElement.find('.dxg-title text').last().html(`${val}%`);
      c_laba.value(val);

    	$.each(res.laba.class,function(k,v){
    		$(k).html(v);
    	})

    	$.each(res.laba.chart,function(k,v){
    		window[k].data = v;
    		window[k].update();
    	})
    	cLoader.close();
    	loadPendBeban();
    }
  });
}

var xhr_pend_beban = null;
function loadPendBeban(){
	var cabang = $('#filter_cabang option:selected').val();
	if(!cabang){return false;}
	cLoader.open(lang.memuat_data + '...');
    if( xhr_pend_beban != null ) {
        xhr_pend_beban.abort();
        xhr_pend_beban = null;
    }

  var page = base_url + 'transaction/'+controller+'/data_pend_beban';
  page += '/'+ $('#filter_tahun').val();
  page += '/'+ $('#filter_cabang').val();
  page += '/'+ $('#filter_bulan').val();

	xhr_pend_beban = $.ajax({
    url: page,
    type: 'post',
		data : {},
    dataType: 'json',
    success: function(res){
    	xhr_pend_beban = null;
    	
    	if(!res.status){
    		cLoader.close();
    		cAlert.open(res.message,'failed');
    		return false;
    	}

    	$.each(res.data.class,function(k,v){
    		$(k).html(v);
    	})
    	$.each(res.data.chart,function(k,v){
    		window[k].data = v;
    		window[k].update();
    	})
    	cLoader.close();
    	loadOther();
    }
  });
}

var xhr_other = null;
function loadOther(){
	var cabang = $('#filter_cabang option:selected').val();
	if(!cabang){return false;}
	cLoader.open(lang.memuat_data + '...');
    if( xhr_other != null ) {
        xhr_other.abort();
        xhr_other = null;
    }

  var page = base_url + 'transaction/'+controller+'/data_other';
  page += '/'+ $('#filter_tahun').val();
  page += '/'+ $('#filter_cabang').val();
  page += '/'+ $('#filter_bulan').val();

	xhr_other = $.ajax({
    url: page,
    type: 'post',
		data : {},
    dataType: 'json',
    success: function(res){
    	xhr_other = null;
    	
    	if(!res.status){
    		cLoader.close();
    		cAlert.open(res.message,'failed');
    		return false;
    	}

    	$.each(res.data.class,function(k,v){
    		$(k).html(v);
    	})
    	$.each(res.data.chart,function(k,v){
    		window[k].data = v;
    		window[k].update();
    	})
    	loadAsset();
    	cLoader.close();
    }
  });
}

var xhr_ajax_asset = null;
function loadAsset(){
	var cabang = $('#filter_cabang option:selected').val();
	if(!cabang){return false;}
	cLoader.open(lang.memuat_data + '...');
    if( xhr_ajax_asset != null ) {
        xhr_ajax_asset.abort();
        xhr_ajax_asset = null;
    }

  var page = base_url + 'transaction/'+controller+'/data_asset';
  page += '/'+ $('#filter_tahun').val();
  page += '/'+ $('#filter_cabang').val();
  page += '/'+ $('#filter_bulan').val();

	xhr_ajax_asset = $.ajax({
    url: page,
    type: 'post',
		data : {},
    dataType: 'json',
    success: function(res){
    	xhr_ajax_asset = null;
    	
    	if(!res.status){
    		cLoader.close();
    		cAlert.open(res.message,'failed');
    		return false;
    	}

    	let c_aset = $('#c-aset').dxCircularGauge('instance');
      let gaugeElement = $(c_aset._$element[0]);
      let val = res.data.aset_penc;
      gaugeElement.find('.dxg-title text').last().html(`${val}%`);
      c_aset.value(val);

    	$.each(res.data.class,function(k,v){
    		$(k).html(v);
    	})
    	cLoader.close();
    	loadPendBebanO();
    }
  });
}

var xhr_pend_beban_o = null;
function loadPendBebanO(){
	var cabang = $('#filter_cabang option:selected').val();
	if(!cabang){return false;}
	cLoader.open(lang.memuat_data + '...');
    if( xhr_pend_beban_o != null ) {
        xhr_pend_beban_o.abort();
        xhr_pend_beban_o = null;
    }

  var page = base_url + 'transaction/'+controller+'/data_pend_beban_o';
  page += '/'+ $('#filter_tahun').val();
  page += '/'+ $('#filter_cabang').val();
  page += '/'+ $('#filter_bulan').val();

	xhr_pend_beban_o = $.ajax({
    url: page,
    type: 'post',
		data : {},
    dataType: 'json',
    success: function(res){
    	xhr_pend_beban_o = null;
    	
    	if(!res.status){
    		cLoader.close();
    		cAlert.open(res.message,'failed');
    		return false;
    	}

    	$.each(res.data.class,function(k,v){
    		$(k).html(v);
    	})
    	cLoader.close();
    }
  });
}
</script>