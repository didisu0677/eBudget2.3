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
	<div class="main-container mt-2">
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
	<div class="main-container mt-2">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
	    			<div class="card-header text-center"><?= $title ?></div>
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
										echo '<th class="border-none bg-white"></th>';
										echo '<th class="w-bulan border-none bg-white" style="color:#fff !important">Jumlah Terakhir</th>';
										echo '<th class="w-bulan border-none bg-white" style="color:#fff !important">Tambahan</th>';
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
										echo '<th class="border-none bg-white"></th>';
										echo '<th class="w-bulan border-none bg-white" style="color:#fff !important">Jumlah Terakhir</th>';
										echo '<th class="w-bulan border-none bg-white" style="color:#fff !important">Tambahan</th>';
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
	<div class="main-container mt-2">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
	    			<div class="card-header"><?php echo 'Kredit per Produk'; ?></div>
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show height-window" id="result3" data-height="100">
							<?php
							table_open('table table-bordered table-app table-hover');
								thead('sticky-top');
									?>
									<tr>
										<th width="30" class="text-center">No</th>
										<th class="text-center w-keterangan">Keterangan</th>
										<th width="50" class="text-center">Rate %</th>
										<?php

										foreach ($detail_tahun as $d) {

											echo '<th class="text-center w-bulan">'.substr(month_lang($d['bulan']),0,3) . ' ' . $d['tahun'].'</th>';
										}

											echo '<th class="border-none bg-white"></th>';

											echo '<th style="min-width:80px;">Netto</th>';
											echo '<th class="border-none bg-white"></th>';
											echo '<th class="w-bulan">'.substr(month_lang($tahun->bulan_terakhir_realisasi),0,3). ' ' . $tahun->tahun_terakhir_realisasi.'</th>';
											echo '<th class="w-bulan">'.substr(month_lang($tahun->bulan_terakhir_realisasi -1),0,3). ' ' . $tahun->tahun_terakhir_realisasi.'</th>';
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
	<div class="main-container mt-2 d-rekening-content">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
	    			<div class="card-header"><?php echo 'Jumlah Rekening'; ?></div>
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show" id="result4">
							<?php
							table_open('table table-bordered table-app table-hover');
								thead();
									?>
									<tr>
										<th width="30" class="text-center">No</th>
										<th class="text-center w-keterangan">Keterangan</th>
										<?php
										foreach ($detail_tahun as $d) {

											echo '<th class="text-center w-bulan">'.substr(month_lang($d['bulan']),0,3) . ' ' . $d['tahun'].'</th>';
										}
											echo '<th class="border-none bg-white"></th>';
											echo '<th class="w-bulan">Jumlah Terakhir</th>';
											echo '<th class="w-bulan">Tambahan</th>';
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
</div>



<script type="text/javascript" src="<?php echo base_url('assets/js/Chart.bundle.min.js'); ?>"></script>

<script type="text/javascript">

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
	resize_window();
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
                text: 'KREDIT',
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
var xhr_ajax = null;
function loadData(){
    if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
	cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/kredit/data/';
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
            	$('#result4 tbody').html('');
            	cLoader.close();
            	cAlert.open(res.message,'failed');
            	return false;
        	}
            $('#result1 tbody').html(res.data);	
            $('#result2 tbody').html(res.data2);

            myChart.data = res.chart;
			myChart.update();
	    	cLoader.close();

            cLoader.close();
            loadData3();
		}
    });
}

var xhr_ajax4= null;
function loadData3(){
    if( xhr_ajax4 != null ) {
        xhr_ajax4.abort();
        xhr_ajax4 = null;
    }
	cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/kredit/data3/';
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
        		$('#result1 tbody').html('');	
            	$('#result2 tbody').html('');
            	$('#result3 tbody').html('');
            	$('#result4 tbody').html('');
            	cLoader.close();
            	cAlert.open(res.message,'failed');
            	return false;
        	}
            $('#result3 tbody').html(res.data);
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

var xhr_ajax5=null;
function loadData4(){
    if( xhr_ajax5 != null ) {
        xhr_ajax5.abort();
        xhr_ajax5 = null;
    }
	cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/kredit/data4/';
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
        		$('#result1 tbody').html('');	
            	$('#result2 tbody').html('');
            	$('#result3 tbody').html('');
            	$('#result4 tbody').html('');
            	cLoader.close();
            	cAlert.open(res.message,'failed');
            	return false;
        	}
            $('#result4 tbody').html(res.data);
            cLoader.close();
            if(res.autorun>0){
				var url = base_url+"transaction/formula_kredit/";
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

//$(document).on('keyup','.edit-value',function(e){
//	var wh 			= e.which;
//	if((48 <= wh && wh <= 57) || (96 <= wh && wh <= 105) || wh == 8) {
//		if($(this).text() == '') {
//			$(this).text('');
//		} else {
//			var n = parseInt($(this).text().replace(/[^0-9\-]/g,''),10);
//		    $(this).text(n.toLocaleString());
//		    var selection = window.getSelection();
//			var range = document.createRange();
//			selection.removeAllRanges();
//			range.selectNodeContents($(this)[0]);
//			range.collapse(false);
//			selection.addRange(range);
//			$(this)[0].focus();
//		}
//	}
//});

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
		
		data_edit[$(this).attr('data-id')][$(this).attr('data-name')] = $(this).text();
	//	data_edit[$(this).attr('data-id')][$(this).attr('data-name')] = $(this).text().replace(/[^0-9\-]/g,'');
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
	var cabang = $('#filter_cabang').val();
	var anggaran = $('#filter_anggaran').val();	
	 $.ajax({
	 	url : base_url + 'transaction/kredit/save_perubahan',
	 	data 	: {
	 		'json' : jsonString,
	 		kode_cabang : cabang,
			kode_anggaran : anggaran,
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

    var classnya = ['#result1','#result2','#result3','#result4'];
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
    var url = base_url + 'transaction/kredit/export';
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