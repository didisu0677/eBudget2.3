<style type="text/css">
.w-keterangan{
	width: 310px !important;
	max-width: 310px !important;
	min-width: 310px !important;
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
			<select class="select2 infinity number-select" id="filter_anggaran">
				<?php foreach ($tahun as $tahun) { ?>
                <option value="<?php echo $tahun->kode_anggaran; ?>"<?php if($tahun->kode_anggaran == user('kode_anggaran')) echo ' selected'; ?>><?php echo $tahun->keterangan; ?></option>
                <?php } ?>
			</select> 		

    		<?php 
    			echo filter_cabang_admin($access_additional,$cabang,['kanpus' => 1]);
    			echo ' <button class="btn btn-success btn-save" href="javascript:;" > '.lang('simpan').' <span class="fa-save"></span></button>';
    			$arr = [
					// ['btn-save','Save Data','fa-save'],
				    ['btn-export','Export Data','fa-upload'],
				    // ['btn-import','Import Data','fa-download'],
				    // ['btn-template','Template Import','fa-reg-file-alt']
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
									<th colspan="15"><?= get_view_report() ?></th>
								</tr>
								<tr>
									<th class="text-center" width="30">No</th>
									<th class="text-center w-keterangan">Keterangan</th>
									<th class="text-center" style="min-width:50px;width: 50px;">Rate %</th>
									<?php
									for ($i = 1; $i <= 12; $i++) { 

										echo '<th class="text-center" style="min-width:100px;width:100px">'.month_lang($i).'</th>';
									}
									?>
									<th class="border-none bg-white" style="min-width:50px;width: 50px;"></th>
									<th class="border-none bg-white" style="min-width:50px;width: 50px;color: #fff !important;">Prosentase</th>							
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
									<th colspan="15"><?= get_view_report() ?></th>
								</tr>
								<tr>
									<th class="text-center" width="30">No</th>
									<th class="text-center w-keterangan">Keterangan</th>
									<th class="text-center" style="min-width:50px;width: 50px;">Rate %</th>
									<?php
									for ($i = 1; $i <= 12; $i++) { 
										echo '<th class="text-center" style="min-width:100px;width:100px">'.month_lang($i).'</th>';
									}?>
									<th class="border-none bg-white" style="min-width:50px;width: 50px;"></th>
									<th class="text-center" style="min-width:50px;width: 50px;">Prosentase</th>
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
   	<div class="main-container mt-3 d-rekening-content">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
					<div class="card-header"><?= lang('jumlah_rekening') ?></div>
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show" id="result3">
						<?php
						table_open('table table-bordered table-app table-hover');
							thead();
								?>
								<tr>
									<th rowspan="2" class="text-center align-middle" width="30">No</th>
									<th rowspan="2" class="w-keterangan text-center align-middle">Keterangan</th>
									<?php
									foreach ($detail_tahun as $d) {

										echo '<th class="text-center" style="min-width:100px;width:100px">'.substr(month_lang($d['bulan']),0,3) . ' ' . $d['tahun'].'</th>';
									}
										echo '<th rowspan="2" style="min-width:50px;width:50px" class="border-none bg-white"</th>';
										echo '<th rowspan="2" class="text-center align-middle" style="min-width:80px;width:80px">Jumlah Terakhir</th>';
										echo '<th rowspan="2" class="text-center align-middle" style="min-width:80px;width:80px">Tambahan</th>';
									?>
								</tr>
								<tr>
									<?php
									foreach ($detail_tahun as $d) {
										echo '<th class="text-center" style="min-width:100px;width:100px">'.$d['singkatan'].'</th>';
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
</div>

<script type="text/javascript" src="<?php echo base_url('assets/js/Chart.bundle.min.js'); ?>"></script>
<script type="text/javascript">
var autorun = 0;
var myChart;
var check_first_data = 1;
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
                text: 'TOTAL GIRO',
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
	autorun = 0;
	$('#result1 tbody').html('');	
    if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }
	cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/giro/data/';
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
        		cLoader.close();
        		$('#result1 tbody').html('');	
            	$('#result2 tbody').html('');
            	$('#result3 tbody').html('');
            	cAlert.open(res.message,'failed');
        		return false;
        	}
        	check_first_data = res.check_first_data;
            $('#result1 tbody').html(res.data);	
            $('#result2 tbody').html(res.data2);

            if(res.access_edit){
            	$('.btn-save').prop('disabled',false);
            	$('.btn-save').show();
            }else{
            	$('.btn-save').prop('disabled',true);
            	$('.btn-save').hide();
            }

            set_chart(res.chart);
            loadData3();	
            cLoader.close();
		}
    });
}

function set_chart(dt){
	myChart.data = dt;
	myChart.update();
	cLoader.close();
}

var xhr_ajax4 = null;
function loadData3(){
	$('#result3 tbody').html('');	
    if( xhr_ajax4 != null ) {
        xhr_ajax4.abort();
        xhr_ajax4 = null;
    }
	cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/giro/data3/';
    page += '/'+ $('#filter_anggaran').val();
    page += '/'+ $('#filter_cabang').val();
  	xhr_ajax = $.ajax({
        url: page,
        type: 'post',
		data : $('#form-filter').serialize(),
        dataType: 'json',
        success: function(res){
        	xhr_ajax4 = null;
        	if(!res.status){
        		cLoader.close();
        		$('#result1 tbody').html('');	
            	$('#result2 tbody').html('');
            	$('#result3 tbody').html('');
            	cAlert.open(res.message,'failed');
        		return false;
        	}
            $('#result3 tbody').html(res.data);
            if(res.autorun>0){
				autorun = 1;
			}
			if(autorun>0){
				var url = base_url+"transaction/formula_dpk/";
				create_formula(url,0);
			}
            cLoader.close();
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


// $(document).on('keyup','.edit-value',function(e){
// 	var wh 			= e.which;
// 	if((48 <= wh && wh <= 57) || (96 <= wh && wh <= 105) || wh == 8) {
// 		if($(this).text() == '') {
// 			$(this).text('');
// 		} else {
// 			var n = parseInt($(this).text().replace(/[^0-9\-]/g,''),10);
// 		    $(this).text(n.toLocaleString());
// 		    var selection = window.getSelection();
// 			var range = document.createRange();
// 			selection.removeAllRanges();
// 			range.selectNodeContents($(this)[0]);
// 			range.collapse(false);
// 			selection.addRange(range);
// 			$(this)[0].focus();
// 		}
// 	}
// });

// $(document).on('keypress','.edit-value',function(e){
// 	var wh 			= e.which;
// 	if (e.shiftKey) {
// 		if(wh == 0) return true;
// 	}
// 	if(e.metaKey || e.ctrlKey) {
// 		if(wh == 86 || wh == 118) {
// 			$(this)[0].onchange = function(){
// 				$(this)[0].innerHTML = $(this)[0].innerHTML.replace(/[^0-9\-]/g, '');
// 			}
// 		}
// 		return true;
// 	}
// 	if(wh == 0 || wh == 8 || wh == 45 || (48 <= wh && wh <= 57) || (96 <= wh && wh <= 105)) 
// 		return true;
// 	return false;
// });

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
	// if( i == 0) msg = lang.anda_yakin_menolak;
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

	var page = base_url + 'transaction/giro/save_perubahan';
    page += '/'+ $('#filter_anggaran').val();
    page += '/'+ $('#filter_cabang').val();

	 $.ajax({
	 	url : page,
	 	data 	: {
	 		'json' : jsonString,
	 		verifikasi : i,
	 		'kode_anggaran' : $('#filter_anggaran option:selected').val(),
	 	},
	 	type : 'post',
	 	success : function(response) {
	 		if(response.status == 'failed'){
                cAlert.open(response.message,'failed');
                return false;
            }
	 		if(check_first_data<=0){
	 			loadData();
	 		}else{
	 			cAlert.open(response.message,'success','loadData');
	 		}
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

    var dt_table2 = get_data_table('#result2');
    var arr_data2 = dt_table2['arr'];
    var arr_header2 = dt_table2['arr_header'];

    var dt_table3 = get_data_table('#result3');
    var arr_data3 = dt_table3['arr'];
    var arr_header3 = dt_table3['arr_header'];

    var post_data = {
        "header" 			: JSON.stringify(arr_header),
        "data"        		: JSON.stringify(arr_data),

        "header2" 			: JSON.stringify(arr_header2),
        "data2"        		: JSON.stringify(arr_data2),

        "header3" 			: JSON.stringify(arr_header3),
        "data3"        		: JSON.stringify(arr_data3),
        
        "kode_anggaran" 	: $('#filter_anggaran option:selected').val(),
        "kode_anggaran_txt" : $('#filter_anggaran option:selected').text(),
        "kode_cabang"   	: $('#filter_cabang option:selected').val(),
        "kode_cabang_txt"   : $('#filter_cabang option:selected').text(),
        "csrf_token"    	: x[0],
    }
    var url = base_url + 'transaction/giro/export';
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