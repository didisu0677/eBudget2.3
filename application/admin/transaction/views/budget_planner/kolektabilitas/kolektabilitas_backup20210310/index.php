<div class="content-header">
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

			<label class=""><?php echo lang('cabang'); ?>  &nbsp</label>
			<select class="select2 custom-select" id="filter_cabang">

                <?php foreach($cabang as $b){ ?>

                <option value="<?php echo $b['kode_cabang']; ?>" <?php if($b['kode_cabang'] == user('kode_cabang')) echo ' selected'; ?>><?php echo $b['nama_cabang']; ?></option>

                <?php } ?>

			</select>   	
    		<?php
    			echo '<button class="btn btn-success btn-save" href="javascript:;" > Save <span class="fa-save"></span></button>';
				$arr = [];
				echo access_button('',$arr); 
			?>
    		</div>
			<div class="clearfix"></div>
	</div>
	<?php $this->load->view($path.'sub_menu'); ?>
</div>
<div class="content-body">
<?php $this->load->view($path.'sub_menu'); ?>
	<div class="main-container">
		<div class="row">
			<div class="col-sm-4">
				<div class="card">
		    		<div class="card-header"><?php echo lang('krd_produktif'); ?></div>
		    		<div class="card-body">
		    			<div class="table-responsive tab-pane fade active show">
						<?php
							table_open('table table-striped table-bordered table-app table-hover tbl-produktif');
								thead();
									tr();
									th(lang('no'),'','width="30" class="text-center align-middle"');
									th(lang('nama_akun'),'','class="text-center align-middle"');
									th(lang('kode_akun'),'','width="100" class="text-center align-middle"');
									th('&nbsp;','','width="30" class="text-center align-middle"');
								tbody();
							table_close();
							?>					
						</div>
		    		</div>
				</div>

				<div class="card mt-3">
		    		<div class="card-header"><?php echo lang('krd_konsumtif'); ?></div>
		    		<div class="card-body">
		    			<div class="table-responsive tab-pane fade active show tbl-konsumtif">
						<?php
							table_open('table table-striped table-bordered table-app table-hover');
								thead();
									tr();
									th(lang('no'),'','width="30" class="text-center align-middle"');
									th(lang('nama_akun'),'','class="text-center align-middle"');
									th(lang('kode_akun'),'','width="100" class="text-center align-middle"');
									th('&nbsp;','','width="30" class="text-center align-middle"');
								tbody();
							table_close();
							?>					
						</div>
		    		</div>
				</div>
			</div>
			<div class="col-sm-8">
				<div class="card">
		    		<div class="card-header"><?php echo lang('npl_krd_produktif'); ?></div>
		    		<div class="card-body">
		    			<canvas id="line_produktif" height="300"></canvas>
		    		</div>
				</div>

				<div class="card mt-3">
		    		<div class="card-header"><?php echo lang('npl_krd_konsumtif'); ?></div>
		    		<div class="card-body">
		    			<canvas id="line_konsumtif" height="300"></canvas>
		    		</div>
				</div>

				<div class="card mt-3">
		    		<div class="card-header"><?php echo lang('npl_total_kredit'); ?></div>
		    		<div class="card-body">
		    			<canvas id="line_total_kredit" height="300"></canvas>
		    		</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-12 d-input-npl">
				<div class="card mt-3">
					<div class="card-header">Non Performing Loan (NPL)</div>
					<div class="card-body">
						<div class="table-responsive tab-pane fade active show">
							<?php
							table_open('table table-striped table-bordered table-app table-hover tbl-input-npl');
								thead();
									tr();
									th('Non Performing Loan (NPL)','','class="text-center align-middle"');
									foreach ($detail_tahun as $k => $v) {
										$column = month_lang($v->bulan).' '.$v->tahun;
										$column .= '('.$v->singkatan.')';
										th($column,'','class="text-center align-middle"');
									}
								tbody();
								tfoot();
							table_close();
							?>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm-12 d-total-kredit"></div>
			<div class="col-sm-12 d-detail"></div>
			<div class="col-sm-12 d-produktif-sum"></div>
		</div>
	</div>
</div>

<?php
modal_open('modal-form','','modal-lg',' data-openCallback="formOpen"');
	modal_body();
		form_open(base_url('transaction/kolektibilitas/save'),'post','form'); 
			col_init(2,4);
				input('hidden','id','id');
				input('text',lang('tahun'),'tahun_anggaran','',user('tahun_anggaran'),'disabled');
				if($access_additional):
					echo cabang($cabang);
				else:
					echo cabang($cabang_input);
				endif;
			col_init(2,9);
			form_button(lang('simpan'),lang('batal'));
		form_close();
	modal_footer();
modal_close();

function cabang($cabang_input){
	$option = '';
	foreach($cabang_input as $b){
	if($b['kode_cabang'] == user('kode_cabang'))  $selected = ' selected'; else $selected = '';
	$option .= '<option value="'.$b['kode_cabang'].'"'.$selected.'>'.$b['nama_cabang'].'</option>';
	$item = '<div class="form-group row">
		<label class="col-form-label col-md-2">'.lang('cabang').' &nbsp</label>
		<div class="col-md-4 col-9 mb-1 mb-md-0">	
			<select class="select2 infinity custom-select" id="kode_cabang" name="kode_cabang">'.$option.'</select>   
		</div>
	</div>';
	$item .= '<div class="card mb-2">
				<div class="mb-3">	
				<div class="table-responsive">
				    <table class="table table-bordered" id="table_produktif">
						<thead>
							<tr>
								<th class="text-center">'.lang('akun_coa').' ( '.lang('krd_produktif').' )'.'</th>
								<th width="10">
									<button type="button" class="btn btn-sm btn-icon-only btn-success btn-add-item"><i class="fa-plus"></i></button>
								</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
				<div class="table-responsive">
				    <table class="table table-bordered mt-3" id="table_konsumtif">
						<thead>
							<tr>
								<th class="text-center">'.lang('akun_coa').' ( '.lang('krd_konsumtif').' )'.'</th>
								<th width="10">
									<button type="button" class="btn btn-sm btn-icon-only btn-success btn-add-item-konsumtif"><i class="fa-plus"></i></button>
								</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
				</div>
			</div>';
	}
	return $item;
}
?>
<script type="text/javascript" src="<?php echo base_url('assets/js/Chart.bundle.min.js'); ?>"></script>
<script type="text/javascript">
var dt_coa_produktif 		= [];
var dt_coa_konsumtif 		= [];
var url_data 	= base_url + 'transaction/kolektibilitas/custom_page';
var dt_index = 0;
var response_data = [];
$(document).ready(function () {
	initchart();
	getData();
});
$('#filter_tahun').change(function(){getData();});
$('#filter_cabang').change(function(){getData();});
function seturl(){
	var url = url_data+'/'+$('#filter_anggaran').val();
	url 	+= '/'+$('#filter_cabang').val();
	return url;
}
function formOpen() {
	dt_index = 0;
	response_data = response_edit;
	$('#table_produktif tbody').html('');
	$('#table_konsumtif tbody').html('');
	var kode_cabang = $('#filter_cabang option:selected').val();
	$('#kode_cabang').val(kode_cabang).trigger('change');
	if(typeof response_data.detail != 'undefined') {
		$('.btn-add-item, .btn-add-item-konsumtif').hide();
		$('#id').val(response_data.detail.id);
		if(response_data.detail.tipe == 1){
			$('#table_produktif').show();
			$('#table_konsumtif').hide();
		}else{
			$('#table_produktif').hide();
			$('#table_konsumtif').show();
		}
		$.each(response_data.data, function(x,v){
			if(v.tipe == 1){
				add_item();
				var f = $('#table_produktif tbody tr').last();
				f.find('.coa').val(v.coa_produk_kredit).trigger('change');
				f.find('.dt_id').val(v.id);
			}else{
				add_item_konsumtif();
				var f = $('#table_konsumtif tbody tr').last();
				f.find('.coa_konsumtif').val(v.coa_produk_kredit).trigger('change');
				f.find('.dt_id_konsumtif').val(v.id);
			}
		})
	}else{
		$('.btn-add-item, .btn-add-item-konsumtif').show();
		$('#table_produktif').show();
		$('#table_konsumtif').show();
		// add_item();
		// add_item_konsumtif();
	}
}
$(document).on('click','.btn-add-item',function(){
	add_item();
});
$(document).on('click','.btn-add-item-konsumtif',function(){
	add_item_konsumtif();
});
$(document).on('click','.btn-delete-item',function(){
	$(this).closest('tr').remove();
});
function add_item(){
	item = `<tr>`;
	item += `<td class="style-select2"><input class="dt_id" type="hidden" name="dt_id[]" /><select style="width:100%" class="form-control pilihan coa" name="coa[]" data-validation="required">`+dt_coa_produktif+`</select></td>`;
	item += '<td><button type="button" class="btn btn-sm btn-icon-only btn-danger btn-delete-item"><i class="fa-times"></i></button></td>';
	item += '</tr>';
	$('#table_produktif tbody').append(item);
	var $t = $('#table_produktif .pilihan').last();
	$.each($t,function(k,o){
		var $o = $(o);
		$o.select2({
			dropdownParent : $o.parent(),
			placeholder : ''
		});
	});
}
function add_item_konsumtif(){
	item = `<tr>`;
	item += `<td class="style-select2"><input class="dt_id_konsumtif" type="hidden" name="dt_id_konsumtif[]" /><select style="width:100%" class="form-control pilihan coa_konsumtif" name="coa_konsumtif[]" data-validation="required">`+dt_coa_konsumtif+`</select></td>`;
	item += '<td><button type="button" class="btn btn-sm btn-icon-only btn-danger btn-delete-item"><i class="fa-times"></i></button></td>';
	item += '</tr>';
	$('#table_konsumtif tbody').append(item);
	var $t = $('#table_konsumtif .pilihan').last();
	$.each($t,function(k,o){
		var $o = $(o);
		$o.select2({
			dropdownParent : $o.parent(),
			placeholder : ''
		});
	});
}
function getData(){
	var url = seturl();
	url_input_npl = url.replace("custom_page", "input_npl");
	getGeneralData(url_input_npl,'.tbl-input-npl','table');
}
function getDetail(){
	cLoader.open(lang.memuat_data + '...');
	var page = base_url + 'transaction/kolektibilitas/data';
	page 	+= '/'+$('#filter_anggaran').val();
	page 	+= '/'+$('#filter_cabang').val();

	$.ajax({
		url 	: page,
		data 	: {},
		type	: 'get',
		dataType: 'json',
		success	: function(response) {
			response_data = [];
			$('.tbl-produktif tbody').html(response.produktif);
			$('.tbl-konsumtif tbody').html(response.konsumtif);
			$('.d-detail').html(response.detail);
			$('.d-total-kredit').html(response.total_kredit);
			$('.d-produktif-sum').html(response.produktif_sum);
			dt_coa_produktif = response.opt_produktif;
			dt_coa_konsumtif = response.opt_konsumtif;
			set_table_npl(response.table_npl);
			set_line_produktif(response.chart.tipe_1,line_produktif,'NPL KRD PRODUKTIF');
			set_line_produktif(response.chart.tipe_2,line_konsumtif,'NPL KRD KONSUMTIF');
			set_line_produktif(response.chart.npl,line_total_kredit,'NPL TOTAL KREDIT');
			cLoader.close();
			cek_autocode();
			fixedTable();
			var item_act	= {};
			if($('.table-app tbody .btn-input').length > 0) {
				item_act['edit'] 		= {name : lang.ubah, icon : "edit"};
			}

			var kode_cabang;
			var cabang ;

			kode_cabang = $('#user_cabang').val();
			cabang = $('#filter_cabang').val();

			if(!response.edit) {	
				$(".btn-add").prop("disabled", true);
				$(".btn-input").prop("disabled", true);
				$(".btn-save").prop("disabled", true);	
			}else{
				$(".btn-add").prop("disabled", false);
				$(".btn-input").prop("disabled", false);
				$(".btn-save").prop("disabled", false);	
			}
			
			var act_count = 0;
			for (var c in item_act) {
				act_count = act_count + 1;
			}
			if(act_count > 0) {
				$.contextMenu({
			        selector: '.table-app tbody tr', 
			        callback: function(key, options) {
			        	if($(this).find('[data-key="'+key+'"]').length > 0) {
				        	if(typeof $(this).find('[data-key="'+key+'"]').attr('href') != 'undefined') {
				        		window.location = $(this).find('[data-key="'+key+'"]').attr('href');
				        	} else {
					        	$(this).find('[data-key="'+key+'"]').trigger('click');
					        }
					    } 
			        },
			        items: item_act
			    });
			}
		}
	});
}
function getGeneralData(url,classnya,tipe){
	cLoader.open(lang.memuat_data + '...');
	$.ajax({
		url 	: url,
		data 	: {},
		type	: 'get',
		dataType: 'json',
		success	: function(response) {
			response_data = [];
			if(tipe == 'table'){
				$(classnya+' tbody').html(response.table);
			}else{
				$(classnya).html(response.table);
			}
			getDetail();
			cLoader.close();
			cek_autocode();
			fixedTable();
			var item_act	= {};
			if($('.table-app tbody .btn-input').length > 0) {
				item_act['edit'] 		= {name : lang.ubah, icon : "edit"};
			}

			var kode_cabang;
			var cabang ;

			kode_cabang = $('#user_cabang').val();
			cabang = $('#filter_cabang').val();

			if(kode_cabang != cabang) {	
				$(".btn-add").prop("disabled", true);
				$(".btn-input").prop("disabled", true);
				$(".btn-save").prop("disabled", true);	
			}else{
				$(".btn-add").prop("disabled", false);
				$(".btn-input").prop("disabled", false);
				$(".btn-save").prop("disabled", false);	
			}
			
			var act_count = 0;
			for (var c in item_act) {
				act_count = act_count + 1;
			}
			if(act_count > 0) {
				$.contextMenu({
			        selector: '.table-app tbody tr', 
			        callback: function(key, options) {
			        	if($(this).find('[data-key="'+key+'"]').length > 0) {
				        	if(typeof $(this).find('[data-key="'+key+'"]').attr('href') != 'undefined') {
				        		window.location = $(this).find('[data-key="'+key+'"]').attr('href');
				        	} else {
					        	$(this).find('[data-key="'+key+'"]').trigger('click');
					        }
					    } 
			        },
			        items: item_act
			    });
			}
		}
	});
}
$(document).on('dblclick','.table-app tbody td .badge',function(){
	if($(this).closest('tr').find('.btn-input').length == 1) {
		var badge_status 	= '0';
		var data_id 		= $(this).closest('tr').find('.btn-input').attr('data-id');
		if( $(this).hasClass('badge-danger') ) {
			badge_status = '1';
		}
		active_inactive(data_id,badge_status);
	}
});


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
	$('.edited').each(function(){
		i++;
	});
	if(i == 0) {
		cAlert.open('tidak ada data yang di ubah');
	} else {
		var msg 	= lang.anda_yakin_menyetujui;
		if( i == 0) msg = lang.anda_yakin_menolak;
		cConfirm.open(msg,'save_perubahan');        
	}

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
		i++;
	});
	
	var jsonString = JSON.stringify(data_edit);		
	console.log(jsonString);
	$.ajax({
		url : base_url + 'transaction/kolektibilitas/save_perubahan',
		data 	: {
			'json' : jsonString,
			verifikasi : i
		},
		type : 'post',
		success : function(response) {
			cAlert.open(response,'success','refreshData');
		}
	})
}

var line_produktif,line_konsumtif,line_total_kredit;
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
var option_chart_line = {
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
                        data = toFixedIfNecessary(data,2);                          
                        ctx.fillText(data, bar._model.x, bar._model.y - 5);
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
                text: '',
                fontSize: 14,
                padding: 10
            },
			maintainAspectRatio: false,
			responsive: true,
		    scales: {
			  xAxes: [{
			      beginAtZero: true,
			      ticks: {
			         autoSkip: false
			      }
			  }],
	            yAxes: [{
	                    display: true,
	                    scaleLabel: {
	                        display: true,
	                        labelString: 'Jumlah'
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
function initchart(){
	var ctx = document.getElementById('line_produktif').getContext('2d');
	line_produktif = new Chart(ctx, {
		type: 'line',
		options: option_chart_line,
	});

	var ctx = document.getElementById('line_konsumtif').getContext('2d');
	line_konsumtif = new Chart(ctx, {
		type: 'line',
		options: option_chart_line,
	});

	var ctx = document.getElementById('line_total_kredit').getContext('2d');
	line_total_kredit = new Chart(ctx, {
		type: 'line',
		options: option_chart_line,
	});
};
function set_line_produktif(data,chart,title){
	labels              = [];
    value               = [];
    datasets            = [];

    $.each(data,function(k,v){
    	labels.push(k);
    	value.push(v);
    });

    d = {
        label           : title,
        data            : value,
        backgroundColor : serialize_color[1],
        borderColor     : serialize_color[1],
        borderWidth     : 1,
    };
    datasets.push(d);
    chart.data = {
		datasets: datasets,
      	labels: labels,
	};
	chart.update();
}
function set_table_npl(data){
	var item = '<tr>';
	item += '<th>NPL Total Kredit</th>';
	$.each(data,function(k,v){
		item += '<th class="text-right">'+toFixedIfNecessary(v,2)+'</th>';
	});
	item += '</tr>';
	$('.tbl-input-npl tfoot').html(item);
}
function formatCurrency(angka, prefix,decimal){
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

	rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
	return prefix == undefined ? rupiah : (rupiah ? '' + rupiah : '');
}
</script>