
var xhr_ajax 	= null;
var dt_coa 		= [];
$(document).ready(function(){
	get_coa();
})
function formOpen(){
	dt_index = 0;
	$('#table_coa tbody').html('');
	response_data = response_edit;
	var length = jQuery.isEmptyObject(response_data);
	if(!length){
		add_item();
		var f = $('#table_coa tbody tr').last();
		f.find('.coa').val(response_data.coa).trigger('change');
		f.find('.dt_id').val(response_data.id);
		if(response_data.is_default == 1){
			f.find('.is_default').prop('checked',true);
		}else{
			f.find('.is_default').prop('checked',false);
		}
		$('.btn-add-item, .btn-delete-item').hide();
	}else{
		add_item();
		$('.btn-add-item, .btn-delete-item').show();
	}
}
function get_coa(){
	var url = base_url+"api/coa_option";
	cLoader.open(lang.memuat_data + '...');
	xhr_ajax = $.ajax({
		url 	: url,
		type	: 'get',
		dataType: 'json',
		success	: function(response) {
			xhr_ajax = null;
			dt_coa = response.data;
			cLoader.close();
		}
	});
}
$(document).on('click','.btn-add-item',function(){
	add_item();
});
$(document).on('click','.btn-delete-item',function(){
	$(this).closest('tr').remove();
});
var dt_index = 0;
function add_item(){
	dt_index += 1;
	item = `<tr>`;
	item += `<td class="style-select2"><input class="dt_id" type="hidden" name="dt_id[]" /><input type="hidden" class="dt_key" value="`+dt_index+`" name="dt_key[]"/><select style="width:100%" class="form-control pilihan coa" name="coa[]" data-validation="required">`+dt_coa+`</select></td>`;
	item += '<td class="text-center"><div class="custom-checkbox custom-control custom-control-inline"><input class="custom-control-input chk-child is_default" id="is_default'+dt_index+'" type="checkbox"name="is_default'+dt_index+'[]" value="1"> <label class="custom-control-label" for="is_default'+dt_index+'">Ya</label></div></td>';
	item += '<td><button type="button" class="btn btn-sm btn-icon-only btn-danger btn-delete-item"><i class="fa-times"></i></button></td>';
	item += '</tr>';
	$('#table_coa tbody').append(item);
	var $t = $('#table_coa .pilihan').last();
	$.each($t,function(k,o){
		var $o = $(o);
		$o.select2({
			dropdownParent : $o.parent(),
			placeholder : ''
		});
	});
}
