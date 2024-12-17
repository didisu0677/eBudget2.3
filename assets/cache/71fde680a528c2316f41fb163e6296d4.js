
var status_group = 0;
$(document).ready(function(){
	var page_data = $('.page-data').data();
	if(page_data && page_data.status_group == 1){
		status_group = 1;
	}
	if(status_group == 1){
		$('.l-cabang').hide();
		$('#filter_cabang').next(".select2-container").hide();
	}
	getData();
});
$('#filter_cabang').on('change',function(){
	if(status_group == 0){
		getData();
	}
});
$('#filter_cabang_induk').change(function(){
	if(status_group == 1){
		getData();
	}
});
function getData(){
	var kode_cabang = $('#filter_cabang_induk option:selected').val();
	if(status_group == 0){
		kode_cabang = $('#filter_cabang option:selected').val();
	}
	if(!kode_cabang){ return ''; }
	cLoader.open(lang.memuat_data + '...');
	var page = base_url + 'transaction/plan_data_kantor/get_data';
	page 	+= '/'+kode_cabang;
	$.ajax({
		url 	: page,
		data 	: { status_group : status_group },
		type	: 'post',
		dataType: 'json',
		success	: function(response) {
			cLoader.close();
			if(!response.status){
				$('#form-command input').val('');
				cAlert.open(response.message,'failed');
				return false;
			}
			if(response){
				v = response;
				$('#id').val(v.id);
				$('#kode_cabang').val(v.kode_cabang);
				$('#kode_cabang').val(v.kode_cabang);
				$('#nama_kantor').val(v.nama_kantor);
				$('#nama_pimpinan').val(v.nama_pimpinan);
				$('#no_hp_pimpinan').val(v.no_hp_pimpinan);
				$('#tgl_mulai_menjabat').val(v.tgl_mulai_menjabat);
				$('#nama_cp').val(v.nama_cp);
				$('#nama_cp2').val(v.nama_cp2);
				$('#no_hp_cp').val(v.no_hp_cp);
				$('#no_hp_cp2').val(v.no_hp_cp2);
				$('#email_Cp').val(v.email_Cp);
				$('#email_lainnya').val(v.email_lainnya);
			}else{
				$('#kode_cabang').val(kode_cabang);
			}
			if(response.access_edit){
				$('#form-command input').prop('disabled',false);
				$('#form-command button').prop('disabled',false);
				$('#form-command button').show();
			}else{
				$('#form-command input').prop('disabled',true);
				$('#form-command button').prop('disabled',true);
				$('#form-command button').hide();
			}
		}
	});
}
$('.btn-wa').on('click',function(){
	var id = $(this).attr('data-id');
	if(id){
		var val = $('#'+id).val();
		if(validatePhone(val)){
			var hashids = new Hashids(encode_key);
    		var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));
			var post_data = {
		        "csrf_token"    	: x[0],
		        "phone" 			: val,
		    }
		    var url = base_url + 'api/redirect_wa';
		    $.redirect(url,post_data,"","_blank");
		}
	}
})
function validatePhone(txt) {
    var filter = /^[0-9-+]+$/;
    if (filter.test(txt)) {
        return true;
    }
    else {
    	cAlert.open("invalid phone number",'info');
        return false;
    }
}
$(document).on('click','.btn-export',function(){
	var kode_cabang = $('#filter_cabang_induk option:selected').val();
	if(status_group == 0){
		kode_cabang = $('#filter_cabang option:selected').val();
	}
	if(!kode_cabang){ return ''; }

	var hashids = new Hashids(encode_key);
    var x = hashids.decode($('meta[name="csrf-token"]').attr('content'));
    var post_data = {
    	"kode_cabang" 		: kode_cabang,
    	"status_group" 		: status_group,
        "csrf_token"    	: x[0],
    }
    var url = base_url + 'transaction/plan_data_kantor/export';
    $.redirect(url,post_data,"","_blank");
});
