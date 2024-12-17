<style type="text/css">
	.bg-1{
		background: #f2f9ff;
	}
	.custom-nav li{
		max-width: 100% !important;
	}
	.multiple .select2-container{
		width: 200px !important;
	}
	.wd-keterangan{
		min-width: 300px !important;
		width: 300px !important;
	}
	.wd-100{
		min-width: 100px !important;
		width: 100px !important;
	}
	.wd-150{
		min-width: 150px !important;
		width: 150px !important;
	}
</style>
<div class="content-header page-data" data-additional="<?= $access_additional ?>" data-type="divisi">
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
    			echo $option.' ';
    			echo ' <button class="btn btn-info btn-keterangan" data-title="'.lang('note').'" href="javascript:;" > '.lang('note').' <span class="fa-plus"></span></button>';
    			if($access_edit):
    				echo ' <button class="btn btn-success btn-save" href="javascript:;" > Save <span class="fa-save"></span></button>';
				endif;
				$arr = [
					// ['btn-save','Save Data','fa-save'],
				    ['btn-export','Export Data','fa-upload'],
				    // ['btn-import','Import Data','fa-download'],
				    // ['btn-template','Template Import','fa-reg-file-alt']
				];
				echo access_button('',$arr); 
			?>
    		</div>
			<div class="clearfix"></div>
	</div>
	<?php $this->load->view($sub_menu); ?>
</div>
<div class="content-body mt-6">
	<?php $this->load->view($sub_menu); ?>
	<div class="main-container mt-2">
		<div class="row">
			<div class="col-sm-12">
				<div class="card">
					<div class="card-header text-center"><?= $title ?> <br>(<?= get_view_report() ?>)</div>
	    			<div class="card-body">
	    				<div class="table-responsive tab-pane fade active show height-window" id="result1">
	    					<?php
							$thn_sebelumnya = user('tahun_anggaran') -1;
							table_open('table table-bordered table-app table-hover',false);
								thead('sticky-top');
									tr();
										tr();
										th(lang('no'),'','width="60" class="text-center align-middle"');
										th(lang('kebijakan_fungsi'),'text-center align-middle wd-keterangan');
										th(lang('uraian'),'text-center align-middle wd-keterangan');
										th(lang('tipe'),'text-center align-middle wd-150');
										th(lang('grup'),'text-center align-middle wd-150');
										th(lang('kode_inventaris'),'text-center align-middle wd-150');
										th(lang('coa'),'text-center align-middle wd-150');
										th(lang('kantor_cabang'),'text-center align-middle wd-150');
										foreach ($detail_tahun as $k2 => $v2) {
											$column = month_lang($v2->bulan).' '.$v2->tahun;
											$column .= '<br>('.$v2->singkatan.')';
											$column .= '<br> <span class="t-column">Biaya Pd Bulan</span>';
											th($column,'','class="text-center align-middle wd-100"');
										}
										th('&nbsp;','','width="30", rowspan="2" class="text-center align-middle"');
								tbody();
							table_close();
							?>
	    				</div>
	    			</div>
	    		</div>
	    	</div>
	    </div>
	</div>
</div>

<?php
modal_open('modal-form','','modal-lg w-90-per',' data-openCallback="formOpen"');
	modal_body('style-select2');
		form_open(base_url('transaction/asumsi_kebijakan_fungsi/save'),'post','form'); 
			col_init(2,4);
				input('hidden','id','id');
				input('text',lang('tahun'),'tahun_anggaran','',user('tahun_anggaran'),'disabled');
				echo cabang($cabang);
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
				<div id="result2" class="mb-3">	
				<div class="table-responsive height-window">
				    <table class="table table-bordered" id="result2">
						<thead class="sticky-top">
							<tr>
								<th class="text-center bg-grey w-150">'.lang('kebijakan_fungsi').'</th>
								<th class="text-center bg-grey wd-keterangan">'.lang('uraian').'</th>
								<th class="text-center bg-grey wd-100">'.lang('tipe').'</th>
								<th class="text-center bg-grey w-150">'.lang('grup').'</th>
								<th class="text-center bg-grey wd-150">'.lang('kode_inventaris').'</th>
								<th class="text-center bg-grey w-150">'.lang('coa').'</th>
								<th class="text-center bg-grey w-150">'.lang('kantor_cabang').'</th>
								<th class="bg-grey" width="10">
									<button type="button" class="btn btn-sm btn-icon-only btn-success btn-add-item"><i class="fa-plus"></i></button>
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
<!-- keterangan -->
<?php
modal_open('modal-keterangan','','w-90-per');
	modal_body();
		form_open(base_url('transaction/asumsi_kebijakan_fungsi/save_keterangan'),'post','form','data-callback="refreshKeterangan"');
		input('hidden','id','id');
		input('hidden','kode_cabang','kode_cabang');
		input('hidden','kode_anggaran','kode_anggaran');
		col_init(0,12);
		textarea('','keterangan','','','data-editor');
		echo '<div class="d-keterangan"></div>';
		form_button(lang('simpan'),lang('batal'));
		form_close();
	modal_footer();
modal_close();
?>
<script type="text/javascript" src="<?php echo base_url('assets/plugins/ckeditor/ckeditor.js'); ?>"></script>
<script type="text/javascript" src="<?php echo base_url('assets/js/maskMoney.js') ?>"></script>
<script type="text/javascript">
var dt_kebijakan_fungsi = '';
var dt_index = 0;
var response_data = [];
var controller = '<?= $controller ?>';
var dt_coa = '';
var dt_cabang = '';
var dt_type = '';
var dt_group = '';
var is_edit = false;
$(document).ready(function () {
	cabang_option();
	getData();
	resize_window();
});
$('#filter_tahun').change(function(){getData();});
$('#filter_cabang').change(function(){getData();});
function getData() {
	var cabang = $('#filter_cabang option:selected').val();
	if(!cabang){
		return false;
	}
	cLoader.open(lang.memuat_data + '...');
	var page = base_url + 'transaction/asumsi_kebijakan_fungsi/data';
	page 	+= '/'+$('#filter_anggaran').val();
	page 	+= '/'+$('#filter_cabang').val();

	$.ajax({
		url 	: page,
		data 	: {},
		type	: 'get',
		dataType: 'json',
		success	: function(response) {
			response_data = [];
			if(!response.status){
				cLoader.close();
				cAlert.open(response.message,'failed');
				$('.table-app tbody').html('');
				return false;
			}
			$('.table-app tbody').html(response.table);
			cLoader.close();
			dt_coa = response.coa;
			var item_act	= {};
			if($('.table-app tbody .btn-input').length > 0) {
				item_act['edit'] 		= {name : lang.ubah, icon : "edit"};
			}

			var kode_cabang;
			var cabang ;

			kode_cabang = $('#user_cabang').val();
			cabang = $('#filter_cabang').val();

			if(!response.access_edit) {	
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

			var cabang_txt = $('#filter_cabang option:selected').text();
			$(document).find('.d-subdiv').html(cabang_txt);
		}
	});
}
function cabang_option(){
	var page = base_url + 'transaction/'+controller+'/cabang_option';
	$.ajax({
		url 	: page,
		type	: 'get',
		dataType: 'json',
		success	: function(response) {
			dt_cabang = response.data;
			dt_type = response.type;
			dt_group= response.group;
		}
	});
}
function formOpen() {
	var c_cabang 		= $('#filter_cabang option:selected').val();
	var c_cabang_name 	= $('#filter_cabang option:selected').text();
	$('#kode_cabang').empty();
	$('#kode_cabang').append('<option value="'+c_cabang+'">'+c_cabang_name+'</option>').trigger('change');
	
	dt_index = 0;
	response_data = response_edit;
	$('#result2 tbody').html('');
	var cabang = $('#filter_cabang option:selected').val();
	$('#kode_cabang').val(cabang).trigger('change');
	get_kebijakan_fungsi();	
}
function get_kebijakan_fungsi(){
	if(proccess) {
		$.ajax({
			url : base_url + 'transaction/asumsi_kebijakan_fungsi/get_kebijakan_fungsi',
			data : {
				cabang_txt : $('#filter_cabang option:selected').text(),
			},
			type : 'POST',
			success	: function(response) {
				dt_kebijakan_fungsi = response;
				
				if(typeof response_data.detail != 'undefined') {
					is_edit = true;
					add_item();
					var list = response_data.data;
					$('.btn-add-item').hide();
					$('#id').val(response_data.detail.id);
					$.each(list, function(k,v){
						if(k != 0){
							add_item();
						}
						var f = $('#result2 tbody tr').last();
						f.find('.kebijakan_fungsi').val(v.id_kebijakan_fungsi).trigger('change');
						f.find('.uraian').val(v.uraian);
						f.find('.type').val(v.type).trigger('change');
						f.find('.group').val(v.grup).trigger('change');
						f.find('.kode_inventaris').val(v.kode_inventaris).trigger('change');
						f.find('.coa').val(v.coa).trigger('change');
						f.find('.type_cabang').val(v.type_cabang).trigger('change');
						f.find('.dt_id').val(v.id);
						if(v.produk == 1){
							f.find('.produk').prop('checked',true);
						}else{
							f.find('.produk').prop('checked',false);
						}
					})
				}else{
					is_edit = false;
					add_item();
					$('.btn-add-item').show();
				}
				$(".money").maskMoney({allowNegative: true, thousands:'.', decimal:',', precision: 0});
			}
		});
	}
}
$(document).on('click','.btn-add-item',function(){
	add_item();
});
$(document).on('click','.btn-remove',function(){
	$(this).closest('tr').remove();
});
function add_item(p1){
	dt_index += 1;
	var btn_remove = '';
	if(!is_edit){
		btn_remove = '<button type="button"class="btn btn-sm btn-icon-only btn-danger btn-remove"><i class="fa-times"></i></button>';
	}
	var konten = '<tr>';
		konten += '<td><input type="hidden" class="dt_id" name="dt_id[]"/><input type="hidden" name="dt_key[]" value="'+dt_index+'"/><select class="form-control pilihan kebijakan_fungsi" name="kebijakan_fungsi[]" data-validation="required">'+dt_kebijakan_fungsi+'</select></td>';
		konten += '<td><input type="text" autocomplete="off" class="form-control uraian" name="uraian[]" aria-label="" data-validation="required"/></td>';
		konten += '<td><select class="form-control pilihan type" name="type[]" data-validation="required">'+dt_type+'</select></td>';
		konten += '<td><div class="g-inv"><select class="form-control pilihan group" name="group[]" data-validation="required">'+dt_group+'</select></div></td>';
		konten += '<td><div class="g-inv"><input type="text" autocomplete="off" class="form-control kode_inventaris" name="kode_inventaris[]" aria-label="" data-validation="required"/></div></td>';
		konten += '<td><div class="g-biaya"><select class="form-control pilihan coa" name="coa[]" data-validation="required">'+dt_coa+'</select></div></td>';
		konten += '<td><div class="multiple"><select class="form-control pilihan type_cabang" name="type_cabang_'+dt_index+'[]"  multiple data-validation="required">'+dt_cabang+'</select></div></td>';
		
		konten += '<td>'+btn_remove+'</td>';
	konten += '</tr>';
	$('#result2 tbody').append(konten);
	var $t = $('#result2 .pilihan:last-child');
	$.each($t,function(k,o){
		var $o = $(o);
		$o.select2({
			dropdownParent : $o.parent(),
			placeholder : ''
		});
	});

	var f = $('#result2 tbody tr').last();
	check_type(f,f.find('.type'));
}
function check_type(tr,type){
	var val = type.find('option:selected').val();
	if(val == 1){
		tr.find('.g-inv').find('input,select').removeAttr('data-validation');
		tr.find('.g-inv').find('.is-invalid').removeClass('is-invalid');
		tr.find('.g-inv').find('.error').text('');
		tr.find('.g-biaya').find('select').attr('data-validation','required');
		tr.find('.g-inv').hide();
		tr.find('.g-biaya').show();
	}else if(val == 2){
		tr.find('.g-inv').find('input,select').attr('data-validation','required');
		tr.find('.g-biaya').find('select').removeAttr('data-validation');
		tr.find('.g-biaya').find('.is-invalid').removeClass('is-invalid');
		tr.find('.g-biaya').find('.error').text('');
		tr.find('.g-inv').show();
		tr.find('.g-biaya').hide();
		tr.find('.group').trigger('change');
	}
}
$(document).on('change','.type',function(){
	var tr = $(this).closest('tr');
	check_type(tr,$(this));
})

$(document).on('change','.group',function(){
	var tr = $(this).closest('tr');
	var td = tr.find('.kode_inventaris').closest('td');
	var val = $(this).val();
	console.log(val);
	td.find('.is-invalid').removeClass('is-invalid');
	td.find('.error').text('');
	if(!inArray(val,['E.4','E.5','E.7'])){
		tr.find('.kode_inventaris').removeAttr('data-validation');
		tr.find('.kode_inventaris').hide();
	}else{
		tr.find('.kode_inventaris').attr('data-validation','required');
		tr.find('.kode_inventaris').show();
	}
})

// keterangan
$(document).on('click','.btn-keterangan',function(){
	var title = $(this).attr('data-title');
	get_keterangan(title);
})
var xhr_keterangan = null;
function get_keterangan(title){
	var data_post = {
    	kode_cabang : $('#filter_cabang option:selected').val(),
    	kode_anggaran : $('#filter_anggaran option:selected').val(),
    }

    cLoader.open(lang.memuat_data + '...');
    var page = base_url + 'transaction/asumsi_kebijakan_fungsi/keterangan_view';
    xhr_keterangan = $.ajax({
        url: page,
        type: 'post',
		data : data_post,
        dataType: 'json',
        success: function(res){
        	xhr_keterangan = null;
        	if(!res.status){
        		cLoader.close();
        		cAlert.open(res.message,'failed');
        		return false;
        	}
        	$('#modal-keterangan').modal('show');
			$('#modal-keterangan .modal-title').text(title);

			$('#modal-keterangan #kode_cabang').val(data_post.kode_cabang);
			$('#modal-keterangan #kode_anggaran').val(data_post.kode_anggaran);
			$('#modal-keterangan #id').val(res.id);
			window.CKEDITOR.instances.keterangan.setData(res.keterangan);

			if(res.access_edit){
       			$('#modal-keterangan #keterangan').removeAttr('data-readonly');
       			$('#modal-keterangan #keterangan').closest('.form-group').show();
       			$('#modal-keterangan .d-keterangan').empty();
       			$('#modal-keterangan button[type="submit"]').show();
       		}else{
       			$('#modal-keterangan #keterangan').attr('data-readonly',"true");
       			if(res.keterangan.length<=0){
       				res.keterangan = lang.data_tidak_ditemukan;
       			}
       			$('#modal-keterangan .d-keterangan').html(res.keterangan);
       			$('#modal-keterangan #keterangan').closest('.form-group').hide();
       			$('#modal-keterangan button[type="submit"]').hide();
       		}
			cLoader.close();
        }
    });
}
function refreshKeterangan(){
	$('.btn-keterangan').click()
}

// edit value
$(document).on('focus','.edit-value',function(){
	$(this).parent().removeClass('edited');
	var val = $(this).text();
	var minus = val.includes("(");
	if(minus){
		val = val.replace('(','');
		val = val.replace(')','');
		$(this).text('-'+val);
	}
});
$(document).on('blur','.edit-value',function(){
	var tr = $(this).closest('tr');
	var angka = $(this).text();
	angka = moneyToNumber(angka);
	if(angka != $(this).attr('data-value')) {
		$(this).addClass('edited');
	}else{
		$(this).removeClass('edited');
	}
	if(tr.find('td.edited').length > 0) {
		tr.addClass('edited-row');
	} else {
		tr.removeClass('edited-row');
	}
	var val = $(this).text();
	var minus = val.includes("-");
	if(minus){
		val = val.replace('-','');
		$(this).text('('+val+')');
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
		split[1] = split[1].substr(0,decimal);
	}
	if(min_txt.length == 2){
      str_min_txt = "-";
    }
	rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
	// return prefix == undefined ? rupiah : (rupiah ? '' + rupiah : '');
	return str_min_txt+rupiah;
}

function calculate() {
	var total_budget = 0;

	$('#result tbody tr').each(function(){
		if($(this).find('.budget').length == 1) {
			var subtotal_budget = moneyToNumber($(this).find('.budget').val());
			total_budget += subtotal_budget;
		}


	});

	$('#total_budget').val(total_budget);
}

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
	var cabang = $('#filter_cabang').val();
	var anggaran = $('#filter_anggaran').val();	
	$.ajax({
		url : base_url + 'transaction/'+controller+'/save_perubahan',
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
			cAlert.open(response.message,'success','refreshData');
		}
	})
}
$(document).on('click','.btn-export',function(){
	var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));
    var cabang = $('#filter_cabang').val();

    var classnya = ['#result1'];
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
    var url = base_url + 'transaction/'+controller+'/export';
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

                if($(this).find('select').length>0){
                	val = $(this).find('select option:selected').text();
                }

                arrayOfThisRow.push(val); 
            });
            arr.push(arrayOfThisRow);
        }
    });
    return {'arr' : arr, 'arr_header' : arr_header};
}
</script>