
var dt_target = ``;
$(document).ready(function () {
	getFinansial();
});
var xhr_ajax2 = null;
function getFinansial(){
	if( xhr_ajax2 != null ) {
        xhr_ajax2.abort();
        xhr_ajax2 = null;
    }
	xhr_ajax2 = $.ajax({
		url : base_url + 'api/target_finansial_option',
		data : {},
		type : 'POST',
		success	: function(response) {
			xhr_ajax2 = null;
			dt_target = response.data;
		}
	});
}

var dt_target = ``;
$(document).ready(function () {
	getFinansial();
});
var xhr_ajax2 = null;
function getFinansial(){
	if( xhr_ajax2 != null ) {
        xhr_ajax2.abort();
        xhr_ajax2 = null;
    }
	xhr_ajax2 = $.ajax({
		url : base_url + 'api/target_finansial_option',
		data : {},
		type : 'POST',
		success	: function(response) {
			xhr_ajax2 = null;
			dt_target = response.data;
		}
	});
}

var dt_contact_type = ``;
var dt_tipe_nasabah = ``;
var dt_tipe_dana = ``;
var dt_pic = ``;
var dt_pelaksanaan = ``;
var dt_target_lainnya = ``;
var controller = 'rko_pipeline_target_lainnya';
var response_data = [];
$(document).ready(function () {
	resize_window();
	get_option('dt_contact_type');
	get_option('dt_tipe_nasabah');
	get_option('dt_tipe_dana');
	get_option('dt_pelaksanaan');
	get_option('dt_target_lainnya');
	// getPegawai();
	getData();
});
$('#filter_tahun').change(function(){getData();});
$('#filter_cabang').change(function(){getData();});
function get_option(page){
	$.ajax({
		url 	: base_url+'transaction/'+controller+'/get_option',
		data 	: {
			page : page,
		},
		type	: 'post',
		dataType: 'json',
		success	: function(response) {
			window[page] = response.data;
		}
	});
}
function getPegawai(){
	cLoader.open();
	$.ajax({
		url : base_url + 'api/pegawai_option',
		data : {},
		type : 'POST',
		success	: function(response) {
			dt_pic = response.data;
		}
	});
}
function formOpen() {
	dt_index = 0;
	response_data = response_edit;
	$('#form_table tbody').html('');
	var cabang 		= $('#filter_cabang option:selected').val();
	var cabang_txt 	= $('#filter_cabang option:selected').text();
	$('#kode_cabang').html('<option value="'+cabang+'">'+cabang_txt+'</option>');
	$('#kode_cabang').val(cabang).trigger('change');
	
	if(typeof response_data.detail != 'undefined') {
		$('.btn-add-item').hide();
		$('#id').val(response_data.detail.id);
		dt_pic = response_data.pic_option;
		var list = response_data.data;
		$.each(list, function(k,v){
			add_item();
			var f = $('#form_table tbody tr').last();
			f.find('.id_target_lainnya').val(v.id_target_lainnya).trigger('change');
			f.find('.contact_type').val(v.id_rko_contact_type).trigger('change');
			f.find('.tipe_nasabah').val(v.id_rko_tipe_nasabah).trigger('change');
			f.find('.tipe_dana').val(v.id_rko_tipe_dana).trigger('change');
			f.find('.dt_id').val(v.id);
			f.find('.keterangan').val(v.keterangan);
			f.find('.cabang').val(v.nama_cabang);
			f.find('.pic').val(v.pic).trigger('change');
			f.find('.pelaksanaan').val(v.pelaksanaan).trigger('change');
			f.find('.biaya_sampai').val(v.biaya_sampai);
		});
	}else{
		add_item();
		$('.btn-add-item').show();
	}
}
$(document).on('click','.btn-add-item',function(){
	add_item();
});
$(document).on('click','.btn-remove',function(){
	$(this).closest('tr').remove();
});
var dt_index = 0;
function add_item(){
	dt_index += 1;
	var item = '<tr>';
	item += '<td class="style-select2"><select class="form-control pilihan id_target_lainnya" name="id_target_lainnya[]" data-validation="required">'+dt_target_lainnya+'</select></td>';
	item += `<td>
		<input type="hidden" class="dt_key" value="`+dt_index+`" name="dt_key[]"/>
		<input type="hidden" class="dt_id" name="dt_id[]"/>
		<input type="text" class="form-control keterangan" name="keterangan[]" data-validation="required" />
		</td>`;
	item += '<td class="style-select2"><select class="form-control pilihan contact_type" name="contact_type[]" data-validation="required">'+dt_contact_type+'</select></td>';
	item += '<td class="style-select2"><select class="form-control pilihan tipe_nasabah" name="tipe_nasabah[]" data-validation="required">'+dt_tipe_nasabah+'</select></td>';
	item += '<td class="style-select2"><select class="form-control pilihan tipe_dana" name="tipe_dana[]" data-validation="required">'+dt_tipe_dana+'</select></td>';
	item += '<td><input type="text" class="form-control cabang" name="cabang[]" data-validation="required" /></td>';
	item += '<td><div class="multiple"><select class="form-control pilihan pic" name="pic'+dt_index+'[]" data-validation="required"  multiple>'+dt_pic+'</select></div></td>';
	item += '<td class="style-select2"><select class="form-control pilihan pelaksanaan" name="pelaksanaan[]" data-validation="required">'+dt_pelaksanaan+'</select></td>';
	item += '<td><input type="text" class="form-control biaya_sampai money text-right" name="biaya_sampai[]" data-validation="required" /></td>';
	item += '<td><button type="button"class="btn btn-sm btn-icon-only btn-danger btn-remove"><i class="fa-times"></i></button></td>';
	item += '</tr>';

	money_init();
	$('#form_table').append(item);
	var $t = $('#form_table .pilihan:last-child');
	$.each($t,function(k,o){
		var $o = $(o);
		if($o.hasClass('pic')){
			$o.select2({
				dropdownParent : $o.parent(),
				placeholder: '',
				width: '100%',
				language: {
					searching: function() {
						return "Search...";
					}
				},
				ajax: {
					url: base_url+'api/pegawai_select2',
					dataType: 'json',
					type: 'POST',
					delay: 250,
					processResults: function (data) {
						return {
							results: data
						};
					},
					cache: true
				}
			})
		}else{
			$o.select2({
				dropdownParent : $o.parent(),
				placeholder : ''
			});
		}
	});
	money_init();
}
var xhr_ajax = null;
function getData() {
	var cabang = $('#filter_cabang').val();
    if(!cabang){
        return false;
    }
	
	var page = base_url + 'transaction/'+controller+'/data';
	page 	+= '/'+$('#filter_anggaran').val();
	page 	+= '/'+$('#filter_cabang').val();

	if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }

	cLoader.open(lang.memuat_data + '...');
	xhr_ajax = $.ajax({
		url 	: page,
		data 	: {},
		type	: 'get',
		dataType: 'json',
		success	: function(response) {
			xhr_ajax = null;
			response_data = [];
			if(!response.status){
				cLoader.close();
				$('.table tbody').html('');
				cAlert.open(response.message,'failed');
				return false;
			}
			$.each(response.data,function(k,v){
				$('#'+k).find('tbody').html(v);
			})
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

			// nett
			$.each(response.ls_nett,function(k,v){
				$(document).find(k).html(v);
			})
			
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
			cLoader.close();
		}
	});
}
$(document).on('click','.d-checkbox',function(){
	var ID = $(this).attr('id');
	var val = $(this).is(':checked');
	if(val){
		val = "1";
	}else{
		val = "0";
	}
	cLoader.open(lang.memuat_data + '...');
	var page = base_url + 'transaction/'+controller+'/save_checkbox';
	$.ajax({
		url 	: page,
		data 	: {ID : ID, val : val},
		type	: 'post',
		dataType: 'json',
		success	: function(response) {
			cLoader.close();
			if(!response.status){
				cAlert.open(res.message,'failed');
			}else{
				getData();
			}
		}
	});
});
$(document).on('click','.btn-export',function(){
	var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));
    var post_data = {
        "kode_anggaran" 	: $('#filter_anggaran option:selected').val(),
        "kode_anggaran_txt" : $('#filter_anggaran option:selected').text(),
        "kode_cabang"   	: $('#filter_cabang option:selected').val(),
        "kode_cabang_txt"   : $('#filter_cabang option:selected').text(),
        "csrf_token"    	: x[0],
        "export" 			: "export"
    }
    var url = base_url + 'transaction/'+controller+'/data';
    $.redirect(url,post_data,"","_blank");
});
