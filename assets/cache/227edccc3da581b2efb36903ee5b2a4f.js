
var xhr_ajax = null;
var controller = 'cab_data_kantor';
$(document).ready(function(){
	getData();
});
$('#filter_cabang').on('change',function(){
	getData();
});

function getData(){
	var kode_cabang = $('#filter_cabang option:selected').val();
	var cabang = $('#filter_cabang').val();
	if(!cabang){ return ''; }
	var page = base_url + 'transaction/'+controller+'/data';
	page 	+= '/'+$('#filter_anggaran').val();
	page 	+= '/'+$('#filter_cabang').val();
		
	if( xhr_ajax != null ) {
        xhr_ajax.abort();
        xhr_ajax = null;
    }

	if(cabang){
		cLoader.open(lang.memuat_data + '...');
		xhr_ajax = $.ajax({
			url 	: page,
			data 	: {},
			type	: 'get',
			dataType: 'json',
			success	: function(response) {
				xhr_ajax = null;
				if(!response.status){
					cLoader.close();
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

					if(response.access_edit){
						$('#form-command input').prop('disabled',false);
						$('#form-command button').prop('disabled',false);
						$('#form-command button').show();
					}else{
						$('#form-command input').prop('disabled',true);
						$('#form-command button').prop('disabled',true);
						$('#form-command button').hide();
					}

				}else{
					$('#kode_cabang').val(kode_cabang);
				}
				cLoader.close();
			}
		});
	}
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
