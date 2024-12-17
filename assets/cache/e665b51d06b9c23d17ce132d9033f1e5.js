
var dt_skala_program = `<option value="1">Inisiatif Strategis</option><option value="2">Mandatory</option><option value="3">Quick Win</option><option value="4">Tugas Rutin</option>`;
var dt_pic = ``;
var dt_target = ``;
var dt_pelaksanaan = ``;
var controller = "pko_satu";
var response_data = [];
$(document).ready(function () {
	resize_window();
	// getPegawai();
	getFinansial();
	get_option('dt_pelaksanaan');
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
	$.ajax({
		url : base_url + 'api/pegawai_option',
		data : {},
		type : 'POST',
		success	: function(response) {
			dt_pic = response.data;
		}
	});
}
function getFinansial(){
	$.ajax({
		url : base_url + 'api/target_finansial_option',
		data : {},
		type : 'POST',
		success	: function(response) {
			dt_target = response.data;
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
		var list = response_data.data;
		dt_pic = response_data.pic_option;
		$.each(list, function(k,v){
			add_item();
			var f = $('#form_table tbody tr').last();
			f.find('.skala_program').val(v.id_skala_program).trigger('change');
			f.find('.dt_id').val(v.id);
			f.find('.keterangan').val(v.keterangan);
			f.find('.target').val(v.target).trigger('change');
			f.find('.pic').val(v.pic).trigger('change');
			f.find('.tujuan').val(v.tujuan);
			f.find('.output').val(v.output);
			f.find('.pelaksanaan').val(v.pelaksanaan).trigger('change');
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
	item += `<td>
		<input type="hidden" class="dt_key" value="`+dt_index+`" name="dt_key[]"/>
		<input type="hidden" class="dt_id" name="dt_id[]"/>
		<input type="text" class="form-control keterangan" name="keterangan[]" data-validation="required" />
		</td>`;
	item += '<td class="style-select2"><select class="form-control pilihan skala_program" name="skala_program[]" data-validation="required">'+dt_skala_program+'</select></td>';
	item += '<td class="style-select2"><select class="form-control pilihan target" name="target[]" data-validation="required">'+dt_target+'</select></td>';
	item += '<td><input type="text" class="form-control tujuan" name="tujuan[]" data-validation="required" /></td>';
	item += '<td><input type="text" class="form-control output" name="output[]" data-validation="required" /></td>';
	item += '<td><div class="multiple"><select class="form-control pilihan pic" name="pic'+dt_index+'[]" data-validation="required"  multiple>'+dt_pic+'</select></div></td>';
	item += '<td class="style-select2"><select class="form-control pilihan pelaksanaan" name="pelaksanaan[]" data-validation="required">'+dt_pelaksanaan+'</select></td>';
	item += '<td><button type="button"class="btn btn-sm btn-icon-only btn-danger btn-remove"><i class="fa-times"></i></button></td>';
	item += '</tr>';

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
				$('.table-app tbody').html('');
				cAlert.open(response.message,'failed');
				return false;
			}
			$('.table-app tbody').html(response.table);
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
