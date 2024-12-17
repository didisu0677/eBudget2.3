<style type="text/css">
.w-keterangan{
	max-width: 250px;
	width: 250px;
	min-width: 250px !important;
}
.w-bulan{
	min-width: 100px;
	width: 100px;
}
.w-no{

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
			?>
			<label class=""><?php echo lang('anggaran'); ?>  &nbsp</label>
			<select class="select2 infinity custom-select" id="filter_anggaran">
				<?php foreach ($tahun as $tahun) { ?>
                <option value="<?php echo $tahun->kode_anggaran; ?>"<?php if($tahun->kode_anggaran == user('kode_anggaran')) echo ' selected'; ?>><?php echo $tahun->keterangan; ?></option>
                <?php } ?>
			</select> 
    		<?php 
    			echo filter_cabang_admin($access_additional,$cabang,['kanpus' => 1]);
    			echo ' <button class="btn btn-success btn-save" href="javascript:;" > '.lang('simpan').' <span class="fa-save"></span></button>';
    			$arr = [
				    ['btn-export','Export Data','fa-upload'],
				];
				echo ' '.access_button('',$arr);
			?>
    		</div>
			<div class="clearfix"></div>
	</div>
	<?php $this->load->view($path.'sub_menu'); ?>
</div>
<div class="content-body mt-6">
<?php $this->load->view($path.'sub_menu'); ?>
	<div class="main-container mt-3">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
	    			<div class="card-body">
	    				<canvas id="chartbar" height="300"></canvas>
	    			</div>
	    		</div>
	    	</div>
	    </div>
   	</div>

   	<div class="main-container mt-3">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
					<div class="card-header"><?= $title ?></div>
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show" id="result1">
						<?php
						table_open('table table-bordered table-app table-hover');
							thead();
								?>
								<tr>
									<th width="30" class="text-center">No</th>
									<th class="text-center w-keterangan">Keterangan</th>
									<?php
									for ($i = 1; $i <= 12; $i++) { 
										echo '<th class="text-center w-bulan">'.month_lang($i).'</th>';
									}
									?>
									<th width="50" class="border-none bg-white"></th>
									<th class="border-none bg-white w-bulan" style="color: #fff !important;">PROSENTASE</th>
								</tr>
							<?php		
							tbody();
								tr();
									td('Tidak ada data','text-left','colspan="7"');
						table_close();
						?>		
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
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show" id="result2">
						<?php
						table_open('table table-bordered table-app table-hover');
							thead();
								?>
								<tr>
									<th width="30" class="text-center">No</th>
									<th class="text-center w-keterangan">Keterangan</th>
									<?php
									for ($i = 1; $i <= 12; $i++) { 

										echo '<th class="text-center w-bulan">'.month_lang($i).'</th>';
									}
									?>
									<th width="50" class="border-none bg-white"></th>
									<th class="border-none bg-white w-bulan" style="color: #fff !important;">PROSENTASE</th>
								</tr>
							<?php		
							tbody();
								tr();
									td('Tidak ada data','text-left','colspan="7"');
						table_close();
						?>					
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
					<div class="card-header"><?= lang('rincian_tabungan') ?></div>
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show" id="result3">
						<?php
						table_open('table table-bordered table-app table-hover');
							thead();
								?>
								<tr>
									<th rowspan="2" width="30" class="text-center align-middle">No</th>
									<th rowspan="2" class="w-keterangan text-center align-middle">Keterangan</th>
									<th rowspan="2" width="50" class="text-center align-middle">Rate %</th>
									<?php
									foreach ($detail_tahun as $d) {

										echo '<th class="text-center w-bulan">'.substr(month_lang($d['bulan']),0,3) . ' ' . $d['tahun'].'</th>';
									}

										echo '<th width="50" rowspan="2" class="border-none bg-white"></th>';
										echo '<th rowspan="2" class="text-center w-bulan align-middle">Prosentase</th>';
									?>
								</tr>
								<tr>
									<?php
									foreach ($detail_tahun as $d) {
										echo '<th class="text-center" style="min-width:80px;">'.$d['singkatan'].'</th>';
									}
									?>
								</tr>	
							<?php		
							tbody();
								tr();
									td('Tidak ada data','text-left','colspan="7"');
						table_close();
						?>					
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
					<div class="card-header"><?= lang('biaya_hadiah_tabungan'); ?></div>
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show" id="biaya_hadiah">
						<?php
						table_open('table table-bordered table-app table-hover');
							thead();
								?>
								<tr>
									<th rowspan="2" width="30" class="text-center align-middle">No</th>
									<th rowspan="2" class="text-center align-middle w-keterangan">Keterangan</th>
									<?php foreach ($detail_tahun as $d) {
										echo '<th class="text-center w-bulan">'.substr(month_lang($d['bulan']),0,3) . ' ' . $d['tahun'].'</th>';
									} ?>
									<th width="50" class="border-none bg-white"></th>
									<th class="border-none bg-white w-bulan" style="color: #fff !important;">PROSENTASE</th>
								</tr>
								<tr>
									<?php foreach ($detail_tahun as $d) {
										echo '<th class="text-center w-bulan">'.$d['singkatan'].'</th>';
									}?>
								</tr>	
							<?php		
							tbody();
								tr();
									td('Tidak ada data','text-left','colspan="'.(2+count($detail_tahun)).'"');
						table_close();
						?>					
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
					<div class="card-header"><?php echo 'Rincian Segmen Tabungan'; ?></div>
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show" id="res_segmen">
						<?php
						table_open('table table-bordered table-app table-hover');
							thead();
								?>
								<tr>
									<th rowspan="2" width="30" class="text-center align-middle">No</th>
									<th rowspan="2" class="w-keterangan text-center align-middle">Keterangan</th>
									<?php foreach ($detail_tahun as $d) {
										echo '<th class="text-center w-bulan">'.substr(month_lang($d['bulan']),0,3) . ' ' . $d['tahun'].'</th>';
									} ?>
									<th width="50" class="border-none bg-white" rowspan="2"></th>
									<th rowspan="2" class="text-center align-middle w-bulan">PROSENTASE</th>
								</tr>
								<tr>
									<?php foreach ($detail_tahun as $d) {
										echo '<th class="text-center w-bulan">'.$d['singkatan'].'</th>';
									}?>
								</tr>	
							<?php		
							tbody();
								tr();
									td('Tidak ada data','text-left','colspan="'.(2+count($detail_tahun)).'"');
						table_close();
						?>					
						</div>
	    			</div>
	    		</div>
	    	</div>
	    </div>
	</div>

	<div class="main-container d-rekening-content">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
					<div class="card-header"><?= lang('jumlah_rekening') ?></div>
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show" id="result4">
						<?php
						table_open('table table-bordered table-app table-hover');
							thead();
								?>
								<tr>
									<th rowspan="2" width="30" class="text-center align-middle">No</th>
									<th rowspan="2" class="w-keterangan text-center align-middle">Keterangan</th>
									<?php
									foreach ($detail_tahun as $d) {
										echo '<th class="text-center w-bulan">'.substr(month_lang($d['bulan']),0,3) . ' ' . $d['tahun'].'</th>';
									}

										echo '<th class="text-center border-none bg-white" rowspan="2"></th>';
										echo '<th class="text-center align-middle w-bulan" rowspan="2"">Jumlah Terakhir</th>';
										echo '<th rowspan="2" class="text-center align-middle w-bulan">Tambahan</th>';
									?>
								</tr>
								<tr>
									<?php
									foreach ($detail_tahun as $d) {
										echo '<th class="text-center w-bulan">'.$d['singkatan'].'</th>';
									}
									?>
								</tr>	
							<?php		
							tbody();
								tr();
									td('Tidak ada data','text-left','colspan="7"');
						table_close();
						?>					
						</div>
	    			</div>
	    		</div>
	    	</div>
	    </div>
	</div>

	<div class="main-container d-rekening-content">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
					<div class="card-header"><?= lang('jumlah_segmen_rekening') ?></div>
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show" id="jum_segmen">
						<?php
						table_open('table table-bordered table-app table-hover');
							thead();
								?>
								<tr>
									<th rowspan="2" width="30" class="text-center align-middle">No</th>
									<th rowspan="2" class="w-keterangan text-center align-middle">Keterangan</th>
									<?php
									foreach ($detail_tahun as $d) {
										echo '<th class="text-center w-bulan">'.substr(month_lang($d['bulan']),0,3) . ' ' . $d['tahun'].'</th>';
									}

									echo '<th class="text-center border-none bg-white" rowspan="2"></th>';
									echo '<th class="text-center align-middle w-bulan" rowspan="2">Jumlah Terakhir</th>';
									echo '<th rowspan="2" class="text-center align-middle w-bulan">Tambahan</th>';
									?>
								</tr>
								<tr>
									<?php
									foreach ($detail_tahun as $d) {
										echo '<th class="text-center w-bulan">'.$d['singkatan'].'</th>';
									}
									?>
								</tr>	
							<?php		
							tbody();
								tr();
									td('Tidak ada data','text-left','colspan="'.(5+count($detail_tahun)).'"');
						table_close();
						?>					
						</div>
	    			</div>
	    		</div>
	    	</div>
	    </div>
	</div>
</div>

<script type="text/javascript" src="<?php echo base_url('assets/js/Chart.bundle.min.js'); ?>"></script>

<script type="text/javascript">

var xhr_ajax = null;
var xhr_ajax2 = null;
var xhr_ajax3 = null;
var xhr_ajax4 = null;
var xhr_ajax5 = null;
var autorun = 0;

var myChart;
var serialize_color = [
    '#404E67',
    '#22C2DC',
    '#00897b',
    '#ff9f40',
    '#ffcd56',
    '#4bc0c0',
    '#9966ff',
    '#36a2eb',
    '#848484',
    '#e8b892',
    '#bcefa0',
    '#4dc9f6',
    '#a0e4ef',
    '#c9cbcf',
    '#00A5A8',
    '#10C888',
    '#7d3cff',
    '#f2d53c',
    '#c80e13',
    '#e1b382',
    '#c89666',
    '#2d545e',
    '#12343b',
    '#9bc400',
    '#8076a3',
    '#f9c5bd',
    '#7c677f'
];

$(document).ready(function(){
	initchart();
	var cabang = $('#filter_cabang').val();
	if(cabang){
		loadData();
	}
	

});	

$('#filter_anggaran').change(function(){
	loadData();
});

$('#filter_cabang').change(function(){
	loadData();
});
	
function initchart(){
	var ctxBar = document.getElementById('chartbar').getContext('2d');
	myChart = new Chart(ctxBar, {
		type: 'bar',
		options: {
        "hover": {
            "animationDuration": 0
        },
          "hover": {
            "animationDuration": 0
        },
        "animation": {
            "duration": 1,
            "onComplete": function () {
                var chartInstance = this.chart,
                ctx = chartInstance.ctx;

                ctx.font = Chart.helpers.fontString(8, Chart.defaults.global.defaultFontStyle, Chart.defaults.global.defaultFontFamily);
                ctx.textAlign = 'center';
                ctx.textBaseline = 'bottom';

                this.data.datasets.forEach(function (dataset, i) {
                    var meta = chartInstance.controller.getDatasetMeta(i);
                    meta.data.forEach(function (bar, index) {
                        var data = dataset.data[index];
                        // data = (parseFloat(data) / 1000);
                        // data = toFixedIfNecessary(data,1);
                        ctx.fillText(customFormat(data,0), bar._model.x, bar._model.y - 5);
                    });
                });
            }
        },
        legend: {
            "display": false
        },
        tooltips: {
            "enabled": false
        },

			title: {
                display: true,
                text: 'TABUNGAN',
                fontSize: 14,
                padding: 10
            },
			maintainAspectRatio: false,
			responsive: true,
		    scales: {
			  xAxes: [{
			  		gridLines: {
		                display:false
		            },
			      beginAtZero: true,
			      ticks: {
			         autoSkip: false
			      }
			  }],
	            yAxes: [{
	            		gridLines: {
			                display:false
			            },
	                    display: true,
	                    scaleLabel: {
	                        display: true,
	                        labelString: 'Jumlah'
	                    },
	                    ticks: {
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
		}
	});
};	

function loadData(){
	var cabang = $('#filter_cabang').val();
	if(!cabang){
		return '';
	}
	autorun = 0;
	$('#result1 tbody').html('');	
    if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
	cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/tabungan/data';
    page += '/'+ $('#filter_anggaran').val();
    page += '/'+ $('#filter_cabang').val();
  	xhr_ajax = $.ajax({
        url: page,
        type: 'post',
		data : $('#form-filter').serialize(),
        dataType: 'json',
        success: function(res){
        	xhr_ajax = null;
        	if(!res.status){
        		$('#result1 tbody').html('');
        		$('#result2 tbody').html('');
				$('#result3 tbody').html('');
        		$('#res_segmen tbody').html('');
            	$('#biaya_hadiah tbody').html('');
				$('#result4 tbody').html('');
            	$('#jum_segmen tbody').html('');
        		cLoader.close();
        		cAlert.open(res.message,'failed');
        		return false;
        	}
            $('#result1 tbody').html(res.data);	
            cLoader.close();
            loadData2();
		}
    });
}

function loadData2(){
	$('#result2 tbody').html('');	
    if( xhr_ajax2 != null ) {
        xhr_ajax2.abort();
        xhr_ajax2 = null;
    }
	cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/tabungan/data2/';
    page += '/'+ $('#filter_anggaran').val();
    page += '/'+ $('#filter_cabang').val();
  	xhr_ajax2 = $.ajax({
        url: page,
        type: 'post',
		data : $('#form-filter').serialize(),
        dataType: 'json',
        success: function(res){
        	xhr_ajax2 = null;
        	if(!res.status){
        		$('#result2 tbody').html('');
        		cLoader.close();
        		cAlert.open(res.message,'failed');
        		return false;
        	}
            $('#result2 tbody').html(res.data);		
            cLoader.close();	
            loadData3();
        }
    });
}

function loadData3(){
	$('#result3 tbody').html('');
	$('#res_segmen tbody').html('');
    if( xhr_ajax4 != null ) {
        xhr_ajax4.abort();
        xhr_ajax4 = null;
    }
	cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/tabungan/data3';
    page += '/'+ $('#filter_anggaran').val();
    page += '/'+ $('#filter_cabang').val();
  	xhr_ajax4 = $.ajax({
        url: page,
        type: 'post',
		data : $('#form-filter').serialize(),
        dataType: 'json',
        success: function(res){
        	xhr_ajax4 = null;
        	if(!res.status){
        		$('#result3 tbody').html('');
        		$('#res_segmen tbody').html('');
            	$('#biaya_hadiah tbody').html('');
        		cLoader.close();
        		cAlert.open(res.message,'failed');
        		return false;
        	}
            $('#result3 tbody').html(res.data);
            $('#res_segmen tbody').html(res.data_segment);
            $('#biaya_hadiah tbody').html(res.data_hadiah);
            if(res.autorun>0){
				autorun = 1;
			}

			if(res.access_edit){
            	$('.btn-save').prop('disabled',false);
            	$('.btn-save').show();
            }else{
            	$('.btn-save').prop('disabled',true);
            	$('.btn-save').hide();
            }

            cLoader.close();
            loadData4();
        }
    });
}

function loadData4(){
	$('#result4 tbody').html('');	
	$('#jum_segmen tbody').html('');	
    if( xhr_ajax5 != null ) {
        xhr_ajax5.abort();
        xhr_ajax5 = null;
    }
    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/tabungan/data4/';
    page += '/'+ $('#filter_anggaran').val();
    page += '/'+ $('#filter_cabang').val();
  	xhr_ajax5 = $.ajax({
        url: page,
        type: 'post',
		data : $('#form-filter').serialize(),
        dataType: 'json',
        success: function(res){
        	xhr_ajax5 = null;
        	if(!res.status){
        		$('#result4 tbody').html('');
            	$('#jum_segmen tbody').html('');
        		cLoader.close();
        		cAlert.open(res.message,'failed');
        		return false;
        	}
            $('#result4 tbody').html(res.data);
            $('#jum_segmen tbody').html(res.data_segment);
            cLoader.close();
            loadChart();
        }
    });
}

function loadChart(){
    if( xhr_ajax3 != null ) {
        xhr_ajax3.abort();
        xhr_ajax3 = null;
    }
    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/tabungan/data2/';
    page += '/'+ $('#filter_anggaran').val();
    page += '/'+ $('#filter_cabang').val();
  	xhr_ajax2 = $.ajax({
        url: page,
        type: 'post',
		data : $('#form-filter').serialize(),
        dataType: 'json',
        success: function(res){
        	xhr_ajax3 = null;
        	if(!res.status){
        		cLoader.close();
        		cAlert.open(res.message,'failed');
        		return false;
        	}
        	cLoader.close();

        	var data_tahun1 = [];
			var data_tahun2 = [];
			var data_tahun3 = [];
			var data_tahun4 = [];

			var label_chart 		= [];
			var no = 0;
			var no2 = 0;
			var a = '';
			var b = '' ;
			var c = '' ; 
			var label1 ='';
			var label2 ='';
			var label3 ='';
			var label4 ='';
			var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
				$.each(res.item_chart,function(x,y){	
					no++;
					if(no == 1) {
						label1 = y.keterangan;
						data_tahun1.push(y.P_01/1000000000);
						data_tahun1.push(y.P_02/1000000000);
						data_tahun1.push(y.P_03/1000000000);
						data_tahun1.push(y.P_04/1000000000);
						data_tahun1.push(y.P_05/1000000000);
						data_tahun1.push(y.P_06/1000000000);
						data_tahun1.push(y.P_07/1000000000);
						data_tahun1.push(y.P_08/1000000000);
						data_tahun1.push(y.P_09/1000000000);
						data_tahun1.push(y.P_10/1000000000);
						data_tahun1.push(y.P_11/1000000000);
						data_tahun1.push(y.P_12/1000000000);												
					}	

					if(no == 2) {
						label2 = y.keterangan;
						data_tahun2.push(y.P_01/1000000000);
						data_tahun2.push(y.P_02/1000000000);
						data_tahun2.push(y.P_03/1000000000);
						data_tahun2.push(y.P_04/1000000000);
						data_tahun2.push(y.P_05/1000000000);
						data_tahun2.push(y.P_06/1000000000);
						data_tahun2.push(y.P_07/1000000000);
						data_tahun2.push(y.P_08/1000000000);
						data_tahun2.push(y.P_09/1000000000);
						data_tahun2.push(y.P_10/1000000000);
						data_tahun2.push(y.P_11/1000000000);
						data_tahun2.push(y.P_12/1000000000);												
					}	

					if(no == 3) {
						label3 = y.keterangan;
						data_tahun3.push(y.P_01/1000000000);
						data_tahun3.push(y.P_02/1000000000);
						data_tahun3.push(y.P_03/1000000000);
						data_tahun3.push(y.P_04/1000000000);
						data_tahun3.push(y.P_05/1000000000);
						data_tahun3.push(y.P_06/1000000000);
						data_tahun3.push(y.P_07/1000000000);
						data_tahun3.push(y.P_08/1000000000);
						data_tahun3.push(y.P_09/1000000000);
						data_tahun3.push(y.P_10/1000000000);
						data_tahun3.push(y.P_11/1000000000);
						data_tahun3.push(y.P_12/1000000000);												
					}	

					if(no == 4) {
						label4 = y.keterangan;
						data_tahun4.push(y.P_01/1000000000);
						data_tahun4.push(y.P_02/1000000000);
						data_tahun4.push(y.P_03/1000000000);
						data_tahun4.push(y.P_04/1000000000);
						data_tahun4.push(y.P_05/1000000000);
						data_tahun4.push(y.P_06/1000000000);
						data_tahun4.push(y.P_07/1000000000);
						data_tahun4.push(y.P_08/1000000000);
						data_tahun4.push(y.P_09/1000000000);
						data_tahun4.push(y.P_10/1000000000);
						data_tahun4.push(y.P_11/1000000000);
						data_tahun4.push(y.P_12/1000000000);												
					}	
				});	

				for (var i = 1; i <= monthNames.length; i++) {
			    	if(i<10) {
			    		b = "P_" + "0"+i;
			    	}else{	
						b="P_"+i;
					}

					label_chart.push(res.arrBln[i]);
			    	
				}
				
				myChart.data = {
					datasets: [
			        {
			          label: label1,
			          type: "bar",
					  backgroundColor: "#0288d1",
					  data: data_tahun1,

			        }, 
			        {
			          label: label2,
			          type: "bar",
					  backgroundColor: "#ef6c00",
					  data: data_tahun2,
			        }, 
			       	
			       	{
			          label: label3,
			          type: "bar",
					  backgroundColor: "#00897b",
					  data: data_tahun3,
			        }, 
			        			        {
			          label: label4,
			          type: "bar",
					  backgroundColor: "#eeff41",
					  data: data_tahun4,
			        }, 
			      ],


			      labels: label_chart,
				};



				myChart.update();
            	
            if(autorun>0){
				var url = base_url+"transaction/formula_dpk/";
				create_formula(url,0);
			}
        }
    });
}

$(document).on('focus','.edit-value',function(){
	$(this).parent().removeClass('edited');
});
$(document).on('blur','.edit-value',function(){
	var tr = $(this).closest('tr');
	if($(this).text() != $(this).attr('data-value')) {
		$(this).addClass('edited');
	}
	if(tr.find('td.edited').length > 0) {
		tr.addClass('edited-row');
	} else {
		tr.removeClass('edited-row');
	}
});

$(document).on('keyup','.edit-value',function(e){
	var n = $(this).text();
	n = formatCurrency(n,'',2);
    $(this).text(n.toLocaleString());
    var selection = window.getSelection();
	var range = document.createRange();
	selection.removeAllRanges();
	range.selectNodeContents($(this)[0]);
	range.collapse(false);
	selection.addRange(range);
	$(this)[0].focus();
});

//$(document).on('keypress','.edit-value',function(e){
//	var wh 			= e.which;
//	if (e.shiftKey) {
//		if(wh == 0) return true;
//	}
//	if(e.metaKey || e.ctrlKey) {
//		if(wh == 86 || wh == 118) {
//			$(this)[0].onchange = function(){
//				$(this)[0].innerHTML = $(this)[0].innerHTML.replace(/[^0-9\-]/g, '');
//			}
//		}
//		return true;
//	}
//	if(wh == 0 || wh == 8 || wh == 45 || (48 <= wh && wh <= 57) || (96 <= wh && wh <= 105)) 
//		return true;
//	return false;
//});

$(document).on('click','.btn-save',function(){
	var i = 0;
	i += $(document).find('.edited2').length;
	$('.edited').each(function(){
		i++;
	});
	// if(i == 0) {
	// 	cAlert.open('tidak ada data yang di ubah');
	// } else {
	// 	var msg 	= lang.anda_yakin_menyetujui;
	// 	if( i == 0) msg = lang.anda_yakin_menolak;
	// 	cConfirm.open(msg,'save_perubahan');        
	// }

	var msg 	= lang.anda_yakin_menyetujui;
	cConfirm.open(msg,'save_perubahan');

});

function save_perubahan() {
	var data_edit = {};
	var i = 0;

	$('.edited').each(function(){
		var content = $(this).children('div');
		if(typeof data_edit[$(this).attr('data-id')] == 'undefined') {
			data_edit[$(this).attr('data-id')] = {};
		}
	//	data_edit[$(this).attr('data-id')][$(this).attr('data-name')] = $(this).text().replace(/[^0-9\-]/g,'');
		data_edit[$(this).attr('data-id')][$(this).attr('data-name')] = $(this).text();
		i++;
	});

	$('.edited2').each(function(){
		var content = $(this).children('div');
		if(typeof data_edit[$(this).attr('data-id')] == 'undefined') {
			data_edit[$(this).attr('data-id')] = {};
		}
		data_edit[$(this).attr('data-id')][$(this).attr('data-name')] = $(this).attr('data-value');
	//	console.log($(this).attr('data-name')+":"+$(this).attr('data-value'));
		i++;
	});
	
	var jsonString = JSON.stringify(data_edit);	
	var jsonString = JSON.stringify(data_edit);
	var page = base_url + 'transaction/tabungan/save_perubahan';
    page += '/'+ $('#filter_anggaran').val();
    page += '/'+ $('#filter_cabang').val();

	 $.ajax({
	 	url : page,
	 	data 	: {
	 		'json' : jsonString,
	 		verifikasi : i
	 	},
	 	type : 'post',
	 	success : function(response) {
	 		if(response.status == 'failed'){
                cAlert.open(response.message,'failed');
                return false;
            }
	 		cAlert.open(response.message,'success','loadData');
	 	}
	 })
}

function formatCurrency(angka, prefix,decimal){
	min_txt     = angka.split("-");
    str_min_txt = '';
	var number_string = angka.replace(/[^,\d]/g, '').toString(),
	split   		= number_string.split(','),
	sisa     		= split[0].length % 3,
	rupiah     		= split[0].substr(0, sisa),
	ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);

	// tambahkan titik jika yang di input sudah menjadi angka ribuan
	if(ribuan){
		separator = sisa ? '.' : '';
		rupiah += separator + ribuan.join('.');
	}
	if(split[1] != undefined && split[1].toString().length > decimal){
		console.log(split[1].toString().length);
		split[1] = split[1].substr(0,decimal);
	}
	if(min_txt.length == 2){
      str_min_txt = "-";
    }
	rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
	// return prefix == undefined ? rupiah : (rupiah ? '' + rupiah : '');
	return str_min_txt+rupiah;
}
var xhr_ajax_formula = null;
function create_formula(url,count){
	
	var page = '';
	var kode_anggaran = $('#filter_anggaran').val();
	var kode_cabang   = $('#filter_cabang').val();
	if(count == 0){
		page = url+'data/'+kode_anggaran+'/'+kode_cabang
	}else{
		page = url+'dataSewa/'+kode_anggaran+'/'+kode_cabang
	}
	cLoader.open(lang.memuat_data + '...');
	if( xhr_ajax_formula != null ) {
        xhr_ajax_formula.abort();
        xhr_ajax_formula = null;
    }

    xhr_ajax_formula = $.ajax({
		url 	: page,
		data 	: {
			special : "special",
		},
		type	: 'post',
		dataType: 'json',
		success	: function(response) {
			xhr_ajax_formula = null;
			cLoader.close();
		}
	});
}
$(document).on('click','.btn-export',function(){
	var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));
    var cabang = $('#filter_cabang').val();
    var dt_table = get_data_table('#result1');
    var arr_data = dt_table['arr'];
    var arr_header = dt_table['arr_header'];

    var classnya = ['#result1','#result2','#result3','#res_segmen','#biaya_hadiah','#result4','#jum_segmen'];
    var dt = {};
    $.each(classnya,function(k,v){
    	var dt_table = get_data_table(v);
    	var arr_data = dt_table['arr']
    	var arr_header = dt_table['arr_header'];
    	dt[v] = {
    		header : arr_header,
    		data : arr_data
    	}
    });
    var post_data = {
        "data"        		: JSON.stringify(dt),
        "kode_anggaran" 	: $('#filter_anggaran option:selected').val(),
        "kode_anggaran_txt" : $('#filter_anggaran option:selected').text(),
        "kode_cabang"   	: $('#filter_cabang option:selected').val(),
        "kode_cabang_txt"   : $('#filter_cabang option:selected').text(),
        "csrf_token"    	: x[0],
    }
    var url = base_url + 'transaction/tabungan/export';
    $.redirect(url,post_data,"","_blank");
});
function get_data_table(classnya){
    var arr = [];
    var arr_header = [];
    var no = 0;
    var index_cabang = 0;
    $(classnya+" table tr").each(function() {
        var arrayOfThisRowHeader = [];
        var tableDataHeader = $(this).find('th');
        if (tableDataHeader.length > 0) {
            tableDataHeader.each(function(k,v) {
                var val = $(this).text();
                arrayOfThisRowHeader.push($(this).text());
            });
            arr_header.push(arrayOfThisRowHeader);
        }

        var arrayOfThisRow = [];
        var tableData = $(this).find('td');
        if (tableData.length > 0) {
            tableData.each(function() {
                var val = $(this).text();
                if($(this).hasClass('sb-1')){
                    val = '     '+$(this).text();
                }else if($(this).hasClass('sb-2')){
                    val = '          '+$(this).text();
                }else if($(this).hasClass('sb-3')){
                    val = '               '+$(this).text();
                }else if($(this).hasClass('sb-4')){
                    val = '                    '+$(this).text();
                }else if($(this).hasClass('sb-5')){
                    val = '                         '+$(this).text();
                }else if($(this).hasClass('sb-6')){
                    val = '                              '+$(this).text();
                }
                arrayOfThisRow.push(val); 
            });
            arr.push(arrayOfThisRow);
        }
    });
    return {'arr' : arr, 'arr_header' : arr_header};
}
</script>